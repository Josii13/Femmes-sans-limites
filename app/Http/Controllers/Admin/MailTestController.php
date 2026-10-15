<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Support\MailSamples;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * Banc d'essai des emails.
 *
 * Jusqu'ici, vérifier qu'un email partait et s'affichait correctement
 * supposait de reproduire la situation réelle : créer une inscription,
 * payer, attendre. Les emails rares — refus de candidature, échec de
 * sauvegarde — n'étaient donc jamais relus.
 *
 * Les envois sont immédiats, jamais mis en file : un test dont le résultat
 * dépend d'un worker ne teste pas grand-chose.
 */
class MailTestController extends Controller
{
    public function index()
    {
        return view('admin.emails.index', [
            // preserveKeys : sans lui, groupBy ré-indexe numériquement et la
            // vue construisait des liens /emails/1/apercu au lieu de la clé.
            'catalog' => collect(MailSamples::catalog())->groupBy('group', preserveKeys: true),
        ]);
    }

    /** Affiche l'email dans le navigateur, sans rien envoyer. */
    public function preview(string $key)
    {
        abort_unless(MailSamples::exists($key), 404);

        return MailSamples::make($key)->render();
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'keys' => 'required|array|min:1',
            'keys.*' => 'string',
        ], [], ['email' => 'adresse', 'keys' => 'emails à tester']);

        $keys = array_values(array_filter($validated['keys'], MailSamples::exists(...)));

        $sent = [];
        $failed = [];

        foreach ($keys as $key) {
            try {
                // sendNow() et non send() : la plupart de ces mailables sont
                // ShouldQueue, et send() les mettrait en file. On veut l'erreur
                // SMTP tout de suite, pas un job silencieux qui échouera plus tard.
                Mail::to($validated['email'])->sendNow(MailSamples::make($key));
                $sent[] = $key;
            } catch (\Throwable $e) {
                // Le message brut du transport est ce qui sert : « authentification
                // refusée » et « port bloqué » appellent deux corrections.
                $failed[$key] = $e->getMessage();
            }
        }

        ActivityLog::record('mail.tested', null, [
            'to' => $validated['email'],
            'sent' => count($sent),
            'failed' => count($failed),
        ]);

        return back()
            ->with('sent', $sent)
            ->with('failed', $failed)
            ->with('tested_email', $validated['email']);
    }
}
