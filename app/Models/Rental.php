<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rental extends Model
{
    use HasFactory;

    protected $guarded = [];

    function rental_detail()
    {
        return $this->hasMany(RentalDetail::class);
    }

    public function users()
    {
        return $this->belongsTo(User::class);
    }
    
}
