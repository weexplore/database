<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FuelStop extends Model
{
    protected $table = 'fuelstops';

    public const CREATED_AT = 'createdat';
    public const UPDATED_AT = 'updatedat';

protected $fillable = [
    'placeid',
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

    public function place()
    {
        return $this->belongsTo(Place::class, 'placeid');
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
}