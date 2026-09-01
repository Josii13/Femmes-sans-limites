<?php

namespace App\Http\Controllers;

use App\Mail\ContactMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ContactController extends Controller
{
    public function index()
    {
        return view('public.contact');
    }

    public function send(Request $request)
    {
        // Honeypot anti-bot : un humain ne remplit jamais ce champ caché.
        if ($request->filled('website')) {
            return back()->with('success', 'Votre message a bien été envoyé. Nous vous répondrons sous 48h.');
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'email' => 'required|email',
                'subject' => 'required|string|max:200',
                'message' => 'required|string|max:2000',
            ], [
                'name.required' => 'Merci d\'indiquer votre nom.',
                'email.required' => 'Merci d\'indiquer votre adresse email.',
                'email.email' => 'L\'adresse email saisie n\'est pas valide.',
                'subject.required' => 'Merci d\'indiquer un sujet.',
                'message.required' => 'Merci d\'écrire votre message.',
                'message.max' => 'Votre message ne doit pas dépasser 2000 caractères.',
            ]);
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('modal', 'contact');
        }

        $validated['email'] = mb_strtolower(trim($validated['email']));

        // Envoi aux administrateurs, en file, sans jamais faire échouer la requête publique.
        foreach ($this->recipients() as $recipient) {
            try {
                Mail::to($recipient)->queue(new ContactMail($validated));
            } catch (\Throwable $e) {
                Log::warning('Contact : échec mise en file de l\'email', [
                    'to' => $recipient, 'error' => $e->getMessage(),
                ]);
            }
        }

        // Trace de secours : le message reste consultable dans les logs si l'email échoue.
        Log::info('Contact form submission', $validated);

        return back()->with('success', 'Votre message a bien été envoyé. Nous vous répondrons sous 48h.')
            ->with('modal', 'contact');
    }

    /**
     * Destinataires du formulaire : les administrateurs, avec repli sur l'adresse
     * d'expédition configurée pour ne jamais perdre un message.
     *
     * @return array<int, string>
     */
    private function recipients(): array
    {
        $emails = User::where('is_admin', true)->pluck('email')->all();

        if (! $emails) {
            $emails = array_filter([config('mail.from.address')]);
        }

        return array_values(array_unique($emails));
    }
}
