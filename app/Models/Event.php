<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    //
    protected $fillable = ['title', 'date', 'start_time', 'end_time', 'location', 'status'];

    public function guests(): HasMany
    {
        return $this->hasMany(EventGuest::class);
    }

    public function eventDuties(): HasMany
    {
        return $this->hasMany(EventDuty::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
