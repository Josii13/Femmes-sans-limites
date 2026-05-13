<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    protected $fillable = ['email', 'name', 'token'];

    protected static function booted(): void
    {
        static::creating(function ($sub) {
            $sub->token = Str::random(64);
        });
    }
}
