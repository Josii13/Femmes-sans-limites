<?php

namespace App\Support;

use App\Mail;
use App\Models\BirthdayWish;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Ebook;
use App\Models\Event;
use App\Models\Member;
use App\Models\NewsletterSubscriber;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\WaitingList;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Str;

/**
 * Jeux d'exemple pour chaque email de l'application.
 *
 * Tous les modèles sont construits en mémoire et jamais enregistrés : tester
 * ses emails ne doit pas semer de fausses inscrites ni de faux paiements dans
 * la base de production, où ces tests ont justement le plus de chances d'être
 * lancés.
 *
 * Les jetons et identifiants sont donc remplis à la main. Sans eux, les vues
 * qui appellent route() planteraient — et Member::birthdayToken() irait même
 * jusqu'à enregistrer le modèle pour s'en créer un.
 */
final class MailSamples
{
    /**
     * Catalogue des emails, groupés comme l'équipe les pense.
     *
     * @return array<string, array{label: string, group: string, description: string}>
     */
    public static function catalog(): array
    {
        return [
            // ── Adhésion ────────────────────────────────────────
            'membership-confirmation' => ['group' => 'Adhésion', 'label' => 'Candidature reçue', 'description' => 'Accusé de réception envoyé à la candidate.'],
            'new-membership' => ['group' => 'Adhésion', 'label' => 'Alerte équipe', 'description' => 'Prévient l\'administration qu\'une candidature est arrivée.'],
            'member-card' => ['group' => 'Adhésion', 'label' => 'Carte de membre', 'description' => 'Adhésion validée, avec la carte en pièce jointe.'],
            'membership-rejected' => ['group' => 'Adhésion', 'label' => 'Candidature refusée', 'description' => 'Réponse négative, à relire attentivement.'],
            'membership-renewal' => ['group' => 'Adhésion', 'label' => 'Relance avant échéance', 'description' => 'Adhésion qui approche de sa fin.'],
            'membership-expired' => ['group' => 'Adhésion', 'label' => 'Adhésion échue', 'description' => 'Même email, après expiration.'],
            'member-password' => ['group' => 'Adhésion', 'label' => 'Accès à l\'espace membre', 'description' => 'Invitation à définir son mot de passe.'],

            // ── Événements ──────────────────────────────────────
            'qr-code' => ['group' => 'Événements', 'label' => 'Billet et QR d\'accès', 'description' => 'Inscription confirmée, avec le QR en pièce jointe.'],
            'payment-link' => ['group' => 'Événements', 'label' => 'Lien de paiement', 'description' => 'Relance manuelle pour une place non réglée.'],
            'ticket-link' => ['group' => 'Événements', 'label' => 'Renvoi du billet', 'description' => 'Demandé depuis « Retrouver mon billet ».'],
            'waiting-list' => ['group' => 'Événements', 'label' => 'Place libérée', 'description' => 'Une place se libère pour la liste d\'attente.'],

            // ── Boutique ────────────────────────────────────────
            'ebook-delivery' => ['group' => 'Boutique', 'label' => 'Livraison d\'ebook', 'description' => 'Achat réglé, lien de téléchargement.'],

            // ── Anniversaires ───────────────────────────────────
            'birthday' => ['group' => 'Anniversaires', 'label' => 'Carte d\'anniversaire', 'description' => 'Le message du jour J, avec sa carte.'],
            'birthday-announcement' => ['group' => 'Anniversaires', 'label' => 'Annonce à la communauté', 'description' => 'Prévient les autres membres de la fête du jour.'],
            'birthday-digest' => ['group' => 'Anniversaires', 'label' => 'Récapitulatif des vœux', 'description' => 'Les messages reçus, renvoyés le lendemain.'],
            'birthday-date-request' => ['group' => 'Anniversaires', 'label' => 'Demande de date', 'description' => 'Invitation à renseigner sa date de naissance.'],

            // ── Communication ───────────────────────────────────
            'newsletter-confirm' => ['group' => 'Communication', 'label' => 'Confirmation newsletter', 'description' => 'Double opt-in après inscription.'],
            'new-event' => ['group' => 'Communication', 'label' => 'Annonce d\'événement', 'description' => 'Nouvel événement annoncé aux abonnées.'],
            'new-ebook' => ['group' => 'Communication', 'label' => 'Annonce d\'ebook', 'description' => 'Nouvelle parution annoncée aux abonnées.'],
            'campaign' => ['group' => 'Communication', 'label' => 'Campagne', 'description' => 'Email de campagne, avec personnalisation.'],
            'contact' => ['group' => 'Communication', 'label' => 'Message de contact', 'description' => 'Formulaire public transmis à l\'équipe.'],

            // ── Technique ───────────────────────────────────────
            'backup-failed' => ['group' => 'Technique', 'label' => 'Échec de sauvegarde', 'description' => 'Alerte envoyée aux administratrices.'],
        ];
    }

    public static function exists(string $key): bool
    {
        return array_key_exists($key, self::catalog());
    }

    public static function make(string $key): Mailable
    {
        return match ($key) {
            'membership-confirmation' => new Mail\MembershipConfirmationMail(self::member()),
            'new-membership' => new Mail\NewMembershipMail(self::member()),
            'member-card' => new Mail\MemberCardMail(self::member()),
            'membership-rejected' => new Mail\MembershipRejectedMail(self::member()),
            'membership-renewal' => new Mail\MembershipRenewalMail(self::member(), expired: false),
            'membership-expired' => new Mail\MembershipRenewalMail(self::member(), expired: true),
            'member-password' => new Mail\MemberPasswordSetupMail(self::member(), url('/espace-membre/mot-de-passe/exemple'), isFirstTime: true),

            'qr-code' => new Mail\QrCodeMail(self::registration(), self::payment()),
            'payment-link' => new Mail\PaymentLinkMail(self::registration(), self::event()),
            'ticket-link' => new Mail\TicketLinkMail(new EloquentCollection([self::registration()])),
            'waiting-list' => new Mail\WaitingListSpotMail(self::event(), self::waitingList()),

            'ebook-delivery' => new Mail\EbookDeliveryMail(self::ebook(), self::payment()),

            'birthday' => new Mail\BirthdayMail(self::member()),
            'birthday-announcement' => new Mail\BirthdayAnnouncementMail(self::member(), new EloquentCollection([self::member('Mariam Traoré')])),
            'birthday-digest' => new Mail\BirthdayWishesDigestMail(self::member(), self::wishes()),
            'birthday-date-request' => new Mail\BirthdayDateRequestMail(self::member()),

            'newsletter-confirm' => new Mail\NewsletterConfirmMail(self::subscriber()),
            'new-event' => new Mail\NewEventMail(self::event(), self::subscriber()),
            'new-ebook' => new Mail\NewEbookMail(self::ebook(), self::subscriber()),
            'campaign' => new Mail\CampaignMail(self::campaign(), self::campaignRecipient()),
            'contact' => new Mail\ContactMail([
                'name' => 'Aïcha Koné',
                'email' => 'aicha@example.com',
                'phone' => Contact::phoneDisplay(),
                'subject' => 'Question sur les ateliers',
                'message' => "Bonjour,\n\nJ'aimerais savoir si les ateliers sont ouverts aux non-membres.\n\nMerci d'avance.",
            ]),

            'backup-failed' => new Mail\BackupFailedMail(
                'Export de la base',
                'SQLSTATE[HY000] [2002] Connexion refusée (exemple)'
            ),

            default => throw new \InvalidArgumentException("Email inconnu : {$key}"),
        };
    }

    // ── Modèles d'exemple, jamais enregistrés ───────────────────

    private static function member(string $name = 'Aïcha Koné'): Member
    {
        $member = new Member([
            'member_number' => 'FSL-0001',
            'name' => $name,
            'email' => 'exemple@femme-sans-limites.com',
            'phone' => Contact::phoneDisplay(),
            'profession' => 'Entrepreneure',
            'country' => 'Côte d\'Ivoire',
            'city' => 'Abidjan',
            'type' => 'gold',
            'status' => 'active',
            'birth_day' => 12,
            'birth_month' => 3,
            'birth_year' => 1990,
            'joined_at' => now()->subYear(),
            'expires_at' => now()->addMonth(),
        ]);

        // Jetons remplis d'avance : les vues en ont besoin pour construire
        // leurs liens, et birthdayToken() enregistrerait le modèle sinon.
        $member->forceFill([
            'id' => 1,
            'verification_token' => 'exemple-verification',
            'birthday_token' => 'exemple-anniversaire',
            'birthday_greeted_at' => now(),
        ]);

        return $member;
    }

    private static function event(): Event
    {
        $event = new Event([
            'title' => 'Atelier « Oser prendre la parole »',
            'slug' => 'exemple-atelier',
            'description' => 'Une matinée pour travailler sa voix, sa posture et son aisance devant un public.',
            'event_date' => now()->addWeeks(3)->setTime(9, 30),
            'location' => 'Espace Latrille',
            'city' => 'Abidjan',
            'capacity' => 40,
            'status' => 'published',
            'is_paid' => true,
            'price' => 5000,
            'currency' => 'XOF',
        ]);
        $event->forceFill(['id' => 1]);

        return $event;
    }

    private static function registration(): Registration
    {
        $registration = new Registration([
            'first_name' => 'Aïcha',
            'last_name' => 'Koné',
            'email' => 'exemple@femme-sans-limites.com',
            'phone' => Contact::phoneDisplay(),
            'status' => 'paid',
        ]);
        $registration->forceFill([
            'id' => 1,
            'event_id' => 1,
            'qr_token' => 'exemple-billet',
            // Chemin volontairement absent du disque : la pièce jointe est
            // alors omise, et l'email se teste quand même.
            'qr_code_path' => 'registrations/qrcodes/exemple.svg',
            'paid_at' => now(),
        ]);
        $registration->setRelation('event', self::event());

        return $registration;
    }

    private static function payment(): Payment
    {
        $payment = new Payment([
            'provider' => 'geniuspay',
            'reference' => 'exemple-reference',
            'status' => 'completed',
            'amount' => 5000,
            'currency' => 'XOF',
            'customer_name' => 'Aïcha Koné',
            'customer_email' => 'exemple@femme-sans-limites.com',
        ]);
        $payment->forceFill([
            'id' => 1,
            'provider_reference' => 'MTX-EXEMPLE',
            'paid_at' => now(),
        ]);

        return $payment;
    }

    private static function ebook(): Ebook
    {
        $ebook = new Ebook([
            'title' => 'Guérir ses blessures invisibles',
            'slug' => 'exemple-ebook',
            'category' => 'Développement personnel',
            'description' => 'Un chemin pour nommer ce qui fait mal et avancer.',
            'status' => 'published',
            'is_paid' => true,
            'price' => 5000,
            'currency' => 'XOF',
            'file_path' => 'ebooks/exemple.pdf',
        ]);
        $ebook->forceFill(['id' => 1, 'published_at' => now()]);

        return $ebook;
    }

    private static function subscriber(): NewsletterSubscriber
    {
        $subscriber = new NewsletterSubscriber([
            'email' => 'exemple@femme-sans-limites.com',
            'name' => 'Aïcha',
        ]);
        $subscriber->forceFill(['id' => 1, 'token' => 'exemple-abonnee']);

        return $subscriber;
    }

    private static function waitingList(): WaitingList
    {
        $entry = new WaitingList([
            'first_name' => 'Aïcha',
            'last_name' => 'Koné',
            'email' => 'exemple@femme-sans-limites.com',
        ]);
        $entry->forceFill(['id' => 1, 'event_id' => 1]);

        return $entry;
    }

    private static function campaign(): Campaign
    {
        $campaign = new Campaign([
            'subject' => 'Des nouvelles de la communauté, {nom}',
            'body' => "Bonjour {nom},\n\nVoici les rendez-vous du mois.\n\nÀ très vite,\nL'équipe FSL",
            'status' => 'sent',
        ]);
        $campaign->forceFill(['id' => 1]);

        return $campaign;
    }

    private static function campaignRecipient(): CampaignRecipient
    {
        $recipient = new CampaignRecipient([
            'email' => 'exemple@femme-sans-limites.com',
            'name' => 'Aïcha',
        ]);
        // « preview » : jeton reconnu par CampaignMail, qui omet alors le lien
        // de désinscription plutôt que de pointer vers un jeton inexistant.
        $recipient->forceFill(['id' => 1, 'campaign_id' => 1, 'token' => 'preview']);

        return $recipient;
    }

    private static function wishes(): EloquentCollection
    {
        $textes = [
            ['Mariam', 'Belle année à toi ! Que cette nouvelle année t\'apporte tout ce que tu mérites.'],
            ['Fatou', 'Joyeux anniversaire ma sœur. Merci d\'être une telle source d\'inspiration.'],
        ];

        return new EloquentCollection(array_map(function ($t) {
            $wish = new BirthdayWish([
                'author_name' => $t[0],
                'message' => $t[1],
            ]);
            $wish->forceFill(['id' => Str::random(4), 'member_id' => 1, 'created_at' => now()]);

            return $wish;
        }, $textes));
    }
}
