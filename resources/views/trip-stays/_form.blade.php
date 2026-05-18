@php
    $selectedTripLegId = old('triplegid', old('tripleg_id', $tripStay->triplegid ?? $selectedTripLegId ?? ''));
    $selectedPlaceId = old('placeid', old('place_id', $tripStay->placeid ?? $selectedPlaceId ?? ''));
    $selectedTravelledFromPlaceId = old('travelledfromplaceid', old('travelledfromplace_id', $tripStay->travelledfromplaceid ?? $selectedTravelledFromPlaceId ?? ''));
    $isCreate = $isCreate ?? false;
    $returnTo = $returnTo ?? route('trips.stays.index', $trip);
@endphp

<div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-medium text-gray-900">
                {{ $isCreate ? 'Add Trip Stay' : 'Stay Details' }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Record planned or actual accommodation, including location, dates, travel origin, and stay costs.
            </p>
        </div>

        @if($isCreate)
            <a href="{{ $returnTo }}"
               class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                Close
            </a>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <div>
            <label for="stayname" class="block text-sm font-medium text-gray-700 mb-1">Stay Name</label>
            <input type="text"
                   name="stayname"
                   id="stayname"
                   value="{{ old('stayname', $tripStay->stayname ?? '') }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="staytype" class="block text-sm font-medium text-gray-700 mb-1">Stay Type</label>
            <select name="staytype" id="staytype" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">Select stay type</option>
                @foreach($stayTypes as $value => $label)
                    <option value="{{ $value }}" @selected((string) old('staytype', $tripStay->staytype ?? '') === (string) $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="triplegid" class="block text-sm font-medium text-gray-700 mb-1">Trip Leg</label>
            <select id="triplegid" name="triplegid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">None</option>
                @foreach($tripLegs as $tripLeg)
                    <option value="{{ $tripLeg->id }}"
                            data-from-place-id="{{ $tripLeg->fromplaceid ?? '' }}"
                            data-to-place-id="{{ $tripLeg->toplaceid ?? '' }}"
                            data-distance-km="{{ $tripLeg->distancekm ?? '' }}"
                            @selected((string) old('triplegid', $tripStay->triplegid ?? '') === (string) $tripLeg->id)>
                        {{ $tripLeg->fromPlace?->placename ?? 'Unknown start' }}
                        -
                        {{ $tripLeg->toPlace?->placename ?? 'Unknown end' }}
                        @if($tripLeg->startdate)
                            - {{ $tripLeg->startdate->format('d/m/Y') }}
                        @endif
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="border-t border-gray-200 pt-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="placeid" class="block text-sm font-medium text-gray-700 mb-1">Place</label>
                <select name="placeid" id="placeid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">Select place</option>
                    @foreach($places as $place)
                        <option value="{{ $place->id }}" @selected((string) $selectedPlaceId === (string) $place->id)>
                            {{ $place->placename }}
                        </option>
                    @endforeach
                </select>

                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <button type="button"
                            id="trip-stay-use-place-button"
                            class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs">
                        Use place details
                    </button>

                    <button type="button"
                            id="trip-stay-use-previous-button"
                            class="inline-flex items-center px-3 py-1.5 bg-gray-700 text-white rounded hover:bg-gray-800 text-xs">
                        Use last stay at this place
                    </button>

                    <span id="trip-stay-prefill-status" class="text-xs text-gray-500"></span>
                </div>

                <div id="trip-stay-place-summary"
                     class="mt-3 hidden rounded-md border border-gray-200 bg-gray-50 p-3 text-xs text-gray-700">
                </div>
            </div>

            <div>
                <label for="destinationitemid" class="block text-sm font-medium text-gray-700 mb-1">
                    Destination Item
                </label>
                <select name="destinationitemid"
                        id="destinationitemid"
                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">None</option>
                    @foreach ($destinationItems as $destinationItem)
                        <option value="{{ $destinationItem->id }}"
                            @selected((string) old('destinationitemid', $tripStay->destinationitemid ?? '') === (string) $destinationItem->id)>
                            {{ $destinationItem->itemname }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">
                    Optional destination item linked to this stay location.
                </p>
            </div>

            <div>
                <label for="travelledfromplaceid" class="block text-sm font-medium text-gray-700 mb-1">Travelled From Place</label>
                <select name="travelledfromplaceid" id="travelledfromplaceid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">Select origin place</option>
                    @foreach($places as $place)
                        <option value="{{ $place->id }}" @selected((string) $selectedTravelledFromPlaceId === (string) $place->id)>
                            {{ $place->placename }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="distancetravelledkm" class="block text-sm font-medium text-gray-700 mb-1">Distance Travelled (km)</label>
                <input type="number"
                       step="0.1"
                       min="0"
                       name="distancetravelledkm"
                       id="distancetravelledkm"
                       value="{{ old('distancetravelledkm', $tripStay->distancetravelledkm ?? '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
        </div>
    </div>

    <div class="border-t border-gray-200 pt-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="checkindate" class="block text-sm font-medium text-gray-700 mb-1">Check-in Date</label>
                <input type="date"
                       name="checkindate"
                       id="checkindate"
                       value="{{ old('checkindate', isset($tripStay?->checkindate) ? \Illuminate\Support\Carbon::parse($tripStay->checkindate)->format('Y-m-d') : '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>

            <div>
                <label for="checkoutdate" class="block text-sm font-medium text-gray-700 mb-1">Check-out Date</label>
                <input type="date"
                       name="checkoutdate"
                       id="checkoutdate"
                       value="{{ old('checkoutdate', isset($tripStay?->checkoutdate) ? \Illuminate\Support\Carbon::parse($tripStay->checkoutdate)->format('Y-m-d') : '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>

            <div>
                <label for="nights" class="block text-sm font-medium text-gray-700 mb-1">Nights</label>
                <input type="number"
                       min="0"
                       name="nights"
                       id="nights"
                       value="{{ old('nights', $tripStay->nights ?? '') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
        </div>
    </div>
</div>

<div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
    <div>
        <h3 class="text-lg font-medium text-gray-900">Accommodation Costs</h3>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        <div class="flex items-center gap-3 pt-7">
            <input type="hidden" name="isaccommodationpaid" value="0">
            <input type="checkbox"
                   name="isaccommodationpaid"
                   id="isaccommodationpaid"
                   value="1"
                   @checked((bool) old('isaccommodationpaid', $tripStay->isaccommodationpaid ?? false))
                   class="rounded border-gray-300 text-green-600 shadow-sm">
            <label for="isaccommodationpaid" class="text-sm font-medium text-gray-700">
                Accommodation Paid
            </label>
        </div>

        <div>
            <label for="costpernight" class="block text-sm font-medium text-gray-700 mb-1">Cost Per Night</label>
            <input type="number"
                   step="0.01"
                   min="0"
                   name="costpernight"
                   id="costpernight"
                   value="{{ old('costpernight', $tripStay->costpernight ?? '') }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="estimatedtotalcost" class="block text-sm font-medium text-gray-700 mb-1">Estimated Total Cost</label>
            <input type="number"
                   step="0.01"
                   min="0"
                   name="estimatedtotalcost"
                   id="estimatedtotalcost"
                   value="{{ old('estimatedtotalcost', $tripStay->estimatedtotalcost ?? '') }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="actualtotalcost" class="block text-sm font-medium text-gray-700 mb-1">Actual Total Cost</label>
            <input type="number"
                   step="0.01"
                   min="0"
                   name="actualtotalcost"
                   id="actualtotalcost"
                   value="{{ old('actualtotalcost', $tripStay->actualtotalcost ?? '') }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
    </div>
</div>

<div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
    <div>
        <h3 class="text-lg font-medium text-gray-900">Notes and Review</h3>
    </div>

    <div class="space-y-4">
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description"
                      id="description"
                      rows="4"
                      class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('description', $tripStay->description ?? '') }}</textarea>
        </div>

        <div class="flex items-center gap-3">
            <input type="hidden" name="woulduseagain" value="0">
            <input type="checkbox"
                   name="woulduseagain"
                   id="woulduseagain"
                   value="1"
                   @checked((bool) old('woulduseagain', $tripStay->woulduseagain ?? false))
                   class="rounded border-gray-300 text-green-600 shadow-sm">
            <label for="woulduseagain" class="text-sm font-medium text-gray-700">
                Would Use Again
            </label>
        </div>

        <div>
            <label for="reviewnotes" class="block text-sm font-medium text-gray-700 mb-1">Review Notes</label>
            <textarea name="reviewnotes"
                      id="reviewnotes"
                      rows="5"
                      class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('reviewnotes', $tripStay->reviewnotes ?? '') }}</textarea>
        </div>
    </div>
</div>

<div class="flex items-center justify-end gap-3">
    <a href="{{ $returnTo }}"
       class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
        Cancel
    </a>

    <button type="submit"
            class="inline-flex items-center px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700">
        Save Trip Stay
    </button>
</div>

{{-- Helper script (can live in index/edit pages or here) --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('trip-stay-create-form') || document.getElementById('trip-stay-edit-form');
    if (!form) return;

    const tripLegSelect = document.getElementById('triplegid');
    const placeSelect = document.getElementById('placeid');
    const destinationItemSelect = document.getElementById('destinationitemid');
    const travelledFromPlaceSelect = document.getElementById('travelledfromplaceid');
    const distanceTravelledInput = document.getElementById('distancetravelledkm');

    const usePlaceButton = document.getElementById('trip-stay-use-place-button');
    const usePreviousButton = document.getElementById('trip-stay-use-previous-button');
    const status = document.getElementById('trip-stay-prefill-status');
    const placeSummary = document.getElementById('trip-stay-place-summary');

    const checkinInput = document.getElementById('checkindate');
    const checkoutInput = document.getElementById('checkoutdate');
    const nightsInput = document.getElementById('nights');
    const costPerNightInput = document.getElementById('costpernight');
    const estimatedTotalCostInput = document.getElementById('estimatedtotalcost');

    function selectedPlaceId() {
        return placeSelect && placeSelect.value ? placeSelect.value : null;
    }

    function showStatus(message, isError = false) {
        if (!status) return;
        status.textContent = message || '';
        status.className = isError ? 'text-xs text-red-600' : 'text-xs text-gray-500';
    }

    function setValue(id, value, overwriteOnlyIfBlank = true) {
        const el = document.getElementById(id);
        if (!el) return;

        if (el.type === 'checkbox') {
            if (!overwriteOnlyIfBlank || el.checked === false) {
                el.checked = !!value;
            }
            return;
        }

        if (!overwriteOnlyIfBlank || !el.value) {
            el.value = value ?? '';
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    function setSelectValue(select, value, overwriteOnlyIfBlank = true) {
        if (!select) return;

        if (!overwriteOnlyIfBlank || !select.value) {
            select.value = value ?? '';
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    function parseLocalDate(value) {
        if (!value) return null;

        const parts = value.split('-');
        if (parts.length !== 3) return null;

        const year = Number(parts[0]);
        const month = Number(parts[1]) - 1;
        const day = Number(parts[2]);

        const date = new Date(year, month, day);
        return Number.isNaN(date.getTime()) ? null : date;
    }

    function calculateNights() {
        if (!checkinInput || !checkoutInput || !nightsInput) return;

        const checkin = parseLocalDate(checkinInput.value);
        const checkout = parseLocalDate(checkoutInput.value);

        if (!checkin || !checkout) return;

        const millisecondsPerDay = 1000 * 60 * 60 * 24;
        const diff = Math.round((checkout - checkin) / millisecondsPerDay);

        if (diff >= 0) {
            nightsInput.value = diff;
            nightsInput.dispatchEvent(new Event('input', { bubbles: true }));
            nightsInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    function calculateEstimatedTotal() {
        if (!nightsInput || !costPerNightInput || !estimatedTotalCostInput) return;

        const nights = parseFloat(nightsInput.value);
        const costPerNight = parseFloat(costPerNightInput.value);

        if (Number.isFinite(nights) && Number.isFinite(costPerNight)) {
            estimatedTotalCostInput.value = (nights * costPerNight).toFixed(2);
        }
    }

    function applyTripLegSelection(overwriteOnlyIfBlank = true) {
        if (!tripLegSelect) return;

        const selectedOption = tripLegSelect.options[tripLegSelect.selectedIndex];
        if (!selectedOption || !selectedOption.value) return;

        const fromPlaceId = selectedOption.dataset.fromPlaceId || '';
        const toPlaceId = selectedOption.dataset.toPlaceId || '';
        const distanceKm = selectedOption.dataset.distanceKm || '';

        setSelectValue(travelledFromPlaceSelect, fromPlaceId, overwriteOnlyIfBlank);
        setSelectValue(placeSelect, toPlaceId, overwriteOnlyIfBlank);
        setValue('distancetravelledkm', distanceKm, overwriteOnlyIfBlank);
    }

    tripLegSelect?.addEventListener('change', function () {
        applyTripLegSelection(false);
    });

    checkinInput?.addEventListener('change', function () {
        calculateNights();
        calculateEstimatedTotal();
    });

    checkoutInput?.addEventListener('change', function () {
        calculateNights();
        calculateEstimatedTotal();
    });

    nightsInput?.addEventListener('input', calculateEstimatedTotal);
    costPerNightInput?.addEventListener('input', calculateEstimatedTotal);

    calculateNights();
    calculateEstimatedTotal();
    applyTripLegSelection(true);

    if (usePlaceButton) {
        usePlaceButton.addEventListener('click', function () {
            const placeid = selectedPlaceId();
            if (!placeid) {
                showStatus('Select a place first.', true);
                return;
            }

            usePlaceButton.disabled = true;
            showStatus('Loading place details...');

            fetch("{{ route('trips.stays.prefill-from-place', $trip) }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ placeid }),
            })
            .then(response => {
                if (!response.ok) throw new Error('Failed');
                return response.json();
            })
            .then(data => {
                if (data.fields) {
                    setValue('stayname', data.fields.stayname, true);
                    setValue('description', data.fields.description, true);
                }

                if (data.place && placeSummary) {
                    placeSummary.classList.remove('hidden');
                    placeSummary.innerHTML = `
                        <div class="font-semibold text-gray-900">${data.place.placename || 'Place'}</div>
                        <div class="mt-1 text-gray-600">
                            ${data.place.placetype || '—'}${data.place.locality ? ' • ' + data.place.locality : ''}
                        </div>
                        ${data.place.accessnotes ? `<div class="mt-2"><span class="font-medium text-gray-800">Access:</span> ${data.place.accessnotes}</div>` : ''}
                        ${data.place.generalnotes ? `<div class="mt-1"><span class="font-medium text-gray-800">Notes:</span> ${data.place.generalnotes}</div>` : ''}
                    `;
                }

                showStatus('Place details applied where fields were blank.');
            })
            .catch(() => {
                showStatus('Could not load place details.', true);
            })
            .finally(() => {
                usePlaceButton.disabled = false;
            });
        });
    }

    if (usePreviousButton) {
        usePreviousButton.addEventListener('click', function () {
            const placeid = selectedPlaceId();
            if (!placeid) {
                showStatus('Select a place first.', true);
                return;
            }

            usePreviousButton.disabled = true;
            showStatus('Looking for previous stay...');

            fetch("{{ route('trips.stays.prefill-from-previous-stay', $trip) }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ placeid }),
            })
            .then(response => {
                if (!response.ok) throw new Error('Failed');
                return response.json();
            })
            .then(data => {
                if (!data.found) {
                    showStatus(data.message || 'No previous stay found.', true);
                    return;
                }

                if (data.fields) {
                    setValue('stayname', data.fields.stayname, false);
                    setValue('staytype', data.fields.staytype, false);
                    setValue('costpernight', data.fields.costpernight, false);
                    setValue('estimatedtotalcost', data.fields.estimatedtotalcost, false);
                    setValue('travelledfromplaceid', data.fields.travelledfromplaceid, false);
                    setValue('distancetravelledkm', data.fields.distancetravelledkm, false);
                    setValue('description', data.fields.description, false);

                    const paidCheckbox = document.getElementById('isaccommodationpaid');
                    if (paidCheckbox && typeof data.fields.isaccommodationpaid !== 'undefined') {
                        paidCheckbox.checked = !!data.fields.isaccommodationpaid;
                    }
                }

                calculateNights();
                calculateEstimatedTotal();
                showStatus('Previous stay values copied.');
            })
            .catch(() => {
                showStatus('Could not load previous stay.', true);
            })
            .finally(() => {
                usePreviousButton.disabled = false;
            });
        });
    }
});
</script>