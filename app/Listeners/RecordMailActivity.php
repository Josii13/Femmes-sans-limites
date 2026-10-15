<?php

namespace App\Listeners;

use App\Models\MailLog;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Journalise chaque email qui sort de l'application.
 *
 * Branché sur les événements du framework plutôt que sur les appels d'envoi :
 * aucun point d'appel à modifier, et surtout aucun à oublier — y compris ceux
 * qu'on écrira demain.
 *
 * L'identifiant de la ligne voyage dans un en-tête du message : MessageSending
 * et MessageSent reçoivent le même objet Email, l'en-tête posé au départ se
 * relit donc à l'arrivée. C'est plus sûr que de rapprocher les deux par
 * destinataire et sujet, deux envois simultanés pouvant être identiques.
 */
class RecordMailActivity
{
    private const HEADER = 'X-FSL-Log';

    /** Marqueur posé par le banc d'essai sur ses propres envois. */
    public const TEST_HEADER = 'X-FSL-Test';

    public function sending(MessageSending $event): void
    {
        try {
            $headers = $event->message->getHeaders();

            $addresses = array_map(
                fn ($a) => $a->getAddress(),
                // Un envoi en copie cachée seule n'a pas de destinataire « to ».
                $event->message->getTo() ?: ($event->message->getCc() ?: $event->message->getBcc())
            );

            $log = MailLog::create([
                'mailable' => $event->data['__laravel_mailable'] ?? null,
                // Tronqué avant l'insertion : en MySQL strict, un sujet trop
                // long fait échouer la requête, et la trace se perd en silence
                // — exactement le contraire de ce que ce journal doit faire.
                'subject' => Str::limit((string) $event->message->getSubject(), 290),
                'recipient' => Str::limit($addresses[0] ?? '', 185) ?: null,
                'recipients_count' => max(1, count($addresses)),
                'status' => 'sending',
                'is_test' => $headers->has(self::TEST_HEADER),
            ]);

            $headers->addTextHeader(self::HEADER, (string) $log->id);
        } catch (\Throwable $e) {
            // Le journal ne doit jamais empêcher un email de partir : mieux
            // vaut une trace manquante qu'une inscription sans son QR.
            Log::warning('Journal des emails : écriture impossible', ['error' => $e->getMessage()]);
        }
    }

    public function sent(MessageSent $event): void
    {
        try {
            // MessageSent expose l'Email via la propriété magique $message ;
            // $event->sent est le wrapper SentMessage, qui n'a pas d'en-têtes.
            $header = $event->message->getHeaders()->get(self::HEADER);

            if (! $header) {
                return;
            }

            MailLog::whereKey((int) $header->getBodyAsString())
                ->update(['status' => 'sent', 'sent_at' => now(), 'updated_at' => now()]);
        } catch (\Throwable $e) {
            Log::warning('Journal des emails : confirmation impossible', ['error' => $e->getMessage()]);
        }
    }
}
