<?php

namespace App\Mail;

use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Prévient les autres membres qu'une des leurs fête son anniversaire, avec
 * le lien de son mur de vœux.
 *
 * Un seul email par destinataire même quand plusieurs membres sont nées le
 * même jour : deux emails « c'est l'anniversaire de… » à la suite passent
 * pour du spam, une liste se lit d'un coup d'œil.
 */
class BirthdayAnnouncementMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** @param  Collection<int, Member>  $celebrants */
    public function __construct(public Member $recipient, public Collection $celebrants) {}

    public function envelope(): Envelope
    {
        $names = $this->celebrants->map(fn (Member $m) => self::firstName($m))->values();

        $subject = $names->count() === 1
            ? 'C\'est l\'anniversaire de '.$names[0].' 🎂'
            : 'C\'est l\'anniversaire de '.$names->slice(0, -1)->implode(', ').' et '.$names->last().' 🎂';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.birthday-announcement');
    }

    public static function firstName(Member $member): string
    {
        return explode(' ', trim($member->name))[0] ?: $member->name;
    }
}
