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

    function equipment_broken()
    {
        return $this->hasMany(EquipmentBroken::class);
    }

    public function users()
    {
        return $this->belongsTo(User::class);
    }
    
}
