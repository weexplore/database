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
            </div>

            <a href="{{ route('trips.fuel-purchases.index', $trip) }}"
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

                {{-- Core details --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Core details</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Link this purchase to a leg, fuel stop, and optional place.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="purchasedate" class="block text-sm font-medium text-gray-700 mb-1">
                                Purchase Date
                            </label>
                            <input type="date"
                                   name="purchasedate"
                                   id="purchasedate"
                                   value="{{ old('purchasedate', optional($fuelPurchase->purchasedate)->format('Y-m-d')) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                   required>
                        </div>

                        <div>
                            <label for="triplegid" class="block text-sm font-medium text-gray-700 mb-1">
                                Trip Leg (optional)
                            </label>
                            <select name="triplegid"
                                    id="triplegid"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">No leg link</option>
                                @foreach($tripLegs as $leg)
                                    <option value="{{ $leg->id }}"
                                        @selected((int) old('triplegid', $fuelPurchase->triplegid) === $leg->id)>
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
                            <label for="fuelstopid" class="block text-sm font-medium text-gray-700 mb-1">
                                Fuel Stop (optional)
                            </label>
                            <select name="fuelstopid"
                                    id="fuelstopid"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">No linked fuel stop</option>
                                @foreach($fuelStops as $fuelStop)
                                    <option value="{{ $fuelStop->id }}"
                                        @selected((int) old('fuelstopid', $fuelPurchase->fuelstopid) === $fuelStop->id)>
                                        {{ $fuelStop->stopname }}
                                        @if($fuelStop->place)
                                            – {{ $fuelStop->place->placename }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="placeid" class="block text-sm font-medium text-gray-700 mb-1">
                                Place (optional fallback)
                            </label>
                            <select name="placeid"
                                    id="placeid"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">No fallback place</option>
                                @foreach($places as $place)
                                    <option value="{{ $place->id }}"
                                        @selected((int) old('placeid', $fuelPurchase->placeid) === $place->id)>
                                        {{ $place->placename }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                Use when no reusable fuel stop has been created yet.
                            </p>
                        </div>

                        <div>
                            <label for="fueltype" class="block text-sm font-medium text-gray-700 mb-1">
                                Fuel Type
                            </label>
                            <select name="fueltype"
                                    id="fueltype"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                    required>
                                <option value="">Select fuel type</option>
                                @foreach($fuelTypes as $value => $label)
                                    <option value="{{ $value }}"
                                        @selected(old('fueltype', $fuelPurchase->fueltype) === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="receiptreference" class="block text-sm font-medium text-gray-700 mb-1">
                                Receipt Reference
                            </label>
                            <input type="text"
                                   name="receiptreference"
                                   id="receiptreference"
                                   value="{{ old('receiptreference', $fuelPurchase->receiptreference) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                   maxlength="150">
                        </div>
                    </div>
                </div>

                {{-- Quantities and costs --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Quantities and costs</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label for="litres" class="block text-sm font-medium text-gray-700 mb-1">
                                Litres
                            </label>
                            <input type="number"
                                   step="0.001"
                                   min="0"
                                   name="litres"
                                   id="litres"
                                   value="{{ old('litres', $fuelPurchase->litres) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                   required>
                        </div>

                        <div>
                            <label for="priceperlitre" class="block text-sm font-medium text-gray-700 mb-1">
                                Price per Litre
                            </label>
                            <input type="number"
                                   step="0.0001"
                                   min="0"
                                   name="priceperlitre"
                                   id="priceperlitre"
                                   value="{{ old('priceperlitre', $fuelPurchase->priceperlitre) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                   required>
                        </div>

                        <div>
                            <label for="fueltotal" class="block text-sm font-medium text-gray-700 mb-1">
                                Fuel Total
                            </label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="fueltotal"
                                   id="fueltotal"
                                   value="{{ old('fueltotal', $fuelPurchase->fueltotal) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                   required>
                        </div>

                        <div>
                            <label for="servicecosts" class="block text-sm font-medium text-gray-700 mb-1">
                                Service Costs
                            </label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="servicecosts"
                                   id="servicecosts"
                                   value="{{ old('servicecosts', $fuelPurchase->servicecosts) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div>
                            <label for="repairscost" class="block text-sm font-medium text-gray-700 mb-1">
                                Repairs Cost
                            </label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="repairscost"
                                   id="repairscost"
                                   value="{{ old('repairscost', $fuelPurchase->repairscost) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>
                    </div>
                </div>

                {{-- Distances and notes --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Distances and notes</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="odometerkm" class="block text-sm font-medium text-gray-700 mb-1">
                                Odometer (km)
                            </label>
                            <input type="number"
                                   step="0.1"
                                   min="0"
                                   name="odometerkm"
                                   id="odometerkm"
                                   value="{{ old('odometerkm', $fuelPurchase->odometerkm) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div>
                            <label for="distancesincelastfillkm" class="block text-sm font-medium text-gray-700 mb-1">
                                Distance since last fill (km)
                            </label>
                            <input type="number"
                                   step="0.1"
                                   min="0"
                                   name="distancesincelastfillkm"
                                   id="distancesincelastfillkm"
                                   value="{{ old('distancesincelastfillkm', $fuelPurchase->distancesincelastfillkm) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                            Notes
                        </label>
                        <textarea name="notes"
                                  id="notes"
                                  rows="4"
                                  class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('notes', $fuelPurchase->notes) }}</textarea>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('trips.fuel-purchases.index', $trip) }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                        Cancel
                    </a>

                    <button type="submit"
                            class="inline-flex items-center px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        Save Fuel Purchase
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('fuel-purchase-edit-form');
            if (!form) return;

            let isDirty = false;
            let isSubmitting = false;

            form.querySelectorAll('input, select, textarea').forEach((element) => {
                element.addEventListener('change', () => isDirty = true);
                element.addEventListener('input', () => isDirty = true);
            });

            form.addEventListener('submit', function () {
                isSubmitting = true;
                isDirty = false;
            });

            window.addEventListener('beforeunload', function (event) {
                if (isDirty && !isSubmitting) {
                    event.preventDefault();
                    event.returnValue = '';
                }
            });
        });
    </script>
</x-app-layout>