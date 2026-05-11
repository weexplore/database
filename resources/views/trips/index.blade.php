<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Trips
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <form method="GET"
                          action="{{ route('trips.index') }}"
                          id="trips-filter-form"
                          class="grid grid-cols-1 md:grid-cols-4 gap-4">

                        <div>
                            <label for="tripstatus" class="block text-sm font-medium text-gray-700 mb-1">
                                Status
                            </label>
                            <select name="tripstatus"
                                    id="tripstatus"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All</option>
                                @foreach($statusOptions as $status)
                                    <option value="{{ $status }}"
                                        @selected(request('tripstatus') === $status)>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="year" class="block text-sm font-medium text-gray-700 mb-1">
                                Year
                            </label>
                            <select name="year"
                                    id="year"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All</option>
                                @foreach($availableYears as $year)
                                    <option value="{{ $year }}"
                                        @selected((string) request('year') === (string) $year)>
                                        {{ $year }}
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
                                   placeholder="Trip name, slug, summary">
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                Filter
                            </button>

                            <a href="{{ route('trips.index') }}"
                               class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm"
                               id="trips-reset-link">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <form method="POST"
                      action="{{ route('trips.bulk-save') }}"
                      id="trips-form">
                    @csrf

                    <input type="hidden" name="tripstatus" value="{{ request('tripstatus') }}">
                    <input type="hidden" name="year" value="{{ request('year') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">

                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        Trip
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        Status
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        Start
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        End
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        Travellers
                                    </th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                        Locked
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        Linked Travellers
                                    </th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                        Actions
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($trips as $trip)
                                    <tr>
                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="existing[{{ $trip->id }}][tripname]"
                                                   value="{{ old("existing.{$trip->id}.tripname", $trip->tripname) }}"
                                                   class="w-64 rounded-md border-gray-300 shadow-sm text-sm"
                                                   required>
                                        </td>

                                        <td class="px-3 py-2">
                                            <select name="existing[{{ $trip->id }}][tripstatus]"
                                                    class="w-36 rounded-md border-gray-300 shadow-sm text-sm">
                                                @foreach($statusOptions as $status)
                                                    <option value="{{ $status }}"
                                                        @selected(old("existing.{$trip->id}.tripstatus", $trip->tripstatus) === $status)>
                                                        {{ ucfirst($status) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td class="px-3 py-2">
                                            <input type="date"
                                                   name="existing[{{ $trip->id }}][startdate]"
                                                   value="{{ old("existing.{$trip->id}.startdate", optional($trip->startdate)->format('Y-m-d')) }}"
                                                   class="w-40 rounded-md border-gray-300 shadow-sm text-sm">
                                        </td>

                                        <td class="px-3 py-2">
                                            <input type="date"
                                                   name="existing[{{ $trip->id }}][enddate]"
                                                   value="{{ old("existing.{$trip->id}.enddate", optional($trip->enddate)->format('Y-m-d')) }}"
                                                   class="w-40 rounded-md border-gray-300 shadow-sm text-sm">
                                        </td>

                                        <td class="px-3 py-2">
                                            <input type="number"
                                                   name="existing[{{ $trip->id }}][travellercount]"
                                                   value="{{ old("existing.{$trip->id}.travellercount", $trip->travellercount) }}"
                                                   class="w-24 rounded-md border-gray-300 shadow-sm text-sm"
                                                   min="1"
                                                   max="20">
                                        </td>

                                        <td class="px-3 py-2 text-center">
                                            <input type="hidden" name="existing[{{ $trip->id }}][islocked]" value="0">
                                            <input type="checkbox"
                                                   name="existing[{{ $trip->id }}][islocked]"
                                                   value="1"
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm"
                                                   @checked(old("existing.{$trip->id}.islocked", $trip->islocked))>
                                        </td>

                                        <td class="px-3 py-2 text-sm text-gray-600">
                                            {{ $trip->travellers->pluck('displayname')->filter()->join(', ') ?: '—' }}
                                        </td>

                                        <td class="px-3 py-2 text-center whitespace-nowrap">
                                            <a href="{{ route('trips.edit', $trip) }}"
                                               class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 text-xs">
                                                Open
                                            </a>


                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-3 py-6 text-center text-sm text-gray-500">
                                            No trips found.
                                        </td>
                                    </tr>
                                @endforelse

                                <tr class="bg-blue-50">
                                    <td class="px-3 py-2">
                                        <input type="text"
                                               name="new[tripname]"
                                               value="{{ old('new.tripname') }}"
                                               class="w-64 rounded-md border-gray-300 shadow-sm text-sm"
                                               placeholder="New trip name">
                                    </td>

                                    <td class="px-3 py-2">
                                        <select name="new[tripstatus]"
                                                class="w-36 rounded-md border-gray-300 shadow-sm text-sm">
                                            @foreach($statusOptions as $status)
                                                <option value="{{ $status }}"
                                                    @selected(old('new.tripstatus', 'planned') === $status)>
                                                    {{ ucfirst($status) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="date"
                                               name="new[startdate]"
                                               value="{{ old('new.startdate') }}"
                                               class="w-40 rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="date"
                                               name="new[enddate]"
                                               value="{{ old('new.enddate') }}"
                                               class="w-40 rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>

                                    <td class="px-3 py-2">
                                        <input type="number"
                                               name="new[travellercount]"
                                               value="{{ old('new.travellercount', 2) }}"
                                               class="w-24 rounded-md border-gray-300 shadow-sm text-sm"
                                               min="1"
                                               max="20">
                                    </td>

                                    <td class="px-3 py-2 text-center">
                                        <input type="hidden" name="new[islocked]" value="0">
                                        <input type="checkbox"
                                               name="new[islocked]"
                                               value="1"
                                               class="rounded border-gray-300 text-blue-600 shadow-sm"
                                               @checked(old('new.islocked', false))>
                                    </td>

                                    <td class="px-3 py-2 text-xs text-gray-500">
                                        Set on detail screen
                                    </td>

                                    <td class="px-3 py-2 text-xs text-gray-500 text-center">
                                        New row
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-6 border-t border-gray-200 flex items-center justify-between">
                        <p class="text-sm text-gray-500">
                            Edit summary fields here. Open a trip for planning notes, budgets, fuel settings, and traveller assignment.
                        </p>

                        <button type="submit"
                                class="px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                                id="trips-save-button">
                            Save Trips
                        </button>
                    </div>
                </form>

                @include('partials.admin.compact-delete-form', [
                    'formId' => 'delete-trip-form',
                    'query' => request()->only(['tripstatus', 'year', 'search']),
                ])
            </div>

            <div>
                {{ $trips->links() }}
            </div>
        </div>
    </div>

    @include('partials.admin.dirty-form-script', [
        'formId' => 'trips-form',
        'filterFormId' => 'trips-filter-form',
        'deleteFormId' => 'delete-trip-form',
        'deleteButtonSelector' => '.delete-trip-btn',
        'dirtyMessage' => 'You have unsaved changes in the Trips table. Continue and lose those changes?',
        'deleteDirtyMessage' => 'You have unsaved changes in the Trips table. Delete anyway and lose those changes?',
        'deleteConfirmPrefix' => 'Delete trip',
        'deleteConfirmSuffix' => 'This cannot be undone.',
    ])
</x-app-layout>