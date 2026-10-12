<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DestinationSource extends Model
{
    protected $table = 'destinationsources';
    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public const CREATED_AT = 'createdat';
    public const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'destinationid',
        'destinationitemid',
        'derivedfromsourceid',
        'sourcetype',
        'sourceurl',
        'sourcetitle',
        'sourcepublisher',
        'retrievedon',
        'importedsummary',
        'importednotes',
        'rawcontent',
        'contentformat',
        'importstatus',
        'reviewedon',
        'reviewedby',
        'internalnotes',
    ];

    protected $casts = [
        'id' => 'integer',
        'destinationid' => 'integer',
        'destinationitemid' => 'integer',
        'derivedfromsourceid' => 'integer',
        'retrievedon' => 'date',
        'reviewedon' => 'date',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];

    public const SOURCE_TYPE_OPTIONS = [
        'website' => 'Website',
        'blog' => 'Blog',
        'tourismboard' => 'Tourism Board',
        'map' => 'Map',
        'guidebook' => 'Guidebook',
        'note' => 'Note',
        'other' => 'Other',
    ];

    public const CONTENT_FORMAT_OPTIONS = [
        'markdown' => 'Markdown',
        'html' => 'HTML',
        'text' => 'Plain text',
    ];

    public const IMPORT_STATUS_OPTIONS = [
        'pendingreview' => 'Pending review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'archived' => 'Archived',
    ];

    public static function sourceTypeOptions(): array
    {
        return self::SOURCE_TYPE_OPTIONS;
    }

    public static function sourceTypeValues(): array
    {
        return array_keys(self::SOURCE_TYPE_OPTIONS);
    }

    public static function contentFormatOptions(): array
    {
        return self::CONTENT_FORMAT_OPTIONS;
    }

    public static function contentFormatValues(): array
    {
        return array_keys(self::CONTENT_FORMAT_OPTIONS);
    }

    public static function importStatusOptions(): array
    {
        return self::IMPORT_STATUS_OPTIONS;
    }

    public static function importStatusValues(): array
    {
        return array_keys(self::IMPORT_STATUS_OPTIONS);
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(
            Destination::class,
            'destinationid'
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
     * The source that referred to or cited this source.
     */
    public function derivedFromSource(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'derivedfromsourceid'
        );
    }

    /**
     * Other sources discovered through this source.
     */
    public function derivedSources(): HasMany
    {
        return $this->hasMany(
            self::class,
            'derivedfromsourceid'
        )->orderByDesc('retrievedon')
            ->orderByDesc('id');
    }

    public function extracts(): HasMany
    {
        return $this->hasMany(
            DestinationSourceExtract::class,
            'destinationsourceid'
        )->orderBy('sortorder')
            ->orderBy('id');
    }

    public function itemCandidates(): HasMany
    {
        return $this->hasMany(
            DestinationSourceItemCandidate::class,
            'destinationsourceid'
        )->orderBy('sortorder')
            ->orderBy('id');
    }

    public function scopeForDestination(
        Builder $query,
        int $destinationId
    ): Builder {
        return $query->where(
            'destinationid',
            $destinationId
        );
    }

    public function scopeForDestinationItem(
        Builder $query,
        int $destinationItemId
    ): Builder {
        return $query->where(
            'destinationitemid',
            $destinationItemId
        );
    }

    public function scopePendingReview(
        Builder $query
    ): Builder {
        return $query->where(
            'importstatus',
            'pendingreview'
        );
    }

    public function scopeApproved(
        Builder $query
    ): Builder {
        return $query->where(
            'importstatus',
            'approved'
        );
    }


}