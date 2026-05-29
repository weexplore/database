@php
    $currentDestinationId = old('destinationid', $selectedDestinationId ?? ($destinationItem->destinationid ?? ''));
    $currentPlaceId = old('placeid', $destinationItem->placeid ?? '');
    $currentItemName = old('itemname', $destinationItem->itemname ?? '');
    $currentShortDescription = old('shortdescription', $destinationItem->shortdescription ?? '');
    $currentNotes = old('notes', $destinationItem->notes ?? '');
    $currentEstimatedCostPerPerson = old('estimatedcostperperson', $destinationItem->estimatedcostperperson ?? '');
    $currentEstimatedTotalCost = old('estimatedtotalcost', $destinationItem->estimatedtotalcost ?? '');
    $currentRecommendedStayMinutes = old('recommendedstayminutes', $destinationItem->recommendedstayminutes ?? '');
    $currentSortOrder = old('sortorder', $destinationItem->sortorder ?? '');
    $currentCaravanAccessNotes = old('caravanaccessnotes', $destinationItem->caravanaccessnotes ?? '');
    $currentDisabilityAccessNotes = old('disabilityaccessnotes', $destinationItem->disabilityaccessnotes ?? '');
    $currentBookingRequired = old('bookingrequired', $destinationItem->bookingrequired ?? false);
    $currentIsActive = old('isactive', $destinationItem->isactive ?? true);

    $relatedTypeIds = [];

    if (old('itemtype_ids')) {
        $relatedTypeIds = (array) old('itemtype_ids');
    } elseif (isset($destinationItem) && $destinationItem->relationLoaded('itemTypes')) {
        $relatedTypeIds = $destinationItem->itemTypes->pluck('id')->all();
    } elseif (isset($destinationItem)) {
        $relatedTypeIds = $destinationItem->itemTypes()->pluck('destination_item_types.id')->all();
    }

    $currentItemTypeIds = collect($relatedTypeIds)
        ->map(fn ($id) => (string) $id)
        ->all();

    $hasSelectedItemTypes = count($currentItemTypeIds) > 0;
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label for="placeid" class="block text-sm font-medium text-gray-700 mb-1">
            Linked Place
        </label>
        <select id="placeid"
                name="placeid"
                class="w-full rounded-md border-gray-300 shadow-sm text-sm">
            <option value="">None</option>
            @foreach($places as $place)
                <option value="{{ $place->id }}"
                    @selected((string) $currentPlaceId === (string) $place->id)>
                    {{ $place->placename }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="destinationid" class="block text-sm font-medium text-gray-700 mb-1">
            Destination
        </label>
        <select id="destinationid"
                name="destinationid"
                class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                required
                data-selected-destination-id="{{ (string) $currentDestinationId }}">
            <option value="">Select</option>
            @foreach($destinations as $destination)
                <option value="{{ $destination->id }}"
                        data-place-id="{{ $destination->placeid }}"
                        @selected((string) $currentDestinationId === (string) $destination->id)>
                    {{ $destination->destinationname }}
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-gray-500">
            Destination list is filtered by the selected place.
        </p>
    </div>

    <div class="md:col-span-2">
        <label for="itemname" class="block text-sm font-medium text-gray-700 mb-1">
            Item name
        </label>
        <input type="text"
               id="itemname"
               name="itemname"
               value="{{ $currentItemName }}"
               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
               required>
    </div>

    <div class="md:col-span-2">
        <div class="flex items-start justify-between gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">
                    Item types
                </label>
                <p class="mt-1 text-xs text-gray-500">
                    Show selected item types by default. Open the full list only when you want to add or change them.
                </p>
            </div>

            <button type="button"
                    id="toggle-item-types-panel"
                    class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-xs sm:text-sm">
                {{ $hasSelectedItemTypes ? 'Add or change types' : 'Hide types' }}
            </button>
        </div>

        <div id="selected-item-types-summary"
             class="mt-3 flex flex-wrap gap-2 {{ $hasSelectedItemTypes ? '' : 'hidden' }}">
            @foreach($itemTypes as $itemType)
                @php
                    $itemTypeId = is_object($itemType) ? $itemType->id : null;
                    $itemTypeName = is_object($itemType) ? $itemType->typename : (string) $itemType;
                @endphp

                @if($itemTypeId && in_array((string) $itemTypeId, $currentItemTypeIds, true))
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-medium border border-blue-200">
                        {{ $itemTypeName }}
                    </span>
                @endif
            @endforeach
        </div>

        <div id="item-types-panel" class="mt-4 {{ $hasSelectedItemTypes ? 'hidden' : '' }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach($itemTypes as $itemType)
                    @php
                        $itemTypeId = is_object($itemType) ? $itemType->id : null;
                        $itemTypeName = is_object($itemType) ? $itemType->typename : (string) $itemType;
                    @endphp

                    @if($itemTypeId)
                        <label class="flex items-center gap-2 text-sm text-gray-700 rounded border border-gray-200 px-3 py-2">
                            <input type="checkbox"
                                   name="itemtype_ids[]"
                                   value="{{ $itemTypeId }}"
                                   class="rounded border-gray-300 text-blue-600 shadow-sm destination-item-type-checkbox"
                                   @checked(in_array((string) $itemTypeId, $currentItemTypeIds, true))>
                            <span>{{ $itemTypeName }}</span>
                        </label>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <div class="md:col-span-2">
        <x-forms.markdown-field
            name="shortdescription"
            id="shortdescription"
            label="Short description"
            :value="$currentShortDescription"
            rows="3"
            minRows="3"
            maxRows="10"
            placeholder="Concise summary of this destination item..."
            help="Markdown supported. Keep this brief — it’s used in lists and quick overviews."
        />
    </div>

    <div class="md:col-span-2">
        <x-forms.markdown-field
            name="notes"
            id="notes"
            label="Notes"
            :value="$currentNotes"
            rows="5"
            minRows="4"
            maxRows="16"
            placeholder="Add notes, context, and commentary for this item..."
            help="Markdown supported. Use Show preview to view formatted content."
        />
    </div>

    <div>
        <label for="estimatedcostperperson" class="block text-sm font-medium text-gray-700 mb-1">
            Estimated cost per person
        </label>
        <input type="number"
               step="0.01"
               id="estimatedcostperperson"
               name="estimatedcostperperson"
               value="{{ $currentEstimatedCostPerPerson }}"
               class="w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>

    <div>
        <label for="estimatedtotalcost" class="block text-sm font-medium text-gray-700 mb-1">
            Estimated total cost
        </label>
        <input type="number"
               step="0.01"
               id="estimatedtotalcost"
               name="estimatedtotalcost"
               value="{{ $currentEstimatedTotalCost }}"
               class="w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>

    <div>
        <label for="recommendedstayminutes" class="block text-sm font-medium text-gray-700 mb-1">
            Recommended stay minutes
        </label>
        <input type="number"
               id="recommendedstayminutes"
               name="recommendedstayminutes"
               value="{{ $currentRecommendedStayMinutes }}"
               class="w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>

    <div>
        <label for="sortorder" class="block text-sm font-medium text-gray-700 mb-1">
            Sort order
        </label>
        <input type="number"
               id="sortorder"
               name="sortorder"
               value="{{ $currentSortOrder }}"
               class="w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>

    <div class="md:col-span-2">
        <x-forms.markdown-field
            name="caravanaccessnotes"
            id="caravanaccessnotes"
            label="Caravan access notes"
            :value="$currentCaravanAccessNotes"
            rows="4"
            minRows="3"
            maxRows="14"
            placeholder="Record turning circles, road width, gradients, low branches, or other caravan-specific details..."
            help="Markdown supported. Use bullets for checklists or key cautions."
        />
    </div>

    <div class="md:col-span-2">
        <x-forms.markdown-field
            name="disabilityaccessnotes"
            id="disabilityaccessnotes"
            label="Disability access notes"
            :value="$currentDisabilityAccessNotes"
            rows="4"
            minRows="3"
            maxRows="14"
            placeholder="Describe accessibility, surfaces, ramps, steps, handrails, toilets, and any limitations..."
            help="Markdown supported. Use lists or headings to make access details easy to scan."
        />
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="bookingrequired" value="0">
        <input type="checkbox"
               id="bookingrequired"
               name="bookingrequired"
               value="1"
               class="rounded border-gray-300 text-blue-600 shadow-sm"
               @checked((bool) $currentBookingRequired)>
        <label for="bookingrequired" class="text-sm text-gray-700">
            Booking required
        </label>
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="isactive" value="0">
        <input type="checkbox"
               id="isactive"
               name="isactive"
               value="1"
               class="rounded border-gray-300 text-blue-600 shadow-sm"
               @checked((bool) $currentIsActive)>
        <label for="isactive" class="text-sm text-gray-700">
            Active
        </label>
    </div>
</div>