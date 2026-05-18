@php
    $isCreate = $isCreate ?? false;
    $returnTo = $returnTo ?? route('trips.reviews.index', $trip);
@endphp

<div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-medium text-gray-900">
                {{ $isCreate ? 'Add Trip Review' : 'Review Details' }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Record traveller feedback, ratings, comments, and return-interest notes.
            </p>
        </div>

        @if($isCreate)
            <a href="{{ $returnTo }}"
               class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                Close
            </a>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        <div>
            <label for="reviewdate" class="block text-sm font-medium text-gray-700 mb-1">Review date</label>
            <input type="date"
                   name="reviewdate"
                   id="reviewdate"
                   value="{{ old('reviewdate', optional($review?->reviewdate)->format('Y-m-d')) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="travellerid" class="block text-sm font-medium text-gray-700 mb-1">Traveller</label>
            <select id="travellerid" name="travellerid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">None</option>
                @foreach($travellers as $traveller)
                    <option value="{{ $traveller->id }}" @selected((string) old('travellerid', $review?->travellerid) === (string) $traveller->id)>
                        {{ $traveller->firstname }} {{ $traveller->lastname }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="tripstayid" class="block text-sm font-medium text-gray-700 mb-1">Stay</label>
            <select id="tripstayid" name="tripstayid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">None</option>
                @foreach($stays as $stay)
                    <option value="{{ $stay->id }}"
                            data-place-id="{{ $stay->placeid ?? '' }}"
                            data-destination-item-id="{{ isset($stay->destinationitemid) ? $stay->destinationitemid : '' }}"
                            @selected((string) old('tripstayid', $review?->tripstayid) === (string) $stay->id)>
                        {{ $stay->stayname ?: 'Stay #'.$stay->id }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="tripitemid" class="block text-sm font-medium text-gray-700 mb-1">Trip item</label>
            <select id="tripitemid" name="tripitemid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">None</option>
                @foreach($tripItems as $item)
                    <option value="{{ $item->id }}"
                            data-place-id="{{ $item->placeid ?? '' }}"
                            data-destination-id="{{ $item->destinationid ?? '' }}"
                            data-destination-item-id="{{ $item->destinationitemid ?? '' }}"
                            @selected((string) old('tripitemid', $review?->tripitemid) === (string) $item->id)>
                        {{ $item->title ?: 'Item #'.$item->id }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="placeid" class="block text-sm font-medium text-gray-700 mb-1">Place</label>
            <select id="placeid" name="placeid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">None</option>
                @foreach($places as $place)
                    <option value="{{ $place->id }}" @selected((string) old('placeid', $review?->placeid) === (string) $place->id)>
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
                            @selected((string) old('destinationid', $review?->destinationid) === (string) $destination->id)>
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
                            @selected((string) old('destinationitemid', $review?->destinationitemid) === (string) $destinationItem->id)>
                        {{ $destinationItem->itemname }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
            <input type="text"
                   name="title"
                   id="title"
                   maxlength="150"
                   value="{{ old('title', $review?->title) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
    </div>
</div>

<div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
    <div>
        <h3 class="text-lg font-medium text-gray-900">Ratings and comments</h3>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-6">
        <div>
            <label for="ratingoverall" class="block text-sm font-medium text-gray-700 mb-1">Overall (1–10)</label>
            <input type="number"
                   name="ratingoverall"
                   id="ratingoverall"
                   min="1"
                   max="10"
                   step="1"
                   value="{{ old('ratingoverall', $review?->ratingoverall) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="ratingvalue" class="block text-sm font-medium text-gray-700 mb-1">Value (1–10)</label>
            <input type="number"
                   name="ratingvalue"
                   id="ratingvalue"
                   min="1"
                   max="10"
                   step="1"
                   value="{{ old('ratingvalue', $review?->ratingvalue) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="ratingfacility" class="block text-sm font-medium text-gray-700 mb-1">Facilities (1–10)</label>
            <input type="number"
                   name="ratingfacility"
                   id="ratingfacility"
                   min="1"
                   max="10"
                   step="1"
                   value="{{ old('ratingfacility', $review?->ratingfacility) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="ratingaccess" class="block text-sm font-medium text-gray-700 mb-1">Access (1–10)</label>
            <input type="number"
                   name="ratingaccess"
                   id="ratingaccess"
                   min="1"
                   max="10"
                   step="1"
                   value="{{ old('ratingaccess', $review?->ratingaccess) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="ratingambience" class="block text-sm font-medium text-gray-700 mb-1">Ambience (1–10)</label>
            <input type="number"
                   name="ratingambience"
                   id="ratingambience"
                   min="1"
                   max="10"
                   step="1"
                   value="{{ old('ratingambience', $review?->ratingambience) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="returninterestlevel" class="block text-sm font-medium text-gray-700 mb-1">Return interest (1–5)</label>
            <input type="number"
                   name="returninterestlevel"
                   id="returninterestlevel"
                   min="1"
                   max="5"
                   step="1"
                   value="{{ old('returninterestlevel', $review?->returninterestlevel) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div class="md:col-span-2 xl:col-span-2 flex items-center gap-6 pt-7">
            <label class="inline-flex items-center text-sm text-gray-700">
                <input type="hidden" name="wouldreturn" value="0">
                <input type="checkbox"
                       name="wouldreturn"
                       value="1"
                       class="rounded border-gray-300 text-green-600 shadow-sm mr-2"
                       @checked((bool) old('wouldreturn', $review?->wouldreturn))>
                Would return
            </label>

            <label class="inline-flex items-center text-sm text-gray-700">
                <input type="hidden" name="isprivate" value="0">
                <input type="checkbox"
                       name="isprivate"
                       value="1"
                       class="rounded border-gray-300 text-green-600 shadow-sm mr-2"
                       @checked((bool) old('isprivate', $review?->isprivate))>
                Private review
            </label>
        </div>

        <div class="md:col-span-2 xl:col-span-5">
            <label for="comments" class="block text-sm font-medium text-gray-700 mb-1">Comments</label>
            <textarea name="comments"
                      id="comments"
                      rows="5"
                      class="js-auto-resize-textarea w-full min-h-[120px] overflow-hidden rounded-md border-gray-300 shadow-sm text-sm">{{ old('comments', $review?->comments) }}</textarea>
        </div>
    </div>
</div>

<div class="flex items-center justify-end gap-3">
    <a href="{{ $returnTo }}"
       class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
        Cancel
    </a>

    <button type="submit"
            class="inline-flex items-center px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
        {{ $isCreate ? 'Save Trip Review' : 'Save Changes' }}
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tripStaySelect = document.getElementById('tripstayid');
    const tripItemSelect = document.getElementById('tripitemid');
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
            destinationItemId: option.dataset.destinationItemId || '',
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
            if (item.destinationItemId) option.dataset.destinationItemId = item.destinationItemId;

            if (String(item.value) === String(selectedValue)) {
                option.selected = true;
            }

            select.appendChild(option);
        });
    }

    function currentOption(select) {
        return select.options[select.selectedIndex] || null;
    }

    function selectedText(select) {
        const option = currentOption(select);
        return option && option.value ? option.text.trim() : '';
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

        rebuildSelect(destinationSelect, filtered, 'None', stillValid ? currentValue : '');

        if (!stillValid) {
            destinationSelect.value = '';
        }
    }

    function filterDestinationItems(preferredValue = null) {
        const selectedPlaceId = placeSelect.value;
        const selectedDestinationId = destinationSelect.value;
        const currentValue = preferredValue ?? destinationItemSelect.value;

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

        rebuildSelect(destinationItemSelect, filtered, 'None', stillValid ? currentValue : '');

        if (!stillValid) {
            destinationItemSelect.value = '';
        }
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

    function applyDestinationItemContext(destinationItemId, overwrite = true) {
        if (!destinationItemId) return;

        const optionData = destinationItemOptions.find(option => String(option.value) === String(destinationItemId));
        if (!optionData) return;

        if (overwrite || !placeSelect.value) {
            placeSelect.value = optionData.placeId || '';
        }

        filterDestinations();

        if (overwrite || !destinationSelect.value) {
            destinationSelect.value = optionData.destinationId || '';
        }

        filterDestinationItems(destinationItemId);

        if (overwrite || !destinationItemSelect.value) {
            destinationItemSelect.value = destinationItemId;
        }

        updateTitle();
    }

    function applyTripItemContext() {
        if (!tripItemSelect || !tripItemSelect.value) return;

        const option = currentOption(tripItemSelect);
        if (!option || !option.value) return;

        const placeId = option.dataset.placeId || '';
        const destinationId = option.dataset.destinationId || '';
        const destinationItemId = option.dataset.destinationItemId || '';

        placeSelect.value = placeId;
        filterDestinations();

        destinationSelect.value = destinationId;
        filterDestinationItems(destinationItemId || null);

        if (destinationItemId) {
            destinationItemSelect.value = destinationItemId;
        }

        updateTitle();
    }

    function applyTripStayContext() {
        if (!tripStaySelect || !tripStaySelect.value) return;

        const option = currentOption(tripStaySelect);
        if (!option || !option.value) return;

        const placeId = option.dataset.placeId || '';
        const destinationItemId = option.dataset.destinationItemId || '';

        placeSelect.value = placeId;
        filterDestinations();

        if (destinationItemId) {
            applyDestinationItemContext(destinationItemId, true);
        } else {
            filterDestinationItems();
            updateTitle();
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

    destinationItemSelect.addEventListener('change', function () {
        const option = currentOption(destinationItemSelect);

        if (option && option.value) {
            const destinationId = option.dataset.destinationId || '';
            if (destinationId) {
                destinationSelect.value = destinationId;
                filterDestinationItems(option.value);
                destinationItemSelect.value = option.value;
            }
        }

        updateTitle();
    });

    tripItemSelect?.addEventListener('change', function () {
        if (tripItemSelect.value) {
            tripStaySelect && (tripStaySelect.value = '');
            applyTripItemContext();
        }
    });

    tripStaySelect?.addEventListener('change', function () {
        if (tripStaySelect.value) {
            tripItemSelect && (tripItemSelect.value = '');
            applyTripStayContext();
        }
    });

    filterDestinations();
    filterDestinationItems();
    updateTitle();

    if (tripItemSelect?.value) {
        applyTripItemContext();
    } else if (tripStaySelect?.value) {
        applyTripStayContext();
    } else if (destinationItemSelect.value) {
        applyDestinationItemContext(destinationItemSelect.value, false);
    }
});
</script>
</query>