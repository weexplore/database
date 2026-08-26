{{-- resources/views/places/edit.blade.php --}}
<x-app-layout>
    @php
        $returnTo = request('return_to', route('places.index', request()->only([
            'search',
            'country_id',
            'state_id',
            'region_id',
            'placetype',
            'status',
            'page',
        ])));
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Place
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $place->placename }}
                </p>
            </div>
            <div>
                <a href="{{ route('reports.places.reference-book.place', [
                        'place' => $place,
                        'return_to' => url()->full(),
                    ]) }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                    Reference Book Report
                </a>
                <a href="{{ route('places.nearby', [
                        'place' => $place->id,
                        'radius_km' => 50,
                        'returnto' => request()->fullUrl(),
                    ]) }}"
                   class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm">
                    Nearby 50 km
                </a>

                <a href="{{ $returnTo }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                    Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <div class="xl:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <form
                            id="place-edit-form"
                            method="POST"
                            action="{{ route('places.update', $place) }}"
                            class="p-6 space-y-6"
                        >
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="return_to" value="{{ $returnTo }}">
                            <input type="hidden" name="create_destination_after_save" id="create_destination_after_save" value="0">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div>
                                        <label for="placename" class="block text-sm font-medium text-gray-700">
                                            Place name
                                        </label>
                                        <input
                                            type="text"
                                            id="placename"
                                            name="placename"
                                            value="{{ old('placename', $place->placename) }}"
                                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm"
                                            required
                                        >
                                    </div>

                                    <div>
                                        <label for="placetype" class="block text-sm font-medium text-gray-700">
                                            Type
                                        </label>
                                        <select
                                            id="placetype"
                                            name="placetype"
                                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm"
                                        >
                                            <option value="">None</option>
                                            @foreach ($placeTypes as $value => $label)
                                                <option
                                                    value="{{ $value }}"
                                                    @selected((string) old('placetype', $place->placetype) === (string) $value)
                                                >
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label for="sourcequality" class="block text-sm font-medium text-gray-700">
                                            Source quality
                                        </label>
                                        <input
                                            type="text"
                                            id="sourcequality"
                                            name="sourcequality"
                                            value="{{ old('sourcequality', $place->sourcequality) }}"
                                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm"
                                            placeholder="user_entered, imported, approximate, verified"
                                        >
                                    </div>

                                    <div class="flex items-center gap-2 pt-2">
                                        <input type="hidden" name="isactive" value="0">
                                        <input
                                            type="checkbox"
                                            id="isactive"
                                            name="isactive"
                                            value="1"
                                            class="rounded border-gray-300 text-blue-600 shadow-sm"
                                            @checked(old('isactive', $place->isactive))
                                        >
                                        <label for="isactive" class="text-sm text-gray-700">
                                            Active
                                        </label>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label for="countryid" class="block text-sm font-medium text-gray-700">
                                            Country
                                        </label>
                                        <select
                                            id="countryid"
                                            name="countryid"
                                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm"
                                            required
                                        >
                                            <option value="">Select</option>
                                            @foreach ($countries as $country)
                                                <option
                                                    value="{{ (int) $country->id }}"
                                                    @selected((string) old('countryid', $place->countryid) === (string) $country->id)
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
                                            id="stateid"
                                            name="stateid"
                                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm"
                                        >
                                            <option value="">None</option>
                                            @foreach ($states as $state)
                                                <option
                                                    value="{{ (int) $state->id }}"
                                                    @selected((string) old('stateid', $place->stateid) === (string) $state->id)
                                                >
                                                    {{ $state->statename }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label for="regionid" class="block text-sm font-medium text-gray-700">
                                            Region
                                        </label>
                                        <select
                                            id="regionid"
                                            name="regionid"
                                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm"
                                        >
                                            <option value="">None</option>
                                            @foreach ($regions as $region)
                                                <option
                                                    value="{{ (int) $region->id }}"
                                                    @selected((string) old('regionid', $place->regionid) === (string) $region->id)
                                                >
                                                    {{ $region->regionname }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label for="locality" class="block text-sm font-medium text-gray-700">
                                            Locality / town
                                        </label>
                                        <input
                                            type="text"
                                            id="locality"
                                            name="locality"
                                            value="{{ old('locality', $place->locality) }}"
                                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm"
                                        >
                                    </div>

                                    <div>
                                        <label for="postcode" class="block text-sm font-medium text-gray-700">
                                            Postcode
                                        </label>
                                        <input
                                            type="text"
                                            id="postcode"
                                            name="postcode"
                                            value="{{ old('postcode', $place->postcode) }}"
                                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm"
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="addressline1" class="block text-sm font-medium text-gray-700">
                                        Address line 1
                                    </label>
                                    <input
                                        type="text"
                                        id="addressline1"
                                        name="addressline1"
                                        value="{{ old('addressline1', $place->addressline1) }}"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm"
                                    >
                                </div>

                                <div>
                                    <label for="addressline2" class="block text-sm font-medium text-gray-700">
                                        Address line 2
                                    </label>
                                    <input
                                        type="text"
                                        id="addressline2"
                                        name="addressline2"
                                        value="{{ old('addressline2', $place->addressline2) }}"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm"
                                    >
                                </div>
                            </div>

                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-4">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900">Location picker</h3>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Search for a location, click the map, or drag the marker to update latitude and longitude.
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-[1fr_auto_auto] gap-3">
                                    <div>
                                        <label for="map-search" class="block text-sm font-medium text-gray-700">Search place or address</label>
                                        <input
                                            type="text"
                                            id="map-search"
                                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm"
                                            placeholder="Search by address, town, or place name"
                                        >
                                    </div>

                                    <div class="flex items-end">
                                        <button
                                            type="button"
                                            id="map-search-button"
                                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50"
                                        >
                                            Search Map
                                        </button>
                                    </div>

                                    <div class="flex items-end">
                                        <button
                                            type="button"
                                            id="use-my-location"
                                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50"
                                        >
                                            Use My Location
                                        </button>
                                    </div>
                                </div>

                                <div id="place-map" class="h-96 w-full rounded-lg border border-gray-300 overflow-hidden"></div>

                                <div class="flex flex-wrap gap-2">
                                    <button
                                        type="button"
                                        id="sync-from-fields"
                                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50"
                                    >
                                        Move Marker to Coordinates
                                    </button>

                                    <a
                                        href="#"
                                        id="open-in-google-maps"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50"
                                    >
                                        Open in Google Maps
                                    </a>
                                </div>

                                <p id="map-status" class="text-xs text-gray-500"></p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="latitude" class="block text-sm font-medium text-gray-700">
                                        Latitude
                                    </label>
                                    <input
                                        type="number"
                                        step="0.0000001"
                                        id="latitude"
                                        name="latitude"
                                        value="{{ old('latitude', $place->latitude) }}"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm"
                                    >
                                </div>

                                <div>
                                    <label for="longitude" class="block text-sm font-medium text-gray-700">
                                        Longitude
                                    </label>
                                    <input
                                        type="number"
                                        step="0.0000001"
                                        id="longitude"
                                        name="longitude"
                                        value="{{ old('longitude', $place->longitude) }}"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm"
                                    >
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-6">
                                <x-forms.markdown-display-editor
                                    name="accessnotes"
                                    id="accessnotes"
                                    :value="old('accessnotes', $place->accessnotes)"
                                    label="Access notes"
                                    :rows="5"
                                    placeholder="Access details, approach notes, vehicle restrictions, gate codes, arrival tips, or check-in instructions"
                                    help="Markdown supported, including headings, lists, links, emphasis, and tables."
                                    preview-title="Access Notes Preview"
                                />

                                <x-forms.markdown-display-editor
                                    name="generalnotes"
                                    id="generalnotes"
                                    :value="old('generalnotes', $place->generalnotes)"
                                    label="General notes"
                                    :rows="5"
                                    placeholder="General notes about the place, suitability, facilities, nearby context, or planning remarks"
                                    help="Markdown supported, including headings, lists, links, emphasis, and tables."
                                    preview-title="General Notes Preview"
                                />

<div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
    <a href="{{ $returnTo }}"
       class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
        Cancel
    </a>

    <button type="submit"
            class="inline-flex items-center px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700">
        Save Place
    </button>

    <button type="submit"
            name="createdestinationaftersave"
            value="1"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
        Save and Add Destination
    </button>
</div>
                        </form>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-red-200">
                        <div class="px-4 py-3 border-b border-red-200 bg-red-50">
                            <h3 class="text-sm font-semibold text-red-800">Delete Place</h3>
                            <p class="mt-1 text-xs text-red-700">
                                This permanently removes this place record.
                            </p>
                        </div>

                        <div class="p-4">
                            <form method="POST"
                                  action="{{ route('places.destroy', $place) }}"
                                  onsubmit="return confirm('Delete this place? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="returnto" value="{{ $returnTo }}">

                                <div class="flex items-center justify-end">
                                    <button type="submit"
                                            class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md text-xs font-semibold text-red-700 bg-white uppercase tracking-widest hover:bg-red-50">
                                        Delete Place
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-1 space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">Destinations and Items</h3>
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $place->destinations->count() }} destination{{ $place->destinations->count() === 1 ? '' : 's' }}
                                    · {{ $destinationItems->count() }} destination item{{ $destinationItems->count() === 1 ? '' : 's' }}
                                </p>
                            </div>
                            

                            <div class="flex items-center gap-2">
                                <button type="submit"
                                        form="place-edit-form"
                                        formnovalidate
                                        name="createdestinationaftersave"
                                        value="1"
                                        class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded text-xs hover:bg-blue-700">
                                    Add destination
                                </button>
                            </div>
                        </div>

                        <div class="p-4">
                            @if($place->destinations->isNotEmpty())
                                <div class="space-y-4">
                                    @foreach($place->destinations as $destination)
                                        <div class="border border-gray-200 rounded-lg">
                                            <a href="{{ route('destinations.edit', [
                                                    'destination' => $destination,
                                                    'return_to' => url()->full(),
                                                ]) }}"
                                            class="block px-3 py-2 border-b border-gray-200 bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $destination->destinationname }}
                                                </div>
                                                <div class="mt-1 text-xs text-gray-500">
                                                    Type: {{ $destination->destinationtype ?: '—' }}
                                                </div>
                                            </a>

                                            <div class="p-3">
                                                @if($destination->items->isNotEmpty())
                                                    <ul class="space-y-2">
                                                        @foreach($destination->items as $item)
                                                            <li>
                                                                <a href="{{ route('destination-items.edit', ['destinationItem' => $item, 'return_to' => url()->full()]) }}"
                                                                class="block border border-gray-200 rounded-md px-3 py-2 hover:bg-gray-50 hover:border-gray-300">
                                                                    <div class="text-sm font-medium text-gray-900">{{ $item->itemname }}</div>
                                                                    <div class="mt-1 text-xs text-gray-500">
                                                                        Type: {{ $item->itemTypes->pluck('typename')->join(', ') ?: '—' }}
                                                                    </div>
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <p class="text-xs text-gray-500">No destination items linked to this destination.</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500">No destinations are currently linked to this place.</p>
                            @endif
                        </div>
                    </div>

                    

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900">
                                Linked Fuel Stops
                            </h3>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ $place->fuelStops->count() }} linked record{{ $place->fuelStops->count() === 1 ? '' : 's' }}
                            </p>
                        </div>

                        <div class="p-4">
                            @if ($place->fuelStops->isNotEmpty())
                                <ul class="space-y-3">
                                    @foreach ($place->fuelStops as $fuelStop)
                                        <li>
                                            <a
                                                href="{{ route('fuel-stops.edit', [
                                                    'fuel_stop' => $fuelStop,
                                                    'return_to' => url()->full(),
                                                ]) }}"
                                                class="block border border-gray-200 rounded-md px-3 py-2 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            >
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $fuelStop->stopname }}
                                                </div>
                                                <div class="mt-1 text-xs text-gray-500">
                                                    Brand: {{ $fuelStop->brandname ?: '—' }}
                                                </div>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-gray-500">
                                    No fuel stops are currently linked to this place.
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900">
                                Trip Stays
                            </h3>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ $place->tripStays->count() }} linked record{{ $place->tripStays->count() === 1 ? '' : 's' }}
                            </p>
                        </div>

                        <div class="p-4">
                            @if ($place->tripStays->isNotEmpty())
                                <ul class="space-y-3">
                                    @foreach ($place->tripStays as $tripStay)
                                        <li>
                                            <a
                                                href="{{ route('trips.stays.edit', [
                                                    'trip' => $tripStay->trip,
                                                    'tripStay' => $tripStay,
                                                    'return_to' => url()->full(),
                                                ]) }}"
                                                class="block border border-gray-200 rounded-md px-3 py-2 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            >
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $tripStay->stayname ?: 'Trip stay' }}
                                                </div>

                                                <div class="mt-1 text-xs text-gray-500">
                                                    Trip: {{ $tripStay->trip?->tripname ?: '—' }}
                                                </div>

                                                <div class="mt-1 text-xs text-gray-500">
                                                    @php
                                                        $checkIn = $tripStay->checkindate
                                                            ? \Illuminate\Support\Carbon::parse($tripStay->checkindate)->format('d M Y')
                                                            : null;

                                                        $checkOut = $tripStay->checkoutdate
                                                            ? \Illuminate\Support\Carbon::parse($tripStay->checkoutdate)->format('d M Y')
                                                            : null;
                                                    @endphp
                                                    Stay: {{ $checkIn ?: '—' }}{{ $checkOut ? ' to ' . $checkOut : '' }}
                                                </div>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-gray-500">
                                    No trip stays are currently linked to this place.
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">
                                    Trip Legs to this Place
                                </h3>
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $place->tripLegsTo->count() }} linked record{{ $place->tripLegsTo->count() === 1 ? '' : 's' }}
                                </p>
                            </div>
                        </div>

                        <div class="p-4">
                            @if ($place->tripLegsTo->isNotEmpty())
                                <ul class="space-y-3">
                                    @foreach ($place->tripLegsTo as $tripLeg)
                                        <li>
                                            <a href="{{ route('trips.legs.edit', [
                                                    'trip' => $tripLeg->trip,
                                                    'tripLeg' => $tripLeg,
                                                    'return_to' => url()->full(),
                                                ]) }}"
                                               class="block border border-gray-200 rounded-md px-3 py-2 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $tripLeg->legname ?? 'Trip leg' }}
                                                </div>

                                                <div class="mt-1 text-xs text-gray-500">
                                                    Trip: {{ optional($tripLeg->trip)->tripname ?? 'Unnamed trip' }}
                                                </div>

                                                @php
                                                    $depart = $tripLeg->startdate ? \Carbon\Carbon::parse($tripLeg->startdate)->format('d M Y') : null;
                                                    $arrive = $tripLeg->enddate ? \Carbon\Carbon::parse($tripLeg->enddate)->format('d M Y') : null;
                                                @endphp
                                                <div class="mt-1 text-xs text-gray-500">
                                                    @if ($depart || $arrive)
                                                        {{ $depart ?? '?' }} → {{ $arrive ?? '?' }}
                                                    @else
                                                        Dates not set
                                                    @endif
                                                </div>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-gray-500">
                                    No trip legs currently end at this place.
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">Linked Knowledge Items</h3>
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $place->knowledgeItems->count() }} linked record{{ $place->knowledgeItems->count() === 1 ? '' : 's' }}
                                </p>
                            </div>
                        </div>

                        <div class="p-4">
                            @if($place->knowledgeItems->isNotEmpty())
                                <ul class="space-y-3">
                                    @foreach($place->knowledgeItems as $knowledgeItem)
                                        <li>
                                            <a href="{{ route('knowledge.items.edit', [
                                                    'knowledgeItem' => $knowledgeItem,
                                                    'return_to' => url()->full(),
                                                ]) }}"
                                               class="block border border-gray-200 rounded-md px-3 py-2 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $knowledgeItem->itemname ?: 'Untitled knowledge item' }}
                                                </div>

                                                <div class="mt-1 text-xs text-gray-500">
                                                    Type: {{ $knowledgeItem->itemType?->typename ?? '—' }}
                                                </div>

                                                <div class="mt-1 text-xs text-gray-500">
                                                    Category: {{ $knowledgeItem->primaryCategory?->categoryname ?? '—' }}
                                                </div>

                                                <div class="mt-1 text-xs text-gray-500">
                                                    Status: {{ $knowledgeItem->itemstatus ?: '—' }}
                                                </div>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-gray-500">
                                    No knowledge items are currently linked to this place.
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-900">
                            Record summary
                        </h3>
                        <dl class="mt-3 space-y-2 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Place ID</dt>
                                <dd class="text-gray-900">{{ $place->id }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Created</dt>
                                <dd class="text-gray-900">{{ optional($place->createdat)->format('d M Y') ?: '—' }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Updated</dt>
                                <dd class="text-gray-900">{{ optional($place->updatedat)->format('d M Y') ?: '—' }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Status</dt>
                                <dd class="text-gray-900">{{ $place->isactive ? 'Active' : 'Inactive' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.markdown.markdown-styles')

    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""
    />
    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""
    ></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const countrySelect = document.getElementById('countryid');
        const stateSelect = document.getElementById('stateid');
        const regionSelect = document.getElementById('regionid');

        if (!countrySelect || !stateSelect || !regionSelect) {
            return;
        }

        function fillSelect(select, placeholder, items, selectedValue = '', formatter = null) {
            select.innerHTML = '';

            const first = document.createElement('option');
            first.value = '';
            first.textContent = placeholder;
            select.appendChild(first);

            items.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = formatter ? formatter(item) : item.name;

                if (String(selectedValue) === String(item.id)) {
                    option.selected = true;
                }

                select.appendChild(option);
            });
        }

        async function loadStates(selectedStateId = '') {
            const countryId = countrySelect.value;

            if (!countryId) {
                fillSelect(stateSelect, 'None', []);
                fillSelect(regionSelect, 'None', []);
                return;
            }

            fillSelect(stateSelect, 'Loading states...', []);
            fillSelect(regionSelect, 'Loading regions...', []);

            try {
                const response = await fetch(`{{ route('places.states-for-country') }}?countryid=${encodeURIComponent(countryId)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error(`States request failed with ${response.status}`);
                }

                const data = await response.json();

                fillSelect(
                    stateSelect,
                    'None',
                    data.states || [],
                    selectedStateId,
                    (state) => state.code ? `${state.name} (${state.code})` : state.name
                );
            } catch (error) {
                fillSelect(stateSelect, 'None', []);
                fillSelect(regionSelect, 'None', []);
            }
        }

        async function loadRegions(selectedRegionId = '') {
            const countryId = countrySelect.value;
            const stateId = stateSelect.value;

            if (!countryId) {
                fillSelect(regionSelect, 'None', []);
                return;
            }

            fillSelect(regionSelect, 'Loading regions...', []);

            try {
                const params = new URLSearchParams({
                    countryid: countryId,
                    stateid: stateId || '',
                });

                const response = await fetch(`{{ route('places.regions-for-country-state') }}?${params.toString()}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error(`Regions request failed with ${response.status}`);
                }

                const data = await response.json();

                fillSelect(regionSelect, 'None', data.regions || [], selectedRegionId);
            } catch (error) {
                fillSelect(regionSelect, 'None', []);
            }
        }

        countrySelect.addEventListener('change', async function () {
            await loadStates('');
            await loadRegions('');
        });

        stateSelect.addEventListener('change', async function () {
            await loadRegions('');
        });

        const initialStateId = @json((string) old('stateid', $place->stateid));
        const initialRegionId = @json((string) old('regionid', $place->regionid));

        if (countrySelect.value) {
            loadStates(initialStateId).then(() => {
                loadRegions(initialRegionId);
            });
        } else {
            fillSelect(stateSelect, 'None', []);
            fillSelect(regionSelect, 'None', []);
        }
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        const searchInput = document.getElementById('map-search');
        const placeNameInput = document.getElementById('placename');
        let searchTouchedManually = false;

        const searchButton = document.getElementById('map-search-button');
        const useMyLocationButton = document.getElementById('use-my-location');
        const syncFromFieldsButton = document.getElementById('sync-from-fields');
        const mapStatus = document.getElementById('map-status');
        const googleMapsLink = document.getElementById('open-in-google-maps');

        if (!latInput || !lngInput || !document.getElementById('place-map') || typeof L === 'undefined') return;

        function coordsAreBlank() {
            return latInput.value.trim() === '' && lngInput.value.trim() === '';
        }

        function shouldAutoFillSearch() {
            return placeNameInput && searchInput && coordsAreBlank();
        }

        function syncSearchFromPlaceName(force = false) {
            if (!placeNameInput || !searchInput) return;
            if (!shouldAutoFillSearch()) return;

            const placeName = placeNameInput.value.trim();
            const currentSearch = searchInput.value.trim();

            if (
                force ||
                !searchTouchedManually ||
                currentSearch === '' ||
                currentSearch === placeName
            ) {
                searchInput.value = placeName;
            }
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const placeName = placeNameInput ? placeNameInput.value.trim() : '';
                const currentSearch = searchInput.value.trim();

                searchTouchedManually = currentSearch !== '' && currentSearch !== placeName;
            });
        }

        if (placeNameInput) {
            placeNameInput.addEventListener('input', function () {
                syncSearchFromPlaceName();
            });
        }

        syncSearchFromPlaceName(true);

        const defaultLat = parseFloat(latInput.value) || -37.8136;
        const defaultLng = parseFloat(lngInput.value) || 144.9631;
        const defaultZoom = (latInput.value && lngInput.value) ? 15 : 6;

        const map = L.map('place-map').setView([defaultLat, defaultLng], defaultZoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const marker = L.marker([defaultLat, defaultLng], {
            draggable: true
        }).addTo(map);

        function setStatus(message) {
            if (mapStatus) {
                mapStatus.textContent = message || '';
            }
        }

        function updateGoogleMapsLink(lat, lng) {
            if (googleMapsLink) {
                googleMapsLink.href = `https://www.google.com/maps?q=${lat},${lng}`;
            }
        }

        function updateFields(lat, lng) {
            latInput.value = Number(lat).toFixed(7);
            lngInput.value = Number(lng).toFixed(7);
            updateGoogleMapsLink(latInput.value, lngInput.value);
        }

        function updateMarker(lat, lng, zoom = null) {
            marker.setLatLng([lat, lng]);
            map.panTo([lat, lng]);
            if (zoom) {
                map.setZoom(zoom);
            }
            updateFields(lat, lng);
        }

        marker.on('dragend', function () {
            const position = marker.getLatLng();
            updateFields(position.lat, position.lng);
            setStatus('Marker moved. Coordinates updated.');
        });

        map.on('click', function (event) {
            updateMarker(event.latlng.lat, event.latlng.lng);
            setStatus('Map clicked. Coordinates updated.');
        });

        latInput.addEventListener('change', function () {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);
            if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
                updateMarker(lat, lng);
                setStatus('Marker moved to typed coordinates.');
            }
        });

        lngInput.addEventListener('change', function () {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);
            if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
                updateMarker(lat, lng);
                setStatus('Marker moved to typed coordinates.');
            }
        });

        syncFromFieldsButton?.addEventListener('click', function () {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);

            if (Number.isNaN(lat) || Number.isNaN(lng)) {
                setStatus('Enter both latitude and longitude first.');
                return;
            }

            updateMarker(lat, lng, 15);
            setStatus('Marker moved to entered coordinates.');
        });

        searchButton?.addEventListener('click', async function () {
            const query = searchInput.value.trim();
            if (!query) {
                setStatus('Enter a place or address to search.');
                return;
            }

            setStatus('Searching map…');

            try {
                const url = `https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=${encodeURIComponent(query)}`;
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Search request failed.');
                }

                const results = await response.json();

                if (!results.length) {
                    setStatus('No matching location found.');
                    return;
                }

                const result = results[0];
                const lat = parseFloat(result.lat);
                const lng = parseFloat(result.lon);

                updateMarker(lat, lng, 15);
                setStatus(`Found: ${result.display_name}`);
            } catch (error) {
                setStatus('Unable to search location right now.');
            }
        });

        useMyLocationButton?.addEventListener('click', function () {
            if (!navigator.geolocation) {
                setStatus('Geolocation is not supported in this browser.');
                return;
            }

            setStatus('Finding your location…');

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    updateMarker(lat, lng, 15);
                    setStatus('Current location loaded.');
                },
                function () {
                    setStatus('Unable to retrieve your location.');
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000
                }
            );
        });

        updateGoogleMapsLink(defaultLat, defaultLng);

        setTimeout(function () {
            map.invalidateSize();
        }, 150);
    });
    </script>
</x-app-layout>