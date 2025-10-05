<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $guarded = [];

    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
