<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Regions
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <form method="GET" action="{{ route('regions.index') }}" id="regions-filter-form" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input type="text"
                                   name="search"
                                   id="search"
                                   value="{{ request('search') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm"
                                   placeholder="Search by region name">
                        </div>

                        <div>
                            <label for="country_id" class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                            <select name="country_id" id="country_id" class="w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">All</option>
                                @foreach ($countries as $country)
                                    <option value="{{ $country->id }}" @selected((string) request('country_id') === (string) $country->id)>
                                        {{ $country->countryname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="state_id" class="block text-sm font-medium text-gray-700 mb-1">State</label>
                            <select name="state_id" id="state_id" class="w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">All</option>
                                @foreach ($states as $state)
                                    <option value="{{ $state->id }}" @selected((string) request('state_id') === (string) $state->id)>
                                        {{ $state->statename }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">All</option>
                                <option value="1" @selected(request('status') === '1')>Active</option>
                                <option value="0" @selected(request('status') === '0')>Inactive</option>
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Filter
                            </button>

                            <a href="{{ route('regions.index') }}"
                               class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300"
                               id="regions-reset-link">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <form method="POST" action="{{ route('regions.bulk-save') }}" id="regions-form">
                    @csrf

                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="country_id" value="{{ request('country_id') }}">
                    <input type="hidden" name="state_id" value="{{ request('state_id') }}">
                    <input type="hidden" name="status" value="{{ request('status') }}">

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[1250px] divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-[24%]">Region Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-[22%]">Country</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-[22%]">State</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-[14%]">Type</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-[8%]">Sort</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase w-[5%]">Active</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase w-[5%]">Actions</th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($regions as $region)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <input type="text"
                                                   name="existing[{{ $region->id }}][regionname]"
                                                   value="{{ old("existing.{$region->id}.regionname", $region->regionname) }}"
                                                   class="w-full min-w-[240px] rounded-md border-gray-300 shadow-sm"
                                                   required>
                                        </td>

                                        <td class="px-4 py-3">
                                            <select name="existing[{{ $region->id }}][country_id]"
                                                    class="w-full min-w-[180px] rounded-md border-gray-300 shadow-sm">
                                                <option value="">None</option>
                                                @foreach ($countries as $country)
                                                    <option value="{{ $country->id }}"
                                                        @selected((string) old("existing.{$region->id}.country_id", $region->countryid) === (string) $country->id)>
                                                        {{ $country->countryname }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td class="px-4 py-3">
                                            <select name="existing[{{ $region->id }}][state_id]"
                                                    class="w-full min-w-[180px] rounded-md border-gray-300 shadow-sm">
                                                <option value="">None</option>
                                                @foreach ($states as $state)
                                                    <option value="{{ $state->id }}"
                                                        @selected((string) old("existing.{$region->id}.state_id", $region->stateid) === (string) $state->id)>
                                                        {{ $state->statename }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td class="px-4 py-3">
                                            <input type="text"
                                                   name="existing[{{ $region->id }}][regiontype]"
                                                   value="{{ old("existing.{$region->id}.regiontype", $region->regiontype) }}"
                                                   class="w-full min-w-[140px] rounded-md border-gray-300 shadow-sm"
                                                   placeholder="tourismregion">
                                        </td>

                                        <td class="px-4 py-3">
                                            <input type="number"
                                                   name="existing[{{ $region->id }}][sortorder]"
                                                   value="{{ old("existing.{$region->id}.sortorder", $region->sortorder) }}"
                                                   class="w-full min-w-[90px] rounded-md border-gray-300 shadow-sm">
                                        </td>

                                        <td class="px-4 py-3 text-center">
                                            <input type="hidden" name="existing[{{ $region->id }}][isactive]" value="0">
                                            <input type="checkbox"
                                                   name="existing[{{ $region->id }}][isactive]"
                                                   value="1"
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm"
                                                   @checked(old("existing.{$region->id}.isactive", $region->isactive))>
                                        </td>

                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <button type="button"
                                                    class="px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm delete-region-btn"
                                                    data-id="{{ $region->id }}"
                                                    data-name="{{ $region->regionname }}"
                                                    data-action="{{ route('regions.destroy', $region->id) }}">
                                                Delete
                                            </button>
                                        </td>

                                        <input type="hidden" name="existing[{{ $region->id }}][notes]" value="{{ old("existing.{$region->id}.notes", $region->notes) }}">
                                        <input type="hidden" name="existing[{{ $region->id }}][parentregionid]" value="{{ old("existing.{$region->id}.parentregionid", $region->parentregionid) }}">
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                            No regions found.
                                        </td>
                                    </tr>
                                @endforelse

                                <tr class="bg-blue-50">
                                    <td class="px-4 py-3">
                                        <input type="text"
                                               name="new[regionname]"
                                               value="{{ old('new.regionname') }}"
                                               class="w-full min-w-[240px] rounded-md border-gray-300 shadow-sm"
                                               placeholder="New region name">
                                    </td>

                                    <td class="px-4 py-3">
                                        <select name="new[country_id]" class="w-full min-w-[180px] rounded-md border-gray-300 shadow-sm">
                                            <option value="">None</option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country->id }}" @selected((string) old('new.country_id') === (string) $country->id)>
                                                    {{ $country->countryname }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="px-4 py-3">
                                        <select name="new[state_id]" class="w-full min-w-[180px] rounded-md border-gray-300 shadow-sm">
                                            <option value="">None</option>
                                            @foreach ($states as $state)
                                                <option value="{{ $state->id }}" @selected((string) old('new.state_id') === (string) $state->id)>
                                                    {{ $state->statename }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="px-4 py-3">
                                        <input type="text"
                                               name="new[regiontype]"
                                               value="{{ old('new.regiontype') }}"
                                               class="w-full min-w-[140px] rounded-md border-gray-300 shadow-sm"
                                               placeholder="travelregion">
                                    </td>

                                    <td class="px-4 py-3">
                                        <input type="number"
                                               name="new[sortorder]"
                                               value="{{ old('new.sortorder') }}"
                                               class="w-full min-w-[90px] rounded-md border-gray-300 shadow-sm"
                                               placeholder="Sort">
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <input type="hidden" name="new[isactive]" value="0">
                                        <input type="checkbox"
                                               name="new[isactive]"
                                               value="1"
                                               class="rounded border-gray-300 text-blue-600 shadow-sm"
                                               @checked(old('new.isactive', true))>
                                    </td>

                                    <td class="px-4 py-3 text-center text-sm text-gray-400 whitespace-nowrap">
                                        New row
                                    </td>

                                    <input type="hidden" name="new[notes]" value="{{ old('new.notes') }}">
                                    <input type="hidden" name="new[parentregionid]" value="{{ old('new.parentregionid') }}">
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-6 border-t border-gray-200 flex items-center justify-between">
                        <p class="text-sm text-gray-500">
                            Edit rows above, add a new region at the bottom, then save once.
                        </p>

                        <button type="submit"
                                class="px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                                id="regions-save-button">
                            Save Regions
                        </button>
                    </div>
                </form>

                @include('partials.admin.compact-delete-form', [
                    'formId' => 'delete-region-form',
                    'query' => request()->only(['search', 'country_id', 'state_id', 'status']),
                ])
            </div>
        </div>
    </div>

    @include('partials.admin.dirty-form-script', [
        'formId' => 'regions-form',
        'filterFormId' => 'regions-filter-form',
        'deleteFormId' => 'delete-region-form',
        'deleteButtonSelector' => '.delete-region-btn',
        'dirtyMessage' => 'You have unsaved changes in the Regions table. Continue and lose those changes?',
        'deleteDirtyMessage' => 'You have unsaved changes in the Regions table. Delete anyway and lose those changes?',
        'deleteConfirmPrefix' => 'Delete region',
        'deleteConfirmSuffix' => 'This cannot be undone.',
    ])
</x-app-layout>