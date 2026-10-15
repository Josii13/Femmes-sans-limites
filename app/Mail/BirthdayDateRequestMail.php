<?php

namespace App\Mail;

use App\Models\Member;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Invite une membre à donner sa date de naissance.
 *
 * Le lien ouvre directement son propre formulaire sur la page de collecte :
 * celles qui ne sont pas dans le groupe WhatsApp, ou qui y ont manqué le
 * lien, n'ont pas à chercher leur photo parmi toutes les autres.
 */
class BirthdayDateRequestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Member $member) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Quand fête-t-on ton anniversaire ? 🎂');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.birthday-date-request', with: [
            'link' => route('birthdays.collect', [
                'token' => Setting::birthdayToken(),
                'membre' => $this->member->id,
            ]),
            'first' => explode(' ', trim($this->member->name))[0] ?: $this->member->name,
        ]);
    }
}
