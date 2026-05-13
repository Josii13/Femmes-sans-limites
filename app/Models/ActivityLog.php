<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'action', 'subject_type', 'subject_id', 'subject_label', 'meta', 'ip'];

    protected $casts = ['meta' => 'array'];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public static function record(string $action, ?object $subject = null, array $meta = []): void
    {
        static::create([
            'user_id'       => auth()->id(),
            'action'        => $action,
            'subject_type'  => $subject ? class_basename($subject) : null,
            'subject_id'    => $subject?->getKey(),
            'subject_label' => $subject?->name ?? $subject?->title ?? $subject?->email ?? null,
            'meta'          => $meta ?: null,
            'ip'            => request()->ip(),
        ]);
    }
}
