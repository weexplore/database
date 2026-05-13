<?php

namespace App\Models;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Destination extends Model
{
    protected $table = 'destinations';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'placeid',
        'destinationname',
        'destinationtype',
        'overview',
        'travelnotes',
        'bestseason',
        'suitability',
        'accessnotes',
        'personalcommentary',
        'revisitinterestlevel',
        'isfeatured',
    ];

    protected $casts = [
       'id' => 'integer',
        'placeid' => 'integer',
        'revisitinterestlevel' => 'integer',
        'isfeatured' => 'boolean',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'placeid');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DestinationItem::class, 'destinationid');
    }

    public function tripLegs(): HasMany
    {
        return $this->hasMany(TripLeg::class, 'destinationid');
    }

    public function tripItems(): HasMany
    {
        return $this->hasMany(TripItem::class, 'destinationid');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'destinationid');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'destinationid');
    }

    public function sources(): HasMany
    {
        return $this->hasMany(DestinationSource::class, 'destinationid');
    }
    public static function typeOptions(): array
    {
        return [
            'town' => 'Town',
            'region' => 'Region',
            'locality' => 'Locality',
            'attraction' => 'Attraction',
        ];
    }
    public function destinationItems()
    {
        return $this->hasMany(\App\Models\DestinationItem::class, 'destinationid');
    }
    public function tripLegPoints(): HasMany
    {
        return $this->hasMany(TripLegPoint::class, 'destinationid');
    }
    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'linkedid')
            ->where('linkedtype', 'destination');
    }
}