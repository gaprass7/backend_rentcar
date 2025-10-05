<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class SiteSetting extends Model
{
    protected $guarded = [];

    // Accessor untuk atribut logo
    protected function logo(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? url('/storage/logos/' . $value) : null,
        );
    }
}
