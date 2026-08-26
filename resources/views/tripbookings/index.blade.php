@php
    $showCreate = $showCreate ?? (request()->boolean('show_create') || $errors->any());
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Trip Bookings
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $trip->tripname }}
                </p>
                <p class="mt-1 text-sm text-gray-500">
                    Status: {{ ucfirst($trip->tripstatus) }} ·
                    Start: {{ optional($trip->startdate)->format('d M Y') ?? '—' }} ·
                    End: {{ optional($trip->enddate)->format('d M Y') ?? '—' }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('trips.bookings.index', array_merge(['trip' => $trip->id], request()->query(), ['show_create' => 1])) }}"
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                    Add Trip Booking
                </a>

                <a href="{{ route('trips.edit', ['trip' => $trip, 'tab' => 'workflow']) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                    Back to Trip
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">
            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('trips.bookings.index', $trip) }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input type="text"
                                   name="search"
                                   id="search"
                                   value="{{ request('search') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                   placeholder="Provider, ref, notes">
                        </div>

                        <div>
                            <label for="booking_type" class="block text-sm font-medium text-gray-700 mb-1">Booking Type</label>
                            <select name="booking_type" id="booking_type" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All types</option>
                                @foreach($bookingTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(request('booking_type') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All statuses</option>
                                @foreach($bookingStatuses as $value => $label)
                                    <option value="{{ $value }}" @selected(request('status') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="payment_status" class="block text-sm font-medium text-gray-700 mb-1">Payment</label>
                            <select name="payment_status" id="payment_status" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All payment states</option>
                                @foreach($paymentStatuses as $value => $label)
                                    <option value="{{ $value }}" @selected(request('payment_status') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="trip_stay_id" class="block text-sm font-medium text-gray-700 mb-1">Stay</label>
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
                            <label for="trip_item_id" class="block text-sm font-medium text-gray-700 mb-1">Trip Item</label>
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
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                            Filter
                        </button>

                        <a href="{{ route('trips.bookings.index', $trip) }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            @if(!empty($showCreate))
                <form method="POST"
                      action="{{ route('trips.bookings.store', $trip) }}"
                      id="trip-booking-create-form"
                      class="space-y-6">
                    @csrf

                    @include('tripbookings._form', [
                        'trip' => $trip,
                        'booking' => null,
                        'stays' => $stays,
                        'tripItems' => $tripItems,
                        'destinations' => $destinations,
                        'destinationItems' => $destinationItems,
                        'places' => $places,
                        'bookingTypes' => $bookingTypes,
                        'bookingStatuses' => $bookingStatuses,
                        'paymentStatuses' => $paymentStatuses,
                        'currencies' => $currencies,
                        'isCreate' => true,
                        'returnTo' => route('trips.bookings.index', $trip),
                    ])
                </form>
            @endif

            @if($bookings->isEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">No Trip Bookings Yet</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Add booking records for accommodation, tours, transport, tickets, and other trip services.
                            </p>
                        </div>

                        @unless($showCreate)
                            <a href="{{ route('trips.bookings.index', ['trip' => $trip->id, 'show_create' => 1]) }}"
                               class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                Add First Booking
                            </a>
                        @endunless
                    </div>
                </div>
            @else
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Provider</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Type</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Dates</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Reference</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Costs</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Links</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach($bookings as $booking)
                                    <tr>
                                        <td class="px-4 py-3 align-top">
                                            <div class="font-medium text-gray-900">{{ $booking->providername }}</div>
                                            @if($booking->providercontact)
                                                <div class="text-xs text-gray-500 mt-1">{{ $booking->providercontact }}</div>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 align-top">
                                            <div class="text-gray-800">{{ $bookingTypes[$booking->bookingtype] ?? $booking->bookingtype }}</div>
                                            @if($booking->stay)
                                                <div class="text-xs text-gray-500 mt-1">
                                                    Stay: {{ $booking->stay->stayname ?: 'Stay #'.$booking->stay->id }}
                                                </div>
                                            @elseif($booking->tripItem)
                                                <div class="text-xs text-gray-500 mt-1">
                                                    Item: {{ $booking->tripItem->title ?: 'Item #'.$booking->tripItem->id }}
                                                </div>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 align-top">
                                            <div class="text-gray-800">{{ $bookingStatuses[$booking->status] ?? $booking->status }}</div>
                                            @if($booking->paymentstatus)
                                                <div class="text-xs text-gray-500 mt-1">
                                                    {{ $paymentStatuses[$booking->paymentstatus] ?? $booking->paymentstatus }}
                                                </div>
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
                                                <a href="{{ $booking->website }}"
                                                   target="_blank"
                                                   rel="noopener noreferrer"
                                                   class="text-blue-600 hover:text-blue-700 text-sm">
                                                    Open
                                                </a>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 align-top">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('trips.bookings.edit', [$trip, $booking]) }}"
                                                   class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs">
                                                    Edit
                                                </a>

                                                <form method="POST"
                                                      action="{{ route('trips.bookings.destroy', [$trip, $booking]) }}"
                                                      onsubmit="return confirm('Delete this booking?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700 text-xs">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="px-4 py-4 border-t border-gray-200">
                        {{ $bookings->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>


