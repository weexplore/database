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

    public const CREATED_AT = 'createdat';

    public const UPDATED_AT = 'updatedat';

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

    public static function fuelTypes(): array
    {
        return [
            'diesel' => 'Diesel',
            'premiumdiesel' => 'Premium Diesel',
            'unleaded91' => 'Unleaded 91',
            'unleaded95' => 'Premium Unleaded 95',
            'unleaded98' => 'Premium Unleaded 98',
            'lpg' => 'LPG',
            'adblue' => 'AdBlue',
            'other' => 'Other',
        ];
    }

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

    public function scopeUnassigned($query)
    {
        return $query->whereNull('tripid');
    }

    public function scopeAssigned($query)
    {
        return $query->whereNotNull('tripid');
    }

    public function getIsAssignedAttribute(): bool
    {
        return ! is_null($this->tripid);
    }

    public function getLocationLabelAttribute(): string
    {
        if ($this->fuelStop) {
            $label = $this->fuelStop->stopname;

            if ($this->fuelStop->place) {
                $label .= ' – ' . $this->fuelStop->place->placename;
            }

            return $label;
        }

        if ($this->place) {
            return $this->place->placename;
        }

        return '—';
    }

    public function getTripLabelAttribute(): string
    {
        if (! $this->trip) {
            return 'Unassigned';
        }

        return $this->trip->tripname;
    }
}