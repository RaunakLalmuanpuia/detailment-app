<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Duty extends Model
{
    //
    protected $fillable = ['name', 'min_required'];

    public function eventDuties(): HasMany
    {
        return $this->hasMany(EventDuty::class);
    }
}
