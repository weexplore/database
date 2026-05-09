{{-- resources/views/places/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Places
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                {{-- Filters --}}
                <div class="p-6 border-b border-gray-200">
                    <form
                        method="GET"
                        action="{{ route('places.index') }}"
                        id="places-filter-form"
                        class="grid grid-cols-1 md:grid-cols-7 gap-4"
                    >
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                                Search
                            </label>
                            <input
                                type="text"
                                name="search"
                                id="search"
                                value="{{ request('search') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm"
                                placeholder="Search by place or location"
                            >
                        </div>

                        <div>
                            <label for="country_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Country
                            </label>
                            <select
                                name="country_id"
                                id="country_id"
                                class="w-full rounded-md border-gray-300 shadow-sm"
                                onchange="
                                    document.getElementById('state_id').value = '';
                                    document.getElementById('region_id').value = '';
                                    this.form.submit();
                                "
                            >
                                <option value="">All</option>
                                @foreach ($countries as $country)
                                    <option
                                        value="{{ (int) $country->id }}"
                                        @selected((string) request('country_id') === (string) $country->id)
                                    >
                                        {{ $country->countryname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="state_id" class="block text-sm font-medium text-gray-700 mb-1">
                                State
                            </label>
                            <select
                                name="state_id"
                                id="state_id"
                                class="w-full rounded-md border-gray-300 shadow-sm"
                                onchange="
                                    document.getElementById('region_id').value = '';
                                    this.form.submit();
                                "
                            >
                                <option value="">All</option>
                                @foreach ($filterStates as $state)
                                    <option
                                        value="{{ (int) $state->id }}"
                                        @selected((string) request('state_id') === (string) $state->id)
                                    >
                                        {{ $state->statename }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="region_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Region
                            </label>
                            <select
                                name="region_id"
                                id="region_id"
                                class="w-full rounded-md border-gray-300 shadow-sm"
                                onchange="this.form.submit()"
                            >
                                <option value="">All</option>
                                @foreach ($filterRegions as $region)
                                    <option
                                        value="{{ (int) $region->id }}"
                                        @selected((string) request('region_id') === (string) $region->id)
                                    >
                                        {{ $region->regionname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="placetype" class="block text-sm font-medium text-gray-700 mb-1">
                                Type
                            </label>
                            <select
                                name="placetype"
                                id="placetype"
                                class="w-full rounded-md border-gray-300 shadow-sm"
                            >
                                <option value="">All</option>
                                @foreach ($placeTypes as $value => $label)
                                    <option
                                        value="{{ $value }}"
                                        @selected((string) request('placetype') === (string) $value)
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                Status
                            </label>
                            <select
                                name="status"
                                id="status"
                                class="w-full rounded-md border-gray-300 shadow-sm"
                            >
                                <option value="">All</option>
                                <option value="1" @selected(request('status') === '1')>Active</option>
                                <option value="0" @selected(request('status') === '0')>Inactive</option>
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button
                                type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                            >
                                Filter
                            </button>
                            <a
                                href="{{ route('places.index') }}"
                                class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300"
                            >
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                {{-- Inline edit form + table --}}
                <form method="POST" action="{{ route('places.bulk-save') }}" id="places-form">
                    @csrf

                    <input type="hidden" name="return_to" value="{{ url()->full() }}">

                    @foreach (request()->only(['search', 'country_id', 'state_id', 'region_id', 'placetype', 'status', 'page']) as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Place</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Country</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">State</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Region</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Postcode</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Active</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Coordinates</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Destination</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($places as $place)
                                    @php
                                        $rowCountryId = old("existing.{$place->id}.country_id", $place->countryid);
                                        $rowStateId = old("existing.{$place->id}.state_id", $place->stateid);
                                        $rowRegionId = old("existing.{$place->id}.region_id", $place->regionid);

                                        $rowStates = $statesByCountry->get($rowCountryId, collect());
                                        $rowRegions = $rowStateId
                                            ? $regionsByState->get($rowStateId, collect())
                                            : $regionsByCountry->get($rowCountryId, collect());
                                    @endphp

                                    <tr>
                                        <td class="px-4 py-3">
                                            <input
                                                type="text"
                                                name="existing[{{ $place->id }}][placename]"
                                                value="{{ old("existing.{$place->id}.placename", $place->placename) }}"
                                                class="w-full min-w-[180px] rounded-md border-gray-300 shadow-sm"
                                                required
                                            >
                                        </td>

                                        <td class="px-4 py-3">
                                            <select
                                                name="existing[{{ $place->id }}][country_id]"
                                                class="w-full min-w-[150px] rounded-md border-gray-300 shadow-sm"
                                                required
                                            >
                                                <option value="">Select</option>
                                                @foreach ($countries as $country)
                                                    <option
                                                        value="{{ (int) $country->id }}"
                                                        @selected((string) $rowCountryId === (string) $country->id)
                                                    >
                                                        {{ $country->countryname }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td class="px-4 py-3">
                                            <select
                                                name="existing[{{ $place->id }}][state_id]"
                                                class="w-full min-w-[160px] rounded-md border-gray-300 shadow-sm"
                                            >
                                                <option value="">None</option>
                                                @foreach ($rowStates as $state)
                                                    <option
                                                        value="{{ (int) $state->id }}"
                                                        @selected((string) $rowStateId === (string) $state->id)
                                                    >
                                                        {{ $state->statename }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td class="px-4 py-3">
                                            <select
                                                name="existing[{{ $place->id }}][region_id]"
                                                class="w-full min-w-[160px] rounded-md border-gray-300 shadow-sm"
                                            >
                                                <option value="">None</option>
                                                @foreach ($rowRegions as $region)
                                                    <option
                                                        value="{{ (int) $region->id }}"
                                                        @selected((string) $rowRegionId === (string) $region->id)
                                                    >
                                                        {{ $region->regionname }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td class="px-4 py-3">
                                            <select
                                                name="existing[{{ $place->id }}][placetype]"
                                                class="w-full min-w-[180px] rounded-md border-gray-300 shadow-sm"
                                            >
                                                <option value="">None</option>
                                                @foreach ($placeTypes as $value => $label)
                                                    <option
                                                        value="{{ $value }}"
                                                        @selected((string) old("existing.{$place->id}.placetype", $place->placetype) === (string) $value)
                                                    >
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td class="px-4 py-3">
                                            <input
                                                type="text"
                                                name="existing[{{ $place->id }}][locality]"
                                                value="{{ old("existing.{$place->id}.locality", $place->locality) }}"
                                                class="w-full min-w-[150px] rounded-md border-gray-300 shadow-sm"
                                            >
                                        </td>

                                        <td class="px-4 py-3">
                                            <input
                                                type="text"
                                                name="existing[{{ $place->id }}][postcode]"
                                                value="{{ old("existing.{$place->id}.postcode", $place->postcode) }}"
                                                class="w-full min-w-[80px] rounded-md border-gray-300 shadow-sm"
                                            >
                                        </td>

                                        <td class="px-4 py-3 text-center">
                                            <input type="hidden" name="existing[{{ $place->id }}][isactive]" value="0">
                                            <input
                                                type="checkbox"
                                                name="existing[{{ $place->id }}][isactive]"
                                                value="1"
                                                class="rounded border-gray-300 text-blue-600 shadow-sm"
                                                @checked(old("existing.{$place->id}.isactive", $place->isactive))
                                            >
                                        </td>
                                        <td class="px-4 py-3">
                                            @if(!is_null($place->latitude) && !is_null($place->longitude))
                                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-green-100 text-green-800 text-xs font-medium">
                                                    Yes
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-medium">
                                                    No
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if(($place->destinations_count ?? 0) > 0)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-green-100 text-green-800 text-xs font-medium">
                                                    Yes
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-medium">
                                                    Missing
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <a
                                                href="{{ route('places.edit', [
                                                    'place' => $place,
                                                    'return_to' => url()->full(),
                                                ]) }}"
                                                class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-sm"
                                            >
                                                Edit
                                            </a>
                                            <button
                                                type="button"
                                                class="px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm js-delete-record"
                                                data-id="{{ $place->id }}"
                                                data-name="{{ $place->placename }}"
                                                data-action="{{ route('places.destroy', $place->id) }}"
                                            >
                                                Delete
                                            </button>
                                        </td>

                                        <input type="hidden" name="existing[{{ $place->id }}][addressline1]" value="{{ old("existing.{$place->id}.addressline1", $place->addressline1) }}">
                                        <input type="hidden" name="existing[{{ $place->id }}][addressline2]" value="{{ old("existing.{$place->id}.addressline2", $place->addressline2) }}">
                                        <input type="hidden" name="existing[{{ $place->id }}][latitude]" value="{{ old("existing.{$place->id}.latitude", $place->latitude) }}">
                                        <input type="hidden" name="existing[{{ $place->id }}][longitude]" value="{{ old("existing.{$place->id}.longitude", $place->longitude) }}">
                                        <input type="hidden" name="existing[{{ $place->id }}][accessnotes]" value="{{ old("existing.{$place->id}.accessnotes", $place->accessnotes) }}">
                                        <input type="hidden" name="existing[{{ $place->id }}][generalnotes]" value="{{ old("existing.{$place->id}.generalnotes", $place->generalnotes) }}">
                                        <input type="hidden" name="existing[{{ $place->id }}][sourcequality]" value="{{ old("existing.{$place->id}.sourcequality", $place->sourcequality) }}">
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-4 py-6 text-center text-gray-500">
                                            No places found.
                                        </td>
                                    </tr>
                                @endforelse

                                @php
                                    $newCountryId = old('new.country_id');
                                    $newStateId = old('new.state_id');
                                    $newRegionId = old('new.region_id');

                                    $newStates = $statesByCountry->get($newCountryId, collect());
                                    $newRegions = $newStateId
                                        ? $regionsByState->get($newStateId, collect())
                                        : $regionsByCountry->get($newCountryId, collect());
                                @endphp

                                <tr class="bg-gray-50">
                                    <td colspan="9" class="px-4 py-2 text-sm font-medium text-gray-600">
                                        Add new place
                                    </td>
                                </tr>

                                <tr class="bg-blue-50/40">
                                    <td class="px-4 py-3">
                                        <input
                                            type="text"
                                            name="new[placename]"
                                            value="{{ old('new.placename') }}"
                                            class="w-full min-w-[180px] rounded-md border-gray-300 shadow-sm"
                                            placeholder="New place name"
                                        >
                                    </td>

                                    <td class="px-4 py-3">
                                        <select
                                            name="new[country_id]"
                                            id="new_country_id"
                                            class="w-full min-w-[150px] rounded-md border-gray-300 shadow-sm"
                                        >
                                            <option value="">Select</option>
                                            @foreach ($countries as $country)
                                                <option
                                                    value="{{ (int) $country->id }}"
                                                    @selected((string) $newCountryId === (string) $country->id)
                                                >
                                                    {{ $country->countryname }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="px-4 py-3">
                                        <select
                                            name="new[state_id]"
                                            id="new_state_id"
                                            class="w-full min-w-[160px] rounded-md border-gray-300 shadow-sm"
                                        >   
                                            <option value="">None</option>
                                            @foreach ($newStates as $state)
                                                <option
                                                    value="{{ (int) $state->id }}"
                                                    @selected((string) $newStateId === (string) $state->id)
                                                >
                                                    {{ $state->statename }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="px-4 py-3">
                                        <select
                                            name="new[region_id]"
                                            id="new_region_id"
                                            class="w-full min-w-[160px] rounded-md border-gray-300 shadow-sm"
                                        >
                                            <option value="">None</option>
                                            @foreach ($newRegions as $region)
                                                <option
                                                    value="{{ (int) $region->id }}"
                                                    @selected((string) $newRegionId === (string) $region->id)
                                                >
                                                    {{ $region->regionname }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="px-4 py-3">
                                        <select
                                            name="new[placetype]"
                                            class="w-full min-w-[180px] rounded-md border-gray-300 shadow-sm"
                                        >
                                            <option value="">None</option>
                                            @foreach ($placeTypes as $value => $label)
                                                <option
                                                    value="{{ $value }}"
                                                    @selected((string) old('new.placetype') === (string) $value)
                                                >
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="px-4 py-3">
                                        <input
                                            type="text"
                                            name="new[locality]"
                                            value="{{ old('new.locality') }}"
                                            class="w-full min-w-[150px] rounded-md border-gray-300 shadow-sm"
                                            placeholder="Locality"
                                        >
                                    </td>

                                    <td class="px-4 py-3">
                                        <input
                                            type="text"
                                            name="new[postcode]"
                                            value="{{ old('new.postcode') }}"
                                            class="w-full min-w-[80px] rounded-md border-gray-300 shadow-sm"
                                            placeholder="Postcode"
                                        >
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <input type="hidden" name="new[isactive]" value="0">
                                        <input
                                            type="checkbox"
                                            name="new[isactive]"
                                            value="1"
                                            class="rounded border-gray-300 text-blue-600 shadow-sm"
                                            @checked(old('new.isactive', 1))
                                        >
                                    </td>

                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <span class="text-sm text-gray-500">
                                            New
                                        </span>
                                    </td>

                                    <input type="hidden" name="new[addressline1]" value="{{ old('new.addressline1') }}">
                                    <input type="hidden" name="new[addressline2]" value="{{ old('new.addressline2') }}">
                                    <input type="hidden" name="new[latitude]" value="{{ old('new.latitude') }}">
                                    <input type="hidden" name="new[longitude]" value="{{ old('new.longitude') }}">
                                    <input type="hidden" name="new[accessnotes]" value="{{ old('new.accessnotes') }}">
                                    <input type="hidden" name="new[generalnotes]" value="{{ old('new.generalnotes') }}">
                                    <input type="hidden" name="new[sourcequality]" value="{{ old('new.sourcequality') }}">
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-6 border-t border-gray-200 flex items-center justify-between">
                        <p class="text-sm text-gray-500">
                            Edit existing rows or enter a new place in the final row, then save once.
                        </p>
<a href="{{ route('reports.places.reference-book', request()->only([
        'search',
        'country_id',
        'state_id',
        'region_id',
        'placetype',
        'status',
    ])) }}"
   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
    Reference Book Report
</a>
                        <button
                            type="submit"
                            class="px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                        >
                            Save Places
                        </button>
                    </div>
                </form>

                @if ($places->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $places->links() }}
                    </div>
                @endif

                @include('partials.admin.compact-delete-form', [
                    'formId' => 'delete-place-form',
                    'query' => request()->only(['search', 'country_id', 'state_id', 'region_id', 'placetype', 'status', 'page']),
                ])
            </div>
        </div>
    </div>

    @include('partials.admin.dirty-form-script', [
        'formId' => 'places-form',
        'filterFormId' => 'places-filter-form',
        'deleteFormId' => 'delete-place-form',
        'deleteButtonSelector' => '.js-delete-record',
        'dirtyMessage' => 'You have unsaved changes. Continue and lose those changes?',
        'deleteDirtyMessage' => 'You have unsaved changes. Delete anyway and lose those changes?',
        'deleteConfirmPrefix' => 'Delete',
        'deleteConfirmSuffix' => 'This cannot be undone.',
    ])
    <script>
document.addEventListener('DOMContentLoaded', function () {
    const statesByCountry = @json($statesByCountryForJs);
    const regionsByCountry = @json($regionsByCountryForJs);
    const regionsByState = @json($regionsByStateForJs);

    const countrySelect = document.getElementById('new_country_id');
    const stateSelect = document.getElementById('new_state_id');
    const regionSelect = document.getElementById('new_region_id');

    if (!countrySelect || !stateSelect || !regionSelect) {
        return;
    }

    function fillSelect(select, placeholder, items, selectedValue = '') {
        select.innerHTML = '';

        const first = document.createElement('option');
        first.value = '';
        first.textContent = placeholder;
        select.appendChild(first);

        items.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name;

            if (String(selectedValue) === String(item.id)) {
                option.selected = true;
            }

            select.appendChild(option);
        });
    }

    function loadStates(selectedStateId = '') {
        const countryId = countrySelect.value;
        const states = countryId && statesByCountry[countryId] ? statesByCountry[countryId] : [];
        fillSelect(stateSelect, 'None', states, selectedStateId);
    }

    function loadRegions(selectedRegionId = '') {
        const countryId = countrySelect.value;
        const stateId = stateSelect.value;

        let regions = [];

        if (stateId && regionsByState[stateId]) {
            regions = regionsByState[stateId];
        } else if (countryId && regionsByCountry[countryId]) {
            regions = regionsByCountry[countryId];
        }

        fillSelect(regionSelect, 'None', regions, selectedRegionId);
    }

    countrySelect.addEventListener('change', function () {
        loadStates('');
        loadRegions('');
    });

    stateSelect.addEventListener('change', function () {
        loadRegions('');
    });

    loadStates(@json(old('new.state_id')));
    loadRegions(@json(old('new.region_id')));
});
</script>
</x-app-layout>