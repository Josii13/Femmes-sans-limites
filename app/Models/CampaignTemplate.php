<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignTemplate extends Model
{
    protected $fillable = [
        'name', 'subject', 'type', 'body', 'cta_label', 'cta_url',
    ];

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'text'           => 'Texte',
            'text_image'     => 'Texte + Image',
            'text_cta'       => 'Texte + CTA',
            'text_image_cta' => 'Texte + Image + CTA',
            default          => $this->type,
        };
    }

    public function getTypeBadgeColorAttribute(): string
    {
        return match($this->type) {
            'text'           => '#6B7280',
            'text_image'     => '#3B82F6',
            'text_cta'       => '#C9A84C',
            'text_image_cta' => '#D91E6E',
            default          => '#6B7280',
        };
    }
}
