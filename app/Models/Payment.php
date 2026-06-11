<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    protected $fillable = [
        'provider', 'reference', 'provider_reference', 'status', 'amount', 'currency',
        'customer_name', 'customer_email', 'customer_phone',
        'payable_type', 'payable_id', 'checkout_url', 'metadata', 'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'paid_at' => 'datetime',
    ];

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isPaid(): bool
    {
        return in_array($this->status, ['completed', 'paid'], true);
    }

    /** Normalise les statuts GeniusPay en booléen « payé ». */
    public static function isSuccessfulStatus(?string $status): bool
    {
        return in_array($status, ['completed', 'paid', 'success', 'successful'], true);
    }
}
