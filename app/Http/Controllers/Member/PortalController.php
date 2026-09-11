<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Ebook;
use App\Models\Event;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Espace membre : ce qu'une adhérente possède déjà mais n'avait aucun moyen de
 * consulter — sa carte, la date d'échéance de son adhésion, ses inscriptions
 * aux événements et ses ebooks achetés.
 */
class PortalController extends Controller
{
    public function dashboard()
    {
        $member = $this->member();

        return view('member.dashboard', [
            'member' => $member,
            'upcomingRegistrations' => $this->memberRegistrations($member)
                ->filter(fn (Registration $r) => $r->event && $r->event->event_date->isFuture())
                ->take(3),
            'purchaseCount' => $this->memberPurchases($member)->count(),
            'daysUntilExpiry' => $member->expires_at?->diffInDays(now(), absolute: false),
            'nextEvents' => Event::where('status', 'published')
                ->where('event_date', '>=', now())
                ->orderBy('event_date')
                ->limit(3)
                ->get(),
        ]);
    }

    public function registrations()
    {
        $member = $this->member();

        return view('member.registrations', [
            'member' => $member,
            'registrations' => $this->memberRegistrations($member),
        ]);
    }

    public function purchases()
    {
        $member = $this->member();

        return view('member.purchases', [
            'member' => $member,
            'purchases' => $this->memberPurchases($member),
        ]);
    }

    /** Téléchargement de la carte de membre depuis l'espace personnel. */
    public function downloadCard(): StreamedResponse
    {
        $member = $this->member();

        abort_unless($member->card_path && Storage::disk('public')->exists($member->card_path), 404,
            'Votre carte est en cours de génération. Réessayez dans quelques instants.');

        return Storage::disk('public')->download(
            $member->card_path,
            'carte-membre-'.$member->member_number.'.png'
        );
    }

    // ── Profil ──────────────────────────────────────────────────

    public function editProfile()
    {
        return view('member.profile', ['member' => $this->member()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $member = $this->member();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:30',
            'profession' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:3072',
            'show_in_gallery' => 'boolean',
            'marketing_opt_in' => 'boolean',
        ], [
            'photo.max' => 'La photo ne doit pas dépasser 3 Mo.',
        ]);

        // L'email n'est pas modifiable ici : il identifie l'adhésion, sert de
        // clé aux inscriptions et aux achats, et son changement doit être vérifié.
        if ($request->hasFile('photo')) {
            if ($member->photo) {
                Storage::disk('public')->delete($member->photo);
            }
            $validated['photo'] = $request->file('photo')->store('members/photos', 'public');
        } else {
            unset($validated['photo']);
        }

        $validated['show_in_gallery'] = $request->boolean('show_in_gallery');

        // Droit d'opposition RGPD : la case est formulée positivement côté membre.
        $member->marketing_opt_out_at = $request->boolean('marketing_opt_in') ? null : ($member->marketing_opt_out_at ?? now());
        unset($validated['marketing_opt_in']);

        $member->fill($validated)->save();

        return redirect()->route('member.profile')
            ->with('success', 'Vos informations sont à jour.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $member = $this->member();

        $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'Indiquez votre mot de passe actuel.',
            'password.confirmed' => 'Les deux nouveaux mots de passe ne correspondent pas.',
        ]);

        if (! Hash::check($request->input('current_password'), (string) $member->password)) {
            return back()->withErrors(['current_password' => 'Ce mot de passe ne correspond pas à votre compte.']);
        }

        $member->forceFill(['password' => Hash::make($request->input('password'))])->save();

        return redirect()->route('member.profile')->with('success', 'Votre mot de passe est modifié.');
    }

    // ── Accès aux données ───────────────────────────────────────

    private function member(): Member
    {
        return Auth::guard('member')->user();
    }

    /**
     * Inscriptions de la membre. Le rapprochement se fait par email : les
     * inscriptions aux événements sont ouvertes à tous, membres ou non.
     */
    private function memberRegistrations(Member $member)
    {
        return Registration::where('email', $member->email)
            ->with('event')
            ->get()
            ->sortByDesc(fn (Registration $r) => $r->event?->event_date)
            ->values();
    }

    /** Ebooks achetés, rapprochés eux aussi par l'adresse email du paiement. */
    private function memberPurchases(Member $member)
    {
        return Payment::where('customer_email', $member->email)
            ->whereIn('status', ['completed', 'paid'])
            ->where('payable_type', (new Ebook)->getMorphClass())
            ->with('payable')
            ->latest()
            ->get()
            ->filter(fn (Payment $payment) => $payment->payable !== null)
            ->map(function (Payment $payment) {
                // Lien de téléchargement régénéré à chaque affichage : celui reçu
                // par email expire au bout de 30 jours, l'espace membre ne doit pas.
                $payment->download_url = URL::temporarySignedRoute(
                    'ebooks.download', now()->addDay(), ['payment' => $payment->reference]
                );

                return $payment;
            })
            ->values();
    }
}
