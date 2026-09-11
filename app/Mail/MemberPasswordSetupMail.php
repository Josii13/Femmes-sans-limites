<?php

namespace App\Mail;

use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Lien de définition ou de réinitialisation du mot de passe de l'espace membre.
 *
 * Le même message sert dans les deux cas : à l'activation d'une adhésion et sur
 * demande d'oubli. Aucun mot de passe n'est jamais transmis par email — seul un
 * lien à usage limité dans le temps l'est.
 */
class MemberPasswordSetupMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Member $member,
        public string $url,
        public bool $isFirstTime = false,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->isFirstTime
                ? 'Bienvenue — activez votre espace membre'
                : 'Réinitialisation de votre mot de passe — Femme Sans Limites',
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.member-password-setup');
    }
}
