<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Member extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'member_number', 'name', 'email', 'phone', 'motivation', 'profession',
        'country', 'city', 'photo', 'type', 'status', 'card_path', 'verification_token',
        'marketing_opt_out_at', 'joined_at', 'expires_at', 'renewal_reminded_at',
    ];

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
        ];
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
