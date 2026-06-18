<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class QifParserService
{
    public function parse(UploadedFile $uploadedFile): array
    {
        $content = file_get_contents($uploadedFile->getRealPath());

        if ($content === false) {
            throw new RuntimeException('Could not read the uploaded QIF file.');
        }

        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $lines = explode("\n", $content);

        $entries = [];
        $current = [];
        $currentType = null;
        $warnings = [];

        foreach ($lines as $lineNumber => $rawLine) {
            $line = trim($rawLine);

            if ($line === '') {
                continue;
            }

            if (str_starts_with($line, '!Type:')) {
                $currentType = trim(substr($line, 6));
                continue;
            }

            if ($line === '^') {
                if (!empty($current)) {
                    $entry = $this->normaliseEntry($current, $currentType, $lineNumber + 1);

                    if (!empty($entry['warning'])) {
                        $warnings[] = $entry['warning'];
                    }

                    $entries[] = $entry;
                    $current = [];
                }

                continue;
            }

            $fieldCode = substr($line, 0, 1);
            $value = trim(substr($line, 1));

            $current[$fieldCode][] = $value;
        }

        if (!empty($current)) {
            $entry = $this->normaliseEntry($current, $currentType, count($lines));

            if (!empty($entry['warning'])) {
                $warnings[] = $entry['warning'];
            }

            $entries[] = $entry;
        }

        return [
            'entries' => $entries,
            'warnings' => $warnings,
        ];
    }

    private function normaliseEntry(array $rawEntry, ?string $currentType, int $lineNumber): array
{
    $rawDate = $this->firstValue($rawEntry, 'D');
    $rawAmount = $this->firstValue($rawEntry, 'T');
    $rawPayee = $this->firstValue($rawEntry, 'P');
    $rawMemo = $this->firstValue($rawEntry, 'M');
    $rawReference = $this->firstValue($rawEntry, 'N');
    $rawAddress = $rawEntry['A'] ?? [];
    $rawCategory = $this->firstValue($rawEntry, 'L');
    $rawNumber = $this->firstValue($rawEntry, 'N');
    $rawCleared = $this->firstValue($rawEntry, 'C');

    $transactionDate = $this->parseDate($rawDate);
    $signedAmount = $this->parseAmount($rawAmount);

    $descriptionParts = array_values(array_filter([
        $rawPayee,
        $rawMemo,
        !empty($rawAddress) ? implode(', ', $rawAddress) : null,
    ], fn ($value) => filled($value)));

    $description = trim(implode(' | ', $descriptionParts));

    if ($description === '') {
        $description = 'Imported from QIF';
    }

    $transactionKind = $signedAmount >= 0 ? 'receipt' : 'payment';
    $absoluteAmount = abs($signedAmount);

    $warning = null;

    if ($transactionDate === null) {
        $warning = 'Could not parse transaction date near line ' . $lineNumber . '.';
    } elseif ($absoluteAmount == 0.0) {
        $warning = 'Imported row with zero amount near line ' . $lineNumber . '.';
    }

    return [
        'source_type' => $currentType,
        'transaction_date' => $transactionDate?->format('Y-m-d'),
        'posted_date' => $transactionDate?->format('Y-m-d'),
        'transaction_kind' => $transactionKind,
        'signed_amount' => round($signedAmount, 2),
        'amount' => round($absoluteAmount, 2),
        'payee_name' => null,
        'description' => $description,
        'reference_number' => $rawReference ?: $rawNumber,
        'memo' => $rawMemo,
        'category_hint' => $rawCategory,
        'cleared_status' => $rawCleared,
        'external_key_seed' => implode('|', [
            $transactionDate?->format('Y-m-d') ?? '',
            number_format($absoluteAmount, 2, '.', ''),
            mb_strtolower(trim((string) ($rawPayee ?? ''))),
            mb_strtolower(trim((string) ($rawMemo ?? ''))),
            mb_strtolower(trim((string) ($rawReference ?? $rawNumber ?? ''))),
        ]),
        'warning' => $warning,
    ];
}

    private function firstValue(array $rawEntry, string $key): ?string
    {
        return isset($rawEntry[$key][0]) ? trim((string) $rawEntry[$key][0]) : null;
    }

    private function parseAmount(?string $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        $normalised = str_replace(',', '', $value);

        return (float) $normalised;
    }

    private function parseDate(?string $value): ?Carbon
{
    if ($value === null || trim($value) === '') {
        return null;
    }

    $value = trim($value);
    $value = str_replace(["'", ".", "-"], "/", $value);
    $value = preg_replace('/\s+/', '', $value);

    if (! is_string($value) || $value === '') {
        return null;
    }

    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{2}|\d{4})$/', $value, $matches)) {
        $first = (int) $matches[1];
        $second = (int) $matches[2];
        $yearRaw = $matches[3];
        $year = (int) $yearRaw;

        if (strlen($yearRaw) === 2) {
            $year += 2000;
        }

        $day = $first;
        $month = $second;

        if (checkdate($month, $day, $year)) {
            return Carbon::create($year, $month, $day, 0, 0, 0)->startOfDay();
        }

        $day = $second;
        $month = $first;

        if (checkdate($month, $day, $year)) {
            return Carbon::create($year, $month, $day, 0, 0, 0)->startOfDay();
        }

        return null;
    }

    if (preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/', $value, $matches)) {
        $year = (int) $matches[1];
        $month = (int) $matches[2];
        $day = (int) $matches[3];

        if (checkdate($month, $day, $year)) {
            return Carbon::create($year, $month, $day, 0, 0, 0)->startOfDay();
        }

        return null;
    }

    try {
        return Carbon::parse($value)->startOfDay();
    } catch (\Throwable $e) {
        return null;
    }
}
}