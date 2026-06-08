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
        'triplegsearchprofileid',
        'legnumber',
        'startdate',
        'enddate',
        'nightsplanned',
        'fromplaceid',
        'fromdestinationid',
        'fromdestinationitemid',
        'toplaceid',
        'todestinationid',
        'todestinationitemid',
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
        'fromdestinationid' => 'integer',
        'fromdestinationitemid' => 'integer',
        'toplaceid' => 'integer',
        'todestinationid' => 'integer',
        'todestinationitemid' => 'integer',
        'distancekm' => 'decimal:1',
        'elevationgainm' => 'decimal:1',
        'elevationlossm' => 'decimal:1',
        'sortorder' => 'integer',
        'triplegsearchprofileid' => 'integer',
    ];

    public function trip()
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

    public function fromDestination(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'fromdestinationid');
    }

    public function toDestination(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'todestinationid');
    }

    public function fromDestinationItem(): BelongsTo
    {
        return $this->belongsTo(DestinationItem::class, 'fromdestinationitemid');
    }

    public function toDestinationItem(): BelongsTo
    {
        return $this->belongsTo(DestinationItem::class, 'todestinationitemid');
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
            ->orderByRaw('COALESCE(triplegvehicles.sortorder, 999999), vehicles.id');
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

    public function legPoints(): HasMany
    {
        return $this->hasMany(TripLegPoint::class, 'triplegid')
            ->orderBy('sequence_no');
    }
    
    public function tripLegPoints()
    {
        return $this->hasMany(TripLegPoint::class, 'triplegid');
    }
    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    public function tripStays()
    {
        return $this->hasMany(TripStay::class, 'triplegid');
    }


    public function tripFuelEstimates()
    {
        return $this->hasMany(TripFuelEstimate::class, 'triplegid');
    }

    public function searchRuns()
    {
        return $this->hasMany(TripLegSearchRun::class, 'trip_leg_id')->latest('id');
    }

    public function suggestions()
    {
        return $this->hasMany(TripLegSuggestion::class, 'trip_leg_id')->latest('id');
    }
    public function searchProfile()
    {
        return $this->belongsTo(\App\Models\TripLegSearchProfile::class, 'triplegsearchprofileid');
    }
    protected $appends = [
        'effective_trip_leg_search_profile_id',
    ];

    public function tripLegSearchProfile(): BelongsTo
    {
        return $this->belongsTo(TripLegSearchProfile::class, 'triplegsearchprofileid');
    }

    public function getEffectiveTripLegSearchProfileIdAttribute(): ?int
    {
        return $this->triplegsearchprofileid
            ?? $this->trip?->triplegsearchprofileid;
    }

    public function effectiveTripLegSearchProfile(): ?TripLegSearchProfile
    {
        if ($this->relationLoaded('tripLegSearchProfile') && $this->tripLegSearchProfile) {
            return $this->tripLegSearchProfile;
        }

        if ($this->triplegsearchprofileid) {
            return $this->tripLegSearchProfile;
        }

        return $this->trip?->tripLegSearchProfile;
    }
}