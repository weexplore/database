@php
    $isCreate = $isCreate ?? false;
    $returnTo = $returnTo ?? route('trips.fuel-purchases.index', $trip);
    $fuelPurchase = $fuelPurchase ?? null;
@endphp

<div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
    <div>
        <h3 class="text-lg font-medium text-gray-900">
            Core details
        </h3>
        <p class="mt-1 text-sm text-gray-500">
            Link this purchase to a leg, fuel stop, and optional place.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <label for="purchasedate" class="block text-sm font-medium text-gray-700 mb-1">
                Purchase Date
            </label>
            <input type="date"
                   name="purchasedate"
                   id="purchasedate"
                   value="{{ old('purchasedate', optional($fuelPurchase?->purchasedate)->format('Y-m-d')) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                   required>
        </div>

        <div>
            <label for="triplegid" class="block text-sm font-medium text-gray-700 mb-1">
                Trip Leg (optional)
            </label>
            <select name="triplegid"
                    id="triplegid"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">No leg link</option>
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
                    @endphp
                    <option value="{{ $leg->id }}"
                            @selected((string) old('triplegid', $fuelPurchase?->triplegid) === (string) $leg->id)>
                        {{ $legLabel }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-col md:flex-row md:items-end gap-2">
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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <label for="placeid" class="block text-sm font-medium text-gray-700 mb-1">
                Place (optional fallback)
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
                Use when no reusable fuel stop has been created yet.
            </p>
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
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                   maxlength="150">
        </div>
    </div>
</div>

<div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
    <div>
        <h3 class="text-lg font-medium text-gray-900">Quantities and costs</h3>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div>
            <label for="litres" class="block text-sm font-medium text-gray-700 mb-1">
                Litres
            </label>
            <input type="number"
                   step="0.001"
                   min="0"
                   name="litres"
                   id="litres"
                   value="{{ old('litres', $fuelPurchase?->litres) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                   required>
        </div>

        <div>
            <label for="priceperlitre" class="block text-sm font-medium text-gray-700 mb-1">
                Price per Litre
            </label>
            <input type="number"
                   step="0.0001"
                   min="0"
                   name="priceperlitre"
                   id="priceperlitre"
                   value="{{ old('priceperlitre', $fuelPurchase?->priceperlitre) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                   required>
        </div>

        <div>
            <label for="fueltotal" class="block text-sm font-medium text-gray-700 mb-1">
                Fuel Total
            </label>
            <input type="number"
                   step="0.01"
                   min="0"
                   name="fueltotal"
                   id="fueltotal"
                   value="{{ old('fueltotal', $fuelPurchase?->fueltotal) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                   required>
            <p class="mt-1 text-xs text-gray-500">
                Auto-calculates from litres and price per litre.
            </p>
        </div>

        <div>
            <label for="servicecosts" class="block text-sm font-medium text-gray-700 mb-1">
                Service Costs
            </label>
            <input type="number"
                   step="0.01"
                   min="0"
                   name="servicecosts"
                   id="servicecosts"
                   value="{{ old('servicecosts', $fuelPurchase?->servicecosts) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="repairscost" class="block text-sm font-medium text-gray-700 mb-1">
                Repairs Cost
            </label>
            <input type="number"
                   step="0.01"
                   min="0"
                   name="repairscost"
                   id="repairscost"
                   value="{{ old('repairscost', $fuelPurchase?->repairscost) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
    </div>
</div>

<div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
    <div>
        <h3 class="text-lg font-medium text-gray-900">Distances and notes</h3>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <label for="odometerkm" class="block text-sm font-medium text-gray-700 mb-1">
                Odometer (km)
            </label>
            <input type="number"
                   step="0.1"
                   min="0"
                   name="odometerkm"
                   id="odometerkm"
                   value="{{ old('odometerkm', $fuelPurchase?->odometerkm) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="distancesincelastfillkm" class="block text-sm font-medium text-gray-700 mb-1">
                Distance since last fill (km)
            </label>
            <input type="number"
                   step="0.1"
                   min="0"
                   name="distancesincelastfillkm"
                   id="distancesincelastfillkm"
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
            value="index"
            class="inline-flex items-center px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
        {{ $isCreate ? 'Save Purchase' : 'Save & Return' }}
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
        isSubmitting = true;
        isDirty = false;
    });

    window.addEventListener('beforeunload', (event) => {
        if (!isDirty || isSubmitting) return;
        event.preventDefault();
        event.returnValue = '';
    });

    const litresInput = form.querySelector('#litres');
    const priceInput = form.querySelector('#priceperlitre');
    const totalInput = form.querySelector('#fueltotal');
    const textareas = form.querySelectorAll('.js-auto-resize-textarea');

    const recalcTotal = () => {
        const litres = parseFloat(litresInput?.value);
        const price = parseFloat(priceInput?.value);

        if (!isNaN(litres) && !isNaN(price) && litres >= 0 && price >= 0) {
            totalInput.value = (litres * price).toFixed(2);
        } else if (totalInput) {
            totalInput.value = '';
        }
    };

    const autoResize = (textarea) => {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    };

    litresInput?.addEventListener('input', recalcTotal);
    litresInput?.addEventListener('change', recalcTotal);
    priceInput?.addEventListener('input', recalcTotal);
    priceInput?.addEventListener('change', recalcTotal);

    textareas.forEach((textarea) => {
        autoResize(textarea);
        textarea.addEventListener('input', () => autoResize(textarea));
    });

    recalcTotal();
});
</script>