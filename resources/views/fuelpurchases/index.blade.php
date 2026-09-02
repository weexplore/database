<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Fuel Purchases
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Record actual fuel purchases, service costs, repairs, and assign purchases to trips.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if(request('assignment') === 'unassigned')
                    <a href="{{ route('fuel-purchases.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                        Show All Purchases
                    </a>
                @else
                    <a href="{{ route('fuel-purchases.index', ['assignment' => 'unassigned']) }}"
                    class="inline-flex items-center px-4 py-2 bg-amber-100 text-amber-800 rounded hover:bg-amber-200 text-sm">
                        Unassigned
                    </a>
                @endif

                <a href="{{ route('fuel-purchases.create') }}"
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                    New Fuel Purchase
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">
            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('fuel-purchases.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4">
                        <div>
                            <label for="assignment" class="block text-sm font-medium text-gray-700 mb-1">
                                Assignment
                            </label>

                            <select name="assignment"
                                    id="assignment"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All purchases</option>
                                <option value="assigned" @selected(request('assignment') === 'assigned')>
                                    Assigned to a trip
                                </option>
                                <option value="unassigned" @selected(request('assignment') === 'unassigned')>
                                    Unassigned
                                </option>
                            </select>
                        </div>

                        <div>
                            <label for="tripid" class="block text-sm font-medium text-gray-700 mb-1">
                                Trip
                            </label>

                            <select name="tripid"
                                    id="tripid"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All trips</option>

                                @foreach($trips as $trip)
                                    <option value="{{ $trip->id }}"
                                        @selected((string) request('tripid') === (string) $trip->id)>
                                        {{ $trip->tripname }}
                                        @if($trip->startdate)
                                            – {{ $trip->startdate->format('d M Y') }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="triplegid" class="block text-sm font-medium text-gray-700 mb-1">
                                Trip Leg
                            </label>

                            <select name="triplegid"
                                    id="triplegid"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All legs</option>

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
                                            data-trip-id="{{ $leg->tripid }}"
                                            @selected((string) request('triplegid') === (string) $leg->id)>
                                        {{ $legLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="fuelstopid" class="block text-sm font-medium text-gray-700 mb-1">
                                Fuel Stop
                            </label>

                            <select name="fuelstopid"
                                    id="fuelstopid"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All fuel stops</option>

                                @foreach($fuelStops as $fuelStop)
                                    <option value="{{ $fuelStop->id }}"
                                        @selected((string) request('fuelstopid') === (string) $fuelStop->id)>
                                        {{ $fuelStop->stopname }}
                                        @if($fuelStop->place)
                                            – {{ $fuelStop->place->placename }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="fueltype" class="block text-sm font-medium text-gray-700 mb-1">
                                Fuel Type
                            </label>

                            <select name="fueltype"
                                    id="fueltype"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All fuel types</option>

                                @foreach($fuelTypes as $value => $label)
                                    <option value="{{ $value }}"
                                        @selected(request('fueltype') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                                Receipt / Notes
                            </label>

                            <input type="search"
                                   name="search"
                                   id="search"
                                   value="{{ request('search') }}"
                                   placeholder="Search receipt or notes"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">
                                Date From
                            </label>

                            <input type="date"
                                   name="date_from"
                                   id="date_from"
                                   value="{{ request('date_from') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">
                                Date To
                            </label>

                            <input type="date"
                                   name="date_to"
                                   id="date_to"
                                   value="{{ request('date_to') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div class="md:col-span-2 xl:col-span-2 flex flex-wrap items-end gap-2">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                Apply Filters
                            </button>

                            <a href="{{ route('fuel-purchases.index') }}"
                               class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 text-sm">
                                Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">
                            Fuel Purchases ({{ $purchases->total() }})
                        </h3>

                        @if(request('assignment') === 'unassigned')
                            <p class="mt-1 text-xs text-amber-700">
                                These purchases are not yet assigned to a trip.
                            </p>
                        @endif
                    </div>

                    <a href="{{ route('fuel-purchases.create', request()->only([
                        'tripid',
                    ])) }}"
                       class="inline-flex items-center px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-xs">
                        Add Purchase
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="border-b border-gray-200 bg-gray-50">
                            <tr class="text-xs text-gray-500 uppercase tracking-wide">
                                <th class="px-3 py-2 text-left">Date</th>
                                <th class="px-3 py-2 text-left">Trip</th>
                                <th class="px-3 py-2 text-left">Leg</th>
                                <th class="px-3 py-2 text-left">Fuel Stop / Place</th>
                                <th class="px-3 py-2 text-left">Fuel Type</th>
                                <th class="px-3 py-2 text-right">Litres</th>
                                <th class="px-3 py-2 text-right">Price/L</th>
                                <th class="px-3 py-2 text-right">Fuel Total</th>
                                <th class="px-3 py-2 text-right">Extra Costs</th>
                                <th class="px-3 py-2 text-right">Odometer</th>
                                <th class="px-3 py-2 text-right">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse($purchases as $purchase)
                                @php
                                    $leg = $purchase->leg;

                                    $legLabel =
                                        $leg?->title
                                        ?: trim(
                                            collect([
                                                optional($leg?->fromPlace)->placename,
                                                optional($leg?->toPlace)->placename,
                                            ])->filter()->implode(' → ')
                                        )
                                        ?: (
                                            $leg
                                                ? (
                                                    $leg->legnumber
                                                        ? 'Leg ' . $leg->legnumber
                                                        : 'Leg #' . $leg->id
                                                )
                                                : null
                                        );

                                    $extraCosts =
                                        (float) ($purchase->servicecosts ?? 0)
                                        + (float) ($purchase->repairscost ?? 0);

                                    $returnTo = url()->full();
                                @endphp

                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 whitespace-nowrap text-gray-800">
                                        {{ optional($purchase->purchasedate)->format('Y-m-d') ?? '—' }}
                                    </td>

                                    <td class="px-3 py-2 min-w-[180px] text-gray-700">
                                        @if($purchase->trip)
                                            <a href="{{ route('trips.edit', [
                                                'trip' => $purchase->trip,
                                                'tab' => 'workflow',
                                            ]) }}"
                                               class="text-blue-700 hover:text-blue-900 hover:underline">
                                                {{ $purchase->trip->tripname }}
                                            </a>

                                            @if($purchase->trip->tripstatus)
                                                <div class="mt-0.5 text-xs text-gray-400">
                                                    {{ ucfirst($purchase->trip->tripstatus) }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-1 text-xs font-medium text-amber-800">
                                                Unassigned
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-3 py-2 min-w-[150px] text-gray-700">
                                        {{ $legLabel ?? '—' }}
                                    </td>

                                    <td class="px-3 py-2 min-w-[180px] text-gray-700">
                                        @if($purchase->fuelStop)
                                            <div>{{ $purchase->fuelStop->stopname }}</div>

                                            @if($purchase->fuelStop->place)
                                                <div class="mt-0.5 text-xs text-gray-400">
                                                    {{ $purchase->fuelStop->place->placename }}
                                                </div>
                                            @endif
                                        @elseif($purchase->place)
                                            {{ $purchase->place->placename }}
                                        @else
                                            <span class="text-gray-400 text-xs">—</span>
                                        @endif
                                    </td>

                                    <td class="px-3 py-2 whitespace-nowrap text-gray-700">
                                        {{ $fuelTypes[$purchase->fueltype] ?? $purchase->fueltype }}
                                    </td>

                                    <td class="px-3 py-2 whitespace-nowrap text-right text-gray-700">
                                        {{ number_format((float) $purchase->litres, 3) }}
                                    </td>

                                    <td class="px-3 py-2 whitespace-nowrap text-right text-gray-700">
                                        {{ number_format((float) $purchase->priceperlitre, 4) }}
                                    </td>

                                    <td class="px-3 py-2 whitespace-nowrap text-right font-medium text-gray-900">
                                        ${{ number_format((float) $purchase->fueltotal, 2) }}
                                    </td>

                                    <td class="px-3 py-2 whitespace-nowrap text-right text-gray-700">
                                        @if($extraCosts > 0)
                                            ${{ number_format($extraCosts, 2) }}

                                            @if($purchase->servicecosts || $purchase->repairscost)
                                                <div class="mt-0.5 text-xs text-gray-400">
                                                    @if((float) $purchase->servicecosts > 0)
                                                        Service ${{ number_format((float) $purchase->servicecosts, 2) }}
                                                    @endif

                                                    @if((float) $purchase->servicecosts > 0 && (float) $purchase->repairscost > 0)
                                                        ·
                                                    @endif

                                                    @if((float) $purchase->repairscost > 0)
                                                        Repairs ${{ number_format((float) $purchase->repairscost, 2) }}
                                                    @endif
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-gray-400 text-xs">—</span>
                                        @endif
                                    </td>

                                    <td class="px-3 py-2 whitespace-nowrap text-right text-gray-700">
                                        @if($purchase->odometerkm !== null)
                                            {{ number_format((float) $purchase->odometerkm, 1) }}
                                        @else
                                            <span class="text-gray-400 text-xs">—</span>
                                        @endif
                                    </td>

                                    <td class="px-3 py-2 whitespace-nowrap text-right text-gray-700">
                                        <div class="inline-flex items-center justify-end gap-2">
                                            <a href="{{ route('fuel-purchases.edit', [
                                                'fuelPurchase' => $purchase,
                                                'return_to' => $returnTo,
                                            ]) }}"
                                               class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs">
                                                Edit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-3 py-8 text-center text-sm text-gray-500">
                                        <div>No fuel purchases found.</div>

                                        <a href="{{ route('fuel-purchases.create') }}"
                                           class="mt-3 inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                            Record First Fuel Purchase
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($purchases->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $purchases->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tripSelect = document.getElementById('tripid');
    const legSelect = document.getElementById('triplegid');

    if (!tripSelect || !legSelect) {
        return;
    }

    const legOptions = Array.from(
        legSelect.querySelectorAll('option[data-trip-id]')
    );

    const filterLegOptions = () => {
        const selectedTripId = tripSelect.value;
        const currentLegId = legSelect.value;

        legOptions.forEach((option) => {
            const isVisible = selectedTripId === ''
                || option.dataset.tripId === selectedTripId;

            option.hidden = !isVisible;
            option.disabled = !isVisible;
        });

        if (currentLegId !== '') {
            const selectedOption = legSelect.querySelector(
                'option[value="' + CSS.escape(currentLegId) + '"]'
            );

            if (!selectedOption || selectedOption.disabled) {
                legSelect.value = '';
            }
        }
    };

    tripSelect.addEventListener('change', () => {
        legSelect.value = '';
        filterLegOptions();
    });

    filterLegOptions();
});
</script>