<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $table = 'vehicles';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'vehiclename',
        'vehicletype',
        'registrationnumber',
        'make',
        'model',
        'notes',
        'isactive',
    ];

    public function tripLegs()
    {
        return $this->belongsToMany(TripLeg::class, 'triplegvehicles', 'vehicleid', 'triplegid')
            ->withPivot(['vehiclerole', 'sortorder'])
            ->withTimestamps();
    }
}