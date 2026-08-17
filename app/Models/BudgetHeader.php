<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class BudgetHeader extends Model
{
    protected $table = 'budget_headers';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'legalentityid',
        'financialyear',
        'status',
        'adopteddate',
        'reviseddate',
        'adoptednotes',
        'revisednotes',
        'preparedby',
        'isactive',
    ];

    protected $casts = [
        'financialyear' => 'integer',
        'adopteddate'   => 'date',
        'reviseddate'   => 'date',
        'isactive'      => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Status constants
    // -------------------------------------------------------------------------

    const STATUS_DRAFT    = 'draft';
    const STATUS_ADOPTED  = 'adopted';
    const STATUS_REVISED  = 'revised';
    const STATUS_CLOSED   = 'closed';

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT   => 'Draft',
            self::STATUS_ADOPTED => 'Adopted',
            self::STATUS_REVISED => 'Revised',
            self::STATUS_CLOSED  => 'Closed',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function legalEntity()
    {
        return $this->belongsTo(LegalEntity::class, 'legalentityid');
    }

    public function budgetLines()
    {
        return $this->hasMany(BudgetLine::class, 'budgetheaderid')->orderBy('sortorder');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    // use ?int and ignore the filter when null
    public function scopeForEntity(Builder $query, ?int $legalEntityId): Builder
    {
        if ($legalEntityId === null) {
            return $query;
        }

        return $query->where('legalentityid', $legalEntityId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('isactive', 1);
    }

    public function scopeForYear(Builder $query, int $financialYear): Builder
    {
        return $query->where('financialyear', $financialYear);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Human-readable label, e.g. "FY2025-26"
     */
    public function getYearLabelAttribute(): string
    {
        return 'FY' . ($this->financialyear - 1) . '-' . substr($this->financialyear, -2);
    }

    /**
     * The financial year starts on 1 July of (financialyear - 1).
     */
    public function getYearStartDateAttribute(): \Carbon\Carbon
    {
        return \Carbon\Carbon::create($this->financialyear - 1, 7, 1);
    }

    /**
     * The financial year ends on 30 June of financialyear.
     */
    public function getYearEndDateAttribute(): \Carbon\Carbon
    {
        return \Carbon\Carbon::create($this->financialyear, 6, 30);
    }

    public function isDraft(): bool    { return $this->status === self::STATUS_DRAFT; }
    public function isAdopted(): bool  { return $this->status === self::STATUS_ADOPTED; }
    public function isRevised(): bool  { return $this->status === self::STATUS_REVISED; }
    public function isClosed(): bool   { return $this->status === self::STATUS_CLOSED; }

    /**
     * Adopted amounts are locked (read-only) once status is adopted, revised, or closed.
     */
    public function isAdoptedLocked(): bool
    {
        return in_array($this->status, [self::STATUS_ADOPTED, self::STATUS_REVISED, self::STATUS_CLOSED]);
    }

    /**
     * Returns the current YTD month number (1=July ... 12=June) based on today's date,
     * clamped to the range of this financial year.
     */
    public function currentYtdMonthNo(): int
    {
        $now = now();
        // Map calendar month to FY month number
        $fyMonth = $now->month >= 7 ? $now->month - 6 : $now->month + 6;
        return max(1, min(12, $fyMonth));
    }
}
