@php
    $showCreate = $showCreate ?? (request()->boolean('show_create') || $errors->any());
@endphp

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
                <p class="mt-1 text-sm text-gray-500">
                    Status: {{ ucfirst($trip->tripstatus) }} ·
                    Start: {{ optional($trip->startdate)->format('d M Y') ?? '—' }} ·
                    End: {{ optional($trip->enddate)->format('d M Y') ?? '—' }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('trips.fuel-estimates.index', array_merge(['trip' => $trip->id], request()->query(), ['show_create' => 1])) }}"
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                    Add Trip Fuel Estimate
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
                <form method="GET" action="{{ route('trips.fuel-estimates.index', $trip) }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4">
                        <div>
                            <label for="filter_triplegid" class="block text-sm font-medium text-gray-700 mb-1">Trip Leg</label>
                            <select name="triplegid"
                                    id="filter_triplegid"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All legs</option>
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
                                    <option value="{{ $tripLeg->id }}" @selected((string) request('triplegid') === (string) $tripLeg->id)>
                                        {{ $legLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="filter_fueltype" class="block text-sm font-medium text-gray-700 mb-1">Fuel Type</label>
                            <select name="fueltype"
                                    id="filter_fueltype"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All fuel types</option>
                                @foreach ($fuelTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(request('fueltype') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="filter_fuelstopid" class="block text-sm font-medium text-gray-700 mb-1">Fuel Stop</label>
                            <select name="fuelstopid"
                                    id="filter_fuelstopid"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
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
                            <input type="date"
                                   name="date_from"
                                   id="date_from"
                                   value="{{ request('date_from') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Estimate To</label>
                            <input type="date"
                                   name="date_to"
                                   id="date_to"
                                   value="{{ request('date_to') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                Filter
                            </button>

                            <a href="{{ route('trips.fuel-estimates.index', $trip) }}"
                               class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            @if($showCreate)
                <form method="POST"
                      action="{{ route('trips.fuel-estimates.store', $trip) }}"
                      id="trip-fuel-estimate-create-form"
                      class="space-y-6">
                    @csrf

                    @include('tripfuelestimates._form', [
                        'trip' => $trip,
                        'fuelEstimate' => null,
                        'tripLegs' => $tripLegs,
                        'fuelStops' => $fuelStops,
                        'places' => $places,
                        'fuelTypes' => $fuelTypes,
                        'sourceObservations' => $sourceObservations,
                        'isCreate' => true,
                        'returnTo' => route('trips.fuel-estimates.index', $trip),
                    ])
                </form>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Date</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Leg</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Fuel Stop</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Place</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Fuel Type</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Price/L</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Distance</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Litres</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Total</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($tripFuelEstimates as $fuelEstimate)
                                <tr>
                                    <td class="px-4 py-3 align-top text-gray-700">
                                        {{ optional($fuelEstimate->estimatedate)->format('Y-m-d') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700">
                                        @php
                                            $leg = $fuelEstimate->tripLeg;
                                            $legLabel =
                                                $leg?->title
                                                ?: trim(
                                                    collect([
                                                        optional($leg?->fromPlace)->placename,
                                                        optional($leg?->toPlace)->placename,
                                                    ])->filter()->implode(' → ')
                                                )
                                                ?: ($leg ? 'Leg ' . ($leg->legnumber ?? $leg->id) : '—');
                                        @endphp
                                        {{ $legLabel }}
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
                                               class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs">
                                                Edit
                                            </a>
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
</x-app-layout>