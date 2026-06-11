<?php

namespace App\Http\Controllers;

use App\Mail\EbookDeliveryMail;
use App\Models\Ebook;
use App\Models\Payment;
use App\Services\GeniusPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EbookPurchaseController extends Controller
{
    public function __construct(private GeniusPayService $genius) {}

    /** Formulaire d'achat (nom + email) avant redirection vers le paiement. */
    public function create(Ebook $ebook)
    {
        if ($ebook->status !== 'published' || ! $ebook->isPurchasable()) {
            return redirect()->route('ebooks.show', $ebook->slug);
        }

        return view('public.ebooks.buy', compact('ebook'));
    }

    /** Crée le paiement GeniusPay et redirige le client vers la page de checkout. */
    public function store(Request $request, Ebook $ebook)
    {
        if ($ebook->status !== 'published' || ! $ebook->isPurchasable()) {
            return redirect()->route('ebooks.show', $ebook->slug);
        }

        // Honeypot anti-bot.
        if ($request->filled('website')) {
            return redirect()->route('ebooks.show', $ebook->slug);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:191',
            'phone' => 'nullable|string|max:40',
        ], [
            'name.required' => 'Merci d\'indiquer ton nom.',
            'email.required' => 'Merci d\'indiquer ton adresse email.',
            'email.email' => 'L\'adresse email n\'est pas valide.',
        ]);

        $payment = Payment::create([
            'provider' => 'geniuspay',
            'reference' => (string) Str::uuid(),
            'status' => 'pending',
            'amount' => $ebook->price,
            'currency' => $ebook->currency ?: config('services.geniuspay.currency', 'XOF'),
            'customer_name' => $validated['name'],
            'customer_email' => mb_strtolower(trim($validated['email'])),
            'customer_phone' => $validated['phone'] ?? null,
            'payable_type' => $ebook->getMorphClass(),
            'payable_id' => $ebook->id,
            'metadata' => ['reference' => null, 'type' => 'ebook', 'ebook_id' => $ebook->id],
        ]);

        try {
            $result = $this->genius->createPayment([
                'amount' => (float) $ebook->price,
                'currency' => $payment->currency,
                'description' => 'Ebook : '.$ebook->title,
                'customer' => [
                    'name' => $payment->customer_name,
                    'email' => $payment->customer_email,
                    'phone' => $payment->customer_phone,
                    'country' => 'CI',
                ],
                'success_url' => route('payment.success', $payment->reference),
                'error_url' => route('payment.cancel', $payment->reference),
                'metadata' => ['reference' => $payment->reference, 'type' => 'ebook', 'ebook_id' => $ebook->id],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', "Le paiement n'a pas pu être initié. Réessaie dans un instant.")->withInput();
        }

        $payment->update([
            'provider_reference' => $result['reference'],
            'checkout_url' => $result['checkout_url'],
        ]);

        return redirect()->away($result['checkout_url']);
    }

    /** Formulaire « renvoyer mon lien de téléchargement ». */
    public function resendForm()
    {
        return view('public.ebooks.resend');
    }

    /** Renvoie le lien de téléchargement pour tous les ebooks achetés avec cet email. */
    public function resend(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $email = mb_strtolower(trim($request->email));

        Payment::where('customer_email', $email)
            ->whereIn('status', ['completed', 'paid'])
            ->where('payable_type', (new Ebook)->getMorphClass())
            ->with('payable')
            ->get()
            ->each(function (Payment $payment) {
                if ($payment->payable) {
                    try {
                        Mail::to($payment->customer_email)->queue(new EbookDeliveryMail($payment->payable, $payment));
                    } catch (\Throwable) {
                        // silencieux
                    }
                }
            });

        // Réponse volontairement générique (ne révèle pas l'existence d'un achat).
        return back()->with('resent', true);
    }

    /** Téléchargement de l'ebook acheté (URL signée envoyée par email). */
    public function download(Payment $payment)
    {
        abort_unless($payment->isPaid() && $payment->payable instanceof Ebook, 403);

        $ebook = $payment->payable;
        abort_unless($ebook->file_path && Storage::disk('local')->exists($ebook->file_path), 404);

        return Storage::disk('local')->download(
            $ebook->file_path,
            Str::slug($ebook->title).'.pdf'
        );
    }
}
