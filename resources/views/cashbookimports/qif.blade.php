<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Import Cashbook Transactions from QIF</h2>

            <a href="{{ route('cashbook-transactions.index', ['accountid' => $selectedAccountId]) }}"
               class="inline-flex items-center rounded-md border border-gray-300 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                Back to Cashbook
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

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 space-y-6">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-4">
                        <div class="text-sm font-medium text-gray-500">Import target</div>
                        <div class="mt-1 text-lg font-semibold text-gray-900">
                            {{ $selectedAccount?->accountname ?: 'Select a bank account' }}
                        </div>

                        @if ($selectedAccount?->legalEntity)
                            <div class="text-sm text-gray-600">
                                {{ $selectedAccount->legalEntity->entityname }}
                            </div>
                        @endif

                        @if ($selectedAccount)
                            <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-3 text-sm">
                                <div>
                                    <div class="font-medium text-gray-500">Last QIF import</div>
                                    <div class="text-gray-900">
                                        {{ $selectedAccount->lastqifimportedat ? $selectedAccount->lastqifimportedat->format('Y-m-d H:i') : 'Never imported' }}
                                    </div>
                                </div>

                                <div>
                                    <div class="font-medium text-gray-500">Last imported transaction date</div>
                                    <div class="text-gray-900">
                                        {{ $selectedAccount->lastqiftransactiondate ? $selectedAccount->lastqiftransactiondate->format('Y-m-d') : 'Not set' }}
                                    </div>
                                </div>

                                <div>
                                    <div class="font-medium text-gray-500">Default import coding</div>
                                    <div class="text-gray-900">
                                        Receipt: {{ $selectedAccount->defaultUnallocatedReceiptCategory?->categoryname ?: 'Not set' }}<br>
                                        Payment: {{ $selectedAccount->defaultUnallocatedPaymentCategory?->categoryname ?: 'Not set' }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <form method="POST"
                          action="{{ route('cashbook-import.qif.store') }}"
                          enctype="multipart/form-data"
                          class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                            <div>
                                <label for="accountid" class="block text-sm font-medium text-gray-700">Bank account</label>
                                <select id="accountid"
                                        name="accountid"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"
                                        required>
                                    <option value="">Select account</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}"
                                            @selected((string) old('accountid', $selectedAccountId) === (string) $account->id)>
                                            {{ $account->accountname }}{{ $account->legalEntity ? ' — ' . $account->legalEntity->entityname : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="qif_file" class="block text-sm font-medium text-gray-700">QIF file</label>
                                <input type="file"
                                       id="qif_file"
                                       name="qif_file"
                                       accept=".qif,.txt"
                                       class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm"
                                       required>
                                <p class="mt-2 text-xs text-gray-500">
                                    Upload the QIF exported for the selected bank account.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <label for="date_from" class="block text-sm font-medium text-gray-700">Date from</label>
                                <input type="date"
                                    id="date_from"
                                    name="date_from"
                                    value="{{ old('date_from') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="date_to" class="block text-sm font-medium text-gray-700">Date to</label>
                                <input type="date"
                                    id="date_to"
                                    name="date_to"
                                    value="{{ old('date_to') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-4 space-y-4">
                            <div class="text-sm font-medium text-gray-700">Import options</div>

                            <div class="flex items-start gap-3">
                                <input type="hidden" name="ignore_before_last_import_date" value="0">

                                <input type="checkbox"
                                       id="ignore_before_last_import_date"
                                       name="ignore_before_last_import_date"
                                       value="1"
                                       class="mt-1 rounded border-gray-300"
                                       @checked(old('ignore_before_last_import_date', 1))>

                                <div>
                                    <label for="ignore_before_last_import_date" class="text-sm font-medium text-gray-700">
                                        Ignore rows on or before the account's last imported transaction date
                                    </label>
                                    <div class="mt-1 text-sm text-gray-600">
                                        This helps prevent overlap when the QIF export includes transactions already imported.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-4 text-sm text-blue-900">
                            <div class="font-medium">How this import behaves</div>
                            <ul class="mt-2 list-disc pl-5 space-y-1">
                                <li>Transactions are imported directly into the live cashbook for the selected account.</li>
                                <li>Suspected duplicates are inserted but left unreconciled and marked for review.</li>
                                <li>Imported rows are initially posted to the account's default unallocated receipt or payment category.</li>
                                <li>Non-duplicate imported transactions are marked reconciled automatically.</li>
                            </ul>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="submit"
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                Import QIF
                            </button>

                            <a href="{{ route('cashbook-transactions.index', ['accountid' => $selectedAccountId]) }}"
                               class="inline-flex items-center rounded-md border border-gray-300 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>