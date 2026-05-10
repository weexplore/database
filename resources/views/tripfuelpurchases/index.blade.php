<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Trip Fuel Purchases
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $trip->tripname }}
                </p>
            </div>

            <a href="{{ route('trips.edit', $trip) }}"
               class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                Back to Trip
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            {{-- Filters and create --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                <form method="GET"
                      action="{{ route('trips.fuel-purchases.index', $trip) }}"
                      class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="trip_leg_id" class="block text-xs font-medium text-gray-700 mb-1">
                                Trip Leg
                            </label>
                            <select name="trip_leg_id"
                                    id="trip_leg_id"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All legs</option>
                                @foreach($tripLegs as $leg)
                                    <option value="{{ $leg->id }}"
                                        @selected(request('trip_leg_id') == $leg->id)>
                                        @if($leg->legnumber)
                                            Leg {{ $leg->legnumber }} –
                                        @endif
                                        {{ optional($leg->startdate)->format('Y-m-d') }}
                                        @if($leg->title)
                                            – {{ $leg->title }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="fuel_stop_id" class="block text-xs font-medium text-gray-700 mb-1">
                                Fuel Stop
                            </label>
                            <select name="fuel_stop_id"
                                    id="fuel_stop_id"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All fuel stops</option>
                                @foreach($fuelStops as $fuelStop)
                                    <option value="{{ $fuelStop->id }}"
                                        @selected(request('fuel_stop_id') == $fuelStop->id)>
                                        {{ $fuelStop->stopname }}
                                        @if($fuelStop->place)
                                            – {{ $fuelStop->place->placename }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

<div>
    <label for="create_fueltype" class="block text-xs font-medium text-gray-700 mb-1">
        Fuel Type
    </label>
    <select name="fueltype"
            id="create_fueltype"
            class="w-full rounded-md border-gray-300 shadow-sm text-sm"
            required>
        <option value="">Select fuel type</option>
        @foreach(($fuelTypes ?? []) as $value => $label)
            <option value="{{ $value }}"
                @selected(old('fueltype') === $value)>
                {{ $label }}
            </option>
        @endforeach
    </select>
</div>

                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                Apply Filters
                            </button>

                            <a href="{{ route('trips.fuel-purchases.index', $trip) }}"
                               class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 text-xs">
                                Clear
                            </a>

                            <button type="button"
                                    onclick="document.getElementById('fuel-purchase-create-form').classList.remove('hidden')"
                                    class="ml-auto inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                New Purchase
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Inline create form (simple) --}}
            <div id="fuel-purchase-create-form" class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4 hidden">
                <h3 class="text-sm font-semibold text-gray-800">Add Fuel Purchase</h3>

                <form method="POST"
                      action="{{ route('trips.fuel-purchases.store', $trip) }}"
                      class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="create_purchasedate" class="block text-xs font-medium text-gray-700 mb-1">
                                Purchase Date
                            </label>
                            <input type="date"
                                   name="purchasedate"
                                   id="create_purchasedate"
                                   value="{{ old('purchasedate') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                   required>
                        </div>

                        <div>
                            <label for="create_triplegid" class="block text-xs font-medium text-gray-700 mb-1">
                                Trip Leg (optional)
                            </label>
                            <select name="triplegid"
                                    id="create_triplegid"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">No leg link</option>
                                @foreach($tripLegs as $leg)
                                    <option value="{{ $leg->id }}"
                                        @selected(old('triplegid') == $leg->id)>
                                        @if($leg->legnumber)
                                            Leg {{ $leg->legnumber }} –
                                        @endif
                                        {{ optional($leg->startdate)->format('Y-m-d') }}
                                        @if($leg->title)
                                            – {{ $leg->title }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex flex-col md:flex-row md:items-end gap-2">
                            <div class="flex-1 min-w-0">
                                <label for="create_fuelstopid" class="block text-xs font-medium text-gray-700 mb-1">
                                    Fuel Stop (optional)
                                </label>
                                <select name="fuelstopid"
                                        id="create_fuelstopid"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">No linked fuel stop</option>
                                    @foreach($fuelStops as $fuelStop)
                                        <option value="{{ $fuelStop->id }}"
                                                @selected(old('fuelstopid') == $fuelStop->id)>
                                            {{ $fuelStop->stopname }}
                                            @if($fuelStop->place)
                                                – {{ $fuelStop->place->placename }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <a href="{{ route('fuel-stops.create', ['return_to' => url()->full()]) }}"
                            class="inline-flex shrink-0 items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-md text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                Add Fuel Stop
                            </a>
                        </div>
                    </div>

                    <<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <div>
        <label for="create_fueltype" class="block text-xs font-medium text-gray-700 mb-1">
            Fuel Type
        </label>
        <select name="fueltype"
                id="create_fueltype"
                class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                required>
            <option value="">Select fuel type</option>
            @foreach(($fuelTypes ?? []) as $value => $label)
                <option value="{{ $value }}"
                    @selected(old('fueltype') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="create_litres" class="block text-xs font-medium text-gray-700 mb-1">
            Litres
        </label>
        <input type="number"
               step="0.001"
               min="0"
               name="litres"
               id="create_litres"
               data-fuel-litres
               value="{{ old('litres') }}"
               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
               required>
    </div>

    <div>
        <label for="create_priceperlitre" class="block text-xs font-medium text-gray-700 mb-1">
            Price per Litre
        </label>
        <input type="number"
               step="0.0001"
               min="0"
               name="priceperlitre"
               id="create_priceperlitre"
               data-fuel-price
               value="{{ old('priceperlitre') }}"
               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
               required>
    </div>

    <div>
        <label for="create_fueltotal" class="block text-xs font-medium text-gray-700 mb-1">
            Fuel Total
        </label>
        <input type="number"
               step="0.01"
               min="0"
               name="fueltotal"
               id="create_fueltotal"
               data-fuel-total
               value="{{ old('fueltotal') }}"
               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
               required>
        <p class="mt-1 text-[11px] text-gray-500">
            Will auto-calculate when litres and price are entered.
        </p>
    </div>
</div>

                    <div class="flex items-center justify-end gap-3">
                        <button type="button"
                                onclick="document.getElementById('fuel-purchase-create-form').classList.add('hidden')"
                                class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 text-xs">
                            Cancel
                        </button>

                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                            Save Purchase
                        </button>
                    </div>
                </form>
            </div>

            {{-- Purchases table --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-800">
                        Fuel Purchases ({{ $purchases->total() }})
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="border-b border-gray-200 bg-gray-50">
                        <tr class="text-xs text-gray-500 uppercase tracking-wide">
                            <th class="px-3 py-2 text-left">Date</th>
                            <th class="px-3 py-2 text-left">Leg</th>
                            <th class="px-3 py-2 text-left">Fuel Stop / Place</th>
                            <th class="px-3 py-2 text-left">Fuel Type</th>
                            <th class="px-3 py-2 text-right">Litres</th>
                            <th class="px-3 py-2 text-right">Price/L</th>
                            <th class="px-3 py-2 text-right">Total</th>
                            <th class="px-3 py-2 text-right">Odometer</th>
                            <th class="px-3 py-2 text-right">Since Last</th>
                            <th class="px-3 py-2 text-right">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                        @forelse($purchases as $purchase)
                            <tr>
                                <td class="px-3 py-2 whitespace-nowrap text-gray-800">
                                    {{ optional($purchase->purchasedate)->format('Y-m-d') }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-gray-700">
                                    @if($purchase->leg)
                                        @if($purchase->leg->legnumber)
                                            Leg {{ $purchase->leg->legnumber }}
                                        @else
                                            Leg #{{ $purchase->leg->id }}
                                        @endif
                                    @else
                                        <span class="text-gray-400 text-xs">No leg</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-gray-700">
                                    @if($purchase->fuelStop)
                                        {{ $purchase->fuelStop->stopname }}
                                        @if($purchase->fuelStop->place)
                                            <span class="text-gray-400 text-xs">
                                                – {{ $purchase->fuelStop->place->placename }}
                                            </span>
                                        @endif
                                    @elseif($purchase->place)
                                        {{ $purchase->place->placename }}
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-gray-700">
                                    {{ $purchase->fueltype }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-right text-gray-700">
                                    {{ number_format((float) $purchase->litres, 3) }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-right text-gray-700">
                                    {{ number_format((float) $purchase->priceperlitre, 4) }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-right text-gray-800">
                                    {{ number_format((float) $purchase->fueltotal, 2) }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-right text-gray-700">
                                    @if($purchase->odometerkm !== null)
                                        {{ number_format((float) $purchase->odometerkm, 1) }}
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-right text-gray-700">
                                    @if($purchase->distancesincelastfillkm !== null)
                                        {{ number_format((float) $purchase->distancesincelastfillkm, 1) }}
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-right text-gray-700">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('trips.fuel-purchases.edit', [$trip, $purchase]) }}"
                                           class="text-xs text-blue-600 hover:text-blue-800">
                                            Edit
                                        </a>

                                        <form method="POST"
                                              action="{{ route('trips.fuel-purchases.destroy', [$trip, $purchase]) }}"
                                              onsubmit="return confirm('Delete this fuel purchase?');"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-xs text-red-600 hover:text-red-800">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-3 py-6 text-center text-sm text-gray-500">
                                    No fuel purchases recorded for this trip yet.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pt-4">
                    {{ $purchases->links() }}
                </div>
            </div>
        </div>
    </div>

    @if(session('show_create'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const panel = document.getElementById('fuel-purchase-create-form');
                if (panel) panel.classList.remove('hidden');
            });
        </script>
    @endif
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const litresInput = document.querySelector('#create_litres');
        const priceInput  = document.querySelector('#create_priceperlitre');
        const totalInput  = document.querySelector('#create_fueltotal');

        if (!litresInput || !priceInput || !totalInput) return;

        function recalcTotal() {
            const litres = parseFloat(litresInput.value);
            const price  = parseFloat(priceInput.value);

            if (!isNaN(litres) && !isNaN(price) && litres > 0 && price > 0) {
                const total = litres * price;
                totalInput.value = total.toFixed(2);
            }
        }

        litresInput.addEventListener('input', recalcTotal);
        priceInput.addEventListener('input', recalcTotal);
    });
</script>
</x-app-layout>