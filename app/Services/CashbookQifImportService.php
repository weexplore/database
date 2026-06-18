<?php

namespace App\Services;

use App\Models\CashbookAccount;
use App\Models\CashbookTransaction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CashbookQifImportService
{
    public function __construct(
        private readonly QifParserService $qifParserService
    ) {
    }

    public function import(
            CashbookAccount $account,
            UploadedFile $uploadedFile,
            bool $ignoreBeforeLastImportDate = true,
            ?string $dateFrom = null,
            ?string $dateTo = null,
            bool $reverseImportOrder = false
        ): array {
        $account->loadMissing([
            'legalEntity',
            'defaultUnallocatedReceiptCategory.categoryType',
            'defaultUnallocatedPaymentCategory.categoryType',
        ]);

        $batchId = time();

        $parsed = $this->qifParserService->parse($uploadedFile);
        $entries = $parsed['entries'] ?? [];
        $parseWarnings = $parsed['warnings'] ?? [];

        $entries = array_values(array_filter($entries, function (array $entry) use ($dateFrom, $dateTo) {
            $transactionDate = $entry['transaction_date'] ?? null;

            if (empty($transactionDate)) {
                return false;
            }

            if ($dateFrom && $transactionDate < $dateFrom) {
                return false;
            }

            if ($dateTo && $transactionDate > $dateTo) {
                return false;
            }

            return true;
        }));

        if ($reverseImportOrder) {
            $entries = array_reverse($entries);
        }

        $result = [
            'inserted_count' => 0,
            'duplicate_count' => 0,
            'skipped_old_count' => 0,
            'needs_allocation_count' => 0,
            'parse_warning_count' => count($parseWarnings),
            'latest_transaction_date' => null,
        ];

        DB::transaction(function () use (
            $account,
            $entries,
            $ignoreBeforeLastImportDate,
            $batchId,
            &$result
        ) {
            foreach ($entries as $entry) {
                $transactionDate = $entry['transaction_date'] ?? null;
                $amount = (float) ($entry['amount'] ?? 0);

                if (empty($transactionDate) || $amount <= 0) {
                    continue;
                }

                //if (
                //    $ignoreBeforeLastImportDate
                //    && $account->lastqiftransactiondate
                //    && $transactionDate <= $account->lastqiftransactiondate->format('Y-m-d')
                //) {
                //    $result['skipped_old_count']++;
                //    continue;
                //}

                $transactionKind = $entry['transaction_kind'] ?? 'payment';
                $externalSourceKey = $this->buildExternalSourceKey($account->id, $entry);

                $duplicateOf = $this->findDuplicateTransaction(
                    accountId: $account->id,
                    transactionDate: $transactionDate,
                    amount: $amount,
                    externalSourceKey: $externalSourceKey,
                    payeeName: $entry['payee_name'] ?? null,
                    referenceNumber: $entry['reference_number'] ?? null,
                    description: $entry['description'] ?? null
                );

                $isDuplicateCandidate = $duplicateOf !== null;
                $needsAllocation = false;
                $categoryId = $this->resolveDefaultCategoryId($account, $transactionKind);

                if ($categoryId === null) {
                    throw new RuntimeException(
                        "No default unallocated category is configured on account {$account->accountname} for {$transactionKind} imports."
                    );
                }

                $notes = [];
                if (!empty($entry['memo'])) {
                    $notes[] = 'QIF memo: ' . $entry['memo'];
                }

                if (!empty($entry['category_hint'])) {
                    $notes[] = 'QIF category hint: ' . $entry['category_hint'];
                }

                if ($isDuplicateCandidate) {
                    $notes[] = 'Suspected duplicate of transaction ID ' . $duplicateOf->id . '.';
                }

                $needsAllocation = true;
                $notes[] = 'Imported from QIF and posted to default unallocated category.';

                $transaction = CashbookTransaction::create([
                    'legalentityid' => $account->legalentityid,
                    'accountid' => $account->id,
                    'transferaccountid' => null,
                    'transactionkind' => $transactionKind,
                    'transactiondate' => $transactionDate,
                    'posteddate' => $entry['posted_date'] ?? $transactionDate,
                    'referencenumber' => $entry['reference_number'] ?? null,
                    'payeename' => null,
                    'description' => $this->buildDescription(
                        $entry['description'] ?? 'Imported from QIF',
                        $isDuplicateCandidate
                    ),
                    'amounttotal' => number_format($amount, 2, '.', ''),
                    'isreconciled' => $isDuplicateCandidate ? 0 : 1,
                    'reconciledat' => $isDuplicateCandidate ? null : now(),
                    'sourcetype' => 'qif',
                    'externalsourcekey' => $externalSourceKey,
                    'importbatchid' => $batchId,
                    'isduplicatecandidate' => $isDuplicateCandidate,
                    'duplicateoftransactionid' => $duplicateOf?->id,
                    'needsallocation' => $needsAllocation,
                    'notes' => !empty($notes) ? implode("\n", $notes) : null,
                ]);

                $transaction->lines()->create([
                    'linenumber' => 1,
                    'categoryid' => $categoryId,
                    'linedescription' => $entry['description'] ?? null,
                    'amount' => number_format($amount, 2, '.', ''),
                    'taxcode' => null,
                    'notes' => 'Imported from QIF',
                ]);

                $result['inserted_count']++;

                if ($isDuplicateCandidate) {
                    $result['duplicate_count']++;
                }

                if ($needsAllocation) {
                    $result['needs_allocation_count']++;
                }

                if (
                    $result['latest_transaction_date'] === null
                    || $transactionDate > $result['latest_transaction_date']
                ) {
                    $result['latest_transaction_date'] = $transactionDate;
                }
            }

            if ($result['inserted_count'] > 0) {
                $account->update([
                    'lastqifimportedat' => now(),
                    'lastqiftransactiondate' => $result['latest_transaction_date'] ?? $account->lastqiftransactiondate,
                ]);
            }
        });

        return $result;
    }

    private function resolveDefaultCategoryId(CashbookAccount $account, string $transactionKind): ?int
    {
        return match ($transactionKind) {
            'receipt' => $account->defaultunallocatedreceiptcategoryid,
            'payment' => $account->defaultunallocatedpaymentcategoryid,
            default => null,
        };
    }

    private function buildExternalSourceKey(int $accountId, array $entry): string
    {
        $seed = implode('|', [
            'qif',
            $accountId,
            $entry['external_key_seed'] ?? '',
        ]);

        return Str::lower(hash('sha1', $seed));
    }

    private function buildDescription(string $description, bool $isDuplicateCandidate): string
    {
        $description = trim($description) !== '' ? trim($description) : 'Imported from QIF';

        if ($isDuplicateCandidate) {
            $suffix = ' [POSSIBLE DUPLICATE]';
            $availableLength = 255 - strlen($suffix);

            return mb_substr($description, 0, max(0, $availableLength)) . $suffix;
        }

        return mb_substr($description, 0, 255);
    }

    private function findDuplicateTransaction(
        int $accountId,
        string $transactionDate,
        float $amount,
        string $externalSourceKey,
        ?string $payeeName,
        ?string $referenceNumber,
        ?string $description
    ): ?CashbookTransaction {
        $duplicate = CashbookTransaction::query()
            ->where('accountid', $accountId)
            ->where('sourcetype', 'qif')
            ->where('externalsourcekey', $externalSourceKey)
            ->first();

        if ($duplicate) {
            return $duplicate;
        }

        return CashbookTransaction::query()
            ->where('accountid', $accountId)
            ->whereDate('transactiondate', $transactionDate)
            ->where('amounttotal', number_format($amount, 2, '.', ''))
            ->where(function ($query) use ($payeeName, $referenceNumber, $description) {
                $matched = false;

                if (!empty($payeeName)) {
                    $query->orWhere('payeename', $payeeName);
                    $matched = true;
                }

                if (!empty($referenceNumber)) {
                    $query->orWhere('referencenumber', $referenceNumber);
                    $matched = true;
                }

                if (!empty($description)) {
                    $query->orWhere('description', $description);
                    $matched = true;
                }

                if (! $matched) {
                    $query->orWhereNotNull('id');
                }
            })
            ->orderBy('id')
            ->first();
    }
}