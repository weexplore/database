<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Travel Interests
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Shared places, destinations and destination items worth considering for future trips.
                </p>
            </div>

        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900">
                        Find Travel Interests
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Browse shared travel priorities by country, state, region, place and interest level.
                    </p>
                </div>

                <form
                    method="GET"
                    action="{{ route('travel-interests.index') }}"
                    id="travel-interests-filter-form"
                    class="p-6"
                >
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-8">
                        <div>
                            <label for="countryid" class="block text-sm font-medium text-gray-700">
                                Country
                            </label>

                            <select
                                name="countryid"
                                id="countryid"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"
                            >
                                <option value="">All countries</option>

                                @foreach ($countries as $country)
                                    <option
                                        value="{{ $country->id }}"
                                        @if ((string) request('countryid') === (string) $country->id)
                                            selected
                                        @endif
                                    >
                                        {{ $country->countryname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="stateid" class="block text-sm font-medium text-gray-700">
                                State
                            </label>

                            <select
                                name="stateid"
                                id="stateid"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"
                            >
                                <option value="">All states</option>

                                @foreach ($states as $state)
                                    <option
                                        value="{{ $state->id }}"
                                        data-country-id="{{ $state->countryid }}"
                                        @if ((string) request('stateid') === (string) $state->id)
                                            selected
                                        @endif
                                    >
                                        {{ $state->statecode ? $state->statecode : $state->statename }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="regionid" class="block text-sm font-medium text-gray-700">
                                Region
                            </label>

                            <select
                                name="regionid"
                                id="regionid"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"
                            >
                                <option value="">All regions</option>

                                @foreach ($regions as $region)
                                    <option
                                        value="{{ $region->id }}"
                                        data-country-id="{{ $region->countryid }}"
                                        data-state-id="{{ $region->stateid }}"
                                        @if ((string) request('regionid') === (string) $region->id)
                                            selected
                                        @endif
                                    >
                                        {{ $region->regionname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="placeid" class="block text-sm font-medium text-gray-700">
                                Place
                            </label>

                            <select
                                name="placeid"
                                id="placeid"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"
                            >
                                <option value="">All places</option>

                                @foreach ($places as $place)
                                    <option
                                        value="{{ $place->id }}"
                                        data-country-id="{{ $place->countryid }}"
                                        data-state-id="{{ $place->stateid }}"
                                        data-region-id="{{ $place->regionid }}"
                                        @if ((string) request('placeid') === (string) $place->id)
                                            selected
                                        @endif
                                    >
                                        {{ $place->placename }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="interest" class="block text-sm font-medium text-gray-700">
                                Travel interest
                            </label>

                            <select
                                name="interest"
                                id="interest"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"
                            >
                                <option value="">All priorities</option>

                                @foreach ($interestOptions as $value => $label)
                                    <option
                                        value="{{ $value }}"
                                        @if (request('interest') === $value)
                                            selected
                                        @endif
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="itemtypeid" class="block text-sm font-medium text-gray-700">
                                Item type
                            </label>

                            <select
                                name="itemtypeid"
                                id="itemtypeid"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"
                            >
                                <option value="">All item types</option>

                                @foreach ($itemTypes as $itemType)
                                    <option
                                        value="{{ $itemType->id }}"
                                        @if ((string) request('itemtypeid') === (string) $itemType->id)
                                            selected
                                        @endif
                                    >
                                        {{ $itemType->typename }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="scope" class="block text-sm font-medium text-gray-700">
                                Show
                            </label>

                            <select
                                name="scope"
                                id="scope"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"
                            >
                                <option value="all" @if ($scope === 'all') selected @endif>
                                    Items and destinations
                                </option>

                                <option value="items" @if ($scope === 'items') selected @endif>
                                    Destination items only
                                </option>

                                <option value="destinations" @if ($scope === 'destinations') selected @endif>
                                    Destinations only
                                </option>
                            </select>
                        </div>

                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700">
                                Search
                            </label>

                            <input
                                type="text"
                                name="search"
                                id="search"
                                value="{{ request('search') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"
                                placeholder="Item, destination, place or notes"
                            >
                        </div>
                    </div>

                    <div class="mt-5 flex flex-wrap items-center justify-between gap-4">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input
                                type="checkbox"
                                name="showvisited"
                                value="1"
                                class="rounded border-gray-300 text-blue-600 shadow-sm"
                                @if ($showVisited) checked @endif
                            >

                            Include visited records
                        </label>

                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm"
                            >
                                Apply filters
                            </button>

                            <a
                                href="{{ route('travel-interests.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm"
                            >
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            @if ($scope === 'all' || $scope === 'items')
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Priority Destination Items
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                Individual places and activities that are relevant to future trip planning.
                            </p>
                        </div>

                        <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">
                            {{ number_format($items->total()) }} items
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        Interest
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        Item
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        Type
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        Destination
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        Location
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        Visit status
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        Planning note
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase text-gray-500">
                                        Open
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200 bg-white">
                                @if ($items->count() > 0)
                                    @foreach ($items as $item)
                                        <tr
                                            @if ($item->visitinterestlevel === 'must_visit')
                                                class="bg-rose-50"
                                            @elseif ($item->visitinterestlevel === 'very_interested')
                                                class="bg-amber-50"
                                            @elseif ($item->visitinterestlevel === 'interested')
                                                class="bg-sky-50"
                                            @else
                                                class=""
                                            @endif
                                        >
                                            <td class="px-4 py-3 align-top">
                                                @if ($item->visitinterestlevel === 'must_visit')
                                                    <span class="inline-flex rounded-full border border-rose-200 bg-rose-100 px-2 py-1 text-xs font-medium text-rose-800">
                                                        Must visit
                                                    </span>
                                                @elseif ($item->visitinterestlevel === 'very_interested')
                                                    <span class="inline-flex rounded-full border border-amber-200 bg-amber-100 px-2 py-1 text-xs font-medium text-amber-800">
                                                        Very interested
                                                    </span>
                                                @elseif ($item->visitinterestlevel === 'interested')
                                                    <span class="inline-flex rounded-full border border-sky-200 bg-sky-100 px-2 py-1 text-xs font-medium text-sky-800">
                                                        Interested
                                                    </span>
                                                @elseif ($item->visitinterestlevel === 'if_nearby')
                                                    <span class="inline-flex rounded-full border border-slate-200 bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">
                                                        If nearby
                                                    </span>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>

                                            <td class="px-4 py-3 align-top">
                                                <div class="font-medium text-gray-900">
                                                    {{ $item->itemname }}
                                                </div>

                                                @if ($item->shortdescription)
                                                    <div class="mt-1 max-w-sm text-xs text-gray-500">
                                                        {{ \Illuminate\Support\Str::limit($item->shortdescription, 120) }}
                                                    </div>
                                                @endif
                                            </td>

                                            <td class="px-4 py-3 align-top text-gray-700">
                                                {{ $item->itemTypes->pluck('typename')->filter()->join(', ') ?: '—' }}
                                            </td>

                                            <td class="px-4 py-3 align-top text-gray-700">
                                                {{ $item->destinationname ?: '—' }}
                                            </td>

                                            <td class="px-4 py-3 align-top text-gray-700">
                                                <div>
                                                    {{ $item->effective_placename ?: '—' }}
                                                </div>

                                                <div class="mt-1 text-xs text-gray-500">
                                                    @if ($item->regionname)
                                                        {{ $item->regionname }}
                                                    @endif

                                                    @if ($item->statecode)
                                                        @if ($item->regionname)
                                                            ·
                                                        @endif

                                                        {{ $item->statecode }}
                                                    @endif

                                                    @if ($item->countryname)
                                                        @if ($item->regionname || $item->statecode)
                                                            ·
                                                        @endif

                                                        {{ $item->countryname }}
                                                    @endif
                                                </div>
                                            </td>

                                            <td class="px-4 py-3 align-top">
                                                @if ($item->hasvisited)
                                                    <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">
                                                        Visited
                                                    </span>

                                                    @if ($item->visitedat)
                                                        <div class="mt-1 text-xs text-gray-500">
                                                            {{ $item->visitedat->format('d M Y') }}
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="inline-flex rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                                                        Not visited
                                                    </span>
                                                @endif

                                                @if ($item->stillwanttovisit)
                                                    <div class="mt-1 text-xs font-medium text-indigo-700">
                                                        Still interested
                                                    </div>
                                                @endif
                                            </td>

                                            <td class="px-4 py-3 align-top">
                                                @if ($item->visitinterestnotes)
                                                    <div class="max-w-md whitespace-pre-line text-sm text-gray-700">
                                                        {{ \Illuminate\Support\Str::limit($item->visitinterestnotes, 180) }}
                                                    </div>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif

                                                <div class="mt-2 flex flex-wrap gap-2 text-xs text-gray-500">
                                                    @if ($item->recommendedstayminutes)
                                                        <span>
                                                            {{ $item->recommendedstayminutes }} min
                                                        </span>
                                                    @endif

                                                    @if ($item->bookingrequired)
                                                        <span class="font-medium text-amber-700">
                                                            Booking required
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>

                                            <td class="px-4 py-3 align-top text-center">
                                                <a
                                                    href="{{ route('destination-items.edit', [
                                                        'destinationItem' => $item->id,
                                                        'return_to' => url()->full(),
                                                    ]) }}"
                                                    class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 text-xs"
                                                >
                                                    Item
                                                </a>

                                                <a
                                                    href="{{ route('destinations.edit', [
                                                        'destination' => $item->destinationid,
                                                        'return_to' => url()->full(),
                                                    ]) }}"
                                                    class="mt-2 inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 text-xs"
                                                >
                                                    Destination
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">
                                            No Destination Items match the selected travel-interest filters.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    @if ($items->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200">
                            {{ $items->links() }}
                        </div>
                    @endif
                </div>
            @endif

            @if ($scope === 'all' || $scope === 'destinations')
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Destinations We Want to Explore
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                Broader destination-level interests that may not yet have a specific item selected.
                            </p>
                        </div>

                        <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">
                            {{ number_format($destinations->total()) }} destinations
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        Interest
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        Destination
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        Type
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        Location
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        Visit status
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        Planning note
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase text-gray-500">
                                        Open
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200 bg-white">
                                @if ($destinations->count() > 0)
                                    @foreach ($destinations as $destination)
                                        <tr
                                            @if ($destination->visitinterestlevel === 'must_visit')
                                                class="bg-rose-50"
                                            @elseif ($destination->visitinterestlevel === 'very_interested')
                                                class="bg-amber-50"
                                            @elseif ($destination->visitinterestlevel === 'interested')
                                                class="bg-sky-50"
                                            @else
                                                class=""
                                            @endif
                                        >
                                            <td class="px-4 py-3 align-top">
                                                @if ($destination->visitinterestlevel === 'must_visit')
                                                    <span class="inline-flex rounded-full border border-rose-200 bg-rose-100 px-2 py-1 text-xs font-medium text-rose-800">
                                                        Must visit
                                                    </span>
                                                @elseif ($destination->visitinterestlevel === 'very_interested')
                                                    <span class="inline-flex rounded-full border border-amber-200 bg-amber-100 px-2 py-1 text-xs font-medium text-amber-800">
                                                        Very interested
                                                    </span>
                                                @elseif ($destination->visitinterestlevel === 'interested')
                                                    <span class="inline-flex rounded-full border border-sky-200 bg-sky-100 px-2 py-1 text-xs font-medium text-sky-800">
                                                        Interested
                                                    </span>
                                                @elseif ($destination->visitinterestlevel === 'if_nearby')
                                                    <span class="inline-flex rounded-full border border-slate-200 bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">
                                                        If nearby
                                                    </span>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>

                                            <td class="px-4 py-3 align-top">
                                                <div class="font-medium text-gray-900">
                                                    {{ $destination->destinationname }}
                                                </div>

                                                @if ($destination->bestseason)
                                                    <div class="mt-1 text-xs text-gray-500">
                                                        Best season: {{ $destination->bestseason }}
                                                    </div>
                                                @endif
                                            </td>

                                            <td class="px-4 py-3 align-top text-gray-700">
                                                {{ $destination->destinationtype ?: '—' }}
                                            </td>

                                            <td class="px-4 py-3 align-top text-gray-700">
                                                <div>
                                                    {{ $destination->placename ?: '—' }}
                                                </div>

                                                <div class="mt-1 text-xs text-gray-500">
                                                    @if ($destination->regionname)
                                                        {{ $destination->regionname }}
                                                    @endif

                                                    @if ($destination->statecode)
                                                        @if ($destination->regionname)
                                                            ·
                                                        @endif

                                                        {{ $destination->statecode }}
                                                    @endif

                                                    @if ($destination->countryname)
                                                        @if ($destination->regionname || $destination->statecode)
                                                            ·
                                                        @endif

                                                        {{ $destination->countryname }}
                                                    @endif
                                                </div>
                                            </td>

                                            <td class="px-4 py-3 align-top">
                                                @if ($destination->hasvisited)
                                                    <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">
                                                        Visited
                                                    </span>

                                                    @if ($destination->visitedat)
                                                        <div class="mt-1 text-xs text-gray-500">
                                                            {{ $destination->visitedat->format('d M Y') }}
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="inline-flex rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                                                        Not visited
                                                    </span>
                                                @endif
                                            </td>

                                            <td class="px-4 py-3 align-top">
                                                @if ($destination->visitinterestnotes)
                                                    <div class="max-w-md whitespace-pre-line text-sm text-gray-700">
                                                        {{ \Illuminate\Support\Str::limit($destination->visitinterestnotes, 180) }}
                                                    </div>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>

                                            <td class="px-4 py-3 align-top text-center">
                                                <a
                                                    href="{{ route('destinations.edit', [
                                                        'destination' => $destination->id,
                                                        'return_to' => url()->full(),
                                                    ]) }}"
                                                    class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 text-xs"
                                                >
                                                    Destination
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">
                                            No Destinations match the selected travel-interest filters.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    @if ($destinations->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200">
                            {{ $destinations->links() }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const countrySelect = document.getElementById('countryid');
            const stateSelect = document.getElementById('stateid');
            const regionSelect = document.getElementById('regionid');
            const placeSelect = document.getElementById('placeid');

            if (!countrySelect || !stateSelect || !regionSelect || !placeSelect) {
                return;
            }

            const stateOptions = Array.from(
                stateSelect.querySelectorAll('option[data-country-id]')
            );

            const regionOptions = Array.from(
                regionSelect.querySelectorAll('option[data-state-id]')
            );

            const placeOptions = Array.from(
                placeSelect.querySelectorAll('option[data-country-id]')
            );

            function setOptionVisibility(option, visible) {
                option.hidden = !visible;
                option.disabled = !visible;
            }

            function filterStates() {
                const countryId = countrySelect.value || '';

                stateOptions.forEach(function (option) {
                    const visible = !countryId
                        || option.dataset.countryId === countryId;

                    setOptionVisibility(option, visible);
                });

                const selectedOption = stateSelect.options[
                    stateSelect.selectedIndex
                ];

                if (
                    selectedOption
                    && selectedOption.value
                    && selectedOption.hidden
                ) {
                    stateSelect.value = '';
                }
            }

            function filterRegions() {
                const countryId = countrySelect.value || '';
                const stateId = stateSelect.value || '';

                regionOptions.forEach(function (option) {
                    const matchesCountry = !countryId
                        || option.dataset.countryId === countryId;

                    const matchesState = !stateId
                        || option.dataset.stateId === stateId;

                    setOptionVisibility(
                        option,
                        matchesCountry && matchesState
                    );
                });

                const selectedOption = regionSelect.options[
                    regionSelect.selectedIndex
                ];

                if (
                    selectedOption
                    && selectedOption.value
                    && selectedOption.hidden
                ) {
                    regionSelect.value = '';
                }
            }

            function filterPlaces() {
                const countryId = countrySelect.value || '';
                const stateId = stateSelect.value || '';
                const regionId = regionSelect.value || '';

                placeOptions.forEach(function (option) {
                    const matchesCountry = !countryId
                        || option.dataset.countryId === countryId;

                    const matchesState = !stateId
                        || option.dataset.stateId === stateId;

                    const matchesRegion = !regionId
                        || option.dataset.regionId === regionId;

                    setOptionVisibility(
                        option,
                        matchesCountry
                        && matchesState
                        && matchesRegion
                    );
                });

                const selectedOption = placeSelect.options[
                    placeSelect.selectedIndex
                ];

                if (
                    selectedOption
                    && selectedOption.value
                    && selectedOption.hidden
                ) {
                    placeSelect.value = '';
                }
            }

            function refreshLocationFilters() {
                filterStates();
                filterRegions();
                filterPlaces();
            }

            countrySelect.addEventListener('change', refreshLocationFilters);

            stateSelect.addEventListener('change', function () {
                filterRegions();
                filterPlaces();
            });

            regionSelect.addEventListener('change', filterPlaces);

            refreshLocationFilters();
        });
    </script>
</x-app-layout>