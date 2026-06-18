<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Cashbook Reports
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Category-based cashbook reporting by legal entity or bank account.
                </p>
            </div>
            <a href="{{ route('cashbook-transactions.index') }}"
               class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                Back to transactions
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @if (session('success'))
                <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <div class="font-medium">Please fix the following:</div>
                    <ul class="mt-2 list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <form method="GET" action="{{ route('cashbook-reports.index') }}" class="p-4 space-y-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">
                        <div>
                            <label for="scope" class="block text-sm font-medium text-gray-700">Scope</label>
                            <select name="scope" id="scope" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                                <option value="legal-entity" @selected($scope === 'legal-entity')>Legal Entity</option>
                                <option value="bank-account" @selected($scope === 'bank-account')>Bank Account</option>
                            </select>
                        </div>

                        <div>
                            <label for="report_type" class="block text-sm font-medium text-gray-700">Report type</label>
                            <select name="report_type" id="report_type" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                                <option value="balances" @selected($reportType === 'balances')>Category Balances</option>
                                <option value="transactions-balances" @selected($reportType === 'transactions-balances')>Category Transaction Totals</option>
                            </select>
                        </div>

                        <div>
                            <label for="legal_entity_id" class="block text-sm font-medium text-gray-700">Legal entity</label>
                            <select name="legal_entity_id" id="legal_entity_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                                <option value="">All / Select</option>
                                @foreach ($legalEntities as $legalEntity)
                                    <option value="{{ $legalEntity->id }}" @selected($legalEntityId === $legalEntity->id)>
                                        {{ $legalEntity->entityname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="account_id" class="block text-sm font-medium text-gray-700">Bank account</label>
                            <select name="account_id" id="account_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                                <option value="">All / Select</option>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}" @selected($accountId === $account->id)>
                                        {{ $account->accountname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700">Date from</label>
                            <input type="date"
                                   name="date_from"
                                   id="date_from"
                                   value="{{ $dateFrom }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                        </div>

                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700">Date to / As at</label>
                            <input type="date"
                                   name="date_to"
                                   id="date_to"
                                   value="{{ $dateTo }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-6">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox"
                                   name="reconciled_only"
                                   value="1"
                                   class="rounded border-gray-300"
                                   @checked($reconciledOnly)>
                            <span>Reconciled only</span>
                        </label>

                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox"
                                   name="include_zero_balances"
                                   value="1"
                                   class="rounded border-gray-300"
                                   @checked($includeZeroBalances)>
                            <span>Include zero balances</span>
                        </label>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button type="submit"
                                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                            Run report
                        </button>

                        <a href="{{ route('cashbook-reports.index') }}"
                           class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-6">
                <div class="rounded-lg border border-gray-200 bg-white px-4 py-4">
                    <div class="text-sm font-medium text-gray-500">Opening total</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900">
                        {{ number_format((float) $reportTotals['opening_balance'], 2) }}
                    </div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white px-4 py-4">
                    <div class="text-sm font-medium text-gray-500">Receipts total</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900">
                        {{ number_format((float) $reportTotals['receipts_total'], 2) }}
                    </div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white px-4 py-4">
                    <div class="text-sm font-medium text-gray-500">Payments total</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900">
                        {{ number_format((float) $reportTotals['payments_total'], 2) }}
                    </div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white px-4 py-4">
                    <div class="text-sm font-medium text-gray-500">Transfers total</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900">
                        {{ number_format((float) ($reportTotals['transfers_total'] ?? 0), 2) }}
                    </div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white px-4 py-4">
                    <div class="text-sm font-medium text-gray-500">Net movement</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900">
                        {{ number_format((float) $reportTotals['net_movement'], 2) }}
                    </div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white px-4 py-4">
                    <div class="text-sm font-medium text-gray-500">Reconciled total</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900">
                        {{ number_format((float) $reportTotals['reconciled_balance'], 2) }}
                    </div>
                </div>
            </div>

            @if ($reportType === 'balances')
                {{-- Category Balances --}}
                <div class="rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Category Balances</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Category type</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Category</th>
                                    <th class="px-3 py-2 text-right font-semibold text-gray-700">Receipts</th>
                                    <th class="px-3 py-2 text-right font-semibold text-gray-700">Payments</th>
                                    <th class="px-3 py-2 text-right font-semibold text-gray-700">Net</th>
                                    <th class="px-3 py-2 text-right font-semibold text-gray-700">Transactions</th>
                                </tr>
                            </thead>

                            @php
                                $rows = collect($reportRows);

                                $receiptRows = $rows->where('category_type_code', 'receipt');
                                $paymentRows = $rows->where('category_type_code', 'payment');
                                $transferRows = $rows->where('category_type_code', 'transfer');

                                $receiptSectionReceipts = (float) $receiptRows->sum('direct_receipts_total');
                                $receiptSectionPayments = (float) $receiptRows->sum('direct_payments_total');
                                $receiptSectionTransfers = (float) $receiptRows->sum('direct_transfers_total');
                                $receiptSectionNet = $receiptSectionReceipts - $receiptSectionPayments - $receiptSectionTransfers;

                                $paymentSectionReceipts = (float) $paymentRows->sum('direct_receipts_total');
                                $paymentSectionPayments = (float) $paymentRows->sum('direct_payments_total');
                                $paymentSectionTransfers = (float) $paymentRows->sum('direct_transfers_total');
                                $paymentSectionNet = $paymentSectionReceipts - $paymentSectionPayments - $paymentSectionTransfers;

                                $transferSectionReceipts = (float) $transferRows->sum('direct_receipts_total');
                                $transferSectionPayments = (float) $transferRows->sum('direct_payments_total');
                                $transferSectionTransfers = (float) $transferRows->sum('direct_transfers_total');
                                $transferSectionNet = $transferSectionReceipts - $transferSectionPayments - $transferSectionTransfers;

                                $receiptsInserted = false;
                                $paymentsInserted = false;
                                $transfersInserted = false;

                                $lastType = null;
                            @endphp

                            <tbody class="divide-y divide-gray-100 bg-white">
                                @if ($rows->isEmpty())
                                    <tr>
                                        <td colspan="7" class="px-3 py-6 text-center text-sm text-gray-500">
                                            No category balances found for the selected filters.
                                        </td>
                                    </tr>
                                @else
                                    @foreach ($rows as $index => $row)
                                        @php
                                            $typeName = strtolower(trim($row['category_type_name'] ?? 'other'));
                                            $depth = (int) ($row['category_depth'] ?? 0);
                                            $isParent = !empty($row['is_parent']);
                                        @endphp

                                        {{-- Total receipts after last receipt row --}}
                                        @if ($typeName !== 'receipt' && $lastType === 'receipt' && ! $receiptsInserted)
                                            <tr class="bg-green-50 border-t border-gray-200">
                                                <td colspan="2" class="px-3 py-2 text-left font-semibold text-gray-900">
                                                    Total receipts
                                                </td>
                                                <td class="px-3 py-2 text-right font-semibold text-gray-900">
                                                    {{ number_format($receiptSectionReceipts, 2) }}
                                                </td>
                                                <td class="px-3 py-2 text-right font-semibold text-gray-900">
                                                    {{ number_format($receiptSectionPayments, 2) }}
                                                </td>
                                                <td class="px-3 py-2 text-right font-semibold text-gray-900">
                                                    {{ number_format($receiptSectionNet, 2) }}
                                                </td>
                                                <td class="px-3 py-2 text-right font-semibold text-gray-900"></td>
                                            </tr>
                                            @php $receiptsInserted = true; @endphp
                                        @endif

                                        {{-- Total payments after last payment row --}}
                                        @if ($typeName !== 'payment' && $lastType === 'payment' && ! $paymentsInserted)
                                            <tr class="bg-red-50 border-t border-gray-200">
                                                <td colspan="2" class="px-3 py-2 text-left font-semibold text-gray-900">
                                                    Total payments
                                                </td>
                                                <td class="px-3 py-2 text-right font-semibold text-gray-900">
                                                    {{ number_format($paymentSectionReceipts, 2) }}
                                                </td>
                                                <td class="px-3 py-2 text-right font-semibold text-gray-900">
                                                    {{ number_format($paymentSectionPayments, 2) }}
                                                </td>
                                                <td class="px-3 py-2 text-right font-semibold text-gray-900">
                                                    {{ number_format($paymentSectionNet, 2) }}
                                                </td>
                                                <td class="px-3 py-2 text-right font-semibold text-gray-900"></td>
                                            </tr>
                                            @php $paymentsInserted = true; @endphp
                                        @endif

                                        {{-- Total transfers after last transfer row --}}
                                        @if ($typeName !== 'transfer' && $lastType === 'transfer' && ! $transfersInserted)
                                            <tr class="bg-blue-50 border-t border-gray-200">
                                                <td colspan="2" class="px-3 py-2 text-left font-semibold text-gray-900">
                                                    Total transfers
                                                </td>
                                                <td class="px-3 py-2 text-right font-semibold text-gray-900">
                                                    {{ number_format($transferSectionReceipts, 2) }}
                                                </td>
                                                <td class="px-3 py-2 text-right font-semibold text-gray-900">
                                                    {{ number_format($transferSectionPayments, 2) }}
                                                </td>
                                                <td class="px-3 py-2 text-right font-semibold text-gray-900">
                                                    {{ number_format($transferSectionNet, 2) }}
                                                </td>
                                                <td class="px-3 py-2 text-right font-semibold text-gray-900"></td>
                                            </tr>
                                            @php $transfersInserted = true; @endphp
                                        @endif

                                        {{-- Normal category row --}}
                                        <tr class="{{ $isParent ? 'bg-gray-50' : '' }}">
                                            <td class="px-3 py-2 text-gray-700">
                                                {{ $row['category_type_name'] ?? '—' }}
                                            </td>
                                            <td class="px-3 py-2 text-gray-800 {{ $isParent ? 'font-semibold' : '' }}">
                                                <span style="padding-left: {{ $depth * 1.25 }}rem;">
                                                    {{ $row['category_name'] ?? 'Uncategorised' }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2 text-right text-gray-800 {{ $isParent ? 'font-semibold' : '' }}">
                                                {{ number_format((float) ($row['rolled_receipts_total'] ?? 0), 2) }}
                                            </td>
                                            <td class="px-3 py-2 text-right text-gray-800 {{ $isParent ? 'font-semibold' : '' }}">
                                                {{ number_format((float) ($row['rolled_payments_total'] ?? 0), 2) }}
                                            </td>
                                            <td class="px-3 py-2 text-right font-medium text-gray-900">
                                                {{ number_format((float) ($row['rolled_net_total'] ?? 0), 2) }}
                                            </td>
                                            <td class="px-3 py-2 text-right text-gray-700">
                                                {{ number_format((int) ($row['transaction_count'] ?? 0)) }}
                                            </td>
                                        </tr>

                                        @php
                                            $lastType = $typeName;
                                        @endphp

                                        {{-- If the last rows are of a given type, append totals --}}
                                        @if ($loop->last)
                                            @if ($lastType === 'receipt' && ! $receiptsInserted)
                                                <tr class="bg-green-50 border-t border-gray-200">
                                                    <td colspan="2" class="px-3 py-2 text-left font-semibold text-gray-900">
                                                        Total receipts
                                                    </td>
                                                    <td class="px-3 py-2 text-right font-semibold text-gray-900">
                                                        {{ number_format($receiptsTotal, 2) }}
                                                    </td>
                                                    <td class="px-3 py-2 text-right text-gray-500"></td>
                                                    <td class="px-3 py-2 text-right text-gray-500"></td>
                                                    <td class="px-3 py-2 text-right text-gray-500"></td>
                                                </tr>
                                            @elseif ($lastType === 'payment' && ! $paymentsInserted)
                                                <tr class="bg-red-50 border-t border-gray-200">
                                                    <td colspan="2" class="px-3 py-2 text-left font-semibold text-gray-900">
                                                        Total payments
                                                    </td>
                                                    <td class="px-3 py-2 text-right text-gray-500"></td>
                                                    <td class="px-3 py-2 text-right font-semibold text-gray-900">
                                                        {{ number_format($paymentsTotal, 2) }}
                                                    </td>
                                                    
                                                    <td class="px-3 py-2 text-right text-gray-500"></td>
                                                    <td class="px-3 py-2 text-right text-gray-500"></td>
                                                </tr>
                                            @elseif ($lastType === 'transfer' && ! $transfersInserted)
                                                <tr class="bg-blue-50 border-t border-gray-200">
                                                    <td colspan="2" class="px-3 py-2 text-left font-semibold text-gray-900">
                                                        Total transfers
                                                    </td>
                                                    <td class="px-3 py-2 text-right font-semibold text-gray-900">
                                                        {{ number_format($transferSectionReceipts, 2) }}
                                                    </td>
                                                    <td class="px-3 py-2 text-right font-semibold text-gray-900">
                                                        {{ number_format($transferSectionPayments, 2) }}
                                                    </td>
                                                    <td class="px-3 py-2 text-right font-semibold text-gray-900">
                                                        {{ number_format($transferSectionNet, 2) }}
                                                    </td>
                                                    <td class="px-3 py-2 text-right font-semibold text-gray-900"></td>
                                                </tr>
                                            @endif
                                        @endif
                                    @endforeach
                                @endif
                            </tbody>

@if ($rows->isNotEmpty())
    @php
        // Aggregate from direct values only to avoid double-counting parents
        $totalDirectReceipts = (float) $rows->sum('direct_receipts_total');
        $totalDirectPayments = (float) $rows->sum('direct_payments_total');
        $totalDirectTransfers = (float) ($rows->sum('direct_transfers_total') ?? 0);
        $totalDirectNet = (float) $rows->sum('direct_net_total');
        $totalTransactions = (int) $rows->sum('transaction_count');
    @endphp

    <tfoot class="bg-gray-50 border-t border-gray-200">
        <tr>
            <th colspan="2" class="px-3 py-2 text-left font-semibold text-gray-900">
                Grand total ({{ number_format($totalTransactions) }} transactions)
            </th>
            <th class="px-3 py-2 text-right font-semibold text-gray-900">
                {{ number_format($totalDirectReceipts, 2) }}
            </th>
            <th class="px-3 py-2 text-right font-semibold text-gray-900">
                {{ number_format($totalDirectPayments, 2) }}
            </th>
            <th class="px-3 py-2 text-right font-semibold text-gray-900">
                {{ number_format($totalDirectNet, 2) }}
            </th>
            <th class="px-3 py-2 text-right font-semibold text-gray-900">
                {{ number_format($totalTransactions) }}
            </th>
        </tr>
    </tfoot>
@endif
                        </table>
                    </div>
                </div>
            @else
                {{-- Category Transaction Detail --}}
                @php
                    $typeGroups = collect($reportRows)
                        ->groupBy(fn ($row) => ($row['category_type_name'] ?? 'Other'));

                    $hasTransactionRows = $typeGroups->flatten(1)->isNotEmpty();
                @endphp

                <div class="rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Category Transaction Detail</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Date</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Reference</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Payee</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Description</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700">Account</th>
                                    <th class="px-3 py-2 text-center font-semibold text-gray-700">Rec</th>
                                    <th class="px-3 py-2 text-right font-semibold text-gray-700">Receipt</th>
                                    <th class="px-3 py-2 text-right font-semibold text-gray-700">Payment</th>
                                    <th class="px-3 py-2 text-right font-semibold text-gray-700">Net</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @if (! $hasTransactionRows)
                                    <tr>
                                        <td colspan="10" class="px-3 py-6 text-center text-sm text-gray-500">
                                            No category transactions found for the selected filters.
                                        </td>
                                    </tr>
                                @else
                                    @foreach ($typeGroups as $typeName => $typeRows)
                                        @php
                                            $categoryGroups = $typeRows->groupBy(
                                                fn ($row) => ($row['category_tree_label'] ?? $row['category_name'] ?? 'Uncategorised')
                                            );

                                            $typeReceiptTotal = 0.00;
                                            $typePaymentTotal = 0.00;
                                            $typeTransferTotal = 0.00;
                                            $typeNetTotal = 0.00;
                                            $typeTransactionCount = 0;
                                        @endphp

                                        <tr class="bg-gray-100">
                                            <td colspan="10" class="px-3 py-2 text-left font-semibold text-gray-900">
                                                {{ $typeName }}
                                            </td>
                                        </tr>

                                        @foreach ($categoryGroups as $categoryName => $categoryRows)
                                            @php
                                                $categoryReceiptTotal = (float) $categoryRows->sum('receipt_amount');
                                                $categoryPaymentTotal = (float) $categoryRows->sum('payment_amount');
                                                $categoryTransferTotal = (float) $categoryRows->sum('transfer_amount');
                                                $categoryNetTotal = (float) $categoryRows->sum('net_amount');
                                                $categoryTransactionCount = (int) $categoryRows->pluck('transaction_id')->filter()->unique()->count();

                                                $typeReceiptTotal += $categoryReceiptTotal;
                                                $typePaymentTotal += $categoryPaymentTotal;
                                                $typeTransferTotal += $categoryTransferTotal;
                                                $typeNetTotal += $categoryNetTotal;
                                                $typeTransactionCount += $categoryTransactionCount;
                                            @endphp

                                            <tr class="bg-gray-50">
                                                <td colspan="10" class="px-3 py-2 text-left font-medium text-gray-900">
                                                    {{ $categoryName }}
                                                </td>
                                            </tr>

                                            @foreach ($categoryRows as $row)
                                                <tr>
                                                    <td class="px-3 py-2 text-gray-700 whitespace-nowrap">
                                                        {{ $row['transaction_date'] ?? '—' }}
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-700 whitespace-nowrap">
                                                        {{ $row['reference'] ?? '—' }}
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-800">
                                                        {{ $row['payee'] ?? '—' }}
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-700">
                                                        {{ $row['description'] ?? '—' }}
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-700 whitespace-nowrap">
                                                        {{ $row['account_name'] ?? '—' }}
                                                    </td>
                                                    <td class="px-3 py-2 text-center text-gray-700">
                                                        {{ !empty($row['is_reconciled']) ? 'Y' : '' }}
                                                    </td>
                                                    <td class="px-3 py-2 text-right text-gray-800">
                                                        {{ number_format((float) ($row['receipt_amount'] ?? 0), 2) }}
                                                    </td>
                                                    <td class="px-3 py-2 text-right text-gray-800">
                                                        {{ number_format((float) ($row['payment_amount'] ?? 0), 2) }}
                                                    </td>
                                                    <td class="px-3 py-2 text-right font-medium text-gray-900">
                                                        {{ number_format((float) ($row['net_amount'] ?? 0), 2) }}
                                                    </td>
                                                </tr>
                                            @endforeach

                                            <tr class="bg-yellow-50">
                                                <td colspan="6" class="px-3 py-2 text-left font-semibold text-gray-900">
                                                    {{ $categoryName }} total ({{ number_format($categoryTransactionCount) }} transactions)
                                                </td>
                                                <td class="px-3 py-2 text-right font-semibold text-gray-900">
                                                    {{ number_format($categoryReceiptTotal, 2) }}
                                                </td>
                                                <td class="px-3 py-2 text-right font-semibold text-gray-900">
                                                    {{ number_format($categoryPaymentTotal, 2) }}
                                                </td>
                                                <td class="px-3 py-2 text-right font-semibold text-gray-900">
                                                    {{ number_format($categoryNetTotal, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach

                                        <tr class="bg-blue-50 border-t border-gray-300">
                                            <td colspan="6" class="px-3 py-2 text-left font-semibold text-gray-900">
                                                {{ $typeName }} total ({{ number_format($typeTransactionCount) }} transactions)
                                            </td>
                                            <td class="px-3 py-2 text-right font-semibold text-gray-900">
                                                {{ number_format($typeReceiptTotal, 2) }}
                                            </td>
                                            <td class="px-3 py-2 text-right font-semibold text-gray-900">
                                                {{ number_format($typePaymentTotal, 2) }}
                                            </td>
                                            <td class="px-3 py-2 text-right font-semibold text-gray-900">
                                                {{ number_format($typeNetTotal, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>

                            @if ($hasTransactionRows)
                                <tfoot class="bg-gray-50 border-t border-gray-200">
                                    <tr>
                                        <th colspan="6" class="px-3 py-2 text-left font-semibold text-gray-900">
                                            Grand total ({{ number_format((int) ($reportTotals['transaction_count'] ?? 0)) }} transactions)
                                        </th>
                                        <th class="px-3 py-2 text-right font-semibold text-gray-900">
                                            {{ number_format((float) ($reportTotals['receipts_total'] ?? 0), 2) }}
                                        </th>
                                        <th class="px-3 py-2 text-right font-semibold text-gray-900">
                                            {{ number_format((float) ($reportTotals['payments_total'] ?? 0), 2) }}
                                        </th>
                                        <th class="px-3 py-2 text-right font-semibold text-gray-900">
                                            {{ number_format((float) ($reportTotals['net_movement'] ?? 0), 2) }}
                                        </th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>