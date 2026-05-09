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

<div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
    <div>
        <label for="triplegid" class="block text-sm font-medium text-gray-700">Trip leg</label>
        <select id="triplegid" name="triplegid" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
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
        <label for="tripstayid" class="block text-sm font-medium text-gray-700">Trip stay</label>
        <select id="tripstayid" name="tripstayid" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
            <option value="">None</option>
            @foreach($tripStays as $tripStay)
                <option value="{{ $tripStay->id }}" @selected((string) $currentTripStayId === (string) $tripStay->id)>
                    {{ $tripStay->stayname }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="destinationid" class="block text-sm font-medium text-gray-700">Destination</label>
        <select id="destinationid" name="destinationid" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
            <option value="">None</option>
            @foreach($destinations as $destination)
                <option value="{{ $destination->id }}" @selected((string) $currentDestinationId === (string) $destination->id)>
                    {{ $destination->destinationname }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="destinationitemid" class="block text-sm font-medium text-gray-700">Destination item</label>
        <select id="destinationitemid" name="destinationitemid" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
            <option value="">None</option>
            @foreach($destinationItems as $destinationItem)
                <option value="{{ $destinationItem->id }}" @selected((string) $currentDestinationItemId === (string) $destinationItem->id)>
                    {{ $destinationItem->itemname }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="placeid" class="block text-sm font-medium text-gray-700">Place</label>
        <select id="placeid" name="placeid" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
            <option value="">None</option>
            @foreach($places as $place)
                <option value="{{ $place->id }}" @selected((string) $currentPlaceId === (string) $place->id)>
                    {{ $place->placename }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="bookingid" class="block text-sm font-medium text-gray-700">Booking</label>
        <select id="bookingid" name="bookingid" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
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

    <div>
        <label for="itemdate" class="block text-sm font-medium text-gray-700">Item date</label>
        <input type="date" id="itemdate" name="itemdate" value="{{ $currentItemDate }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>

    <div>
        <label for="itemtype" class="block text-sm font-medium text-gray-700">Item type</label>
        <select id="itemtype" name="itemtype" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
            <option value="">Select</option>
            @foreach($itemTypeOptions as $value => $label)
                <option value="{{ $value }}" @selected((string) $currentItemType === (string) $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
        <select id="status" name="status" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
            <option value="">Select</option>
            @foreach($itemStatusOptions as $value => $label)
                <option value="{{ $value }}" @selected((string) $currentStatus === (string) $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
        <select id="priority" name="priority" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
            <option value="">Select</option>
            @foreach($priorityOptions as $value => $label)
                <option value="{{ $value }}" @selected((string) $currentPriority === (string) $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="md:col-span-2">
        <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
        <input type="text" id="title" name="title" value="{{ $currentTitle }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm" required>
    </div>

    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
        <textarea id="description" name="description" rows="3" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">{{ $currentDescription }}</textarea>
    </div>

    <div>
        <label for="startdatetime" class="block text-sm font-medium text-gray-700">Start date/time</label>
        <input type="datetime-local" id="startdatetime" name="startdatetime" value="{{ $currentStartDateTime }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>

    <div>
        <label for="enddatetime" class="block text-sm font-medium text-gray-700">End date/time</label>
        <input type="datetime-local" id="enddatetime" name="enddatetime" value="{{ $currentEndDateTime }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>

    <div>
        <label for="peoplecount" class="block text-sm font-medium text-gray-700">People count</label>
        <input type="number" id="peoplecount" name="peoplecount" value="{{ $currentPeopleCount }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm" min="1">
    </div>

    <div>
        <label for="sortorder" class="block text-sm font-medium text-gray-700">Sort order</label>
        <input type="number" id="sortorder" name="sortorder" value="{{ $currentSortOrder }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm" min="0">
    </div>

    <div>
        <label for="estimatedcostperperson" class="block text-sm font-medium text-gray-700">Estimated cost per person</label>
        <input type="number" step="0.01" id="estimatedcostperperson" name="estimatedcostperperson" value="{{ $currentEstimatedCostPerPerson }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>

    <div>
        <label for="estimatedtotalcost" class="block text-sm font-medium text-gray-700">Estimated total cost</label>
        <input type="number" step="0.01" id="estimatedtotalcost" name="estimatedtotalcost" value="{{ $currentEstimatedTotalCost }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>

    <div>
        <label for="actualcost" class="block text-sm font-medium text-gray-700">Actual cost</label>
        <input type="number" step="0.01" id="actualcost" name="actualcost" value="{{ $currentActualCost }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>

    <div class="flex items-center gap-2 pt-7">
        <input type="hidden" name="isfullday" value="0">
        <input type="checkbox" id="isfullday" name="isfullday" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm" @checked((bool) $currentIsFullDay)>
        <label for="isfullday" class="text-sm text-gray-700">Full day item</label>
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="allocateasdailycost" value="0">
        <input type="checkbox" id="allocateasdailycost" name="allocateasdailycost" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm" @checked((bool) $currentAllocateAsDailyCost)>
        <label for="allocateasdailycost" class="text-sm text-gray-700">Allocate as daily cost</label>
    </div>

    <div class="md:col-span-2">
        <label for="notesinternal" class="block text-sm font-medium text-gray-700">Internal notes</label>
        <textarea id="notesinternal" name="notesinternal" rows="4" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">{{ $currentNotesInternal }}</textarea>
    </div>
</div>