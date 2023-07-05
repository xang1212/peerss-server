<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function rentals()
    {
        return $this->belongsTo(Rental::class);
    }
}
