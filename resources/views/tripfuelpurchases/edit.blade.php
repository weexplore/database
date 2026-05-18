@php
    $returnTo = $returnTo ?? route('trips.fuel-purchases.index', $trip);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Fuel Purchase
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

            <a href="{{ $returnTo }}"
               class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                Back to Purchases
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">
            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <form method="POST"
                  action="{{ route('trips.fuel-purchases.update', [$trip, $fuelPurchase]) }}"
                  id="fuel-purchase-edit-form"
                  class="space-y-6">
                @csrf
                @method('PUT')

                @include('tripfuelpurchases._form', [
                    'trip' => $trip,
                    'fuelPurchase' => $fuelPurchase,
                    'tripLegs' => $tripLegs,
                    'fuelStops' => $fuelStops,
                    'places' => $places,
                    'fuelTypes' => $fuelTypes,
                    'isCreate' => false,
                    'returnTo' => $returnTo,
                ])
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg border border-red-200">
                <div class="p-6 flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-red-700">Delete Fuel Purchase</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            This action will permanently remove this fuel purchase record.
                        </p>
                    </div>

                    <form method="POST"
                          action="{{ route('trips.fuel-purchases.destroy', [$trip, $fuelPurchase]) }}"
                          onsubmit="return confirm('Delete this fuel purchase?');">
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