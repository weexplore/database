<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DestinationItem extends Model
{
    protected $table = 'destinationitems';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'destinationid',
        'placeid',
        'addressline1',
        'addressline2',
        'addressline3',
        'postcode',
        'telephone',
        'website',
        'latitude',
        'longitude',
        'internetsearch',
        'itemname',
        'itemtype',
        'shortdescription',
        'notes',
        'estimatedcostperperson',
        'estimatedtotalcost',
        'bookingrequired',
        'caravanaccessnotes',
        'recommendedstayminutes',
        'sortorder',
        'isactive',
    ];

    protected $casts = [
        'destinationid' => 'integer',
        'placeid' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'estimatedcostperperson' => 'decimal:2',
        'estimatedtotalcost' => 'decimal:2',
        'bookingrequired' => 'boolean',
        'recommendedstayminutes' => 'integer',
        'sortorder' => 'integer',
        'isactive' => 'boolean',
    ];

    public static function itemTypeOptions(): array
    {
        return [
            'attraction' => 'Attraction',
            'walk' => 'Walk',
            'museum' => 'Museum',
            'drive' => 'Drive',
            'lookout' => 'Lookout',
            'market' => 'Market',
            'beach' => 'Beach',
            'river' => 'River',
            'lake' => 'Lake',
            'Artesian Pool' => 'Artesian Pool',
            'quilt-shop' => 'Quilt Shop',
            'campground' => 'Campground',
            'caravan-park' => 'Caravan Park',
            'free-camp' => 'Free Camp',
            'cafe' => 'Cafe',
            'bakery' => 'Bakery',
            'hotel-motel' => 'Hotel/Motel',
            'restaurant' => 'Restaurant',
            'winery' => 'Winery',
            'supermarket' => 'Supermarket',
            'dump_point' => 'Dump Point',
            'water_point' => 'Water Point',
            'water_dump_point' => 'Water & Dump Point',
            'church' => 'Church',
            'information' => 'Information',
            'vehicle-repair' => 'Vehicle Repair',
            'rest-area' => 'Rest Area',
            'silo-art' => 'Silo Art',
            'street-art' => 'Street Art',
            'other' => 'Other',
        ];
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'destinationid');
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'placeid');
    }

    public function tripItems(): HasMany
    {
        return $this->hasMany(TripItem::class, 'destinationitemid');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'destinationitemid');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'destinationitemid');
    }

    public function sources(): HasMany
    {
        return $this->hasMany(DestinationSource::class, 'destinationitemid');
    }
}