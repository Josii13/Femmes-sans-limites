<?php

namespace App\Http\Controllers;

use App\Mail\NewsletterConfirmMail;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NewsletterController extends Controller
{
    /**
     * Inscription en double opt-in : on enregistre l'abonné comme NON confirmé
     * et on lui envoie un email de confirmation. L'abonnement n'est effectif
     * qu'après clic sur le lien (preuve de consentement).
     */
    public function subscribe(Request $request)
    {
        // Honeypot anti-bot.
        if ($request->filled('website')) {
            return back()->with('newsletter_pending', true);
        }

        // Sac d'erreurs dédié « newsletter » : le formulaire vit dans le pied de page,
        // partagé avec les modals adhésion/contact qui ont aussi un champ « email ».
        $request->validateWithBag('newsletter', [
            'email' => 'required|email|max:191',
            'name' => 'nullable|string|max:100',
        ], [
            'email.required' => 'Merci de renseigner ton adresse email.',
            'email.email' => 'Cette adresse email est invalide.',
        ]);

        // Normalisation identique au reste du site (achats ebook, inscriptions) :
        // évite deux abonnés pour la même adresse écrite différemment.
        $email = mb_strtolower(trim($request->email));

        $subscriber = NewsletterSubscriber::firstOrCreate(
            ['email' => $email],
            ['name' => $request->name],
        );

        // Déjà confirmé et toujours abonné : rien à refaire.
        if ($subscriber->isConfirmed() && ! $subscriber->isUnsubscribed()) {
            return back()->with('newsletter_success', true);
        }

        // Réabonnement d'un ancien désinscrit : on réinitialise l'état d'opt-in.
        $subscriber->update([
            'name' => $request->name ?: $subscriber->name,
            'confirmed_at' => null,
            'unsubscribed_at' => null,
            'consent_ip' => $request->ip(),
            'consent_at' => now(),
        ]);

        try {
            Mail::to($subscriber->email)->send(new NewsletterConfirmMail($subscriber));
        } catch (\Throwable $e) {
            Log::warning('Newsletter: échec envoi email de confirmation', [
                'email' => $subscriber->email, 'error' => $e->getMessage(),
            ]);
        }

        return back()->with('newsletter_pending', true);
    }

    /**
     * Confirmation du double opt-in (lien reçu par email).
     */
    public function confirm(string $token)
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->first();

        if (! $subscriber) {
            abort(404);
        }

        if (! $subscriber->isConfirmed()) {
            $subscriber->update([
                'confirmed_at' => now(),
                'unsubscribed_at' => null,
                'consent_at' => $subscriber->consent_at ?: now(),
            ]);
        }

        return view('public.newsletter.confirmed', compact('subscriber'));
    }

    /**
     * Affiche la page de confirmation de désinscription (jamais d'action sur un simple GET,
     * pour éviter les désinscriptions involontaires par pré-chargement de lien).
     */
    public function unsubscribe(string $token)
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->firstOrFail();

        return view('public.newsletter.unsubscribe', compact('subscriber'));
    }

    /**
     * Désinscription effective (POST) : on conserve la ligne avec un flag
     * (preuve d'opt-out + blocage des réinscriptions silencieuses).
     */
    public function unsubscribeConfirm(string $token)
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->firstOrFail();

        if (! $subscriber->isUnsubscribed()) {
            $subscriber->update(['unsubscribed_at' => now()]);
        }

        return view('public.newsletter.unsubscribed');
    }
}
