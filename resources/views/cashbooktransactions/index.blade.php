<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Cashbook Transactions</h2>

            <div class="flex items-center gap-2">
                @if (request('accountid'))
                    <a href="{{ route('cashbook-import.qif.show', ['accountid' => request('accountid')]) }}"
                       class="inline-flex items-center rounded-md border border-gray-300 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                        Import QIF
                    </a>
                @else
                    <span class="inline-flex items-center rounded-md border border-gray-200 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-400 cursor-not-allowed">
                        Import QIF
                    </span>
                @endif

                <a href="{{ route('cashbook-transactions.create', ['accountid' => request('accountid')]) }}"
                   class="inline-flex items-center rounded-md border border-gray-300 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Full Transaction Entry
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $selectedAccount = $accounts->firstWhere('id', (int) request('accountid'));
        $defaultEntityId = old('legalentityid', $selectedAccount?->legalentityid);

        $categoryLabel = function ($category) {
            if (! $category) {
                return '—';
            }

            return $category->display_label
                ?? trim(
                    (($category->categoryType?->typename ?: ucfirst($category->categoryType?->typecode ?? '')) ? (($category->categoryType?->typename ?: ucfirst($category->categoryType?->typecode ?? '')) . ' - ') : '')
                    . ($category->categoryname ?? 'Unnamed category')
                );
        };
    @endphp

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">
            @if (session('success'))
                <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 space-y-3">
                    <div>{{ session('success') }}</div>

                    @if (session('last_import_batch_id') && request('accountid'))
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="text-xs font-medium text-green-900">
                                Last QIF import batch: {{ session('last_import_batch_id') }}
                            </div>

                            <form method="POST"
                                  action="{{ route('cashbook-import.qif.batch.destroy') }}"
                                  onsubmit="return confirm('Reverse and delete the last QIF import for this account?');">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="accountid" value="{{ request('accountid') }}">
                                <input type="hidden" name="importbatchid" value="{{ session('last_import_batch_id') }}">
                                <button type="submit"
                                        class="inline-flex items-center rounded-md border border-red-300 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100">
                                    Reverse last QIF import
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endif

            @if (session('warning'))
                <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    {{ session('warning') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <div class="font-semibold">Please fix the following:</div>
                    <ul class="mt-2 list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (request('accountid'))
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-4">
                    <label class="inline-flex items-center gap-3 text-sm font-medium text-gray-700">
                        <input type="checkbox" id="show-reverse-import-panel" class="rounded border-gray-300">
                        <span>Show reverse QIF import tools</span>
                    </label>

                    <div id="reverse-import-panel" class="mt-4 hidden rounded-lg border border-red-200 bg-red-50 px-4 py-4 space-y-4">
                        <div>
                            <div class="text-sm font-semibold text-red-900">Reverse QIF import</div>
                            <div class="mt-1 text-sm text-red-800">
                                Delete imported QIF transactions for the selected bank account by batch number.
                            </div>
                        </div>

                        <form method="POST"
                              action="{{ route('cashbook-import.qif.batch.destroy') }}"
                              class="flex flex-col gap-3 md:flex-row md:items-end"
                              onsubmit="return confirm('Reverse and delete this QIF import batch for the selected account?');">
                            @csrf
                            @method('DELETE')

                            <input type="hidden" name="accountid" value="{{ request('accountid') }}">
                            <input type="hidden" name="return_transactionkind" value="{{ request('transactionkind') }}">
                            <input type="hidden" name="return_date_from" value="{{ request('date_from') }}">
                            <input type="hidden" name="return_date_to" value="{{ request('date_to') }}">
                            <input type="hidden" name="return_category_search" value="{{ request('category_search') }}">

                            <div class="w-full md:max-w-xs">
                                <label for="importbatchid" class="block text-sm font-medium text-gray-700">Batch number</label>
                                <input type="number"
                                       id="importbatchid"
                                       name="importbatchid"
                                       value="{{ old('importbatchid', session('last_import_batch_id')) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"
                                       placeholder="Enter batch number"
                                       required>
                            </div>

                            <div>
                                <button type="submit"
                                        class="inline-flex items-center rounded-md border border-red-300 bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                                    Reverse import batch
                                </button>
                            </div>
                        </form>

                        @if (!empty($recentImportBatches) && $recentImportBatches->count())
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-red-200 text-sm">
                                    <thead>
                                        <tr class="text-left text-red-900">
                                            <th class="px-3 py-2">Batch</th>
                                            <th class="px-3 py-2">Rows</th>
                                            <th class="px-3 py-2">From</th>
                                            <th class="px-3 py-2">To</th>
                                            <th class="px-3 py-2">Imported</th>
                                            <th class="px-3 py-2 text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-red-100">
                                        @foreach ($recentImportBatches as $batch)
                                            <tr>
                                                <td class="px-3 py-2 font-medium text-gray-900">{{ $batch->importbatchid }}</td>
                                                <td class="px-3 py-2 text-gray-700">{{ $batch->transaction_count }}</td>
                                                <td class="px-3 py-2 text-gray-700">{{ $batch->min_date }}</td>
                                                <td class="px-3 py-2 text-gray-700">{{ $batch->max_date }}</td>
                                                <td class="px-3 py-2 text-gray-700">{{ $batch->imported_at }}</td>
                                                <td class="px-3 py-2 text-right">
                                                    <button type="button"
                                                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50"
                                                            onclick="document.getElementById('importbatchid').value='{{ $batch->importbatchid }}'; document.getElementById('show-reverse-import-panel').checked = true; document.getElementById('reverse-import-panel').classList.remove('hidden');">
                                                        Use batch
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 space-y-6">
                    <form method="GET" action="{{ route('cashbook-transactions.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                        <div class="md:col-span-2">
                            <label for="accountid" class="block text-sm font-medium text-gray-700">Bank account</label>
                            <select id="accountid" name="accountid" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">Select account</option>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}" @selected((string) request('accountid') === (string) $account->id)>
                                        {{ $account->accountname }}{{ $account->legalEntity ? ' - ' . $account->legalEntity->entityname : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="transactionkind" class="block text-sm font-medium text-gray-700">Kind</label>
                            <select id="transactionkind" name="transactionkind" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All kinds</option>
                                <option value="receipt" @selected(request('transactionkind') === 'receipt')>Receipt</option>
                                <option value="payment" @selected(request('transactionkind') === 'payment')>Payment</option>
                                <option value="transfer" @selected(request('transactionkind') === 'transfer')>Transfer</option>
                            </select>
                        </div>

                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700">Date from</label>
                            <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700">Date to</label>
                            <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div>
                            <label for="category_search" class="block text-sm font-medium text-gray-700">Category contains</label>
                            <input
                                type="text"
                                id="category_search"
                                name="category_search"
                                value="{{ request('category_search') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"
                                placeholder="Fuel, accom, grocery..."
                            >
                        </div>

                        <div class="md:col-span-6 flex items-end gap-2">
                            <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                Filter
                            </button>
                            <a href="{{ route('cashbook-transactions.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Reset
                            </a>
                        </div>
                    </form>

                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-4">
                        <div class="text-sm font-medium text-gray-500">Current cash book</div>
                        <div class="mt-1 text-lg font-semibold text-gray-900">
                            {{ $selectedAccount?->accountname ?: 'Select a bank account' }}
                        </div>
                        @if ($selectedAccount?->legalEntity)
                            <div class="text-sm text-gray-600">{{ $selectedAccount->legalEntity->entityname }}</div>
                        @endif
                    </div>

                    @if (request('accountid'))
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <div class="rounded-lg border border-gray-200 bg-white px-4 py-4">
                                <div class="text-sm font-medium text-gray-500">Opening balance</div>
                                <div class="mt-1 text-2xl font-semibold text-gray-900">
                                    {{ number_format((float) $openingBalance, 2) }}
                                </div>
                            </div>

                            <div class="rounded-lg border border-gray-200 bg-white px-4 py-4">
                                <div class="text-sm font-medium text-gray-500">Ledger balance</div>
                                <div class="mt-1 text-2xl font-semibold text-gray-900">
                                    {{ number_format((float) $ledgerBalance, 2) }}
                                </div>
                            </div>

                            <div class="rounded-lg border border-gray-200 bg-white px-4 py-4">
                                <div class="text-sm font-medium text-gray-500">Reconciled balance</div>
                                <div class="mt-1 text-2xl font-semibold text-gray-900">
                                    {{ number_format((float) $reconciledBalance, 2) }}
                                </div>
                            </div>

                            <div class="rounded-lg border border-gray-200 bg-white px-4 py-4">
                                <div class="text-sm font-medium text-gray-500">Reconciliation</div>
                                <div class="mt-1 text-sm text-gray-700">
                                    Compare Reconciled balance to the bank statement closing balance.
                                </div>
                            </div>
                        </div>

                        <form id="quick-add-form" method="POST" action="{{ route('cashbook-transactions.quick-store') }}">
                            @csrf
                            <input type="hidden" name="accountid" value="{{ request('accountid') }}">
                            <input type="hidden" name="legalentityid" value="{{ $defaultEntityId }}">
                            <input type="hidden" name="return_accountid" value="{{ request('accountid') }}">
                            <input type="hidden" name="return_transactionkind" value="{{ request('transactionkind') }}">
                            <input type="hidden" name="return_date_from" value="{{ request('date_from') }}">
                            <input type="hidden" name="return_date_to" value="{{ request('date_to') }}">
                            <input type="hidden" name="isreconciled" value="0">
                        </form>

                        <form id="bulk-update-form" method="POST" action="{{ route('cashbook-transactions.bulk-update') }}">
                            @csrf
                            <input type="hidden" name="return_accountid" value="{{ request('accountid') }}">
                            <input type="hidden" name="return_transactionkind" value="{{ request('transactionkind') }}">
                            <input type="hidden" name="return_date_from" value="{{ request('date_from') }}">
                            <input type="hidden" name="return_date_to" value="{{ request('date_to') }}">
                            <input type="hidden" name="return_category_search" value="{{ request('category_search') }}">
                        </form>

                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                            <div class="text-sm text-gray-700">
                                Inline rows can be edited across the page and saved once. Split and transfer rows stay on full edit.
                            </div>

                            <div class="flex items-center gap-2">
                                <button form="bulk-update-form"
                                        type="submit"
                                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                    Bulk Save
                                </button>

                                <a href="{{ route('cashbook-transactions.create', ['accountid' => request('accountid')]) }}"
                                   class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Full Transaction Entry
                                </a>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Date</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Ref</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Payee</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Description</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Type</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Category</th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Amount</th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Balance</th>
                                        <th class="px-3 py-2 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Rec</th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr class="bg-indigo-50/40">
                                        <td class="px-3 py-2 align-top">
                                            <input form="quick-add-form" type="date" name="transactiondate" value="{{ old('transactiondate', now()->format('Y-m-d')) }}" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        </td>

                                        <td class="px-3 py-2 align-top">
                                            <input form="quick-add-form" type="text" name="referencenumber" value="{{ old('referencenumber') }}" class="block w-28 rounded-md border-gray-300 shadow-sm text-sm">
                                        </td>

                                        <td class="px-3 py-2 align-top">
                                            <input form="quick-add-form" type="text" name="payeename" value="{{ old('payeename') }}" class="block w-full min-w-[10rem] rounded-md border-gray-300 shadow-sm text-sm">
                                        </td>

                                        <td class="px-3 py-2 align-top">
                                            <input form="quick-add-form" type="text" name="description" value="{{ old('description') }}" class="block w-full min-w-[14rem] rounded-md border-gray-300 shadow-sm text-sm" placeholder="Description">
                                        </td>

                                        <td class="px-3 py-2 align-top">
                                            <select form="quick-add-form" name="transactionkind" class="block w-28 rounded-md border-gray-300 shadow-sm text-sm">
                                                <option value="payment" @selected(old('transactionkind') === 'payment')>Payment</option>
                                                <option value="receipt" @selected(old('transactionkind') === 'receipt')>Receipt</option>
                                            </select>
                                        </td>

                                        <td class="px-3 py-2 align-top">
                                            <select form="quick-add-form" name="categoryid" class="block w-full min-w-[14rem] rounded-md border-gray-300 shadow-sm text-sm">
                                                <option value="">Select category</option>
                                                @foreach ($categories as $category)
                                                    @php
                                                        $categoryAllowed = $category->legalentityid === null || (int) $category->legalentityid === (int) $defaultEntityId;
                                                    @endphp
                                                    @if ($categoryAllowed)
                                                        <option value="{{ $category->id }}" @selected(old('categoryid') == $category->id)>
                                                            {{ $categoryLabel($category) }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </td>

                                        <td class="px-3 py-2 align-top">
                                            <input form="quick-add-form" type="number" step="0.01" name="amounttotal" value="{{ old('amounttotal') }}" class="block w-28 rounded-md border-gray-300 shadow-sm text-sm text-right" placeholder="0.00">
                                        </td>

                                        <td class="px-3 py-2 align-top text-right text-sm text-gray-400">
                                            —
                                        </td>

                                        <td class="px-3 py-2 align-top text-center">
                                            <input form="quick-add-form" type="checkbox" name="isreconciled" value="1" class="rounded border-gray-300" @checked(old('isreconciled'))>
                                        </td>

                                        <td class="px-3 py-2 text-right align-top">
                                            <div class="flex items-center justify-end gap-2">
                                                <button form="quick-add-form" type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                                    Add
                                                </button>

                                                <a href="{{ route('cashbook-transactions.create', ['accountid' => request('accountid')]) }}"
                                                   class="inline-flex items-center rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                                                    Split / Full
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    @forelse ($transactions as $transaction)
                                        @php
                                            $lineCount = $transaction->lines->count();
                                            $firstLine = $transaction->lines->first();
                                            $isSimple = $transaction->transactionkind !== 'transfer' && $lineCount === 1;

                                            $categoryType = strtolower(trim($firstLine?->category?->categoryType?->typecode ?? ''));
                                            $transactionKind = strtolower(trim($transaction->transactionkind ?? ''));

                                            $hasTypeMismatch = $transactionKind !== 'transfer'
                                                && $categoryType !== ''
                                                && $categoryType !== 'transfer'
                                                && $transactionKind !== ''
                                                && $categoryType !== $transactionKind;

                                            $rowBalance = $transactionBalances[$transaction->id] ?? null;
                                        @endphp

                                        @if ($isSimple)
                                            <tr>
                                                <td class="hidden">
                                                    <input form="bulk-update-form" type="hidden" name="rows[{{ $transaction->id }}][id]" value="{{ $transaction->id }}">
                                                    <input form="bulk-update-form" type="hidden" name="rows[{{ $transaction->id }}][legalentityid]" value="{{ $transaction->legalentityid }}">
                                                    <input form="bulk-update-form" type="hidden" name="rows[{{ $transaction->id }}][accountid]" value="{{ $transaction->accountid }}">
                                                </td>

                                                <td class="px-3 py-2 align-top">
                                                    <input form="bulk-update-form" type="date" name="rows[{{ $transaction->id }}][transactiondate]" value="{{ optional($transaction->transactiondate)->format('Y-m-d') }}" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                </td>

                                                <td class="px-3 py-2 align-top">
                                                    <input form="bulk-update-form" type="text" name="rows[{{ $transaction->id }}][referencenumber]" value="{{ $transaction->referencenumber }}" class="block w-28 rounded-md border-gray-300 shadow-sm text-sm">
                                                </td>

                                                <td class="px-3 py-2 align-top">
                                                    <input form="bulk-update-form" type="text" name="rows[{{ $transaction->id }}][payeename]" value="{{ $transaction->payeename }}" class="block w-full min-w-[10rem] rounded-md border-gray-300 shadow-sm text-sm">
                                                </td>

                                                <td class="px-3 py-2 align-top">
                                                    <input form="bulk-update-form" type="text" name="rows[{{ $transaction->id }}][description]" value="{{ $transaction->description }}" class="block w-full min-w-[14rem] rounded-md border-gray-300 shadow-sm text-sm">
                                                </td>

                                                <td class="px-3 py-2 align-top">
                                                    <select form="bulk-update-form" name="rows[{{ $transaction->id }}][transactionkind]" class="block w-28 rounded-md border-gray-300 shadow-sm text-sm">
                                                        <option value="payment" @selected($transaction->transactionkind === 'payment')>Payment</option>
                                                        <option value="receipt" @selected($transaction->transactionkind === 'receipt')>Receipt</option>
                                                    </select>
                                                </td>

                                                <td class="px-3 py-2 align-top">
                                                    <div class="space-y-2">
                                                        <select form="bulk-update-form" name="rows[{{ $transaction->id }}][categoryid]" class="block w-full min-w-[14rem] rounded-md border-gray-300 shadow-sm text-sm">
                                                            <option value="">Select category</option>
                                                            @foreach ($categories as $category)
                                                                @php
                                                                    $categoryAllowed = $category->legalentityid === null || (int) $category->legalentityid === (int) $transaction->legalentityid;
                                                                @endphp
                                                                @if ($categoryAllowed)
                                                                    <option value="{{ $category->id }}" @selected($firstLine?->categoryid == $category->id)>
                                                                        {{ $categoryLabel($category) }}
                                                                    </option>
                                                                @endif
                                                            @endforeach
                                                        </select>

                                                        @if ($hasTypeMismatch)
                                                            <div class="inline-flex items-center rounded-md border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700">
                                                                Warning: type and category differ
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>

                                                <td class="px-3 py-2 align-top">
                                                    <input form="bulk-update-form" type="number" step="0.01" name="rows[{{ $transaction->id }}][amounttotal]" value="{{ $transaction->amounttotal }}" class="block w-28 rounded-md border-gray-300 shadow-sm text-sm text-right">
                                                </td>

                                                <td class="px-3 py-2 align-top text-right">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $rowBalance !== null ? number_format((float) $rowBalance, 2) : '—' }}
                                                    </div>
                                                </td>

                                                <td class="px-3 py-2 align-top text-center">
                                                    <input
                                                        type="checkbox"
                                                        class="rounded border-gray-300 js-rec-toggle"
                                                        data-url="{{ route('cashbook-transactions.toggle-reconciled', $transaction) }}"
                                                        @checked($transaction->isreconciled)
                                                    >
                                                </td>

                                                <td class="px-3 py-2 text-right align-top">
                                                    <a href="{{ route('cashbook-transactions.edit', [
                                                        'cashbookTransaction' => $transaction->id,
                                                        'return_accountid' => request('accountid'),
                                                        'return_transactionkind' => request('transactionkind'),
                                                        'return_date_from' => request('date_from'),
                                                        'return_date_to' => request('date_to'),
                                                    ]) }}"
                                                       class="inline-flex items-center rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                                                        Full Edit
                                                    </a>
                                                </td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td class="px-3 py-2 text-sm text-gray-700 align-top">
                                                    {{ optional($transaction->transactiondate)->format('Y-m-d') ?: '—' }}
                                                </td>

                                                <td class="px-3 py-2 text-sm text-gray-700 align-top">
                                                    {{ $transaction->referencenumber ?: '—' }}
                                                </td>

                                                <td class="px-3 py-2 text-sm text-gray-700 align-top">
                                                    {{ $transaction->payeename ?: '—' }}
                                                </td>

                                                <td class="px-3 py-2 text-sm text-gray-900 align-top">
                                                    {{ $transaction->description }}
                                                </td>

                                                <td class="px-3 py-2 text-sm text-gray-700 align-top">
                                                    @if ($transaction->transactionkind === 'transfer')
                                                        <span class="inline-flex items-center rounded-md border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">
                                                            Transfer{{ $transaction->transferAccount ? ' to ' . $transaction->transferAccount->accountname : '' }}
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center rounded-md border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700">
                                                            Split ({{ $lineCount }} lines)
                                                        </span>
                                                    @endif
                                                </td>

                                                <td class="px-3 py-2 text-sm text-gray-700 align-top">
                                                    <div class="space-y-2">
                                                        @if ($transaction->transactionkind === 'transfer')
                                                            <span class="inline-flex items-center rounded-md border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">
                                                                Transfer{{ $transaction->transferAccount ? ' to ' . $transaction->transferAccount->accountname : '' }}
                                                            </span>
                                                        @elseif ($lineCount > 1)
                                                            <span class="inline-flex items-center rounded-md border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700">
                                                                Split ({{ $lineCount }} lines)
                                                            </span>
                                                        @else
                                                            <span>{{ $categoryLabel($firstLine?->category) }}</span>
                                                        @endif

                                                        @if ($hasTypeMismatch)
                                                            <div class="inline-flex items-center rounded-md border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700">
                                                                Warning: type and category differ
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>

                                                <td class="px-3 py-2 text-sm text-right text-gray-700 align-top">
                                                    {{ number_format((float) $transaction->amounttotal, 2) }}
                                                </td>

                                                <td class="px-3 py-2 text-sm text-right text-gray-700 align-top">
                                                    <span class="font-medium text-gray-900">
                                                        {{ $rowBalance !== null ? number_format((float) $rowBalance, 2) : '—' }}
                                                    </span>
                                                </td>

                                                <td class="px-3 py-2 align-top text-center">
                                                    <input
                                                        type="checkbox"
                                                        class="rounded border-gray-300 js-rec-toggle"
                                                        data-url="{{ route('cashbook-transactions.toggle-reconciled', $transaction) }}"
                                                        @checked($transaction->isreconciled)
                                                    >
                                                </td>

                                                <td class="px-3 py-2 text-right text-sm align-top">
                                                    <a href="{{ route('cashbook-transactions.edit', [
                                                        'cashbookTransaction' => $transaction->id,
                                                        'return_accountid' => request('accountid'),
                                                        'return_transactionkind' => request('transactionkind'),
                                                        'return_date_from' => request('date_from'),
                                                        'return_date_to' => request('date_to'),
                                                    ]) }}"
                                                       class="inline-flex items-center rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                                                        Edit
                                                    </a>
                                                </td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="10" class="px-3 py-6 text-center text-sm text-gray-500">
                                                No transactions found for the selected account and filters.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="text-sm text-gray-500">
                                Bulk Save applies to editable one-line receipt and payment rows shown on this page.
                            </div>

                            <button form="bulk-update-form"
                                    type="submit"
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                Bulk Save
                            </button>
                        </div>

                        <div>
                            {{ $transactions->links() }}
                        </div>
                    @else
                        <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-800">
                            Select a bank account to start working in cash book view.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            document.querySelectorAll('.js-rec-toggle').forEach((checkbox) => {
                checkbox.addEventListener('change', async function () {
                    const checked = this.checked;
                    const url = this.dataset.url;

                    this.disabled = true;

                    try {
                        const response = await fetch(url, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                            },
                            body: JSON.stringify({
                                isreconciled: checked ? 1 : 0,
                            }),
                        });

                        if (!response.ok) {
                            throw new Error('Save failed');
                        }

                        window.location.reload();
                    } catch (error) {
                        this.checked = !checked;
                        alert('Could not update reconciliation status.');
                    } finally {
                        this.disabled = false;
                    }
                });
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.getElementById('show-reverse-import-panel');
            const panel = document.getElementById('reverse-import-panel');
            const bulkForm = document.getElementById('bulk-update-form');

            if (toggle && panel) {
                const syncPanel = () => {
                    panel.classList.toggle('hidden', !toggle.checked);
                };

                syncPanel();
                toggle.addEventListener('change', syncPanel);
            }

            if (bulkForm) {
                let isDirty = false;

                bulkForm.querySelectorAll('input, select, textarea').forEach((element) => {
                    if (element.type === 'hidden') {
                        return;
                    }

                    element.addEventListener('change', () => {
                        isDirty = true;
                    });

                    element.addEventListener('input', () => {
                        isDirty = true;
                    });
                });

                bulkForm.addEventListener('submit', () => {
                    isDirty = false;
                });

                window.addEventListener('beforeunload', (event) => {
                    if (!isDirty) {
                        return;
                    }

                    event.preventDefault();
                    event.returnValue = '';
                });
            }
        });
    </script>
</x-app-layout>