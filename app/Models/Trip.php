<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    protected $table = 'trips';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'tripname',
        'slug',
        'tripstatus',
        'startdate',
        'enddate',
        'summary',
        'planningnotes',
        'actualnotes',
        'travellercount',
        'defaultdailyfoodbudget',
        'defaultdailymiscbudget',
        'defaultfuelpriceperlitre',
        'defaultfuelconsumptionlper100km',
        'estimatedtotaldistancekm',
        'actualtotaldistancekm',
        'islocked',
    ];

    protected $casts = [
        'startdate' => 'date',
        'enddate' => 'date',
        'travellercount' => 'integer',
        'defaultdailyfoodbudget' => 'decimal:2',
        'defaultdailymiscbudget' => 'decimal:2',
        'defaultfuelpriceperlitre' => 'decimal:4',
        'defaultfuelconsumptionlper100km' => 'decimal:4',
        'estimatedtotaldistancekm' => 'decimal:1',
        'actualtotaldistancekm' => 'decimal:1',
        'islocked' => 'boolean',
    ];

    public function tripTravellerLinks(): HasMany
    {
        return $this->hasMany(TripTraveller::class, 'tripid');
    }

    public function travellers(): BelongsToMany
    {
        return $this->belongsToMany(
            Traveller::class,
            'triptravellers',
            'tripid',
            'travellerid'
        )->withPivot(['id', 'rolename', 'createdat', 'updatedat']);
    }

    public function legs(): HasMany
    {
        return $this->hasMany(TripLeg::class, 'tripid');
    }

    public function stays(): HasMany
    {
        return $this->hasMany(TripStay::class, 'tripid');
    }

    public function fuelPurchases(): HasMany
    {
        return $this->hasMany(FuelPricePurchase::class, 'tripid');
    }

    public function fuelEstimates(): HasMany
    {
        return $this->hasMany(FuelPriceEstimate::class, 'tripid');
    }

    public function fuelPriceObservations(): HasMany
    {
        return $this->hasMany(FuelPriceObservation::class, 'tripid');
    }

    public function tripItems(): HasMany
    {
        return $this->hasMany(TripItem::class, 'tripid');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'tripid');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'tripid');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'tripid');
    }

    public function items()
    {
        return $this->hasMany(TripItem::class, 'tripid');
    }
    public function planItems(): HasMany
    {
        return $this->hasMany(TripPlanItem::class, 'tripid')
            ->orderByRaw('planneddate IS NULL, planneddate ASC')
            ->orderBy('sequence_no')
            ->orderBy('id');
    }
}