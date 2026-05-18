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
                <p class="mt-1 text-sm text-gray-500">
                    Status: {{ ucfirst($trip->tripstatus) }} ·
                    Start: {{ optional($trip->startdate)->format('d M Y') ?? '—' }} ·
                    End: {{ optional($trip->enddate)->format('d M Y') ?? '—' }}
                </p>
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
                        <form method="POST"
                            action="{{ route('trips.bookings.update', [$trip, $booking]) }}"
                            id="trip-booking-edit-form"
                            class="space-y-6">
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="return_to" value="{{ $returnTo }}">

                            @include('tripbookings._form', [
                                'trip' => $trip,
                                'booking' => $booking,
                                'stays' => $stays,
                                'tripItems' => $tripItems,
                                'destinations' => $destinations,
                                'destinationItems' => $destinationItems,
                                'places' => $places,
                                'bookingTypes' => $bookingTypes,
                                'bookingStatuses' => $bookingStatuses,
                                'paymentStatuses' => $paymentStatuses,
                                'currencies' => $currencies,
                                'isCreate' => false,
                                'returnTo' => $returnTo,
                            ])
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