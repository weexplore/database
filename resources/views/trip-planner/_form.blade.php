@php
    $returnTo = $returnTo ?? route('trips.planner.index', $trip);

    $selectedDestinationItemIdsForForm = collect(
        old('selected_destinationitemids', $existingSelectedDestinationItemIds ?? [])
    )->map(fn ($id) => (int) $id)->all();

    $selectedPlaceId = (string) old('placeid', $tripPlanItem->placeid);
    $selectedDestinationId = (string) old('destinationid', $tripPlanItem->destinationid);
@endphp

<input type="hidden" name="return_to" value="{{ $returnTo }}">

        <div class="space-y-6">
            {{-- One complete planning-item entry area: no save button here. --}}
            <section class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 sm:p-6 space-y-6">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">
                        {{ $tripPlanItem->exists ? 'Edit Planning Item' : 'New Planning Item' }}
                    </h3>

                    <p class="mt-1 text-sm text-gray-600">
                        Choose the trip date, place or destination, and any Destination Items to create.
                        Further planning details are available below.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4">
            <div>
                <label for="planneddate" class="block text-sm font-medium text-gray-700">
                    Planned date
                </label>

                <input type="date"
                    name="planneddate"
                    id="planneddate"
                    value="{{ old('planneddate', optional($tripPlanItem->planneddate)->format('Y-m-d')) }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                    autofocus>
            </div>

            <div>
                <label for="plannedenddate" class="block text-sm font-medium text-gray-700">
                    Planned end date
                </label>

                <input type="date"
                    name="plannedenddate"
                    id="plannedenddate"
                    value="{{ old('plannedenddate', optional($tripPlanItem->plannedenddate)->format('Y-m-d')) }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>

            <div>
                <label for="plantype" class="block text-sm font-medium text-gray-700">
                    Planning type
                </label>

                <select name="plantype"
                        id="plantype"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        required>
                    <option value="">Select type</option>

                    @foreach($planTypeOptions as $value => $label)
                        <option value="{{ $value }}"
                            @selected(old('plantype', $tripPlanItem->plantype) === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="xl:col-span-2">
                <label for="title" class="block text-sm font-medium text-gray-700">
                    Title
                </label>

                <input type="text"
                    name="title"
                    id="title"
                    value="{{ old('title', $tripPlanItem->title) }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                    maxlength="200"
                    placeholder="e.g. Travel to Bendigo">
            </div>

            <div>
                <div class="flex items-center justify-between gap-2">
                    <label for="placeid" class="block text-sm font-medium text-gray-700">
                        Place
                    </label>

                    <button type="button"
                            id="nearby_places_toggle"
                            class="inline-flex items-center px-2 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-xs font-medium disabled:opacity-50"
                            disabled>
                        Nearby
                    </button>
                </div>

                <select name="placeid"
                        id="placeid"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm js-place-select">
                    <option value="">None</option>

                    @foreach($places as $place)
                        <option value="{{ $place->id }}"
                            @selected($selectedPlaceId === (string) $place->id)>
                            {{ $place->placename }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="destinationid" class="block text-sm font-medium text-gray-700">
                    Destination
                </label>

                <select name="destinationid"
                        id="destinationid"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm js-destination-select">
                    <option value="">None</option>

                    @foreach($destinations as $destination)
                        @php
                            $matchesSelectedPlace = blank($selectedPlaceId)
                                || (string) $destination->placeid === $selectedPlaceId;
                        @endphp

                        <option value="{{ $destination->id }}"
                                data-place-id="{{ $destination->placeid }}"
                                @selected($selectedDestinationId === (string) $destination->id)
                                @disabled(! $matchesSelectedPlace)
                                @if(! $matchesSelectedPlace) hidden @endif>
                            {{ $destination->destinationname }}
                            @if($destination->place)
                                · {{ $destination->place->placename }}
                            @endif
                        </option>
                    @endforeach
                </select>

                <p class="mt-1 text-xs text-gray-500">
                    Destination options are limited to the selected place.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-x-5 gap-y-3">
            <label class="inline-flex items-center gap-2">
                <input type="hidden" name="isrouteanchor" value="0">
                <input type="checkbox"
                       name="isrouteanchor"
                       value="1"
                       class="rounded border-gray-300 text-blue-600 shadow-sm"
                       @checked((bool) old('isrouteanchor', $tripPlanItem->isrouteanchor))>
                <span class="text-sm text-gray-700">Route anchor</span>
            </label>

            <label class="inline-flex items-center gap-2">
                <input type="hidden" name="isgovia" value="0">
                <input type="checkbox"
                       name="isgovia"
                       value="1"
                       class="rounded border-gray-300 text-blue-600 shadow-sm"
                       @checked((bool) old('isgovia', $tripPlanItem->isgovia))>
                <span class="text-sm text-gray-700">Go via</span>
            </label>

            <label class="inline-flex items-center gap-2">
                <input type="hidden" name="isovernight" value="0">
                <input type="checkbox"
                       name="isovernight"
                       value="1"
                       class="rounded border-gray-300 text-blue-600 shadow-sm"
                       @checked((bool) old('isovernight', $tripPlanItem->isovernight))>
                <span class="text-sm text-gray-700">Overnight</span>
            </label>

            <label class="inline-flex items-center gap-2">
                <input type="hidden" name="isstaytarget" value="0">
                <input type="checkbox"
                       name="isstaytarget"
                       value="1"
                       class="rounded border-gray-300 text-blue-600 shadow-sm"
                       @checked((bool) old('isstaytarget', $tripPlanItem->isstaytarget))>
                <span class="text-sm text-gray-700">Stay target</span>
            </label>
        </div>

        {{-- Destination Items belong with the main creation controls. --}}
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-3">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h4 class="text-sm font-semibold text-gray-900">
                        Destination Items
                    </h4>

                    <p class="mt-1 text-xs text-gray-500">
                        Tick one or more items to create separate planning rows for them when this planning item is saved.
                    </p>
                </div>

                <label class="inline-flex items-center gap-2 text-sm text-gray-700 shrink-0">
                    <input type="checkbox"
                           id="related_toggle_all"
                           class="rounded border-gray-300">
                    <span>Select all visible</span>
                </label>
            </div>

            <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-md divide-y divide-gray-100 bg-white"
                 id="related_destinationitem_list">
                @foreach($destinationItems as $item)
                    @php
                        $resolvedPlaceId = (string) ($item->placeid ?? $item->destination?->placeid ?? '');
                        $resolvedDestinationId = (string) ($item->destinationid ?? '');
                        $matchesPlace = blank($selectedPlaceId) || $resolvedPlaceId === $selectedPlaceId;
                        $matchesDestination = blank($selectedDestinationId) || $resolvedDestinationId === $selectedDestinationId;
                        $isVisibleInitially = $selectedDestinationId
                            ? $matchesDestination
                            : ($selectedPlaceId ? $matchesPlace : true);
                    @endphp

                    <label class="related-destination-item-row flex items-start gap-3 px-3 py-2 hover:bg-gray-50"
                           data-place-id="{{ $resolvedPlaceId }}"
                           data-destination-id="{{ $resolvedDestinationId }}"
                           @if(! $isVisibleInitially) style="display:none;" @endif>
                        <input type="checkbox"
                               name="selected_destinationitemids[]"
                               value="{{ $item->id }}"
                               class="related-destination-item-checkbox mt-1 rounded border-gray-300"
                               @checked(in_array((int) $item->id, $selectedDestinationItemIdsForForm, true))>

                        <span class="min-w-0">
                            <span class="block text-xs font-medium text-gray-900">
                                {{ $item->itemname }}
                            </span>

                            <span class="mt-0.5 block text-[11px] text-gray-500">
                                {{ $item->destination->destinationname ?? 'No destination' }}
                                @if($item->place || $item->destination?->place)
                                    · {{ $item->place->placename ?? $item->destination?->place?->placename }}
                                @endif
                            </span>
                        </span>
                    </label>
                @endforeach
            </div>

            <p class="text-[11px] text-gray-400">
                The list filters automatically when you change Place or Destination. Leave all items unticked to create only one planning row.
            </p>
        </div>
    </section>

    {{-- Detail areas remain in the same form, below the unified creation area. --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="sequence_no" class="block text-sm font-medium text-gray-700">
                        Sequence
                    </label>

                    <input type="number"
                           min="1"
                           name="sequence_no"
                           id="sequence_no"
                           value="{{ old('sequence_no', $tripPlanItem->sequence_no) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">

                    <p class="mt-1 text-xs text-gray-500">
                        Leave blank to use the next available sequence number automatically.
                    </p>
                </div>

                <div>
                    <label for="sortgroup" class="block text-sm font-medium text-gray-700">
                        Sort group
                    </label>

                    <input type="text"
                           name="sortgroup"
                           id="sortgroup"
                           value="{{ old('sortgroup', $tripPlanItem->sortgroup) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                           maxlength="30">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="starttime" class="block text-sm font-medium text-gray-700">
                        Start time
                    </label>

                    <input type="time"
                           name="starttime"
                           id="starttime"
                           value="{{ old('starttime', $tripPlanItem->starttime ? \Illuminate\Support\Carbon::parse($tripPlanItem->starttime)->format('H:i') : '') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>

                <div>
                    <label for="endtime" class="block text-sm font-medium text-gray-700">
                        End time
                    </label>

                    <input type="time"
                           name="endtime"
                           id="endtime"
                           value="{{ old('endtime', $tripPlanItem->endtime ? \Illuminate\Support\Carbon::parse($tripPlanItem->endtime)->format('H:i') : '') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
            </div>

            <x-forms.markdown-display-editor
                name="notes"
                id="notes"
                label="Notes"
                :value="old('notes', $tripPlanItem->notes)"
                :rows="5"
                placeholder="Add planning notes, reminders, linked destination details, or activity planning..."
                help="Use Markdown for planning notes, reminders, linked destination details, or activity planning."
                preview-title="Planning Notes Preview"
            />

            <div id="nearby_places_card"
                 class="hidden bg-indigo-50 border border-indigo-200 rounded-lg p-4 space-y-4">
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Nearby Places</h3>
                        <p class="text-xs text-gray-600">
                            Search nearby places for the selected place without leaving this page.
                        </p>
                    </div>

                    <div class="flex items-end gap-3">
                        <div>
                            <label for="nearby_radius_km" class="block text-xs font-medium text-gray-700 mb-1">
                                Radius
                            </label>

                            <select id="nearby_radius_km" class="rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="25">25 km</option>
                                <option value="50" selected>50 km</option>
                                <option value="100">100 km</option>
                                <option value="150">150 km</option>
                                <option value="200">200 km</option>
                            </select>
                        </div>

                        <button type="button"
                                id="nearby_places_apply"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                            Apply
                        </button>

                        <button type="button"
                                id="nearby_places_close"
                                class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">
                            Close
                        </button>
                    </div>
                </div>

                <div id="nearby_places_status" class="text-xs text-gray-500">
                    Select a place and click Apply.
                </div>

                <div class="overflow-x-auto border border-indigo-100 rounded-md bg-white">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Place</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Distance</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>

                        <tbody id="nearby_places_results" class="divide-y divide-gray-100 bg-white">
                            <tr>
                                <td colspan="4" class="px-3 py-4 text-center text-sm text-gray-500">
                                    No nearby search loaded yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p class="text-[11px] text-gray-500">
                    Use <span class="font-medium">Add after</span> to insert a new planning item after the current one.
                </p>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-4">
                <h3 class="text-sm font-semibold text-gray-900">Stay details</h3>

                <div>
                    <label for="staytype" class="block text-sm font-medium text-gray-700">
                        Stay type
                    </label>

                    <select name="staytype"
                            id="staytype"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">None</option>

                        @foreach($stayTypeOptions as $value => $label)
                            <option value="{{ $value }}"
                                @selected(old('staytype', $tripPlanItem->staytype) === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="nightsplanned" class="block text-sm font-medium text-gray-700">
                        Nights planned
                    </label>

                    <input type="number"
                           min="0"
                           name="nightsplanned"
                           id="nightsplanned"
                           value="{{ old('nightsplanned', $tripPlanItem->nightsplanned) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
            </div>

            @if(isset($tripLegs) && isset($tripStays) && $tripLegs->count() + $tripStays->count() > 0)
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-4">
                    <h3 class="text-sm font-semibold text-gray-900">Linked outputs</h3>

                    <div>
                        <label for="triplegid" class="block text-sm font-medium text-gray-700">
                            Trip Leg
                        </label>

                        <select name="triplegid"
                                id="triplegid"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
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
                        <label for="tripstayid" class="block text-sm font-medium text-gray-700">
                            Trip Stay
                        </label>

                        <select name="tripstayid"
                                id="tripstayid"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
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

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-3">
                <h3 class="text-sm font-semibold text-gray-900">Actions</h3>

                <p class="text-xs text-gray-500">
                    Save creates the main planning item and any selected Destination Item rows together.
                </p>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ $returnTo }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                        Cancel
                    </a>

                    <button type="submit"
                            class="inline-flex items-center px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm font-medium">
                        {{ $tripPlanItem->exists ? 'Save Changes' : 'Create Planning Item' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@include('partials.markdown.markdown-styles')

<script>
document.addEventListener('DOMContentLoaded', function () {
    const placeSelect = document.getElementById('placeid');
    const destinationSelect = document.getElementById('destinationid');
    const toggleAll = document.getElementById('related_toggle_all');
    const destinationOptions = Array.from(
        destinationSelect
            ? destinationSelect.querySelectorAll('option[data-place-id]')
            : []
    );
    const rows = Array.from(document.querySelectorAll('.related-destination-item-row'));

    const nearbyToggle = document.getElementById('nearby_places_toggle');
    const nearbyCard = document.getElementById('nearby_places_card');
    const nearbyClose = document.getElementById('nearby_places_close');
    const nearbyApply = document.getElementById('nearby_places_apply');
    const nearbyRadius = document.getElementById('nearby_radius_km');
    const nearbyStatus = document.getElementById('nearby_places_status');
    const nearbyResults = document.getElementById('nearby_places_results');
    const nearbyBaseTemplate = @json(route('places.nearby-data', ['place' => '__PLACE__']));

    if (!placeSelect || !destinationSelect) {
        return;
    }

    function setNearbyButtonState() {
        const hasPlace = !!placeSelect.value;

        if (nearbyToggle) {
            nearbyToggle.disabled = !hasPlace;
        }

        if (!hasPlace && nearbyCard) {
            nearbyCard.classList.add('hidden');
        }
    }

    function filterDestinations() {
        const selectedPlaceId = placeSelect.value || '';
        const currentDestinationId = destinationSelect.value || '';
        let selectedDestinationIsVisible = false;

        destinationOptions.forEach(option => {
            const optionPlaceId = option.dataset.placeId || '';
            const visible = !selectedPlaceId || optionPlaceId === selectedPlaceId;

            option.hidden = !visible;
            option.disabled = !visible;

            if (visible && option.value === currentDestinationId) {
                selectedDestinationIsVisible = true;
            }
        });

        if (currentDestinationId && !selectedDestinationIsVisible) {
            destinationSelect.value = '';
        }
    }

    function filterRows() {
        const selectedPlaceId = placeSelect.value || '';
        const selectedDestinationId = destinationSelect.value || '';

        rows.forEach(row => {
            const rowPlaceId = row.dataset.placeId || '';
            const rowDestinationId = row.dataset.destinationId || '';

            const visible = selectedDestinationId
                ? rowDestinationId === selectedDestinationId
                : (selectedPlaceId ? rowPlaceId === selectedPlaceId : true);

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

    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function renderNearbyRows(items) {
        if (!nearbyResults) {
            return;
        }

        if (!items.length) {
            nearbyResults.innerHTML = `
                <tr>
                    <td colspan="4" class="px-3 py-4 text-center text-sm text-gray-500">
                        No places found within the selected radius.
                    </td>
                </tr>
            `;
            return;
        }

        nearbyResults.innerHTML = items.map(item => `
            <tr>
                <td class="px-3 py-2 text-sm text-gray-900">${escapeHtml(item.placename || '')}</td>
                <td class="px-3 py-2 text-sm text-gray-700">${escapeHtml(item.placetype || '')}</td>
                <td class="px-3 py-2 text-sm text-gray-700">${Number(item.distance_km).toFixed(1)} km</td>
                <td class="px-3 py-2 text-sm whitespace-nowrap">
                    <button type="button"
                            class="nearby-add-after inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded hover:bg-green-700 text-xs font-medium"
                            data-place-id="${escapeHtml(item.id)}"
                            data-place-name="${escapeHtml(item.placename || '')}">
                        Add after
                    </button>
                </td>
            </tr>
        `).join('');
    }

    async function loadNearbyPlaces() {
        const selectedPlaceId = placeSelect.value || '';

        if (!selectedPlaceId) {
            nearbyStatus.textContent = 'Select a place first.';
            renderNearbyRows([]);
            return;
        }

        const url = nearbyBaseTemplate.replace('__PLACE__', selectedPlaceId);
        const finalUrl = new URL(url, window.location.origin);
        finalUrl.searchParams.set('radius_km', nearbyRadius.value || '50');

        nearbyStatus.textContent = 'Loading nearby places...';

        nearbyResults.innerHTML = `
            <tr>
                <td colspan="4" class="px-3 py-4 text-center text-sm text-gray-500">
                    Loading...
                </td>
            </tr>
        `;

        try {
            const response = await fetch(finalUrl.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) {
                throw new Error('Nearby lookup failed.');
            }

            const data = await response.json();

            nearbyStatus.textContent =
                `Showing places within ${data.radius_km} km of ${data.place.placename}.`;

            renderNearbyRows(data.nearby_places || []);
        } catch (error) {
            nearbyStatus.textContent = 'Could not load nearby places.';

            nearbyResults.innerHTML = `
                <tr>
                    <td colspan="4" class="px-3 py-4 text-center text-sm text-red-600">
                        Could not load nearby places.
                    </td>
                </tr>
            `;
        }
    }

    nearbyToggle?.addEventListener('click', function () {
        nearbyCard?.classList.toggle('hidden');

        if (nearbyCard && !nearbyCard.classList.contains('hidden')) {
            loadNearbyPlaces();
        }
    });

    nearbyClose?.addEventListener('click', function () {
        nearbyCard?.classList.add('hidden');
    });

    nearbyApply?.addEventListener('click', loadNearbyPlaces);

    placeSelect.addEventListener('change', function () {
        filterDestinations();
        filterRows();
        setNearbyButtonState();
    });

    destinationSelect.addEventListener('change', filterRows);

    toggleAll?.addEventListener('change', function () {
        rows
            .filter(row => row.style.display !== 'none')
            .map(row => row.querySelector('.related-destination-item-checkbox'))
            .filter(Boolean)
            .forEach(checkbox => {
                checkbox.checked = toggleAll.checked;
            });
    });

    filterDestinations();
    filterRows();
    setNearbyButtonState();
});
</script>