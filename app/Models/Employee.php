<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    //
    protected $fillable = ['name', 'user_id', 'designation_id', 'contact_info', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function assignments() : HasMany
    {
        return $this->hasMany(EventAssignment::class);
    }

    public function notifications() : HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
