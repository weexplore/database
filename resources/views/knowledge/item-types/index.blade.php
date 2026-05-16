<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $pageTitle ?? 'Knowledge Item Types' }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                    <div class="font-semibold mb-2">Please fix the following:</div>
                    <ul class="list-disc pl-5 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <form method="GET"
                          action="{{ route('knowledge.item-types.index') }}"
                          id="knowledge-item-types-filter-form"
                          class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="md:col-span-2">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                                Search
                            </label>
                            <input type="text"
                                   name="search"
                                   id="search"
                                   value="{{ $filters['search'] ?? '' }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm"
                                   placeholder="Type name or description">
                        </div>

                        <div>
                            <label for="active" class="block text-sm font-medium text-gray-700 mb-1">
                                Status
                            </label>
                            <select name="active"
                                    id="active"
                                    class="w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">All</option>
                                <option value="1" @selected(($filters['active'] ?? '') === '1')>Active</option>
                                <option value="0" @selected(($filters['active'] ?? '') === '0')>Inactive</option>
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                Filter
                            </button>

                            <a href="{{ route('knowledge.item-types.index') }}"
                               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <form method="POST"
                      action="{{ route('knowledge.item-types.bulk-save') }}"
                      id="knowledge-item-types-form">
                    @csrf

                    <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
                    <input type="hidden" name="active" value="{{ $filters['active'] ?? '' }}">

                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type Name</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Sort</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Active</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Items</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($rows as $row)
                                    <tr>
                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="existing[{{ $row->id }}][typename]"
                                                   value="{{ old("existing.{$row->id}.typename", $row->typename) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                   maxlength="100"
                                                   required>
                                        </td>

                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="existing[{{ $row->id }}][description]"
                                                   value="{{ old("existing.{$row->id}.description", $row->description) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                   maxlength="255">
                                        </td>

                                        <td class="px-3 py-2">
                                            <input type="number"
                                                   name="existing[{{ $row->id }}][sortorder]"
                                                   value="{{ old("existing.{$row->id}.sortorder", $row->sortorder) }}"
                                                   class="w-24 rounded-md border-gray-300 shadow-sm text-sm text-right"
                                                   min="0"
                                                   step="1">
                                        </td>

                                        <td class="px-3 py-2 text-center">
                                            <input type="hidden" name="existing[{{ $row->id }}][isactive]" value="0">
                                            <input type="checkbox"
                                                   name="existing[{{ $row->id }}][isactive]"
                                                   value="1"
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm"
                                                   @checked(old("existing.{$row->id}.isactive", $row->isactive))>
                                        </td>

                                        <td class="px-3 py-2 text-center text-sm text-gray-600">
                                            {{ $row->itemscount ?? 0 }}
                                        </td>

                                        <td class="px-3 py-2 text-center whitespace-nowrap">
                                            <button type="button"
                                                    class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm delete-knowledge-item-type-btn"
                                                    data-id="{{ $row->id }}"
                                                    data-name="{{ $row->typename }}"
                                                    data-action="{{ route('knowledge.item-types.destroy', $row) }}">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-3 py-6 text-center text-sm text-gray-500">
                                            No knowledge item types found.
                                        </td>
                                    </tr>
                                @endforelse

                                <tr class="bg-green-50">
                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[typename]"
                                               value="{{ old('new.typename') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               maxlength="100"
                                               placeholder="New type name">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[description]"
                                               value="{{ old('new.description') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               maxlength="255"
                                               placeholder="Optional description">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="number"
                                               name="new[sortorder]"
                                               value="{{ old('new.sortorder', 0) }}"
                                               class="w-24 rounded-md border-gray-300 shadow-sm text-sm text-right"
                                               min="0"
                                               step="1">
                                    </td>

                                    <td class="px-3 py-2 text-center">
                                        <input type="hidden" name="new[isactive]" value="0">
                                        <input type="checkbox"
                                               name="new[isactive]"
                                               value="1"
                                               class="rounded border-gray-300 text-blue-600 shadow-sm"
                                               @checked(old('new.isactive', true))>
                                    </td>

                                    <td class="px-3 py-2 text-center text-sm text-gray-400">
                                        New
                                    </td>

                                    <td class="px-3 py-2 text-center text-sm text-gray-400">
                                        New row
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-6 border-t border-gray-200 flex items-center justify-between">
                        <p class="text-sm text-gray-500">
                            Maintain reusable Knowledge Item Types here for consistent item entry and filtering.
                        </p>

                        <button type="submit"
                                class="inline-flex items-center px-5 py-2 bg-green-600 border border-transparent rounded-md text-sm font-semibold text-white hover:bg-green-700">
                            Save Changes
                        </button>
                    </div>
                </form>

                <form method="POST" id="delete-knowledge-item-type-form" class="hidden">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
                    <input type="hidden" name="active" value="{{ $filters['active'] ?? '' }}">
                </form>
            </div>
        </div>
    </div>

    @include('partials.admin.dirty-form-script', [
    'formId' => 'knowledge-item-types-form',
    'filterFormId' => 'knowledge-item-types-filter-form',
    'deleteFormId' => 'delete-knowledge-item-type-form',
    'deleteButtonSelector' => '.delete-knowledge-item-type-btn',
    'dirtyMessage' => 'You have unsaved changes in Knowledge Item Types. Continue and lose those changes?',
    'deleteDirtyMessage' => 'You have unsaved changes in Knowledge Item Types. Delete anyway and lose those changes?',
    'deleteConfirmPrefix' => 'Delete knowledge item type',
    'deleteConfirmSuffix' => 'This cannot be undone.',
])
</x-app-layout>