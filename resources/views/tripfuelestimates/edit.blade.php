<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Trip Fuel Estimate
                </h2>
                <div class="mt-1 text-sm text-gray-500">
                    {{ $trip->tripname ?? ('Trip #' . $trip->id) }}
                </div>
            </div>

            <a href="{{ route('trips.fuel-estimates.index', $trip) }}"
               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                Back to Estimates
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <div class="font-semibold mb-2">Please fix the following:</div>
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <form method="POST" action="{{ route('trips.fuel-estimates.update', [$trip, $fuelEstimate]) }}" class="p-6 space-y-6" data-dirty-form>
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="triplegid" class="block text-sm font-medium text-gray-700 mb-1">Trip Leg</label>
                            <select
                                name="triplegid"
                                id="triplegid"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select leg</option>
                                @foreach ($tripLegs as $tripLeg)
                                    <option value="{{ $tripLeg->id }}" @selected(old('triplegid', $fuelEstimate->triplegid) == $tripLeg->id)>
                                        {{ $tripLeg->sequencenumber ? 'Leg ' . $tripLeg->sequencenumber : 'Leg ' . $tripLeg->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="fuelstopid" class="block text-sm font-medium text-gray-700 mb-1">Fuel Stop</label>
                            <select
                                name="fuelstopid"
                                id="fuelstopid"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select fuel stop</option>
                                @foreach ($fuelStops as $fuelStop)
                                    <option value="{{ $fuelStop->id }}" @selected(old('fuelstopid', $fuelEstimate->fuelstopid) == $fuelStop->id)>
                                        {{ $fuelStop->stopname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="placeid" class="block text-sm font-medium text-gray-700 mb-1">Place</label>
                            <select
                                name="placeid"
                                id="placeid"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select place</option>
                                @foreach ($places as $place)
                                    <option value="{{ $place->id }}" @selected(old('placeid', $fuelEstimate->placeid) == $place->id)>
                                        {{ $place->placename }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="estimatedate" class="block text-sm font-medium text-gray-700 mb-1">Estimate Date</label>
                            <input
                                type="date"
                                name="estimatedate"
                                id="estimatedate"
                                value="{{ old('estimatedate', optional($fuelEstimate->estimatedate)->format('Y-m-d')) }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                        </div>

                        <div>
                            <label for="fueltype" class="block text-sm font-medium text-gray-700 mb-1">Fuel Type</label>
                            <select
                                name="fueltype"
                                id="fueltype"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">Select fuel type</option>
                                @foreach ($fuelTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('fueltype', $fuelEstimate->fueltype) === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="expectedpriceperlitre" class="block text-sm font-medium text-gray-700 mb-1">Expected Price / Litre</label>
                            <input
                                type="number"
                                name="expectedpriceperlitre"
                                id="expectedpriceperlitre"
                                value="{{ old('expectedpriceperlitre', $fuelEstimate->expectedpriceperlitre) }}"
                                step="0.0001"
                                min="0"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="estimateddistancekm" class="block text-sm font-medium text-gray-700 mb-1">Estimated Distance Km</label>
                            <input
                                type="number"
                                name="estimateddistancekm"
                                id="estimateddistancekm"
                                value="{{ old('estimateddistancekm', $fuelEstimate->estimateddistancekm) }}"
                                step="0.1"
                                min="0"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="estimatedlitres" class="block text-sm font-medium text-gray-700 mb-1">Estimated Litres</label>
                            <input
                                type="number"
                                name="estimatedlitres"
                                id="estimatedlitres"
                                value="{{ old('estimatedlitres', $fuelEstimate->estimatedlitres) }}"
                                step="0.001"
                                min="0"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="estimatedtotalcost" class="block text-sm font-medium text-gray-700 mb-1">Estimated Total Cost</label>
                            <input
                                type="number"
                                name="estimatedtotalcost"
                                id="estimatedtotalcost"
                                value="{{ old('estimatedtotalcost', $fuelEstimate->estimatedtotalcost) }}"
                                step="0.01"
                                min="0"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="md:col-span-2">
                            <label for="sourceobservationid" class="block text-sm font-medium text-gray-700 mb-1">Source Observation</label>
                            <select
                                name="sourceobservationid"
                                id="sourceobservationid"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select source observation</option>
                                @foreach ($sourceObservations as $sourceObservation)
                                    <option value="{{ $sourceObservation->id }}" @selected(old('sourceobservationid', $fuelEstimate->sourceobservationid) == $sourceObservation->id)>
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

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea
                            name="notes"
                            id="notes"
                            rows="4"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $fuelEstimate->notes) }}</textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-200">
                        <a href="{{ route('trips.fuel-estimates.index', $trip) }}"
                           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                            Cancel
                        </a>

                        <button type="submit"
                                name="save_action"
                                value="stay"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                            Save
                        </button>

                        <button type="submit"
                                name="save_action"
                                value="index"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Save & Return
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm rounded-lg border border-red-200">
                <div class="p-6 flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-red-700">Delete Trip Fuel Estimate</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            This action will permanently remove this trip fuel estimate from the trip plan.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('trips.fuel-estimates.destroy', [$trip, $fuelEstimate]) }}" onsubmit="return confirm('Delete this trip fuel estimate?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

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

            // Auto-calc Estimated Total Cost from Estimated Litres and Expected Price / Litre
            const litresInput = form.querySelector('#estimatedlitres');
            const priceInput  = form.querySelector('#expectedpriceperlitre');
            const totalInput  = form.querySelector('#estimatedtotalcost');

            if (litresInput && priceInput && totalInput) {
                function recalcEstimatedTotal() {
                    const litres = parseFloat(litresInput.value);
                    const price  = parseFloat(priceInput.value);

                    if (!isNaN(litres) && !isNaN(price) && litres > 0 && price > 0) {
                        const total = litres * price;
                        totalInput.value = total.toFixed(2);
                    }
                }

                litresInput.addEventListener('input', recalcEstimatedTotal);
                priceInput.addEventListener('input', recalcEstimatedTotal);
            }
        });
    })();
</script>
</x-app-layout>