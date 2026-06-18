@php
    $existingLines = old('lines', $cashbookTransaction->exists ? $cashbookTransaction->lines->map(function ($line) {
        return [
            'categoryid' => $line->categoryid,
            'linedescription' => $line->linedescription,
            'amount' => $line->amount,
            'taxcode' => $line->taxcode,
            'notes' => $line->notes,
        ];
    })->toArray() : []);

    if (empty($existingLines)) {
        $existingLines = [
            ['categoryid' => '', 'linedescription' => '', 'amount' => '', 'taxcode' => '', 'notes' => ''],
        ];
    }
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $cashbookTransaction->exists ? 'Edit Cashbook Transaction' : 'Add Cashbook Transaction' }}
            </h2>
            <a href="{{ route('cashbook-transactions.index', [
                'accountid' => $returnAccountId ?? request('return_accountid'),
                'transactionkind' => $returnTransactionKind ?? request('return_transactionkind'),
                'date_from' => $returnDateFrom ?? request('return_date_from'),
                'date_to' => $returnDateTo ?? request('return_date_to'),
            ]) }}"
            class="inline-flex items-center rounded-md border border-gray-300 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                Back to list
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">
            @if (session('success'))
                <div class="rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                    <div class="font-semibold">Please fix the following:</div>
                    <ul class="mt-2 list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-6">
                    <form method="POST" action="{{ $cashbookTransaction->exists ? route('cashbook-transactions.update', $cashbookTransaction) : route('cashbook-transactions.store') }}" class="space-y-6" id="cashbook-transaction-form">
                        @csrf
                        @if ($cashbookTransaction->exists)
                            @method('PUT')
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="legalentityid" class="block text-sm font-medium text-gray-700">Legal entity</label>
                                <select id="legalentityid" name="legalentityid" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Select legal entity</option>
                                    @foreach ($legalEntities as $legalEntity)
                                        <option value="{{ $legalEntity->id }}" @selected(old('legalentityid', $cashbookTransaction->legalentityid) == $legalEntity->id)>
                                            {{ $legalEntity->entityname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="accountid" class="block text-sm font-medium text-gray-700">Account</label>
                                <select id="accountid" name="accountid" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Select account</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}" @selected(old('accountid', $cashbookTransaction->accountid) == $account->id)>
                                            {{ $account->accountname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="transactionkind" class="block text-sm font-medium text-gray-700">Transaction kind</label>
                                <select id="transactionkind" name="transactionkind" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Select kind</option>
                                    @foreach ($transactionKinds as $value => $label)
                                        <option value="{{ $value }}" @selected(old('transactionkind', $cashbookTransaction->transactionkind) === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="transactiondate" class="block text-sm font-medium text-gray-700">Transaction date</label>
                                <input type="date" id="transactiondate" name="transactiondate" value="{{ old('transactiondate', optional($cashbookTransaction->transactiondate)->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="posteddate" class="block text-sm font-medium text-gray-700">Posted date</label>
                                <input type="date" id="posteddate" name="posteddate" value="{{ old('posteddate', optional($cashbookTransaction->posteddate)->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="amounttotal" class="block text-sm font-medium text-gray-700">Total amount</label>
                                <input type="number" step="0.01" id="amounttotal" name="amounttotal" value="{{ old('amounttotal', $cashbookTransaction->amounttotal) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="payeename" class="block text-sm font-medium text-gray-700">Payee</label>
                                <input type="text" id="payeename" name="payeename" value="{{ old('payeename', $cashbookTransaction->payeename) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="referencenumber" class="block text-sm font-medium text-gray-700">Reference number</label>
                                <input type="text" id="referencenumber" name="referencenumber" value="{{ old('referencenumber', $cashbookTransaction->referencenumber) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="transferaccountid" class="block text-sm font-medium text-gray-700">Transfer account</label>
                                <select id="transferaccountid" name="transferaccountid" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Not applicable</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}" @selected(old('transferaccountid', $cashbookTransaction->transferaccountid) == $account->id)>
                                            {{ $account->accountname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-3">
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <input type="text" id="description" name="description" value="{{ old('description', $cashbookTransaction->description) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="sourcetype" class="block text-sm font-medium text-gray-700">Source type</label>
                                <input type="text" id="sourcetype" name="sourcetype" value="{{ old('sourcetype', $cashbookTransaction->sourcetype) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="externalsourcekey" class="block text-sm font-medium text-gray-700">External source key</label>
                                <input type="text" id="externalsourcekey" name="externalsourcekey" value="{{ old('externalsourcekey', $cashbookTransaction->externalsourcekey) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="reconciledat" class="block text-sm font-medium text-gray-700">Reconciled at</label>
                                <input type="date" id="reconciledat" name="reconciledat" value="{{ old('reconciledat', optional($cashbookTransaction->reconciledat)->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div class="md:col-span-3">
                                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('notes', $cashbookTransaction->notes) }}</textarea>
                            </div>
                        </div>

                        <div>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="isreconciled" value="1" class="rounded border-gray-300" @checked(old('isreconciled', $cashbookTransaction->isreconciled ?? false))>
                                <span>Reconciled</span>
                            </label>
                        </div>

                        <div class="border-t border-gray-200 pt-6 space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Transaction Lines</h3>
                                <button type="button" id="add-line-button" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Add Line
                                </button>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200" id="transaction-lines-table">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Category</th>
                                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Description</th>
                                            <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Amount</th>
                                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Tax code</th>
                                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Notes</th>
                                            <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white" id="transaction-lines-body">
                                        @foreach ($existingLines as $index => $line)
                                            <tr>
                                                <td class="px-3 py-2">
                                                    <select name="lines[{{ $index }}][categoryid]" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                        <option value="">Select category</option>
                                                        @foreach ($categories as $category)
                                                            <option value="{{ $category->id }}" @selected(($line['categoryid'] ?? '') == $category->id)>
                                                                {{ $category->categoryname }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="px-3 py-2">
                                                    <input type="text" name="lines[{{ $index }}][linedescription]" value="{{ $line['linedescription'] ?? '' }}" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                </td>
                                                <td class="px-3 py-2">
                                                    <input type="number" step="0.01" name="lines[{{ $index }}][amount]" value="{{ $line['amount'] ?? '' }}" class="block w-full rounded-md border-gray-300 shadow-sm text-sm text-right">
                                                </td>
                                                <td class="px-3 py-2">
                                                    <input type="text" name="lines[{{ $index }}][taxcode]" value="{{ $line['taxcode'] ?? '' }}" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                </td>
                                                <td class="px-3 py-2">
                                                    <input type="text" name="lines[{{ $index }}][notes]" value="{{ $line['notes'] ?? '' }}" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                </td>
                                                <td class="px-3 py-2 text-right">
                                                    <button type="button" class="remove-line-button inline-flex items-center rounded-md border border-red-300 bg-white px-3 py-2 text-xs font-medium text-red-700 hover:bg-red-50">
                                                        Remove
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
                            <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                Save Transaction
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if ($cashbookTransaction->exists)
                <div class="bg-white shadow-sm sm:rounded-lg border border-red-200">
                    <div class="p-6 space-y-4">
                        <div>
                            <h3 class="text-lg font-semibold text-red-700">Delete Transaction</h3>
                            <p class="mt-1 text-sm text-gray-600">
                                This permanently deletes the cashbook transaction. Only do this if you are sure it is no longer needed.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('cashbook-transactions.destroy', $cashbookTransaction) }}" onsubmit="return confirm('Delete this cashbook transaction?');">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                                Delete Transaction
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const body = document.getElementById('transaction-lines-body');
            const addButton = document.getElementById('add-line-button');
            let index = {{ count($existingLines) }};
            const categoryOptions = `
                <option value="">Select category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->categoryname }}</option>
                @endforeach
            `;

            addButton?.addEventListener('click', function () {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-3 py-2">
                        <select name="lines[${index}][categoryid]" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">${categoryOptions}</select>
                    </td>
                    <td class="px-3 py-2">
                        <input type="text" name="lines[${index}][linedescription]" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </td>
                    <td class="px-3 py-2">
                        <input type="number" step="0.01" name="lines[${index}][amount]" class="block w-full rounded-md border-gray-300 shadow-sm text-sm text-right">
                    </td>
                    <td class="px-3 py-2">
                        <input type="text" name="lines[${index}][taxcode]" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </td>
                    <td class="px-3 py-2">
                        <input type="text" name="lines[${index}][notes]" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </td>
                    <td class="px-3 py-2 text-right">
                        <button type="button" class="remove-line-button inline-flex items-center rounded-md border border-red-300 bg-white px-3 py-2 text-xs font-medium text-red-700 hover:bg-red-50">Remove</button>
                    </td>
                `;
                body.appendChild(row);
                index++;
            });

            body?.addEventListener('click', function (event) {
                if (event.target.classList.contains('remove-line-button')) {
                    const rows = body.querySelectorAll('tr');
                    if (rows.length > 1) {
                        event.target.closest('tr')?.remove();
                    }
                }
            });
        });
    </script>
</x-app-layout>