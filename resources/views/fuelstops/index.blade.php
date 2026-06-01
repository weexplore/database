<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Fuel Stops
        </h2>
    </x-slot>

    @php
        $showCreate = request()->boolean('show_create');
        $newFuelStop = $newFuelStop ?? new \App\Models\FuelStop();
    @endphp

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">
            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <form method="GET" action="{{ route('fuel-stops.index') }}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                        <input
                            type="text"
                            name="search"
                            id="search"
                            value="{{ request('search') }}"
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm"
                            placeholder="Stop, brand, fuel type"
                        >
                    </div>

                    <div>
                        <label for="place_id" class="block text-sm font-medium text-gray-700">Place</label>
                        <select
                            name="place_id"
                            id="place_id"
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm"
                        >
                            <option value="">All places</option>
                            @foreach ($places as $place)
                                <option value="{{ $place->id }}" @selected((string) request('place_id') === (string) $place->id)>
                                    {{ $place->placename }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="destination_id" class="block text-sm font-medium text-gray-700">Destination</label>
                        <select
                            name="destination_id"
                            id="destination_id"
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm"
                            data-selected="{{ request('destination_id') }}"
                        >
                            <option value="">All destinations</option>
                            @foreach ($destinations as $destination)
                                <option
                                    value="{{ $destination->id }}"
                                    data-place-id="{{ $destination->placeid }}"
                                    @selected((string) request('destination_id') === (string) $destination->id)
                                >
                                    {{ $destination->destinationname }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="brand" class="block text-sm font-medium text-gray-700">Brand</label>
                        <input
                            type="text"
                            name="brand"
                            id="brand"
                            value="{{ request('brand') }}"
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm"
                            placeholder="Brand"
                        >
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select
                            name="status"
                            id="status"
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm"
                        >
                            <option value="">All</option>
                            <option value="1" @selected(request('status') === '1')>Active</option>
                            <option value="0" @selected(request('status') === '0')>Inactive</option>
                        </select>
                    </div>

                    <div class="xl:block hidden"></div>

                    <div class="md:col-span-2 xl:col-span-5 flex items-center justify-between pt-2">
                        <div class="space-x-2">
                            <button type="submit" class="px-4 py-2 bg-slate-700 text-white rounded hover:bg-slate-800">
                                Filter
                            </button>
                            <a href="{{ route('fuel-stops.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                                Reset
                            </a>
                        </div>

                        <a href="{{ route('fuel-stops.index', array_merge(request()->query(), ['show_create' => 1])) }}"
                           class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            Add Fuel Stop
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    @if($showCreate)
                        <div class="bg-white shadow-sm sm:rounded-lg p-6 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Add Fuel Stop</h3>

                            <form method="POST" action="{{ route('fuel-stops.store') }}" class="space-y-6" data-dirty-form>
                                @csrf

                                @include('fuelstops._form', [
                                    'fuelStop' => $newFuelStop,
                                    'places' => $places,
                                    'destinations' => $destinations,
                                    'fuelTypes' => $fuelTypes,
                                ])

                                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                                    <a href="{{ route('fuel-stops.index', request()->except('show_create')) }}"
                                       class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                                        Cancel
                                    </a>

                                    <button type="submit"
                                            class="inline-flex items-center px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                        Save Fuel Stop
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Stop</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Place</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Destination</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Brand</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Fuel Types</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Facilities</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Coordinates</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($fuelStops as $fuelStop)
                                <tr>
                                    <td class="px-4 py-3 align-top">
                                        <div class="font-medium text-gray-900">{{ $fuelStop->stopname }}</div>
                                        @if ($fuelStop->caravanaccessnotes)
                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ \Illuminate\Support\Str::limit($fuelStop->caravanaccessnotes, 80) }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 align-top text-gray-700">
                                        {{ $fuelStop->place?->placename ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700">
                                        {{ $fuelStop->destination?->destinationname ?? '—' }}
                                    </td>

                                    <td class="px-4 py-3 align-top text-gray-700">
                                        {{ $fuelStop->brandname ?: '—' }}
                                    </td>

                                    <td class="px-4 py-3 align-top text-gray-700">
                                        @if (count($fuelStop->fuel_types_array))
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($fuelStop->fuel_types_array as $fuelType)
                                                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">
                                                        {{ $fuelTypes[$fuelType] ?? $fuelType }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            —
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 align-top text-gray-700">
                                        <div class="flex flex-wrap gap-1">
                                            @if ($fuelStop->hashighflowdiesel)
                                                <span class="inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">High-flow</span>
                                            @endif
                                            @if ($fuelStop->hasadblue)
                                                <span class="inline-flex rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-800">AdBlue</span>
                                            @endif
                                            @if ($fuelStop->hascarwash)
                                                <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">Car wash</span>
                                            @endif
                                            @if ($fuelStop->hasairwater)
                                                <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">Air / water</span>
                                            @endif
                                            @if (
                                                !$fuelStop->hashighflowdiesel &&
                                                !$fuelStop->hasadblue &&
                                                !$fuelStop->hascarwash &&
                                                !$fuelStop->hasairwater
                                            )
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 align-top">
                                        @if (!is_null($fuelStop->latitude) && !is_null($fuelStop->longitude))
                                            <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">
                                                Added
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">
                                                Missing
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 align-top">
                                        @if ($fuelStop->isactive)
                                            <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Active</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">Inactive</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 align-top text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('fuel-stops.edit', ['fuel_stop' => $fuelStop, 'return_to' => request()->fullUrl()]) }}"
                                               class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50">
                                                Edit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500">
                                        No fuel stops found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($fuelStops->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200">
                        {{ $fuelStops->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($showCreate)
        <link rel="stylesheet"
              href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
              integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
              crossorigin="" />

        <script defer
                src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
                crossorigin=""></script>

        <script>
    function setupFuelStopDestinationFilter() {
        const placeSelect = document.getElementById('placeid');
        const destinationSelect = document.getElementById('destinationid');

        if (!placeSelect || !destinationSelect) {
            return;
        }

        const originalOptions = Array.from(destinationSelect.options).map((option) => ({
            value: option.value,
            text: option.text,
            placeId: option.dataset.placeId || '',
            selected: option.selected,
        }));

        function rebuildDestinationOptions() {
            const selectedPlaceId = placeSelect.value;
            const currentValue = destinationSelect.value || destinationSelect.dataset.selected || '';

            destinationSelect.innerHTML = '';

            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Select destination';
            destinationSelect.appendChild(placeholder);

            originalOptions.forEach((option) => {
                if (option.value === '') {
                    return;
                }

                if (selectedPlaceId !== '' && option.placeId !== selectedPlaceId) {
                    return;
                }

                const el = document.createElement('option');
                el.value = option.value;
                el.textContent = option.text;
                el.dataset.placeId = option.placeId;

                if (String(option.value) === String(currentValue)) {
                    el.selected = true;
                }

                destinationSelect.appendChild(el);
            });

            const stillExists = Array.from(destinationSelect.options).some(
                (option) => option.value === currentValue
            );

            if (!stillExists) {
                destinationSelect.value = '';
                destinationSelect.dataset.selected = '';
            }
        }

        placeSelect.addEventListener('change', function () {
            destinationSelect.dataset.selected = '';
            rebuildDestinationOptions();
        });

        rebuildDestinationOptions();
    }

    window.addEventListener('load', setupFuelStopDestinationFilter);
</script>

        <script>
            window.addEventListener('load', function () {
                const latInput = document.getElementById('latitude');
                const lngInput = document.getElementById('longitude');
                const searchInput = document.getElementById('fuel-map-search');
                const searchButton = document.getElementById('fuel-map-search-button');
                const useMyLocationButton = document.getElementById('fuel-use-my-location');
                const syncFromFieldsButton = document.getElementById('fuel-sync-from-fields');
                const mapStatus = document.getElementById('fuel-map-status');
                const googleMapsLink = document.getElementById('fuel-open-in-google-maps');
                const mapElement = document.getElementById('fuel-stop-map');

                const placeSelect = document.getElementById('placeid');
                const address1Input = document.getElementById('addressline1');
                const address3Input = document.getElementById('addressline3');

                if (!latInput || !lngInput || !mapElement) {
                    return;
                }

                if (typeof window.L === 'undefined') {
                    if (mapStatus) {
                        mapStatus.textContent = 'Leaflet map library did not load.';
                    }
                    return;
                }

                let searchTouchedManually = false;

                function coordsAreBlank() {
                    return latInput.value.trim() === '' && lngInput.value.trim() === '';
                }

                function buildPreferredSearchText() {
                    const parts = [];

                    const addr1 = address1Input ? address1Input.value.trim() : '';
                    const addr3 = address3Input ? address3Input.value.trim() : '';

                    if (addr1 !== '') parts.push(addr1);
                    if (addr3 !== '') parts.push(addr3);

                    if (parts.length > 0) {
                        return parts.join(', ');
                    }

                    if (placeSelect && placeSelect.value) {
                        const opt = placeSelect.options[placeSelect.selectedIndex];
                        if (opt) {
                            const placeName = opt.text.trim();
                            if (placeName !== '') {
                                return placeName;
                            }
                        }
                    }

                    return '';
                }

                function shouldAutoFillSearch() {
                    return searchInput && coordsAreBlank();
                }

                function syncSearchFromContext(force = false) {
                    if (!searchInput) return;
                    if (!shouldAutoFillSearch()) return;

                    const preferred = buildPreferredSearchText();
                    const current = searchInput.value.trim();

                    if (preferred === '') return;

                    if (force || !searchTouchedManually || current === '' || current === preferred) {
                        searchInput.value = preferred;
                    }
                }

                if (searchInput) {
                    searchInput.addEventListener('input', function () {
                        const preferred = buildPreferredSearchText();
                        const current = searchInput.value.trim();
                        searchTouchedManually = current !== '' && current !== preferred;
                    });
                }

                if (placeSelect) {
                    placeSelect.addEventListener('change', function () {
                        searchTouchedManually = false;
                        syncSearchFromContext();
                    });
                }

                if (address1Input) {
                    address1Input.addEventListener('input', function () {
                        syncSearchFromContext();
                    });
                }

                if (address3Input) {
                    address3Input.addEventListener('input', function () {
                        syncSearchFromContext();
                    });
                }

                syncSearchFromContext(true);

                const hasCoords = latInput.value !== '' && lngInput.value !== '';
                const defaultLat = parseFloat(latInput.value || '-37.8136');
                const defaultLng = parseFloat(lngInput.value || '144.9631');
                const defaultZoom = hasCoords ? 15 : 6;

                const map = L.map('fuel-stop-map').setView([defaultLat, defaultLng], defaultZoom);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                const marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

                function setStatus(message) {
                    if (mapStatus) mapStatus.textContent = message;
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

                    if (zoom !== null) {
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
                    const query = searchInput?.value.trim();

                    if (!query) {
                        setStatus('Enter a place or address to search.');
                        return;
                    }

                    setStatus('Searching map...');

                    try {
                        const url = `https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=${encodeURIComponent(query)}`;
                        const response = await fetch(url, {
                            headers: { Accept: 'application/json' }
                        });

                        if (!response.ok) {
                            throw new Error(`Search request failed with status ${response.status}`);
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
                        setStatus(`Unable to search location right now. ${error.message}`);
                    }
                });

                searchInput?.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        searchButton?.click();
                    }
                });

                useMyLocationButton?.addEventListener('click', function () {
                    if (!navigator.geolocation) {
                        setStatus('Geolocation is not supported in this browser.');
                        return;
                    }

                    setStatus('Finding your location...');

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
                            timeout: 10000,
                        }
                    );
                });

                updateGoogleMapsLink(defaultLat, defaultLng);
                setStatus(hasCoords ? 'Loaded saved coordinates. Click the map or search to set coordinates.' : '');

                requestAnimationFrame(() => map.invalidateSize());
                setTimeout(() => map.invalidateSize(), 300);
            });
        </script>

        <script>
            (() => {
                const forms = document.querySelectorAll('[data-dirty-form]');

                forms.forEach((form) => {
                    let isDirty = false;

                    form.querySelectorAll('input, select, textarea').forEach((field) => {
                        field.addEventListener('change', () => isDirty = true);
                        field.addEventListener('input', () => isDirty = true);
                    });

                    form.addEventListener('submit', () => isDirty = false);

                    window.addEventListener('beforeunload', (event) => {
                        if (!isDirty) return;
                        event.preventDefault();
                        event.returnValue = '';
                    });
                });
            })();
        </script>
    @endif
</x-app-layout>