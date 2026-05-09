<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TripStay extends Model
{
    protected $table = 'tripstays';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'tripid',
        'triplegid',
        'placeid',
        'stayname',
        'staytype',
        'checkindate',
        'checkoutdate',
        'nights',
        'isaccommodationpaid',
        'costpernight',
        'estimatedtotalcost',
        'actualtotalcost',
        'travelledfromplaceid',
        'distancetravelledkm',
        'description',
        'woulduseagain',
        'reviewnotes',
    ];

    protected $casts = [
        'tripid' => 'integer',
        'triplegid' => 'integer',
        'placeid' => 'integer',
        'checkindate' => 'date',
        'checkoutdate' => 'date',
        'nights' => 'integer',
        'isaccommodationpaid' => 'boolean',
        'costpernight' => 'decimal:2',
        'estimatedtotalcost' => 'decimal:2',
        'actualtotalcost' => 'decimal:2',
        'travelledfromplaceid' => 'integer',
        'distancetravelledkm' => 'decimal:1',
        'woulduseagain' => 'boolean',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'tripid');
    }

    public function leg(): BelongsTo
    {
        return $this->belongsTo(TripLeg::class, 'triplegid');
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'placeid');
    }

    public function travelledFromPlace(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'travelledfromplaceid');
    }

    public function tripItems(): HasMany
    {
        return $this->hasMany(TripItem::class, 'tripstayid');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'tripstayid');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'tripstayid');
    }
    public function tripLeg()
    {
        return $this->belongsTo(TripLeg::class, 'triplegid');
    }
    public function destinationItem(): BelongsTo
    {
        return $this->belongsTo(DestinationItem::class, 'destinationitemid');
    }
}