<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Trip Fuel Estimates
                </h2>
                <div class="mt-1 text-sm text-gray-500">
                    {{ $trip->tripname ?? ('Trip #' . $trip->id) }}
                </div>
            </div>

            <a href="{{ route('trips.edit', $trip) }}"
               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                Back to Trip
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @if (session('success'))
                <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
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
                <div class="p-4 border-b border-gray-200">
                    <form method="GET" action="{{ route('trips.fuel-estimates.index', $trip) }}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4">
                        <div>
                            <label for="triplegid" class="block text-sm font-medium text-gray-700 mb-1">Trip Leg</label>
                            <select
                                name="triplegid"
                                id="triplegid"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All legs</option>
                                @foreach ($tripLegs as $tripLeg)
                                    <option value="{{ $tripLeg->id }}" @selected((string) request('triplegid') === (string) $tripLeg->id)>
                                        {{ $tripLeg->legnumber ? 'Leg ' . $tripLeg->legnumber : 'Leg ' . $tripLeg->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="fueltype" class="block text-sm font-medium text-gray-700 mb-1">Fuel Type</label>
                            <select
                                name="fueltype"
                                id="fueltype"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All fuel types</option>
                                @foreach ($fuelTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(request('fueltype') === $value)>
                                        {{ $label }}
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
                                <option value="">All fuel stops</option>
                                @foreach ($fuelStops as $fuelStop)
                                    <option value="{{ $fuelStop->id }}" @selected((string) request('fuelstopid') === (string) $fuelStop->id)>
                                        {{ $fuelStop->stopname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Estimate From</label>
                            <input
                                type="date"
                                name="date_from"
                                id="date_from"
                                value="{{ request('date_from') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Estimate To</label>
                            <input
                                type="date"
                                name="date_to"
                                id="date_to"
                                value="{{ request('date_to') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                Filter
                            </button>
                            <a href="{{ route('trips.fuel-estimates.index', $trip) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <div class="p-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">Add Trip Fuel Estimate</h3>

                    <form method="POST" action="{{ route('trips.fuel-estimates.store', $trip) }}" class="space-y-4" data-dirty-form>
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div>
                                <label for="triplegid" class="block text-sm font-medium text-gray-700 mb-1">Trip Leg</label>
                                <select
                                    name="triplegid"
                                    id="triplegid"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select leg</option>
                                    @foreach ($tripLegs as $tripLeg)
                                        <option value="{{ $tripLeg->id }}" @selected(old('triplegid') == $tripLeg->id)>
                                            {{ $tripLeg->legnumber ? 'Leg ' . $tripLeg->legnumber : '—' }}
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
                                        <option value="{{ $fuelStop->id }}" @selected(old('fuelstopid') == $fuelStop->id)>
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
                                        <option value="{{ $place->id }}" @selected(old('placeid') == $place->id)>
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
                                    value="{{ old('estimatedate') }}"
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
                                        <option value="{{ $value }}" @selected(old('fueltype') === $value)>
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
                                    value="{{ old('expectedpriceperlitre') }}"
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
                                    value="{{ old('estimateddistancekm') }}"
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
                                    value="{{ old('estimatedlitres') }}"
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
                                    value="{{ old('estimatedtotalcost') }}"
                                    step="0.01"
                                    min="0"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div class="xl:col-span-3">
                                <label for="sourceobservationid" class="block text-sm font-medium text-gray-700 mb-1">Source Observation</label>
                                <select
                                    name="sourceobservationid"
                                    id="sourceobservationid"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select source observation</option>
                                    @foreach ($sourceObservations as $sourceObservation)
                                        <option value="{{ $sourceObservation->id }}" @selected(old('sourceobservationid') == $sourceObservation->id)>
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
                                rows="3"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                Add Estimate
                            </button>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Date</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Leg</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Fuel Stop</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Place</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Fuel Type</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Price/L</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Distance</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Litres</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Total</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($tripFuelEstimates as $fuelEstimate)
                                <tr>
                                    <td class="px-4 py-3 align-top text-gray-700">
                                        {{ optional($fuelEstimate->estimatedate)->format('Y-m-d') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700">
                                        {{ $fuelEstimate->tripLeg?->legnumber ? 'Leg ' . $fuelEstimate->tripLeg->legnumber : '—' }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-900 font-medium">
                                        {{ $fuelEstimate->fuelStop?->stopname ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700">
                                        {{ $fuelEstimate->place?->placename ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700">
                                        {{ $fuelEstimate->fuel_type_label }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700">
                                        {{ $fuelEstimate->expectedpriceperlitre !== null ? number_format((float) $fuelEstimate->expectedpriceperlitre, 4) : '—' }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700">
                                        {{ $fuelEstimate->estimateddistancekm !== null ? number_format((float) $fuelEstimate->estimateddistancekm, 1) : '—' }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700">
                                        {{ $fuelEstimate->estimatedlitres !== null ? number_format((float) $fuelEstimate->estimatedlitres, 3) : '—' }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700">
                                        {{ $fuelEstimate->estimatedtotalcost !== null ? number_format((float) $fuelEstimate->estimatedtotalcost, 2) : '—' }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('trips.fuel-estimates.edit', [$trip, $fuelEstimate]) }}"
                                               class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50">
                                                Edit
                                            </a>

                                            <form method="POST" action="{{ route('trips.fuel-estimates.destroy', [$trip, $fuelEstimate]) }}" onsubmit="return confirm('Delete this trip fuel estimate?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center px-3 py-1.5 border border-red-300 rounded-md text-xs font-medium text-red-700 bg-white hover:bg-red-50">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-6 text-center text-sm text-gray-500">
                                        No trip fuel estimates found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($tripFuelEstimates->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200">
                        {{ $tripFuelEstimates->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

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