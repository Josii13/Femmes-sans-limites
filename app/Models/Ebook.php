<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Ebook extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'category', 'description', 'author_note',
        'image', 'file_path', 'price', 'promo_price', 'promo_starts_at', 'promo_ends_at',
        'currency', 'cta_label', 'cta_url',
        'status', 'sort_order', 'newsletter_sent_at',
    ];

    protected $casts = [
        'newsletter_sent_at' => 'datetime',
        'price' => 'decimal:2',
        'promo_price' => 'decimal:2',
        'promo_starts_at' => 'datetime',
        'promo_ends_at' => 'datetime',
    ];

    /** Vendable sur le site : a un prix > 0 ET un fichier PDF à livrer. */
    public function isPurchasable(): bool
    {
        return $this->price !== null && (float) $this->price > 0 && ! empty($this->file_path);
    }

    /**
     * Une promotion est en cours si un prix promotionnel valable est défini et que
     * l'instant présent tombe dans la fenêtre. Une borne absente est ouverte : sans
     * date de début la promo vaut immédiatement, sans date de fin elle ne s'arrête
     * pas d'elle-même. À l'échéance, plus rien n'est actif et `price` reprend effet
     * sans qu'aucune tâche planifiée n'ait à intervenir.
     */
    public function hasActivePromo(): bool
    {
        if ($this->promo_price === null || (float) $this->promo_price <= 0) {
            return false;
        }

        // Un « prix promotionnel » supérieur ou égal au prix normal n'en est pas un.
        if ($this->price === null || (float) $this->promo_price >= (float) $this->price) {
            return false;
        }

        if ($this->promo_starts_at && $this->promo_starts_at->isFuture()) {
            return false;
        }

        if ($this->promo_ends_at && $this->promo_ends_at->isPast()) {
            return false;
        }

        return true;
    }

    /** Promotion enregistrée dont la date de début n'est pas encore arrivée. */
    public function hasScheduledPromo(): bool
    {
        return $this->promo_price !== null
            && (float) $this->promo_price > 0
            && $this->promo_starts_at !== null
            && $this->promo_starts_at->isFuture();
    }

    /** Promotion enregistrée dont la date de fin est passée. */
    public function hasExpiredPromo(): bool
    {
        return $this->promo_price !== null
            && $this->promo_ends_at !== null
            && $this->promo_ends_at->isPast();
    }

    /**
     * Prix à afficher ET à encaisser. C'est la seule source de vérité du montant :
     * le tunnel de paiement doit l'utiliser, jamais `price` directement, sinon une
     * promotion serait annoncée puis facturée au prix normal.
     */
    public function effectivePrice(): ?float
    {
        if ($this->price === null) {
            return null;
        }

        return (float) ($this->hasActivePromo() ? $this->promo_price : $this->price);
    }

    /** Remise en pourcentage, arrondie, pour l'étiquette « -30 % ». */
    public function promoDiscountPercent(): ?int
    {
        if (! $this->hasActivePromo()) {
            return null;
        }

        return (int) round((1 - ((float) $this->promo_price / (float) $this->price)) * 100);
    }

    /** Ebooks dont la promotion est en cours à cet instant. */
    public function scopeOnPromo($query)
    {
        return $query->whereNotNull('promo_price')
            ->where('promo_price', '>', 0)
            ->whereColumn('promo_price', '<', 'price')
            ->where(fn ($q) => $q->whereNull('promo_starts_at')->orWhere('promo_starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('promo_ends_at')->orWhere('promo_ends_at', '>', now()));
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    protected static function booted(): void
    {
        static::creating(function ($ebook) {
            if (empty($ebook->slug)) {
                do {
                    $slug = Str::slug($ebook->title).'-'.Str::random(5);
                } while (static::withTrashed()->where('slug', $slug)->exists());
                $ebook->slug = $slug;
            }
        });
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')->orderBy('sort_order')->orderBy('created_at', 'desc');
    }
}
