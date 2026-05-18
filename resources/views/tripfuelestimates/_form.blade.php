@php
    $isCreate = $isCreate ?? false;
    $returnTo = $returnTo ?? route('trips.fuel-estimates.index', $trip);
    $tripDefaultConsumption = (float) ($trip->defaultfuelconsumptionlper100km ?? 0);
@endphp

<div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-medium text-gray-900">
                {{ $isCreate ? 'Add Trip Fuel Estimate' : 'Fuel Estimate Details' }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Record expected fuel price, litres, distance, source observation, and notes for this trip.
            </p>
        </div>

        @if($isCreate)
            <a href="{{ $returnTo }}"
               class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                Close
            </a>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        <div>
            <label for="triplegid" class="block text-sm font-medium text-gray-700 mb-1">Trip Leg</label>
            <select name="triplegid"
                    id="triplegid"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">Select leg</option>
                @foreach ($tripLegs as $tripLeg)
                    @php
                        $legLabel =
                            $tripLeg->title
                            ?: trim(
                                collect([
                                    optional($tripLeg->fromPlace)->placename,
                                    optional($tripLeg->toPlace)->placename,
                                ])->filter()->implode(' → ')
                            )
                            ?: ('Leg ' . ($tripLeg->legnumber ?? $tripLeg->id));
                    @endphp
                    <option value="{{ $tripLeg->id }}"
                            data-distancekm="{{ $tripLeg->distancekm ?? '' }}"
                            @selected((string) old('triplegid', $fuelEstimate->triplegid ?? null) === (string) $tripLeg->id)>
                        {{ $legLabel }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="fuelstopid" class="block text-sm font-medium text-gray-700 mb-1">Fuel Stop</label>
            <select name="fuelstopid"
                    id="fuelstopid"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">Select fuel stop</option>
                @foreach ($fuelStops as $fuelStop)
                    <option value="{{ $fuelStop->id }}"
                            @selected((string) old('fuelstopid', $fuelEstimate->fuelstopid ?? null) === (string) $fuelStop->id)>
                        {{ $fuelStop->stopname }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="placeid" class="block text-sm font-medium text-gray-700 mb-1">Place</label>
            <select name="placeid"
                    id="placeid"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">Select place</option>
                @foreach ($places as $place)
                    <option value="{{ $place->id }}"
                            @selected((string) old('placeid', $fuelEstimate->placeid ?? null) === (string) $place->id)>
                        {{ $place->placename }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="estimatedate" class="block text-sm font-medium text-gray-700 mb-1">Estimate Date</label>
            <input type="date"
                   name="estimatedate"
                   id="estimatedate"
                   value="{{ old('estimatedate', optional($fuelEstimate->estimatedate ?? null)->format('Y-m-d')) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                   required>
        </div>

        <div>
            <label for="fueltype" class="block text-sm font-medium text-gray-700 mb-1">Fuel Type</label>
            <select name="fueltype"
                    id="fueltype"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                    required>
                <option value="">Select fuel type</option>
                @foreach ($fuelTypes as $value => $label)
                    <option value="{{ $value }}"
                            @selected(old('fueltype', $fuelEstimate->fueltype ?? null) === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="expectedpriceperlitre" class="block text-sm font-medium text-gray-700 mb-1">Expected Price / Litre</label>
            <input type="number"
                   name="expectedpriceperlitre"
                   id="expectedpriceperlitre"
                   value="{{ old('expectedpriceperlitre', $fuelEstimate->expectedpriceperlitre ?? $trip->defaultfuelpriceperlitre) }}"
                   step="0.0001"
                   min="0"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="estimateddistancekm" class="block text-sm font-medium text-gray-700 mb-1">Estimated Distance Km</label>
            <input type="number"
                   name="estimateddistancekm"
                   id="estimateddistancekm"
                   value="{{ old('estimateddistancekm', $fuelEstimate->estimateddistancekm ?? null) }}"
                   step="0.1"
                   min="0"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="estimatedlitres" class="block text-sm font-medium text-gray-700 mb-1">Estimated Litres</label>
            <input type="number"
                   name="estimatedlitres"
                   id="estimatedlitres"
                   value="{{ old('estimatedlitres', $fuelEstimate->estimatedlitres ?? null) }}"
                   step="0.001"
                   min="0"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="estimatedtotalcost" class="block text-sm font-medium text-gray-700 mb-1">Estimated Total Cost</label>
            <input type="number"
                   name="estimatedtotalcost"
                   id="estimatedtotalcost"
                   value="{{ old('estimatedtotalcost', $fuelEstimate->estimatedtotalcost ?? null) }}"
                   step="0.01"
                   min="0"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div class="md:col-span-2 xl:col-span-3">
            <label for="sourceobservationid" class="block text-sm font-medium text-gray-700 mb-1">Source Observation</label>
            <select name="sourceobservationid"
                    id="sourceobservationid"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">Select source observation</option>
                @foreach ($sourceObservations as $sourceObservation)
                    <option value="{{ $sourceObservation->id }}"
                            @selected((string) old('sourceobservationid', $fuelEstimate->sourceobservationid ?? null) === (string) $sourceObservation->id)>
                        {{ optional($sourceObservation->observedon)->format('Y-m-d') }}
                        —
                        {{ $sourceObservation->fuelStop?->stopname ?? 'Unknown stop' }}
                        —
                        {{ $sourceObservation->fuel_type_label }}
                        —
                        {{ number_format((float) $sourceObservation->priceperlitre, 4) }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
    <div>
        <h3 class="text-lg font-medium text-gray-900">Notes</h3>
    </div>

    <div>
        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
        <textarea name="notes"
                  id="notes"
                  rows="4"
                  class="js-auto-resize-textarea w-full min-h-[120px] overflow-hidden rounded-md border-gray-300 shadow-sm text-sm">{{ old('notes', $fuelEstimate->notes ?? null) }}</textarea>
    </div>
</div>

<div class="flex items-center justify-end gap-3">
    <a href="{{ $returnTo }}"
       class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
        Cancel
    </a>

    @if(!$isCreate)
        <button type="submit"
                name="save_action"
                value="stay"
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded text-sm font-medium text-gray-700 hover:bg-gray-50">
            Save
        </button>
    @endif

    <button type="submit"
            name="save_action"
            value="{{ $isCreate ? 'index' : 'index' }}"
            class="inline-flex items-center px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
        {{ $isCreate ? 'Add Estimate' : 'Save & Return' }}
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.currentScript.closest('form');
    if (!form) return;

    let isDirty = false;
    let isSubmitting = false;

    form.querySelectorAll('input, select, textarea').forEach((field) => {
        field.addEventListener('change', () => isDirty = true);
        field.addEventListener('input', () => isDirty = true);
    });

    form.addEventListener('submit', () => {
        isDirty = false;
        isSubmitting = true;
    });

    window.addEventListener('beforeunload', (event) => {
        if (!isDirty || isSubmitting) return;
        event.preventDefault();
        event.returnValue = '';
    });

    const tripLegSelect = form.querySelector('#triplegid');
    const distanceInput = form.querySelector('#estimateddistancekm');
    const litresInput = form.querySelector('#estimatedlitres');
    const priceInput = form.querySelector('#expectedpriceperlitre');
    const totalInput = form.querySelector('#estimatedtotalcost');
    const textareas = form.querySelectorAll('.js-auto-resize-textarea');

    const tripDefaultConsumption = {{ json_encode($tripDefaultConsumption) }};

    const autoResize = (textarea) => {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    };

    const recalcEstimatedTotal = () => {
        const litres = parseFloat(litresInput?.value);
        const price = parseFloat(priceInput?.value);

        if (!isNaN(litres) && !isNaN(price) && litres >= 0 && price >= 0) {
            totalInput.value = (litres * price).toFixed(2);
        } else if (totalInput) {
            totalInput.value = '';
        }
    };

    const recalcEstimatedLitres = () => {
        const distance = parseFloat(distanceInput?.value);

        if (!isNaN(distance) && distance >= 0 && tripDefaultConsumption > 0) {
            litresInput.value = ((distance / 100) * tripDefaultConsumption).toFixed(3);
        } else if (litresInput) {
            litresInput.value = '';
        }

        recalcEstimatedTotal();
    };

    const applyTripLegDistance = () => {
        if (!tripLegSelect || !distanceInput) return;

        const selectedOption = tripLegSelect.options[tripLegSelect.selectedIndex];
        const legDistance = selectedOption?.dataset?.distancekm;

        if (legDistance !== undefined && legDistance !== '') {
            distanceInput.value = legDistance;
            recalcEstimatedLitres();
        }
    };

    tripLegSelect?.addEventListener('change', applyTripLegDistance);
    distanceInput?.addEventListener('input', recalcEstimatedLitres);
    distanceInput?.addEventListener('change', recalcEstimatedLitres);

    litresInput?.addEventListener('input', recalcEstimatedTotal);
    litresInput?.addEventListener('change', recalcEstimatedTotal);
    priceInput?.addEventListener('input', recalcEstimatedTotal);
    priceInput?.addEventListener('change', recalcEstimatedTotal);

    textareas.forEach((textarea) => {
        autoResize(textarea);
        textarea.addEventListener('input', () => autoResize(textarea));
    });

    recalcEstimatedLitres();
    recalcEstimatedTotal();
});
</script>