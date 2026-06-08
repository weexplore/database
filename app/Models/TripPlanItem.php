<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripPlanItem extends Model
{
    protected $table = 'tripplanitems';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'tripid',
        'sequence_no',
        'plantype',
        'placeid',
        'destinationid',
        'destinationitemid',
        'triplegid',
        'tripstayid',
        'planneddate',
        'plannedenddate',
        'starttime',
        'endtime',
        'title',
        'notes',
        'isovernight',
        'isstaytarget',
        'isrouteanchor',
        'isgovia',
        'staytype',
        'nightsplanned',
        'sortgroup',
        'mapcolor',
    ];

    protected $casts = [
        'tripid' => 'integer',
        'sequence_no' => 'integer',
        'placeid' => 'integer',
        'destinationid' => 'integer',
        'destinationitemid' => 'integer',
        'triplegid' => 'integer',
        'tripstayid' => 'integer',
        'planneddate' => 'date',
        'plannedenddate' => 'date',
        'starttime' => 'datetime:H:i',
        'endtime' => 'datetime:H:i',
        'isovernight' => 'boolean',
        'isgovia' => 'boolean',
        'isstaytarget' => 'boolean',
        'isrouteanchor' => 'boolean',
        'nightsplanned' => 'integer',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'tripid');
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'placeid');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'destinationid');
    }

    public function destinationItem(): BelongsTo
    {
        return $this->belongsTo(DestinationItem::class, 'destinationitemid');
    }

    public function tripLeg(): BelongsTo
    {
        return $this->belongsTo(TripLeg::class, 'triplegid');
    }

    public function tripStay(): BelongsTo
    {
        return $this->belongsTo(TripStay::class, 'tripstayid');
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->title
            ?: $this->destinationItem?->itemname
            ?: $this->destination?->destinationname
            ?: $this->place?->placename
            ?: 'Planning item';
    }
}