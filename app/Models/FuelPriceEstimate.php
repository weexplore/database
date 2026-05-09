<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuelPriceEstimate extends Model
{
    protected $table = 'tripfuelestimates';

    public const CREATED_AT = 'createdat';
    public const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'tripid',
        'triplegid',
        'fuelstopid',
        'placeid',
        'estimatedate',
        'fueltype',
        'expectedpriceperlitre',
        'estimateddistancekm',
        'estimatedlitres',
        'estimatedtotalcost',
        'sourceobservationid',
        'notes',
    ];

    protected $casts = [
        'estimatedate' => 'date',
        'expectedpriceperlitre' => 'decimal:4',
        'estimateddistancekm' => 'decimal:1',
        'estimatedlitres' => 'decimal:3',
        'estimatedtotalcost' => 'decimal:2',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class, 'tripid');
    }

    public function tripLeg()
    {
        return $this->belongsTo(TripLeg::class, 'triplegid');
    }

    public function fuelStop()
    {
        return $this->belongsTo(FuelStop::class, 'fuelstopid');
    }

    public function place()
    {
        return $this->belongsTo(Place::class, 'placeid');
    }

    public function sourceObservation()
    {
        return $this->belongsTo(FuelPriceObservation::class, 'sourceobservationid');
    }

    public function getFuelTypeLabelAttribute(): string
    {
        return config('fuel.fuel_types')[$this->fueltype] ?? $this->fueltype;
    }
}