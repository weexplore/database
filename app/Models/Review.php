<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $table = 'reviews';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'tripid',
        'travellerid',
        'tripstayid',
        'tripitemid',
        'destinationid',
        'destinationitemid',
        'placeid',
        'reviewdate',
        'ratingoverall',
        'ratingvalue',
        'ratingfacility',
        'ratingaccess',
        'ratingambience',
        'title',
        'comments',
        'returninterestlevel',
        'wouldreturn',
        'isprivate',
    ];

    protected $casts = [
        'tripid' => 'integer',
        'travellerid' => 'integer',
        'tripstayid' => 'integer',
        'tripitemid' => 'integer',
        'destinationid' => 'integer',
        'destinationitemid' => 'integer',
        'placeid' => 'integer',
        'reviewdate' => 'date',
        'ratingoverall' => 'integer',
        'ratingvalue' => 'integer',
        'ratingfacility' => 'integer',
        'ratingaccess' => 'integer',
        'ratingambience' => 'integer',
        'returninterestlevel' => 'integer',
        'wouldreturn' => 'boolean',
        'isprivate' => 'boolean',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'tripid');
    }

    public function traveller(): BelongsTo
    {
        return $this->belongsTo(Traveller::class, 'travellerid');
    }

    public function stay(): BelongsTo
    {
        return $this->belongsTo(TripStay::class, 'tripstayid');
    }

    public function tripItem(): BelongsTo
    {
        return $this->belongsTo(TripItem::class, 'tripitemid');
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
}