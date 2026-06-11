<?php

namespace App\Mail;

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

    public function __construct(public Registration $registration) {}

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
        $disk = Storage::disk('local')->exists($this->registration->qr_code_path) ? 'local' : 'public';
        $path = Storage::disk($disk)->path($this->registration->qr_code_path);

        return [
            Attachment::fromPath($path)->as('qr-code-acces.svg')->withMime('image/svg+xml'),
        ];
    }
}
