<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Trip Legs
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $trip->tripname }}
                </p>
                <p class="mt-1 text-sm text-gray-500">
                    Status: {{ ucfirst($trip->tripstatus) }} ·
                    Start: {{ optional($trip->startdate)->format('d M Y') ?? '—' }} ·
                    End: {{ optional($trip->enddate)->format('d M Y') ?? '—' }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('trips.legs.index', array_merge(['trip' => $trip->id], request()->query(), ['show_create' => 1])) }}"
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                    Add Trip Leg
                </a>
                <a href="{{ route('trips.edit', ['trip' => $trip, 'tab' => 'workflow']) }}"
                class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                    Back to Trip
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">
            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('trips.legs.index', $trip) }}">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label for="fromplace_id" class="block text-sm font-medium text-gray-700 mb-1">
                                From Place
                            </label>
                            <select name="fromplace_id" id="fromplace_id" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All origin places</option>
                                @foreach($places as $place)
                                    <option value="{{ $place->id }}" @selected((string) request('fromplace_id') === (string) $place->id)>
                                        {{ $place->placename }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="fromdestination_id" class="block text-sm font-medium text-gray-700 mb-1">
                                From Destination
                            </label>
                            <select name="fromdestination_id" id="fromdestination_id" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All origin destinations</option>
                                @foreach($destinations as $destination)
                                    <option value="{{ $destination->id }}" @selected((string) request('fromdestination_id') === (string) $destination->id)>
                                        {{ $destination->destinationname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="toplace_id" class="block text-sm font-medium text-gray-700 mb-1">
                                To Place
                            </label>
                            <select name="toplace_id" id="toplace_id" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All destination places</option>
                                @foreach($places as $place)
                                    <option value="{{ $place->id }}" @selected((string) request('toplace_id') === (string) $place->id)>
                                        {{ $place->placename }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="todestination_id" class="block text-sm font-medium text-gray-700 mb-1">
                                To Destination
                            </label>
                            <select name="todestination_id" id="todestination_id" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All destination destinations</option>
                                @foreach($destinations as $destination)
                                    <option value="{{ $destination->id }}" @selected((string) request('todestination_id') === (string) $destination->id)>
                                        {{ $destination->destinationname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>



                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                Filter
                            </button>

                            <a href="{{ route('trips.legs.index', $trip) }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            @if($showCreate)
                <form method="POST"
                      action="{{ route('trips.legs.store', $trip) }}"
                      id="trip-leg-create-form"
                      class="space-y-6">
                    @csrf

                    @php
                        $tripLeg = null;
                    @endphp

                    @include('trip-legs._form', [
                        'trip' => $trip,
                        'tripLeg' => $tripLeg,
                        'places' => $places,
                        'destinations' => $destinations,
                        'destinationItems' => $destinationItems,
                        'vehicles' => $vehicles,
                        'selectedFromDestinationId' => $selectedFromDestinationId ?? null,
                        'selectedFromPlaceId' => $selectedFromPlaceId ?? null,
                        'selectedToDestinationId' => $selectedToDestinationId ?? null,
                        'selectedToPlaceId' => $selectedToPlaceId ?? null,
                        'isCreate' => true,
                    ])
                </form>
            @endif

            @if($legs->isEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">No Trip Legs Yet</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Start building the itinerary by adding the first leg for this trip.
                            </p>
                        </div>

                        @unless($showCreate)
                            <a href="{{ route('trips.legs.index', ['trip' => $trip->id, 'show_create' => 1]) }}
                               " class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                Add First Leg
                            </a>
                        @endunless
                    </div>
                </div>
            @else
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Leg</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Dates</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">From</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">To</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Distance</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Title</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach($legs as $leg)
                                    <tr>
                                        <td class="px-4 py-3 align-top">
                                            {{ $leg->legnumber ?? '—' }}
                                        </td>

                                        <td class="px-4 py-3 align-top">
                                            <div>{{ $leg->startdate ? \Illuminate\Support\Carbon::parse($leg->startdate)->format('d/m/Y') : '—' }}</div>
                                            <div class="text-xs text-gray-500">
                                                {{ $leg->enddate ? \Illuminate\Support\Carbon::parse($leg->enddate)->format('d/m/Y') : '—' }}
                                            </div>
                                        </td>

                                        <td class="px-4 py-3 align-top">
                                            <div class="text-sm text-gray-900">
                                                {{ $leg->fromPlace?->placename ?? '—' }}
                                            </div>

                                            @if($leg->fromDestination?->destinationname)
                                                <div class="text-xs text-gray-500">
                                                    Destination: {{ $leg->fromDestination->destinationname }}
                                                </div>
                                            @endif

                                            @if($leg->fromDestinationItem?->itemname)
                                                <div class="text-xs text-gray-500">
                                                    Item: {{ $leg->fromDestinationItem->itemname }}
                                                </div>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 align-top">
                                            <div class="text-sm text-gray-900">
                                                {{ $leg->toPlace?->placename ?? '—' }}
                                            </div>

                                            @if($leg->toDestination?->destinationname)
                                                <div class="text-xs text-gray-500">
                                                    Destination: {{ $leg->toDestination->destinationname }}
                                                </div>
                                            @endif

                                            @if($leg->toDestinationItem?->itemname)
                                                <div class="text-xs text-gray-500">
                                                    Item: {{ $leg->toDestinationItem->itemname }}
                                                </div>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 align-top">
                                            {{ $leg->distancekm !== null ? number_format((float) $leg->distancekm, 1) . ' km' : '—' }}
                                        </td>

                                        <td class="px-4 py-3 align-top">
                                            {{ $leg->title ?: '—' }}
                                        </td>

                                        <td class="px-4 py-3 align-top">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('trips.legs.edit', ['trip' => $trip, 'tripLeg' => $leg]) }}"
                                                class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs">
                                                    Edit
                                                </a>

                                                <form method="POST"
                                                    action="{{ route('trips.legs.destroy', ['trip' => $trip, 'tripLeg' => $leg]) }}"
                                                    onsubmit="return confirm('Delete this trip leg?');">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit"
                                                            class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700 text-xs">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

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
        const form = document.getElementById('trip-leg-create-form');
        if (!form) return;

        let isDirty = false;
        let isSubmitting = false;

        form.querySelectorAll('input, select, textarea').forEach((element) => {
            element.addEventListener('change', () => {
                isDirty = true;
            });
            element.addEventListener('input', () => {
                isDirty = true;
            });
        });

        form.addEventListener('submit', function () {
            isSubmitting = true;
            isDirty = false;
        });

        window.addEventListener('beforeunload', function (event) {
            if (isDirty && !isSubmitting) {
                event.preventDefault();
                event.returnValue = '';
            }
        });

        const vehicleRows = document.getElementById('vehicle-rows');
        const addVehicleRowButton = document.getElementById('add-vehicle-row');

        function reindexVehicleRows() {
            if (!vehicleRows) return;

            vehicleRows.querySelectorAll('.vehicle-row').forEach((row, index) => {
                row.querySelectorAll('select, input').forEach((field) => {
                    if (field.name.includes('[vehicleid]')) {
                        field.name = `vehicles[${index}][vehicleid]`;
                    } else if (field.name.includes('[vehiclerole]')) {
                        field.name = `vehicles[${index}][vehiclerole]`;
                    } else if (field.name.includes('[sortorder]')) {
                        field.name = `vehicles[${index}][sortorder]`;
                    }
                });
            });
        }

        if (addVehicleRowButton && vehicleRows) {
            addVehicleRowButton.addEventListener('click', function () {
                const index = vehicleRows.querySelectorAll('.vehicle-row').length;
                const template = document.createElement('div');
                template.className = 'grid grid-cols-1 md:grid-cols-12 gap-3 vehicle-row';
                template.innerHTML = `
                    <div class="md:col-span-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle</label>
                        <select name="vehicles[${index}][vehicleid]" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">Select vehicle</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}">{{ $vehicle->vehiclename }}{{ $vehicle->registrationnumber ? ' (' . $vehicle->registrationnumber . ')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <input type="text"
                               name="vehicles[${index}][vehiclerole]"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                               placeholder="Tow vehicle, caravan, support vehicle">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sort</label>
                        <input type="number"
                               name="vehicles[${index}][sortorder]"
                               value="${index + 1}"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                               min="0">
                    </div>

                    <div class="md:col-span-1 flex items-end">
                        <button type="button"
                                class="remove-vehicle-row inline-flex items-center px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-xs w-full justify-center">
                            Remove
                        </button>
                    </div>
                `;

                vehicleRows.appendChild(template);
                isDirty = true;
            });

            vehicleRows.addEventListener('click', function (event) {
                const button = event.target.closest('.remove-vehicle-row');
                if (!button) return;

                const rows = vehicleRows.querySelectorAll('.vehicle-row');
                if (rows.length === 1) {
                    rows[0].querySelectorAll('input').forEach(input => input.value = '');
                    rows[0].querySelectorAll('select').forEach(select => select.selectedIndex = 0);
                    isDirty = true;
                    return;
                }

                button.closest('.vehicle-row').remove();
                reindexVehicleRows();
                isDirty = true;
            });
        }

        const mapElement = document.getElementById('trip-leg-map');
        const summaryElement = document.getElementById('trip-leg-map-summary');
        const googleMapsLink = document.getElementById('trip-leg-open-in-google-maps');

        if (!mapElement || typeof L === 'undefined') {
            return;
        }

        const fromPlaceSelect = document.getElementById('fromplaceid');
        const toPlaceSelect = document.getElementById('toplaceid');
        const fromDestinationItemSelect = document.getElementById('fromdestinationitemid');
        const destinationItemSelect = document.getElementById('destinationitemid');

        function getSelectedCoords(select) {
            if (!select) return null;

            const option = select.options[select.selectedIndex];
            if (!option || !option.value) return null;

            const lat = parseFloat(option.dataset.lat);
            const lng = parseFloat(option.dataset.lng);

            if (Number.isNaN(lat) || Number.isNaN(lng)) return null;

            return {
                name: option.text.trim(),
                lat: lat,
                lng: lng,
            };
        }

        function getCurrentFromPoint() {
            const item = getSelectedCoords(fromDestinationItemSelect);
            if (item && !Number.isNaN(item.lat) && !Number.isNaN(item.lng)) {
                return item;
            }
            return getSelectedCoords(fromPlaceSelect);
        }

        function getCurrentToPoint() {
            const item = getSelectedCoords(destinationItemSelect);
            if (item && !Number.isNaN(item.lat) && !Number.isNaN(item.lng)) {
                return item;
            }
            return getSelectedCoords(toPlaceSelect);
        }

        function updateGoogleMapsLink(from, to) {
            if (!googleMapsLink) return;

            if (from && to) {
                googleMapsLink.href = `https://www.google.com/maps/dir/${from.lat},${from.lng}/${to.lat},${to.lng}/`;
                googleMapsLink.classList.remove('pointer-events-none', 'opacity-50');
            } else if (from) {
                googleMapsLink.href = `https://www.google.com/maps?q=${from.lat},${from.lng}`;
                googleMapsLink.classList.remove('pointer-events-none', 'opacity-50');
            } else if (to) {
                googleMapsLink.href = `https://www.google.com/maps?q=${to.lat},${to.lng}`;
                googleMapsLink.classList.remove('pointer-events-none', 'opacity-50');
            } else {
                googleMapsLink.href = 'https://www.google.com/maps';
                googleMapsLink.classList.add('pointer-events-none', 'opacity-50');
            }
        }

        const map = L.map('trip-leg-map').setView([-37.8136, 144.9631], 6);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        let fromMarker = null;
        let toMarker = null;
        let routeLayer = null;

        function clearRoute() {
            if (fromMarker) {
                map.removeLayer(fromMarker);
                fromMarker = null;
            }

            if (toMarker) {
                map.removeLayer(toMarker);
                toMarker = null;
            }

            if (routeLayer) {
                map.removeLayer(routeLayer);
                routeLayer = null;
            }
        }

        function setSummaryHtml(html) {
            if (summaryElement) {
                summaryElement.innerHTML = html;
            }
        }

        async function refreshMap() {
            const from = getCurrentFromPoint();
            const to = getCurrentToPoint();

            console.log('Create page map refresh:');
            console.log('from =', from);
            console.log('to   =', to);

            clearRoute();
            updateGoogleMapsLink(from, to);

            if (!from && !to) {
                if (summaryElement) {
                    summaryElement.textContent = 'Select a start point and an end point with coordinates to display the route preview.';
                }
                return;
            }

            if (from) {
                fromMarker = L.marker([from.lat, from.lng])
                    .addTo(map)
                    .bindPopup(`<strong>From</strong><br>${from.name}`);
            }

            if (to) {
                toMarker = L.marker([to.lat, to.lng])
                    .addTo(map)
                    .bindPopup(`<strong>To</strong><br>${to.name}`);
            }

            if (from && !to) {
                map.setView([from.lat, from.lng], 10);
                if (summaryElement) {
                    summaryElement.textContent = 'Only the start point has coordinates available. Select an end point to preview the leg.';
                }
                return;
            }

            if (!from && to) {
                map.setView([to.lat, to.lng], 10);
                if (summaryElement) {
                    summaryElement.textContent = 'Only the end point has coordinates available. Select a start point to preview the leg.';
                }
                return;
            }

            if (summaryElement) {
                summaryElement.textContent = 'Loading routed road preview...';
            }

            const osrmUrl =
                `https://router.project-osrm.org/route/v1/driving/` +
                `${from.lng},${from.lat};${to.lng},${to.lat}` +
                `?overview=full&geometries=geojson&steps=false`;

            try {
                const response = await fetch(osrmUrl, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error(`Routing request failed with status ${response.status}`);
                }

                const data = await response.json();

                if (!data.routes || !data.routes.length || !data.routes[0].geometry) {
                    throw new Error('No route returned');
                }

                const route = data.routes[0];

                routeLayer = L.geoJSON(route.geometry, {
                    style: {
                        color: '#2563eb',
                        weight: 5,
                        opacity: 0.9
                    }
                }).addTo(map);

                map.fitBounds(routeLayer.getBounds(), { padding: [30, 30] });

                const distanceKm = (route.distance / 1000).toFixed(1);
                const durationMinutes = Math.round(route.duration / 60);

                setSummaryHtml(`
                    <span class="font-medium text-gray-900">${from.name}</span>
                    <span class="text-gray-400"> to </span>
                    <span class="font-medium text-gray-900">${to.name}</span>
                    <span class="text-gray-500"> — routed via roads, ${distanceKm} km, about ${durationMinutes} min</span>
                `);
            } catch (error) {
                routeLayer = L.polyline([
                    [from.lat, from.lng],
                    [to.lat, to.lng]
                ], {
                    color: '#2563eb',
                    weight: 4,
                    opacity: 0.6,
                    dashArray: '8, 8'
                }).addTo(map);

                map.fitBounds(routeLayer.getBounds(), { padding: [30, 30] });

                setSummaryHtml(`
                    <span class="font-medium text-gray-900">${from.name}</span>
                    <span class="text-gray-400"> to </span>
                    <span class="font-medium text-gray-900">${to.name}</span>
                    <span class="text-amber-600"> — routing unavailable, showing straight-line fallback</span>
                `);
            }
        }

        [fromPlaceSelect, toPlaceSelect, fromDestinationItemSelect, destinationItemSelect].forEach((select) => {
            if (select) {
                select.addEventListener('change', refreshMap);
            }
        });

        document.addEventListener('trip-leg:selection-updated', function () {
            refreshMap();
        });

        refreshMap();

        setTimeout(function () {
            map.invalidateSize();
        }, 150);
    });
</script>
</x-app-layout>