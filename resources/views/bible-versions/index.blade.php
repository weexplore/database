<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $pageTitle ?? 'Bible Versions' }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <form method="GET"
                          action="{{ route('bible-versions.index') }}"
                          id="bible-versions-filter-form"
                          class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                                Search
                            </label>
                            <input type="text"
                                   name="search"
                                   id="search"
                                   value="{{ $filters['search'] ?? '' }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm"
                                   placeholder="Code, name, or language">
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
                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Filter
                            </button>

                            <a href="{{ route('bible-versions.index') }}"
                               class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <form method="POST"
                      action="{{ route('bible-versions.bulk-save') }}"
                      id="bible-versions-form">
                    @csrf

                    <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
                    <input type="hidden" name="active" value="{{ $filters['active'] ?? '' }}">

                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Version Code</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Version Name</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Language</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Active</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($rows as $row)
                                    <tr>
                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="existing[{{ $row->id }}][versioncode]"
                                                   value="{{ old("existing.{$row->id}.versioncode", $row->versioncode) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                   maxlength="20"
                                                   required>
                                        </td>

                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="existing[{{ $row->id }}][versionname]"
                                                   value="{{ old("existing.{$row->id}.versionname", $row->versionname) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                   maxlength="150"
                                                   required>
                                        </td>

                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="existing[{{ $row->id }}][languagecode]"
                                                   value="{{ old("existing.{$row->id}.languagecode", $row->languagecode) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                   maxlength="20"
                                                   placeholder="ENG">
                                        </td>

                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="existing[{{ $row->id }}][notes]"
                                                   value="{{ old("existing.{$row->id}.notes", $row->notes) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                   placeholder="Optional notes">
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
                                                    class="px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm delete-bible-version-btn"
                                                    data-id="{{ $row->id }}"
                                                    data-name="{{ $row->versioncode }} - {{ $row->versionname }}"
                                                    data-action="{{ route('bible-versions.destroy', $row->id) }}">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-3 py-6 text-center text-sm text-gray-500">
                                            No Bible versions found.
                                        </td>
                                    </tr>
                                @endforelse

                                <tr class="bg-blue-50">
                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[versioncode]"
                                               value="{{ old('new.versioncode') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               maxlength="20"
                                               placeholder="KJV">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[versionname]"
                                               value="{{ old('new.versionname') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               maxlength="150"
                                               placeholder="King James Version">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[languagecode]"
                                               value="{{ old('new.languagecode') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               maxlength="20"
                                               placeholder="ENG">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[notes]"
                                               value="{{ old('new.notes') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               placeholder="Optional notes">
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
                            Edit rows above, add a new Bible version at the bottom, then save once.
                        </p>

                        <button type="submit"
                                class="px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            Save Bible Versions
                        </button>
                    </div>
                </form>

                @include('partials.admin.compact-delete-form', [
                    'formId' => 'delete-bible-version-form',
                    'query' => request()->only(['search', 'active']),
                ])
            </div>
        </div>
    </div>

    @include('partials.admin.dirty-form-script', [
        'formId' => 'bible-versions-form',
        'filterFormId' => 'bible-versions-filter-form',
        'deleteFormId' => 'delete-bible-version-form',
        'deleteButtonSelector' => '.delete-bible-version-btn',
        'dirtyMessage' => 'You have unsaved changes in the Bible Versions table. Continue and lose those changes?',
        'deleteDirtyMessage' => 'You have unsaved changes in the Bible Versions table. Delete anyway and lose those changes?',
        'deleteConfirmPrefix' => 'Delete Bible version',
        'deleteConfirmSuffix' => 'This cannot be undone.',
    ])
</x-app-layout>