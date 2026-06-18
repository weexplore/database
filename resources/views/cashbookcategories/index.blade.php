<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Cashbook Categories</h2>
            <a href="{{ route('cashbook-categories.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                Add Category
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">
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
                <div class="p-4 sm:p-6 space-y-6">
                    <form method="GET" action="{{ route('cashbook-categories.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="legalentityid" class="block text-sm font-medium text-gray-700">Legal entity</label>
                            <select id="legalentityid" name="legalentityid" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All scopes</option>
                                @foreach ($legalEntities as $legalEntity)
                                    <option value="{{ $legalEntity->id }}" @selected((string) request('legalentityid') === (string) $legalEntity->id)>
                                        {{ $legalEntity->entityname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="categorytypeid" class="block text-sm font-medium text-gray-700">Category type</label>
                            <select id="categorytypeid" name="categorytypeid" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All types</option>
                                @foreach ($categoryTypes as $categoryType)
                                    <option value="{{ $categoryType->id }}" @selected((string) request('categorytypeid') === (string) $categoryType->id)>
                                        {{ $categoryType->typename }}
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
                            <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                Filter
                            </button>
                            <a href="{{ route('cashbook-categories.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Reset
                            </a>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('cashbook-categories.bulk-update') }}" id="cashbook-categories-bulk-form">
                        @csrf

                        <div class="flex items-center justify-between gap-4 mb-4">
                            <p class="text-sm text-gray-600">
                                Update sort order and other compact fields directly in the table, then save all changes together.
                            </p>

                            <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                Bulk Save
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Scope</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Type</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Parent</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Sort</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Code</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Category</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Posting</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Active</th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @forelse ($cashbookCategories as $cashbookCategory)
                                        <tr>
                                            <td class="px-3 py-2 align-top">
                                                <a href="{{ route('cashbook-categories.edit', $cashbookCategory) }}"
                                                class="block w-56 rounded-md border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-700 hover:bg-gray-200 hover:text-gray-900">
                                                    {{ $cashbookCategory->legalEntity?->entityname ?: 'Shared' }}
                                                </a>
                                            </td>

                                            <td class="px-3 py-2 text-sm text-gray-700 align-top">
                                                {{ $cashbookCategory->categoryType?->typename }}
                                            </td>

                                            <td class="px-3 py-2 text-sm text-gray-700 align-top">
                                                {{ $cashbookCategory->parentCategory?->categoryname ?: '—' }}
                                            </td>
                                            <td class="px-3 py-2 align-top">
                                                <input type="hidden" name="rows[{{ $cashbookCategory->id }}][id]" value="{{ $cashbookCategory->id }}">
                                                <input type="number"
                                                       name="rows[{{ $cashbookCategory->id }}][sortorder]"
                                                       value="{{ old("rows.{$cashbookCategory->id}.sortorder", $cashbookCategory->sortorder) }}"
                                                       class="block w-24 rounded-md border-gray-300 shadow-sm text-sm">
                                            </td>
                                            <td class="px-3 py-2 align-top">
                                                <input type="text"
                                                       name="rows[{{ $cashbookCategory->id }}][categorycode]"
                                                       value="{{ old("rows.{$cashbookCategory->id}.categorycode", $cashbookCategory->categorycode) }}"
                                                       class="block w-28 rounded-md border-gray-300 shadow-sm text-sm">
                                            </td>

                                            <td class="px-3 py-2 align-top">
                                                <div style="padding-left: {{ ($cashbookCategory->depth ?? 0) * 1.25 }}rem;">
                                                    @if (($cashbookCategory->depth ?? 0) > 0)
                                                        <span class="text-gray-400">↳</span>
                                                    @endif
                                                    {{ $cashbookCategory->categoryname }}
                                                </div>
                                            </td>

                                            <td class="px-3 py-2 align-top">
                                                <label class="inline-flex items-center">
                                                    <input type="hidden" name="rows[{{ $cashbookCategory->id }}][allowposting]" value="0">
                                                    <input type="checkbox"
                                                           name="rows[{{ $cashbookCategory->id }}][allowposting]"
                                                           value="1"
                                                           class="rounded border-gray-300"
                                                           @checked(old("rows.{$cashbookCategory->id}.allowposting", $cashbookCategory->allowposting))>
                                                </label>
                                            </td>

                                            <td class="px-3 py-2 align-top">
                                                <label class="inline-flex items-center">
                                                    <input type="hidden" name="rows[{{ $cashbookCategory->id }}][isactive]" value="0">
                                                    <input type="checkbox"
                                                           name="rows[{{ $cashbookCategory->id }}][isactive]"
                                                           value="1"
                                                           class="rounded border-gray-300"
                                                           @checked(old("rows.{$cashbookCategory->id}.isactive", $cashbookCategory->isactive))>
                                                </label>
                                            </td>

                                            <td class="px-3 py-2 text-right text-sm align-top">
                                                <a href="{{ route('cashbook-categories.edit', $cashbookCategory) }}"
                                                class="inline-flex items-center rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                                                    Edit
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="px-3 py-6 text-center text-sm text-gray-500">
                                                No cashbook categories found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 flex items-center justify-between gap-4">
                            <div>
                                {{ $cashbookCategories->links() }}
                            </div>

                            <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                Bulk Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>