<x-app-layout>
    @php
        $returnTo = request('return_to', route('fuel-stops.index', request()->only([
            'search',
            'place_id',
            'brand',
            'status',
            'page',
        ])));
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Fuel Stop
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">
            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <div class="xl:col-span-2">
                    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                        <form
                            method="POST"
                            action="{{ route('fuel-stops.update', $fuelStop) }}"
                            class="p-6 space-y-6"
                            data-dirty-form
                        >
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="return_to" value="{{ $returnTo }}">

                            @include('fuelstops._form', [
                                'fuelStop' => $fuelStop,
                                'places' => $places,
                                'destinations' => $destinations,
                                'fuelTypes' => $fuelTypes,
                            ])

                            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                <div></div>

                                <div class="flex items-center gap-2">
                                    <a
                                        href="{{ $returnTo }}"
                                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50"
                                    >
                                        Cancel
                                    </a>

                                    <button
                                        type="submit"
                                        name="save_action"
                                        value="stay"
                                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50"
                                    >
                                        Save
                                    </button>

                                    <button
                                        type="submit"
                                        name="save_action"
                                        value="index"
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                                    >
                                        Save & Return
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="mt-6 bg-white shadow-sm rounded-lg border border-red-200">
                        <div class="p-6 flex items-center justify-between gap-4">
                            <div>
                                <h3 class="text-sm font-semibold text-red-700">Delete Fuel Stop</h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    This action will permanently remove this fuel stop.
                                </p>
                            </div>

                            <form
                                method="POST"
                                action="{{ route('fuel-stops.destroy', $fuelStop) }}"
                                onsubmit="return confirm('Delete this fuel stop?');"
                            >
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="return_to" value="{{ $returnTo }}">
                                <button
                                    type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700"
                                >
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-1 space-y-6">
                    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                        <div class="px-4 py-3 border-b border-gray-200 flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">Fuel Price Observations</h3>
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $fuelStop->fuelPriceObservations->count() }}
                                    linked record{{ $fuelStop->fuelPriceObservations->count() === 1 ? '' : 's' }}
                                </p>
                            </div>

                            <a
                                href="{{ route('fuel-price-observations.create', [
                                    'fuel_stop_id' => $fuelStop->id,
                                    'return_to' => url()->full(),
                                ]) }}"
                                class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md text-xs font-semibold text-white uppercase tracking-widest hover:bg-indigo-700"
                            >
                                Add Observation
                            </a>
                        </div>

                        <div class="p-4">
                            @if ($fuelStop->fuelPriceObservations->isNotEmpty())
                                <ul class="space-y-3 text-sm">
                                    @foreach ($fuelStop->fuelPriceObservations->sortByDesc('observedon') as $observation)
                                        <li>
                                            <a
                                                href="{{ route('fuel-price-observations.edit', [
                                                    'fuel_price_observation' => $observation,
                                                    'return_to' => url()->full(),
                                                ]) }}"
                                                class="block rounded-md border border-gray-200 px-3 py-3 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            >
                                                <div class="flex items-start justify-between gap-3">
                                                    <div>
                                                        <div class="font-medium text-gray-900">
                                                            {{ $observation->fuel_type_label ?? $observation->fueltype ?? 'Fuel observation' }}
                                                        </div>

                                                        <div class="mt-1 text-xs text-gray-500">
                                                            Observed:
                                                            {{ $observation->observedon ? \Illuminate\Support\Carbon::parse($observation->observedon)->format('d M Y') : '—' }}
                                                        </div>

                                                        <div class="mt-1 text-xs text-gray-500">
                                                            Source:
                                                            {{ $observation->price_source_label ?? $observation->pricesource ?? '—' }}
                                                        </div>

                                                        @if ($observation->observationnotes)
                                                            <div class="mt-1 text-xs text-gray-500">
                                                                {{ \Illuminate\Support\Str::limit($observation->observationnotes, 90) }}
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div class="shrink-0 text-right">
                                                        @if (!is_null($observation->priceperlitre))
                                                            <div class="text-sm font-semibold text-gray-900">
                                                                ${{ number_format((float) $observation->priceperlitre, 4) }}
                                                            </div>
                                                            <div class="text-xs text-gray-500">per litre</div>
                                                        @else
                                                            <div class="text-sm text-gray-400">—</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="rounded-md border border-dashed border-gray-300 bg-gray-50 px-4 py-6 text-sm text-gray-500">
                                    No fuel price observations are currently linked to this stop.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900">Trip Fuel Purchases</h3>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ $fuelStop->tripFuelPurchases->count() }}
                                linked record{{ $fuelStop->tripFuelPurchases->count() === 1 ? '' : 's' }}
                            </p>
                        </div>

                        <div class="p-4">
                            @if ($fuelStop->tripFuelPurchases->isNotEmpty())
                                <ul class="space-y-3 text-sm">
                                    @foreach ($fuelStop->tripFuelPurchases as $purchase)
                                        <li>
                                            <a
                                                href="{{ route('trips.fuel-purchases.edit', [
                                                    'trip' => $purchase->tripid,
                                                    'fuel_purchase' => $purchase,
                                                    'return_to' => url()->full(),
                                                ]) }}"
                                                class="block border border-gray-200 rounded-md px-3 py-2 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            >
                                                <div class="font-medium text-gray-900">
                                                    {{ $purchase->trip?->tripname ?: 'Trip fuel purchase' }}
                                                </div>
                                                <div class="mt-1 text-xs text-gray-500">
                                                    {{ $purchase->fueltype ?: 'Fuel' }}
                                                    @if (!is_null($purchase->litres))
                                                        • {{ number_format((float) $purchase->litres, 3) }} L
                                                    @endif
                                                </div>
                                                <div class="mt-1 text-xs text-gray-500">
                                                    {{ $purchase->purchasedate ? \Illuminate\Support\Carbon::parse($purchase->purchasedate)->format('d M Y') : '—' }}
                                                    @if (!is_null($purchase->fueltotal))
                                                        • ${{ number_format((float) $purchase->fueltotal, 2) }}
                                                    @endif
                                                </div>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-gray-500">
                                    No trip fuel purchases are currently linked to this stop.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet"
          href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin="" />

    <script defer
            src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>

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
            const form = document.querySelector('[data-dirty-form]');
            if (!form) return;

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
        })();
    </script>
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
</x-app-layout>