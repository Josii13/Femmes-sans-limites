<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Event extends Model
{
    use SoftDeletes;

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

    public function getRegistrationOpenAttribute(): bool
    {
        if ($this->is_sold_out) {
            return false;
        }
        if ($this->registration_closes_at && now()->gt($this->registration_closes_at)) {
            return false;
        }

        return true;
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

    public function getSpotsLeftAttribute(): ?int
    {
        if (! $this->capacity) {
            return null;
        }

        return max(0, $this->capacity - $this->registrations()->whereNotIn('status', ['cancelled'])->count());
    }

    public function getIsSoldOutAttribute(): bool
    {
        if (! $this->capacity) {
            return false;
        }

        return $this->spots_left === 0;
    }
}
