<?php

namespace App\Models;

use App\Mail\MemberPasswordSetupMail;
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
            'show_in_gallery' => 'boolean',
        ];
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
