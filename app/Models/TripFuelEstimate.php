<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TripFuelEstimate extends Model
{
    protected $table = 'tripfuelestimates';

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
    ];

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'tripid');
    }

    public function tripLeg(): BelongsTo
    {
        return $this->belongsTo(TripLeg::class, 'triplegid');
    }

    public function fuelStop(): BelongsTo
    {
        return $this->belongsTo(FuelStop::class, 'fuelstopid');
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'placeid');
    }

    public function sourceObservation(): BelongsTo
    {
        return $this->belongsTo(FuelPriceObservation::class, 'sourceobservationid');
    }

    public function sourceSuggestions(): HasMany
    {
        return $this->hasMany(TripLegSuggestion::class, 'converted_to_trip_fuel_estimate_id');
    }
}