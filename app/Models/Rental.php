<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Rental extends Model
{
    protected $guarded = [];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function payments()
    {
        return $this->hasMany(RentalPayment::class);
    }


    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function addons()
    {
        return $this->belongsToMany(Addon::class, 'rental_addons')
            ->withPivot('quantity', 'total_price')
            ->withTimestamps();
    }

    protected function lateFeePaymentProof(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? url('/storage/late_fee_proofs/' . $value) : null
        );
    }
}
