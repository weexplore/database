@php
    $currentDestinationId = old('destinationid', $selectedDestinationId ?? ($destinationItem->destinationid ?? ''));
    $currentPlaceId = old('placeid', $destinationItem->placeid ?? '');
    $currentItemName = old('itemname', $destinationItem->itemname ?? '');
    $currentItemType = old('itemtype', $destinationItem->itemtype ?? '');
    $currentShortDescription = old('shortdescription', $destinationItem->shortdescription ?? '');
    $currentNotes = old('notes', $destinationItem->notes ?? '');
    $currentEstimatedCostPerPerson = old('estimatedcostperperson', $destinationItem->estimatedcostperperson ?? '');
    $currentEstimatedTotalCost = old('estimatedtotalcost', $destinationItem->estimatedtotalcost ?? '');
    $currentRecommendedStayMinutes = old('recommendedstayminutes', $destinationItem->recommendedstayminutes ?? '');
    $currentSortOrder = old('sortorder', $destinationItem->sortorder ?? '');
    $currentCaravanAccessNotes = old('caravanaccessnotes', $destinationItem->caravanaccessnotes ?? '');
    $currentBookingRequired = old('bookingrequired', $destinationItem->bookingrequired ?? false);
    $currentIsActive = old('isactive', $destinationItem->isactive ?? true);
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label for="destinationid" class="block text-sm font-medium text-gray-700">
            Destination
        </label>
        <select id="destinationid"
                name="destinationid"
                class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm"
                required>
            <option value="">Select</option>
            @foreach($destinations as $destination)
                <option value="{{ $destination->id }}"
                    @selected((string) $currentDestinationId === (string) $destination->id)>
                    {{ $destination->destinationname }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="placeid" class="block text-sm font-medium text-gray-700">
            Linked Place
        </label>
        <select id="placeid"
                name="placeid"
                class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
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
        <label for="itemname" class="block text-sm font-medium text-gray-700">
            Item name
        </label>
        <input type="text"
               id="itemname"
               name="itemname"
               value="{{ $currentItemName }}"
               class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm"
               required>
    </div>

    <div>
        <label for="itemtype" class="block text-sm font-medium text-gray-700">
            Item type
        </label>
        <select id="itemtype"
                name="itemtype"
                class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
            <option value="">None</option>
            @foreach($itemTypes as $value => $label)
                <option value="{{ $value }}"
                    @selected((string) $currentItemType === (string) $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="md:col-span-2">
        <label for="shortdescription" class="block text-sm font-medium text-gray-700">
            Short description
        </label>
        <textarea id="shortdescription"
                  name="shortdescription"
                  rows="3"
                  class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">{{ $currentShortDescription }}</textarea>
    </div>

    <div class="md:col-span-2">
        <label for="notes" class="block text-sm font-medium text-gray-700">
            Notes
        </label>
        <textarea id="notes"
                  name="notes"
                  rows="4"
                  class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">{{ $currentNotes }}</textarea>
    </div>

    <div>
        <label for="estimatedcostperperson" class="block text-sm font-medium text-gray-700">
            Estimated cost per person
        </label>
        <input type="number"
               step="0.01"
               id="estimatedcostperperson"
               name="estimatedcostperperson"
               value="{{ $currentEstimatedCostPerPerson }}"
               class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>

    <div>
        <label for="estimatedtotalcost" class="block text-sm font-medium text-gray-700">
            Estimated total cost
        </label>
        <input type="number"
               step="0.01"
               id="estimatedtotalcost"
               name="estimatedtotalcost"
               value="{{ $currentEstimatedTotalCost }}"
               class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>

    <div>
        <label for="recommendedstayminutes" class="block text-sm font-medium text-gray-700">
            Recommended stay minutes
        </label>
        <input type="number"
               id="recommendedstayminutes"
               name="recommendedstayminutes"
               value="{{ $currentRecommendedStayMinutes }}"
               class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>

    <div>
        <label for="sortorder" class="block text-sm font-medium text-gray-700">
            Sort order
        </label>
        <input type="number"
               id="sortorder"
               name="sortorder"
               value="{{ $currentSortOrder }}"
               class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>

    <div class="md:col-span-2">
        <label for="caravanaccessnotes" class="block text-sm font-medium text-gray-700">
            Caravan access notes
        </label>
        <textarea id="caravanaccessnotes"
                  name="caravanaccessnotes"
                  rows="3"
                  class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">{{ $currentCaravanAccessNotes }}</textarea>
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