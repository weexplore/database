<?php

namespace App\Http\Controllers;

use App\Models\CashbookAccount;
use App\Models\CashbookAccountType;
use App\Models\CashbookCategory;
use App\Models\LegalEntity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CashbookAccountController extends Controller
{
    public function index(Request $request): View
    {
        $legalEntities = LegalEntity::orderBy('entityname')->get();
        $accountTypes = CashbookAccountType::where('isactive', 1)->orderBy('sortorder')->orderBy('typename')->get();

        $cashbookAccounts = CashbookAccount::query()
            ->select('cashbook_accounts.*')
            ->leftJoin('legal_entities', 'cashbook_accounts.legalentityid', '=', 'legal_entities.id')
            ->with(['legalEntity', 'accountType'])
            ->when($request->filled('legalentityid'), fn ($query) => $query->where('cashbook_accounts.legalentityid', $request->integer('legalentityid')))
            ->when($request->filled('accounttypeid'), fn ($query) => $query->where('cashbook_accounts.accounttypeid', $request->integer('accounttypeid')))
            ->when($request->filled('isactive'), fn ($query) => $query->where('cashbook_accounts.isactive', $request->boolean('isactive')))
            ->orderBy('legal_entities.entityname')
            ->orderBy('cashbook_accounts.accountname')
            ->paginate(25)
            ->withQueryString();

        return view('cashbookaccounts.index', compact('cashbookAccounts', 'legalEntities', 'accountTypes'));
    }

    public function create(): View
{
    $legalEntities = LegalEntity::where('isactive', 1)->orderBy('entityname')->get();
    $accountTypes = CashbookAccountType::where('isactive', 1)->orderBy('sortorder')->orderBy('typename')->get();
    $categories = CashbookCategory::with('categoryType')
        ->where('isactive', 1)
        ->orderBy('categoryname')
        ->get();

    return view('cashbookaccounts.edit', [
        'cashbookAccount' => new CashbookAccount(),
        'legalEntities' => $legalEntities,
        'accountTypes' => $accountTypes,
        'categories' => $categories,
    ]);
}

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateAccount($request);

        CashbookAccount::create($validated);

        return redirect()
            ->route('cashbook-accounts.index')
            ->with('success', 'Cashbook account added successfully.');
    }

    public function edit(CashbookAccount $cashbookAccount): View
{
    $legalEntities = LegalEntity::where('isactive', 1)->orderBy('entityname')->get();
    $accountTypes = CashbookAccountType::where('isactive', 1)->orderBy('sortorder')->orderBy('typename')->get();
    $categories = CashbookCategory::with('categoryType')
        ->where('isactive', 1)
        ->orderBy('categoryname')
        ->get();

    return view('cashbookaccounts.edit', compact('cashbookAccount', 'legalEntities', 'accountTypes', 'categories'));
}

    
    public function update(Request $request, CashbookAccount $cashbookAccount): RedirectResponse
    {
        $validated = $this->validateAccount($request, $cashbookAccount);

        $cashbookAccount->update($validated);

        return redirect()
            ->route('cashbook-accounts.index')
            ->with('success', 'Cashbook account updated successfully.');
    }

    public function destroy(CashbookAccount $cashbookAccount): RedirectResponse
    {
        $cashbookAccount->delete();

        return redirect()
            ->route('cashbook-accounts.index')
            ->with('success', 'Cashbook account deleted successfully.');
    }

    private function validateAccount(Request $request, ?CashbookAccount $cashbookAccount = null): array
    {
        $accountId = $cashbookAccount?->id;
        $legalEntityId = $request->integer('legalentityid');

        return $request->validate([
            'legalentityid' => ['required', 'integer', 'exists:legal_entities,id'],
            'accounttypeid' => ['required', 'integer', 'exists:cashbook_account_types,id'],
            'accountcode' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('cashbook_accounts', 'accountcode')
                    ->where(fn ($query) => $query->where('legalentityid', $legalEntityId))
                    ->ignore($accountId),
            ],
            'accountname' => [
                'required',
                'string',
                'max:150',
                Rule::unique('cashbook_accounts', 'accountname')
                    ->where(fn ($query) => $query->where('legalentityid', $legalEntityId))
                    ->ignore($accountId),
            ],
            'institutionname' => ['nullable', 'string', 'max:150'],
            'accountnumbermasked' => ['nullable', 'string', 'max:50'],
            'currencycode' => ['required', 'string', 'size:3'],
            'openingbalance' => ['nullable', 'numeric'],
            'openingbalancedate' => ['nullable', 'date'],
            'includeincashreporting' => ['sometimes', 'boolean'],
            'isreconcilable' => ['sometimes', 'boolean'],
            'isactive' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string'],
            'defaultunallocatedreceiptcategoryid' => ['nullable', 'integer', 'exists:cashbook_categories,id'],
            'defaultunallocatedpaymentcategoryid' => ['nullable', 'integer', 'exists:cashbook_categories,id'],
        ]);
    }
}
