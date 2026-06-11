<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\WaitingList;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WaitingListSpotMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Event $event,
        public WaitingList $entry,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 Une place s\'est libérée : '.$this->event->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.waiting-list-spot',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
