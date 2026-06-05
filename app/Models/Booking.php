<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'owner_id', 'dog_id', 'type', 'subtype', 'is_regular', 'start_date', 'end_date',
        'frequency', 'pet_taxi', 'notes', 'status', 'staff_notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'pet_taxi'   => 'boolean',
        'is_regular' => 'boolean',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function dog(): BelongsTo
    {
        return $this->belongsTo(Dog::class);
    }

    public function payment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
