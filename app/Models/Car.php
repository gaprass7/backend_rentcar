<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Car extends Model
{
    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }


    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn($image) => $image ? url('/storage/cars/' . $image) : null,
        );
    }
}
