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
        'image', 'cta_label', 'cta_url', 'status', 'sort_order', 'newsletter_sent_at',
    ];

    protected $casts = [
        'newsletter_sent_at' => 'datetime',
    ];

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
