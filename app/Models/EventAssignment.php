<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventAssignment extends Model
{
    //
    protected $fillable = ['event_duty_id', 'employee_id', 'status'];

    public function eventDuty(): BelongsTo
    {
        return $this->belongsTo(EventDuty::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
