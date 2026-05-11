<?php

namespace App\Mail;

use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MembershipConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Member $member) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Candidature reçue — Femmes Sans Limites');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.membership-confirmation');
    }
}
