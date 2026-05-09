<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DestinationSource extends Model
{
    protected $table = 'destinationsources';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'destinationid',
        'destinationitemid',
        'sourcetype',
        'sourceurl',
        'sourcetitle',
        'sourcepublisher',
        'retrievedon',
        'importedsummary',
        'importednotes',
        'importstatus',
        'reviewedon',
        'reviewedby',
        'internalnotes',
    ];

    protected $casts = [
        'id' => 'integer',
        'destinationid' => 'integer',
        'destinationitemid' => 'integer',
        'retrievedon' => 'date',
        'reviewedon' => 'date',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'destinationid');
    }

    public function destinationItem(): BelongsTo
    {
        return $this->belongsTo(DestinationItem::class, 'destinationitemid');
    }

    public static function sourceTypeOptions(): array
    {
        return [
            'website' => 'Website',
            'blog' => 'Blog',
            'tourismboard' => 'Tourism Board',
            'map' => 'Map',
            'guidebook' => 'Guidebook',
            'note' => 'Note',
            'other' => 'Other',
        ];
    }

    public static function importStatusOptions(): array
    {
        return [
            'pendingreview' => 'Pending review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'archived' => 'Archived',
        ];
    }

    public function scopeForDestination(Builder $query, int $destinationId): Builder
    {
        return $query->where('destinationid', $destinationId);
    }

    public function scopePendingReview(Builder $query): Builder
    {
        return $query->where('importstatus', 'pendingreview');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('importstatus', 'approved');
    }
}