<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentLinkMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Registration $registration,
        public Event $event
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
