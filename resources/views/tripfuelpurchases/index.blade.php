@php
    $showCreate = request()->boolean('show_create') || session('show_create') || $errors->any();
@endphp

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
                <p class="mt-1 text-sm text-gray-500">
                    Status: {{ ucfirst($trip->tripstatus) }} ·
                    Start: {{ optional($trip->startdate)->format('d M Y') ?? '—' }} ·
                    End: {{ optional($trip->enddate)->format('d M Y') ?? '—' }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('trips.fuel-purchases.index', array_merge(['trip' => $trip->id], request()->query(), ['show_create' => 1])) }}"
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                    New Purchase
                </a>

                <a href="{{ route('trips.edit', ['trip' => $trip, 'tab' => 'workflow']) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                    Back to Trip
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">
            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('trips.fuel-purchases.index', $trip) }}">
                    <div class="grid grid-cols-1 md:grid-cols-4 xl:grid-cols-6 gap-4">
                        <div>
                            <label for="trip_leg_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Trip Leg
                            </label>
                            <select name="trip_leg_id"
                                    id="trip_leg_id"
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
                                    <option value="{{ $leg->id }}" @selected((string) request('trip_leg_id') === (string) $leg->id)>
                                        {{ $legLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="fuel_stop_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Fuel Stop
                            </label>
                            <select name="fuel_stop_id"
                                    id="fuel_stop_id"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All fuel stops</option>
                                @foreach($fuelStops as $fuelStop)
                                    <option value="{{ $fuelStop->id }}" @selected((string) request('fuel_stop_id') === (string) $fuelStop->id)>
                                        {{ $fuelStop->stopname }}
                                        @if($fuelStop->place)
                                            – {{ $fuelStop->place->placename }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="fueltype_filter" class="block text-sm font-medium text-gray-700 mb-1">
                                Fuel Type
                            </label>
                            <select name="fueltype"
                                    id="fueltype_filter"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All fuel types</option>
                                @foreach(($fuelTypes ?? []) as $value => $label)
                                    <option value="{{ $value }}" @selected(request('fueltype') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2 flex items-end gap-2">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                Apply Filters
                            </button>

                            <a href="{{ route('trips.fuel-purchases.index', $trip) }}"
                               class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 text-sm">
                                Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            @if($showCreate)
                <form method="POST"
                      action="{{ route('trips.fuel-purchases.store', $trip) }}"
                      id="fuel-purchase-create-form"
                      class="space-y-6">
                    @csrf

                    @include('tripfuelpurchases._form', [
                        'trip' => $trip,
                        'fuelPurchase' => null,
                        'tripLegs' => $tripLegs,
                        'fuelStops' => $fuelStops,
                        'places' => $places,
                        'fuelTypes' => $fuelTypes,
                        'isCreate' => true,
                        'returnTo' => route('trips.fuel-purchases.index', $trip),
                    ])
                </form>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
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
                                        {{ optional($purchase->purchasedate)->format('Y-m-d') ?? '—' }}
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-gray-700">
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
                                                ?: ($leg ? ($leg->legnumber ? 'Leg ' . $leg->legnumber : 'Leg #' . $leg->id) : null);
                                        @endphp
                                        {{ $legLabel ?? '—' }}
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
                                        {{ $fuelTypes[$purchase->fueltype] ?? $purchase->fueltype }}
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
                                               class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs">
                                                Edit
                                            </a>
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

                @if ($purchases->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $purchases->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>