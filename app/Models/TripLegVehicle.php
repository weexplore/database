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
        'vehiclerole',
        'sortorder',
    ];

    public function tripLeg()
    {
        return $this->belongsTo(TripLeg::class, 'triplegid');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicleid');
    }
}