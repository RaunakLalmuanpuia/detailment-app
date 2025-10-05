<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventDuty extends Model
{
    //
    protected $fillable = ['event_id', 'duty_id'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function duty()
    {
        return $this->belongsTo(Duty::class);
    }

    public function assignments()
    {
        return $this->hasMany(EventAssignment::class);
    }
}
