@php
    $selectedFuelTypes = old('fueltypesavailable', $fuelStop->fuel_types_array ?? []);
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <div>
            <label for="placeid" class="block text-sm font-medium text-gray-700 mb-1">Place</label>
            <select
                name="placeid"
                id="placeid"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                required
            >
                <option value="">Select place</option>
                @foreach ($places as $place)
                    <option value="{{ $place->id }}" @selected(old('placeid', $fuelStop->placeid) == $place->id)>
                        {{ $place->placename }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="destinationid" class="block text-sm font-medium text-gray-700 mb-1">Destination</label>
            <select
                name="destinationid"
                id="destinationid"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                data-selected="{{ old('destinationid', $fuelStop->destinationid) }}"
            >
                <option value="">Select destination</option>
                @foreach ($destinations as $destination)
                    <option
                        value="{{ $destination->id }}"
                        data-place-id="{{ $destination->placeid }}"
                        @selected(old('destinationid', $fuelStop->destinationid) == $destination->id)
                    >
                        {{ $destination->destinationname }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="stopname" class="block text-sm font-medium text-gray-700 mb-1">Stop Name</label>
            <input
                type="text"
                name="stopname"
                id="stopname"
                value="{{ old('stopname', $fuelStop->stopname) }}"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                maxlength="200"
                required
            >
        </div>

        <div>
            <label for="brandname" class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
            <input
                type="text"
                name="brandname"
                id="brandname"
                value="{{ old('brandname', $fuelStop->brandname) }}"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                maxlength="100"
            >
        </div>
    </div>

    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-6">
        <div>
            <h3 class="text-sm font-semibold text-gray-900">Location and contact</h3>
            <p class="mt-1 text-xs text-gray-500">
                Use these fields for the precise fuel stop location and contact details.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label for="addressline1" class="block text-sm font-medium text-gray-700 mb-1">Address line 1</label>
                <input
                    type="text"
                    name="addressline1"
                    id="addressline1"
                    value="{{ old('addressline1', $fuelStop->addressline1) }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>

            <div class="md:col-span-2">
                <label for="addressline2" class="block text-sm font-medium text-gray-700 mb-1">Address line 2</label>
                <input
                    type="text"
                    name="addressline2"
                    id="addressline2"
                    value="{{ old('addressline2', $fuelStop->addressline2) }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>

            <div class="md:col-span-2">
                <label for="addressline3" class="block text-sm font-medium text-gray-700 mb-1">Address line 3</label>
                <input
                    type="text"
                    name="addressline3"
                    id="addressline3"
                    value="{{ old('addressline3', $fuelStop->addressline3) }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>

            <div>
                <label for="postcode" class="block text-sm font-medium text-gray-700 mb-1">Postcode</label>
                <input
                    type="text"
                    name="postcode"
                    id="postcode"
                    value="{{ old('postcode', $fuelStop->postcode) }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>

            <div>
                <label for="telephone" class="block text-sm font-medium text-gray-700 mb-1">Telephone</label>
                <input
                    type="text"
                    name="telephone"
                    id="telephone"
                    value="{{ old('telephone', $fuelStop->telephone) }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>

            <div class="md:col-span-2">
                <label for="website" class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                <input
                    type="url"
                    name="website"
                    id="website"
                    value="{{ old('website', $fuelStop->website) }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-4 space-y-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Location picker</h3>
                <p class="mt-1 text-xs text-gray-500">
                    Search for a location, click the map, or drag the marker to update latitude and longitude.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-[1fr_auto_auto] gap-3">
                <div>
                    <label for="fuel-map-search" class="block text-sm font-medium text-gray-700">
                        Search place or address
                    </label>
                    <input
                        type="text"
                        id="fuel-map-search"
                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm"
                        placeholder="Search by address, town, or place name"
                    >
                </div>

                <div class="flex items-end">
                    <button
                        type="button"
                        id="fuel-map-search-button"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        Search Map
                    </button>
                </div>

                <div class="flex items-end">
                    <button
                        type="button"
                        id="fuel-use-my-location"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        Use My Location
                    </button>
                </div>
            </div>

            <div id="fuel-stop-map" class="h-96 w-full rounded-lg border border-gray-300 overflow-hidden"></div>

            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    id="fuel-sync-from-fields"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50"
                >
                    Move Marker to Coordinates
                </button>

                <a
                    href="#"
                    id="fuel-open-in-google-maps"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50"
                >
                    Open in Google Maps
                </a>
            </div>

            <p id="fuel-map-status" class="text-xs text-gray-500"></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="latitude" class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
                <input
                    type="number"
                    step="0.0000001"
                    name="latitude"
                    id="latitude"
                    value="{{ old('latitude', $fuelStop->latitude) }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>

            <div>
                <label for="longitude" class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
                <input
                    type="number"
                    step="0.0000001"
                    name="longitude"
                    id="longitude"
                    value="{{ old('longitude', $fuelStop->longitude) }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>

            <div class="md:col-span-2">
                <label for="internetsearch" class="block text-sm font-medium text-gray-700 mb-1">Internet search terms</label>
                <input
                    type="text"
                    name="internetsearch"
                    id="internetsearch"
                    value="{{ old('internetsearch', $fuelStop->internetsearch) }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>
        </div>
    </div>

    <div>
        <span class="block text-sm font-medium text-gray-700 mb-2">Fuel Types Available</span>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
            @foreach ($fuelTypes as $value => $label)
                <label class="inline-flex items-center gap-2">
                    <input
                        type="checkbox"
                        name="fueltypesavailable[]"
                        value="{{ $value }}"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm"
                        @checked(in_array($value, $selectedFuelTypes))
                    >
                    <span class="text-sm text-gray-700">{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <label class="inline-flex items-center gap-2">
            <input
                type="checkbox"
                name="hashighflowdiesel"
                value="1"
                class="rounded border-gray-300 text-indigo-600 shadow-sm"
                @checked(old('hashighflowdiesel', $fuelStop->hashighflowdiesel))
            >
            <span class="text-sm text-gray-700">High-flow diesel</span>
        </label>

        <label class="inline-flex items-center gap-2">
            <input
                type="checkbox"
                name="hasadblue"
                value="1"
                class="rounded border-gray-300 text-indigo-600 shadow-sm"
                @checked(old('hasadblue', $fuelStop->hasadblue))
            >
            <span class="text-sm text-gray-700">AdBlue</span>
        </label>

        <label class="inline-flex items-center gap-2">
            <input
                type="checkbox"
                name="hascarwash"
                value="1"
                class="rounded border-gray-300 text-indigo-600 shadow-sm"
                @checked(old('hascarwash', $fuelStop->hascarwash))
            >
            <span class="text-sm text-gray-700">Car wash</span>
        </label>

        <label class="inline-flex items-center gap-2">
            <input
                type="checkbox"
                name="hasairwater"
                value="1"
                class="rounded border-gray-300 text-indigo-600 shadow-sm"
                @checked(old('hasairwater', $fuelStop->hasairwater))
            >
            <span class="text-sm text-gray-700">Air / water</span>
        </label>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <div>
            <label for="caravanaccessnotes" class="block text-sm font-medium text-gray-700 mb-1">Caravan Access Notes</label>
            <textarea
                name="caravanaccessnotes"
                id="caravanaccessnotes"
                rows="4"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >{{ old('caravanaccessnotes', $fuelStop->caravanaccessnotes) }}</textarea>
        </div>

        <div>
            <label for="openingnotes" class="block text-sm font-medium text-gray-700 mb-1">Opening Notes</label>
            <textarea
                name="openingnotes"
                id="openingnotes"
                rows="4"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >{{ old('openingnotes', $fuelStop->openingnotes) }}</textarea>
        </div>

        <div>
            <label for="generalnotes" class="block text-sm font-medium text-gray-700 mb-1">General Notes</label>
            <textarea
                name="generalnotes"
                id="generalnotes"
                rows="4"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >{{ old('generalnotes', $fuelStop->generalnotes) }}</textarea>
        </div>
    </div>

    <div class="pt-2">
        <label class="inline-flex items-center gap-2">
            <input
                type="checkbox"
                name="isactive"
                value="1"
                class="rounded border-gray-300 text-indigo-600 shadow-sm"
                @checked(old('isactive', $fuelStop->isactive ?? true))
            >
            <span class="text-sm text-gray-700">Active</span>
        </label>
    </div>
</div>