<x-app-layout>
    <x-slot name="header">
<div class="flex items-center gap-2">
    <a href="{{ route('trips.edit', $trip) }}"
       class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
        Back to Trip
    </a>

    <a href="{{ route('trips.planner.generate', ['trip' => $trip->id, 'return_to' => url()->full()]) }}"
       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm">
        Generate Legs & Stays
    </a>

    <a href="{{ route('trips.planner.create', ['trip' => $trip->id]) }}"
       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
        Add Planning Item
    </a>
</div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">
            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            {{-- Planning sequence card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Planning Sequence</h3>
                        <p class="mt-1 text-xs text-gray-500">
                            Review the current order first, then adjust sequence and dates inline.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('trips.planner.renumber', $trip) }}">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center px-3 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-xs">
                                Renumber Sequentially
                            </button>
                        </form>

                        <div class="text-xs text-gray-500">
                            {{ $planItems->count() }} item{{ $planItems->count() === 1 ? '' : 's' }}
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('trips.planner.bulk-update', $trip) }}">
                    @csrf

                    <div class="p-6">
                        @if($planItems->isEmpty())
                            <p class="text-sm text-gray-500">
                                No planning items have been added yet.
                            </p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Seq</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Type</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Title</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Linked record</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Start date</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">End date</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Flags</th>
                                            <th class="px-3 py-2 text-right font-semibold text-gray-600">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        @foreach($planItems as $item)
                                            @php
                                                $linkedLabel = $item->destinationItem?->itemname
                                                    ?? $item->destination?->destinationname
                                                    ?? $item->place?->placename
                                                    ?? '—';

                                                $flags = collect([
                                                    $item->isrouteanchor ? 'Route anchor' : null,
                                                    $item->isovernight ? 'Overnight' : null,
                                                    $item->isstaytarget ? 'Stay target' : null,
                                                ])->filter()->implode(', ');
                                            @endphp

                                            <tr>
                                                <td class="px-3 py-3 align-top">
                                                    <input type="hidden" name="items[{{ $item->id }}][id]" value="{{ $item->id }}">
                                                    <input type="number"
                                                           min="1"
                                                           name="items[{{ $item->id }}][sequence_no]"
                                                           value="{{ old("items.{$item->id}.sequence_no", $item->sequence_no) }}"
                                                           class="w-24 rounded-md border-gray-300 shadow-sm text-sm">
                                                </td>

                                                <td class="px-3 py-3 align-top text-gray-700">
                                                    {{ $planTypeOptions[$item->plantype] ?? $item->plantype }}
                                                </td>

                                                <td class="px-3 py-3 align-top">
                                                    <div class="font-medium text-gray-900">
                                                        {{ $item->display_title }}
                                                    </div>
                                                    @if($item->notes)
                                                        <div class="mt-1 text-xs text-gray-500 line-clamp-2">
                                                            {{ $item->notes }}
                                                        </div>
                                                    @endif
                                                </td>

                                                <td class="px-3 py-3 align-top text-gray-700">
                                                    {{ $linkedLabel }}
                                                </td>

                                                <td class="px-3 py-3 align-top">
                                                    <input type="date"
                                                           name="items[{{ $item->id }}][planneddate]"
                                                           value="{{ old("items.{$item->id}.planneddate", optional($item->planneddate)->format('Y-m-d')) }}"
                                                           class="w-40 rounded-md border-gray-300 shadow-sm text-sm">
                                                </td>

                                                <td class="px-3 py-3 align-top">
                                                    <input type="date"
                                                           name="items[{{ $item->id }}][plannedenddate]"
                                                           value="{{ old("items.{$item->id}.plannedenddate", optional($item->plannedenddate)->format('Y-m-d')) }}"
                                                           class="w-40 rounded-md border-gray-300 shadow-sm text-sm">
                                                </td>

                                                <td class="px-3 py-3 align-top text-xs text-gray-500">
                                                    {{ $flags ?: '—' }}
                                                </td>

                                                <td class="px-3 py-3 align-top">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <a href="{{ route('trips.planner.edit', [
                                                                'trip' => $trip->id,
                                                                'tripPlanItem' => $item->id,
                                                                'return_to' => url()->full(),
                                                            ]) }}"
                                                        class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-xs">
                                                            Edit
                                                        </a>

                                                        <button type="button"
                                                                class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200 text-xs js-delete-trip-plan-item"
                                                                data-name="{{ $item->title ?: 'this planning item' }}"
                                                                data-action="{{ route('trips.planner.destroy', ['trip' => $trip->id, 'tripPlanItem' => $item->id]) }}"
                                                                data-return-to="{{ url()->full() }}">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4 flex justify-end gap-3">
                                <a href="{{ route('trips.planner.create', ['trip' => $trip->id]) }}"
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                    Add Planning Item
                                </a>

                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                    Update Planning Order
                                </button>
                            </div>
                        @endif
                    </div>
                </form>
            </div>

            <form method="POST" id="delete-trip-plan-item-form" class="hidden">
                @csrf
                @method('DELETE')
                <input type="hidden" name="return_to" id="delete-trip-plan-item-return-to" value="">
            </form>

            {{-- Add Planning Item card (now the only create flow) --}}
            @if($showCreate)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Add Planning Item</h3>
                        <p class="mt-1 text-xs text-gray-500">
                            Use this for one-off additions and optionally to add related destination items as separate rows.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('trips.planner.store', $trip) }}" class="p-6 space-y-6">
                        @csrf
                        @include('trip-planner._form', [
                            'tripPlanItem' => new \App\Models\TripPlanItem([
                                'plantype' => $selectedPlanType,
                                'placeid' => $selectedPlaceId,
                                'destinationid' => $selectedDestinationId,
                                'destinationitemid' => $selectedDestinationItemId,
                            ]),
                            'trip' => $trip,
                            'places' => $places,
                            'destinations' => $destinations,
                            'destinationItems' => $destinationItems,
                            'tripLegs' => collect(),
                            'tripStays' => collect(),
                            'planTypeOptions' => $planTypeOptions,
                            'stayTypeOptions' => $stayTypeOptions,
                            'returnTo' => route('trips.planner.index', $trip),
                        ])
                    </form>
                </div>
            @endif

            {{-- Notes + summary --}}
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <div class="xl:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Planning notes</h3>
                        <p class="mt-1 text-xs text-gray-500">
                            This planner layer is intended to be simpler than Legs and Stays, with those records generated or linked later.
                        </p>
                    </div>
                    <div class="p-6 text-sm text-gray-700 space-y-2">
                        <p>Use Places for geographic anchors.</p>
                        <p>Use Destinations for broader editorial areas.</p>
                        <p>Use Destination Items for precise stops, camps, attractions, and route-side points.</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Trip summary</h3>
                    </div>
                    <div class="p-6 text-sm">
                        <dl class="space-y-3">
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Trip</dt>
                                <dd class="text-gray-900">{{ $trip->tripname }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Planning items</dt>
                                <dd class="text-gray-900">{{ $planItems->count() }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Trip status</dt>
                                <dd class="text-gray-900">{{ ucfirst($trip->tripstatus ?? 'planned') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Keep only the script that supports the Add Planning Item form --}}
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deleteForm = document.getElementById('delete-trip-plan-item-form');
            const returnToInput = document.getElementById('delete-trip-plan-item-return-to');

            document.querySelectorAll('.js-delete-trip-plan-item').forEach(button => {
                button.addEventListener('click', function () {
                    const action = this.dataset.action;
                    const name = this.dataset.name || 'this planning item';
                    const returnTo = this.dataset.returnTo || '';

                    if (!action) {
                        return;
                    }

                    if (!confirm(`Delete ${name}? This cannot be undone.`)) {
                        return;
                    }

                    deleteForm.action = action;
                    returnToInput.value = returnTo;
                    deleteForm.submit();
                });
            });
        });
        </script>
</x-app-layout>