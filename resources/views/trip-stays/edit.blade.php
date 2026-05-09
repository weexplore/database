<x-app-layout>
    @php
        $returnTo = request('return_to', route('trips.stays.index', $trip));
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Trip Stay
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $trip->tripname }}
                </p>
            </div>

            <a href="{{ $returnTo }}"
               class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                Back to Trip Stays
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <form method="POST"
                  action="{{ route('trips.stays.update', ['trip' => $trip, 'tripStay' => $tripStay]) }}"
                  id="trip-stay-edit-form"
                  class="space-y-6">
                @csrf
                @method('PUT')

                <input type="hidden" name="return_to" value="{{ $returnTo }}">

                @php
                    $selectedTripLegId = $selectedTripLegId ?? null;
                    $selectedPlaceId = $selectedPlaceId ?? null;
                    $selectedTravelledFromPlaceId = $selectedTravelledFromPlaceId ?? null;
                @endphp

                @include('trip-stays._form', [
                    'trip' => $trip,
                    'tripStay' => $tripStay,
                    'places' => $places,
                    'tripLegs' => $tripLegs,
                    'stayTypes' => $stayTypes,
                    'selectedTripLegId' => $selectedTripLegId,
                    'selectedPlaceId' => $selectedPlaceId,
                    'selectedTravelledFromPlaceId' => $selectedTravelledFromPlaceId,
                    'isCreate' => false,
                    'returnTo' => $returnTo,
                ])
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('trip-stay-edit-form');
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