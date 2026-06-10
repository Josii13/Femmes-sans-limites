<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SiteImage extends Model
{
    protected $fillable = ['key', 'label', 'page', 'default_path', 'custom_path'];

    public function getUrlAttribute(): string
    {
        if ($this->custom_path) {
            return Storage::url($this->custom_path);
        }

        return asset('images/'.$this->default_path);
    }

    public function isCustomized(): bool
    {
        return ! empty($this->custom_path);
    }
}
