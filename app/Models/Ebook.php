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
        'image', 'file_path', 'price', 'currency', 'cta_label', 'cta_url',
        'status', 'sort_order', 'newsletter_sent_at',
    ];

    protected $casts = [
        'newsletter_sent_at' => 'datetime',
        'price' => 'decimal:2',
    ];

    /** Vendable sur le site : a un prix > 0 ET un fichier PDF à livrer. */
    public function isPurchasable(): bool
    {
        return $this->price !== null && (float) $this->price > 0 && ! empty($this->file_path);
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
