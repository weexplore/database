<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripLegSuggestion extends Model
{
    protected $table = 'trip_leg_suggestions';

    protected $fillable = [
        'trip_id',
        'trip_leg_id',
        'run_id',
        'profile_id',
        'suggestion_type',
        'place_id',
        'destination_id',
        'destination_item_id',
        'fuel_stop_id',
        'title',
        'summary',
        'distance_from_corridor_km',
        'distance_from_start_km',
        'distance_from_end_km',
        'detour_distance_km',
        'detour_minutes',
        'rank_score',
        'match_reason',
        'is_shortlisted',
        'is_dismissed',
        'dismissed_reason',
        'converted_to_trip_item_id',
        'converted_to_trip_stay_id',
        'converted_to_trip_fuel_estimate_id',
        'converted_to_booking_id',
        'converted_at',
    ];

    protected $casts = [
        'distance_from_corridor_km' => 'decimal:1',
        'distance_from_start_km' => 'decimal:1',
        'distance_from_end_km' => 'decimal:1',
        'detour_distance_km' => 'decimal:1',
        'detour_minutes' => 'integer',
        'rank_score' => 'decimal:2',
        'is_shortlisted' => 'boolean',
        'is_dismissed' => 'boolean',
        'converted_at' => 'datetime',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function tripLeg(): BelongsTo
    {
        return $this->belongsTo(TripLeg::class, 'trip_leg_id');
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(TripLegSearchRun::class, 'run_id');
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(TripLegSearchProfile::class, 'profile_id');
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function destinationItem(): BelongsTo
    {
        return $this->belongsTo(DestinationItem::class, 'destination_item_id');
    }

    public function fuelStop(): BelongsTo
    {
        return $this->belongsTo(FuelStop::class, 'fuel_stop_id');
    }

    public function convertedTripItem(): BelongsTo
    {
        return $this->belongsTo(TripItem::class, 'converted_to_trip_item_id');
    }

    public function convertedTripStay(): BelongsTo
    {
        return $this->belongsTo(TripStay::class, 'converted_to_trip_stay_id');
    }

    public function convertedTripFuelEstimate(): BelongsTo
    {
        return $this->belongsTo(TripFuelEstimate::class, 'converted_to_trip_fuel_estimate_id');
    }

    public function convertedBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'converted_to_booking_id');
    }
}