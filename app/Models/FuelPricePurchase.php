<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelPricePurchase extends Model
{
    protected $table = 'tripfuelpurchases';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'tripid',
        'triplegid',
        'fuelstopid',
        'placeid',
        'purchasedate',
        'odometerkm',
        'distancesincelastfillkm',
        'fueltype',
        'litres',
        'priceperlitre',
        'fueltotal',
        'servicecosts',
        'repairscost',
        'receiptreference',
        'notes',
    ];

    protected $casts = [
        'tripid' => 'integer',
        'triplegid' => 'integer',
        'fuelstopid' => 'integer',
        'placeid' => 'integer',
        'purchasedate' => 'date',
        'odometerkm' => 'decimal:1',
        'distancesincelastfillkm' => 'decimal:1',
        'litres' => 'decimal:3',
        'priceperlitre' => 'decimal:4',
        'fueltotal' => 'decimal:2',
        'servicecosts' => 'decimal:2',
        'repairscost' => 'decimal:2',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'tripid');
    }

    public function leg(): BelongsTo
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
}