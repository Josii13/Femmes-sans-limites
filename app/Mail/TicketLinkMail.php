<?php

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Renvoi du lien de billet, à la demande de l'inscrite.
 *
 * Volontairement SANS ShouldQueue : c'est le recours de quelqu'un qui n'a pas
 * reçu son email de confirmation. Le mettre en file d'attente reproduirait
 * exactement la panne qu'il est censé réparer.
 */
class TicketLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @param  Collection<int, Registration>  $registrations */
    public function __construct(public Collection $registrations) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Vos billets d\'accès — Femme Sans Limites');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.ticket-link');
    }
}
