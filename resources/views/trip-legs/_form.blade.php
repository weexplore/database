@php
    $selectedDestinationId = old('destinationid', old('destination_id', $tripLeg->destinationid ?? $selectedDestinationId ?? ''));
    $selectedFromPlaceId = old('fromplaceid', old('fromplace_id', $tripLeg->fromplaceid ?? $selectedFromPlaceId ?? ''));
    $selectedToPlaceId = old('toplaceid', old('toplace_id', $tripLeg->toplaceid ?? $selectedToPlaceId ?? ''));
    $isCreate = $isCreate ?? false;
@endphp

<div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-medium text-gray-900">
                {{ $isCreate ? 'Add Trip Leg' : 'Leg Details' }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Define the route segment, places, dates, and planning notes for this trip leg.
            </p>
        </div>

        @if($isCreate)
            <a href="{{ route('trips.legs.index', $trip) }}"
               class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                Close
            </a>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <div>
            <label for="legnumber" class="block text-sm font-medium text-gray-700 mb-1">Leg Number</label>
            <input type="number"
                   name="legnumber"
                   id="legnumber"
                   value="{{ old('legnumber', $tripLeg->legnumber ?? '') }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                   min="1">
        </div>

        <div>
            <label for="sortorder" class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
            <input type="number"
                   name="sortorder"
                   id="sortorder"
                   value="{{ old('sortorder', $tripLeg->sortorder ?? '') }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                   min="0">
        </div>

        <div>
            <label for="nightsplanned" class="block text-sm font-medium text-gray-700 mb-1">Nights Planned</label>
            <input type="number"
                   name="nightsplanned"
                   id="nightsplanned"
                   value="{{ old('nightsplanned', $tripLeg->nightsplanned ?? '') }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                   min="0">
        </div>

        <div>
            <label for="startdate" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
            <input type="date"
                   name="startdate"
                   id="startdate"
                   value="{{ old('startdate', isset($tripLeg?->startdate) ? \Illuminate\Support\Carbon::parse($tripLeg->startdate)->format('Y-m-d') : '') }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="enddate" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
            <input type="date"
                   name="enddate"
                   id="enddate"
                   value="{{ old('enddate', isset($tripLeg?->enddate) ? \Illuminate\Support\Carbon::parse($tripLeg->enddate)->format('Y-m-d') : '') }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="destinationid" class="block text-sm font-medium text-gray-700 mb-1">Destination</label>
            <select name="destinationid" id="destinationid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">Select destination</option>
                @foreach($destinations as $destination)
                    <option value="{{ $destination->id }}" @selected((string) $selectedDestinationId === (string) $destination->id)>
                        {{ $destination->destinationname }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="fromplaceid" class="block text-sm font-medium text-gray-700 mb-1">From Place</label>
            <select name="fromplaceid" id="fromplaceid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">Select origin place</option>
                @foreach($places as $place)
                    <option value="{{ $place->id }}" @selected((string) $selectedFromPlaceId === (string) $place->id)>
                        {{ $place->placename }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="toplaceid" class="block text-sm font-medium text-gray-700 mb-1">To Place</label>
            <select name="toplaceid" id="toplaceid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">Select destination place</option>
                @foreach($places as $place)
                    <option value="{{ $place->id }}" @selected((string) $selectedToPlaceId === (string) $place->id)>
                        {{ $place->placename }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="xl:col-span-3">
            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
            <input type="text"
                   name="title"
                   id="title"
                   value="{{ old('title', $tripLeg->title ?? '') }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div class="xl:col-span-3">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description"
                      id="description"
                      rows="4"
                      class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('description', $tripLeg->description ?? '') }}</textarea>
        </div>
    </div>
</div>
<div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
    <div>
        <h3 class="text-lg font-medium text-gray-900">Route Map</h3>
        <p class="mt-1 text-sm text-gray-500">
            Preview the trip leg between the selected From Place and To Place.
        </p>
    </div>

    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-4">
        <div id="trip-leg-map" class="h-96 w-full rounded-lg border border-gray-300 overflow-hidden"></div>

        <div class="flex flex-wrap gap-2">
            <a href="#"
               id="trip-leg-open-in-google-maps"
               target="_blank"
               rel="noopener noreferrer"
               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Open in Google Maps
            </a>
        </div>

        <div id="trip-leg-map-summary" class="text-sm text-gray-600">
            Select both a From Place and To Place with coordinates to display the route preview.
        </div>
    </div>
</div>

<div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
    <div>
        <h3 class="text-lg font-medium text-gray-900">Distance and Elevation</h3>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <label for="distancekm" class="block text-sm font-medium text-gray-700 mb-1">Distance (km)</label>
            <input type="number"
                   step="0.1"
                   min="0"
                   name="distancekm"
                   id="distancekm"
                   value="{{ old('distancekm', $tripLeg->distancekm ?? '') }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="elevationgainm" class="block text-sm font-medium text-gray-700 mb-1">Elevation Gain (m)</label>
            <input type="number"
                   step="0.1"
                   name="elevationgainm"
                   id="elevationgainm"
                   value="{{ old('elevationgainm', $tripLeg->elevationgainm ?? '') }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="elevationlossm" class="block text-sm font-medium text-gray-700 mb-1">Elevation Loss (m)</label>
            <input type="number"
                   step="0.1"
                   name="elevationlossm"
                   id="elevationlossm"
                   value="{{ old('elevationlossm', $tripLeg->elevationlossm ?? '') }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
    </div>
</div>

<div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
    <div>
        <h3 class="text-lg font-medium text-gray-900">Notes</h3>
    </div>

    <div class="space-y-4">
        <div>
            <label for="drivingnotes" class="block text-sm font-medium text-gray-700 mb-1">Driving Notes</label>
            <textarea name="drivingnotes"
                      id="drivingnotes"
                      rows="4"
                      class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('drivingnotes', $tripLeg->drivingnotes ?? '') }}</textarea>
        </div>

        <div>
            <label for="planningnotes" class="block text-sm font-medium text-gray-700 mb-1">Planning Notes</label>
            <textarea name="planningnotes"
                      id="planningnotes"
                      rows="5"
                      class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('planningnotes', $tripLeg->planningnotes ?? '') }}</textarea>
        </div>

        <div>
            <label for="actualnotes" class="block text-sm font-medium text-gray-700 mb-1">Actual Notes</label>
            <textarea name="actualnotes"
                      id="actualnotes"
                      rows="5"
                      class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('actualnotes', $tripLeg->actualnotes ?? '') }}</textarea>
        </div>
    </div>
</div>
@php
    $selectedVehicles = old('vehicles');

    if ($selectedVehicles === null && isset($tripLeg) && $tripLeg?->exists) {
        $selectedVehicles = $tripLeg->vehicles
            ->sortBy(fn ($vehicle) => $vehicle->pivot->sortorder ?? 9999)
            ->map(function ($vehicle) {
                return [
                    'vehicleid' => $vehicle->id,
                    'vehiclerole' => $vehicle->pivot->vehiclerole,
                    'sortorder' => $vehicle->pivot->sortorder,
                ];
            })
            ->values()
            ->all();
    }

    $selectedVehicles = is_array($selectedVehicles) ? array_values($selectedVehicles) : [];
@endphp

<div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-medium text-gray-900">Vehicles</h3>
            <p class="mt-1 text-sm text-gray-500">
                Link the tow vehicle, caravan, trailer, or other vehicles used on this leg.
            </p>
        </div>
    </div>

    <div class="space-y-4">
        <div id="vehicle-rows" class="space-y-3">
            @forelse($selectedVehicles as $index => $selectedVehicle)
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 vehicle-row">
                    <div class="md:col-span-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle</label>
                        <select name="vehicles[{{ $index }}][vehicleid]" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">Select vehicle</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" @selected((string) ($selectedVehicle['vehicleid'] ?? '') === (string) $vehicle->id)>
                                    {{ $vehicle->vehiclename }}{{ $vehicle->registrationnumber ? ' (' . $vehicle->registrationnumber . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <input type="text"
                               name="vehicles[{{ $index }}][vehiclerole]"
                               value="{{ $selectedVehicle['vehiclerole'] ?? '' }}"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                               placeholder="Tow vehicle, caravan, support vehicle">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sort</label>
                        <input type="number"
                               name="vehicles[{{ $index }}][sortorder]"
                               value="{{ $selectedVehicle['sortorder'] ?? $index + 1 }}"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                               min="0">
                    </div>

                    <div class="md:col-span-1 flex items-end">
                        <button type="button"
                                class="remove-vehicle-row inline-flex items-center px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-xs w-full justify-center">
                            Remove
                        </button>
                    </div>
                </div>
            @empty
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 vehicle-row">
                    <div class="md:col-span-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle</label>
                        <select name="vehicles[0][vehicleid]" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">Select vehicle</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}">
                                    {{ $vehicle->vehiclename }}{{ $vehicle->registrationnumber ? ' (' . $vehicle->registrationnumber . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <input type="text"
                               name="vehicles[0][vehiclerole]"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                               placeholder="Tow vehicle, caravan, support vehicle">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sort</label>
                        <input type="number"
                               name="vehicles[0][sortorder]"
                               value="1"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                               min="0">
                    </div>

                    <div class="md:col-span-1 flex items-end">
                        <button type="button"
                                class="remove-vehicle-row inline-flex items-center px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-xs w-full justify-center">
                            Remove
                        </button>
                    </div>
                </div>
            @endforelse
        </div>

        <div>
            <button type="button"
                    id="add-vehicle-row"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                Add Vehicle
            </button>
        </div>
    </div>
</div>

<div class="flex items-center justify-end gap-3">
    <a href="{{ route('trips.legs.index', $trip) }}"
       class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
        Cancel
    </a>

    <button type="submit"
            class="inline-flex items-center px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700">
        Save Trip Leg
    </button>
</div>