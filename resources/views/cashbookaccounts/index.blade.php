<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Cashbook Accounts</h2>
            <a href="{{ route('cashbook-accounts.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                Add Account
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">
            @if (session('success'))
                <div class="rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 space-y-6">
                    <form method="GET" action="{{ route('cashbook-accounts.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="legalentityid" class="block text-sm font-medium text-gray-700">Legal entity</label>
                            <select id="legalentityid" name="legalentityid" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All entities</option>
                                @foreach ($legalEntities as $legalEntity)
                                    <option value="{{ $legalEntity->id }}" @selected((string) request('legalentityid') === (string) $legalEntity->id)>
                                        {{ $legalEntity->entityname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="accounttypeid" class="block text-sm font-medium text-gray-700">Account type</label>
                            <select id="accounttypeid" name="accounttypeid" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All account types</option>
                                @foreach ($accountTypes as $accountType)
                                    <option value="{{ $accountType->id }}" @selected((string) request('accounttypeid') === (string) $accountType->id)>
                                        {{ $accountType->typename }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="isactive" class="block text-sm font-medium text-gray-700">Active status</label>
                            <select id="isactive" name="isactive" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All</option>
                                <option value="1" @selected(request('isactive') === '1')>Active</option>
                                <option value="0" @selected(request('isactive') === '0')>Inactive</option>
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Filter</button>
                            <a href="{{ route('cashbook-accounts.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Reset</a>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Entity</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Type</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Code</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Account</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Institution</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Opening Balance</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Active</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse ($cashbookAccounts as $cashbookAccount)
                                    <tr>
                                        <td class="px-3 py-2 text-sm align-top">
                                            <a href="{{ route('cashbook-accounts.edit', $cashbookAccount) }}"
                                            class="inline-flex items-center rounded-md border border-gray-300 w-56 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                                                {{ $cashbookAccount->legalEntity?->entityname ?? '—' }}
                                            </a>
                                        </td>
                                        <td class="px-3 py-2 text-sm text-gray-700">{{ $cashbookAccount->accountType?->typename }}</td>
                                        <td class="px-3 py-2 text-sm text-gray-700">{{ $cashbookAccount->accountcode ?: '—' }}</td>
                                        <td class="px-3 py-2 text-sm text-gray-900">{{ $cashbookAccount->accountname }}</td>
                                        <td class="px-3 py-2 text-sm text-gray-700">{{ $cashbookAccount->institutionname ?: '—' }}</td>
                                        <td class="px-3 py-2 text-sm text-right text-gray-700">{{ $cashbookAccount->openingbalance !== null ? number_format((float) $cashbookAccount->openingbalance, 2) : '—' }}</td>
                                        <td class="px-3 py-2 text-sm text-gray-700">{{ $cashbookAccount->isactive ? 'Yes' : 'No' }}</td>
                                        <td class="px-3 py-2 text-right text-sm align-top">
                                            <a href="{{ route('cashbook-accounts.edit', $cashbookAccount) }}"
                                            class="inline-flex items-center rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                                                Edit
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-3 py-6 text-center text-sm text-gray-500">No cashbook accounts found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div>
                        {{ $cashbookAccounts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
