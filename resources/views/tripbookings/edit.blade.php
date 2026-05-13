<x-app-layout>
    @php
        $returnTo = $returnTo ?? route('trips.bookings.index', $trip);
        $bookingAttachments = $bookingAttachments ?? collect();
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Booking
                </h2>
                <p class="text-sm text-gray-500 mt-1">{{ $trip->tripname }}</p>
            </div>
            <a href="{{ $returnTo }}"
               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Back to Bookings
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <div class="xl:col-span-2 space-y-6">
                    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                        <form method="POST" action="{{ route('trips.bookings.update', [$trip, $booking]) }}" class="p-6 space-y-6">
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="return_to" value="{{ $returnTo }}">

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
                            </div>

                            <div class="flex items-center justify-between gap-3 pt-4 border-t border-gray-200">
                                <a href="{{ $returnTo }}"
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

                <div class="xl:col-span-1 space-y-6">
                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900">Booking Attachments</h3>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ $bookingAttachments->count() }} linked record{{ $bookingAttachments->count() === 1 ? '' : 's' }}
                            </p>
                        </div>

                        <div class="p-4 space-y-3">
                            @forelse ($bookingAttachments as $bookingAttachment)
                                <div class="border border-gray-200 rounded-md px-3 py-2">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $bookingAttachment->description ?: $bookingAttachment->originalfilename ?: 'Attachment' }}
                                    </div>

                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $bookingAttachment->attachmenttype ?: 'File' }}
                                        @if ($bookingAttachment->uploadedat)
                                            · {{ \Illuminate\Support\Carbon::parse($bookingAttachment->uploadedat)->format('d M Y') }}
                                        @endif
                                        @if ($bookingAttachment->isprimary)
                                            · Primary
                                        @endif
                                    </div>

                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @if (Route::has('attachments.view'))
                                            <a href="{{ route('attachments.view', $bookingAttachment) }}"
                                               target="_blank"
                                               rel="noopener noreferrer"
                                               class="inline-flex items-center px-2.5 py-1.5 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-xs">
                                                View
                                            </a>
                                        @endif

                                        @if (Route::has('attachments.download'))
                                            <a href="{{ route('attachments.download', $bookingAttachment) }}"
                                               class="inline-flex items-center px-2.5 py-1.5 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-xs">
                                                Download
                                            </a>
                                        @endif

                                        @if (Route::has('attachments.edit'))
                                            <a href="{{ route('attachments.edit', [
                                                    'attachment' => $bookingAttachment,
                                                    'return_to' => url()->full(),
                                                ]) }}"
                                               class="inline-flex items-center px-2.5 py-1.5 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-xs">
                                                Edit
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">
                                    No attachments are currently linked to this booking.
                                </p>
                            @endforelse

                            @if (Route::has('attachments.index'))
                                <div class="flex flex-wrap gap-2 pt-2">
                                    <a href="{{ route('attachments.index', [
                                            'linkedtype' => 'booking',
                                            'linkedid' => $booking->id,
                                            'return_to' => url()->full(),
                                        ]) }}"
                                       class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-sm">
                                        View Attachments
                                    </a>

                                    <a href="{{ route('attachments.index', [
                                            'linkedtype' => 'booking',
                                            'linkedid' => $booking->id,
                                            'show_create' => 1,
                                            'return_to' => url()->full(),
                                        ]) }}"
                                       class="inline-flex items-center px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                        Add Attachment
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Record summary</h3>
                        <dl class="mt-3 space-y-2 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Booking ID</dt>
                                <dd class="text-gray-900">{{ $booking->id }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Trip</dt>
                                <dd class="text-gray-900">{{ $trip->tripname }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Provider</dt>
                                <dd class="text-gray-900">{{ $booking->providername ?: '—' }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Status</dt>
                                <dd class="text-gray-900">{{ $bookingStatuses[$booking->status] ?? ($booking->status ?: '—') }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Payment</dt>
                                <dd class="text-gray-900">{{ $paymentStatuses[$booking->paymentstatus] ?? ($booking->paymentstatus ?: '—') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>