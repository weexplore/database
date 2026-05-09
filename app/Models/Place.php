<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Place extends Model
{
    protected $table = 'places';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'countryid',
        'stateid',
        'regionid',
        'placename',
        'placetype',
        'locality',
        'addressline1',
        'addressline2',
        'postcode',
        'latitude',
        'longitude',
        'accessnotes',
        'generalnotes',
        'sourcequality',
        'isactive',
    ];

    protected $casts = [
        'id' => 'integer',
        'countryid' => 'integer',
        'stateid' => 'integer',
        'regionid' => 'integer',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'isactive' => 'boolean',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'countryid');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'stateid');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'regionid');
    }

    public function aliases(): HasMany
    {
        return $this->hasMany(PlaceAlias::class, 'placeid');
    }

    public function destinations(): HasMany
    {
        return $this->hasMany(Destination::class, 'placeid');
    }

    public function destinationItems(): HasMany
    {
        return $this->hasMany(DestinationItem::class, 'placeid');
    }

    public function fuelStops(): HasMany
    {
        return $this->hasMany(FuelStop::class, 'placeid');
    }

    public function tripStays(): HasMany
    {
        return $this->hasMany(TripStay::class, 'placeid');
    }

    // Trip legs where this place is the TO place

    public function tripLegsTo()
    {
        return $this->hasMany(\App\Models\TripLeg::class, 'toplaceid');
    }

    // Trip legs where this place is the FROM place (optional)
    public function tripLegsFrom()
    {
        return $this->hasMany(\App\Models\TripLeg::class, 'fromplaceid');
    }
}