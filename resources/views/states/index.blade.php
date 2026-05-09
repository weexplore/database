<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            States
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <form method="GET"
                          action="{{ route('states.index') }}"
                          id="states-filter-form"
                          class="grid grid-cols-1 md:grid-cols-4 gap-4">

                        <div>
                            <label for="countryid" class="block text-sm font-medium text-gray-700 mb-1">
                                Country
                            </label>
                            <select name="countryid"
                                    id="countryid"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}"
                                        @selected((string) request('countryid') === (string) $country->id)>
                                        {{ $country->countryname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                                Search
                            </label>
                            <input type="text"
                                   name="search"
                                   id="search"
                                   value="{{ request('search') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                   placeholder="Search by code or name">
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                Status
                            </label>
                            <select name="status"
                                    id="status"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All</option>
                                <option value="1" @selected(request('status') === '1')>Active</option>
                                <option value="0" @selected(request('status') === '0')>Inactive</option>
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                Filter
                            </button>

                            <a href="{{ route('states.index') }}"
                               class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm"
                               id="states-reset-link">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <form method="POST"
                      action="{{ route('states.bulk-save') }}"
                      id="states-form">
                    @csrf

                    <input type="hidden" name="countryid" value="{{ request('countryid') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="status" value="{{ request('status') }}">

                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        Country
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        Code
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        State Name
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        Sort
                                    </th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                        Active
                                    </th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                        Actions
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($states as $state)
                                    <tr>
                                        <td class="px-3 py-2">
                                            <select name="existing[{{ $state->id }}][countryid]"
                                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                @foreach($countries as $country)
                                                    <option value="{{ $country->id }}"
                                                        @selected((string) old("existing.{$state->id}.countryid", $state->countryid) === (string) $country->id)>
                                                        {{ $country->countryname }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="existing[{{ $state->id }}][statecode]"
                                                   value="{{ old("existing.{$state->id}.statecode", $state->statecode) }}"
                                                   class="w-24 rounded-md border-gray-300 shadow-sm text-sm uppercase"
                                                   maxlength="10"
                                                   required>
                                        </td>

                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="existing[{{ $state->id }}][statename]"
                                                   value="{{ old("existing.{$state->id}.statename", $state->statename) }}"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                   required>
                                        </td>

                                        <td class="px-3 py-2">
                                            <input type="number"
                                                   name="existing[{{ $state->id }}][sortorder]"
                                                   value="{{ old("existing.{$state->id}.sortorder", $state->sortorder) }}"
                                                   class="w-24 rounded-md border-gray-300 shadow-sm text-sm">
                                        </td>

                                        <td class="px-3 py-2 text-center">
                                            <input type="hidden" name="existing[{{ $state->id }}][isactive]" value="0">
                                            <input type="checkbox"
                                                   name="existing[{{ $state->id }}][isactive]"
                                                   value="1"
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm"
                                                   @checked(old("existing.{$state->id}.isactive", $state->isactive))>
                                        </td>

                                        <td class="px-3 py-2 text-center whitespace-nowrap">
                                            <button type="button"
                                                    class="px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm delete-state-btn"
                                                    data-id="{{ $state->id }}"
                                                    data-name="{{ $state->statename }}"
                                                    data-action="{{ route('states.destroy', $state->id) }}">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-3 py-6 text-center text-sm text-gray-500">
                                            No states found.
                                        </td>
                                    </tr>
                                @endforelse

                                <tr class="bg-blue-50">
                                    <td class="px-3 py-2">
                                        <select name="new[countryid]"
                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                            <option value="">Select country</option>
                                            @foreach($countries as $country)
                                                <option value="{{ $country->id }}"
                                                    @selected((string) old('new.countryid', request('countryid')) === (string) $country->id)>
                                                    {{ $country->countryname }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[statecode]"
                                               value="{{ old('new.statecode') }}"
                                               class="w-24 rounded-md border-gray-300 shadow-sm text-sm uppercase"
                                               maxlength="10"
                                               placeholder="VIC">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[statename]"
                                               value="{{ old('new.statename') }}"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                               placeholder="New state or territory name">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="number"
                                               name="new[sortorder]"
                                               value="{{ old('new.sortorder') }}"
                                               class="w-24 rounded-md border-gray-300 shadow-sm text-sm">
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
                            Edit rows above, add a new state at the bottom, then save once.
                        </p>

                        <button type="submit"
                                class="px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                                id="states-save-button">
                            Save States
                        </button>
                    </div>
                </form>

                @include('partials.admin.compact-delete-form', [
                    'formId' => 'delete-state-form',
                    'query' => request()->only(['countryid', 'search', 'status']),
                ])
            </div>
        </div>
    </div>

    @include('partials.admin.dirty-form-script', [
        'formId' => 'states-form',
        'filterFormId' => 'states-filter-form',
        'deleteFormId' => 'delete-state-form',
        'deleteButtonSelector' => '.delete-state-btn',
        'dirtyMessage' => 'You have unsaved changes in the States table. Continue and lose those changes?',
        'deleteDirtyMessage' => 'You have unsaved changes in the States table. Delete anyway and lose those changes?',
        'deleteConfirmPrefix' => 'Delete state',
        'deleteConfirmSuffix' => 'This cannot be undone.',
    ])
</x-app-layout>