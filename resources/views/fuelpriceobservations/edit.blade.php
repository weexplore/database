<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Fuel Price Observation
        </h2>
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
                <form method="POST" action="{{ route('fuel-price-observations.update', $fuelPriceObservation) }}" class="p-6 space-y-6" data-dirty-form>
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="fuelstopid" class="block text-sm font-medium text-gray-700 mb-1">Fuel Stop</label>
                            <select
                                name="fuelstopid"
                                id="fuelstopid"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">Select fuel stop</option>
                                @foreach ($fuelStops as $fuelStop)
                                    <option value="{{ $fuelStop->id }}" @selected(old('fuelstopid', $fuelPriceObservation->fuelstopid) == $fuelStop->id)>
                                        {{ $fuelStop->stopname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="observedon" class="block text-sm font-medium text-gray-700 mb-1">Observed On</label>
                            <input
                                type="date"
                                name="observedon"
                                id="observedon"
                                value="{{ old('observedon', optional($fuelPriceObservation->observedon)->format('Y-m-d')) }}"
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
                                    <option value="{{ $value }}" @selected(old('fueltype', $fuelPriceObservation->fueltype) === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="priceperlitre" class="block text-sm font-medium text-gray-700 mb-1">Price Per Litre</label>
                            <input
                                type="number"
                                name="priceperlitre"
                                id="priceperlitre"
                                value="{{ old('priceperlitre', $fuelPriceObservation->priceperlitre) }}"
                                step="0.0001"
                                min="0"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                        </div>

                        <div>
                            <label for="pricesource" class="block text-sm font-medium text-gray-700 mb-1">Price Source</label>
                            <select
                                name="pricesource"
                                id="pricesource"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select source</option>
                                @foreach ($priceSources as $value => $label)
                                    <option value="{{ $value }}" @selected(old('pricesource', $fuelPriceObservation->pricesource) === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="observationnotes" class="block text-sm font-medium text-gray-700 mb-1">Observation Notes</label>
                        <textarea
                            name="observationnotes"
                            id="observationnotes"
                            rows="4"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('observationnotes', $fuelPriceObservation->observationnotes) }}</textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-200">
                        <a href="{{ route('fuel-price-observations.index') }}"
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
                        <h3 class="text-sm font-semibold text-red-700">Delete Fuel Price Observation</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            This action will permanently remove this fuel price observation.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('fuel-price-observations.destroy', $fuelPriceObservation) }}" onsubmit="return confirm('Delete this fuel price observation?');">
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
</x-app-layout>