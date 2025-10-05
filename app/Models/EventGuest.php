<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventGuest extends Model
{
    //
    protected $fillable = [
        'event_id',
        'role', // chief_guest, chairman, guest
        'name',
        'designation',
        'organization',
        'contact',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

}
