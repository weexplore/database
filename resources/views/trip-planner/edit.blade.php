<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Planning Item
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $trip->tripname }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ $returnTo }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded text-sm hover:bg-gray-300">
                    Back to Planner
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">
            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Planning item details</h3>
                    <p class="mt-1 text-xs text-gray-500">
                        Update the planning item, linked place and destination details, and optional related destination items.
                    </p>
                </div>

                <form method="POST"
                      action="{{ route('trips.planner.update', ['trip' => $trip->id, 'tripPlanItem' => $tripPlanItem->id]) }}"
                      class="p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    @include('trip-planner._form', [
                        'tripPlanItem' => $tripPlanItem,
                        'trip' => $trip,
                        'places' => $places,
                        'destinations' => $destinations,
                        'destinationItems' => $destinationItems,
                        'tripLegs' => $tripLegs,
                        'tripStays' => $tripStays,
                        'planTypeOptions' => $planTypeOptions,
                        'stayTypeOptions' => $stayTypeOptions,
                        'returnTo' => $returnTo,
                        'existingSelectedDestinationItemIds' => $existingSelectedDestinationItemIds ?? [],
                    ])
                </form>
            </div>

            <form method="POST"
                  action="{{ route('trips.planner.add-nearby-place', ['trip' => $trip->id, 'tripPlanItem' => $tripPlanItem->id]) }}"
                  id="nearby-add-after-form"
                  class="hidden">
                @csrf
                <input type="hidden" name="placeid" id="nearby_add_after_placeid">
                <input type="hidden" name="returnto" value="{{ request()->fullUrl() }}">
            </form>

            <form method="POST"
                  action="{{ route('trips.planner.destroy', ['trip' => $trip->id, 'tripPlanItem' => $tripPlanItem->id]) }}"
                  id="delete-trip-plan-item-form"
                  class="hidden">
                @csrf
                @method('DELETE')
                <input type="hidden" name="return_to" value="{{ $returnTo }}">
            </form>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Danger zone</h3>
                </div>

                <div class="p-6">
                    <button type="button"
                            id="delete-trip-plan-item-button"
                            class="inline-flex items-center px-4 py-2 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm">
                        Delete Planning Item
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const addAfterForm = document.getElementById('nearby-add-after-form');
            const addAfterPlaceIdInput = document.getElementById('nearby_add_after_placeid');
            const nearbyResults = document.getElementById('nearby_places_results');
            const deleteButton = document.getElementById('delete-trip-plan-item-button');
            const deleteForm = document.getElementById('delete-trip-plan-item-form');

            if (nearbyResults && addAfterForm && addAfterPlaceIdInput) {
                nearbyResults.addEventListener('click', function (event) {
                    const button = event.target.closest('.nearby-add-after');

                    if (!button) {
                        return;
                    }

                    event.preventDefault();

                    const placeId = button.dataset.placeId || '';
                    if (!placeId) {
                        return;
                    }

                    addAfterPlaceIdInput.value = placeId;
                    addAfterForm.submit();
                });
            }

            if (deleteButton && deleteForm) {
                deleteButton.addEventListener('click', function () {
                    if (!confirm('Delete this planning item? This cannot be undone.')) {
                        return;
                    }

                    deleteForm.submit();
                });
            }
        });
    </script>

@pushOnce('scripts')
    @include('partials.forms.markdown-field-scripts')
@endPushOnce
</x-app-layout>

