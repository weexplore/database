<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class BudgetLineMonth extends Model
{
    protected $table = 'budget_line_months';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'budgetlineid',
        'monthno',
        'adoptedamount',
        'revisedamount',
        'revisedisactual',
    ];

    protected $casts = [
        'monthno'         => 'integer',
        'adoptedamount'   => 'decimal:2',
        'revisedamount'   => 'decimal:2',
        'revisedisactual' => 'boolean',
    ];

    // Month name map: FY month number => short label
    const MONTH_LABELS = [
        1  => 'Jul',
        2  => 'Aug',
        3  => 'Sep',
        4  => 'Oct',
        5  => 'Nov',
        6  => 'Dec',
        7  => 'Jan',
        8  => 'Feb',
        9  => 'Mar',
        10 => 'Apr',
        11 => 'May',
        12 => 'Jun',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function budgetLine()
    {
        return $this->belongsTo(BudgetLine::class, 'budgetlineid');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeForLine(Builder $query, int $lineId): Builder
    {
        return $query->where('budgetlineid', $lineId);
    }

    public function scopeUpToMonth(Builder $query, int $monthNo): Builder
    {
        return $query->where('monthno', '<=', $monthNo);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Returns the effective revised amount, falling back to adoptedamount
     * when no revision has been prepared yet.
     */
    public function effectiveRevisedAmount(): float
    {
        return $this->revisedamount !== null
            ? (float) $this->revisedamount
            : (float) $this->adoptedamount;
    }

    /**
     * Short month label for this month number, e.g. "Jul", "Aug".
     */
    public function getMonthLabelAttribute(): string
    {
        return self::MONTH_LABELS[$this->monthno] ?? "M{$this->monthno}";
    }

    /**
     * Set revisedamount from an actual Cashbook figure and flag it as actual-sourced.
     */
    public function lockFromActual(float $actualAmount): void
    {
        $this->revisedamount    = $actualAmount;
        $this->revisedisactual  = true;
        $this->save();
    }

    /**
     * Clear the revised amount and actual flag, reverting to adopted.
     */
    public function clearRevised(): void
    {
        $this->revisedamount   = null;
        $this->revisedisactual = false;
        $this->save();
    }
}
