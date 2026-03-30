<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = ['dog_id', 'date', 'type', 'notes'];

    protected $casts = ['date' => 'date'];

    public function dog(): BelongsTo
    {
        return $this->belongsTo(Dog::class);
    }
}
