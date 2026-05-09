<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Booking
                </h2>
                <p class="text-sm text-gray-500 mt-1">{{ $trip->tripname }}</p>
            </div>
            <a href="{{ route('trips.bookings.index', $trip) }}"
               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Back to Bookings
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="rounded-md bg-green-50 border border-green-200 p-4 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-md bg-red-50 border border-red-200 p-4">
                    <div class="text-sm font-semibold text-red-800 mb-2">Please fix the following:</div>
                    <ul class="list-disc ml-5 text-sm text-red-700 space-y-1">
                        @foreach ($errors->all() as $erroror)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <form method="POST" action="{{ route('trips.bookings.update', [$trip, $booking]) }}" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        <div>
                            <label for="providername" class="block text-sm font-medium text-gray-700 mb-1">Provider</label>
                            <input type="text" name="providername" id="providername"
                                   value="{{ old('providername', $booking->providername) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm" required>
                        </div>

                        <div>
                            <label for="bookingtype" class="block text-sm font-medium text-gray-700 mb-1">Booking Type</label>
                            <select name="bookingtype" id="bookingtype"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm" required>
                                <option value="">Select type</option>
                                @foreach($bookingTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('bookingtype', $booking->bookingtype) === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" id="status"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm" required>
                                <option value="">Select status</option>
                                @foreach($bookingStatuses as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $booking->status) === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="paymentstatus" class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
                            <select name="paymentstatus" id="paymentstatus"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">Select payment state</option>
                                @foreach($paymentStatuses as $value => $label)
                                    <option value="{{ $value }}" @selected(old('paymentstatus', $booking->paymentstatus) === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="currency" class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                            <select name="currency" id="currency"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">Select currency</option>
                                @foreach($currencies as $value => $label)
                                    <option value="{{ $value }}" @selected(old('currency', $booking->currency ?: 'AUD') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="externalreference" class="block text-sm font-medium text-gray-700 mb-1">Reference</label>
                            <input type="text" name="externalreference" id="externalreference"
                                   value="{{ old('externalreference', $booking->externalreference) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div>
                            <label for="tripstayid" class="block text-sm font-medium text-gray-700 mb-1">Stay</label>
                            <select name="tripstayid" id="tripstayid"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">Optional</option>
                                @foreach($stays as $stay)
                                    <option value="{{ $stay->id }}" @selected((string) old('tripstayid', $booking->tripstayid) === (string) $stay->id)>
                                        {{ $stay->stayname ?: 'Stay #'.$stay->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="tripitemid" class="block text-sm font-medium text-gray-700 mb-1">Trip Item</label>
                            <select name="tripitemid" id="tripitemid"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">Optional</option>
                                @foreach($tripItems as $item)
                                    <option value="{{ $item->id }}" @selected((string) old('tripitemid', $booking->tripitemid) === (string) $item->id)>
                                        {{ $item->title ?: 'Item #'.$item->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="destinationid" class="block text-sm font-medium text-gray-700 mb-1">Destination</label>
                            <select name="destinationid" id="destinationid"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">Optional</option>
                                @foreach($destinations as $destination)
                                    <option value="{{ $destination->id }}" @selected((string) old('destinationid', $booking->destinationid) === (string) $destination->id)>
                                        {{ $destination->destinationname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="destinationitemid" class="block text-sm font-medium text-gray-700 mb-1">Destination Item</label>
                            <select name="destinationitemid" id="destinationitemid"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">Optional</option>
                                @foreach($destinationItems as $destinationItem)
                                    <option value="{{ $destinationItem->id }}" @selected((string) old('destinationitemid', $booking->destinationitemid) === (string) $destinationItem->id)>
                                        {{ $destinationItem->itemname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="placeid" class="block text-sm font-medium text-gray-700 mb-1">Place</label>
                            <select name="placeid" id="placeid"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">Optional</option>
                                @foreach($places as $place)
                                    <option value="{{ $place->id }}" @selected((string) old('placeid', $booking->placeid) === (string) $place->id)>
                                        {{ $place->placename }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="requestedon" class="block text-sm font-medium text-gray-700 mb-1">Requested On</label>
                            <input type="date" name="requestedon" id="requestedon"
                                   value="{{ old('requestedon', optional($booking->requestedon)->format('Y-m-d')) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div>
                            <label for="confirmedon" class="block text-sm font-medium text-gray-700 mb-1">Confirmed On</label>
                            <input type="date" name="confirmedon" id="confirmedon"
                                   value="{{ old('confirmedon', optional($booking->confirmedon)->format('Y-m-d')) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div>
                            <label for="startdate" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input type="date" name="startdate" id="startdate"
                                   value="{{ old('startdate', optional($booking->startdate)->format('Y-m-d')) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div>
                            <label for="enddate" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input type="date" name="enddate" id="enddate"
                                   value="{{ old('enddate', optional($booking->enddate)->format('Y-m-d')) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div>
                            <label for="website" class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                            <input type="url" name="website" id="website"
                                   value="{{ old('website', $booking->website) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div>
                            <label for="estimatedcost" class="block text-sm font-medium text-gray-700 mb-1">Estimated Cost</label>
                            <input type="number" step="0.01" min="0" name="estimatedcost" id="estimatedcost"
                                   value="{{ old('estimatedcost', $booking->estimatedcost) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div>
                            <label for="actualcost" class="block text-sm font-medium text-gray-700 mb-1">Actual Cost</label>
                            <input type="number" step="0.01" min="0" name="actualcost" id="actualcost"
                                   value="{{ old('actualcost', $booking->actualcost) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>
                    </div>

                    <div>
                        <label for="providercontact" class="block text-sm font-medium text-gray-700 mb-1">Provider Contact</label>
                        <input type="text" name="providercontact" id="providercontact"
                               value="{{ old('providercontact', $booking->providercontact) }}"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" id="notes" rows="4"
                                  class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('notes', $booking->notes) }}</textarea>
                    </div>

                    <div>
                        <label for="paymentnotes" class="block text-sm font-medium text-gray-700 mb-1">Payment Notes</label>
                        <textarea name="paymentnotes" id="paymentnotes" rows="3"
                                  class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('paymentnotes', $booking->paymentnotes) }}</textarea>
                    </di

                    <div class="flex items-center justify-between gap-3 pt-4 border-t border-gray-200">
                        <a href="{{ route('trips.bookings.index', $trip) }}"
                           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>

                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-gray-900 border border-transparent rounded-md text-sm font-semibold text-white hover:bg-gray-800">
                            Save Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>