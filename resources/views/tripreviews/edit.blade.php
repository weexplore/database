@php
    $returnTo = $returnTo ?? route('trips.reviews.index', $trip);
    $reviewAttachments = $reviewAttachments ?? collect();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Trip Reviews
                </h2>
                <p class="mt-1 text-sm text-gray-500">{{ $trip->tripname }}</p>
                <p class="mt-1 text-sm text-gray-500">
                    Status: {{ ucfirst($trip->tripstatus) }} ·
                    Start: {{ optional($trip->startdate)->format('d M Y') ?? '—' }} ·
                    End: {{ optional($trip->enddate)->format('d M Y') ?? '—' }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('trips.reviews.index', array_merge(['trip' => $trip->id], request()->query(), ['show_create' => 1])) }}"
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                    Add Trip Review
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

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <div class="xl:col-span-2">
                    <form method="POST"
                          action="{{ route('trips.reviews.update', [$trip, $review]) }}"
                          id="trip-review-edit-form"
                          class="space-y-6">
                        @csrf
                        @method('PUT')

                        @include('tripreviews._form', [
                            'trip' => $trip,
                            'review' => $review,
                            'travellers' => $travellers,
                            'stays' => $stays,
                            'tripItems' => $tripItems,
                            'destinations' => $destinations,
                            'destinationItems' => $destinationItems,
                            'places' => $places,
                            'isCreate' => false,
                            'returnTo' => $returnTo,
                        ])
                    </form>
                </div>

                <div class="xl:col-span-1 space-y-6">
                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="px-4 py-4 border-b border-gray-200 flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Attachments</h3>
                                <p class="text-sm text-gray-500">Files linked to this review.</p>
                            </div>

                            <a href="{{ route('attachments.index', [
                                    'linkedtype' => 'review',
                                    'linkedid' => $review->id,
                                    'trip_id' => $review->tripid,
                                    'show_create' => 1,
                                    'return_to' => request()->fullUrl(),
                                ]) }}"
                               class="inline-flex items-center px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                Add Attachment
                            </a>
                        </div>

                        <div class="p-4 space-y-3">
                            @if($reviewAttachments->isEmpty())
                                <p class="text-sm text-gray-500">No attachments added for this review yet.</p>
                            @else
                                @foreach($reviewAttachments as $attachment)
                                    <div class="border border-gray-200 rounded-md px-3 py-3">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $attachment->originalfilename }}
                                        </div>

                                        @if($attachment->description)
                                            <div class="mt-1 text-xs text-gray-500">{{ $attachment->description }}</div>
                                        @endif

                                        <div class="mt-1 text-xs text-gray-500">
                                            {{ $attachmentTypeOptions[$attachment->attachmenttype] ?? $attachment->attachmenttype }}
                                            @if($attachment->uploadedat)
                                                · {{ optional($attachment->uploadedat)->format('d M Y H:i') }}
                                            @endif
                                            @if($attachment->isprimary)
                                                · Primary
                                            @endif
                                        </div>

                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <a href="{{ route('attachments.view', $attachment) }}"
                                               target="_blank"
                                               rel="noopener noreferrer"
                                               class="inline-flex items-center px-2.5 py-1.5 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-xs">
                                                View
                                            </a>

                                            <a href="{{ route('attachments.edit', ['attachment' => $attachment, 'return_to' => request()->fullUrl()]) }}"
                                               class="inline-flex items-center px-2.5 py-1.5 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-xs">
                                                Edit
                                            </a>

                                            <a href="{{ route('attachments.download', $attachment) }}"
                                               class="inline-flex items-center px-2.5 py-1.5 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-xs">
                                                Download
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Record summary</h3>

                        <dl class="mt-3 space-y-2 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Review ID</dt>
                                <dd class="text-gray-900">{{ $review->id }}</dd>
                            </div>

                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Trip</dt>
                                <dd class="text-gray-900">{{ $trip->tripname }}</dd>
                            </div>

                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Date</dt>
                                <dd class="text-gray-900">{{ optional($review->reviewdate)->format('d M Y') ?? '—' }}</dd>
                            </div>

                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Overall</dt>
                                <dd class="text-gray-900">{{ $review->ratingoverall ? $review->ratingoverall . '/10' : '—' }}</dd>
                            </div>

                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Private</dt>
                                <dd class="text-gray-900">{{ $review->isprivate ? 'Yes' : 'No' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('trip-review-edit-form');
            if (!form) return;

            const textareas = form.querySelectorAll('.js-auto-resize-textarea');

            const autoResize = (textarea) => {
                textarea.style.height = 'auto';
                textarea.style.height = textarea.scrollHeight + 'px';
            };

            textareas.forEach((textarea) => {
                autoResize(textarea);
                textarea.addEventListener('input', () => autoResize(textarea));
            });
        });
    </script>
</x-app-layout>