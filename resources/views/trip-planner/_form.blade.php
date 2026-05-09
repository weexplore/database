@php
    $returnTo = $returnTo ?? route('trips.planner.index', $trip);

    // Normalised array of selected destination item IDs for the checkbox list
    $selectedDestinationItemIdsForForm = collect(
        old('selected_destinationitemids', $existingSelectedDestinationItemIds ?? [])
    )->map(fn ($id) => (int) $id)->all();
@endphp

<input type="hidden" name="return_to" value="{{ $returnTo }}">

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    {{-- Main column --}}
    <div class="xl:col-span-2 space-y-6">
        {{-- Sequence + type + title + sort group --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="sequence_no" class="block text-sm font-medium text-gray-700">Sequence</label>
                <input type="number"
                       min="1"
                       name="sequence_no"
                       id="sequence_no"
                       value="{{ old('sequence_no', $tripPlanItem->sequence_no) }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>

            <div>
                <label for="plantype" class="block text-sm font-medium text-gray-700">Planning type</label>
                <select name="plantype" id="plantype" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    <option value="">Select type</option>
                    @foreach($planTypeOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('plantype', $tripPlanItem->plantype) === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text"
                       name="title"
                       id="title"
                       value="{{ old('title', $tripPlanItem->title) }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                       maxlength="200">
            </div>

            <div>
                <label for="sortgroup" class="block text-sm font-medium text-gray-700">Sort group</label>
                <input type="text"
                       name="sortgroup"
                       id="sortgroup"
                       value="{{ old('sortgroup', $tripPlanItem->sortgroup) }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                       maxlength="30">
            </div>
        </div>

        {{-- Place / Destination / Destination Item --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="placeid" class="block text-sm font-medium text-gray-700">Place</label>
                <select name="placeid" id="placeid" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">None</option>
                    @foreach($places as $place)
                        <option value="{{ $place->id }}"
                            @selected((string) old('placeid', $tripPlanItem->placeid) === (string) $place->id)>
                            {{ $place->placename }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="destinationid" class="block text-sm font-medium text-gray-700">Destination</label>
                <select name="destinationid" id="destinationid" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">None</option>
                    @foreach($destinations as $destination)
                        <option value="{{ $destination->id }}"
                            data-place-id="{{ $destination->placeid }}"
                            @selected((string) old('destinationid', $tripPlanItem->destinationid) === (string) $destination->id)>
                            {{ $destination->destinationname }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="destinationitemid" class="block text-sm font-medium text-gray-700">Destination Item</label>
                <select name="destinationitemid" id="destinationitemid" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">None</option>
                    @foreach($destinationItems as $destinationItem)
                        <option value="{{ $destinationItem->id }}"
                            data-destination-id="{{ $destinationItem->destinationid }}"
                            data-place-id="{{ $destinationItem->placeid }}"
                            @selected((string) old('destinationitemid', $tripPlanItem->destinationitemid) === (string) $destinationItem->id)>
                            {{ $destinationItem->itemname }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Dates / times --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div>
                <label for="planneddate" class="block text-sm font-medium text-gray-700">Planned date</label>
                <input type="date"
                       name="planneddate"
                       id="planneddate"
                       value="{{ old('planneddate', optional($tripPlanItem->planneddate)->format('Y-m-d')) }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>

            <div>
                <label for="plannedenddate" class="block text-sm font-medium text-gray-700">Planned end date</label>
                <input type="date"
                       name="plannedenddate"
                       id="plannedenddate"
                       value="{{ old('plannedenddate', optional($tripPlanItem->plannedenddate)->format('Y-m-d')) }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>

            <div>
                <label for="starttime" class="block text-sm font-medium text-gray-700">Start time</label>
                <input type="time"
                       name="starttime"
                       id="starttime"
                       value="{{ old('starttime', $tripPlanItem->starttime ? \Illuminate\Support\Carbon::parse($tripPlanItem->starttime)->format('H:i') : '') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>

            <div>
                <label for="endtime" class="block text-sm font-medium text-gray-700">End time</label>
                <input type="time"
                       name="endtime"
                       id="endtime"
                       value="{{ old('endtime', $tripPlanItem->endtime ? \Illuminate\Support\Carbon::parse($tripPlanItem->endtime)->format('H:i') : '') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
        </div>

        {{-- Notes --}}
        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
            <textarea name="notes"
                      id="notes"
                      rows="6"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('notes', $tripPlanItem->notes) }}</textarea>
        </div>

        {{-- Related destination items --}}
        <div class="mt-6">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-3">
                <h3 class="text-sm font-semibold text-gray-900">
                    Also add related destination items
                </h3>
                <p class="text-xs text-gray-500">
                    After saving this planning item, separate rows will be created for any checked destination items
                    that match the selected place or destination.
                </p>

                <div class="space-y-3">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" id="related_toggle_all" class="rounded border-gray-300">
                        <span>Select all visible</span>
                    </label>

                    <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-md divide-y divide-gray-100"
                         id="related_destinationitem_list">
                        @foreach($destinationItems as $item)
                            @php
                                $resolvedPlaceId = $item->placeid ?? $item->destination?->placeid;
                                $resolvedDestinationId = $item->destinationid;
                            @endphp

                            <label class="related-destination-item-row flex items-start gap-3 px-3 py-2 hover:bg-gray-50"
                                   data-place-id="{{ $resolvedPlaceId }}"
                                   data-destination-id="{{ $resolvedDestinationId }}">
                                <input type="checkbox"
                                       name="selected_destinationitemids[]"
                                       value="{{ $item->id }}"
                                       class="related-destination-item-checkbox mt-1 rounded border-gray-300"
                                       @checked(in_array((int) $item->id, $selectedDestinationItemIdsForForm, true))>

                                <div class="min-w-0">
                                    <div class="text-xs font-medium text-gray-900">
                                        {{ $item->itemname }}
                                    </div>
                                    <div class="mt-0.5 text-[11px] text-gray-500">
                                        {{ $item->destination->destinationname ?? 'No destination' }}
                                        @if($item->place || $item->destination?->place)
                                            · {{ $item->place->placename ?? $item->destination?->place?->placename }}
                                        @endif
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <p class="text-[11px] text-gray-400">
                        The list filters automatically based on the Place and Destination above. Leave all unchecked
                        to create only this single planning row.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Right-hand column --}}
    <div class="space-y-6">
        {{-- Planning flags --}}
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-4">
            <h3 class="text-sm font-semibold text-gray-900">Planning flags</h3>

            <div class="space-y-3">
                <label class="flex items-center gap-3">
                    <input type="hidden" name="isrouteanchor" value="0">
                    <input type="checkbox"
                           name="isrouteanchor"
                           value="1"
                           class="rounded border-gray-300 text-blue-600 shadow-sm"
                           @checked((bool) old('isrouteanchor', $tripPlanItem->isrouteanchor))>
                    <span class="text-sm text-gray-700">Route anchor</span>
                </label>

                <label class="flex items-center gap-3">
                    <input type="hidden" name="isovernight" value="0">
                    <input type="checkbox"
                           name="isovernight"
                           value="1"
                           class="rounded border-gray-300 text-blue-600 shadow-sm"
                           @checked((bool) old('isovernight', $tripPlanItem->isovernight))>
                    <span class="text-sm text-gray-700">Overnight</span>
                </label>

                <label class="flex items-center gap-3">
                    <input type="hidden" name="isstaytarget" value="0">
                    <input type="checkbox"
                           name="isstaytarget"
                           value="1"
                           class="rounded border-gray-300 text-blue-600 shadow-sm"
                           @checked((bool) old('isstaytarget', $tripPlanItem->isstaytarget))>
                    <span class="text-sm text-gray-700">Stay target</span>
                </label>
            </div>
        </div>

        {{-- Stay details --}}
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-4">
            <h3 class="text-sm font-semibold text-gray-900">Stay details</h3>

            <div>
                <label for="staytype" class="block text-sm font-medium text-gray-700">Stay type</label>
                <select name="staytype" id="staytype" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">None</option>
                    @foreach($stayTypeOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('staytype', $tripPlanItem->staytype) === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="nightsplanned" class="block text-sm font-medium text-gray-700">Nights planned</label>
                <input type="number"
                       min="0"
                       name="nightsplanned"
                       id="nightsplanned"
                       value="{{ old('nightsplanned', $tripPlanItem->nightsplanned) }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
        </div>

        {{-- Linked outputs --}}
        @if(isset($tripLegs) && isset($tripStays) && $tripLegs->count() + $tripStays->count() > 0)
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-4">
                <h3 class="text-sm font-semibold text-gray-900">Linked outputs</h3>

                <div>
                    <label for="triplegid" class="block text-sm font-medium text-gray-700">Trip Leg</label>
                    <select name="triplegid" id="triplegid" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">None</option>
                        @foreach($tripLegs as $leg)
                            <option value="{{ $leg->id }}"
                                @selected((string) old('triplegid', $tripPlanItem->triplegid) === (string) $leg->id)>
                                Leg {{ $leg->legnumber }} - {{ $leg->title ?: 'Untitled' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="tripstayid" class="block text-sm font-medium text-gray-700">Trip Stay</label>
                    <select name="tripstayid" id="tripstayid" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">None</option>
                        @foreach($tripStays as $stay)
                            <option value="{{ $stay->id }}"
                                @selected((string) old('tripstayid', $tripPlanItem->tripstayid) === (string) $stay->id)>
                                {{ $stay->stayname ?: 'Trip Stay' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif

        {{-- Actions --}}
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-3">
            <h3 class="text-sm font-semibold text-gray-900">Actions</h3>

            <div class="flex flex-wrap gap-3">
                <a href="{{ $returnTo }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                    Cancel
                </a>

                <button type="submit"
                        class="inline-flex items-center px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    Save Planning Item
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Related destination items filter script --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const placeSelect = document.getElementById('placeid');
    const destinationSelect = document.getElementById('destinationid');
    const toggleAll = document.getElementById('related_toggle_all');
    const rows = Array.from(document.querySelectorAll('.related-destination-item-row'));

    if (!placeSelect || !destinationSelect || !rows.length) {
        return;
    }

    function filterRows() {
        const selectedPlaceId = placeSelect.value;
        const selectedDestinationId = destinationSelect.value;

        rows.forEach(row => {
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

    if (placeSelect) {
        placeSelect.addEventListener('change', filterRows);
    }

    if (destinationSelect) {
        destinationSelect.addEventListener('change', filterRows);
    }

    if (toggleAll) {
        toggleAll.addEventListener('change', function () {
            const visibleCheckboxes = rows
                .filter(row => row.style.display !== 'none')
                .map(row => row.querySelector('.related-destination-item-checkbox'))
                .filter(Boolean);

            visibleCheckboxes.forEach(checkbox => {
                checkbox.checked = toggleAll.checked;
            });
        });
    }

    filterRows();
});
</script>