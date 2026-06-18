<?php

namespace App\Http\Controllers;

use App\Models\CashbookAccount;
use App\Models\CashbookCategory;
use App\Models\CashbookTransaction;
use App\Models\CashbookTransactionLine;
use App\Models\LegalEntity;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CashbookReportController extends Controller
{
    public function index(Request $request)
    {
        $legalEntities = LegalEntity::orderBy('entityname')->get();

        $accounts = CashbookAccount::with('legalEntity')
            ->orderBy('accountname')
            ->get();

        $scope = $request->input('scope', 'legal-entity');
        $reportType = $request->input('report_type', 'balances');
        $legalEntityId = $request->filled('legal_entity_id') ? (int) $request->input('legal_entity_id') : null;
        $accountId = $request->filled('account_id') ? (int) $request->input('account_id') : null;
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to') ?: now()->toDateString();
        $reconciledOnly = $request->boolean('reconciled_only');
        $includeZeroBalances = $request->boolean('include_zero_balances');

        $selectedAccounts = $this->resolveSelectedAccounts(
            $accounts,
            $scope,
            $legalEntityId,
            $accountId
        );

        $reportRows = collect();

        if ($selectedAccounts->isNotEmpty()) {
            if ($reportType === 'balances') {
                $reportRows = $this->buildCategoryBalanceRows(
                    $selectedAccounts,
                    $dateFrom,
                    $dateTo,
                    $reconciledOnly,
                    $includeZeroBalances
                );
            } else {
                $reportRows = $this->buildCategoryTransactionRows(
                    $selectedAccounts,
                    $dateFrom,
                    $dateTo,
                    $reconciledOnly,
                    $includeZeroBalances
                );
            }
        }

        $reportTotals = $this->buildReportTotals(
            $selectedAccounts,
            $dateFrom,
            $dateTo,
            $reconciledOnly,
            $reportRows,
            $reportType
        );

        return view('cashbookreports.index', [
            'legalEntities' => $legalEntities,
            'accounts' => $accounts,
            'scope' => $scope,
            'reportType' => $reportType,
            'legalEntityId' => $legalEntityId,
            'accountId' => $accountId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'reconciledOnly' => $reconciledOnly,
            'includeZeroBalances' => $includeZeroBalances,
            'reportRows' => $reportRows,
            'reportTotals' => $reportTotals,
        ]);
    }

    private function resolveSelectedAccounts(
        Collection $accounts,
        string $scope,
        ?int $legalEntityId,
        ?int $accountId
    ): Collection {
        if ($scope === 'legal-entity' && $legalEntityId) {
            return $accounts->where('legalentityid', $legalEntityId)->values();
        }

        if ($scope === 'bank-account' && $accountId) {
            $account = $accounts->firstWhere('id', $accountId);
            return $account ? collect([$account]) : collect();
        }

        return collect();
    }

    private function categoryTypeSort(?string $typeCode, ?string $typeName = null): int
    {
        $value = strtolower(trim((string) ($typeCode ?: $typeName ?: '')));

        return match ($value) {
            'receipt' => 1,
            'payment' => 2,
            default => 99,
        };
    }

    

    private function buildCategoryBalanceRows(
    Collection $accounts,
    ?string $dateFrom,
    ?string $dateTo,
    bool $reconciledOnly,
    bool $includeZeroBalances
): Collection {
    $accountIds = $accounts->pluck('id')->values();

    $lines = CashbookTransactionLine::query()
        ->with([
            'category.categoryType',
            'category.parentCategory.parentCategory.parentCategory.parentCategory',
            'transaction',
        ])
        ->select('cashbook_transaction_lines.*')
        ->join('cashbook_transactions', 'cashbook_transactions.id', '=', 'cashbook_transaction_lines.transactionid')
        ->whereIn('cashbook_transactions.accountid', $accountIds)
        ->when($dateFrom, fn ($query) => $query->whereDate('cashbook_transactions.transactiondate', '>=', $dateFrom))
        ->when($dateTo, fn ($query) => $query->whereDate('cashbook_transactions.transactiondate', '<=', $dateTo))
        ->when($reconciledOnly, fn ($query) => $query->where('cashbook_transactions.isreconciled', 1))
        ->get();

$categories = CashbookCategory::query()
    ->with([
        'categoryType',
        'parentCategory.parentCategory.parentCategory.parentCategory',
    ])
    ->orderBy('parentcategoryid')
    ->orderBy('categoryname')
    ->get();

    $directSums = $lines
        ->groupBy('categoryid')
        ->map(function (Collection $categoryLines) {
            $receiptTotal = 0.00;
            $paymentTotal = 0.00;
            $transferTotal = 0.00;
            $transactionIds = [];

            foreach ($categoryLines as $line) {
                $amount = (float) ($line->amount ?? 0);
                $kind = strtolower(trim((string) ($line->transaction?->transactionkind ?? '')));
                $transactionId = $line->transactionid;

                if ($kind === 'receipt') {
                    $receiptTotal += $amount;
                } elseif ($kind === 'payment') {
                    $paymentTotal += $amount;
                } elseif ($kind === 'transfer') {
                    $transferTotal += $amount;
                }

                if ($transactionId) {
                    $transactionIds[$transactionId] = true;
                }
            }

            return [
                'direct_receipts_total' => round($receiptTotal, 2),
                'direct_payments_total' => round($paymentTotal, 2),
                'direct_transfers_total' => round($transferTotal, 2),
                'direct_net_total' => round($receiptTotal - $paymentTotal, 2),
                'transaction_count' => count($transactionIds),
            ];
        });

    $orderMap = $this->categoryOrderMap($accounts);

    $rowsById = $categories->mapWithKeys(function ($category) use ($directSums, $orderMap) {
        $sum = $directSums->get($category->id, [
            'direct_receipts_total' => 0.00,
            'direct_payments_total' => 0.00,
            'direct_transfers_total' => 0.00,
            'direct_net_total' => 0.00,
            'transaction_count' => 0,
        ]);

        $typeCode = strtolower(trim((string) ($category->categoryType?->typecode ?? '')));
        $typeName = $category->categoryType?->typename ?: ucfirst($category->categoryType?->typecode ?? '');

        return [
            $category->id => [
                'category_id' => $category->id,
                'parent_category_id' => $category->parentcategoryid,
                'category_name' => $category->categoryname,
                'category_type_code' => $typeCode,
                'category_type_name' => $typeName ?: 'Other',
                'category_type_sort' => $this->categoryTypeSort($typeCode, $typeName),
                'category_sequence' => $orderMap->get($category->id)['sequence'] ?? 999999,
                'category_depth' => $orderMap->get($category->id)['depth'] ?? 0,
                'category_tree_label' => $orderMap->get($category->id)['tree_label'] ?? $this->categoryTreeLabel($category),

                'direct_receipts_total' => (float) $sum['direct_receipts_total'],
                'direct_payments_total' => (float) $sum['direct_payments_total'],
                'direct_transfers_total' => (float) $sum['direct_transfers_total'],
                'direct_net_total' => (float) $sum['direct_net_total'],
                'transaction_count' => (int) $sum['transaction_count'],

                'rolled_receipts_total' => 0.00,
                'rolled_payments_total' => 0.00,
                'rolled_transfers_total' => 0.00,
                'rolled_net_total' => 0.00,
                'is_parent' => false,
            ],
        ];
    });

    $childrenByParent = $categories
    ->groupBy(fn ($category) => $category->parentcategoryid ?: 0)
    ->map(fn ($group) => $group->sortBy('categoryname')->pluck('id')->values());

    $rollup = function (int $categoryId) use (&$rollup, &$rowsById, $childrenByParent) {
        $row = $rowsById[$categoryId];
        $childIds = $childrenByParent->get($categoryId, collect());

        $rolledReceipts = (float) $row['direct_receipts_total'];
        $rolledPayments = (float) $row['direct_payments_total'];
        $rolledTransfers = (float) $row['direct_transfers_total'];
        $rolledNet = (float) $row['direct_net_total'];

        if ($childIds->isNotEmpty()) {
            $row['is_parent'] = true;

            foreach ($childIds as $childId) {
                $childRow = $rollup($childId);

                $rolledReceipts += (float) $childRow['rolled_receipts_total'];
                $rolledPayments += (float) $childRow['rolled_payments_total'];
                $rolledTransfers += (float) $childRow['rolled_transfers_total'];
                $rolledNet += (float) $childRow['rolled_net_total'];
            }
        }

        $row['rolled_receipts_total'] = round($rolledReceipts, 2);
        $row['rolled_payments_total'] = round($rolledPayments, 2);
        $row['rolled_transfers_total'] = round($rolledTransfers, 2);
        $row['rolled_net_total'] = round($rolledNet, 2);

        $rowsById[$categoryId] = $row;

        return $row;
    };

    foreach ($rowsById->keys() as $categoryId) {
        $rollup($categoryId);
    }

    $flatten = function ($parentId = 0) use (&$flatten, $childrenByParent, $rowsById) {
        $result = collect();

        foreach ($childrenByParent->get($parentId, collect()) as $categoryId) {
            $row = $rowsById[$categoryId];

            $hasAnyValues =
                abs((float) $row['rolled_receipts_total']) > 0.00001 ||
                abs((float) $row['rolled_payments_total']) > 0.00001 ||
                abs((float) $row['rolled_transfers_total']) > 0.00001 ||
                abs((float) $row['rolled_net_total']) > 0.00001 ||
                (int) $row['transaction_count'] > 0;

            $result->push($row);
            $result = $result->merge($flatten($categoryId));
        }

        return $result;
    };

    $rows = $flatten(0);

    if (! $includeZeroBalances) {
        $rows = $rows->filter(function (array $row) {
            return
                abs((float) $row['rolled_receipts_total']) > 0.00001 ||
                abs((float) $row['rolled_payments_total']) > 0.00001 ||
                abs((float) $row['rolled_transfers_total']) > 0.00001 ||
                abs((float) $row['rolled_net_total']) > 0.00001 ||
                (int) ($row['transaction_count'] ?? 0) > 0;
        })->values();
    }

    return $rows
        ->sortBy([
            ['category_type_sort', 'asc'],
            ['category_sequence', 'asc'],
        ])
        ->values();
}

    private function buildReportTotals(
    Collection $accounts,
    ?string $dateFrom,
    ?string $dateTo,
    bool $reconciledOnly,
    Collection $reportRows,
    string $reportType
): array {
    $openingBalanceAtStart = 0.00;
    $periodReceiptsTotal = 0.00;
    $periodPaymentsTotal = 0.00;
    $periodTransfersTotal = 0.00;
    $receiptCount = 0;
    $paymentCount = 0;
    $transferCount = 0;
    $reconciledBalanceDelta = 0.00;

    $hasDateFrom = ! empty($dateFrom);
    $accountIds = $accounts->pluck('id')->filter()->values();

    foreach ($accounts as $account) {
        $accountOpening = (float) ($account->openingbalance ?? 0.00);
        $openingDate = $account->openingbalancedate;

        if (! $hasDateFrom) {
            $accountOpeningAtStart = $accountOpening;
        } else {
            $priorTransactions = CashbookTransaction::query()
                ->where('accountid', $account->id)
                ->when($openingDate, fn ($query) => $query->whereDate('transactiondate', '>=', $openingDate))
                ->whereDate('transactiondate', '<', $dateFrom)
                ->get(['transactionkind', 'amounttotal']);

            $accountOpeningAtStart = $accountOpening;

            foreach ($priorTransactions as $transaction) {
                $amount = (float) $transaction->amounttotal;

                if ($transaction->transactionkind === 'receipt') {
                    $accountOpeningAtStart += $amount;
                } elseif ($transaction->transactionkind === 'payment') {
                    $accountOpeningAtStart -= $amount;
                }
            }
        }

        $openingBalanceAtStart += $accountOpeningAtStart;

        $periodTransactions = CashbookTransaction::query()
            ->where('accountid', $account->id)
            ->when($openingDate, fn ($query) => $query->whereDate('transactiondate', '>=', $openingDate))
            ->when($hasDateFrom, fn ($query) => $query->whereDate('transactiondate', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('transactiondate', '<=', $dateTo))
            ->when($reconciledOnly, fn ($query) => $query->where('isreconciled', 1))
            ->get(['transactionkind', 'amounttotal']);

        foreach ($periodTransactions as $transaction) {
            $amount = (float) $transaction->amounttotal;

            if ($transaction->transactionkind === 'receipt') {
                $reconciledBalanceDelta += $amount;
            } elseif ($transaction->transactionkind === 'payment') {
                $reconciledBalanceDelta -= $amount;
            }
        }
    }

    $periodLines = CashbookTransactionLine::query()
        ->with(['transaction', 'category.categoryType'])
        ->select('cashbook_transaction_lines.*')
        ->join('cashbook_transactions', 'cashbook_transactions.id', '=', 'cashbook_transaction_lines.transactionid')
        ->whereIn('cashbook_transactions.accountid', $accountIds)
        ->when($hasDateFrom, fn ($query) => $query->whereDate('cashbook_transactions.transactiondate', '>=', $dateFrom))
        ->when($dateTo, fn ($query) => $query->whereDate('cashbook_transactions.transactiondate', '<=', $dateTo))
        ->when($reconciledOnly, fn ($query) => $query->where('cashbook_transactions.isreconciled', 1))
        ->get();

    $receiptTransactionIds = [];
    $paymentTransactionIds = [];
    $transferTransactionIds = [];

    foreach ($periodLines as $line) {
        $amount = (float) ($line->amount ?? 0.00);
        $transactionKind = strtolower(trim((string) ($line->transaction?->transactionkind ?? '')));
        $categoryType = strtolower(trim((string) ($line->category?->categoryType?->typecode ?? '')));
        $transactionId = (int) ($line->transactionid ?? 0);

        if ($categoryType === 'receipt') {
            if ($transactionKind === 'receipt') {
                $periodReceiptsTotal += $amount;
            } elseif ($transactionKind === 'payment') {
                $periodReceiptsTotal -= $amount;
            }

            if ($transactionId) {
                $receiptTransactionIds[$transactionId] = true;
            }
        } elseif ($categoryType === 'payment') {
            if ($transactionKind === 'payment') {
                $periodPaymentsTotal += $amount;
            } elseif ($transactionKind === 'receipt') {
                $periodPaymentsTotal -= $amount;
            }

            if ($transactionId) {
                $paymentTransactionIds[$transactionId] = true;
            }
        } elseif ($categoryType === 'transfer') {
            if ($transactionKind === 'payment') {
                $periodTransfersTotal += $amount;
            } elseif ($transactionKind === 'receipt') {
                $periodTransfersTotal -= $amount;
            }

            if ($transactionId) {
                $transferTransactionIds[$transactionId] = true;
            }
        }
    }

    $periodReceiptsTotal = round($periodReceiptsTotal, 2);
    $periodPaymentsTotal = round($periodPaymentsTotal, 2);
    $periodTransfersTotal = round($periodTransfersTotal, 2);

    $receiptCount = count($receiptTransactionIds);
    $paymentCount = count($paymentTransactionIds);
    $transferCount = count($transferTransactionIds);

    $ledgerBalanceAtEnd = $openingBalanceAtStart + $reconciledBalanceDelta;

    return [
        'opening_balance' => round($openingBalanceAtStart, 2),
        'receipts_total' => $periodReceiptsTotal,
        'payments_total' => $periodPaymentsTotal,
        'transfers_total' => $periodTransfersTotal,
        'net_movement' => round($periodReceiptsTotal - $periodPaymentsTotal, 2),
        'ledger_balance' => round($ledgerBalanceAtEnd, 2),
        'reconciled_balance' => $reconciledOnly
            ? round($ledgerBalanceAtEnd, 2)
            : round($openingBalanceAtStart + $reconciledBalanceDelta, 2),
        'receipt_count' => $receiptCount,
        'payment_count' => $paymentCount,
        'transfer_count' => $transferCount,
        'transaction_count' => $reportType === 'balances'
            ? (int) $reportRows->sum('transaction_count')
            : (int) $reportRows->pluck('transaction_id')->filter()->unique()->count(),
    ];
}

private function categoryTreeLabel(?CashbookCategory $category): string
{
    if (! $category) {
        return 'Uncategorised';
    }

    $parts = [];
    $current = $category;
    $guard = 0;

    while ($current && $guard < 20) {
        array_unshift($parts, trim((string) $current->categoryname));
        $current = $current->parentCategory;
        $guard++;
    }

    return implode(' > ', array_filter($parts));
}

private function categoryTreeSortKey(?CashbookCategory $category): string
{
    if (! $category) {
        return '999999';
    }

    $segments = [];
    $current = $category;
    $guard = 0;

    while ($current && $guard < 20) {
        $sort = (int) ($current->sortorder ?? 0);
        $id = (int) ($current->id ?? 0);

        array_unshift($segments, str_pad((string) $sort, 6, '0', STR_PAD_LEFT) . '-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT));

        $current = $current->parentCategory;
        $guard++;
    }

    return implode('.', $segments);
}

private function orderedCategoriesForReport(Collection $categoryIds): Collection
{
    $categories = CashbookCategory::with(['categoryType', 'childCategories'])
        ->whereIn('id', $categoryIds->filter()->unique()->values())
        ->get();

    $byParent = $categories
        ->sortBy([
            ['sortorder', 'asc'],
            ['categoryname', 'asc'],
            ['id', 'asc'],
        ])
        ->groupBy(fn ($category) => $category->parentcategoryid ?: 0);

    $ordered = collect();

    $walk = function ($parentId, $depth = 0) use (&$walk, $byParent, &$ordered) {
        foreach ($byParent->get($parentId, collect()) as $category) {
            $ordered->push([
                'id' => $category->id,
                'depth' => $depth,
                'tree_label' => $this->categoryTreeLabel($category),
            ]);

            $walk($category->id, $depth + 1);
        }
    };

    $walk(0);

    return $ordered->values();
}

private function categoryOrderMap(Collection $accounts): Collection
{
    $legalEntityIds = $accounts
        ->pluck('legalentityid')
        ->filter()
        ->unique()
        ->values();

    $categories = CashbookCategory::query()
        ->with(['categoryType'])
        ->where('isactive', 1)
        ->where(function ($query) use ($legalEntityIds) {
            $query->whereNull('legalentityid')
                ->orWhereIn('legalentityid', $legalEntityIds);
        })
        ->orderByRaw('COALESCE(parentcategoryid, 0)')
        ->orderByRaw('COALESCE(sortorder, 999999)')
        ->orderBy('categoryname')
        ->orderBy('id')
        ->get();

    $byParent = $categories->groupBy(fn ($category) => (int) ($category->parentcategoryid ?? 0));

    $ordered = collect();

    $walk = function (int $parentId, int $depth = 0) use (&$walk, $byParent, &$ordered) {
        foreach ($byParent->get($parentId, collect()) as $category) {
            $ordered->push([
                'id' => (int) $category->id,
                'sequence' => $ordered->count() + 1,
                'depth' => $depth,
                'tree_label' => $this->categoryTreeLabelFromMap($category, $byParent),
            ]);

            $walk((int) $category->id, $depth + 1);
        }
    };

    $walk(0);

    return $ordered->keyBy('id');
}

private function categoryTreeLabelFromMap(CashbookCategory $category, Collection $byParent): string
{
    $all = $byParent->flatten(1)->keyBy('id');

    $parts = [];
    $current = $category;
    $guard = 0;

    while ($current && $guard < 20) {
        array_unshift($parts, trim((string) $current->categoryname));
        $current = $current->parentcategoryid
            ? $all->get((int) $current->parentcategoryid)
            : null;
        $guard++;
    }

    return implode(' > ', array_filter($parts));
}


}