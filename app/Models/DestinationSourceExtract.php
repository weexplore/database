<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DestinationSourceExtract extends Model
{
    protected $table = 'destinationsourceextracts';
    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public const CREATED_AT = 'createdat';
    public const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'destinationsourceid',
        'targetfield',
        'extractedtext',
        'editedtext',
        'confidence',
        'extractstatus',
        'appliedon',
        'sortorder',
    ];

    protected $casts = [
        'id' => 'integer',
        'destinationsourceid' => 'integer',
        'confidence' => 'integer',
        'appliedon' => 'datetime',
        'sortorder' => 'integer',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];

    public const TARGET_FIELD_OPTIONS = [
        'overview' => 'Overview',
        'travelnotes' => 'Travel notes',
        'bestseason' => 'Best season',
        'suitability' => 'Suitability',
        'access' => 'Access notes',
    ];

    public const STATUS_OPTIONS = [
        'pendingreview' => 'Pending review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'applied' => 'Applied',
    ];

    public static function targetFieldOptions(): array
    {
        return self::TARGET_FIELD_OPTIONS;
    }

    public static function targetFieldValues(): array
    {
        return array_keys(self::TARGET_FIELD_OPTIONS);
    }

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

    /**
     * Return edited text when present; otherwise use captured text.
     */
    public function getApplicableTextAttribute(): string
    {
        $editedText = trim(
            (string) $this->editedtext
        );

        return $editedText !== ''
            ? $editedText
            : (string) $this->extractedtext;
    }

    public function scopePendingReview(
        Builder $query
    ): Builder {
        return $query->where(
            'extractstatus',
            'pendingreview'
        );
    }

    public function scopeApproved(
        Builder $query
    ): Builder {
        return $query->where(
            'extractstatus',
            'approved'
        );
    }

    public function scopeApplied(
        Builder $query
    ): Builder {
        return $query->where(
            'extractstatus',
            'applied'
        );
    }
}