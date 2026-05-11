<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QrCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public \App\Models\Registration $registration) {}

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
        $path = \Illuminate\Support\Facades\Storage::disk('public')->path($this->registration->qr_code_path);
        return [
            Attachment::fromPath($path)->as('qr-code-acces.svg')->withMime('image/svg+xml'),
        ];
    }
}
