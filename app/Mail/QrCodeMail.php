<?php

namespace App\Mail;

use App\Models\Payment;
use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class QrCodeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Registration $registration,
        public ?Payment $payment = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Votre QR code d\'accès — '.$this->registration->event->title);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.qr-code');
    }

    public function attachments(): array
    {
        $stored = $this->registration->qr_code_path;

        // Un fichier manquant faisait échouer tout l'envoi, donc privait
        // l'inscrite y compris du lien vers son billet — qui, lui, suffit à
        // entrer. La pièce jointe est un confort, pas la condition de l'email.
        $disk = collect(['local', 'public'])
            ->first(fn ($d) => $stored && Storage::disk($d)->exists($stored));

        if (! $disk) {
            return [];
        }

        return [
            Attachment::fromPath(Storage::disk($disk)->path($stored))
                ->as('qr-code-acces.svg')->withMime('image/svg+xml'),
        ];
    }
}
