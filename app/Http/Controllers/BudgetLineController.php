<?php

namespace App\Http\Controllers;

use App\Models\BudgetHeader;
use App\Models\BudgetLine;
use App\Models\BudgetLineMonth;
use App\Models\CashbookAccount;
use App\Models\CashbookCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\CashbookTransaction;


class BudgetLineController extends Controller
{
    // FY month sequence: 1=Jul ... 12=Jun
    private const FY_MONTHS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

    // -------------------------------------------------------------------------
    // Main workflow page — all lines with month grid
    // -------------------------------------------------------------------------

public function index(Request $request, BudgetHeader $budget)
{
    session(['active_legal_entity_id' => $budget->legalentityid]);
    $this->authoriseEntity($budget);

    $legalEntityId = session('active_legal_entity_id');

    $lines = $budget->budgetLines()
        ->with(['account', 'category', 'months'])
        ->orderBy('sortorder')
        ->get();

    // Financial year date range: 1 Jul (year-1) to 30 Jun (year)
    $startOfYear = Carbon::create($budget->financialyear - 1, 7, 1)->startOfDay();
    $endOfYear   = Carbon::create($budget->financialyear, 6, 30)->endOfDay();

    // Adjust field names for your schema:
    // - 'transactiondate' is the date column
    // - 'amount' is the signed amount (income positive, expense negative) or net column you want to budget against
    $txns = CashbookTransaction::where('legalentityid', $legalEntityId)
        ->whereBetween('transactiondate', [$startOfYear, $endOfYear])
        ->get();

    $actualMap = [];

    foreach ($txns as $txn) {
        $month = Carbon::parse($txn->transactiondate)->month; // 1–12
        $key   = $txn->accountid . '|' . $txn->categoryid;

        // Replace 'amount' with your actual amount field or debit-credit logic
        $actualMap[$key][$month] = ($actualMap[$key][$month] ?? 0) + $txn->amount;
    }

    $lineActuals = [];

    foreach ($lines as $line) {
        $key = $line->accountid . '|' . $line->categoryid;

        if (!isset($actualMap[$key])) {
            continue;
        }

        foreach ($actualMap[$key] as $month => $total) {
            $lineActuals[$line->id][$month] = $total;
        }
    }

    $nextYearHeader = BudgetHeader::forEntity($legalEntityId)
        ->forYear($budget->financialyear + 1)
        ->with(['budgetLines.months'])
        ->first();

    $accounts = CashbookAccount::where('legalentityid', $legalEntityId)
        ->where('isactive', 1)
        ->orderBy('accountname')
        ->get();

    $categories = CashbookCategory::where('legalentityid', $legalEntityId)
        ->where('isactive', 1)
        ->where('allowposting', 1)
        ->orderBy('categoryname')
        ->get();

    $monthLabels   = BudgetLineMonth::MONTH_LABELS;
    $methodLineId  = $request->query('line');
    $methodField   = $request->query('field', 'adopted');

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
    ));
}

    // -------------------------------------------------------------------------
    // Store a new budget line and seed 12 blank month rows
    // -------------------------------------------------------------------------

    public function store(Request $request, BudgetHeader $budget)
    {
        $this->authoriseEntity($budget);
        abort_unless($budget->isDraft(), 403, 'Lines can only be added to draft budgets.');

        $validated = $request->validate([
            'accountid'  => 'required|integer|exists:cashbook_accounts,id',
            'categoryid' => 'required|integer|exists:cashbook_categories,id',
            'sortorder'  => 'nullable|integer|min:0',
        ]);

        $exists = BudgetLine::where('budgetheaderid', $budget->id)
            ->where('accountid', $validated['accountid'])
            ->where('categoryid', $validated['categoryid'])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'accountid' => 'This account/category combination already exists on this budget.'
            ]);
        }

        DB::transaction(function () use ($budget, $validated) {
            $line = BudgetLine::create([
                'budgetheaderid' => $budget->id,
                'accountid'      => $validated['accountid'],
                'categoryid'     => $validated['categoryid'],
                'sortorder'      => $validated['sortorder'] ?? null,
            ]);

            foreach (self::FY_MONTHS as $monthNo) {
                BudgetLineMonth::create([
                    'budgetlineid'  => $line->id,
                    'monthno'       => $monthNo,
                    'adoptedamount' => 0.00,
                    'revisedamount' => null,
                ]);
            }
        });

        return redirect()
            ->route('cashbook.budgets.lines.index', $budget)
            ->with('success', 'Budget line added.');
    }

    // -------------------------------------------------------------------------
    // Save month amounts for a single line
    // field: 'adopted' or 'revised'
    // -------------------------------------------------------------------------

    public function updateMonths(Request $request, BudgetHeader $budget, BudgetLine $line)
    {
        $this->authoriseEntity($budget);
        abort_unless($line->budgetheaderid === $budget->id, 403);

        $field = $request->input('field', 'adopted');

        if ($field === 'adopted' && $budget->isAdoptedLocked()) {
            return back()->withErrors(['field' => 'Adopted budget is locked and cannot be edited.']);
        }

        $validated = $request->validate([
            'amounts'   => 'required|array|size:12',
            'amounts.*' => 'required|numeric|min:0|max:99999999.99',
        ]);

        $dbField = $field === 'revised' ? 'revisedamount' : 'adoptedamount';

        DB::transaction(function () use ($line, $validated, $dbField) {
            foreach (self::FY_MONTHS as $index => $monthNo) {
                BudgetLineMonth::where('budgetlineid', $line->id)
                    ->where('monthno', $monthNo)
                    ->update([$dbField => $validated['amounts'][$index]]);
            }
        });

        return redirect()
            ->route('cashbook.budgets.lines.index', $budget)
            ->with('success', 'Budget amounts saved.');
    }

    // -------------------------------------------------------------------------
    // Apply a preparation method to a line
    // method: equal | proportioned | lock_actuals
    // field:  adopted | revised
    // -------------------------------------------------------------------------

    public function applyMethod(Request $request, BudgetHeader $budget, BudgetLine $line)
    {
        $this->authoriseEntity($budget);
        abort_unless($line->budgetheaderid === $budget->id, 403);

        $validated = $request->validate([
            'method'          => 'required|in:equal,proportioned,lock_actuals',
            'field'           => 'required|in:adopted,revised',
            'total'           => 'required_unless:method,lock_actuals|nullable|numeric|min:0',
            'lock_thru_month' => 'required_if:method,lock_actuals|nullable|integer|between:1,12',
        ]);

        $field   = $validated['field'];
        $dbField = $field === 'revised' ? 'revisedamount' : 'adoptedamount';

        if ($field === 'adopted' && $budget->isAdoptedLocked()) {
            return back()->withErrors(['field' => 'Adopted budget is locked.']);
        }

        $line->load('months');

        DB::transaction(function () use ($validated, $line, $budget, $dbField) {

            switch ($validated['method']) {

                case 'equal':
                    $line->distributeEqual((float) $validated['total'], $dbField);
                    break;

                case 'proportioned':
                    $priorActuals = $this->getPriorYearActuals(
                        $budget->legalentityid,
                        $line->accountid,
                        $line->categoryid,
                        $budget->financialyear - 1
                    );
                    $line->distributeProportioned(
                        (float) $validated['total'],
                        $priorActuals,
                        $dbField
                    );
                    break;

                case 'lock_actuals':
                    abort_unless($dbField === 'revisedamount', 422, 'Lock Actuals is only available for Revised Budget.');

                    $thruMonth = (int) $validated['lock_thru_month'];
                    $actuals   = $this->getCurrentYearActuals(
                        $budget->legalentityid,
                        $line->accountid,
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

    // -------------------------------------------------------------------------
    // Delete a budget line (month rows cascade automatically)
    // -------------------------------------------------------------------------

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

    // -------------------------------------------------------------------------
    // Private: fetch prior-year Cashbook actuals by FY month number
    // Returns array keyed 1..12 (1=Jul) => total amount
    // NOTE: Update the table/column names below to match your cashbook_transactions schema
    // -------------------------------------------------------------------------

    private function getPriorYearActuals(int $legalEntityId, int $accountId, int $categoryId, int $financialYear): array
    {
        $startDate = ($financialYear - 1) . '-07-01';
        $endDate   = $financialYear . '-06-30';

        $rows = DB::table('cashbook_transactions')
            ->where('legalentityid', $legalEntityId)
            ->where('accountid', $accountId)
            ->where('categoryid', $categoryId)
            ->whereBetween('transactiondate', [$startDate, $endDate])
            ->selectRaw('MONTH(transactiondate) AS cal_month, YEAR(transactiondate) AS cal_year, SUM(amount) AS total')
            ->groupBy('cal_year', 'cal_month')
            ->get();

        return $this->mapToFyMonths($rows, $financialYear);
    }

    private function getCurrentYearActuals(int $legalEntityId, int $accountId, int $categoryId, int $financialYear): array
    {
        $startDate = ($financialYear - 1) . '-07-01';
        $endDate   = $financialYear . '-06-30';

        $rows = DB::table('cashbook_transactions')
            ->where('legalentityid', $legalEntityId)
            ->where('accountid', $accountId)
            ->where('categoryid', $categoryId)
            ->whereBetween('transactiondate', [$startDate, $endDate])
            ->selectRaw('MONTH(transactiondate) AS cal_month, YEAR(transactiondate) AS cal_year, SUM(amount) AS total')
            ->groupBy('cal_year', 'cal_month')
            ->get();

        return $this->mapToFyMonths($rows, $financialYear);
    }

    /**
     * Map calendar {year, month, total} rows to FY month numbers (1=Jul ... 12=Jun).
     */
    private function mapToFyMonths($rows, int $financialYear): array
    {
        $result = array_fill(1, 12, 0.00);

        foreach ($rows as $row) {
            $fyMonthNo = $row->cal_month >= 7
                ? $row->cal_month - 6   // Jul=1 ... Dec=6
                : $row->cal_month + 6;  // Jan=7 ... Jun=12

            $result[$fyMonthNo] = (float) $row->total;
        }

        return $result;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function authoriseEntity(BudgetHeader $budget): void
    {
        abort_unless(
            $budget->legalentityid == session('active_legal_entity_id'),
            403
        );
    }
}
