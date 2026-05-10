<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Fuel Price Observations
        </h2>
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
                    <form method="GET" action="{{ route('fuel-price-observations.index') }}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4">
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
                            <label for="pricesource" class="block text-sm font-medium text-gray-700 mb-1">Price Source</label>
                            <select
                                name="pricesource"
                                id="pricesource"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All sources</option>
                                @foreach ($priceSources as $value => $label)
                                    <option value="{{ $value }}" @selected(request('pricesource') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Observed From</label>
                            <input
                                type="date"
                                name="date_from"
                                id="date_from"
                                value="{{ request('date_from') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Observed To</label>
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
                            <a href="{{ route('fuel-price-observations.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <div class="p-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">Add Fuel Price Observation</h3>

                    <form method="POST" action="{{ route('fuel-price-observations.store') }}" class="space-y-4" data-dirty-form>
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                            <div>
                                <label for="create_fuelstopid" class="block text-sm font-medium text-gray-700 mb-1">Fuel Stop</label>
                                <select
                                    name="fuelstopid"
                                    id="create_fuelstopid"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required>
                                    <option value="">Select fuel stop</option>
                                    @foreach ($fuelStops as $fuelStop)
                                        <option value="{{ $fuelStop->id }}" @selected(old('fuelstopid') == $fuelStop->id)>
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
                                    value="{{ old('observedon') }}"
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
                                <label for="priceperlitre" class="block text-sm font-medium text-gray-700 mb-1">Price Per Litre</label>
                                <input
                                    type="number"
                                    name="priceperlitre"
                                    id="priceperlitre"
                                    value="{{ old('priceperlitre') }}"
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
                                        <option value="{{ $value }}" @selected(old('pricesource') === $value)>
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
                                rows="3"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('observationnotes') }}</textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                Add Observation
                            </button>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Observed</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Fuel Stop</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Place</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Fuel Type</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Price / Litre</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Source</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Notes</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($fuelPriceObservations as $fuelPriceObservation)
                                <tr>
                                    <td class="px-4 py-3 align-top text-gray-700">
                                        {{ optional($fuelPriceObservation->observedon)->format('Y-m-d') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-900 font-medium">
                                        {{ $fuelPriceObservation->fuelStop?->stopname ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700">
                                        {{ $fuelPriceObservation->fuelStop?->place?->placename ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700">
                                        {{ $fuelPriceObservation->fuel_type_label }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700">
                                        {{ number_format((float) $fuelPriceObservation->priceperlitre, 4) }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700">
                                        {{ $fuelPriceObservation->price_source_label }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700">
                                        {{ $fuelPriceObservation->observationnotes ? \Illuminate\Support\Str::limit($fuelPriceObservation->observationnotes, 80) : '—' }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('fuel-price-observations.edit', $fuelPriceObservation) }}"
                                               class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50">
                                                Edit
                                            </a>

                                            <form method="POST" action="{{ route('fuel-price-observations.destroy', $fuelPriceObservation) }}" onsubmit="return confirm('Delete this fuel price observation?');">
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
                                    <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500">
                                        No fuel price observations found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($fuelPriceObservations->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200">
                        {{ $fuelPriceObservations->links() }}
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

                const priceInput = form.querySelector('#priceperlitre');
                if (priceInput) {
                    priceInput.addEventListener('blur', () => {
                        const value = parseFloat(priceInput.value);
                        if (!isNaN(value) && value >= 0) {
                            priceInput.value = value.toFixed(4);
                        }
                    });
                }
            });
        })();
    </script>
</x-app-layout>