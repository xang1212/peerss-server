<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $guarded = [];
    // protected $fillable=[
    //     'name',
    //     'category',
    //     'desc',
    //     'qty',
    //     'price',
    //     'broken_price',
    //     'unit',
    //     'images'
    // ];

    public function rental_details()
    {
        return $this->belongsTo(RentalDetail::class);
    }
}
