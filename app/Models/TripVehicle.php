<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripVehicle extends Model
{

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';
    protected $table = 'tripvehicles';

    protected $fillable = [
        'tripid',
        'vehicleid',
        'vehiclerole',
        'sortorder',
        'isdefaultforlegs',
        'notes',
    ];

    protected $casts = [
        'isdefaultforlegs' => 'boolean',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class, 'tripid');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicleid');
    }

    public function tripLegVehicles()
    {
        return $this->hasMany(TripLegVehicle::class, 'tripvehicleid');
    }
}
