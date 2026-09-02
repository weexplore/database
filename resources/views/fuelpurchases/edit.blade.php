@php
    $returnTo = $returnTo ?? route('fuel-purchases.index');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Fuel Purchase
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Update the receipt details and optionally assign it to a trip and trip leg.
                </p>

                <p class="mt-1 text-sm text-gray-500">
                    Purchase date:
                    {{ optional($fuelPurchase->purchasedate)->format('d M Y') ?? '—' }}
                    · Fuel total:
                    ${{ number_format((float) $fuelPurchase->fueltotal, 2) }}
                    ·
                    @if($fuelPurchase->trip)
                        Trip: {{ $fuelPurchase->trip->tripname }}
                    @else
                        <span class="text-amber-700">Unassigned</span>
                    @endif
                </p>
            </div>

            <a href="{{ $returnTo }}"
               class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                Back to Purchases
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">
            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <form method="POST"
                  action="{{ route('fuel-purchases.update', $fuelPurchase) }}"
                  id="fuel-purchase-edit-form"
                  class="space-y-6">
                @csrf
                @method('PUT')

                <input type="hidden"
                       name="return_to"
                       value="{{ $returnTo }}">

                @include('fuelpurchases._form', [
                    'fuelPurchase' => $fuelPurchase,
                    'trips' => $trips,
                    'tripLegs' => $tripLegs,
                    'fuelStops' => $fuelStops,
                    'places' => $places,
                    'fuelTypes' => $fuelTypes,
                    'selectedTripId' => $selectedTripId ?? $fuelPurchase->tripid,
                    'fixedTrip' => null,
                    'isCreate' => false,
                    'returnTo' => $returnTo,
                ])
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg border border-red-200">
                <div class="p-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-red-700">
                            Delete Fuel Purchase
                        </h3>

                        <p class="mt-1 text-sm text-gray-600">
                            This permanently removes this fuel purchase record, including its trip allocation and receipt details.
                        </p>
                    </div>

                    <form method="POST"
                          action="{{ route('fuel-purchases.destroy', $fuelPurchase) }}"
                          onsubmit="return confirm('Delete this fuel purchase permanently?');">
                        @csrf
                        @method('DELETE')

                        <input type="hidden"
                               name="return_to"
                               value="{{ $returnTo }}">

                        <button type="submit"
                                class="inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>