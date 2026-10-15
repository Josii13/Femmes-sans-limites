<?php

namespace App\Models;

use App\Mail\MemberPasswordSetupMail;
use App\Support\CommunityRules;
use Carbon\Carbon;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use SensitiveParameter;

class Member extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use AuthenticatableTrait, CanResetPassword, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'member_number', 'name', 'email', 'phone', 'motivation', 'profession',
        'rules_accepted_at', 'rules_version',
        'birth_day', 'birth_month', 'birth_year', 'birth_date_filled_at', 'birthday_invited_at',
        'birthday_token', 'birthday_greeted_at', 'birthday_card_path', 'birthday_digest_sent_at',
        'country', 'city', 'photo', 'show_in_gallery', 'type', 'status', 'card_path', 'verification_token',
        'marketing_opt_out_at', 'joined_at', 'expires_at', 'renewal_reminded_at',
    ];

    /**
     * Jamais exposé : ni en JSON, ni dans un dump de debug.
     * `password` est volontairement absent de $fillable : il ne se définit que
     * par assignation explicite, jamais depuis une requête HTTP.
     */
    protected $hidden = ['password', 'remember_token', 'verification_token'];

    protected static function booted(): void
    {
        static::creating(function (Member $member) {
            $member->verification_token ??= Str::lower(Str::random(20));
        });
    }

    /** Cycle de vie des adhésions. */
    public const STATUSES = ['pending', 'active', 'rejected', 'expired', 'suspended'];

    public const TYPES = ['standard', 'gold', 'premium'];

    /** Durée de validité d'une adhésion (en années) à compter de l'activation. */
    public const MEMBERSHIP_YEARS = 1;

    protected function casts(): array
    {
        return [
            'marketing_opt_out_at' => 'datetime',
            'joined_at' => 'datetime',
            'expires_at' => 'datetime',
            'renewal_reminded_at' => 'datetime',
            'last_login_at' => 'datetime',
            'rules_accepted_at' => 'datetime',
            'birth_date_filled_at' => 'datetime',
            'birthday_invited_at' => 'datetime',
            'birthday_greeted_at' => 'datetime',
            'birthday_digest_sent_at' => 'datetime',
            'show_in_gallery' => 'boolean',
        ];
    }

    // ── Anniversaires ───────────────────────────────────────────

    public function hasBirthday(): bool
    {
        return $this->birth_day !== null && $this->birth_month !== null;
    }

    public function birthdayWishes()
    {
        return $this->hasMany(BirthdayWish::class)->latest();
    }

    /**
     * Jeton de sa page d'anniversaire, créé à la première demande.
     *
     * Le lien circule dans le groupe le jour J : il doit être impossible à
     * deviner, sans pour autant exiger un compte de celles qui veulent
     * simplement laisser un mot.
     */
    public function birthdayToken(): string
    {
        if (! $this->birthday_token) {
            $this->forceFill(['birthday_token' => Str::lower(Str::random(32))])->save();
        }

        return $this->birthday_token;
    }

    /**
     * Le mur n'accepte des vœux que pendant la fête.
     *
     * Une boîte à messages ouverte toute l'année sur la page d'une membre
     * finirait en dépotoir à spam, et un mot reçu en juillet pour un
     * anniversaire de mars ne fait plaisir à personne.
     */
    public function birthdayWishesOpen(): bool
    {
        if (! $this->birthday_greeted_at) {
            return false;
        }

        return $this->birthday_greeted_at->gt(now()->subDays(7));
    }

    /** Membres actives dont la date reste à recueillir. */
    public function scopeMissingBirthday(Builder $query): Builder
    {
        return $query->where('status', 'active')->whereNull('birth_day');
    }

    /**
     * Membres joignables par un email communautaire : adhésion active, adresse
     * renseignée, et pas d'opposition aux communications (RGPD) — le même
     * filtre que les campagnes.
     */
    public function scopeReachableByEmail(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->marketable()
            ->whereNotNull('email')
            ->where('email', '!=', '');
    }

    /** Délai minimal entre deux emails demandant la date de naissance. */
    public const BIRTHDAY_INVITE_COOLDOWN_DAYS = 3;

    /**
     * Membres à qui l'on peut demander leur date de naissance par email : date
     * inconnue, joignables, et pas déjà sollicitées ces derniers jours — un
     * double clic ou deux administratrices le même jour enverraient sinon le
     * même email deux fois.
     */
    public function scopeInvitableForBirthday(Builder $query): Builder
    {
        return $query->missingBirthday()
            ->reachableByEmail()
            ->where(function (Builder $q) {
                $q->whereNull('birthday_invited_at')
                    ->orWhere('birthday_invited_at', '<', now()->subDays(self::BIRTHDAY_INVITE_COOLDOWN_DAYS));
            });
    }

    /**
     * Membres actives qui fêtent leur anniversaire ce jour-là.
     *
     * Le 29 février est rattaché au 28 : sans cela, une femme née un 29
     * ne serait fêtée qu'une année sur quatre.
     */
    public function scopeBirthdayOn(Builder $query, \DateTimeInterface $date): Builder
    {
        $day = (int) $date->format('j');
        $month = (int) $date->format('n');

        $query->where('status', 'active');

        if ($month === 2 && $day === 28 && ! $this->isLeapYear((int) $date->format('Y'))) {
            return $query->where('birth_month', 2)->whereIn('birth_day', [28, 29]);
        }

        return $query->where('birth_month', $month)->where('birth_day', $day);
    }

    private function isLeapYear(int $year): bool
    {
        return ($year % 4 === 0 && $year % 100 !== 0) || $year % 400 === 0;
    }

    /** Âge atteint ce jour-là, ou null si l'année n'a pas été donnée. */
    public function ageOn(?\DateTimeInterface $date = null): ?int
    {
        if (! $this->birth_year || ! $this->hasBirthday()) {
            return null;
        }

        return (int) ($date ? $date->format('Y') : now()->year) - (int) $this->birth_year;
    }

    /** « 12 mars », ou « 12 mars 1990 » quand l'année est connue. */
    public function birthdayLabel(): ?string
    {
        if (! $this->hasBirthday()) {
            return null;
        }

        $date = Carbon::createFromDate($this->birth_year ?: 2000, $this->birth_month, $this->birth_day);

        return $this->birth_year
            ? $date->translatedFormat('j F Y')
            : $date->translatedFormat('j F');
    }

    /**
     * A accepté la charte dans sa version en vigueur.
     *
     * Une acceptation portant sur une version antérieure ne vaut pas pour la
     * version actuelle : la membre doit être réinvitée à en prendre connaissance.
     */
    public function hasAcceptedCurrentRules(): bool
    {
        return $this->rules_accepted_at !== null
            && $this->rules_version === CommunityRules::VERSION;
    }

    /** Enregistre l'acceptation de la charte dans sa version courante. */
    public function acceptCommunityRules(): void
    {
        $this->forceFill([
            'rules_accepted_at' => now(),
            'rules_version' => CommunityRules::VERSION,
        ])->save();
    }

    /**
     * Le lien de réinitialisation doit mener à l’espace membre, pas au
     * back-office : la notification par défaut de Laravel pointe vers la route
     * d’administration, qui refuserait l’adresse.
     */
    public function sendPasswordResetNotification(#[SensitiveParameter] $token): void
    {
        $url = route('member.password.reset', ['token' => $token, 'email' => $this->email]);

        Mail::to($this->email)->queue(new MemberPasswordSetupMail($this, $url, $this->password === null));
    }

    /**
     * Une membre peut se connecter si son adhésion est active, non expirée, et
     * qu'elle a défini un mot de passe. Le contrôle vit ici plutôt que dans le
     * fournisseur d'authentification, pour pouvoir expliquer le refus.
     */
    public function canAccessPortal(): bool
    {
        return $this->status === 'active' && ! $this->isExpired() && $this->password !== null;
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'email', 'email');
    }

    /** Membres acceptant le marketing (non opposés au sens RGPD). */
    public function scopeMarketable(Builder $query): Builder
    {
        return $query->whereNull('marketing_opt_out_at');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Membres dont la photo peut illustrer publiquement la communauté :
     * adhésion active, photo fournie, et publication non retirée par l'administration.
     * Les plus récemment arrivées d'abord, pour que la galerie vive au fil des adhésions.
     */
    public function scopeInPublicGallery(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where('show_in_gallery', true)
            ->whereNotNull('photo')
            ->where('photo', '!=', '')
            ->orderByDesc('joined_at')
            ->orderByDesc('id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function hasOptedOutOfMarketing(): bool
    {
        return $this->marketing_opt_out_at !== null;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /** URL publique de vérification de la carte (encodée dans le QR de la carte). */
    public function getVerificationUrlAttribute(): string
    {
        return route('members.verify', $this->verification_token ?? 'invalid');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /** Génère un matricule unique, lisible et séquentiel : FSL-{PRM|GLD|STD}-{année}-{NNNNN}. */
    public static function generateNumber(string $type): string
    {
        $prefix = ['premium' => 'PRM', 'gold' => 'GLD', 'standard' => 'STD'][$type] ?? 'STD';
        $year = now()->format('Y');

        do {
            $number = sprintf('FSL-%s-%s-%05d', $prefix, $year, random_int(1, 99999));
        } while (static::withTrashed()->where('member_number', $number)->exists());

        return $number;
    }

    public function getBadgeColorAttribute(): string
    {
        return match ($this->type) {
            'premium' => '#D91E6E',
            'gold' => '#C9A84C',
            default => '#6B7280',
        };
    }

    public function getPrivilegesListAttribute(): array
    {
        return match ($this->type) {
            'premium' => [
                'Accès VIP à tous les événements',
                'Réduction de 30% sur tous les events payants',
                'Ressources exclusives & formations premium',
                'Accès prioritaire aux panels et ateliers',
                'Badge Premium + carte personnalisée',
            ],
            'gold' => [
                'Réduction de 15% sur les events payants',
                'Accès prioritaire aux inscriptions',
                'Ressources formations avancées',
                'Badge Gold + carte personnalisée',
            ],
            default => [
                'Accès aux événements publics',
                'Newsletter & actualités FSL',
                'Badge Standard + carte personnalisée',
            ],
        };
    }
}
