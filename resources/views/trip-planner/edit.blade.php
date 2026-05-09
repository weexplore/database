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
    const destinationItemSelect = document.getElementById('destinationitemid');

    if (!placeSelect || !destinationSelect || !destinationItemSelect) {
        return;
    }

    const destinationOptions = Array.from(destinationSelect.options).map(option => ({
        value: option.value,
        text: option.text,
        placeId: option.dataset.placeId || '',
        selected: option.selected,
    }));

    const destinationItemOptions = Array.from(destinationItemSelect.options).map(option => ({
        value: option.value,
        text: option.text,
        destinationId: option.dataset.destinationId || '',
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

            if (item.destinationId) {
                option.dataset.destinationId = item.destinationId;
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

        let filtered = destinationOptions.filter(option => option.value === '');

        filtered = filtered.concat(
            destinationOptions.filter(option => {
                if (!option.value) return false;
                if (!selectedPlaceId) return true;
                return String(option.placeId) === String(selectedPlaceId);
            })
        );

        const destinationStillValid = filtered.some(option => String(option.value) === String(currentDestinationId));

        rebuildSelect(
            destinationSelect,
            filtered.filter(option => option.value !== ''),
            'None',
            destinationStillValid ? currentDestinationId : ''
        );

        if (!destinationStillValid) {
            destinationSelect.value = '';
        }
    }

    function filterDestinationItems() {
        const selectedPlaceId = placeSelect.value;
        const selectedDestinationId = destinationSelect.value;
        const currentDestinationItemId = destinationItemSelect.value;

        let filtered = destinationItemOptions.filter(option => option.value === '');

        filtered = filtered.concat(
            destinationItemOptions.filter(option => {
                if (!option.value) return false;

                if (selectedDestinationId) {
                    return String(option.destinationId) === String(selectedDestinationId);
                }

                if (selectedPlaceId) {
                    return String(option.placeId) === String(selectedPlaceId);
                }

                return true;
            })
        );

        const itemStillValid = filtered.some(option => String(option.value) === String(currentDestinationItemId));

        rebuildSelect(
            destinationItemSelect,
            filtered.filter(option => option.value !== ''),
            'None',
            itemStillValid ? currentDestinationItemId : ''
        );

        if (!itemStillValid) {
            destinationItemSelect.value = '';
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
        filterDestinationItems();
    });

    destinationSelect.addEventListener('change', function () {
        syncPlaceFromDestination();
        filterDestinations();
        filterDestinationItems();
    });

    filterDestinations();
    filterDestinationItems();
});
</script>
</x-app-layout>