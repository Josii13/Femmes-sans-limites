<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CampaignRecipient extends Model
{
    protected $fillable = [
        'campaign_id', 'member_id', 'email', 'name', 'token',
        'sent_at', 'failed_at', 'opened_at', 'open_count',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
        'opened_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($r) {
            $r->token = $r->token ?: Str::random(64);
        });
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
