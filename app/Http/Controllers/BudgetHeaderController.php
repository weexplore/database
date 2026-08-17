<?php

namespace App\Http\Controllers;

use App\Models\BudgetHeader;
use App\Models\LegalEntity;
use App\Models\CashbookAccount;
use App\Models\CashbookCategory;
use App\Models\BudgetLine;
use App\Models\BudgetLineMonth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BudgetHeaderController extends Controller
{
    // ---------------------------------------------------------------------
    // List all budget headers with optional legal entity filter
    // ---------------------------------------------------------------------

    public function index(Request $request)
    {
        // Use query parameter if supplied, otherwise fall back to session
        $selectedEntityId = $request->input('legalentityid');

        if ($selectedEntityId) {
            session(['active_legal_entity_id' => (int) $selectedEntityId]);
        } else {
            $selectedEntityId = session('active_legal_entity_id');
        }

        $query = BudgetHeader::query();

        if ($selectedEntityId) {
            $query->forEntity((int) $selectedEntityId);
        }

        $headers = $query
            ->withCount('budgetLines')
            ->orderByDesc('financialyear')
            ->get();

        $legalEntities = LegalEntity::orderBy('entityname')->get();

        return view('budgets.index', [
            'headers'            => $headers,
            'legalEntities'      => $legalEntities,
            'selectedEntityId'   => $selectedEntityId,
        ]);
    }

    // ---------------------------------------------------------------------
    // Show create form
    // ---------------------------------------------------------------------

    public function create()
    {
        $selectedEntityId = session('active_legal_entity_id');

        // Suggest next financial year as default (year in which June falls)
        $suggestedYear = now()->month >= 7 ? now()->year + 1 : now()->year;

        $existingYears = BudgetHeader::when($selectedEntityId, function ($q) use ($selectedEntityId) {
                $q->where('legalentityid', $selectedEntityId);
            })
            ->pluck('financialyear')
            ->toArray();

        $legalEntities = LegalEntity::orderBy('entityname')->get();
        $selectedEntityId = session('active_legal_entity_id');

        $accounts = CashbookAccount::where('legalentityid', $selectedEntityId)
            ->where('isactive', 1)
            ->orderBy('accountname')
            ->get();

        return view('budgets.create', [
            'suggestedYear'    => $suggestedYear,
            'existingYears'    => $existingYears,
            'legalEntities'    => $legalEntities,
            'selectedEntityId' => $selectedEntityId,
            'accounts'         => $accounts,
        ]);
    }

    // ---------------------------------------------------------------------
    // Store new budget header
    // ---------------------------------------------------------------------

    public function store(Request $request)
{
    $validated = $request->validate([
        'legalentityid'     => ['required', 'integer', 'exists:legal_entities,id'],
        'financialyear'     => [
            'required', 'integer', 'min:2000', 'max:2099',
            Rule::unique('budget_headers')->where(
                fn ($q) => $q->where('legalentityid', $request->input('legalentityid'))
            ),
        ],
        'default_accountid' => ['required', 'integer', 'exists:cashbook_accounts,id'],
        'preparedby'        => 'nullable|string|max:150',
        'adoptednotes'      => 'nullable|string',
        'revisednotes'      => 'nullable|string',
    ]);

    $header = BudgetHeader::create([
        'legalentityid' => $validated['legalentityid'],
        'financialyear' => $validated['financialyear'],
        'status'        => BudgetHeader::STATUS_DRAFT,
        'preparedby'    => $validated['preparedby'] ?? null,
        'adoptednotes'  => $validated['adoptednotes'] ?? null,
        'revisednotes'  => $validated['revisednotes'] ?? null,
    ]);

    // Remember current entity
    session(['active_legal_entity_id' => $header->legalentityid]);

    // Seed lines and months
    $categories = CashbookCategory::where('legalentityid', $header->legalentityid)
        ->where('isactive', 1)
        ->where('allowposting', 1)
        ->orderBy('categoryname')
        ->get();

    DB::transaction(function () use ($header, $categories, $validated) {
        foreach ($categories as $category) {
            $line = BudgetLine::create([
                'budgetheaderid' => $header->id,
                'accountid'      => $validated['default_accountid'],
                'categoryid'     => $category->id,
                'sortorder'      => $category->sortorder ?? null,
            ]);

            for ($m = 1; $m <= 12; $m++) {
                BudgetLineMonth::create([
                    'budgetlineid'  => $line->id,
                    'monthno'       => $m,
                    'adoptedamount' => 0.00,
                    'revisedamount' => null,
                ]);
            }
        }
    });

    return redirect()
        ->route('cashbook.budgets.lines.index', $header)
        ->with('success', "Budget {$header->year_label} created and seeded with categories.");
}

    // ---------------------------------------------------------------------
    // Edit / Update / Status transitions / Destroy (unchanged except
    // for removing strict session checks)
    // ---------------------------------------------------------------------

    public function edit(BudgetHeader $budget)
    {
        return view('budgets.edit', compact('budget'));
    }

    public function update(Request $request, BudgetHeader $budget)
    {
        $validated = $request->validate([
            'preparedby'   => 'nullable|string|max:150',
            'adoptednotes' => 'nullable|string',
            'revisednotes' => 'nullable|string',
        ]);

        $budget->update($validated);

        return redirect()
            ->route('cashbook.budgets.index')
            ->with('success', "Budget {$budget->year_label} updated.");
    }

    public function adopt(BudgetHeader $budget)
    {
        abort_unless($budget->isDraft(), 403, 'Only draft budgets can be adopted.');
        abort_if($budget->budgetLines()->count() === 0, 422, 'Cannot adopt a budget with no lines.');

        $budget->update([
            'status'      => BudgetHeader::STATUS_ADOPTED,
            'adopteddate' => now()->toDateString(),
        ]);

        return redirect()
            ->route('cashbook.budgets.index')
            ->with('success', "Budget {$budget->year_label} has been adopted and locked.");
    }

    public function revise(BudgetHeader $budget)
    {
        abort_unless($budget->isAdopted(), 403, 'Only adopted budgets can be revised.');

        $budget->update([
            'status'      => BudgetHeader::STATUS_REVISED,
            'reviseddate' => now()->toDateString(),
        ]);

        return redirect()
            ->route('cashbook.budgets.lines.index', $budget)
            ->with('success', "Budget {$budget->year_label} is now open for revision.");
    }

    public function close(BudgetHeader $budget)
    {
        abort_unless($budget->isAdopted() || $budget->isRevised(), 403, 'Only adopted or revised budgets can be closed.');

        $budget->update(['status' => BudgetHeader::STATUS_CLOSED]);

        return redirect()
            ->route('cashbook.budgets.index')
            ->with('success', "Budget {$budget->year_label} closed.");
    }

    public function reopen(BudgetHeader $budget)
    {
        abort_unless($budget->isAdopted(), 403, 'Only adopted budgets can be reopened to draft.');

        $budget->update([
            'status'      => BudgetHeader::STATUS_DRAFT,
            'adopteddate' => null,
        ]);

        return redirect()
            ->route('cashbook.budgets.index')
            ->with('success', "Budget {$budget->year_label} reopened as draft.");
    }

    public function destroy(BudgetHeader $budget)
    {
        abort_unless($budget->isDraft(), 403, 'Only draft budgets can be deleted.');

        $label = $budget->year_label;
        $budget->delete();

        return redirect()
            ->route('cashbook.budgets.index')
            ->with('success', "Budget {$label} deleted.");
    }
}
