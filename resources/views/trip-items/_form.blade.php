@php
    $currentTripLegId = old('triplegid', $selectedTripLegId ?? ($tripItem->triplegid ?? ''));
    $currentTripStayId = old('tripstayid', $selectedTripStayId ?? ($tripItem->tripstayid ?? ''));
    $currentDestinationId = old('destinationid', $selectedDestinationId ?? ($tripItem->destinationid ?? ''));
    $currentDestinationItemId = old('destinationitemid', $selectedDestinationItemId ?? ($tripItem->destinationitemid ?? ''));
    $currentPlaceId = old('placeid', $selectedPlaceId ?? ($tripItem->placeid ?? ''));
    $currentBookingId = old('bookingid', $selectedBookingId ?? ($tripItem->bookingid ?? ''));
    $currentItemDate = old('itemdate', optional($tripItem->itemdate ?? null)->format('Y-m-d') ?? ($selectedItemDate ?? ''));
    $currentStartDateTime = old('startdatetime', optional($tripItem->startdatetime ?? null)->format('Y-m-d\TH:i'));
    $currentEndDateTime = old('enddatetime', optional($tripItem->enddatetime ?? null)->format('Y-m-d\TH:i'));
    $currentItemType = old('itemtype', $selectedItemType ?? ($tripItem->itemtype ?? ''));
    $currentStatus = old('status', $tripItem->status ?? 'planned');
    $currentTitle = old('title', $tripItem->title ?? '');
    $currentDescription = old('description', $tripItem->description ?? '');
    $currentPriority = old('priority', $tripItem->priority ?? 'normal');
    $currentIsFullDay = old('isfullday', $tripItem->isfullday ?? false);
    $currentPeopleCount = old('peoplecount', $tripItem->peoplecount ?? $trip->travellercount ?? 2);
    $currentEstimatedCostPerPerson = old('estimatedcostperperson', $tripItem->estimatedcostperperson ?? '');
    $currentEstimatedTotalCost = old('estimatedtotalcost', $tripItem->estimatedtotalcost ?? '');
    $currentActualCost = old('actualcost', $tripItem->actualcost ?? '');
    $currentAllocateAsDailyCost = old('allocateasdailycost', $tripItem->allocateasdailycost ?? false);
    $currentNotesInternal = old('notesinternal', $tripItem->notesinternal ?? '');
    $currentSortOrder = old('sortorder', $tripItem->sortorder ?? '');
@endphp

<div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 space-y-6">
    <div class="space-y-4">
        <div>
            <h3 class="text-lg font-medium text-gray-900">Core details</h3>
            <p class="mt-1 text-xs text-gray-500">
                Main planning details for this trip item.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div>
                <label for="itemtype" class="block text-sm font-medium text-gray-700 mb-1">Item type</label>
                <select id="itemtype" name="itemtype" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">Select</option>
                    @foreach($itemTypeOptions as $value => $label)
                        <option value="{{ $value }}" @selected((string) $currentItemType === (string) $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="status" name="status" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">Select</option>
                    @foreach($itemStatusOptions as $value => $label)
                        <option value="{{ $value }}" @selected((string) $currentStatus === (string) $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="itemdate" class="block text-sm font-medium text-gray-700 mb-1">Item date</label>
                <input type="date" id="itemdate" name="itemdate"
                       value="{{ $currentItemDate }}"
                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>

            <div>
                <label for="sortorder" class="block text-sm font-medium text-gray-700 mb-1">Sort order</label>
                <input type="number" id="sortorder" name="sortorder"
                       value="{{ $currentSortOrder }}"
                       class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                       min="0">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            <div>
                <label for="placeid" class="block text-sm font-medium text-gray-700 mb-1">Place</label>
                <select id="placeid" name="placeid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">None</option>
                    @foreach($places as $place)
                        <option value="{{ $place->id }}" @selected((string) $currentPlaceId === (string) $place->id)>
                            {{ $place->placename }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="destinationid" class="block text-sm font-medium text-gray-700 mb-1">Destination</label>
                <select id="destinationid" name="destinationid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">None</option>
                    @foreach($destinations as $destination)
                        <option value="{{ $destination->id }}"
                                data-place-id="{{ $destination->placeid ?? '' }}"
                                @selected((string) $currentDestinationId === (string) $destination->id)>
                            {{ $destination->destinationname }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="destinationitemid" class="block text-sm font-medium text-gray-700 mb-1">Destination item</label>
                <select id="destinationitemid" name="destinationitemid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">None</option>
                    @foreach($destinationItems as $destinationItem)
                        <option value="{{ $destinationItem->id }}"
                                data-place-id="{{ $destinationItem->placeid ?? $destinationItem->destination?->placeid ?? '' }}"
                                data-destination-id="{{ $destinationItem->destinationid ?? '' }}"
                                @selected((string) $currentDestinationItemId === (string) $destinationItem->id)>
                            {{ $destinationItem->itemname }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
            <input type="text" id="title" name="title"
                   value="{{ $currentTitle }}"
                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm"
                   required>
        </div>

        <div>
            <x-forms.markdown-display-editor
                name="description"
                id="description"
                label="Description"
                :value="$currentDescription"
                :rows="5"
                placeholder="Add item details, activity summaries, inclusions, and planning context..."
                help="Use Markdown for item details, activity summaries, inclusions, and planning context."
                preview-title="Description Preview"
            />
        </div>
    </div>

    {{-- Links and context --}}
    <div class="space-y-4">
    <div>
        <h3 class="text-lg font-medium text-gray-900">Links and context</h3>
        <p class="mt-1 text-xs text-gray-500">
            Connect this item to related trip records.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <div>
            <label for="triplegid" class="block text-sm font-medium text-gray-700 mb-1">Trip leg</label>
            <select id="triplegid" name="triplegid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">None</option>
                @foreach($tripLegs as $tripLeg)
                    <option value="{{ $tripLeg->id }}" @selected((string) $currentTripLegId === (string) $tripLeg->id)>
                        {{ $tripLeg->fromPlace?->placename ?? 'Unknown start' }}
                        -
                        {{ $tripLeg->toPlace?->placename ?? 'Unknown end' }}
                        @if($tripLeg->startdate)
                            - {{ $tripLeg->startdate->format('d/m/Y') }}
                        @endif
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="tripstayid" class="block text-sm font-medium text-gray-700 mb-1">Trip stay</label>
            <select id="tripstayid" name="tripstayid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">None</option>
                @foreach($tripStays as $tripStay)
                    <option value="{{ $tripStay->id }}" @selected((string) $currentTripStayId === (string) $tripStay->id)>
                        {{ $tripStay->stayname }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="bookingid" class="block text-sm font-medium text-gray-700 mb-1">Booking</label>
            <select id="bookingid" name="bookingid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">None</option>
                @foreach($bookings as $booking)
                    <option value="{{ $booking->id }}" @selected((string) $currentBookingId === (string) $booking->id)>
                        {{ $booking->providername ?: 'Booking #' . $booking->id }}
                        @if($booking->bookingtype)
                            - {{ $bookingTypes[$booking->bookingtype] ?? ucfirst(str_replace('_', ' ', $booking->bookingtype)) }}
                        @endif
                        @if($booking->startdate)
                            - {{ $booking->startdate->format('d/m/Y') }}
                        @endif
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

    {{-- People and cost --}}
    <div class="space-y-4">
        <div>
            <h3 class="text-lg font-medium text-gray-900">People and cost</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div>
                <label for="peoplecount" class="block text-sm font-medium text-gray-700 mb-1">People count</label>
                <input type="number" id="peoplecount" name="peoplecount"
                       value="{{ $currentPeopleCount }}"
                       class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                       min="1">
            </div>

            <div>
                <label for="estimatedcostperperson" class="block text-sm font-medium text-gray-700 mb-1">Est. cost per person</label>
                <input type="number" step="0.01" id="estimatedcostperperson" name="estimatedcostperperson"
                       value="{{ $currentEstimatedCostPerPerson }}"
                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>

            <div>
                <label for="estimatedtotalcost" class="block text-sm font-medium text-gray-700 mb-1">Est. total cost</label>
                <input type="number" step="0.01" id="estimatedtotalcost" name="estimatedtotalcost"
                       value="{{ $currentEstimatedTotalCost }}"
                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>

            <div>
                <label for="actualcost" class="block text-sm font-medium text-gray-700 mb-1">Actual cost</label>
                <input type="number" step="0.01" id="actualcost" name="actualcost"
                       value="{{ $currentActualCost }}"
                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
        </div>
    </div>

    {{-- Internal notes --}}
    <div class="space-y-2">
        <x-forms.markdown-display-editor
            name="notesinternal"
            id="notesinternal"
            label="Internal notes"
            :value="$currentNotesInternal"
            :rows="5"
            placeholder="Add private planning notes, reminders, admin details, or follow-up actions..."
            help="Use Markdown for private planning notes, reminders, admin details, or follow-up actions."
            preview-title="Internal Notes Preview"
        />
    </div>
</div>
@include('partials.markdown.markdown-styles')

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const peopleInput = document.getElementById('peoplecount');
        const perPersonInput = document.getElementById('estimatedcostperperson');
        const totalInput = document.getElementById('estimatedtotalcost');

        if (!peopleInput || !perPersonInput || !totalInput) {
            return;
        }

        function toNumber(el) {
            const value = parseFloat(el.value);
            return Number.isFinite(value) ? value : null;
        }

        function formatMoney(value) {
            if (!Number.isFinite(value)) return '';
            return value.toFixed(2);
        }

        function recalcFromPerPerson() {
            const people = toNumber(peopleInput);
            const perPerson = toNumber(perPersonInput);

            if (!people || people <= 0 || perPerson === null) {
                return;
            }

            const total = people * perPerson;
            totalInput.value = formatMoney(total);
        }

        function recalcFromTotal() {
            const people = toNumber(peopleInput);
            const total = toNumber(totalInput);

            if (!people || people <= 0 || total === null) {
                return;
            }

            const perPerson = total / people;
            perPersonInput.value = formatMoney(perPerson);
        }

        // When people or per-person change, prefer recalculating total
        peopleInput.addEventListener('input', function () {
            if (perPersonInput.value !== '') {
                recalcFromPerPerson();
            } else if (totalInput.value !== '') {
                recalcFromTotal();
            }
        });

        perPersonInput.addEventListener('input', function () {
            recalcFromPerPerson();
        });

        // When total changes, recalc per-person
        totalInput.addEventListener('input', function () {
            recalcFromTotal();
        });
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const placeSelect = document.getElementById('placeid');
    const destinationSelect = document.getElementById('destinationid');
    const destinationItemSelect = document.getElementById('destinationitemid');
    const titleInput = document.getElementById('title');

    if (!placeSelect || !destinationSelect || !destinationItemSelect || !titleInput) {
        return;
    }

    function buildOptionCache(select) {
        return Array.from(select.options).map(option => ({
            value: option.value,
            text: option.text,
            placeId: option.dataset.placeId || '',
            destinationId: option.dataset.destinationId || '',
        }));
    }

    const destinationOptions = buildOptionCache(destinationSelect);
    const destinationItemOptions = buildOptionCache(destinationItemSelect);

    function rebuildSelect(select, options, placeholder, selectedValue) {
        select.innerHTML = '';

        const placeholderOption = document.createElement('option');
        placeholderOption.value = '';
        placeholderOption.textContent = placeholder;
        select.appendChild(placeholderOption);

        options.forEach(item => {
            const option = document.createElement('option');
            option.value = item.value;
            option.textContent = item.text;

            if (item.placeId) option.dataset.placeId = item.placeId;
            if (item.destinationId) option.dataset.destinationId = item.destinationId;

            if (String(item.value) === String(selectedValue)) {
                option.selected = true;
            }

            select.appendChild(option);
        });
    }

    function filterDestinations() {
        const selectedPlaceId = placeSelect.value;
        const currentValue = destinationSelect.value;

        const filtered = destinationOptions.filter(option => {
            if (!option.value) return false;
            if (!selectedPlaceId) return true;
            return String(option.placeId) === String(selectedPlaceId);
        });

        const stillValid = filtered.some(option => String(option.value) === String(currentValue));

        rebuildSelect(
            destinationSelect,
            filtered,
            'None',
            stillValid ? currentValue : ''
        );

        if (!stillValid) {
            destinationSelect.value = '';
        }
    }

    function filterDestinationItems() {
        const selectedPlaceId = placeSelect.value;
        const selectedDestinationId = destinationSelect.value;
        const currentValue = destinationItemSelect.value;

        const filtered = destinationItemOptions.filter(option => {
            if (!option.value) return false;

            if (selectedPlaceId && String(option.placeId) !== String(selectedPlaceId)) {
                return false;
            }

            if (selectedDestinationId && String(option.destinationId) !== String(selectedDestinationId)) {
                return false;
            }

            return true;
        });

        const stillValid = filtered.some(option => String(option.value) === String(currentValue));

        rebuildSelect(
            destinationItemSelect,
            filtered,
            'None',
            stillValid ? currentValue : ''
        );

        if (!stillValid) {
            destinationItemSelect.value = '';
        }
    }

    function selectedText(select) {
        const option = select.options[select.selectedIndex];
        return option && option.value ? option.text.trim() : '';
    }

    let titleTouched = false;
    titleInput.dataset.lastAutoTitle = titleInput.value.trim();

    titleInput.addEventListener('input', function () {
        const currentValue = titleInput.value.trim();
        const lastAutoTitle = titleInput.dataset.lastAutoTitle || '';
        titleTouched = currentValue !== '' && currentValue !== lastAutoTitle;
    });

    function updateTitle() {
        const placeText = selectedText(placeSelect);
        const destinationText = selectedText(destinationSelect);
        const destinationItemText = selectedText(destinationItemSelect);

        let computed = '';

        if (!destinationText) {
            computed = placeText;
        } else if (destinationText && destinationItemText) {
            computed = `${destinationText} - ${destinationItemText}`;
        } else {
            computed = destinationText;
        }

        const currentValue = titleInput.value.trim();
        const lastAutoTitle = titleInput.dataset.lastAutoTitle || '';

        if (!titleTouched || currentValue === '' || currentValue === lastAutoTitle) {
            titleInput.value = computed;
            titleInput.dataset.lastAutoTitle = computed;
            titleTouched = false;
        }
    }

    placeSelect.addEventListener('change', function () {
        filterDestinations();
        filterDestinationItems();
        updateTitle();
    });

    destinationSelect.addEventListener('change', function () {
        filterDestinationItems();
        updateTitle();
    });

    destinationItemSelect.addEventListener('change', updateTitle);

    filterDestinations();
    filterDestinationItems();
    updateTitle();
});
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tripStaySelect = document.getElementById('tripstayid');
        const perPersonInput = document.getElementById('estimatedcostperperson');
        const totalInput = document.getElementById('estimatedtotalcost');
        const actualInput = document.getElementById('actualcost');

        if (!tripStaySelect || !perPersonInput || !totalInput || !actualInput) {
            return;
        }

        function toggleStayCostFields() {
            const hasStay = tripStaySelect.value !== '';

            [perPersonInput, totalInput, actualInput].forEach(function (input) {
                input.readOnly = hasStay;

                if (hasStay) {
                    input.value = '0.00';
                    input.classList.add('bg-gray-100', 'text-gray-500');
                } else {
                    input.classList.remove('bg-gray-100', 'text-gray-500');
                }
            });
        }

        tripStaySelect.addEventListener('change', toggleStayCostFields);
        toggleStayCostFields();
    });
</script>
@endpush