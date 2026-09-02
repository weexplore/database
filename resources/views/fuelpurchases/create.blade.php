@php
    $returnTo = $returnTo ?? route('fuel-purchases.index');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    New Fuel Purchase
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Record an actual fuel purchase, service cost, or repair cost.
                </p>

                <p class="mt-1 text-sm text-gray-500">
                    Select a trip when known, or leave it unassigned and allocate it later.
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
                  action="{{ route('fuel-purchases.store') }}"
                  id="fuel-purchase-create-form"
                  class="space-y-6">
                @csrf

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
                    'selectedTripId' => $selectedTripId ?? null,
                    'fixedTrip' => null,
                    'isCreate' => true,
                    'returnTo' => $returnTo,
                ])
            </form>
        </div>
    </div>
</x-app-layout>