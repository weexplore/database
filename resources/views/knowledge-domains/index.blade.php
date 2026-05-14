<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $pageTitle }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <form method="GET"
                          action="{{ route('knowledge-domains.index') }}"
                          id="knowledge-domains-filter-form"
                          class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input type="text"
                                   name="search"
                                   id="search"
                                   value="{{ $filters['search'] }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm"
                                   placeholder="Search by code or name">
                        </div>

                        <div>
                            <label for="active" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="active" id="active" class="w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">All</option>
                                <option value="1" @selected($filters['active'] === '1')>Active</option>
                                <option value="0" @selected($filters['active'] === '0')>Inactive</option>
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Filter
                            </button>

                            <a href="{{ route('knowledge-domains.index') }}"
                               class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <form method="POST" action="{{ route('knowledge-domains.bulk-save') }}" id="knowledge-domains-form">
                    @csrf

                    <input type="hidden" name="search" value="{{ $filters['search'] }}">
                    <input type="hidden" name="active" value="{{ $filters['active'] }}">

                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Domain Name</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sort</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Active</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($rows as $row)
                                    <tr>
                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="existing[{{ $row->id }}][domaincode]"
                                                   value="{{ old("existing.{$row->id}.domaincode", $row->domaincode) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                   maxlength="50"
                                                   required>
                                        </td>

                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="existing[{{ $row->id }}][domainname]"
                                                   value="{{ old("existing.{$row->id}.domainname", $row->domainname) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                   maxlength="150"
                                                   required>
                                        </td>

                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="existing[{{ $row->id }}][description]"
                                                   value="{{ old("existing.{$row->id}.description", $row->description) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        </td>

                                        <td class="px-3 py-2">
                                            <input type="number"
                                                   name="existing[{{ $row->id }}][sortorder]"
                                                   value="{{ old("existing.{$row->id}.sortorder", $row->sortorder) }}"
                                                   class="w-24 rounded-md border-gray-300 shadow-sm text-sm"
                                                   min="0">
                                        </td>

                                        <td class="px-3 py-2 text-center">
                                            <input type="hidden" name="existing[{{ $row->id }}][isactive]" value="0">
                                            <input type="checkbox"
                                                   name="existing[{{ $row->id }}][isactive]"
                                                   value="1"
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm"
                                                   @checked(old("existing.{$row->id}.isactive", $row->isactive))>
                                        </td>

                                        <td class="px-3 py-2 text-center whitespace-nowrap">
                                            <button type="button"
                                                    class="px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm delete-knowledge-domain-btn"
                                                    data-id="{{ $row->id }}"
                                                    data-name="{{ $row->domainname }}"
                                                    data-action="{{ route('knowledge-domains.destroy', $row->id) }}">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-3 py-6 text-center text-sm text-gray-500">
                                            No knowledge domains found.
                                        </td>
                                    </tr>
                                @endforelse

                                <tr class="bg-blue-50">
                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[domaincode]"
                                               value="{{ old('new.domaincode') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               maxlength="50"
                                               placeholder="finance">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[domainname]"
                                               value="{{ old('new.domainname') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               maxlength="150"
                                               placeholder="Finance">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[description]"
                                               value="{{ old('new.description') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               placeholder="Optional description">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="number"
                                               name="new[sortorder]"
                                               value="{{ old('new.sortorder') }}"
                                               class="w-24 rounded-md border-gray-300 shadow-sm text-sm"
                                               min="0">
                                    </td>

                                    <td class="px-3 py-2 text-center">
                                        <input type="hidden" name="new[isactive]" value="0">
                                        <input type="checkbox"
                                               name="new[isactive]"
                                               value="1"
                                               class="rounded border-gray-300 text-blue-600 shadow-sm"
                                               @checked(old('new.isactive', true))>
                                    </td>

                                    <td class="px-3 py-2 text-center text-sm text-gray-400 whitespace-nowrap">
                                        New row
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-6 border-t border-gray-200 flex items-center justify-between">
                        <p class="text-sm text-gray-500">
                            Edit rows above, add a new knowledge domain at the bottom, then save once.
                        </p>

                        <button type="submit"
                                class="px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            Save Knowledge Domains
                        </button>
                    </div>
                </form>

                @include('partials.admin.compact-delete-form', [
                    'formId' => 'delete-knowledge-domain-form',
                    'query' => request()->only(['search', 'active']),
                ])
            </div>
        </div>
    </div>

    @include('partials.admin.dirty-form-script', [
        'formId' => 'knowledge-domains-form',
        'filterFormId' => 'knowledge-domains-filter-form',
        'deleteFormId' => 'delete-knowledge-domain-form',
        'deleteButtonSelector' => '.delete-knowledge-domain-btn',
        'dirtyMessage' => 'You have unsaved changes in the Knowledge Domains table. Continue and lose those changes?',
        'deleteDirtyMessage' => 'You have unsaved changes in the Knowledge Domains table. Delete anyway and lose those changes?',
        'deleteConfirmPrefix' => 'Delete knowledge domain',
        'deleteConfirmSuffix' => 'This cannot be undone.',
    ])
</x-app-layout>