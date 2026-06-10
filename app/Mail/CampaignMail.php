<?php

namespace App\Mail;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Campaign $campaign,
        public CampaignRecipient $recipient
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->campaign->subject);
    }

    public function content(): Content
    {
        $member = $this->recipient->member ?? Member::find($this->recipient->member_id);
        $fullName = $this->recipient->name ?? ($member?->name ?? '');
        $firstName = mb_ucfirst(explode(' ', trim($fullName))[0]);

        $vars = [
            '{prenom}' => $firstName,
            '{nom}' => $fullName,
            '{numero}' => $member?->member_number ?? '',
            '{type}' => $member ? ucfirst($member->type) : '',
            '{ville}' => $member?->city ?? '',
            '{pays}' => $member?->country ?? '',
            '{profession}' => $member?->profession ?? '',
            // variantes avec crochets (format alternatif)
            '[prenom]' => $firstName,
            '[nom]' => $fullName,
            '[numero]' => $member?->member_number ?? '',
            '[type]' => $member ? ucfirst($member->type) : '',
            '[ville]' => $member?->city ?? '',
        ];

        $body = str_replace(array_keys($vars), array_values($vars), $this->campaign->body);

        // Lien de désinscription (RGPD). Absent pour l'aperçu (token factice « preview »).
        $unsubscribeUrl = ($this->recipient->token && $this->recipient->token !== 'preview')
            ? route('marketing.unsubscribe', $this->recipient->token)
            : null;

        return new Content(
            view: 'emails.campaign',
            with: [
                'resolvedBody' => $body,
                'unsubscribeUrl' => $unsubscribeUrl,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
