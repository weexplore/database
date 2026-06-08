<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelStop extends Model
{
    protected $table = 'fuelstops';

    public const CREATED_AT = 'createdat';
    public const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'placeid',
        'destinationid',
        'addressline1',
        'addressline2',
        'addressline3',
        'postcode',
        'latitude',
        'longitude',
        'website',
        'telephone',
        'internetsearch',
        'stopname',
        'brandname',
        'fueltypesavailable',
        'hashighflowdiesel',
        'hasadblue',
        'hascarwash',
        'hasairwater',
        'caravanaccessnotes',
        'openingnotes',
        'generalnotes',
        'isactive',
    ];

    protected $casts = [
        'hashighflowdiesel' => 'boolean',
        'hasadblue' => 'boolean',
        'hascarwash' => 'boolean',
        'hasairwater' => 'boolean',
        'isactive' => 'boolean',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'placeid');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'destinationid');
    }

    public function fuelPriceObservations(): HasMany
    {
        return $this->hasMany(FuelPriceObservation::class, 'fuelstopid');
    }

    public function tripFuelPurchases(): HasMany
    {
        return $this->hasMany(FuelPricePurchase::class, 'fuelstopid');
    }

    public function getFuelTypesArrayAttribute(): array
    {
        if (blank($this->fueltypesavailable)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $this->fueltypesavailable))));
    }
    public function tripLegSuggestions()
    {
        return $this->hasMany(TripLegSuggestion::class, 'fuel_stop_id');
    }
}