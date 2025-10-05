<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class PaymentMethod extends Model
{
    protected $guarded = [];

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn($image) => url('/storage/payment_methods/' . $image),
        );
    }
}
