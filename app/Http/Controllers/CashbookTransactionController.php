<?php

namespace App\Http\Controllers;

use App\Models\CashbookAccount;
use App\Models\CashbookCategory;
use App\Models\CashbookTransaction;
use App\Models\LegalEntity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CashbookTransactionController extends Controller
{
    public function index(Request $request): View
    {
            $accounts = CashbookAccount::with('legalEntity')
        ->orderBy('accountname')
        ->get();

    $categories = $this->transactionCategories();

    $selectedAccountId = $request->filled('accountid')
        ? (int) $request->input('accountid')
        : null;

    $transactionKind = $request->filled('transactionkind')
        ? trim((string) $request->input('transactionkind'))
        : null;

    $dateFrom = $request->filled('date_from')
        ? $request->input('date_from')
        : null;

    $dateTo = $request->filled('date_to')
        ? $request->input('date_to')
        : null;

    $categorySearch = $request->filled('category_search')
        ? trim((string) $request->input('category_search'))
        : null;

    $selectedAccount = $selectedAccountId
        ? $accounts->firstWhere('id', $selectedAccountId)
        : null;

    $openingBalance = (float) ($selectedAccount?->openingbalance ?? 0.00);
    $ledgerBalance = $openingBalance;
    $reconciledBalance = $openingBalance;
    $transactionBalances = [];

    $transactionsQuery = CashbookTransaction::query()
        ->with([
            'lines.category.categoryType',
            'transferAccount',
        ])
        ->when($selectedAccountId, fn ($query) => $query->where('accountid', $selectedAccountId))
        ->when($transactionKind, fn ($query) => $query->where('transactionkind', $transactionKind))
        ->when($dateFrom, fn ($query) => $query->whereDate('transactiondate', '>=', $dateFrom))
        ->when($dateTo, fn ($query) => $query->whereDate('transactiondate', '<=', $dateTo))
        ->when($categorySearch, function ($query) use ($categorySearch) {
            $query->whereHas('lines.category', function ($categoryQuery) use ($categorySearch) {
                $categoryQuery->where('categoryname', 'like', '%' . $categorySearch . '%');
            });
        })
        ->orderByDesc('transactiondate')
        ->orderByDesc('id');

    $transactions = $transactionsQuery
        ->paginate(50)
        ->withQueryString();

        if ($selectedAccountId) {
            $balanceTransactions = CashbookTransaction::query()
                ->where('accountid', $selectedAccountId)
                ->when($selectedAccount?->openingbalancedate, fn ($query) => $query->whereDate('transactiondate', '>=', $selectedAccount->openingbalancedate))
                ->when($transactionKind, fn ($query) => $query->where('transactionkind', $transactionKind))
                ->when($dateTo, fn ($query) => $query->whereDate('transactiondate', '<=', $dateTo))
                ->orderBy('transactiondate')
                ->orderBy('id')
                ->get(['id', 'transactionkind', 'amounttotal', 'isreconciled']);

            $runningBalance = $openingBalance;
            $reconciledRunningBalance = $openingBalance;

            foreach ($balanceTransactions as $balanceTransaction) {
                if ($balanceTransaction->transactionkind === 'receipt') {
                    $runningBalance += (float) $balanceTransaction->amounttotal;

                    if ($balanceTransaction->isreconciled) {
                        $reconciledRunningBalance += (float) $balanceTransaction->amounttotal;
                    }
                } elseif ($balanceTransaction->transactionkind === 'payment') {
                    $runningBalance -= (float) $balanceTransaction->amounttotal;

                    if ($balanceTransaction->isreconciled) {
                        $reconciledRunningBalance -= (float) $balanceTransaction->amounttotal;
                    }
                }

                $transactionBalances[$balanceTransaction->id] = $runningBalance;
            }

            $ledgerBalance = $runningBalance;
            $reconciledBalance = $reconciledRunningBalance;
        }

        $recentImportBatches = collect();

        if ($selectedAccountId) {
            $recentImportBatches = CashbookTransaction::query()
                ->where('accountid', $selectedAccountId)
                ->where('sourcetype', 'qif')
                ->whereNotNull('importbatchid')
                ->selectRaw('importbatchid, COUNT(*) as transaction_count, MIN(transactiondate) as min_date, MAX(transactiondate) as max_date, MAX(createdat) as imported_at')
                ->groupBy('importbatchid')
                ->orderByDesc('importbatchid')
                ->limit(10)
                ->get();
        }

    return view('cashbooktransactions.index', [
        'accounts' => $accounts,
        'categories' => $categories,
        'transactions' => $transactions,
        'selectedAccountId' => $selectedAccountId,
        'openingBalance' => $openingBalance,
        'ledgerBalance' => $ledgerBalance,
        'reconciledBalance' => $reconciledBalance,
        'transactionBalances' => $transactionBalances,
        'recentImportBatches' => $recentImportBatches,
    ]);
    }

    public function create(Request $request): View
    {
        $transaction = new CashbookTransaction();

        if ($request->filled('accountid')) {
            $account = CashbookAccount::find($request->integer('accountid'));

            if ($account) {
                $transaction->accountid = $account->id;
                $transaction->legalentityid = $account->legalentityid;
            }
        }

        return view('cashbooktransactions.edit', $this->editViewData($transaction));
    }

    public function quickStore(Request $request): RedirectResponse
    {
        $validated = $this->validateQuickTransaction($request);
        $warning = $this->categoryTypeWarning($validated);

        DB::transaction(function () use ($validated) {
            $transaction = CashbookTransaction::create(
                $this->transactionPayload($validated, false)
            );

            $transaction->lines()->create([
                'linenumber' => 1,
                'categoryid' => $validated['categoryid'],
                'linedescription' => $validated['linedescription'] ?? null,
                'amount' => $validated['amounttotal'],
                'taxcode' => $validated['taxcode'] ?? null,
                'notes' => $validated['linenotes'] ?? null,
            ]);
        });

        $redirect = $this->redirectToLedger($request)
            ->with('success', 'Transaction added successfully.');

        if ($warning) {
            $redirect->with('warning', $warning);
        }

        return $redirect;
    }

    public function quickUpdate(Request $request, CashbookTransaction $cashbookTransaction): RedirectResponse
    {
        $cashbookTransaction->load('lines');

        if ($cashbookTransaction->transactionkind === 'transfer' || $cashbookTransaction->lines->count() !== 1) {
            return redirect()
                ->route('cashbook-transactions.edit', $cashbookTransaction)
                ->with('error', 'This transaction must be edited on the full edit screen.');
        }

        $validated = $this->validateQuickTransaction($request, $cashbookTransaction);
        $warning = $this->categoryTypeWarning($validated);

        DB::transaction(function () use ($cashbookTransaction, $validated) {
            $cashbookTransaction->update(
                $this->transactionPayload($validated, false)
            );

            $line = $cashbookTransaction->lines->first();

            $line?->update([
                'categoryid' => $validated['categoryid'],
                'linedescription' => $validated['linedescription'] ?? null,
                'amount' => $validated['amounttotal'],
                'taxcode' => $validated['taxcode'] ?? null,
                'notes' => $validated['linenotes'] ?? null,
            ]);
        });

        $redirect = $this->redirectToLedger($request)
            ->with('success', 'Transaction updated successfully.');

        if ($warning) {
            $redirect->with('warning', $warning);
        }

        return $redirect;
    }

    public function bulkUpdate(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'rows' => ['required', 'array'],
        'rows.*.id' => ['required', 'integer', 'exists:cashbook_transactions,id'],
        'rows.*.legalentityid' => ['required', 'integer', 'exists:legal_entities,id'],
        'rows.*.accountid' => ['required', 'integer', 'exists:cashbook_accounts,id'],
        'rows.*.transactionkind' => ['required', 'string', Rule::in(['receipt', 'payment'])],
        'rows.*.transactiondate' => ['required', 'date'],
        'rows.*.referencenumber' => ['nullable', 'string', 'max:100'],
        'rows.*.payeename' => ['nullable', 'string', 'max:150'],
        'rows.*.description' => ['required', 'string', 'max:255'],
        'rows.*.amounttotal' => ['required', 'numeric', 'gt:0'],
        'rows.*.categoryid' => ['required', 'integer', 'exists:cashbook_categories,id'],
    ]);

    $warnings = [];

    DB::transaction(function () use ($validated, &$warnings) {
        foreach ($validated['rows'] as $row) {
            $transaction = CashbookTransaction::with('lines')->findOrFail($row['id']);

            if ($transaction->transactionkind === 'transfer' || $transaction->lines->count() !== 1) {
                continue;
            }

            $account = CashbookAccount::findOrFail($row['accountid']);
            $category = CashbookCategory::with('categoryType')->findOrFail($row['categoryid']);

            if ((int) $account->legalentityid !== (int) $row['legalentityid']) {
                throw ValidationException::withMessages([
                    "rows.{$row['id']}.accountid" => 'The selected account does not belong to the chosen legal entity.',
                ]);
            }

            if ($category->legalentityid !== null && (int) $category->legalentityid !== (int) $row['legalentityid']) {
                throw ValidationException::withMessages([
                    "rows.{$row['id']}.categoryid" => 'The selected category is not valid for the chosen legal entity.',
                ]);
            }

            if (! $category->allowposting) {
                throw ValidationException::withMessages([
                    "rows.{$row['id']}.categoryid" => 'The selected category does not allow posting.',
                ]);
            }

            $transaction->update([
                'legalentityid' => $row['legalentityid'],
                'accountid' => $row['accountid'],
                'transactionkind' => $row['transactionkind'],
                'transactiondate' => $row['transactiondate'],
                'referencenumber' => $row['referencenumber'] ?? null,
                'payeename' => $row['payeename'] ?? null,
                'description' => $row['description'],
                'amounttotal' => $row['amounttotal'],
            ]);

            $line = $transaction->lines->first();

            $line?->update([
                'categoryid' => $row['categoryid'],
                'amount' => $row['amounttotal'],
            ]);

            $categoryType = strtolower(trim($category->categoryType?->typecode ?? ''));
            $transactionKind = strtolower(trim($row['transactionkind'] ?? ''));

            if ($categoryType !== '' && $transactionKind !== '' && $categoryType !== 'transfer' && $categoryType !== $transactionKind) {
                $warnings[] = "Transaction {$transaction->id}: type and category differ.";
            }
        }
    });

    $redirect = $this->redirectToLedger($request)
        ->with('success', 'Cashbook transactions updated successfully.');

    if (! empty($warnings)) {
        $redirect->with('warning', implode(' ', $warnings));
    }

    return $redirect;
}

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTransaction($request);

        $transaction = DB::transaction(function () use ($validated) {
            $lines = $validated['lines'] ?? [];

            $transaction = CashbookTransaction::create(
                $this->transactionPayload($validated, true)
            );

            foreach ($lines as $index => $line) {
                $transaction->lines()->create([
                    'linenumber' => $index + 1,
                    'categoryid' => $line['categoryid'] ?? null,
                    'linedescription' => $line['linedescription'] ?? null,
                    'amount' => $line['amount'],
                    'taxcode' => $line['taxcode'] ?? null,
                    'notes' => $line['notes'] ?? null,
                ]);
            }

            return $transaction;
        });

        return redirect()
            ->route('cashbook-transactions.edit', $transaction)
            ->with('success', 'Cashbook transaction added successfully.');
    }

    public function edit(Request $request, CashbookTransaction $cashbookTransaction): View
    {
        $cashbookTransaction->load(['lines.category', 'legalEntity', 'account', 'transferAccount']);

        return view('cashbooktransactions.edit', array_merge(
            $this->editViewData($cashbookTransaction),
            [
                'returnAccountId' => $request->input('return_accountid'),
                'returnTransactionKind' => $request->input('return_transactionkind'),
                'returnDateFrom' => $request->input('return_date_from'),
                'returnDateTo' => $request->input('return_date_to'),
            ]
        ));
    }

    public function update(Request $request, CashbookTransaction $cashbookTransaction): RedirectResponse
    {
        $validated = $this->validateTransaction($request);

        DB::transaction(function () use ($cashbookTransaction, $validated) {
            $lines = $validated['lines'] ?? [];

            $cashbookTransaction->update(
                $this->transactionPayload($validated, true)
            );

            $cashbookTransaction->lines()->delete();

            foreach ($lines as $index => $line) {
                $cashbookTransaction->lines()->create([
                    'linenumber' => $index + 1,
                    'categoryid' => $line['categoryid'] ?? null,
                    'linedescription' => $line['linedescription'] ?? null,
                    'amount' => $line['amount'],
                    'taxcode' => $line['taxcode'] ?? null,
                    'notes' => $line['notes'] ?? null,
                ]);
            }
        });

        return $this->redirectToLedger($request)
            ->with('success', 'Cashbook transaction updated successfully.');
    }

    public function destroy(CashbookTransaction $cashbookTransaction): RedirectResponse
    {
        $cashbookTransaction->delete();

        return redirect()
            ->route('cashbook-transactions.index')
            ->with('success', 'Cashbook transaction deleted successfully.');
    }

    public function toggleReconciled(Request $request, CashbookTransaction $transaction): JsonResponse
    {
        $validated = $request->validate([
            'isreconciled' => ['required', 'boolean'],
        ]);

        $isReconciled = (bool) $validated['isreconciled'];

        $transaction->update([
            'isreconciled' => $isReconciled,
            'reconciledat' => $isReconciled
                ? ($transaction->reconciledat ?? now())
                : null,
        ]);

        return response()->json([
            'success' => true,
            'isreconciled' => (bool) $transaction->isreconciled,
        ]);
    }

    public function destroyBatch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'accountid' => ['required', 'integer', 'exists:cashbook_accounts,id'],
            'importbatchid' => ['required', 'integer'],
        ]);

        $accountId = (int) $validated['accountid'];
        $batchId = (int) $validated['importbatchid'];

        $deletedCount = 0;

        DB::transaction(function () use ($accountId, $batchId, &$deletedCount) {
            $transactions = CashbookTransaction::where('accountid', $accountId)
                ->where('sourcetype', 'qif')
                ->where('importbatchid', $batchId)
                ->get();

            $deletedCount = $transactions->count();

            foreach ($transactions as $transaction) {
                $transaction->lines()->delete();
                $transaction->delete();
            }
        });

        if ($deletedCount === 0) {
            return redirect()
                ->route('cashbook-transactions.index', ['accountid' => $accountId])
                ->with('error', 'No QIF transactions were found for that batch number on this account.');
        }

        return redirect()
            ->route('cashbook-transactions.index', ['accountid' => $accountId])
            ->with('success', "QIF import batch {$batchId} reversed and {$deletedCount} transactions deleted.");
    }

    private function editViewData(CashbookTransaction $cashbookTransaction): array
    {
        $legalEntities = LegalEntity::where('isactive', 1)->orderBy('entityname')->get();
        $accounts = CashbookAccount::where('isactive', 1)->orderBy('accountname')->get();
        $categories = CashbookCategory::with('categoryType')
            ->where('isactive', 1)
            ->orderBy('categoryname')
            ->get();

        $transactionKinds = [
            'receipt' => 'Receipt',
            'payment' => 'Payment',
            'transfer' => 'Transfer',
        ];

        return compact('cashbookTransaction', 'legalEntities', 'accounts', 'categories', 'transactionKinds');
    }

    private function validateQuickTransaction(Request $request, ?CashbookTransaction $cashbookTransaction = null): array
    {
        $request->merge([
            'isreconciled' => $request->boolean('isreconciled'),
        ]);

        $validated = $request->validate([
            'legalentityid' => ['required', 'integer', 'exists:legal_entities,id'],
            'accountid' => ['required', 'integer', 'exists:cashbook_accounts,id'],
            'transactionkind' => ['required', 'string', Rule::in(['receipt', 'payment', 'transfer'])],
            'transactiondate' => ['required', 'date'],
            'posteddate' => ['nullable', 'date'],
            'referencenumber' => ['nullable', 'string', 'max:100'],
            'payeename' => ['nullable', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:255'],
            'amounttotal' => ['required', 'numeric', 'gt:0'],
            'categoryid' => ['required', 'integer', 'exists:cashbook_categories,id'],
            'linedescription' => ['nullable', 'string', 'max:255'],
            'taxcode' => ['nullable', 'string', 'max:30'],
            'linenotes' => ['nullable', 'string'],
            'isreconciled' => ['required', 'boolean'],
            'reconciledat' => ['nullable', 'date'],
            'sourcetype' => ['nullable', 'string', 'max:30'],
            'externalsourcekey' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
        ]);

        $account = CashbookAccount::findOrFail($validated['accountid']);
        $category = CashbookCategory::with('categoryType')->findOrFail($validated['categoryid']);

        if ((int) $account->legalentityid !== (int) $validated['legalentityid']) {
            throw ValidationException::withMessages([
                'accountid' => 'The selected account does not belong to the chosen legal entity.',
            ]);
        }

        if ($category->legalentityid !== null && (int) $category->legalentityid !== (int) $validated['legalentityid']) {
            throw ValidationException::withMessages([
                'categoryid' => 'The selected category is not valid for the chosen legal entity.',
            ]);
        }

        if (! $category->allowposting) {
            throw ValidationException::withMessages([
                'categoryid' => 'The selected category does not allow posting.',
            ]);
        }

        return $validated;
    }

    private function validateTransaction(Request $request, ?CashbookTransaction $cashbookTransaction = null): array
    {
        $request->merge([
            'isreconciled' => $request->boolean('isreconciled'),
        ]);

        $validated = $request->validate([
            'legalentityid' => ['required', 'integer', 'exists:legal_entities,id'],
            'accountid' => ['required', 'integer', 'exists:cashbook_accounts,id'],
            'transferaccountid' => ['nullable', 'integer', 'exists:cashbook_accounts,id'],
            'transactionkind' => ['required', 'string', Rule::in(['receipt', 'payment', 'transfer'])],
            'transactiondate' => ['required', 'date'],
            'posteddate' => ['nullable', 'date'],
            'referencenumber' => ['nullable', 'string', 'max:100'],
            'payeename' => ['nullable', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:255'],
            'amounttotal' => ['required', 'numeric'],
            'isreconciled' => ['required', 'boolean'],
            'reconciledat' => ['nullable', 'date'],
            'sourcetype' => ['nullable', 'string', 'max:30'],
            'externalsourcekey' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
            'lines' => ['nullable', 'array'],
            'lines.*.categoryid' => ['nullable', 'integer', 'exists:cashbook_categories,id'],
            'lines.*.linedescription' => ['nullable', 'string', 'max:255'],
            'lines.*.amount' => ['required_with:lines', 'numeric'],
            'lines.*.taxcode' => ['nullable', 'string', 'max:30'],
            'lines.*.notes' => ['nullable', 'string'],
        ]);

        $account = CashbookAccount::findOrFail($validated['accountid']);
        $transferAccount = !empty($validated['transferaccountid'])
            ? CashbookAccount::findOrFail($validated['transferaccountid'])
            : null;

        if ((int) $account->legalentityid !== (int) $validated['legalentityid']) {
            throw ValidationException::withMessages([
                'accountid' => 'The selected account does not belong to the chosen legal entity.',
            ]);
        }

        if ($validated['transactionkind'] === 'transfer') {
            if (! $transferAccount) {
                throw ValidationException::withMessages([
                    'transferaccountid' => 'A transfer account is required for transfer transactions.',
                ]);
            }

            if ((int) $validated['accountid'] === (int) $validated['transferaccountid']) {
                throw ValidationException::withMessages([
                    'transferaccountid' => 'The transfer account must be different from the source account.',
                ]);
            }

            if ((int) $transferAccount->legalentityid !== (int) $validated['legalentityid']) {
                throw ValidationException::withMessages([
                    'transferaccountid' => 'The transfer account must belong to the same legal entity.',
                ]);
            }
        }

        if (in_array($validated['transactionkind'], ['receipt', 'payment'], true) && !empty($validated['transferaccountid'])) {
            throw ValidationException::withMessages([
                'transferaccountid' => 'Transfer account can only be used for transfer transactions.',
            ]);
        }

        $lineTotal = collect($validated['lines'] ?? [])->sum(
            fn ($line) => (float) $line['amount']
        );

        if (
            $validated['transactionkind'] !== 'transfer'
            && !empty($validated['lines'])
            && round($lineTotal, 2) !== round((float) $validated['amounttotal'], 2)
        ) {
            throw ValidationException::withMessages([
                'amounttotal' => 'Transaction total must equal the sum of the transaction lines.',
            ]);
        }

        foreach ($validated['lines'] ?? [] as $index => $line) {
            if (!empty($line['categoryid'])) {
                $category = CashbookCategory::findOrFail($line['categoryid']);

                if ($category->legalentityid !== null && (int) $category->legalentityid !== (int) $validated['legalentityid']) {
                    throw ValidationException::withMessages([
                        'lines.' . $index . '.categoryid' => 'The selected category is not valid for the chosen legal entity.',
                    ]);
                }
                if (! empty($line['categoryid'])) {
                    $category = CashbookCategory::findOrFail($line['categoryid']);

                    if (! $category->allowposting) {
                        throw ValidationException::withMessages([
                            'lines.' . $index . '.categoryid' => 'The selected category does not allow posting.',
                        ]);
                    }

                    if ($category->legalentityid !== null && (int) $category->legalentityid !== (int) $validated['legalentityid']) {
                        throw ValidationException::withMessages([
                            'lines.' . $index . '.categoryid' => 'The selected category is not valid for the chosen legal entity.',
                        ]);
                    }
                }
            }
        }

        return $validated;
    }

    private function transactionCategories()
{
    return CashbookCategory::query()
        ->with(['categoryType', 'parentCategory.parentCategory.parentCategory.parentCategory'])
        ->where('isactive', 1)
        ->where('allowposting', 1)
        ->get()
        ->map(function ($category) {
            $typeCode = strtolower(trim($category->categoryType?->typecode ?? ''));

            $category->type_sort = match ($typeCode) {
                'receipt' => 1,
                'payment' => 2,
                'transfer' => 3,
                default => 9,
            };

            $parts = [];
            $current = $category;

            while ($current) {
                array_unshift($parts, $current->categoryname);
                $current = $current->parentCategory;
            }

            $category->tree_label = implode(' > ', $parts);
            $category->display_label = trim(
                $category->tree_label . ' (' . ($category->categoryType?->typename ?: ucfirst($typeCode)) . ')'
            );

            return $category;
        })
        ->sortBy([
            ['type_sort', 'asc'],
            ['tree_label', 'asc'],
        ])
        ->values();
}


    private function transactionPayload(array $validated, bool $allowTransferAccount = true): array
    {
        $isReconciled = (bool) ($validated['isreconciled'] ?? false);

        return [
            'legalentityid' => $validated['legalentityid'],
            'accountid' => $validated['accountid'],
            'transferaccountid' => $allowTransferAccount
                ? ($validated['transferaccountid'] ?? null)
                : null,
            'transactionkind' => $validated['transactionkind'],
            'transactiondate' => $validated['transactiondate'],
            'posteddate' => $validated['posteddate'] ?? null,
            'referencenumber' => $validated['referencenumber'] ?? null,
            'payeename' => null,
            'description' => $validated['description'],
            'amounttotal' => $validated['amounttotal'],
            'isreconciled' => $isReconciled,
            'reconciledat' => $isReconciled
                ? ($validated['reconciledat'] ?? now())
                : null,
            'sourcetype' => $validated['sourcetype'] ?? null,
            'externalsourcekey' => $validated['externalsourcekey'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];
    }

    private function categoryTypeWarning(array $validated): ?string
    {
        $category = CashbookCategory::with('categoryType')
            ->find($validated['categoryid'] ?? null);

        if (! $category || ! $category->categoryType) {
            return null;
        }

        $categoryType = strtolower(trim($category->categoryType->typecode ?? ''));
        $transactionKind = strtolower(trim($validated['transactionkind'] ?? ''));

        if ($categoryType === '' || $transactionKind === '') {
            return null;
        }

        if ($categoryType === 'transfer') {
            return null;
        }

        if ($transactionKind !== $categoryType) {
            return 'Saved with warning: the transaction type and category type differ. This can be valid for refunds or reversals.';
        }

        return null;
    }

    private function redirectToLedger(Request $request): RedirectResponse
{
    return redirect()->route('cashbook-transactions.index', [
        'accountid' => $request->input('return_accountid'),
        'transactionkind' => $request->input('return_transactionkind'),
        'date_from' => $request->input('return_date_from'),
        'date_to' => $request->input('return_date_to'),
        'category_search' => $request->input('return_category_search'),
    ]);
}
}