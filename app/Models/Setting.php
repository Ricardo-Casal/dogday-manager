<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'label', 'value'];

    protected $casts = ['value' => 'decimal:2'];

    public static function get(string $key): ?string
    {
        return static::where('key', $key)->value('value');
    }
}
