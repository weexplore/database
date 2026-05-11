<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TripLeg extends Model
{
    protected $table = 'triplegs';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

protected $fillable = [
    'tripid',
    'legnumber',
    'startdate',
    'enddate',
    'nightsplanned',
    'fromplaceid',
    'toplaceid',
    'destinationid',
    'fromdestinationitemid',
    'destinationitemid',
    'title',
    'description',
    'distancekm',
    'elevationgainm',
    'elevationlossm',
    'drivingnotes',
    'planningnotes',
    'actualnotes',
    'sortorder',
    'plannerstatus',
];

protected $casts = [
    'tripid' => 'integer',
    'legnumber' => 'integer',
    'startdate' => 'date',
    'enddate' => 'date',
    'nightsplanned' => 'integer',
    'fromplaceid' => 'integer',
    'toplaceid' => 'integer',
    'destinationid' => 'integer',
    'fromdestinationitemid' => 'integer',
    'distancekm' => 'decimal:1',
    'elevationgainm' => 'decimal:1',
    'elevationlossm' => 'decimal:1',
    'sortorder' => 'integer',
];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'tripid');
    }

    public function fromPlace(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'fromplaceid');
    }

    public function toPlace(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'toplaceid');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'destinationid');
    }

    public function stays(): HasMany
    {
        return $this->hasMany(TripStay::class, 'triplegid');
    }

    public function fuelPurchases(): HasMany
    {
        return $this->hasMany(FuelPricePurchase::class, 'triplegid');
    }

    public function fuelEstimates(): HasMany
    {
        return $this->hasMany(FuelPriceEstimate::class, 'triplegid');
    }

    public function tripItems(): HasMany
    {
        return $this->hasMany(TripItem::class, 'triplegid');
    }

    public function tripLegVehicles(): HasMany
    {
        return $this->hasMany(TripLegVehicle::class, 'triplegid');
    }

    public function vehicles(): BelongsToMany
    {
        return $this->belongsToMany(Vehicle::class, 'triplegvehicles', 'triplegid', 'vehicleid')
            ->withPivot(['vehiclerole', 'sortorder'])
            ->withTimestamps('createdat', 'updatedat');
    }

    public function planItems(): HasMany
    {
        return $this->hasMany(TripPlanItem::class, 'triplegid');
    }

    public function tripLegDestinations(): HasMany
    {
        return $this->hasMany(TripLegDestination::class, 'triplegid');
    }

    public function tripLegDestinationItems(): HasMany
    {
        return $this->hasMany(TripLegDestinationItem::class, 'triplegid');
    }

    public function linkedDestinations(): BelongsToMany
    {
        return $this->belongsToMany(Destination::class, 'tripleg_destinations', 'triplegid', 'destinationid')
            ->withPivot(['id', 'sequence_no', 'linkrole', 'notes', 'createdat', 'updatedat'])
            ->withTimestamps('createdat', 'updatedat');
    }

    public function linkedDestinationItems(): BelongsToMany
    {
        return $this->belongsToMany(DestinationItem::class, 'tripleg_destinationitems', 'triplegid', 'destinationitemid')
            ->withPivot(['id', 'sequence_no', 'linkrole', 'notes', 'planneddate', 'createdat', 'updatedat'])
            ->withTimestamps('createdat', 'updatedat');
    }
    public function fromDestinationItem(): BelongsTo
    {
        return $this->belongsTo(DestinationItem::class, 'fromdestinationitemid');
    }
    public function destinationItem(): BelongsTo
    {
        return $this->belongsTo(DestinationItem::class, 'destinationitemid');
    }
    public function legPoints(): HasMany
    {
        return $this->hasMany(TripLegPoint::class, 'triplegid')
            ->orderBy('sequence_no');
    }
}