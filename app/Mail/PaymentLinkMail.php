<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public \App\Models\Registration $registration,
        public \App\Models\Event $event
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Votre lien de paiement — '.$this->event->title);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.payment-link');
    }

    public function attachments(): array
    {
        return [];
    }
}
