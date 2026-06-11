<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaitingList extends Model
{
    protected $table = 'waiting_list';

    protected $fillable = ['event_id', 'first_name', 'last_name', 'email', 'phone', 'notified'];

    protected $casts = ['notified' => 'boolean'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }
}
