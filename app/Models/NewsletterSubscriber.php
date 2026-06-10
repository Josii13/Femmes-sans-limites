<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    protected $fillable = [
        'email', 'name', 'token',
        'confirmed_at', 'unsubscribed_at', 'consent_ip', 'consent_at',
    ];

    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
            'consent_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($sub) {
            $sub->token = $sub->token ?: Str::random(64);
        });
    }

    /** Abonnés ayant confirmé leur email et non désinscrits : seuls destinataires légitimes. */
    public function scopeMailable(Builder $query): Builder
    {
        return $query->whereNotNull('confirmed_at')->whereNull('unsubscribed_at');
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }

    public function isUnsubscribed(): bool
    {
        return $this->unsubscribed_at !== null;
    }
}
