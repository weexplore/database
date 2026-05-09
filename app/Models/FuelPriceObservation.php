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
        return [
            'actual_purchase' => 'Actual Purchase',
            'signboard' => 'Signboard',
            'website' => 'Website',
            'imported' => 'Imported',
            'estimate' => 'Estimate',
        ][$this->pricesource] ?? ($this->pricesource ?: '—');
    }
}