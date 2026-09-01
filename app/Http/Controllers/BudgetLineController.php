<?php

namespace App\Http\Controllers;

use App\Models\BudgetHeader;
use App\Models\BudgetLine;
use App\Models\BudgetLineMonth;
use App\Models\CashbookAccount;
use App\Models\CashbookCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BudgetLineController extends Controller
{
    // Financial-year sequence: 1=Jul ... 12=Jun.
    private const FY_MONTHS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

    public function index(Request $request, BudgetHeader $budget)
    {
        session(['active_legal_entity_id' => $budget->legalentityid]);
        $this->authoriseEntity($budget);

        $budget->load('legalEntity');
        $legalEntityId = (int) $budget->legalentityid;
        $monthLabels = BudgetLineMonth::MONTH_LABELS;

        $lines = $budget->budgetLines()
            ->with([
                'account.accountType',
                'category.categoryType',
                'category.parentCategory.parentCategory.parentCategory',
                'months',
            ])
            ->get();

        $lines = $this->orderBudgetLines($lines, $legalEntityId);

        $startOfYear = Carbon::create($budget->financialyear - 1, 7, 1)->toDateString();
        $endOfYear = Carbon::create($budget->financialyear, 6, 30)->toDateString();

        /*
        * Actuals are collected as display amounts by budget category:
        *
        * - Receipt categories: positive incoming amounts.
        * - Payment categories: positive expense amounts. They are subtracted later
        *   by entityNetMovementMultiplier().
        * - Transfer categories: retain cash-direction sign:
        *      receipt / transfer-in = positive;
        *      payment / transfer-out = negative.
        *
        * This makes Budget, Revised, and Actual section totals comparable and lets
        * the final net formula consistently be:
        *
        * Receipts - Payments + Transfers
        */
        $actualRows = DB::table('cashbook_transaction_lines as line')
            ->join('cashbook_transactions as txn', 'txn.id', '=', 'line.transactionid')
            ->join('cashbook_categories as category', 'category.id', '=', 'line.categoryid')
            ->join(
                'cashbook_category_types as categoryType',
                'categoryType.id',
                '=',
                'category.categorytypeid'
            )
            ->where('txn.legalentityid', $legalEntityId)
            ->whereBetween('txn.transactiondate', [$startOfYear, $endOfYear])
            ->whereNotNull('line.categoryid')
            ->selectRaw("
                line.categoryid,
                MONTH(txn.transactiondate) AS calendar_month,

                SUM(
                    CASE
                        /*
                        * Transfer category rows must retain their direction:
                        * payment = transfer out = negative;
                        * receipt = transfer in = positive.
                        *
                        * ABS protects this calculation if historic transaction-line
                        * values happen to contain either positive or negative signs.
                        */
                        WHEN LOWER(TRIM(categoryType.typecode)) IN ('transfer', 'transfers')
                            AND LOWER(TRIM(txn.transactionkind)) = 'payment'
                        THEN -ABS(line.amount)

                        WHEN LOWER(TRIM(categoryType.typecode)) IN ('transfer', 'transfers')
                            AND LOWER(TRIM(txn.transactionkind)) = 'receipt'
                        THEN ABS(line.amount)

                        /*
                        * Receipt and payment category rows are display values:
                        * both remain positive here.
                        *
                        * Payments are turned into an outflow later by:
                        * entityNetMovementMultiplier('payment') => -1
                        */
                        ELSE ABS(line.amount)
                    END
                ) AS total
            ")
            ->groupBy(
                'line.categoryid',
                DB::raw('MONTH(txn.transactiondate)')
            )
            ->get();

        $actualMap = [];

        foreach ($actualRows as $row) {
            $fyMonthNo = $this->calendarMonthToFyMonth((int) $row->calendar_month);
            $actualMap[(int) $row->categoryid][$fyMonthNo] = (float) $row->total;
        }

        $lineActuals = [];

        foreach ($lines as $line) {
            if (isset($actualMap[$line->categoryid])) {
                $lineActuals[$line->id] = $actualMap[$line->categoryid];
            }
        }

        $nextYearHeader = BudgetHeader::forEntity($legalEntityId)
            ->forYear($budget->financialyear + 1)
            ->with(['budgetLines.months'])
            ->first();

        $accounts = CashbookAccount::query()
            ->where('legalentityid', $legalEntityId)
            ->where('isactive', 1)
            ->orderBy('accountname')
            ->get();

        $categories = $this->postingCategoriesForEntity($legalEntityId);

        $budgetSectionTotals = [];

        foreach ($lines as $line) {
            $typeCode = strtolower(trim((string) ($line->category?->categoryType?->typecode ?? 'other')));

            if (!isset($budgetSectionTotals[$typeCode])) {
                $budgetSectionTotals[$typeCode] = [
                    'label' => $line->category?->categoryType?->typename ?? ucfirst($typeCode),
                    'adopted' => array_fill(1, 12, 0.00),
                    'revised' => array_fill(1, 12, 0.00),
                    'actuals' => array_fill(1, 12, 0.00),
                ];
            }

            foreach ($line->months as $month) {
                $monthNo = (int) $month->monthno;

                $budgetSectionTotals[$typeCode]['adopted'][$monthNo] += (float) $month->adoptedamount;
                $budgetSectionTotals[$typeCode]['revised'][$monthNo] += (float) $month->effectiveRevisedAmount();
                $budgetSectionTotals[$typeCode]['actuals'][$monthNo] += (float) ($lineActuals[$line->id][$monthNo] ?? 0.00);
            }
        }

        /*
        * Gross activity totals:
        *
        * These are useful only where you want to show total budget activity
        * without treating payments as a cash outflow.
        *
        * Gross = Receipts + Payments + Transfers
        */
        $budgetOverallTotals = [
            'adopted' => array_fill(1, 12, 0.00),
            'revised' => array_fill(1, 12, 0.00),
            'actuals' => array_fill(1, 12, 0.00),
        ];

        /*
        * Net cash movement totals:
        *
        * Net movement = Receipts - Payments + Transfers
        *
        * This is the total that should be presented as the principal budget
        * total / expected cash movement / actual cash movement.
        */
        $budgetNetMovementTotals = [
            'adopted' => array_fill(1, 12, 0.00),
            'revised' => array_fill(1, 12, 0.00),
            'actuals' => array_fill(1, 12, 0.00),
        ];

        foreach ($budgetSectionTotals as $typeCode => $section) {
            /*
            * Normalise category type codes so variations such as "receipt",
            * "receipts", "income", "payment", and "payments" are handled
            * deliberately and consistently.
            */
            $normalisedTypeCode = strtolower(trim((string) $typeCode));

            $multiplier = match ($normalisedTypeCode) {
                /*
                * Cash coming in increases available cash.
                */
                'receipt',
                'receipts',
                'income',
                'incomes' => 1,

                /*
                * Cash paid out decreases available cash.
                *
                * Payment amounts are held/displayed as positive values, so they
                * must be subtracted when calculating a net cash movement total.
                */
                'payment',
                'payments',
                'expense',
                'expenses' => -1,

                /*
                * Transfers retain their recorded sign. A transfer in should be
                * positive; a transfer out should be negative.
                */
                'transfer',
                'transfers' => 1,

                /*
                * Do not silently treat unknown category types as receipts.
                * Zero means they do not distort the cash movement total until
                * their intended accounting treatment is defined.
                */
                default => 0,
            };

            foreach ($monthLabels as $monthNo => $label) {
                foreach (['adopted', 'revised', 'actuals'] as $field) {
                    $amount = (float) ($section[$field][$monthNo] ?? 0.00);

                    /*
                    * Gross activity: all displayed section values added together.
                    */
                    $budgetOverallTotals[$field][$monthNo] += $amount;

                    /*
                    * Net movement: receipts - payments + transfers.
                    */
                    $budgetNetMovementTotals[$field][$monthNo] += $multiplier * $amount;
                }
            }
        }

        $editLineId = $request->query('edit_line');
        $editField = $request->query('edit_field', 'adopted');
        $methodLineId = $request->query('line');
        $methodField = $request->query('field', 'adopted');

        return view('budgets.lines.index', compact(
            'budget',
            'lines',
            'nextYearHeader',
            'accounts',
            'categories',
            'monthLabels',
            'methodLineId',
            'methodField',
            'lineActuals',
            'editLineId',
            'editField',
            'budgetSectionTotals',
            'budgetOverallTotals',
            'budgetNetMovementTotals',
        ));
    }

    public function store(Request $request, BudgetHeader $budget)
    {
        $this->authoriseEntity($budget);
        abort_unless($budget->isDraft(), 403, 'Lines can only be added to draft budgets.');

        $validated = $request->validate([
            'accountid' => ['required', 'integer'],
            'categoryid' => ['required', 'integer'],
            'sortorder' => ['nullable', 'integer', 'min:0'],
        ]);

        $account = CashbookAccount::query()
            ->whereKey($validated['accountid'])
            ->where('legalentityid', $budget->legalentityid)
            ->where('isactive', 1)
            ->first();

        if (!$account) {
            throw ValidationException::withMessages([
                'accountid' => 'Choose an active account belonging to this legal entity.',
            ]);
        }

        $category = CashbookCategory::query()
            ->whereKey($validated['categoryid'])
            ->where('isactive', 1)
            ->where('allowposting', 1)
            ->where(function ($query) use ($budget) {
                $query->whereNull('legalentityid')
                    ->orWhere('legalentityid', $budget->legalentityid);
            })
            ->first();

        if (!$category) {
            throw ValidationException::withMessages([
                'categoryid' => 'Choose an active posting category available to this legal entity.',
            ]);
        }

        $exists = BudgetLine::query()
            ->where('budgetheaderid', $budget->id)
            ->where('accountid', $account->id)
            ->where('categoryid', $category->id)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'categoryid' => 'This account/category combination already exists on this budget.',
            ])->withInput();
        }

        DB::transaction(function () use ($budget, $account, $category, $validated) {
            $line = BudgetLine::create([
                'budgetheaderid' => $budget->id,
                'accountid' => $account->id,
                'categoryid' => $category->id,
                'sortorder' => $validated['sortorder'] ?? $category->sortorder,
            ]);

            foreach (self::FY_MONTHS as $monthNo) {
                BudgetLineMonth::create([
                    'budgetlineid' => $line->id,
                    'monthno' => $monthNo,
                    'adoptedamount' => 0.00,
                    'revisedamount' => null,
                    'revisedisactual' => false,
                ]);
            }
        });

        return redirect()
            ->route('cashbook.budgets.lines.index', $budget)
            ->with('success', 'Budget line added.');
    }

    public function updateMonths(Request $request, BudgetHeader $budget, BudgetLine $line)
    {
        $this->authoriseEntity($budget);
        abort_unless($line->budgetheaderid === $budget->id, 403);

        $field = $request->input('field', 'adopted');

        if ($field === 'adopted') {
            abort_unless($budget->isDraft(), 403, 'Adopted amounts can only be edited in a draft budget.');
        }

        if ($field === 'revised') {
            abort_unless($budget->isRevised(), 403, 'Revised amounts can only be edited while the budget is being revised.');
        }

        $validated = $request->validate([
            'amounts' => ['required', 'array', 'size:12'],
            'amounts.*' => ['required', 'numeric', 'between:-99999999.99,99999999.99'],
        ]);

        $dbField = $field === 'revised' ? 'revisedamount' : 'adoptedamount';

        DB::transaction(function () use ($line, $validated, $dbField) {
            foreach (self::FY_MONTHS as $index => $monthNo) {
                $changes = [
                    $dbField => $validated['amounts'][$index],
                ];

                if ($dbField === 'revisedamount') {
                    // A manual revision is no longer a value locked from actuals.
                    $changes['revisedisactual'] = false;
                }

                BudgetLineMonth::query()
                    ->where('budgetlineid', $line->id)
                    ->where('monthno', $monthNo)
                    ->update($changes);
            }
        });

        return redirect()
            ->route('cashbook.budgets.lines.index', $budget)
            ->with('success', 'Budget amounts saved.');
    }

    public function applyMethod(Request $request, BudgetHeader $budget, BudgetLine $line)
    {
        $this->authoriseEntity($budget);
        abort_unless($line->budgetheaderid === $budget->id, 403);

        $validated = $request->validate([
            'method' => ['required', 'in:equal,prior_year_adjusted,copy_prior_year_actuals,lock_actuals'],
            'field' => ['required', 'in:adopted,revised'],
            'total' => ['nullable', 'numeric', 'between:-99999999.99,99999999.99'],
            'percentage_adjustment' => ['nullable', 'numeric', 'between:-100,1000'],
            'lock_thru_month' => ['required_if:method,lock_actuals', 'nullable', 'integer', 'between:1,12'],
        ]);

        $field = $validated['field'];
        $dbField = $field === 'revised' ? 'revisedamount' : 'adoptedamount';

        if ($field === 'adopted') {
            abort_unless($budget->isDraft(), 403, 'Adopted preparation methods can only be used in a draft budget.');
        }

        if ($field === 'revised') {
            abort_unless($budget->isRevised(), 403, 'Revised preparation methods can only be used while the budget is being revised.');
        }

        if ($validated['method'] === 'lock_actuals') {
            abort_unless($dbField === 'revisedamount', 422, 'Lock Actuals is only available for a revised budget.');
        }

        if ($validated['method'] === 'equal' && !array_key_exists('total', $validated)) {
            throw ValidationException::withMessages([
                'total' => 'Enter an annual total for Equal allocation.',
            ]);
        }

        $line->load('months');

        DB::transaction(function () use ($validated, $line, $budget, $dbField) {
            switch ($validated['method']) {
                case 'equal':
                    $line->distributeEqual((float) $validated['total'], $dbField);

                    if ($dbField === 'revisedamount') {
                        $this->clearRevisedActualFlags($line);
                    }
                    break;

                case 'prior_year_adjusted':
                    $priorActuals = $this->getActualsForFinancialYear(
                        $budget->legalentityid,
                        $line->categoryid,
                        $budget->financialyear - 1
                    );

                    $priorTotal = array_sum($priorActuals);

                    if (abs($priorTotal) < 0.005) {
                        throw ValidationException::withMessages([
                            'method' => 'No usable prior-year actuals are available for this category. Use Equal allocation or enter monthly values manually.',
                        ]);
                    }

                    $percentageAdjustment = (float) ($validated['percentage_adjustment'] ?? 0.00);
                    $newTotal = round($priorTotal * (1 + ($percentageAdjustment / 100)), 2);

                    $line->distributeProportioned($newTotal, $priorActuals, $dbField);

                    if ($dbField === 'revisedamount') {
                        $this->clearRevisedActualFlags($line);
                    }
                    break;

                case 'copy_prior_year_actuals':
                    $priorActuals = $this->getActualsForFinancialYear(
                        $budget->legalentityid,
                        $line->categoryid,
                        $budget->financialyear - 1
                    );

                    if (abs(array_sum($priorActuals)) < 0.005) {
                        throw ValidationException::withMessages([
                            'method' => 'No usable prior-year actuals are available for this category.',
                        ]);
                    }

                    foreach ($line->months as $month) {
                        $month->$dbField = $priorActuals[$month->monthno] ?? 0.00;

                        if ($dbField === 'revisedamount') {
                            $month->revisedisactual = false;
                        }

                        $month->save();
                    }
                    break;

                case 'lock_actuals':
                    $thruMonth = (int) $validated['lock_thru_month'];
                    $actuals = $this->getActualsForFinancialYear(
                        $budget->legalentityid,
                        $line->categoryid,
                        $budget->financialyear
                    );

                    foreach ($line->months as $month) {
                        if ($month->monthno <= $thruMonth) {
                            $month->lockFromActual($actuals[$month->monthno] ?? 0.00);
                        }
                    }
                    break;
            }
        });

        return redirect()
            ->route('cashbook.budgets.lines.index', $budget)
            ->with('success', 'Preparation method applied.');
    }

    public function destroy(BudgetHeader $budget, BudgetLine $line)
    {
        $this->authoriseEntity($budget);
        abort_unless($line->budgetheaderid === $budget->id, 403);
        abort_unless($budget->isDraft(), 403, 'Lines can only be deleted from a draft budget.');

        $line->delete();

        return redirect()
            ->route('cashbook.budgets.lines.index', $budget)
            ->with('success', 'Budget line removed.');
    }

    private function getActualsForFinancialYear(
        int $legalEntityId,
        int $categoryId,
        int $financialYear
    ): array {
        $startDate = ($financialYear - 1) . '-07-01';
        $endDate = $financialYear . '-06-30';

        /*
         * Return raw category display values:
         * receipts positive, payments positive, transfers retain stored signs.
         */
       $rows = DB::table('cashbook_transaction_lines as line')
    ->join('cashbook_transactions as txn', 'txn.id', '=', 'line.transactionid')
    ->join('cashbook_categories as category', 'category.id', '=', 'line.categoryid')
    ->join('cashbook_category_types as categoryType', 'categoryType.id', '=', 'category.categorytypeid')
    ->where('txn.legalentityid', $legalEntityId)
    ->where('line.categoryid', $categoryId)
    ->whereBetween('txn.transactiondate', [$startDate, $endDate])
    ->selectRaw('
        MONTH(txn.transactiondate) AS cal_month,
        SUM(
            CASE
                WHEN LOWER(categoryType.typecode) = "transfer"
                     AND txn.transactionkind = "payment"
                    THEN -1 * line.amount
                ELSE line.amount
            END
        ) AS total
    ')
    ->groupBy(DB::raw('MONTH(txn.transactiondate)'))
    ->get();

        return $this->mapToFyMonths($rows);
    }

    private function mapToFyMonths(iterable $rows): array
    {
        $result = array_fill(1, 12, 0.00);

        foreach ($rows as $row) {
            $fyMonthNo = $this->calendarMonthToFyMonth((int) $row->cal_month);
            $result[$fyMonthNo] = (float) $row->total;
        }

        return $result;
    }

    private function calendarMonthToFyMonth(int $calendarMonth): int
    {
        return $calendarMonth >= 7
            ? $calendarMonth - 6
            : $calendarMonth + 6;
    }

    private function entityNetMovementMultiplier(string $typeCode): int
{
    return match (strtolower(trim($typeCode))) {
        'receipt',
        'receipts',
        'income',
        'incomes' => 1,

        'payment',
        'payments',
        'expense',
        'expenses' => -1,

        'transfer',
        'transfers' => 1,

        default => 0,
    };
}

    private function clearRevisedActualFlags(BudgetLine $line): void
    {
        BudgetLineMonth::query()
            ->where('budgetlineid', $line->id)
            ->update(['revisedisactual' => false]);
    }

    private function postingCategoriesForEntity(int $legalEntityId): Collection
    {
        return CashbookCategory::query()
            ->where('isactive', 1)
            ->where('allowposting', 1)
            ->where(function ($query) use ($legalEntityId) {
                $query->whereNull('legalentityid')
                    ->orWhere('legalentityid', $legalEntityId);
            })
            ->orderBy('sortorder')
            ->orderBy('categoryname')
            ->get();
    }

    private function orderBudgetLines(Collection $lines, int $legalEntityId): Collection
    {
        if ($lines->isEmpty()) {
            return $lines;
        }

        $categories = CashbookCategory::query()
            ->with('categoryType')
            ->where('isactive', 1)
            ->where(function ($query) use ($legalEntityId) {
                $query->whereNull('legalentityid')
                    ->orWhere('legalentityid', $legalEntityId);
            })
            ->get();

        $ordered = collect();

        /*
         * Existing budget lines retain account IDs for schema compatibility.
         * Ordering remains stable across account groups, while actuals and totals
         * are deliberately legal-entity and category based.
         */
        $accountGroups = $lines
            ->groupBy('accountid')
            ->sortBy(function (Collection $accountLines) {
                $account = $accountLines->first()->account;

                return sprintf(
                    '%06d|%s|%010d',
                    (int) ($account?->accountType?->sortorder ?? 999999),
                    mb_strtolower($account?->accountname ?? ''),
                    (int) ($account?->id ?? 0)
                );
            });

        foreach ($accountGroups as $accountLines) {
            $ordered = $ordered->concat(
                $this->orderAccountBudgetLinesByFullCategoryTree($accountLines, $categories)
            );
        }

        return $ordered->values();
    }

    private function budgetCategoryTypeRank(?string $typeCode): int
    {
        return match (strtolower(trim((string) $typeCode))) {
            'receipt' => 1,
            'payment' => 2,
            'transfer' => 3,
            default => 99,
        };
    }

    private function orderAccountBudgetLinesByFullCategoryTree(
        Collection $accountLines,
        Collection $allCategories
    ): Collection {
        $accountLinesByCategory = $accountLines->groupBy('categoryid');

        $categoriesByParent = $allCategories
            ->sortBy(function (CashbookCategory $category) {
                return sprintf(
                    '%03d|%010d|%s|%010d',
                    $this->budgetCategoryTypeRank($category->categoryType?->typecode),
                    (int) ($category->sortorder ?? 999999999),
                    mb_strtolower($category->categoryname ?? ''),
                    (int) $category->id
                );
            })
            ->groupBy(fn (CashbookCategory $category) => $category->parentcategoryid ?: 0);

        $ordered = collect();
        $visitedCategoryIds = collect();
        $addedLineIds = collect();

        $walk = function (int $parentCategoryId) use (
            &$walk,
            $categoriesByParent,
            $accountLinesByCategory,
            &$ordered,
            &$visitedCategoryIds,
            &$addedLineIds
        ): void {
            foreach ($categoriesByParent->get($parentCategoryId, collect()) as $category) {
                if ($visitedCategoryIds->contains($category->id)) {
                    continue;
                }

                $visitedCategoryIds->push($category->id);

                foreach ($accountLinesByCategory->get($category->id, collect())->sortBy('id') as $line) {
                    if (!$addedLineIds->contains($line->id)) {
                        $ordered->push($line);
                        $addedLineIds->push($line->id);
                    }
                }

                $walk($category->id);
            }
        };

        $walk(0);

        foreach ($accountLines->sortBy('id') as $line) {
            if (!$addedLineIds->contains($line->id)) {
                $ordered->push($line);
            }
        }

        return $ordered->values();
    }

    private function authoriseEntity(BudgetHeader $budget): void
    {
        abort_unless(
            (int) $budget->legalentityid === (int) session('active_legal_entity_id'),
            403
        );
    }

    
}
