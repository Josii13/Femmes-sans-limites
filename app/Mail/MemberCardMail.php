<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MemberCardMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public \App\Models\Member $member) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Votre carte de membre — Femmes Sans Limites');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.member-card');
    }

    public function attachments(): array
    {
        $path = \Illuminate\Support\Facades\Storage::disk('public')->path($this->member->card_path);
        return [
            Attachment::fromPath($path)->as('carte-membre-fsl.png')->withMime('image/png'),
        ];
    }
}
