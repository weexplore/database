<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $pageTitle ?? 'Knowledge Items' }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')
            <div class="p-6 border-b border-gray-200 flex items-center justify-between gap-3">
                <div class="text-sm text-gray-500">
                    Quick update key fields here, then open full edit for richer notes, relationships, and review content.
                </div>

                <div class="flex items-center gap-2">
                    @if($filters['categoryid'] ?? null)
                        <a href="{{ route('knowledge-categories.index', [
                                'domainid' => $filters['domainid'] ?? null,
                                'categoryid' => $filters['categoryid'] ?? null,
                            ]) }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                            Back to Category
                        </a>
                    @endif
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <form method="GET"
                          action="{{ route('knowledge.items.index') }}"
                          id="knowledge-items-filter-form"
                          class="grid grid-cols-1 md:grid-cols-6 gap-4">
                        <div>
                            <label for="domainid" class="block text-sm font-medium text-gray-700 mb-1">Domain</label>
                            <select name="domainid" id="domainid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                @foreach($domains as $domain)
                                    <option value="{{ $domain->id }}" @selected((int) ($filters['domainid'] ?? 0) === (int) $domain->id)>
                                        {{ $domain->domainname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="categoryid" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select name="categoryid" id="categoryid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected((int) ($filters['categoryid'] ?? 0) === (int) $category->id)>
                                        {{ $category->categoryname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input type="text"
                                   name="search"
                                   id="search"
                                   value="{{ $filters['search'] ?? '' }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                   placeholder="Name, summary, notes">
                        </div>

<div>
    <label for="itemtype" class="block text-sm font-medium text-gray-700 mb-1">
        Type
    </label>
    <select name="itemtype" id="itemtype" class="w-full rounded-md border-gray-300 shadow-sm">
        <option value="">All item types</option>
        @foreach($itemTypes as $itemType)
            <option value="{{ $itemType->id }}"
                @selected((string) ($filters['itemtype'] ?? '') === (string) $itemType->id)>
                {{ $itemType->typename }}
            </option>
        @endforeach
    </select>
</div>

                        <div>
                            <label for="itemstatus" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="itemstatus" id="itemstatus" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All statuses</option>
                                @foreach($itemStatuses as $status)
                                    <option value="{{ $status }}" @selected(($filters['itemstatus'] ?? '') === $status)>
                                        {{ $status }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                Filter
                            </button>
                            <a href="{{ route('knowledge.items.index') }}"
                               class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <form method="POST"
                      action="{{ route('knowledge.items.bulk-save') }}"
                      id="knowledge-items-form">
                    @csrf

                    <input type="hidden" name="domainid" value="{{ $filters['domainid'] ?? '' }}">
                    <input type="hidden" name="categoryid" value="{{ $filters['categoryid'] ?? '' }}">
                    <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
                    <input type="hidden" name="itemtype" value="{{ $filters['itemtype'] ?? '' }}">
                    <input type="hidden" name="itemstatus" value="{{ $filters['itemstatus'] ?? '' }}">
                    <input type="hidden" name="active" value="{{ $filters['active'] ?? '' }}">

                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Summary</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sort</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Next Review</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Featured</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Active</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($items as $item)
                                    <tr>
                                        <td class="px-3 py-2 min-w-[220px]">
                                            <input type="text"
                                                   name="existing[{{ $item->id }}][itemname]"
                                                   value="{{ old("existing.{$item->id}.itemname", $item->itemname) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                   required>

                                            <a href="{{ route('knowledge.items.edit', $item) }}"
                                               class="inline-block mt-2 text-xs text-blue-700 hover:text-blue-900">
                                                Open full edit
                                            </a>
                                        </td>

                                        <td class="px-3 py-2 min-w-[180px]">
                                            <select name="existing[{{ $item->id }}][primarycategoryid]"
                                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                    required>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}"
                                                        @selected((string) old("existing.{$item->id}.primarycategoryid", $item->primarycategoryid) === (string) $category->id)>
                                                        {{ $category->categoryname }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>

<td class="px-3 py-2 min-w-[140px]">
    <select name="existing[{{ $item->id }}][itemtype]"
            class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        <option value="">Select type</option>
        @foreach($itemTypes as $itemType)
            <option value="{{ $itemType->id }}"
                @selected((string) old("existing.{$item->id}.itemtype", $item->itemtype) === (string) $itemType->id)>
                {{ $itemType->typename }}
            </option>
        @endforeach
    </select>
</td>

                                        <td class="px-3 py-2 min-w-[140px]">
                                            <input type="text"
                                                   name="existing[{{ $item->id }}][itemstatus]"
                                                   value="{{ old("existing.{$item->id}.itemstatus", $item->itemstatus) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        </td>

                                        <td class="px-3 py-2 min-w-[260px]">
                                            <textarea name="existing[{{ $item->id }}][summary]"
                                                      rows="2"
                                                      class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old("existing.{$item->id}.summary", $item->summary) }}</textarea>
                                        </td>

                                        <td class="px-3 py-2 w-[90px]">
                                            <input type="number"
                                                   name="existing[{{ $item->id }}][sortorder]"
                                                   value="{{ old("existing.{$item->id}.sortorder", $item->sortorder) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                   min="0">
                                        </td>

                                        <td class="px-3 py-2 min-w-[150px]">
                                            <input type="date"
                                                   name="existing[{{ $item->id }}][nextreviewdate]"
                                                   value="{{ old("existing.{$item->id}.nextreviewdate", optional($item->nextreviewdate)->format('Y-m-d')) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        </td>

                                        <td class="px-3 py-2 text-center">
                                            <input type="hidden" name="existing[{{ $item->id }}][isfeatured]" value="0">
                                            <input type="checkbox"
                                                   name="existing[{{ $item->id }}][isfeatured]"
                                                   value="1"
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm"
                                                   @checked(old("existing.{$item->id}.isfeatured", $item->isfeatured))>
                                        </td>

                                        <td class="px-3 py-2 text-center">
                                            <input type="hidden" name="existing[{{ $item->id }}][isactive]" value="0">
                                            <input type="checkbox"
                                                   name="existing[{{ $item->id }}][isactive]"
                                                   value="1"
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm"
                                                   @checked(old("existing.{$item->id}.isactive", $item->isactive))>
                                        </td>

                                        <td class="px-3 py-2 whitespace-nowrap">
                                            <a href="{{ route('knowledge.items.edit', $item) }}"
                                               class="inline-flex items-center px-3 py-1.5 bg-slate-700 text-white rounded hover:bg-slate-600 text-sm">
                                                Edit
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="px-3 py-6 text-center text-sm text-gray-500">
                                            No knowledge items found.
                                        </td>
                                    </tr>
                                @endforelse

                                <tr class="bg-blue-50">
                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[itemname]"
                                               value="{{ old('new.itemname') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               placeholder="New knowledge item">
                                    </td>

                                    <td class="px-3 py-2">
                                        <select name="new[primarycategoryid]" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                            <option value="">Select category</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}"
                                                    @selected((string) old('new.primarycategoryid', $filters['categoryid'] ?? '') === (string) $category->id)>
                                                    {{ $category->categoryname }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

<td class="px-3 py-2 min-w-[140px]">
    <select name="new[itemtype]"
            class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        <option value="">Select type</option>
        @foreach($itemTypes as $itemType)
            <option value="{{ $itemType->id }}"
                @selected((string) old('new.itemtype') === (string) $itemType->id)>
                {{ $itemType->typename }}
            </option>
        @endforeach
    </select>
</td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[itemstatus]"
                                               value="{{ old('new.itemstatus', 'active') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               placeholder="Status">
                                    </td>

                                    <td class="px-3 py-2">
                                        <textarea name="new[summary]"
                                                  rows="2"
                                                  class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                  placeholder="Short summary">{{ old('new.summary') }}</textarea>
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="number"
                                               name="new[sortorder]"
                                               value="{{ old('new.sortorder', 0) }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               min="0">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="date"
                                               name="new[nextreviewdate]"
                                               value="{{ old('new.nextreviewdate') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>

                                    <td class="px-3 py-2 text-center">
                                        <input type="hidden" name="new[isfeatured]" value="0">
                                        <input type="checkbox"
                                               name="new[isfeatured]"
                                               value="1"
                                               class="rounded border-gray-300 text-blue-600 shadow-sm"
                                               @checked(old('new.isfeatured', false))>
                                    </td>

                                    <td class="px-3 py-2 text-center">
                                        <input type="hidden" name="new[isactive]" value="0">
                                        <input type="checkbox"
                                               name="new[isactive]"
                                               value="1"
                                               class="rounded border-gray-300 text-blue-600 shadow-sm"
                                               @checked(old('new.isactive', true))>
                                    </td>

                                    <td class="px-3 py-2 text-sm text-gray-400 whitespace-nowrap">
                                        New row
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

<div class="p-6 border-t border-gray-200 flex items-center justify-between">
    <p class="text-sm text-gray-500">
        Quick update key fields here, then open full edit for richer notes, relationships, and review content.
    </p>

    <div class="flex items-center gap-2">
        <a href="{{ route('knowledge.items.index', array_filter([
                'domainid' => $filters['domainid'] ?? request('domainid'),
                'categoryid' => $filters['categoryid'] ?? request('categoryid'),
                'search' => $filters['search'] ?? request('search'),
                'itemtype' => $filters['itemtype'] ?? request('itemtype'),
                'itemstatus' => $filters['itemstatus'] ?? request('itemstatus'),
                'active' => $filters['active'] ?? request('active'),
            ], fn ($value) => $value !== null && $value !== '')) }}"
           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Cancel
        </a>

        <button type="submit"
                class="inline-flex items-center px-5 py-2 bg-green-600 border border-transparent rounded-md text-sm font-semibold text-white hover:bg-green-700">
            Save Changes
        </button>
    </div>
</div>
                </form>
            </div>
        </div>
    </div>

    @include('partials.admin.dirty-form-script', [
        'formId' => 'knowledge-items-form',
        'filterFormId' => 'knowledge-items-filter-form',
        'dirtyMessage' => 'You have unsaved changes in the Knowledge Items table. Continue and lose those changes?',
    ])
</x-app-layout>