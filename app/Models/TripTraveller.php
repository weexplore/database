<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripTraveller extends Model
{
    protected $table = 'triptravellers';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'tripid',
        'travellerid',
        'rolename',
    ];

    protected $casts = [
        'tripid' => 'integer',
        'travellerid' => 'integer',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'tripid');
    }

    public function traveller(): BelongsTo
    {
        return $this->belongsTo(Traveller::class, 'travellerid');
    }
}