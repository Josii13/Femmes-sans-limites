<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'name', 'description', 'price', 'currency', 'duration_months', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /** Un niveau payant : seuls ceux-là passent par le tunnel de paiement. */
    public function isPaid(): bool
    {
        return $this->price !== null && (float) $this->price > 0;
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    /** Avantages du niveau, repris de la définition portée par le membre. */
    public function privileges(): array
    {
        $member = new Member(['type' => $this->type]);

        return $member->privileges_list;
    }
}
