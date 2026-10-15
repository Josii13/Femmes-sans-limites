<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Trace d'un email sorti de l'application.
 */
class MailLog extends Model
{
    protected $fillable = [
        'mailable', 'subject', 'recipient', 'recipients_count',
        'status', 'error', 'is_test', 'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'is_test' => 'boolean',
            'sent_at' => 'datetime',
        ];
    }

    /**
     * Délai au-delà duquel un envoi sans confirmation est tenu pour échoué.
     *
     * Un envoi SMTP qui aboutit déclenche MessageSent dans la seconde. Passé
     * dix minutes, c'est que le transport a levé une exception — souvent
     * avalée par le try/catch de l'appelant.
     */
    public const STALE_AFTER_MINUTES = 10;

    /** Envois réels : ceux du banc d'essai ne disent rien de la santé du système. */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    /** Partis sans confirmation, et trop vieux pour qu'elle arrive encore. */
    public function scopeStale(Builder $query): Builder
    {
        return $query->where('status', 'sending')
            ->where('created_at', '<', now()->subMinutes(self::STALE_AFTER_MINUTES));
    }

    public function isStale(): bool
    {
        return $this->status === 'sending'
            && $this->created_at?->lt(now()->subMinutes(self::STALE_AFTER_MINUTES));
    }

    /** Nom court et lisible : « QrCodeMail » plutôt que le chemin complet. */
    public function typeLabel(): string
    {
        return $this->mailable ? class_basename($this->mailable) : 'Inconnu';
    }

    public function statusLabel(): string
    {
        return match (true) {
            $this->status === 'sent' => 'Envoyé',
            $this->status === 'failed' => 'Échec',
            $this->isStale() => 'Sans confirmation',
            default => 'En cours',
        };
    }
}
