<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'description', 'short_description', 'image',
        'event_date', 'location', 'city', 'capacity', 'registration_closes_at',
        'is_paid', 'price', 'currency', 'payment_link', 'status',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'registration_closes_at' => 'datetime',
        'is_paid' => 'boolean',
        'price' => 'decimal:2',
    ];

    /**
     * Les inscriptions sont-elles ouvertes ?
     *
     * La date de l'événement fait partie du test. Sans elle, un événement passé
     * sans date de clôture restait « ouvert » : la fiche affichait « Réserver »
     * et le formulaire s'ouvrait, pour n'être refusé qu'au moment de l'envoi.
     * Devenu très visible depuis que les éditions passées figurent en archive.
     */
    public function getRegistrationOpenAttribute(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        if ($this->hasTakenPlace()) {
            return false;
        }

        if ($this->is_sold_out) {
            return false;
        }

        if ($this->registration_closes_at && now()->gt($this->registration_closes_at)) {
            return false;
        }

        return true;
    }

    /** L'événement a déjà eu lieu : plus rien ne doit s'y rattacher. */
    public function hasTakenPlace(): bool
    {
        return $this->event_date !== null && $this->event_date->isPast();
    }

    /**
     * Événement réellement payant : « payant » coché ET un tarif strictement
     * positif. Un tarif à zéro ferait basculer l'inscription dans le circuit
     * gratuit — place confirmée sans encaissement.
     */
    public function isPaidEvent(): bool
    {
        return (bool) $this->is_paid && $this->price !== null && (float) $this->price > 0;
    }

    protected static function booted(): void
    {
        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->title).'-'.Str::random(5);
            }
        });
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    public function waitingList()
    {
        return $this->hasMany(WaitingList::class);
    }

    private ?int $memoActiveCount = null;

    /**
     * Nombre d'inscriptions actives (hors annulées). Réutilise le compteur
     * éventuellement préchargé via withCount('... as active_registrations_count'),
     * et mémoïse le résultat pour éviter de recompter à chaque accès dans une vue.
     */
    public function activeRegistrationsCount(): int
    {
        if ($this->memoActiveCount !== null) {
            return $this->memoActiveCount;
        }

        if (isset($this->attributes['active_registrations_count'])) {
            return $this->memoActiveCount = (int) $this->attributes['active_registrations_count'];
        }

        return $this->memoActiveCount = $this->registrations()->whereNotIn('status', ['cancelled'])->count();
    }

    public function getSpotsLeftAttribute(): ?int
    {
        if (! $this->capacity) {
            return null;
        }

        return max(0, $this->capacity - $this->activeRegistrationsCount());
    }

    public function getIsSoldOutAttribute(): bool
    {
        if (! $this->capacity) {
            return false;
        }

        return $this->spots_left === 0;
    }

    public function scopeWithActiveRegistrationsCount($query)
    {
        return $query->withCount(['registrations as active_registrations_count' => fn ($q) => $q->whereNotIn('status', ['cancelled'])]);
    }
}
