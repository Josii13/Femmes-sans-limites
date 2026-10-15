<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Un mot laissé à une membre le jour de son anniversaire.
 */
class BirthdayWish extends Model
{
    protected $fillable = ['member_id', 'author_name', 'message', 'hidden_at', 'ip'];

    protected function casts(): array
    {
        return ['hidden_at' => 'datetime'];
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->whereNull('hidden_at');
    }
}
