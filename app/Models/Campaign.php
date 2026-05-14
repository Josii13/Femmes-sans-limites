<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    protected $fillable = [
        'title','subject','body','image','cta_label','cta_url',
        'type','target_type','target_member_id','target_member_ids',
        'status','scheduled_at','sent_at','sent_count','open_count',
    ];

    protected $casts = [
        'target_member_ids' => 'array',
        'scheduled_at'      => 'datetime',
        'sent_at'           => 'datetime',
    ];

    public function recipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class);
    }

    public function targetMember()
    {
        return $this->belongsTo(Member::class, 'target_member_id');
    }

    public function getOpenRateAttribute(): float
    {
        if ($this->sent_count === 0) return 0;
        return round(($this->open_count / $this->sent_count) * 100, 1);
    }

    public function getTargetLabelAttribute(): string
    {
        return match($this->target_type) {
            'all'      => 'Tous les membres',
            'standard' => 'Membres Standard',
            'gold'     => 'Membres Gold',
            'premium'  => 'Membres Premium',
            'custom'   => 'Sélection manuelle',
            'single'   => 'Membre unique',
            default    => $this->target_type,
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'text'            => 'Texte',
            'text_image'      => 'Texte + Image',
            'text_cta'        => 'Texte + CTA',
            'text_image_cta'  => 'Texte + Image + CTA',
            default           => $this->type,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft'     => '#6B7280',
            'scheduled' => '#C9A84C',
            'sending'   => '#3B82F6',
            'sent'      => '#10B981',
            default     => '#6B7280',
        };
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }
}
