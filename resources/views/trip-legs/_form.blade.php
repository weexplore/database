@php
    $selectedFromDestinationId = old('fromdestinationid', old('fromdestination_id', $tripLeg->fromdestinationid ?? $selectedFromDestinationId ?? ''));
    $selectedFromPlaceId = old('fromplaceid', old('fromplace_id', $tripLeg->fromplaceid ?? $selectedFromPlaceId ?? ''));
    $selectedToDestinationId = old('todestinationid', old('todestination_id', $tripLeg->todestinationid ?? $selectedToDestinationId ?? ''));
    $selectedToPlaceId = old('toplaceid', old('toplace_id', $tripLeg->toplaceid ?? $selectedToPlaceId ?? ''));
    $selectedFromDestinationItemId = old('fromdestinationitemid', $tripLeg->fromdestinationitemid ?? '');
    $selectedToDestinationItemId = old('todestinationitemid', $tripLeg->todestinationitemid ?? '');
    $isCreate = $isCreate ?? false;

    $selectedLegPoints = old('leg_points');

    if ($selectedLegPoints === null && isset($tripLeg) && $tripLeg?->exists) {
        $selectedLegPoints = $tripLeg->legPoints
            ->sortBy('sequence_no')
            ->map(function ($point) {
                return [
                    'id' => $point->id,
                    'sequence_no' => $point->sequence_no,
                    'pointtype' => $point->pointtype,
                    'placeid' => $point->placeid,
                    'destinationid' => $point->destinationid,
                    'destinationitemid' => $point->destinationitemid,
                    'title' => $point->title,
                    'notes' => $point->notes,
                ];
            })
            ->values()
            ->all();
    }

    $selectedLegPoints = is_array($selectedLegPoints) ? array_values($selectedLegPoints) : [];

    $selectedVehicles = old('vehicles');

    if ($selectedVehicles === null && isset($tripLeg) && $tripLeg?->exists && $tripLeg->relationLoaded('vehicles') && $tripLeg->vehicles->isNotEmpty()) {
        $selectedVehicles = $tripLeg->vehicles
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

    if (($selectedVehicles === null || $selectedVehicles === []) && $trip->relationLoaded('tripVehicles') && $trip->tripVehicles->isNotEmpty()) {
        $selectedVehicles = $trip->tripVehicles
            ->where('isdefaultforlegs', 1)
            ->sortBy(function ($tripVehicle) {
                return $tripVehicle->sortorder ?? 999999;
            })
            ->map(function ($tripVehicle) {
                return [
                    'vehicleid' => $tripVehicle->vehicleid,
                    'vehiclerole' => $tripVehicle->vehiclerole,
                    'sortorder' => $tripVehicle->sortorder,
                ];
            })
            ->values()
            ->all();
    }

    $selectedVehicles = is_array($selectedVehicles) ? array_values($selectedVehicles) : [];

    if ($selectedVehicles === []) {
        $selectedVehicles = [
            ['vehicleid' => '', 'vehiclerole' => '', 'sortorder' => 1],
        ];
    }

    $initialTab = old('_active_trip_leg_tab', 'map');
@endphp

<style>
    [data-trip-leg-tab-panel].hidden {
        display: none;
    }
</style>

<div class="space-y-6">
    <input type="hidden" name="_active_trip_leg_tab" id="active-trip-leg-tab" value="{{ $initialTab }}">

    <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-medium text-gray-900">
                    {{ $isCreate ? 'Add Trip Leg' : 'Leg Details' }}
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Define the route segment from the start place to the end place, with optional destination and destination item detail.
                </p>
            </div>

            @if($isCreate)
                <a href="{{ route('trips.legs.index', $trip) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                    Close
                </a>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-6">
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
        </div>

        <div class="border-t border-gray-200 pt-6 space-y-6">
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <div>
                    <label for="fromplaceid" class="block text-sm font-medium text-gray-700 mb-1">From Place</label>
                    <select name="fromplaceid" id="fromplaceid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="">Select origin place</option>
                        @foreach($places as $place)
                            <option value="{{ $place->id }}"
                                    data-lat="{{ $place->latitude ?? '' }}"
                                    data-lng="{{ $place->longitude ?? '' }}"
                                    @selected((string) $selectedFromPlaceId === (string) $place->id)>
                                {{ $place->placename }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="fromdestinationid" class="block text-sm font-medium text-gray-700 mb-1">From Destination</label>
                    <select name="fromdestinationid" id="fromdestinationid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="">Select origin destination</option>
                        @foreach($destinations as $destination)
                            <option value="{{ $destination->id }}"
                                    data-place-id="{{ $destination->placeid ?? '' }}"
                                    @selected((string) $selectedFromDestinationId === (string) $destination->id)>
                                {{ $destination->destinationname }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Optional editorial destination within the selected From Place.</p>
                </div>

                <div>
                    <label for="fromdestinationitemid" class="block text-sm font-medium text-gray-700 mb-1">From Destination Item</label>
                    <select name="fromdestinationitemid" id="fromdestinationitemid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="">Select start destination item</option>
                        @foreach($destinationItems as $destinationItem)
                            @php
                                $resolvedPlaceId = $destinationItem->placeid ?? $destinationItem->destination?->placeid;
                                $resolvedLat = $destinationItem->latitude ?? $destinationItem->place?->latitude ?? $destinationItem->destination?->place?->latitude;
                                $resolvedLng = $destinationItem->longitude ?? $destinationItem->place?->longitude ?? $destinationItem->destination?->place?->longitude;
                            @endphp
                            <option value="{{ $destinationItem->id }}"
                                    data-destination-id="{{ $destinationItem->destinationid ?? '' }}"
                                    data-place-id="{{ $resolvedPlaceId ?? '' }}"
                                    data-lat="{{ $resolvedLat ?? '' }}"
                                    data-lng="{{ $resolvedLng ?? '' }}"
                                    @selected((string) $selectedFromDestinationItemId === (string) $destinationItem->id)>
                                {{ $destinationItem->itemname }}@if($destinationItem->destination?->destinationname) - {{ $destinationItem->destination->destinationname }}@endif
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">If selected, this item is used as the route start point instead of the From Place.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <div>
                    <label for="toplaceid" class="block text-sm font-medium text-gray-700 mb-1">To Place</label>
                    <select name="toplaceid" id="toplaceid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="">Select destination place</option>
                        @foreach($places as $place)
                            <option value="{{ $place->id }}"
                                    data-lat="{{ $place->latitude ?? '' }}"
                                    data-lng="{{ $place->longitude ?? '' }}"
                                    @selected((string) $selectedToPlaceId === (string) $place->id)>
                                {{ $place->placename }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="todestinationid" class="block text-sm font-medium text-gray-700 mb-1">To Destination</label>
                    <select name="todestinationid" id="todestinationid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="">Select destination</option>
                        @foreach($destinations as $destination)
                            <option value="{{ $destination->id }}"
                                    data-place-id="{{ $destination->placeid ?? '' }}"
                                    @selected((string) $selectedToDestinationId === (string) $destination->id)>
                                {{ $destination->destinationname }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Optional editorial destination within the selected To Place.</p>
                </div>

                <div>
                    <label for="todestinationitemid" class="block text-sm font-medium text-gray-700 mb-1">To Destination Item</label>
                    <select name="todestinationitemid" id="todestinationitemid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="">Select end destination item</option>
                        @foreach($destinationItems as $destinationItem)
                            @php
                                $resolvedPlaceId = $destinationItem->placeid ?? $destinationItem->destination?->placeid;
                                $resolvedLat = $destinationItem->latitude ?? $destinationItem->place?->latitude ?? $destinationItem->destination?->place?->latitude;
                                $resolvedLng = $destinationItem->longitude ?? $destinationItem->place?->longitude ?? $destinationItem->destination?->place?->longitude;
                            @endphp
                            <option value="{{ $destinationItem->id }}"
                                    data-destination-id="{{ $destinationItem->destinationid ?? '' }}"
                                    data-place-id="{{ $resolvedPlaceId ?? '' }}"
                                    data-lat="{{ $resolvedLat ?? '' }}"
                                    data-lng="{{ $resolvedLng ?? '' }}"
                                    @selected((string) $selectedToDestinationItemId === (string) $destinationItem->id)>
                                {{ $destinationItem->itemname }}@if($destinationItem->destination?->destinationname) - {{ $destinationItem->destination->destinationname }}@endif
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">If selected, this item is used as the route end point instead of the To Place.</p>
                </div>
            </div>

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text"
                       name="title"
                       id="title"
                       value="{{ old('title', $tripLeg->title ?? '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
        </div>
    </div>

    <div class="bg-white shadow-sm sm:rounded-lg">
        <div class="border-b border-gray-200 px-6 pt-4">
            <nav class="flex flex-wrap gap-2" aria-label="Trip leg sections">
                <button type="button"
                        class="trip-leg-tab-button inline-flex items-center px-4 py-2 rounded-t-md text-sm font-medium"
                        data-tab="map">
                    Route Map
                </button>

                <button type="button"
                        class="trip-leg-tab-button inline-flex items-center px-4 py-2 rounded-t-md text-sm font-medium"
                        data-tab="leg-points">
                    Leg Points
                </button>

                <button type="button"
                        class="trip-leg-tab-button inline-flex items-center px-4 py-2 rounded-t-md text-sm font-medium"
                        data-tab="distance">
                    Distance & Elevation
                </button>

                <button type="button"
                        class="trip-leg-tab-button inline-flex items-center px-4 py-2 rounded-t-md text-sm font-medium"
                        data-tab="vehicles">
                    Vehicles
                </button>

                <button type="button"
                        class="trip-leg-tab-button inline-flex items-center px-4 py-2 rounded-t-md text-sm font-medium"
                        data-tab="notes">
                    Notes
                </button>
            </nav>
        </div>

        <div class="p-6">
            <div class="trip-leg-tab-panel space-y-6" data-trip-leg-tab-panel="map">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Route Map</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Preview the trip leg between the selected From Destination Item or From Place, and the selected To Destination Item or To Place.
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
                        Select a start point and an end point with coordinates to display the route preview.
                    </div>
                </div>
            </div>

            <div class="trip-leg-tab-panel hidden space-y-6" data-trip-leg-tab-panel="leg-points">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Leg Points</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Add route anchors and planned stops between the start and end of this leg.
                        </p>
                    </div>
                </div>

                <div id="leg-point-rows" class="space-y-4">
                    @forelse($selectedLegPoints as $index => $point)
                        <div class="border border-gray-200 rounded-lg p-4 leg-point-row space-y-4">
                            <input type="hidden" name="leg_points[{{ $index }}][id]" value="{{ $point['id'] ?? '' }}">

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Seq</label>
                                    <input type="number"
                                           name="leg_points[{{ $index }}][sequence_no]"
                                           value="{{ $point['sequence_no'] ?? ($index + 1) }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                           min="1">
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Point Type</label>
                                    <select name="leg_points[{{ $index }}][pointtype]" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        <option value="route_anchor" @selected(($point['pointtype'] ?? 'route_anchor') === 'route_anchor')>Route Anchor</option>
                                        <option value="planned_stop" @selected(($point['pointtype'] ?? '') === 'planned_stop')>Planned Stop</option>
                                    </select>
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                    <input type="text"
                                           name="leg_points[{{ $index }}][title]"
                                           value="{{ $point['title'] ?? '' }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>

                                <div class="md:col-span-1 flex items-end">
                                    <button type="button"
                                            class="remove-leg-point-row inline-flex items-center px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-xs w-full justify-center">
                                        Remove
                                    </button>
                                </div>

                                <div class="md:col-span-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Place</label>
                                    <select name="leg_points[{{ $index }}][placeid]" class="leg-point-place w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        <option value="">Select place</option>
                                        @foreach($places as $place)
                                            <option value="{{ $place->id }}"
                                                    data-lat="{{ $place->latitude ?? '' }}"
                                                    data-lng="{{ $place->longitude ?? '' }}"
                                                    @selected((string) ($point['placeid'] ?? '') === (string) $place->id)>
                                                {{ $place->placename }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Destination</label>
                                    <select name="leg_points[{{ $index }}][destinationid]" class="leg-point-destination w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        <option value="">Select destination</option>
                                        @foreach ($destinations as $destination)
                                            <option value="{{ $destination->id }}"
                                                    data-place-id="{{ $destination->placeid ?? '' }}"
                                                    @selected((string) ($point['destinationid'] ?? '') === (string) $destination->id)>
                                                {{ $destination->destinationname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Destination Item</label>
                                    <select name="leg_points[{{ $index }}][destinationitemid]" class="leg-point-destination-item w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        <option value="">Select destination item</option>
                                        @foreach($destinationItems as $destinationItem)
                                            @php
                                                $resolvedPlaceId = $destinationItem->placeid ?? $destinationItem->destination?->placeid;
                                                $resolvedLat = $destinationItem->latitude ?? $destinationItem->place?->latitude ?? $destinationItem->destination?->place?->latitude;
                                                $resolvedLng = $destinationItem->longitude ?? $destinationItem->place?->longitude ?? $destinationItem->destination?->place?->longitude;
                                            @endphp
                                            <option value="{{ $destinationItem->id }}"
                                                    data-destination-id="{{ $destinationItem->destinationid ?? '' }}"
                                                    data-place-id="{{ $resolvedPlaceId ?? '' }}"
                                                    data-lat="{{ $resolvedLat ?? '' }}"
                                                    data-lng="{{ $resolvedLng ?? '' }}"
                                                    @selected((string) ($point['destinationitemid'] ?? '') === (string) $destinationItem->id)>
                                                {{ $destinationItem->itemname }}
                                                @if($destinationItem->destination?->destinationname)
                                                    - {{ $destinationItem->destination->destinationname }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-12">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                    <textarea name="leg_points[{{ $index }}][notes]"
                                              rows="2"
                                              class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ $point['notes'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="border border-dashed border-gray-300 rounded-lg p-4 text-sm text-gray-500">
                            No leg points added yet.
                        </div>
                    @endforelse
                </div>

                <template id="leg-point-row-template">
                    <div class="border border-gray-200 rounded-lg p-4 leg-point-row space-y-4">
                        <input type="hidden" name="leg_points[__INDEX__][id]" value="">

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Seq</label>
                                <input type="number"
                                       name="leg_points[__INDEX__][sequence_no]"
                                       value="__SEQ__"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                       min="1">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Point Type</label>
                                <select name="leg_points[__INDEX__][pointtype]" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="route_anchor">Route Anchor</option>
                                    <option value="planned_stop">Planned Stop</option>
                                </select>
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                <input type="text"
                                       name="leg_points[__INDEX__][title]"
                                       value=""
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div class="md:col-span-1 flex items-end">
                                <button type="button"
                                        class="remove-leg-point-row inline-flex items-center px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-xs w-full justify-center">
                                    Remove
                                </button>
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Place</label>
                                <select name="leg_points[__INDEX__][placeid]" class="leg-point-place w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Select place</option>
                                    @foreach($places as $place)
                                        <option value="{{ $place->id }}"
                                                data-lat="{{ $place->latitude ?? '' }}"
                                                data-lng="{{ $place->longitude ?? '' }}">
                                            {{ $place->placename }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Destination</label>
                                <select name="leg_points[__INDEX__][destinationid]" class="leg-point-destination w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Select destination</option>
                                    @foreach($destinations as $destination)
                                        <option value="{{ $destination->id }}"
                                                data-place-id="{{ $destination->placeid ?? '' }}">
                                            {{ $destination->destinationname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Destination Item</label>
                                <select name="leg_points[__INDEX__][destinationitemid]" class="leg-point-destination-item w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Select destination item</option>
                                    @foreach($destinationItems as $destinationItem)
                                        @php
                                            $resolvedPlaceId = $destinationItem->placeid ?? $destinationItem->destination?->placeid;
                                            $resolvedLat = $destinationItem->latitude ?? $destinationItem->place?->latitude ?? $destinationItem->destination?->place?->latitude;
                                            $resolvedLng = $destinationItem->longitude ?? $destinationItem->place?->longitude ?? $destinationItem->destination?->place?->longitude;
                                        @endphp
                                        <option value="{{ $destinationItem->id }}"
                                                data-destination-id="{{ $destinationItem->destinationid ?? '' }}"
                                                data-place-id="{{ $resolvedPlaceId ?? '' }}"
                                                data-lat="{{ $resolvedLat ?? '' }}"
                                                data-lng="{{ $resolvedLng ?? '' }}">
                                            {{ $destinationItem->itemname }}
                                            @if($destinationItem->destination?->destinationname)
                                                - {{ $destinationItem->destination->destinationname }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-12">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea name="leg_points[__INDEX__][notes]"
                                          rows="2"
                                          class="w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
                            </div>
                        </div>
                    </div>
                </template>

                <div>
                    <button type="button"
                            id="add-leg-point-row"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                        Add Leg Point
                    </button>
                </div>
            </div>

            <div class="trip-leg-tab-panel hidden space-y-6" data-trip-leg-tab-panel="distance">
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
                               min="0"
                               name="elevationgainm"
                               id="elevationgainm"
                               value="{{ old('elevationgainm', $tripLeg->elevationgainm ?? '') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>

                    <div>
                        <label for="elevationlossm" class="block text-sm font-medium text-gray-700 mb-1">Elevation Loss (m)</label>
                        <input type="number"
                               step="0.1"
                               min="0"
                               name="elevationlossm"
                               id="elevationlossm"
                               value="{{ old('elevationlossm', $tripLeg->elevationlossm ?? '') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>
                </div>
            </div>

            <div class="trip-leg-tab-panel hidden space-y-6" data-trip-leg-tab-panel="vehicles">
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
                        @foreach($selectedVehicles as $index => $selectedVehicle)
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
                        @endforeach
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

            <div class="trip-leg-tab-panel hidden space-y-6" data-trip-leg-tab-panel="notes">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Notes</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Record narrative detail, driving context, planning notes, and actual trip notes for this leg.
                    </p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description"
                                  id="description"
                                  rows="4"
                                  class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('description', $tripLeg->description ?? '') }}</textarea>
                    </div>

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
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const fromPlaceSelect = document.getElementById('fromplaceid');
    const fromDestinationSelect = document.getElementById('fromdestinationid');
    const fromDestinationItemSelect = document.getElementById('fromdestinationitemid');

    const toPlaceSelect = document.getElementById('toplaceid');
    const toDestinationSelect = document.getElementById('todestinationid');
    const toDestinationItemSelect = document.getElementById('todestinationitemid');

    const titleInput = document.getElementById('title');

    const activeTabInput = document.getElementById('active-trip-leg-tab');
    const tabButtons = document.querySelectorAll('.trip-leg-tab-button');
    const tabPanels = document.querySelectorAll('[data-trip-leg-tab-panel]');
    const legPointRows = document.getElementById('leg-point-rows');

    function setTabButtonState(button, isActive) {
        button.classList.toggle('bg-blue-600', isActive);
        button.classList.toggle('text-white', isActive);
        button.classList.toggle('bg-gray-100', !isActive);
        button.classList.toggle('text-gray-700', !isActive);
        button.classList.toggle('hover:bg-blue-700', isActive);
        button.classList.toggle('hover:bg-gray-200', !isActive);
        button.setAttribute('aria-selected', isActive ? 'true' : 'false');
    }

    function activateTripLegTab(tabName) {
        tabButtons.forEach((button) => {
            setTabButtonState(button, button.dataset.tab === tabName);
        });

        tabPanels.forEach((panel) => {
            panel.classList.toggle('hidden', panel.dataset.tripLegTabPanel !== tabName);
        });

        if (activeTabInput) {
            activeTabInput.value = tabName;
        }

        if (tabName === 'map') {
            document.dispatchEvent(new CustomEvent('trip-leg:map-tab-shown'));
        }
    }

    tabButtons.forEach((button) => {
        button.addEventListener('click', function () {
            activateTripLegTab(button.dataset.tab);
        });
    });

    activateTripLegTab(activeTabInput ? activeTabInput.value : 'map');

    function buildOptionCache(select) {
        if (!select) return [];

        return Array.from(select.options).map(option => ({
            value: option.value,
            text: option.text,
            destinationId: option.dataset.destinationId || '',
            placeId: option.dataset.placeId || '',
            lat: option.dataset.lat || '',
            lng: option.dataset.lng || '',
        }));
    }

    function rebuildSelect(select, options, placeholder, selectedValue) {
        if (!select) return;

        select.innerHTML = '';

        const placeholderOption = document.createElement('option');
        placeholderOption.value = '';
        placeholderOption.textContent = placeholder;
        select.appendChild(placeholderOption);

        options.forEach(item => {
            const option = document.createElement('option');
            option.value = item.value;
            option.textContent = item.text;

            if (item.destinationId) option.dataset.destinationId = item.destinationId;
            if (item.placeId) option.dataset.placeId = item.placeId;
            if (item.lat !== '') option.dataset.lat = item.lat;
            if (item.lng !== '') option.dataset.lng = item.lng;

            if (String(item.value) === String(selectedValue)) {
                option.selected = true;
            }

            select.appendChild(option);
        });
    }

    const fromDestinationOptions = buildOptionCache(fromDestinationSelect);
    const fromDestinationItemOptions = buildOptionCache(fromDestinationItemSelect);
    const toDestinationOptions = buildOptionCache(toDestinationSelect);
    const toDestinationItemOptions = buildOptionCache(toDestinationItemSelect);

    function filterFromDestinations() {
        if (!fromDestinationSelect) return;

        const selectedFromPlaceId = fromPlaceSelect ? fromPlaceSelect.value : '';
        const currentValue = fromDestinationSelect.value;

        const filtered = fromDestinationOptions.filter(option => {
            if (!option.value) return false;
            if (selectedFromPlaceId) {
                return String(option.placeId) === String(selectedFromPlaceId);
            }
            return true;
        });

        const stillValid = filtered.some(option => String(option.value) === String(currentValue));
        rebuildSelect(fromDestinationSelect, filtered, 'Select origin destination', stillValid ? currentValue : '');

        if (!stillValid) {
            fromDestinationSelect.value = '';
        }
    }

    function filterFromDestinationItems() {
        if (!fromDestinationItemSelect) return;

        const selectedFromPlaceId = fromPlaceSelect ? fromPlaceSelect.value : '';
        const selectedFromDestinationId = fromDestinationSelect ? fromDestinationSelect.value : '';
        const currentValue = fromDestinationItemSelect.value;

        const filtered = fromDestinationItemOptions.filter(option => {
            if (!option.value) return false;
            if (selectedFromPlaceId && String(option.placeId) !== String(selectedFromPlaceId)) return false;
            if (selectedFromDestinationId && String(option.destinationId) !== String(selectedFromDestinationId)) return false;
            if (!selectedFromDestinationId && selectedFromPlaceId) return String(option.placeId) === String(selectedFromPlaceId);
            return true;
        });

        const stillValid = filtered.some(option => String(option.value) === String(currentValue));
        rebuildSelect(fromDestinationItemSelect, filtered, 'Select start destination item', stillValid ? currentValue : '');

        if (!stillValid) {
            fromDestinationItemSelect.value = '';
        }
    }

    function filterToDestinations() {
        if (!toDestinationSelect) return;

        const selectedToPlaceId = toPlaceSelect ? toPlaceSelect.value : '';
        const currentValue = toDestinationSelect.value;

        const filtered = toDestinationOptions.filter(option => {
            if (!option.value) return false;
            if (selectedToPlaceId) {
                return String(option.placeId) === String(selectedToPlaceId);
            }
            return true;
        });

        const stillValid = filtered.some(option => String(option.value) === String(currentValue));
        rebuildSelect(toDestinationSelect, filtered, 'Select destination', stillValid ? currentValue : '');

        if (!stillValid) {
            toDestinationSelect.value = '';
        }
    }

    function filterToDestinationItems() {
        if (!toDestinationItemSelect) return;

        const selectedToPlaceId = toPlaceSelect ? toPlaceSelect.value : '';
        const selectedToDestinationId = toDestinationSelect ? toDestinationSelect.value : '';
        const currentValue = toDestinationItemSelect.value;

        const filtered = toDestinationItemOptions.filter(option => {
            if (!option.value) return false;
            if (selectedToPlaceId && String(option.placeId) !== String(selectedToPlaceId)) return false;
            if (selectedToDestinationId && String(option.destinationId) !== String(selectedToDestinationId)) return false;
            if (!selectedToDestinationId && selectedToPlaceId) return String(option.placeId) === String(selectedToPlaceId);
            return true;
        });

        const stillValid = filtered.some(option => String(option.value) === String(currentValue));
        rebuildSelect(toDestinationItemSelect, filtered, 'Select end destination item', stillValid ? currentValue : '');

        if (!stillValid) {
            toDestinationItemSelect.value = '';
        }
    }

    function getSelectedText(select) {
        if (!select) return '';
        const option = select.options[select.selectedIndex];
        return option && option.value ? option.text.trim() : '';
    }

    let titleTouched = false;

    if (titleInput) {
        titleInput.dataset.lastAutoTitle = titleInput.value.trim();

        titleInput.addEventListener('input', function () {
            const currentValue = titleInput.value.trim();
            const lastAutoTitle = titleInput.dataset.lastAutoTitle || '';
            titleTouched = currentValue !== '' && currentValue !== lastAutoTitle;
        });
    }

    function updateTitle() {
        if (!titleInput) return;

        const fromDestinationItemText = getSelectedText(fromDestinationItemSelect);
        const fromPlaceText = getSelectedText(fromPlaceSelect);
        const toDestinationItemText = getSelectedText(toDestinationItemSelect);
        const toPlaceText = getSelectedText(toPlaceSelect);
        const toDestinationText = getSelectedText(toDestinationSelect);

        const fromLabel = fromDestinationItemText || fromPlaceText;
        const toLabel = toDestinationItemText || toPlaceText || toDestinationText;

        let computed = '';

        if (fromLabel && toLabel) {
            computed = `${fromLabel} → ${toLabel}`;
        } else if (fromLabel) {
            computed = fromLabel;
        } else if (toLabel) {
            computed = toLabel;
        }

        const currentValue = titleInput.value.trim();
        const lastAutoTitle = titleInput.dataset.lastAutoTitle || '';

        if (!titleTouched || currentValue === '' || currentValue === lastAutoTitle) {
            titleInput.value = computed;
            titleInput.dataset.lastAutoTitle = computed;
            titleTouched = false;
        }
    }

    function buildLegPointDestinationCache(row) {
        const select = row.querySelector('.leg-point-destination');
        return buildOptionCache(select);
    }

    function buildLegPointDestinationItemCache(row) {
        const select = row.querySelector('.leg-point-destination-item');
        return buildOptionCache(select);
    }

    function ensureLegPointCaches(row) {
        if (!row._destinationOptionsCache) {
            row._destinationOptionsCache = buildLegPointDestinationCache(row);
        }

        if (!row._destinationItemOptionsCache) {
            row._destinationItemOptionsCache = buildLegPointDestinationItemCache(row);
        }
    }

    function filterLegPointRow(row) {
        if (!row) return;

        ensureLegPointCaches(row);

        const placeSelect = row.querySelector('.leg-point-place');
        const destinationSelect = row.querySelector('.leg-point-destination');
        const destinationItemSelect = row.querySelector('.leg-point-destination-item');

        if (!placeSelect || !destinationSelect || !destinationItemSelect) return;

        const selectedPlaceId = placeSelect.value;
        const currentDestinationValue = destinationSelect.value;
        const currentDestinationItemValue = destinationItemSelect.value;

        const filteredDestinations = row._destinationOptionsCache.filter(option => {
            if (!option.value) return false;

            if (selectedPlaceId) {
                return String(option.placeId) === String(selectedPlaceId);
            }

            return true;
        });

        const destinationStillValid = filteredDestinations.some(option => String(option.value) === String(currentDestinationValue));

        rebuildSelect(
            destinationSelect,
            filteredDestinations,
            'Select destination',
            destinationStillValid ? currentDestinationValue : ''
        );

        if (!destinationStillValid) {
            destinationSelect.value = '';
        }

        const selectedDestinationId = destinationSelect.value;

        const filteredDestinationItems = row._destinationItemOptionsCache.filter(option => {
            if (!option.value) return false;

            if (selectedPlaceId && String(option.placeId) !== String(selectedPlaceId)) {
                return false;
            }

            if (selectedDestinationId && String(option.destinationId) !== String(selectedDestinationId)) {
                return false;
            }

            if (!selectedDestinationId && selectedPlaceId) {
                return String(option.placeId) === String(selectedPlaceId);
            }

            return true;
        });

        const destinationItemStillValid = filteredDestinationItems.some(option => String(option.value) === String(currentDestinationItemValue));

        rebuildSelect(
            destinationItemSelect,
            filteredDestinationItems,
            'Select destination item',
            destinationItemStillValid ? currentDestinationItemValue : ''
        );

        if (!destinationItemStillValid) {
            destinationItemSelect.value = '';
        }
    }

    function bindLegPointRow(row) {
        if (!row || row.dataset.rowBound === 'true') return;

        ensureLegPointCaches(row);

        const placeSelect = row.querySelector('.leg-point-place');
        const destinationSelect = row.querySelector('.leg-point-destination');
        const destinationItemSelect = row.querySelector('.leg-point-destination-item');

        if (placeSelect) {
            placeSelect.addEventListener('change', function () {
                filterLegPointRow(row);
                document.dispatchEvent(new CustomEvent('trip-leg:selection-updated'));
            });
        }

        if (destinationSelect) {
            destinationSelect.addEventListener('change', function () {
                filterLegPointRow(row);
                document.dispatchEvent(new CustomEvent('trip-leg:selection-updated'));
            });
        }

        if (destinationItemSelect) {
            destinationItemSelect.addEventListener('change', function () {
                document.dispatchEvent(new CustomEvent('trip-leg:selection-updated'));
            });
        }

        row.dataset.rowBound = 'true';
        filterLegPointRow(row);
    }

    function bindAllLegPointRows() {
        if (!legPointRows) return;

        legPointRows.querySelectorAll('.leg-point-row').forEach((row) => {
            bindLegPointRow(row);
        });
    }

    function emitTripLegSelectionUpdated() {
        window.setTimeout(() => {
            document.dispatchEvent(new CustomEvent('trip-leg:selection-updated'));
        }, 0);
    }

    function refreshDependentUi() {
        filterFromDestinations();
        filterFromDestinationItems();
        filterToDestinations();
        filterToDestinationItems();
        updateTitle();
        bindAllLegPointRows();
        emitTripLegSelectionUpdated();
    }

    if (fromPlaceSelect) {
        fromPlaceSelect.addEventListener('change', refreshDependentUi);
    }

    if (fromDestinationSelect) {
        fromDestinationSelect.addEventListener('change', function () {
            filterFromDestinationItems();
            updateTitle();
            emitTripLegSelectionUpdated();
        });
    }

    if (toPlaceSelect) {
        toPlaceSelect.addEventListener('change', refreshDependentUi);
    }

    if (toDestinationSelect) {
        toDestinationSelect.addEventListener('change', function () {
            filterToDestinationItems();
            updateTitle();
            emitTripLegSelectionUpdated();
        });
    }

    if (fromDestinationItemSelect) {
        fromDestinationItemSelect.addEventListener('change', function () {
            updateTitle();
            emitTripLegSelectionUpdated();
        });
    }

    if (toDestinationItemSelect) {
        toDestinationItemSelect.addEventListener('change', function () {
            updateTitle();
            emitTripLegSelectionUpdated();
        });
    }

    document.addEventListener('trip-leg:leg-point-row-added', function (event) {
        const row = event.detail && event.detail.row ? event.detail.row : null;
        if (!row) return;

        bindLegPointRow(row);
        emitTripLegSelectionUpdated();
    });

    bindAllLegPointRows();
    refreshDependentUi();
});
</script>