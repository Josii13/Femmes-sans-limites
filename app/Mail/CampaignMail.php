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
        $vars = self::variables($this->member(), ['nom' => $this->recipient->name]);

        return new Envelope(subject: self::personalize($this->campaign->subject, $vars));
    }

    public function content(): Content
    {
        $vars = self::variables($this->member(), ['nom' => $this->recipient->name]);
        $body = self::personalize($this->campaign->body, $vars);

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

    private function member(): ?Member
    {
        return $this->recipient->member ?? Member::find($this->recipient->member_id);
    }

    /**
     * Construit le jeu de variables de personnalisation à partir d'un membre,
     * avec des valeurs de repli optionnelles (aperçu, destinataire hors-membre).
     */
    public static function variables(?Member $member, array $defaults = []): array
    {
        $fullName = $member?->name ?: ($defaults['nom'] ?? '');
        $parts = preg_split('/\s+/', trim((string) $fullName)) ?: [];
        $firstName = mb_ucfirst($parts[0] ?? '');

        return [
            'prenom' => $defaults['prenom'] ?? $firstName,
            'nom' => $fullName,
            'numero' => $member?->member_number ?? ($defaults['numero'] ?? ''),
            'type' => $member ? ucfirst($member->type) : ($defaults['type'] ?? ''),
            'ville' => $member?->city ?? ($defaults['ville'] ?? ''),
            'pays' => $member?->country ?? ($defaults['pays'] ?? ''),
            'profession' => $member?->profession ?? ($defaults['profession'] ?? ''),
            'mois' => now()->translatedFormat('F Y'),
        ];
    }

    /**
     * Remplace les variables de personnalisation dans un texte.
     * Tolérant : accepte {var} et [var], insensible à la casse et aux accents
     * (ex. {prenom}, [Prénom], [PRENOM] → tous remplacés).
     */
    public static function personalize(string $text, array $vars): string
    {
        $labels = [
            'prenom' => ['prenom', 'prénom'],
            'nom' => ['nom'],
            'numero' => ['numero', 'numéro', 'numero de membre', 'numéro de membre'],
            'type' => ['type'],
            'ville' => ['ville'],
            'pays' => ['pays'],
            'profession' => ['profession'],
            'mois' => ['mois'],
        ];

        foreach ($labels as $key => $aliases) {
            $value = (string) ($vars[$key] ?? '');
            foreach ($aliases as $alias) {
                $text = preg_replace(
                    '/[\{\[]\s*'.preg_quote($alias, '/').'\s*[\}\]]/iu',
                    $value,
                    $text
                );
            }
        }

        return $text;
    }
}
