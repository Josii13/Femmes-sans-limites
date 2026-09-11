<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Services\GeniusPayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Cotisation et renouvellement en ligne.
 *
 * L'association disposait déjà d'une chaîne de paiement complète (ebooks,
 * événements) mais l'adhésion elle-même ne rapportait rien, et l'email de
 * relance renvoyait vers le formulaire de contact.
 *
 * Le paiement réutilise le même prestataire et le même webhook : l'adhésion est
 * simplement un nouveau type d'objet payable.
 */
class MembershipPaymentController extends Controller
{
    public function __construct(private GeniusPayService $genius) {}

    public function show()
    {
        $member = $this->member();

        return view('member.renewal', [
            'member' => $member,
            'plans' => MembershipPlan::available()->get(),
            'currentPlan' => MembershipPlan::where('type', $member->type)->first(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $member = $this->member();

        $validated = $request->validate([
            'plan' => 'required|exists:membership_plans,type',
        ], [
            'plan.required' => 'Choisissez une formule.',
        ]);

        $plan = MembershipPlan::where('type', $validated['plan'])->where('is_active', true)->first();

        if (! $plan) {
            return back()->with('error', 'Cette formule n’est plus disponible.');
        }

        // Une formule gratuite ne passe pas par le paiement : on prolonge directement.
        if (! $plan->isPaid()) {
            return back()->with('error', 'La formule '.$plan->name.' est gratuite : votre adhésion actuelle la couvre déjà. Contactez-nous pour toute question.');
        }

        $payment = Payment::create([
            'provider' => 'geniuspay',
            'reference' => (string) Str::uuid(),
            'status' => 'pending',
            'amount' => $plan->price,
            'currency' => $plan->currency ?: config('services.geniuspay.currency', 'XOF'),
            'customer_name' => $member->name,
            'customer_email' => $member->email,
            'customer_phone' => $member->phone,
            'payable_type' => $member->getMorphClass(),
            'payable_id' => $member->id,
            // Le niveau visé est figé ici : un changement de tarif après coup ne
            // doit pas modifier ce que la membre a effectivement acheté.
            'metadata' => [
                'type' => 'membership',
                'plan' => $plan->type,
                'duration_months' => $plan->duration_months,
                'member_id' => $member->id,
            ],
        ]);

        try {
            $result = $this->genius->createPayment([
                'amount' => (float) $plan->price,
                'currency' => $payment->currency,
                'description' => 'Adhésion '.$plan->name.' — '.$member->member_number,
                'customer' => [
                    'name' => $member->name,
                    'email' => $member->email,
                    'phone' => $member->phone,
                    'country' => 'CI',
                ],
                'success_url' => route('payment.success', $payment->reference),
                'error_url' => route('payment.cancel', $payment->reference),
                'metadata' => ['reference' => $payment->reference, 'type' => 'membership', 'member_id' => $member->id],
            ]);
        } catch (\Throwable $e) {
            report($e);
            $payment->update(['status' => 'failed']);

            return back()->with('error', 'Le paiement n’a pas pu être initié. Réessayez dans un instant.');
        }

        $payment->update([
            'provider_reference' => $result['reference'],
            'checkout_url' => $result['checkout_url'],
        ]);

        return redirect()->away($result['checkout_url']);
    }

    private function member(): Member
    {
        return Auth::guard('member')->user();
    }
}
