<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    // protected $guarded = [];
    protected $fillable=[
        'name',
        'category',
        'desc',
        'qty',
        'price',
        'broken_price',
        'unit',
        'images'
    ];

    // /**
    //  * The attributes that should be cast.
    //  *
    //  * @var array
    //  */
    // protected $casts = [
    //     'images' => 'array',
    // ];

    public function rental_details()
    {
        return $this->belongsTo(RentalDetail::class);
    }

    public function equipment_brokens()
    {
        return $this->belongsTo(EquipmentBroken::class);
    }

    public function package_equipments()
    {
        return $this->belongsTo(PackageEquipment::class);
    }
}
