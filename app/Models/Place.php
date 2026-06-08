<?php

namespace App\Models;

use App\Models\KnowledgeItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
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
    public function knowledgeItems(): HasMany
    {
        return $this->hasMany(KnowledgeItem::class, 'placeid');
    }
     public function scopeWithinRadius(
        Builder $query,
        float $latitude,
        float $longitude,
        float $radiusKm,
        ?int $stateId = null,
        ?string $placeType = null,
        bool $activeOnly = true
    ): Builder {
        $latDelta = $radiusKm / 111.045;
        $lngDelta = $radiusKm / (111.045 * max(cos(deg2rad($latitude)), 0.01));

        $distanceSql = <<<SQL
(
    6371 * ACOS(
        LEAST(
            1,
            COS(RADIANS(?)) *
            COS(RADIANS(latitude)) *
            COS(RADIANS(longitude) - RADIANS(?)) +
            SIN(RADIANS(?)) *
            SIN(RADIANS(latitude))
        )
    )
)
SQL;

        return $query
            ->select('places.*')
            ->selectRaw($distanceSql . ' as distance_km', [$latitude, $longitude, $latitude])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [$latitude - $latDelta, $latitude + $latDelta])
            ->whereBetween('longitude', [$longitude - $lngDelta, $longitude + $lngDelta])
            ->when($stateId, fn (Builder $q) => $q->where('stateid', $stateId))
            ->when($placeType, fn (Builder $q) => $q->where('placetype', $placeType))
            ->when($activeOnly, fn (Builder $q) => $q->where('isactive', 1))
            ->having('distance_km', '<=', $radiusKm)
            ->orderBy('distance_km')
            ->orderBy('placename');
    }

    public function scopeNearbyToPlace(
        Builder $query,
        Place $place,
        float $radiusKm = 50,
        bool $excludeSelf = true
    ): Builder {
        return $query
            ->withinRadius(
                latitude: (float) $place->latitude,
                longitude: (float) $place->longitude,
                radiusKm: $radiusKm,
                stateId: null,
                placeType: null,
                activeOnly: true
            )
            ->when($excludeSelf, fn (Builder $q) => $q->where('id', '!=', $place->id));
    }

    public function tripLegSuggestions()
    {
        return $this->hasMany(TripLegSuggestion::class);
    }
}