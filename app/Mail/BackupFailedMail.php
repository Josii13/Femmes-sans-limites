<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Alerte d'échec de la sauvegarde automatique.
 *
 * Délibérément NON mise en file : une sauvegarde échoue souvent parce que la base
 * ou le disque pose problème — or la file d'attente repose justement sur la base.
 * L'alerte part donc immédiatement, dans le processus du cron.
 */
class BackupFailedMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public string $step,
        public string $reason,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '⚠️ Échec de la sauvegarde — Femme Sans Limites');
    }

    public function content(): Content
    {
        return new Content(text: 'emails.backup-failed');
    }
}
