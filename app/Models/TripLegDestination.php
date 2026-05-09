<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripLegDestination extends Model
{
    protected $table = 'tripleg_destinations';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'triplegid',
        'destinationid',
        'sequence_no',
        'linkrole',
        'notes',
    ];

    protected $casts = [
        'triplegid' => 'integer',
        'destinationid' => 'integer',
        'sequence_no' => 'integer',
    ];

    public function tripLeg(): BelongsTo
    {
        return $this->belongsTo(TripLeg::class, 'triplegid');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'destinationid');
    }
}