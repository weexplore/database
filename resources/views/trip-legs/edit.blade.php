<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Trip Leg
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $trip->tripname }}
                </p>
            </div>

            <a href="{{ route('trips.legs.index', $trip) }}"
               class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                Back to Trip Legs
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <form method="POST"
                  action="{{ route('trips.legs.update', ['trip' => $trip, 'tripLeg' => $tripLeg]) }}"
                  id="trip-leg-edit-form"
                  class="space-y-6">
                @csrf
                @method('PUT')

                @php
                    $selectedDestinationId = $selectedDestinationId ?? null;
                    $selectedFromPlaceId = $selectedFromPlaceId ?? null;
                    $selectedToPlaceId = $selectedToPlaceId ?? null;
                @endphp

                @include('trip-legs._form', [
                    'trip' => $trip,
                    'tripLeg' => $tripLeg,
                    'places' => $places,
                    'destinations' => $destinations,
                    'selectedDestinationId' => $selectedDestinationId,
                    'selectedFromPlaceId' => $selectedFromPlaceId,
                    'selectedToPlaceId' => $selectedToPlaceId,
                    'isCreate' => false,
                ])
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('trip-leg-edit-form');
            if (!form) return;

            let isDirty = false;
            let isSubmitting = false;

            form.querySelectorAll('input, select, textarea').forEach((element) => {
                element.addEventListener('change', () => isDirty = true);
                element.addEventListener('input', () => isDirty = true);
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
                    return;
                }

                button.closest('.vehicle-row').remove();
                reindexVehicleRows();
                isDirty = true;
            });
        }
    </script>
    <script>
    let isDirty = false;
    let isSubmitting = false;

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('trip-leg-edit-form');
        if (!form) return;

        form.querySelectorAll('input, select, textarea').forEach((element) => {
            element.addEventListener('change', () => isDirty = true);
            element.addEventListener('input', () => isDirty = true);
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

        const tripLegMap = @json($tripLegMap ?? ['from' => null, 'to' => null]);

        const from = tripLegMap.from;
        const to = tripLegMap.to;

        const hasFromCoords = from && from.lat !== null && from.lng !== null;
        const hasToCoords = to && to.lat !== null && to.lng !== null;

        const defaultLat = hasFromCoords ? from.lat : (hasToCoords ? to.lat : -37.8136);
        const defaultLng = hasFromCoords ? from.lng : (hasToCoords ? to.lng : 144.9631);
        const defaultZoom = (hasFromCoords || hasToCoords) ? 7 : 6;

        const map = L.map('trip-leg-map').setView([defaultLat, defaultLng], defaultZoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        let bounds = [];

        if (hasFromCoords) {
            const fromLatLng = [from.lat, from.lng];
            bounds.push(fromLatLng);

            L.marker(fromLatLng)
                .addTo(map)
                .bindPopup(`<strong>From</strong><br>${from.name}`);
        }

        if (hasToCoords) {
            const toLatLng = [to.lat, to.lng];
            bounds.push(toLatLng);

            L.marker(toLatLng)
                .addTo(map)
                .bindPopup(`<strong>To</strong><br>${to.name}`);
        }

        async function loadRoadRoute() {
            if (!hasFromCoords || !hasToCoords) {
                if (bounds.length === 1) {
                    map.setView(bounds[0], 10);
                    if (summaryElement) {
                        summaryElement.textContent = 'Only one place has coordinates available. Select both places with coordinates to preview the leg.';
                    }

                    const point = hasFromCoords ? from : to;
                    if (googleMapsLink) {
                        googleMapsLink.href = `https://www.google.com/maps?q=${point.lat},${point.lng}`;
                    }
                } else {
                    if (summaryElement) {
                        summaryElement.textContent = 'No coordinates are available yet for the selected places.';
                    }

                    if (googleMapsLink) {
                        googleMapsLink.href = 'https://www.google.com/maps';
                    }
                }

                return;
            }

            if (summaryElement) {
                summaryElement.textContent = 'Loading routed road preview...';
            }

            if (googleMapsLink) {
                googleMapsLink.href = `https://www.google.com/maps/dir/${from.lat},${from.lng}/${to.lat},${to.lng}`;
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
                const routeLayer = L.geoJSON(route.geometry, {
                    style: {
                        color: '#2563eb',
                        weight: 5,
                        opacity: 0.9
                    }
                }).addTo(map);

                map.fitBounds(routeLayer.getBounds(), { padding: [30, 30] });

                const distanceKm = (route.distance / 1000).toFixed(1);
                const durationMinutes = Math.round(route.duration / 60);

                if (summaryElement) {
                    summaryElement.innerHTML = `
                        <span class="font-medium text-gray-900">${from.name}</span>
                        <span class="text-gray-400"> to </span>
                        <span class="font-medium text-gray-900">${to.name}</span>
                        <span class="text-gray-500"> — routed via roads, ${distanceKm} km, about ${durationMinutes} min</span>
                    `;
                }
            } catch (error) {
                const fallbackLine = L.polyline([
                    [from.lat, from.lng],
                    [to.lat, to.lng]
                ], {
                    color: '#2563eb',
                    weight: 4,
                    opacity: 0.6,
                    dashArray: '8, 8'
                }).addTo(map);

                map.fitBounds(fallbackLine.getBounds(), { padding: [30, 30] });

                if (summaryElement) {
                    summaryElement.innerHTML = `
                        <span class="font-medium text-gray-900">${from.name}</span>
                        <span class="text-gray-400"> to </span>
                        <span class="font-medium text-gray-900">${to.name}</span>
                        <span class="text-amber-600"> — routing unavailable, showing straight-line fallback</span>
                    `;
                }
            }
        }

        loadRoadRoute();

        setTimeout(function () {
            map.invalidateSize();
        }, 150);
    });
</script>
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
</x-app-layout>