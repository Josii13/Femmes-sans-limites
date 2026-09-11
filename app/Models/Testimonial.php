<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id', 'name', 'role', 'quote', 'photo', 'is_published', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    /** Membre citée, quand le témoignage provient de la communauté. */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->orderBy('sort_order')
            ->orderByDesc('id');
    }

    /**
     * Photo à afficher : celle téléversée pour le témoignage, sinon celle de la
     * membre liée. Retourne null s'il n'y en a aucune — la vue affiche alors
     * l'initiale, plutôt qu'une image cassée.
     */
    public function photoUrl(): ?string
    {
        $path = $this->photo ?: $this->member?->photo;

        return $path ? Storage::disk('public')->url($path) : null;
    }

    public function initial(): string
    {
        return mb_strtoupper(mb_substr(trim($this->name), 0, 1)) ?: '?';
    }
}
