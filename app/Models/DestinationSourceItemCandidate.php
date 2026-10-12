<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DestinationSourceItemCandidate extends Model
{
    protected $table = 'destinationsourceitemcandidates';
    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public const CREATED_AT = 'createdat';
    public const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'destinationsourceid',
        'itemname',
        'itemtype',
        'shortdescription',
        'editeddescription',
        'addressline1',
        'addressline2',
        'addressline3',
        'postcode',
        'telephone',
        'website',
        'latitude',
        'longitude',
        'bookingrequired',
        'caravanaccessnotes',
        'disabilityaccessnotes',
        'recommendedstayminutes',
        'confidence',
        'reviewnotes',
        'candidatestatus',
        'destinationitemid',
        'appliedon',
        'sortorder',
    ];

    protected $casts = [
        'id' => 'integer',
        'destinationsourceid' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'bookingrequired' => 'boolean',
        'recommendedstayminutes' => 'integer',
        'confidence' => 'integer',
        'destinationitemid' => 'integer',
        'appliedon' => 'datetime',
        'sortorder' => 'integer',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];

    public const STATUS_OPTIONS = [
        'pendingreview' => 'Pending review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'created' => 'Created',
        'linked' => 'Linked',
    ];

    public static function statusOptions(): array
    {
        return self::STATUS_OPTIONS;
    }

    public static function statusValues(): array
    {
        return array_keys(self::STATUS_OPTIONS);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(
            DestinationSource::class,
            'destinationsourceid'
        );
    }

    public function destinationItem(): BelongsTo
    {
        return $this->belongsTo(
            DestinationItem::class,
            'destinationitemid'
        );
    }

    /**
     * itemtype contains the destination-item-type slug rather than its ID.
     */
    public function destinationItemType(): BelongsTo
    {
        return $this->belongsTo(
            DestinationItemType::class,
            'itemtype',
            'slug'
        );
    }

    /**
     * Return edited description when present.
     */
    public function getApplicableDescriptionAttribute(): ?string
    {
        $editedDescription = trim(
            (string) $this->editeddescription
        );

        if ($editedDescription !== '') {
            return $editedDescription;
        }

        $shortDescription = trim(
            (string) $this->shortdescription
        );

        return $shortDescription !== ''
            ? $shortDescription
            : null;
    }

    public function scopePendingReview(
        Builder $query
    ): Builder {
        return $query->where(
            'candidatestatus',
            'pendingreview'
        );
    }

    public function scopeApproved(
        Builder $query
    ): Builder {
        return $query->where(
            'candidatestatus',
            'approved'
        );
    }

    public function scopeUnresolved(
        Builder $query
    ): Builder {
        return $query->whereIn(
            'candidatestatus',
            [
                'pendingreview',
                'approved',
            ]
        );
    }
}