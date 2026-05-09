<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TripItem extends Model
{
    protected $table = 'tripitems';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'tripid',
        'triplegid',
        'tripstayid',
        'destinationid',
        'destinationitemid',
        'placeid',
        'itemdate',
        'startdatetime',
        'enddatetime',
        'itemtype',
        'status',
        'title',
        'description',
        'priority',
        'isfullday',
        'peoplecount',
        'estimatedcostperperson',
        'estimatedtotalcost',
        'actualcost',
        'allocateasdailycost',
        'bookingid',
        'notesinternal',
        'sortorder',
    ];

    protected $casts = [
        'tripid' => 'integer',
        'triplegid' => 'integer',
        'tripstayid' => 'integer',
        'destinationid' => 'integer',
        'destinationitemid' => 'integer',
        'placeid' => 'integer',
        'itemdate' => 'date',
        'startdatetime' => 'datetime',
        'enddatetime' => 'datetime',
        'isfullday' => 'boolean',
        'peoplecount' => 'integer',
        'estimatedcostperperson' => 'decimal:2',
        'estimatedtotalcost' => 'decimal:2',
        'actualcost' => 'decimal:2',
        'allocateasdailycost' => 'boolean',
        'bookingid' => 'integer',
        'sortorder' => 'integer',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'tripid');
    }

    public function tripleg(): BelongsTo
    {
        return $this->belongsTo(TripLeg::class, 'triplegid');
    }

    public function stay(): BelongsTo
    {
        return $this->belongsTo(TripStay::class, 'tripstayid');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'destinationid');
    }

    public function destinationItem(): BelongsTo
    {
        return $this->belongsTo(DestinationItem::class, 'destinationitemid');
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'placeid');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'bookingid');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'tripitemid');
    }
}