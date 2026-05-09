<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripLegDestinationItem extends Model
{
    protected $table = 'tripleg_destinationitems';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'triplegid',
        'destinationitemid',
        'sequence_no',
        'linkrole',
        'notes',
        'planneddate',
    ];

    protected $casts = [
        'triplegid' => 'integer',
        'destinationitemid' => 'integer',
        'sequence_no' => 'integer',
        'planneddate' => 'date',
    ];

    public function tripLeg(): BelongsTo
    {
        return $this->belongsTo(TripLeg::class, 'triplegid');
    }

    public function destinationItem(): BelongsTo
    {
        return $this->belongsTo(DestinationItem::class, 'destinationitemid');
    }
}