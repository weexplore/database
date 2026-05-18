@php
    $returnTo = $returnTo ?? route('trips.fuel-estimates.index', $trip);
@endphp

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
                <p class="mt-1 text-sm text-gray-500">
                    Status: {{ ucfirst($trip->tripstatus) }} ·
                    Start: {{ optional($trip->startdate)->format('d M Y') ?? '—' }} ·
                    End: {{ optional($trip->enddate)->format('d M Y') ?? '—' }}
                </p>
            </div>

            <a href="{{ $returnTo }}"
               class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                Back to Estimates
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">
            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <form method="POST"
                  action="{{ route('trips.fuel-estimates.update', [$trip, $fuelEstimate]) }}"
                  id="trip-fuel-estimate-edit-form"
                  class="space-y-6">
                @csrf
                @method('PUT')

                @include('tripfuelestimates._form', [
                    'trip' => $trip,
                    'fuelEstimate' => $fuelEstimate,
                    'tripLegs' => $tripLegs,
                    'fuelStops' => $fuelStops,
                    'places' => $places,
                    'fuelTypes' => $fuelTypes,
                    'sourceObservations' => $sourceObservations,
                    'isCreate' => false,
                    'returnTo' => $returnTo,
                ])
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg border border-red-200">
                <div class="p-6 flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-red-700">Delete Trip Fuel Estimate</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            This action will permanently remove this trip fuel estimate from the trip plan.
                        </p>
                    </div>

                    <form method="POST"
                          action="{{ route('trips.fuel-estimates.destroy', [$trip, $fuelEstimate]) }}"
                          onsubmit="return confirm('Delete this trip fuel estimate?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>