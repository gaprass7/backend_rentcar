<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class RentalPayment extends Model
{
    protected $guarded = [];

    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }

    protected function paymentProof(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? url('/storage/payment_proofs/' . $value) : null
        );
    }
}
