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
        'fueltype',
        'defaultfuelconsumptionlper100km',
        'fueltankcapacitylitres',
        'notes',
        'isactive',
    ];

    protected $casts = [
        'defaultfuelconsumptionlper100km' => 'decimal:4',
        'fueltankcapacitylitres' => 'decimal:2',
        'isactive' => 'boolean',
    ];

    public function tripLegs()
    {
        return $this->belongsToMany(TripLeg::class, 'triplegvehicles', 'vehicleid', 'triplegid')
            ->withPivot(['vehiclerole', 'sortorder'])
            ->withTimestamps();
    }

    public function tripVehicles()
    {
        return $this->hasMany(TripVehicle::class, 'vehicleid');
    }

    public function tripLegVehicles()
    {
        return $this->hasMany(TripLegVehicle::class, 'vehicleid');
    }
}