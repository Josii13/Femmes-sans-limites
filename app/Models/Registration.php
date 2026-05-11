<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    protected $fillable = [
        'event_id', 'first_name', 'last_name', 'email', 'phone',
        'status', 'qr_token', 'qr_code_path',
        'payment_sent_at', 'paid_at', 'attended_at',
    ];

    protected $casts = [
        'payment_sent_at' => 'datetime',
        'paid_at'         => 'datetime',
        'attended_at'     => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
