<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TripLegVehicle extends Model
{
    protected $table = 'triplegvehicles';

    protected $fillable = [
        'triplegid',
        'vehicleid',
        'tripvehicleid',
        'vehiclerole',
        'includedinfuelcalculation',
        'fuelconsumptionoverridelper100km',
        'sortorder',
    ];

    protected $casts = [
        'includedinfuelcalculation' => 'boolean',
        'fuelconsumptionoverridelper100km' => 'decimal:4',
    ];

    public function tripLeg()
    {
        return $this->belongsTo(TripLeg::class, 'triplegid');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicleid');
    }

    public function tripVehicle()
{
    return $this->belongsTo(TripVehicle::class, 'tripvehicleid');
}
}