<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripLegPoint extends Model
{
    protected $table = 'triplegpoints';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'triplegid',
        'sequence_no',
        'pointtype',
        'placeid',
        'destinationid',
        'destinationitemid',
        'title',
        'notes',
    ];

    protected $casts = [
        'triplegid' => 'integer',
        'sequence_no' => 'integer',
        'placeid' => 'integer',
        'destinationid' => 'integer',
        'destinationitemid' => 'integer',
        'planneddate' => 'date',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];

    public function tripLeg(): BelongsTo
    {
        return $this->belongsTo(TripLeg::class, 'triplegid');
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
}