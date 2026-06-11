<?php

namespace App\Mail;

use App\Models\Ebook;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class EbookDeliveryMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ebook $ebook,
        public Payment $payment,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📚 Ton ebook « '.$this->ebook->title.' » est prêt',
        );
    }

    public function content(): Content
    {
        // Lien de téléchargement signé, valable 30 jours.
        $downloadUrl = URL::temporarySignedRoute(
            'ebooks.download',
            now()->addDays(30),
            ['payment' => $this->payment->reference],
        );

        return new Content(
            markdown: 'emails.ebook-delivery',
            with: ['downloadUrl' => $downloadUrl],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
