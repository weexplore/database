@php
    $isCreate = $isCreate ?? false;
    $returnTo = $returnTo ?? route('fuel-purchases.index');
    $fuelPurchase = $fuelPurchase ?? null;
    $fixedTrip = $fixedTrip ?? null;

    $selectedTripId = old(
        'tripid',
        $fixedTrip?->id
            ?? $selectedTripId
            ?? $fuelPurchase?->tripid
    );
@endphp

<div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
    <div>
        <h3 class="text-lg font-medium text-gray-900">
            Core Details
        </h3>

        <p class="mt-1 text-sm text-gray-500">
            Record the purchase and optionally link it to a trip and itinerary leg.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        <div>
            <label for="purchasedate" class="block text-sm font-medium text-gray-700 mb-1">
                Purchase Date
            </label>

            <input type="date"
                   name="purchasedate"
                   id="purchasedate"
                   value="{{ old('purchasedate', optional($fuelPurchase?->purchasedate)->format('Y-m-d') ?: now()->format('Y-m-d')) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                   required>
        </div>

        <div>
            <label for="fueltype" class="block text-sm font-medium text-gray-700 mb-1">
                Fuel Type
            </label>

            <select name="fueltype"
                    id="fueltype"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                    required>
                <option value="">Select fuel type</option>

                @foreach($fuelTypes as $value => $label)
                    <option value="{{ $value }}"
                        @selected(old('fueltype', $fuelPurchase?->fueltype) === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="receiptreference" class="block text-sm font-medium text-gray-700 mb-1">
                Receipt Reference
            </label>

            <input type="text"
                   name="receiptreference"
                   id="receiptreference"
                   value="{{ old('receiptreference', $fuelPurchase?->receiptreference) }}"
                   maxlength="150"
                   placeholder="Receipt number, card reference, etc."
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div class="flex items-end">
            @if(!$isCreate && $fuelPurchase)
                <div class="w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-600">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-400">
                        Record ID
                    </div>

                    <div class="mt-1">
                        #{{ $fuelPurchase->id }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <div>
            <label for="tripid" class="block text-sm font-medium text-gray-700 mb-1">
                Trip
            </label>

            @if($fixedTrip)
                <input type="hidden"
                       name="tripid"
                       id="tripid"
                       value="{{ $fixedTrip->id }}">

                <div class="w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                    {{ $fixedTrip->tripname }}
                </div>

                <p class="mt-1 text-xs text-gray-500">
                    This purchase is being recorded within the selected trip.
                </p>
            @else
                <select name="tripid"
                        id="tripid"
                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">Unassigned — allocate later</option>

                    @foreach($trips as $trip)
                        <option value="{{ $trip->id }}"
                            @selected((string) $selectedTripId === (string) $trip->id)>
                            {{ $trip->tripname }}
                            @if($trip->startdate)
                                – {{ $trip->startdate->format('d M Y') }}
                            @endif
                            @if($trip->tripstatus)
                                ({{ ucfirst($trip->tripstatus) }})
                            @endif
                        </option>
                    @endforeach
                </select>

                <p class="mt-1 text-xs text-gray-500">
                    Leave blank to save this purchase in the unassigned fuel receipt register.
                </p>
            @endif
        </div>

        <div>
            <label for="triplegid" class="block text-sm font-medium text-gray-700 mb-1">
                Trip Leg
            </label>

            <select name="triplegid"
                id="triplegid"
                data-selected-trip-id="{{ $selectedTripId ?? '' }}"
                class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                disabled>
                <option value=""
                        id="triplegid-placeholder"
                        data-no-trip-label="Select a trip first"
                        data-no-legs-label="No legs available for this trip"
                        data-optional-label="No leg link">
                    Select a trip first
                </option>

                @foreach($tripLegs as $leg)
                    @php
                        $legLabel =
                            $leg->title
                            ?: trim(
                                collect([
                                    optional($leg->fromPlace)->placename,
                                    optional($leg->toPlace)->placename,
                                ])->filter()->implode(' → ')
                            )
                            ?: (
                                $leg->legnumber
                                    ? 'Leg ' . $leg->legnumber
                                    : 'Leg #' . $leg->id
                            );

                        $selectedLegId = old(
                            'triplegid',
                            $fuelPurchase?->triplegid
                        );
                    @endphp

                    <option value="{{ $leg->id }}"
                            data-trip-id="{{ $leg->tripid }}"
                            hidden
                            disabled
                            @selected((string) $selectedLegId === (string) $leg->id)>
                        {{ $legLabel }}
                    </option>
                @endforeach
            </select>

            <p class="mt-1 text-xs text-gray-500">
                Optional. Only legs belonging to the selected trip are available.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-end gap-2">
            <div class="flex-1 min-w-0">
                <label for="fuelstopid" class="block text-sm font-medium text-gray-700 mb-1">
                    Fuel Stop
                </label>

                <select name="fuelstopid"
                        id="fuelstopid"
                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">Select fuel stop</option>

                    @foreach($fuelStops as $fuelStop)
                        <option value="{{ $fuelStop->id }}"
                            @selected((string) old('fuelstopid', $fuelPurchase?->fuelstopid) === (string) $fuelStop->id)>
                            {{ $fuelStop->stopname }}
                            @if($fuelStop->place)
                                – {{ $fuelStop->place->placename }}
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <a href="{{ route('fuel-stops.create', ['return_to' => url()->full()]) }}"
               class="inline-flex shrink-0 items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Add Fuel Stop
            </a>
        </div>
    </div>

    <div>
        <label for="placeid" class="block text-sm font-medium text-gray-700 mb-1">
            Place
        </label>

        <select name="placeid"
                id="placeid"
                class="w-full rounded-md border-gray-300 shadow-sm text-sm">
            <option value="">No fallback place</option>

            @foreach($places as $place)
                <option value="{{ $place->id }}"
                    @selected((string) old('placeid', $fuelPurchase?->placeid) === (string) $place->id)>
                    {{ $place->placename }}
                </option>
            @endforeach
        </select>

        <p class="mt-1 text-xs text-gray-500">
            Use this if a reusable fuel stop has not been created. A selected fuel stop takes precedence in reports.
        </p>
    </div>
</div>

<div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
    <div>
        <h3 class="text-lg font-medium text-gray-900">
            Fuel and Costs
        </h3>

        <p class="mt-1 text-sm text-gray-500">
            Fuel total is calculated from litres multiplied by price per litre.
        </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-6">
        <div>
            <label for="litres" class="block text-sm font-medium text-gray-700 mb-1">
                Litres
            </label>

            <input type="number"
                   name="litres"
                   id="litres"
                   step="0.001"
                   min="0.001"
                   value="{{ old('litres', $fuelPurchase?->litres) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                   required>
        </div>

        <div>
            <label for="priceperlitre" class="block text-sm font-medium text-gray-700 mb-1">
                Price per Litre
            </label>

            <input type="number"
                   name="priceperlitre"
                   id="priceperlitre"
                   step="0.0001"
                   min="0"
                   value="{{ old('priceperlitre', $fuelPurchase?->priceperlitre) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                   required>
        </div>

        <div>
            <label for="fueltotal" class="block text-sm font-medium text-gray-700 mb-1">
                Fuel Total
            </label>

            <input type="number"
                   name="fueltotal"
                   id="fueltotal"
                   step="0.01"
                   min="0"
                   value="{{ old('fueltotal', $fuelPurchase?->fueltotal) }}"
                   class="w-full rounded-md border-gray-300 bg-gray-50 shadow-sm text-sm"
                   readonly>

            <p class="mt-1 text-xs text-gray-500">
                Calculated automatically.
            </p>
        </div>

        <div>
            <label for="servicecosts" class="block text-sm font-medium text-gray-700 mb-1">
                Service Costs
            </label>

            <input type="number"
                   name="servicecosts"
                   id="servicecosts"
                   step="0.01"
                   min="0"
                   value="{{ old('servicecosts', $fuelPurchase?->servicecosts) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="repairscost" class="block text-sm font-medium text-gray-700 mb-1">
                Repairs Cost
            </label>

            <input type="number"
                   name="repairscost"
                   id="repairscost"
                   step="0.01"
                   min="0"
                   value="{{ old('repairscost', $fuelPurchase?->repairscost) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
    </div>
</div>

<div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
    <div>
        <h3 class="text-lg font-medium text-gray-900">
            Distance and Notes
        </h3>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <div>
            <label for="odometerkm" class="block text-sm font-medium text-gray-700 mb-1">
                Odometer (km)
            </label>

            <input type="number"
                   name="odometerkm"
                   id="odometerkm"
                   step="0.1"
                   min="0"
                   value="{{ old('odometerkm', $fuelPurchase?->odometerkm) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="distancesincelastfillkm" class="block text-sm font-medium text-gray-700 mb-1">
                Distance Since Last Fill (km)
            </label>

            <input type="number"
                   name="distancesincelastfillkm"
                   id="distancesincelastfillkm"
                   step="0.1"
                   min="0"
                   value="{{ old('distancesincelastfillkm', $fuelPurchase?->distancesincelastfillkm) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
    </div>

    <div>
        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
            Notes
        </label>

        <textarea name="notes"
                  id="notes"
                  rows="4"
                  class="js-auto-resize-textarea w-full min-h-[120px] overflow-hidden rounded-md border-gray-300 shadow-sm text-sm">{{ old('notes', $fuelPurchase?->notes) }}</textarea>
    </div>
</div>

<div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
    <a href="{{ $returnTo }}"
       class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
        Cancel
    </a>

    @if(!$isCreate)
        <button type="submit"
                name="save_action"
                value="stay"
                class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded text-sm font-medium text-gray-700 hover:bg-gray-50">
            Save
        </button>
    @endif

    <button type="submit"
            name="save_action"
            value="index"
            class="inline-flex items-center justify-center px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
        {{ $isCreate ? 'Save Purchase' : 'Save & Return' }}
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('fuel-purchase-create-form')
        || document.getElementById('fuel-purchase-edit-form');

    if (!form) {
        return;
    }

    const tripSelect = form.querySelector('#tripid');
    const legSelect = form.querySelector('#triplegid');
    const litresInput = form.querySelector('#litres');
    const priceInput = form.querySelector('#priceperlitre');
    const totalInput = form.querySelector('#fueltotal');
    const textareas = form.querySelectorAll('.js-auto-resize-textarea');

    let isDirty = false;
    let isSubmitting = false;

    form.querySelectorAll('input, select, textarea').forEach((field) => {
        field.addEventListener('change', function () {
            isDirty = true;
        });

        field.addEventListener('input', function () {
            isDirty = true;
        });
    });

    form.addEventListener('submit', function () {
        isSubmitting = true;
        isDirty = false;
    });

    window.addEventListener('beforeunload', function (event) {
        if (!isDirty || isSubmitting) {
            return;
        }

        event.preventDefault();
        event.returnValue = '';
    });

    const recalculateFuelTotal = function () {
        const litres = parseFloat(litresInput?.value);
        const pricePerLitre = parseFloat(priceInput?.value);

        if (
            !Number.isNaN(litres)
            && !Number.isNaN(pricePerLitre)
            && litres >= 0
            && pricePerLitre >= 0
        ) {
            totalInput.value = (litres * pricePerLitre).toFixed(2);
        } else if (totalInput) {
            totalInput.value = '';
        }
    };

    const resizeTextarea = function (textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    };

   const filterTripLegOptions = function () {
    if (!tripSelect || !legSelect) {
        return;
    }

    const selectedTripId = String(
            tripSelect.value
            || legSelect.dataset.selectedTripId
            || ''
        );

        const selectedLegId = String(legSelect.value || '');

        legSelect.dataset.selectedTripId = selectedTripId;

        const placeholder = legSelect.querySelector(
            '#triplegid-placeholder'
        );

        const legOptions = Array.from(
            legSelect.querySelectorAll('option[data-trip-id]')
        );

        const matchingLegOptions = legOptions.filter(function (option) {
            return String(option.dataset.tripId) === selectedTripId;
        });

        legOptions.forEach(function (option) {
            const isForSelectedTrip =
                selectedTripId !== ''
                && String(option.dataset.tripId) === selectedTripId;

            option.hidden = !isForSelectedTrip;
            option.disabled = !isForSelectedTrip;
        });

        if (!placeholder) {
            return;
        }

        if (selectedTripId === '') {
            placeholder.textContent = placeholder.dataset.noTripLabel;
            placeholder.hidden = false;
            placeholder.disabled = false;

            legSelect.value = '';
            legSelect.disabled = true;

            return;
        }

        if (matchingLegOptions.length === 0) {
            placeholder.textContent = placeholder.dataset.noLegsLabel;
            placeholder.hidden = false;
            placeholder.disabled = false;

            legSelect.value = '';
            legSelect.disabled = true;

            return;
        }

        placeholder.textContent = placeholder.dataset.optionalLabel;
        placeholder.hidden = false;
        placeholder.disabled = false;

        legSelect.disabled = false;

        if (selectedLegId !== '') {
            const selectedLegOption = legOptions.find(function (option) {
                return String(option.value) === selectedLegId;
            });

            if (
                !selectedLegOption
                || selectedLegOption.disabled
                || selectedLegOption.hidden
            ) {
                legSelect.value = '';
            }
        }
    };

    litresInput?.addEventListener('input', recalculateFuelTotal);
    litresInput?.addEventListener('change', recalculateFuelTotal);

    priceInput?.addEventListener('input', recalculateFuelTotal);
    priceInput?.addEventListener('change', recalculateFuelTotal);

    tripSelect?.addEventListener('change', function () {
        if (legSelect) {
            legSelect.value = '';
            legSelect.dataset.selectedTripId = tripSelect.value || '';
        }

        filterTripLegOptions();
    });

    textareas.forEach(function (textarea) {
        resizeTextarea(textarea);

        textarea.addEventListener('input', function () {
            resizeTextarea(textarea);
        });
    });

    filterTripLegOptions();
    recalculateFuelTotal();
});
</script>