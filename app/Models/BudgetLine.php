<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class BudgetLine extends Model
{
    protected $table = 'budget_lines';

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'budgetheaderid',
        'accountid',
        'categoryid',
        'sortorder',
    ];

    protected $casts = [
        'sortorder' => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function budgetHeader()
    {
        return $this->belongsTo(BudgetHeader::class, 'budgetheaderid');
    }

    public function account()
    {
        return $this->belongsTo(CashbookAccount::class, 'accountid');
    }

    public function category()
    {
        return $this->belongsTo(CashbookCategory::class, 'categoryid');
    }

    public function months()
    {
        return $this->hasMany(BudgetLineMonth::class, 'budgetlineid')->orderBy('monthno');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeForHeader(Builder $query, int $headerId): Builder
    {
        return $query->where('budgetheaderid', $headerId);
    }

    public function scopeForAccount(Builder $query, int $accountId): Builder
    {
        return $query->where('accountid', $accountId);
    }

    public function scopeForCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('categoryid', $categoryId);
    }

    // -------------------------------------------------------------------------
    // Totals (calculated — never stored)
    // -------------------------------------------------------------------------

    /**
     * Sum of all 12 adopted amounts for this line.
     */
    public function adoptedTotal(): float
    {
        return $this->months->sum('adoptedamount');
    }

    /**
     * Sum of all 12 revised amounts for this line.
     * Falls back to adoptedamount where revisedamount is null.
     */
    public function revisedTotal(): float
    {
        return $this->months->sum(function (BudgetLineMonth $m) {
            return $m->effectiveRevisedAmount();
        });
    }

    /**
     * YTD adopted total up to and including the given month number (1–12).
     */
    public function adoptedYtd(int $throughMonthNo): float
    {
        return $this->months
            ->where('monthno', '<=', $throughMonthNo)
            ->sum('adoptedamount');
    }

    /**
     * YTD revised total up to and including the given month number (1–12).
     */
    public function revisedYtd(int $throughMonthNo): float
    {
        return $this->months
            ->where('monthno', '<=', $throughMonthNo)
            ->sum(function (BudgetLineMonth $m) {
                return $m->effectiveRevisedAmount();
            });
    }

    // -------------------------------------------------------------------------
    // Preparation helpers
    // -------------------------------------------------------------------------

    /**
     * Distribute a total equally across all 12 months.
     * Updates adoptedamount or revisedamount depending on $field.
     */
    public function distributeEqual(float $total, string $field = 'adoptedamount'): void
    {
        $monthly = round($total / 12, 2);
        $remainder = round($total - ($monthly * 12), 2);

        foreach ($this->months as $index => $month) {
            // Add remainder to the last month to ensure total is exact
            $amount = ($index === 11) ? $monthly + $remainder : $monthly;
            $month->$field = $amount;
            $month->save();
        }
    }

    /**
     * Distribute a total proportionally based on prior-year actuals.
     * $priorYearMonthlyActuals = array keyed by monthno (1–12) => amount.
     * Falls back to equal distribution if prior-year data sums to zero.
     */
    public function distributeProportioned(float $total, array $priorYearMonthlyActuals, string $field = 'adoptedamount'): void
    {
        $priorTotal = array_sum($priorYearMonthlyActuals);

        if ($priorTotal == 0) {
            $this->distributeEqual($total, $field);
            return;
        }

        $assigned = 0.00;
        $months = $this->months->sortBy('monthno')->values();

        foreach ($months as $index => $month) {
            $prior = $priorYearMonthlyActuals[$month->monthno] ?? 0;
            if ($index === $months->count() - 1) {
                // Last month absorbs any rounding difference
                $amount = round($total - $assigned, 2);
            } else {
                $amount = round(($prior / $priorTotal) * $total, 2);
                $assigned += $amount;
            }
            $month->$field = $amount;
            $month->save();
        }
    }
}
