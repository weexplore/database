<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuelPriceObservation extends Model
{
    protected $table = 'fuelpriceobservations';

    public const CREATED_AT = 'createdat';
    public const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'fuelstopid',
        'observedon',
        'fueltype',
        'priceperlitre',
        'pricesource',
        'tripid',
        'observationnotes',
    ];

    protected $casts = [
        'observedon' => 'date',
        'priceperlitre' => 'decimal:4',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];

    public function fuelStop()
    {
        return $this->belongsTo(FuelStop::class, 'fuelstopid');
    }

    public function getFuelTypeLabelAttribute(): string
    {
        return config('fuel.fuel_types')[$this->fueltype] ?? $this->fueltype;
    }

    public function getPriceSourceLabelAttribute(): string
    {
        return static::priceSourceOptions()[$this->pricesource] ?? ($this->pricesource ?: '—');
    }
        public static function fuelTypeOptions(): array
    {
        return [
            'diesel' => 'Diesel',
            'premiumdiesel' => 'Premium Diesel',
            'unleaded91' => 'Unleaded 91',
            'unleaded95' => 'Unleaded 95',
            'unleaded98' => 'Unleaded 98',
            'e10' => 'E10',
            'lpg' => 'LPG',
            'adblue' => 'AdBlue',
        ];
    }

    public static function priceSourceOptions(): array
    {
        return [
            'actualpurchase' => 'Actual Purchase',
            'signboard' => 'Signboard',
            'website' => 'Website',
            'imported' => 'Imported',
            'estimate' => 'Estimate',
        ];
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class, 'tripid');
    }

    public function tripFuelEstimates(): HasMany
    {
        return $this->hasMany(TripFuelEstimate::class, 'sourceobservationid');
    }
}