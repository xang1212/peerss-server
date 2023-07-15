<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $guarded = [];

    function package_equipment()
    {
        return $this->hasMany(PackageEquipment::class);
    }

    function package_food()
    {
        return $this->hasMany(PackageFood::class);
    }
}
