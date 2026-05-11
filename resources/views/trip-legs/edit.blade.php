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
            const form = document.getElementById('trip-leg-edit-form');
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

            const legPointRows = document.getElementById('leg-point-rows');
            const addLegPointRowButton = document.getElementById('add-leg-point-row');

            function reindexLegPointRows() {
                if (!legPointRows) return;

                legPointRows.querySelectorAll('.leg-point-row').forEach((row, index) => {
                    row.querySelectorAll('input, select, textarea').forEach((field) => {
                        field.name = field.name.replace(/leg_points\[\d+\]/, `leg_points[${index}]`);
                    });
                });
            }

            function removeEmptyLegPointMessage() {
                if (!legPointRows) return;

                const emptyMessage = legPointRows.querySelector('.border-dashed');
                if (emptyMessage) {
                    emptyMessage.remove();
                }
            }

            if (addLegPointRowButton && legPointRows) {
                addLegPointRowButton.addEventListener('click', function () {
                    const index = legPointRows.querySelectorAll('.leg-point-row').length;
                    const template = document.getElementById('leg-point-row-template');

                    if (!template) return;

                    const html = template.innerHTML
                        .replaceAll('__INDEX__', index)
                        .replaceAll('__SEQ__', index + 1);

                    const tempWrapper = document.createElement('div');
                    tempWrapper.innerHTML = html.trim();

                    const row = tempWrapper.firstElementChild;
                    removeEmptyLegPointMessage();
                    legPointRows.appendChild(row);

                    isDirty = true;
                    document.dispatchEvent(new CustomEvent('trip-leg:selection-updated'));
                });

                legPointRows.addEventListener('click', function (event) {
                    const button = event.target.closest('.remove-leg-point-row');
                    if (!button) return;

                    button.closest('.leg-point-row').remove();
                    reindexLegPointRows();
                    isDirty = true;
                    document.dispatchEvent(new CustomEvent('trip-leg:selection-updated'));
                });

                legPointRows.addEventListener('change', function () {
                    isDirty = true;
                    document.dispatchEvent(new CustomEvent('trip-leg:selection-updated'));
                });

                legPointRows.addEventListener('input', function () {
                    isDirty = true;
                    document.dispatchEvent(new CustomEvent('trip-leg:selection-updated'));
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
            const distanceKmInput = document.getElementById('distancekm');

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
                return getSelectedCoords(fromDestinationItemSelect) || getSelectedCoords(fromPlaceSelect);
            }

            function getCurrentToPoint() {
                return getSelectedCoords(destinationItemSelect) || getSelectedCoords(toPlaceSelect);
            }

            function getSelectedOptionCoords(select) {
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

            function getLegPointCoords() {
                if (!legPointRows) return [];

                return Array.from(legPointRows.querySelectorAll('.leg-point-row'))
                    .map((row) => {
                        const sequenceInput = row.querySelector('input[name*="[sequence_no]"]');
                        const destinationItemSelect = row.querySelector('.leg-point-destination-item');
                        const placeSelect = row.querySelector('.leg-point-place');

                        const destinationItemPoint = getSelectedOptionCoords(destinationItemSelect);
                        const placePoint = getSelectedOptionCoords(placeSelect);
                        const chosenPoint = destinationItemPoint || placePoint;

                        if (!chosenPoint) {
                            return null;
                        }

                        return {
                            sequence: parseInt(sequenceInput?.value || '9999', 10),
                            name: chosenPoint.name,
                            lat: chosenPoint.lat,
                            lng: chosenPoint.lng,
                        };
                    })
                    .filter(Boolean)
                    .sort((a, b) => a.sequence - b.sequence);
            }

            function updateGoogleMapsLink(from, to, legPoints = []) {
                if (!googleMapsLink) return;

                const points = [
                    ...(from ? [from] : []),
                    ...legPoints,
                    ...(to ? [to] : []),
                ];

                if (points.length >= 2) {
                    const path = points.map(point => `${point.lat},${point.lng}`).join('/');
                    googleMapsLink.href = `https://www.google.com/maps/dir/${path}/`;
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

            const initialFrom = @json($tripLegMap['from'] ?? null);
            const initialTo = @json($tripLegMap['to'] ?? null);

            const defaultLat = initialFrom?.lat ?? initialTo?.lat ?? -37.8136;
            const defaultLng = initialFrom?.lng ?? initialTo?.lng ?? 144.9631;
            const defaultZoom = (initialFrom || initialTo) ? 7 : 6;

            const map = L.map('trip-leg-map').setView([defaultLat, defaultLng], defaultZoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            let routeMarkers = [];
            let routeLayer = null;

            function clearRoute() {
                routeMarkers.forEach(marker => map.removeLayer(marker));
                routeMarkers = [];

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
                const legPoints = getLegPointCoords();

                clearRoute();
                updateGoogleMapsLink(from, to, legPoints);

                const routePoints = [
                    ...(from ? [{ ...from, role: 'from' }] : []),
                    ...legPoints.map(point => ({ ...point, role: 'via' })),
                    ...(to ? [{ ...to, role: 'to' }] : []),
                ];

                if (routePoints.length === 0) {
                    if (summaryElement) {
                        summaryElement.textContent = 'Select a start point and an end point with coordinates to display the route preview.';
                    }
                    return;
                }

                routePoints.forEach((point, index) => {
                    let label = 'Point';

                    if (point.role === 'from') {
                        label = 'From';
                    } else if (point.role === 'to') {
                        label = 'To';
                    } else {
                        label = `Via ${index}`;
                    }

                    const marker = L.marker([point.lat, point.lng])
                        .addTo(map)
                        .bindPopup(`<strong>${label}</strong><br>${point.name}`);

                    routeMarkers.push(marker);
                });

                if (routePoints.length === 1) {
                    map.setView([routePoints[0].lat, routePoints[0].lng], 10);

                    if (summaryElement) {
                        summaryElement.textContent = 'Only one point has coordinates available. Add at least a start and end point to preview the route.';
                    }

                    return;
                }

                if (summaryElement) {
                    summaryElement.textContent = 'Loading routed road preview...';
                }

                const coordString = routePoints
                    .map(point => `${point.lng},${point.lat}`)
                    .join(';');

                const osrmUrl =
                    `https://router.project-osrm.org/route/v1/driving/${coordString}` +
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

                    if (
                        distanceKmInput &&
                        (
                            distanceKmInput.value === '' ||
                            distanceKmInput.value === null ||
                            Number(distanceKmInput.value) === 0
                        )
                    ) {
                        distanceKmInput.value = distanceKm;
                    }

                    const viaLabel = legPoints.length
                        ? ` via ${legPoints.map(point => point.name).join(', ')}`
                        : '';

                    setSummaryHtml(`
                        <span class="font-medium text-gray-900">${from ? from.name : 'Start'}</span>
                        <span class="text-gray-400"> to </span>
                        <span class="font-medium text-gray-900">${to ? to.name : 'End'}</span>
                        <span class="text-gray-500">${viaLabel} — routed via roads, ${distanceKm} km, about ${durationMinutes} min</span>
                    `);
                } catch (error) {
                    const latLngs = routePoints.map(point => [point.lat, point.lng]);

                    routeLayer = L.polyline(latLngs, {
                        color: '#2563eb',
                        weight: 4,
                        opacity: 0.6,
                        dashArray: '8, 8'
                    }).addTo(map);

                    map.fitBounds(routeLayer.getBounds(), { padding: [30, 30] });

                    const viaLabel = legPoints.length
                        ? ` via ${legPoints.map(point => point.name).join(', ')}`
                        : '';

                    setSummaryHtml(`
                        <span class="font-medium text-gray-900">${from ? from.name : 'Start'}</span>
                        <span class="text-gray-400"> to </span>
                        <span class="font-medium text-gray-900">${to ? to.name : 'End'}</span>
                        <span class="text-gray-500">${viaLabel}</span>
                        <span class="text-amber-600"> — routing unavailable, showing straight-line fallback</span>
                    `);
                }
            }

            [fromPlaceSelect, toPlaceSelect, fromDestinationItemSelect, destinationItemSelect].forEach((select) => {
                if (select) {
                    select.addEventListener('change', refreshMap);
                }
            });

            document.addEventListener('trip-leg:selection-updated', refreshMap);

            refreshMap();

            setTimeout(function () {
                map.invalidateSize();
            }, 150);
        });
    </script>
</x-app-layout>