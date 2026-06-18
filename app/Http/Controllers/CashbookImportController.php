<?php

namespace App\Http\Controllers;

use App\Models\CashbookAccount;
use App\Services\CashbookQifImportService;
use App\Models\CashbookTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashbookImportController extends Controller
{
    public function __construct(
        private readonly CashbookQifImportService $cashbookQifImportService
    ) {
    }

    public function showUpload(Request $request): View
    {
        $accounts = CashbookAccount::with([
                'legalEntity',
                'defaultUnallocatedReceiptCategory',
                'defaultUnallocatedPaymentCategory',
            ])
            ->where('isactive', 1)
            ->orderBy('accountname')
            ->get();

        $selectedAccountId = $request->filled('accountid')
            ? (int) $request->input('accountid')
            : null;

        $selectedAccount = $selectedAccountId
            ? $accounts->firstWhere('id', $selectedAccountId)
            : null;

        return view('cashbookimports.qif', [
            'accounts' => $accounts,
            'selectedAccount' => $selectedAccount,
            'selectedAccountId' => $selectedAccountId,
        ]);
    }

    public function destroyBatch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'accountid' => ['required', 'integer', 'exists:cashbook_accounts,id'],
            'importbatchid' => ['required', 'string'],
        ]);

        $accountId = $validated['accountid'];
        $batchId = $validated['importbatchid'];

        DB::transaction(function () use ($accountId, $batchId) {
            $transactions = CashbookTransaction::where('accountid', $accountId)
                ->where('sourcetype', 'qif')
                ->where('importbatchid', $batchId)
                ->get();

            foreach ($transactions as $transaction) {
                $transaction->lines()->delete();
                $transaction->delete();
            }
        });

        return redirect()
            ->route('cashbook-transactions.index', ['accountid' => $accountId])
            ->with('success', 'QIF import batch reversed and transactions deleted.');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'accountid' => ['required', 'integer', 'exists:cashbook_accounts,id'],
            'qif_file' => ['required', 'file', 'mimes:qif,txt', 'max:5120'],
            'ignore_before_last_import_date' => ['nullable', 'boolean'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'reverse_import_order' => ['nullable', 'boolean'],
        ]);
        $account = CashbookAccount::with([
                'legalEntity',
                'defaultUnallocatedReceiptCategory.categoryType',
                'defaultUnallocatedPaymentCategory.categoryType',
            ])
            ->findOrFail($validated['accountid']);

        $result = $this->cashbookQifImportService->import(
            account: $account,
            uploadedFile: $request->file('qif_file'),
            ignoreBeforeLastImportDate: $request->boolean('ignore_before_last_import_date', true),
            dateFrom: $validated['date_from'] ?? null,
            dateTo: $validated['date_to'] ?? null,
            reverseImportOrder: $request->boolean('reverse_import_order', false),
        );

        return redirect()
            ->route('cashbook-transactions.index', ['accountid' => $account->id])
            ->with('success', $this->buildSuccessMessage($result))
            ->with('last_import_batch_id', $result['import_batch_id'] ?? null);
    }

    private function buildSuccessMessage(array $result): string
    {
        $parts = [];

        $parts[] = ($result['inserted_count'] ?? 0) . ' imported';
        $parts[] = ($result['duplicate_count'] ?? 0) . ' suspected duplicates';
        $parts[] = ($result['skipped_old_count'] ?? 0) . ' skipped as older than import cutoff';
        $parts[] = ($result['needs_allocation_count'] ?? 0) . ' marked for allocation';

        return 'QIF import complete: ' . implode(', ', $parts) . '.';
    }

    private function buildWarningMessage(array $result): ?string
    {
        $warnings = [];

        if (($result['parse_warning_count'] ?? 0) > 0) {
            $warnings[] = ($result['parse_warning_count']) . ' rows had parse warnings';
        }

        if (($result['duplicate_count'] ?? 0) > 0) {
            $warnings[] = 'suspected duplicates were left unreconciled';
        }

        if (($result['needs_allocation_count'] ?? 0) > 0) {
            $warnings[] = 'some rows were posted to default unallocated categories';
        }

        return $warnings
            ? 'Import warnings: ' . implode('; ', $warnings) . '.'
            : null;
    }
}