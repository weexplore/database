<x-app-layout>
    @php
        $returnTo = $returnTo ?? route('trips.planner.index', $trip);
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Planning Item
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $tripPlanItem->display_title }}
                </p>
            </div>

            <a href="{{ $returnTo }}"
               class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                Back
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">
            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <div class="xl:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg">
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
                        ])
                    </form>
                </div>

                <div class="space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900">Record summary</h3>
                        </div>
                        <div class="p-4">
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between gap-4">
                                    <dt class="text-gray-500">ID</dt>
                                    <dd class="text-gray-900">{{ $tripPlanItem->id }}</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-gray-500">Sequence</dt>
                                    <dd class="text-gray-900">{{ $tripPlanItem->sequence_no ?? '—' }}</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-gray-500">Type</dt>
                                    <dd class="text-gray-900">{{ $planTypeOptions[$tripPlanItem->plantype] ?? $tripPlanItem->plantype }}</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-gray-500">Created</dt>
                                    <dd class="text-gray-900">{{ optional($tripPlanItem->createdat)->format('d M Y') ?: '—' }}</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-gray-500">Updated</dt>
                                    <dd class="text-gray-900">{{ optional($tripPlanItem->updatedat)->format('d M Y') ?: '—' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900">Delete</h3>
                        </div>
                        <div class="p-4">
                            <form method="POST"
                                  action="{{ route('trips.planner.destroy', ['trip' => $trip->id, 'tripPlanItem' => $tripPlanItem->id]) }}"
                                  onsubmit="return confirm('Delete this planning item?');">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="return_to" value="{{ $returnTo }}">

                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                                    Delete Planning Item
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Next stage</h3>
                        <p class="mt-2 text-sm text-gray-700">
                            Once your sequence feels right, the next enhancement is to generate or link Trip Legs and Trip Stays from these planning items.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
document.addEventListener('DOMContentLoaded', function () {
    const placeSelect = document.getElementById('placeid');
    const destinationSelect = document.getElementById('destinationid');
    const toggleAll = document.getElementById('related_toggle_all');
    const relatedRows = Array.from(document.querySelectorAll('.related-destination-item-row'));

    if (!placeSelect || !destinationSelect) {
        return;
    }

    const destinationOptions = Array.from(destinationSelect.options).map(option => ({
        value: option.value,
        text: option.text,
        placeId: option.dataset.placeId || '',
        selected: option.selected,
    }));

    function rebuildSelect(select, options, placeholder = 'None', selectedValue = '') {
        select.innerHTML = '';

        const placeholderOption = document.createElement('option');
        placeholderOption.value = '';
        placeholderOption.textContent = placeholder;
        select.appendChild(placeholderOption);

        options.forEach(item => {
            const option = document.createElement('option');
            option.value = item.value;
            option.textContent = item.text;

            if (item.placeId) {
                option.dataset.placeId = item.placeId;
            }

            if (String(item.value) === String(selectedValue)) {
                option.selected = true;
            }

            select.appendChild(option);
        });
    }

    function filterDestinations() {
        const selectedPlaceId = placeSelect.value;
        const currentDestinationId = destinationSelect.value;

        const filtered = destinationOptions.filter(option => {
            if (!option.value) return false;
            if (!selectedPlaceId) return true;
            return String(option.placeId) === String(selectedPlaceId);
        });

        const destinationStillValid = filtered.some(
            option => String(option.value) === String(currentDestinationId)
        );

        rebuildSelect(
            destinationSelect,
            filtered,
            'None',
            destinationStillValid ? currentDestinationId : ''
        );

        if (!destinationStillValid) {
            destinationSelect.value = '';
        }
    }

    function filterRelatedDestinationItems() {
        const selectedPlaceId = placeSelect.value;
        const selectedDestinationId = destinationSelect.value;

        relatedRows.forEach(row => {
            const rowPlaceId = row.dataset.placeId || '';
            const rowDestinationId = row.dataset.destinationId || '';

            let visible = true;

            if (selectedDestinationId) {
                visible = String(rowDestinationId) === String(selectedDestinationId);
            } else if (selectedPlaceId) {
                visible = String(rowPlaceId) === String(selectedPlaceId);
            }

            row.style.display = visible ? '' : 'none';

            const checkbox = row.querySelector('.related-destination-item-checkbox');
            if (checkbox && !visible) {
                checkbox.checked = false;
            }
        });

        if (toggleAll) {
            toggleAll.checked = false;
        }
    }

    function syncPlaceFromDestination() {
        const selectedDestination = destinationSelect.options[destinationSelect.selectedIndex];
        if (!selectedDestination) return;

        const destinationPlaceId = selectedDestination.dataset.placeId || '';
        if (destinationPlaceId && !placeSelect.value) {
            placeSelect.value = destinationPlaceId;
        }
    }

    placeSelect.addEventListener('change', function () {
        filterDestinations();
        filterRelatedDestinationItems();
    });

    destinationSelect.addEventListener('change', function () {
        syncPlaceFromDestination();
        filterDestinations();
        filterRelatedDestinationItems();
    });

    if (toggleAll) {
        toggleAll.addEventListener('change', function () {
            const visibleCheckboxes = relatedRows
                .filter(row => row.style.display !== 'none')
                .map(row => row.querySelector('.related-destination-item-checkbox'))
                .filter(Boolean);

            visibleCheckboxes.forEach(checkbox => {
                checkbox.checked = toggleAll.checked;
            });
        });
    }

    filterDestinations();
    filterRelatedDestinationItems();
});
</script>
</x-app-layout>