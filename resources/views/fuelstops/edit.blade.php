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
                {{-- Main form --}}
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

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="placeid" class="block text-sm font-medium text-gray-700 mb-1">Place</label>
                                    <select
                                        name="placeid"
                                        id="placeid"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option value="">Select place</option>
                                        @foreach ($places as $place)
                                            <option value="{{ $place->id }}" @selected(old('placeid', $fuelStop->placeid) == $place->id)>
                                                {{ $place->placename }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="stopname" class="block text-sm font-medium text-gray-700 mb-1">Stop Name</label>
                                    <input
                                        type="text"
                                        name="stopname"
                                        id="stopname"
                                        value="{{ old('stopname', $fuelStop->stopname) }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        maxlength="200"
                                        required
                                    >
                                </div>

                                <div>
                                    <label for="brandname" class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                                    <input
                                        type="text"
                                        name="brandname"
                                        id="brandname"
                                        value="{{ old('brandname', $fuelStop->brandname) }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        maxlength="100"
                                    >
                                </div>
                            </div>

                            <div>
                                <span class="block text-sm font-medium text-gray-700 mb-2">Fuel Types Available</span>
                                @php
                                    $selectedFuelTypes = old('fueltypesavailable', $fuelStop->fuel_types_array);
                                @endphp
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                    @foreach ($fuelTypes as $value => $label)
                                        <label class="inline-flex items-center gap-2">
                                            <input
                                                type="checkbox"
                                                name="fueltypesavailable[]"
                                                value="{{ $value }}"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm"
                                                @checked(in_array($value, $selectedFuelTypes))
                                            >
                                            <span class="text-sm text-gray-700">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                <label class="inline-flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        name="hashighflowdiesel"
                                        value="1"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm"
                                        @checked(old('hashighflowdiesel', $fuelStop->hashighflowdiesel))
                                    >
                                    <span class="text-sm text-gray-700">High-flow diesel</span>
                                </label>

                                <label class="inline-flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        name="hasadblue"
                                        value="1"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm"
                                        @checked(old('hasadblue', $fuelStop->hasadblue))
                                    >
                                    <span class="text-sm text-gray-700">AdBlue</span>
                                </label>

                                <label class="inline-flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        name="hascarwash"
                                        value="1"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm"
                                        @checked(old('hascarwash', $fuelStop->hascarwash))
                                    >
                                    <span class="text-sm text-gray-700">Car wash</span>
                                </label>

                                <label class="inline-flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        name="hasairwater"
                                        value="1"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm"
                                        @checked(old('hasairwater', $fuelStop->hasairwater))
                                    >
                                    <span class="text-sm text-gray-700">Air / water</span>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label for="caravanaccessnotes" class="block text-sm font-medium text-gray-700 mb-1">Caravan Access Notes</label>
                                    <textarea
                                        name="caravanaccessnotes"
                                        id="caravanaccessnotes"
                                        rows="4"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >{{ old('caravanaccessnotes', $fuelStop->caravanaccessnotes) }}</textarea>
                                </div>

                                <div>
                                    <label for="openingnotes" class="block text-sm font-medium text-gray-700 mb-1">Opening Notes</label>
                                    <textarea
                                        name="openingnotes"
                                        id="openingnotes"
                                        rows="4"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >{{ old('openingnotes', $fuelStop->openingnotes) }}</textarea>
                                </div>

                                <div>
                                    <label for="generalnotes" class="block text-sm font-medium text-gray-700 mb-1">General Notes</label>
                                    <textarea
                                        name="generalnotes"
                                        id="generalnotes"
                                        rows="4"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >{{ old('generalnotes', $fuelStop->generalnotes) }}</textarea>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                <label class="inline-flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        name="isactive"
                                        value="1"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm"
                                        @checked(old('isactive', $fuelStop->isactive))
                                    >
                                    <span class="text-sm text-gray-700">Active</span>
                                </label>

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

                    {{-- Delete block --}}
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

                {{-- Sidebar --}}
                <div class="xl:col-span-1 space-y-6">
                    {{-- Fuel Price Observations --}}
                    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900">
                                Fuel Price Observations
                            </h3>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ $fuelStop->fuelPriceObservations->count() }} linked record{{ $fuelStop->fuelPriceObservations->count() === 1 ? '' : 's' }}
                            </p>
                        </div>

                        <div class="p-4">
                            @if ($fuelStop->fuelPriceObservations->isNotEmpty())
                                <ul class="space-y-3 text-sm">
                                    @foreach ($fuelStop->fuelPriceObservations as $observation)
                                        <li>
                                            <a
                                                href="{{ route('fuel-price-observations.edit', [
                                                    'fuel_price_observation' => $observation,
                                                    'return_to' => url()->full(),
                                                ]) }}"
                                                class="block border border-gray-200 rounded-md px-3 py-2 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            >
                                                <div class="font-medium text-gray-900">
                                                    {{ $observation->fuel_type_label ?? $observation->fueltype ?? 'Fuel observation' }}
                                                </div>
                                                <div class="mt-1 text-xs text-gray-500">
                                                    Price:
                                                    @if (!is_null($observation->priceperlitre))
                                                        ${{ number_format((float) $observation->priceperlitre, 4) }} / L
                                                    @else
                                                        —
                                                    @endif
                                                </div>
                                                <div class="mt-1 text-xs text-gray-500">
                                                    Observed:
                                                    {{ $observation->observedon ? \Illuminate\Support\Carbon::parse($observation->observedon)->format('d M Y') : '—' }}
                                                </div>
                                                <div class="mt-1 text-xs text-gray-500">
                                                    Source: {{ $observation->price_source_label ?? $observation->pricesource ?? '—' }}
                                                </div>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-gray-500">
                                    No fuel price observations are currently linked to this stop.
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Trip Fuel Purchases --}}
                    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900">
                                Trip Fuel Purchases
                            </h3>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ $fuelStop->tripFuelPurchases->count() }} linked record{{ $fuelStop->tripFuelPurchases->count() === 1 ? '' : 's' }}
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