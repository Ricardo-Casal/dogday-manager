<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dog extends Model
{
    protected $fillable = ['owner_id', 'name', 'breed', 'birthdate', 'notes'];

    protected $casts = ['birthdate' => 'date'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
