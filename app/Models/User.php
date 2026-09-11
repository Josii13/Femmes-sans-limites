<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'is_admin', 'role'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Rôles du back-office.
     *
     * owner  : tout, y compris la gestion des comptes d'administration
     * admin  : tout sauf la gestion des comptes
     * editor : contenu éditorial seulement — ni membres, ni paiements, ni campagnes
     */
    public const ROLE_OWNER = 'owner';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_EDITOR = 'editor';

    public const ROLES = [self::ROLE_OWNER, self::ROLE_ADMIN, self::ROLE_EDITOR];

    public const ROLE_LABELS = [
        self::ROLE_OWNER => 'Propriétaire',
        self::ROLE_ADMIN => 'Administratrice',
        self::ROLE_EDITOR => 'Éditrice',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
            // Chiffrés au repos : un accès en lecture à la base ne suffit pas à
            // reconstituer les codes à usage unique.
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
        ];
    }

    /**
     * Détermine si l'utilisateur dispose des droits d'administration du back-office.
     */
    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    /** Peut gérer membres, paiements, événements et campagnes. */
    public function canManageOperations(): bool
    {
        return in_array($this->role, [self::ROLE_OWNER, self::ROLE_ADMIN], true);
    }

    public function roleLabel(): string
    {
        return self::ROLE_LABELS[$this->role] ?? 'Administratrice';
    }

    // ── Double authentification ─────────────────────────────────

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_secret !== null && $this->two_factor_confirmed_at !== null;
    }

    /**
     * Consomme un code de secours. Retourne false si le code est inconnu.
     * Un code utilisé est retiré : c'est tout l'intérêt d'un usage unique.
     */
    public function consumeRecoveryCode(string $code): bool
    {
        $codes = (array) ($this->two_factor_recovery_codes ?? []);
        $normalised = strtoupper(trim($code));

        $index = array_search($normalised, array_map('strtoupper', $codes), true);

        if ($index === false) {
            return false;
        }

        unset($codes[$index]);
        $this->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();

        return true;
    }
}
