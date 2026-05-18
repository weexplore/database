<div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-medium text-gray-900">Add Trip Booking</h3>
            <p class="mt-1 text-sm text-gray-500">
                Record booking details, dates, costs, links, and payment notes for this trip.
            </p>
        </div>

        <a href="{{ route('trips.bookings.index', $trip) }}"
            class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
            Close
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        <div>
            <label for="providername" class="block text-sm font-medium text-gray-700 mb-1">Provider</label>
            <input type="text" name="providername" id="providername" value="{{ old('providername') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm" required>
        </div>

        <div>
            <label for="bookingtype" class="block text-sm font-medium text-gray-700 mb-1">Booking Type</label>
            <select name="bookingtype" id="bookingtype" class="w-full rounded-md border-gray-300 shadow-sm text-sm" required>
                <option value="">Select type</option>
                @foreach($bookingTypes as $value => $label)
                    <option value="{{ $value }}" @selected(old('bookingtype') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="status_create" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="status" id="status_create" class="w-full rounded-md border-gray-300 shadow-sm text-sm" required>
                <option value="">Select status</option>
                @foreach($bookingStatuses as $value => $label)
                    <option value="{{ $value }}" @selected(old('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="paymentstatus" class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
            <select name="paymentstatus" id="paymentstatus" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">Select payment state</option>
                @foreach($paymentStatuses as $value => $label)
                    <option value="{{ $value }}" @selected(old('paymentstatus') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="placeid" class="block text-sm font-medium text-gray-700 mb-1">Place</label>
            <select name="placeid" id="placeid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">Optional</option>
                @foreach($places as $place)
                    <option value="{{ $place->id }}" @selected((string) old('placeid') === (string) $place->id)>
                        {{ $place->placename }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="destinationid" class="block text-sm font-medium text-gray-700 mb-1">Destination</label>
            <select name="destinationid" id="destinationid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">Optional</option>
                @foreach($destinations as $destination)
                    <option value="{{ $destination->id }}" @selected((string) old('destinationid') === (string) $destination->id)>
                        {{ $destination->destinationname }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="destinationitemid" class="block text-sm font-medium text-gray-700 mb-1">Destination Item</label>
            <select name="destinationitemid" id="destinationitemid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">Optional</option>
                @foreach($destinationItems as $destinationItem)
                    <option value="{{ $destinationItem->id }}" @selected((string) old('destinationitemid') === (string) $destinationItem->id)>
                        {{ $destinationItem->itemname }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="tripstayid" class="block text-sm font-medium text-gray-700 mb-1">Stay</label>
            <select name="tripstayid" id="tripstayid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">Optional</option>
                @foreach($stays as $stay)
                    <option value="{{ $stay->id }}" @selected((string) old('tripstayid') === (string) $stay->id)>
                        {{ $stay->stayname ?: 'Stay #'.$stay->id }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="tripitemid" class="block text-sm font-medium text-gray-700 mb-1">Trip Item</label>
            <select name="tripitemid" id="tripitemid" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">Optional</option>
                @foreach($tripItems as $item)
                    <option value="{{ $item->id }}" @selected((string) old('tripitemid') === (string) $item->id)>
                        {{ $item->title ?: 'Item #'.$item->id }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="requestedon" class="block text-sm font-medium text-gray-700 mb-1">Requested On</label>
            <input type="date" name="requestedon" id="requestedon" value="{{ old('requestedon') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="confirmedon" class="block text-sm font-medium text-gray-700 mb-1">Confirmed On</label>
            <input type="date" name="confirmedon" id="confirmedon" value="{{ old('confirmedon') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="startdate" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
            <input type="date" name="startdate" id="startdate" value="{{ old('startdate') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="enddate" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
            <input type="date" name="enddate" id="enddate" value="{{ old('enddate') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="website" class="block text-sm font-medium text-gray-700 mb-1">Website</label>
            <input type="url" name="website" id="website" value="{{ old('website') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="externalreference" class="block text-sm font-medium text-gray-700 mb-1">Reference</label>
            <input type="text" name="externalreference" id="externalreference" value="{{ old('externalreference') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="estimatedcost" class="block text-sm font-medium text-gray-700 mb-1">Estimated Cost</label>
            <input type="number" step="0.01" min="0" name="estimatedcost" id="estimatedcost" value="{{ old('estimatedcost') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="actualcost" class="block text-sm font-medium text-gray-700 mb-1">Actual Cost</label>
            <input type="number" step="0.01" min="0" name="actualcost" id="actualcost" value="{{ old('actualcost') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="currency" class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
            <select name="currency" id="currency" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">Select currency</option>
                @foreach($currencies as $value => $label)
                    <option value="{{ $value }}" @selected(old('currency', 'AUD') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-2 xl:col-span-3">
            <label for="providercontact" class="block text-sm font-medium text-gray-700 mb-1">Provider Contact</label>
            <input type="text" name="providercontact" id="providercontact" value="{{ old('providercontact') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div class="md:col-span-2 xl:col-span-4">
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea name="notes" id="notes" rows="3"
                        class="js-auto-resize-textarea w-full min-h-[96px] overflow-hidden rounded-md border-gray-300 shadow-sm text-sm">{{ old('notes') }}</textarea>
        </div>

        <div class="md:col-span-2 xl:col-span-4">
            <label for="paymentnotes" class="block text-sm font-medium text-gray-700 mb-1">Payment Notes</label>
            <textarea name="paymentnotes" id="paymentnotes" rows="2"
                        class="js-auto-resize-textarea w-full min-h-[80px] overflow-hidden rounded-md border-gray-300 shadow-sm text-sm">{{ old('paymentnotes') }}</textarea>
        </div>
    </div>
</div>
<div class="flex items-center justify-end gap-3">
    <a href="{{ route('trips.bookings.index', $trip) }}"
        class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
        Cancel
    </a>

    <button type="submit"
            class="inline-flex items-center px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
        Save Trip Booking
    </button>
</div>