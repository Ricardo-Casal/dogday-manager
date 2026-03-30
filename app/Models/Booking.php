<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'owner_id', 'dog_id', 'type', 'start_date', 'end_date',
        'frequency', 'pet_taxi', 'notes', 'status', 'staff_notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'pet_taxi' => 'boolean',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function dog(): BelongsTo
    {
        return $this->belongsTo(Dog::class);
    }
}
