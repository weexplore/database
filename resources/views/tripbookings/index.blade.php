<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Trip Bookings
                </h2>
                <p class="text-sm text-gray-500 mt-1">{{ $trip->tripname }}</p>
            </div>
            <a href="{{ route('trips.edit', $trip) }}"
               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Back to Trip
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">
            @if (session('success'))
                <div class="rounded-md bg-green-50 border border-green-200 p-4 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-md bg-red-50 border border-red-200 p-4">
                    <div class="text-sm font-semibold text-red-800 mb-2">Please fix the following:</div>
                    <ul class="list-disc ml-5 text-sm text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="p-4 border-b border-gray-200">
                    <form method="GET" action="{{ route('trips.bookings.index', $trip) }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4">
                            <div>
                                <label for="search" class="block text-xs font-medium text-gray-700 mb-1">Search</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                       placeholder="Provider, ref, notes">
                            </div>

                            <div>
                                <label for="booking_type" class="block text-xs font-medium text-gray-700 mb-1">Booking Type</label>
                                <select name="booking_type" id="booking_type" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">All types</option>
                                    @foreach($bookingTypes as $value => $label)
                                        <option value="{{ $value }}" @selected(request('booking_type') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="status" class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">All statuses</option>
                                    @foreach($bookingStatuses as $value => $label)
                                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="payment_status" class="block text-xs font-medium text-gray-700 mb-1">Payment</label>
                                <select name="payment_status" id="payment_status" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">All payment states</option>
                                    @foreach($paymentStatuses as $value => $label)
                                        <option value="{{ $value }}" @selected(request('payment_status') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="trip_stay_id" class="block text-xs font-medium text-gray-700 mb-1">Stay</label>
                                <select name="trip_stay_id" id="trip_stay_id" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">All stays</option>
                                    @foreach($stays as $stay)
                                        <option value="{{ $stay->id }}" @selected((string) request('trip_stay_id') === (string) $stay->id)>
                                            {{ $stay->stayname ?: 'Stay #'.$stay->id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="trip_item_id" class="block text-xs font-medium text-gray-700 mb-1">Trip Item</label>
                                <select name="trip_item_id" id="trip_item_id" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">All items</option>
                                    @foreach($tripItems as $item)
                                        <option value="{{ $item->id }}" @selected((string) request('trip_item_id') === (string) $item->id)>
                                            {{ $item->title ?: 'Item #'.$item->id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-gray-900 border border-transparent rounded-md text-sm font-semibold text-white hover:bg-gray-800">
                                Filter
                            </button>
                            <a href="{{ route('trips.bookings.index', $trip) }}"
                               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <div class="p-4 border-b border-gray-200 bg-gray-50">
                    <form method="POST" action="{{ route('trips.bookings.store', $trip) }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div>
                                <label for="providername" class="block text-xs font-medium text-gray-700 mb-1">Provider</label>
                                <input type="text" name="providername" id="providername" value="{{ old('providername') }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm" required>
                            </div>

                            <div>
                                <label for="bookingtype" class="block text-xs font-medium text-gray-700 mb-1">Booking Type</label>
                                <select name="bookingtype" id="bookingtype" class="w-full rounded-md border-gray-300 shadow-sm text-sm" required>
                                    <option value="">Select type</option>
                                    @foreach($bookingTypes as $value => $label)
                                        <option value="{{ $value }}" @selected(old('bookingtype') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="status_create" class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" id="status_create" class="w-full rounded-md border-gray-300 shadow-sm text-sm" required>
                                    <option value="">Select status</option>
                                    @foreach($bookingStatuses as $value => $label)
                                        <option value="{{ $value }}" @selected(old('status') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="paymentstatus" class="block text-xs font-medium text-gray-700 mb-1">Payment Status</label>
                                <select name="paymentstatus" id="paymentstatus" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Select payment state</option>
                                    @foreach($paymentStatuses as $value => $label)
                                        <option value="{{ $value }}" @selected(old('paymentstatus') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="tripstayid" class="block text-xs font-medium text-gray-700 mb-1">Stay</label>
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
                                <label for="tripitemid" class="block text-xs font-medium text-gray-700 mb-1">Trip Item</label>
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
                                <label for="destinationid" class="block text-xs font-medium text-gray-700 mb-1">Destination</label>
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
                                <label for="destinationitemid" class="block text-xs font-medium text-gray-700 mb-1">Destination Item</label>
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
                                <label for="placeid" class="block text-xs font-medium text-gray-700 mb-1">Place</label>
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
                                <label for="requestedon" class="block text-xs font-medium text-gray-700 mb-1">Requested On</label>
                                <input type="date" name="requestedon" id="requestedon" value="{{ old('requestedon') }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="confirmedon" class="block text-xs font-medium text-gray-700 mb-1">Confirmed On</label>
                                <input type="date" name="confirmedon" id="confirmedon" value="{{ old('confirmedon') }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="startdate" class="block text-xs font-medium text-gray-700 mb-1">Start Date</label>
                                <input type="date" name="startdate" id="startdate" value="{{ old('startdate') }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="enddate" class="block text-xs font-medium text-gray-700 mb-1">End Date</label>
                                <input type="date" name="enddate" id="enddate" value="{{ old('enddate') }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="website" class="block text-xs font-medium text-gray-700 mb-1">Website</label>
                                <input type="url" name="website" id="website" value="{{ old('website') }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="externalreference" class="block text-xs font-medium text-gray-700 mb-1">Reference</label>
                                <input type="text" name="externalreference" id="externalreference" value="{{ old('externalreference') }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="estimatedcost" class="block text-xs font-medium text-gray-700 mb-1">Estimated Cost</label>
                                <input type="number" step="0.01" min="0" name="estimatedcost" id="estimatedcost" value="{{ old('estimatedcost') }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="actualcost" class="block text-xs font-medium text-gray-700 mb-1">Actual Cost</label>
                                <input type="number" step="0.01" min="0" name="actualcost" id="actualcost" value="{{ old('actualcost') }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="currency" class="block text-xs font-medium text-gray-700 mb-1">Currency</label>
                                <select name="currency" id="currency" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Select currency</option>
                                    @foreach($currencies as $value => $label)
                                        <option value="{{ $value }}" @selected(old('currency', 'AUD') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-2 xl:col-span-4">
                                <label for="providercontact" class="block text-xs font-medium text-gray-700 mb-1">Provider Contact</label>
                                <input type="text" name="providercontact" id="providercontact" value="{{ old('providercontact') }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div class="md:col-span-2 xl:col-span-4">
                                <label for="notes" class="block text-xs font-medium text-gray-700 mb-1">Notes</label>
                                <textarea name="notes" id="notes" rows="3"
                                          class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('notes') }}</textarea>
                            </div>

                            <div class="md:col-span-2 xl:col-span-4">
                                <label for="paymentnotes" class="block text-xs font-medium text-gray-700 mb-1">Payment Notes</label>
                                <textarea name="paymentnotes" id="paymentnotes" rows="2"
                                          class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('paymentnotes') }}</textarea>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-gray-900 border border-transparent rounded-md text-sm font-semibold text-white hover:bg-gray-800">
                                Add Booking
                            </button>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Provider</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Type</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Dates</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Reference</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Costs</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Links</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($bookings as $booking)
                                <tr>
                                    <td class="px-4 py-3 align-top">
                                        <div class="font-semibold text-gray-900">{{ $booking->providername }}</div>
                                        @if($booking->providercontact)
                                            <div class="text-xs text-gray-500 mt-1">{{ $booking->providercontact }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <div class="text-gray-800">{{ $bookingTypes[$booking->bookingtype] ?? $booking->bookingtype }}</div>
                                        @if($booking->stay)
                                            <div class="text-xs text-gray-500 mt-1">Stay: {{ $booking->stay->stayname ?: 'Stay #'.$booking->stay->id }}</div>
                                        @elseif($booking->tripItem)
                                            <div class="text-xs text-gray-500 mt-1">Item: {{ $booking->tripItem->title ?: 'Item #'.$booking->tripItem->id }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <div class="text-gray-800">{{ $bookingStatuses[$booking->status] ?? $booking->status }}</div>
                                        @if($booking->paymentstatus)
                                            <div class="text-xs text-gray-500 mt-1">{{ $paymentStatuses[$booking->paymentstatus] ?? $booking->paymentstatus }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700">
                                        @if($booking->startdate)
                                            <div>{{ $booking->startdate->format('d/m/Y') }}</div>
                                        @endif
                                        @if($booking->enddate)
                                            <div class="text-xs text-gray-500 mt-1">to {{ $booking->enddate->format('d/m/Y') }}</div>
                                        @endif
                                        @if(!$booking->startdate && $booking->requestedon)
                                            <div>Requested {{ $booking->requestedon->format('d/m/Y') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700">
                                        {{ $booking->externalreference ?: '—' }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700">
                                        @if(!is_null($booking->estimatedcost))
                                            <div>Est: {{ $booking->currency ?: 'AUD' }} {{ number_format((float) $booking->estimatedcost, 2) }}</div>
                                        @endif
                                        @if(!is_null($booking->actualcost))
                                            <div class="text-xs text-gray-500 mt-1">Act: {{ $booking->currency ?: 'AUD' }} {{ number_format((float) $booking->actualcost, 2) }}</div>
                                        @endif
                                        @if(is_null($booking->estimatedcost) && is_null($booking->actualcost))
                                            <span>—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        @if($booking->website)
                                            <a href="{{ $booking->website }}" target="_blank" rel="noopener noreferrer"
                                               class="text-indigo-600 hover:text-indigo-800 text-sm">
                                                Open
                                            </a>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 align-top text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('trips.bookings.edit', [$trip, $booking]) }}"
                                               class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                                                Edit
                                            </a>

                                            <form method="POST" action="{{ route('trips.bookings.destroy', [$trip, $booking]) }}"
                                                  onsubmit="return confirm('Delete this booking?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-md border border-red-300 bg-white text-red-700 hover:bg-red-50">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500">
                                        No bookings found for this trip.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-4 border-t border-gray-200">
                    {{ $bookings->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>