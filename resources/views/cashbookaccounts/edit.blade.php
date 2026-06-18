<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $cashbookAccount->exists ? 'Edit Cashbook Account' : 'Add Cashbook Account' }}
            </h2>
            <a href="{{ route('cashbook-accounts.index') }}"
               class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Back to list
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
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
                <div class="p-6">
                    <form method="POST"
                          action="{{ $cashbookAccount->exists ? route('cashbook-accounts.update', $cashbookAccount) : route('cashbook-accounts.store') }}"
                          class="space-y-6">
                        @csrf
                        @if ($cashbookAccount->exists)
                            @method('PUT')
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="legalentityid" class="block text-sm font-medium text-gray-700">Legal entity</label>
                                <select id="legalentityid" name="legalentityid" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Select legal entity</option>
                                    @foreach ($legalEntities as $legalEntity)
                                        <option value="{{ $legalEntity->id }}" @selected(old('legalentityid', $cashbookAccount->legalentityid) == $legalEntity->id)>
                                            {{ $legalEntity->entityname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="accounttypeid" class="block text-sm font-medium text-gray-700">Account type</label>
                                <select id="accounttypeid" name="accounttypeid" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Select account type</option>
                                    @foreach ($accountTypes as $accountType)
                                        <option value="{{ $accountType->id }}" @selected(old('accounttypeid', $cashbookAccount->accounttypeid) == $accountType->id)>
                                            {{ $accountType->typename }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="accountcode" class="block text-sm font-medium text-gray-700">Account code</label>
                                <input type="text" id="accountcode" name="accountcode" value="{{ old('accountcode', $cashbookAccount->accountcode) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="accountname" class="block text-sm font-medium text-gray-700">Account name</label>
                                <input type="text" id="accountname" name="accountname" value="{{ old('accountname', $cashbookAccount->accountname) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="institutionname" class="block text-sm font-medium text-gray-700">Institution name</label>
                                <input type="text" id="institutionname" name="institutionname" value="{{ old('institutionname', $cashbookAccount->institutionname) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="accountnumbermasked" class="block text-sm font-medium text-gray-700">Masked account number</label>
                                <input type="text" id="accountnumbermasked" name="accountnumbermasked" value="{{ old('accountnumbermasked', $cashbookAccount->accountnumbermasked) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="currencycode" class="block text-sm font-medium text-gray-700">Currency code</label>
                                <input type="text" id="currencycode" name="currencycode" value="{{ old('currencycode', $cashbookAccount->currencycode ?: 'AUD') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm uppercase" maxlength="3">
                            </div>

                            <div>
                                <label for="openingbalancedate" class="block text-sm font-medium text-gray-700">Opening balance date</label>
                                <input type="date" id="openingbalancedate" name="openingbalancedate" value="{{ old('openingbalancedate', optional($cashbookAccount->openingbalancedate)->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="openingbalance" class="block text-sm font-medium text-gray-700">Opening balance</label>
                                <input type="number" step="0.01" id="openingbalance" name="openingbalance" value="{{ old('openingbalance', $cashbookAccount->openingbalance) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea id="notes" name="notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('notes', $cashbookAccount->notes) }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="includeincashreporting" value="1" class="rounded border-gray-300" @checked(old('includeincashreporting', $cashbookAccount->includeincashreporting ?? true))>
                                <span>Include in cash reporting</span>
                            </label>

                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="isreconcilable" value="1" class="rounded border-gray-300" @checked(old('isreconcilable', $cashbookAccount->isreconcilable ?? true))>
                                <span>Reconcilable</span>
                            </label>

                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="isactive" value="1" class="rounded border-gray-300" @checked(old('isactive', $cashbookAccount->isactive ?? true))>
                                <span>Active</span>
                            </label>
                        </div>

                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 space-y-4">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">QIF Import Defaults</h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    These categories are used when imported QIF transactions cannot yet be coded properly.
                                </p>
                            </div>

                            @php
                                $selectedLegalEntityId = old('legalentityid', $cashbookAccount->legalentityid);

                                $categoryLabel = function ($category) {
                                    if (! $category) {
                                        return '—';
                                    }

                                    $typeName = $category->categoryType?->typename
                                        ?: ucfirst($category->categoryType?->typecode ?? '');

                                    return trim(($typeName ? $typeName . ' - ' : '') . ($category->categoryname ?? 'Unnamed category'));
                                };
                            @endphp

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="defaultunallocatedreceiptcategoryid" class="block text-sm font-medium text-gray-700">
                                        Default unallocated receipt category
                                    </label>
                                    <select id="defaultunallocatedreceiptcategoryid"
                                            name="defaultunallocatedreceiptcategoryid"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        <option value="">Select receipt category</option>
                                        @foreach ($categories as $category)
                                            @php
                                                $categoryAllowed = $category->legalentityid === null
                                                    || (int) $category->legalentityid === (int) $selectedLegalEntityId;

                                                $typeCode = strtolower(trim($category->categoryType?->typecode ?? ''));
                                            @endphp
                                            @if ($categoryAllowed && $typeCode === 'receipt')
                                                <option value="{{ $category->id }}"
                                                    @selected(old('defaultunallocatedreceiptcategoryid', $cashbookAccount->defaultunallocatedreceiptcategoryid) == $category->id)>
                                                    {{ $categoryLabel($category) }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="defaultunallocatedpaymentcategoryid" class="block text-sm font-medium text-gray-700">
                                        Default unallocated payment category
                                    </label>
                                    <select id="defaultunallocatedpaymentcategoryid"
                                            name="defaultunallocatedpaymentcategoryid"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        <option value="">Select payment category</option>
                                        @foreach ($categories as $category)
                                            @php
                                                $categoryAllowed = $category->legalentityid === null
                                                    || (int) $category->legalentityid === (int) $selectedLegalEntityId;

                                                $typeCode = strtolower(trim($category->categoryType?->typecode ?? ''));
                                            @endphp
                                            @if ($categoryAllowed && $typeCode === 'payment')
                                                <option value="{{ $category->id }}"
                                                    @selected(old('defaultunallocatedpaymentcategoryid', $cashbookAccount->defaultunallocatedpaymentcategoryid) == $category->id)>
                                                    {{ $categoryLabel($category) }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
                            <button type="submit"
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                Save Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if ($cashbookAccount->exists)
                <div class="bg-white shadow-sm sm:rounded-lg border border-red-200">
                    <div class="p-6 space-y-4">
                        <div>
                            <h3 class="text-lg font-semibold text-red-700">Delete Account</h3>
                            <p class="mt-1 text-sm text-gray-600">
                                This permanently deletes the cashbook account. Only do this if you are sure it is no longer needed.
                            </p>
                        </div>

                        <form method="POST"
                              action="{{ route('cashbook-accounts.destroy', $cashbookAccount) }}"
                              onsubmit="return confirm('Delete this cashbook account?');">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                                Delete Account
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>