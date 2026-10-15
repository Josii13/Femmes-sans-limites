<?php

namespace App\Mail;

use App\Models\BirthdayWish;
use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Récapitulatif des vœux reçus, envoyé le lendemain.
 *
 * Le jour même, les messages arrivent au compte-gouttes ; les relire d'un
 * bloc le lendemain, c'est là que l'attention se mesure.
 */
class BirthdayWishesDigestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** @param  Collection<int, BirthdayWish>  $wishes */
    public function __construct(public Member $member, public Collection $wishes) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Les messages reçus pour ton anniversaire 💌');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.birthday-digest');
    }
}
