<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\DetectsBots;
use App\Mail\TicketLinkMail;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Billet d'accès consultable en ligne.
 *
 * Le QR n'existait que dans un email. Quand cet email n'arrivait pas — file
 * d'attente sans worker, spam, suppression — l'inscrite n'avait plus aucun
 * moyen de récupérer son accès, et se réinscrivait pour comprendre : elle
 * tombait alors sur « Vous êtes déjà inscrit(e) », sans issue.
 *
 * Le jeton du QR sert de secret : connaître l'URL équivaut à détenir le QR,
 * ni plus ni moins. Les pages sont en noindex pour qu'aucun billet ne se
 * retrouve dans un moteur de recherche.
 */
class TicketController extends Controller
{
    use DetectsBots;

    public function show(string $token)
    {
        $registration = Registration::where('qr_token', $token)
            ->whereIn('status', ['paid', 'attended'])
            ->with('event')
            ->firstOrFail();

        // Le SVG vient de notre propre générateur : il est affiché tel quel
        // dans la page, pour que le billet reste lisible hors connexion une
        // fois la page ouverte — le jour J, le réseau est rarement fiable.
        $svg = Storage::disk('local')->exists((string) $registration->qr_code_path)
            ? Storage::disk('local')->get($registration->qr_code_path)
            : null;

        return view('public.tickets.show', compact('registration', 'svg'));
    }

    public function download(string $token): StreamedResponse
    {
        $registration = Registration::where('qr_token', $token)
            ->whereIn('status', ['paid', 'attended'])
            ->firstOrFail();

        abort_unless(
            $registration->qr_code_path && Storage::disk('local')->exists($registration->qr_code_path),
            404
        );

        return Storage::disk('local')->download($registration->qr_code_path, 'billet-fsl.svg');
    }

    public function findForm()
    {
        return view('public.tickets.find');
    }

    /** Renvoie par email le lien du billet pour chaque inscription confirmée. */
    public function send(Request $request)
    {
        if ($this->isBotSubmission($request, 'ticket.find')) {
            return back()->with('sent', true);
        }

        $request->validate(['email' => 'required|email']);
        $email = mb_strtolower(trim($request->email));

        $registrations = Registration::where('email', $email)
            ->whereIn('status', ['paid', 'attended'])
            ->whereNotNull('qr_token')
            ->whereHas('event', fn ($q) => $q->where('event_date', '>=', now()->startOfDay()))
            ->with('event')
            ->get();

        if ($registrations->isNotEmpty()) {
            try {
                Mail::to($email)->send(new TicketLinkMail($registrations));
            } catch (\Throwable) {
                // Silencieux : la réponse ne doit rien révéler de l'existence du compte.
            }
        }

        // Réponse volontairement identique dans tous les cas : elle ne dit pas
        // si cette adresse est inscrite à quoi que ce soit.
        return back()->with('sent', true);
    }
}
