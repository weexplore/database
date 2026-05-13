<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Attachment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Booking extends Model
{
    protected $table = 'bookings';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'tripid',
        'tripstayid',
        'tripitemid',
        'destinationid',
        'destinationitemid',
        'placeid',
        'bookingtype',
        'providername',
        'providercontact',
        'website',
        'externalreference',
        'status',
        'requestedon',
        'confirmedon',
        'startdate',
        'enddate',
        'notes',
        'estimatedcost',
        'actualcost',
        'currency',
        'paymentstatus',
        'paymentnotes',
    ];

    protected $casts = [
        'tripid' => 'integer',
        'tripstayid' => 'integer',
        'tripitemid' => 'integer',
        'destinationid' => 'integer',
        'destinationitemid' => 'integer',
        'placeid' => 'integer',
        'requestedon' => 'date',
        'confirmedon' => 'date',
        'startdate' => 'date',
        'enddate' => 'date',
        'estimatedcost' => 'decimal:2',
        'actualcost' => 'decimal:2',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'tripid');
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
    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'linkedid')
            ->where('linkedtype', 'booking');
    }
}