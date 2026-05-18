@php
    $showCreate = $showCreate ?? (request()->boolean('show_create') || $errors->any());
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Trip Reviews
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

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('trips.reviews.index', $trip) }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input type="text"
                                   name="search"
                                   id="search"
                                   value="{{ request('search') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                   placeholder="Title or comments">
                        </div>

                        <div>
                            <label for="traveller_id" class="block text-sm font-medium text-gray-700 mb-1">Traveller</label>
                            <select name="traveller_id" id="traveller_id" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All travellers</option>
                                @foreach($travellers as $traveller)
                                    <option value="{{ $traveller->id }}" @selected((string) request('traveller_id') === (string) $traveller->id)>
                                        {{ $traveller->firstname }} {{ $traveller->lastname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="rating_min" class="block text-sm font-medium text-gray-700 mb-1">Minimum overall rating</label>
                            <select name="rating_min" id="rating_min" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">Any</option>
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" @selected((string) request('rating_min') === (string) $i)>
                                        {{ $i }}+
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="flex items-end">
                            <label class="inline-flex items-center text-sm text-gray-700">
                                <input type="checkbox"
                                       name="only_public"
                                       value="1"
                                       class="rounded border-gray-300 text-blue-600 shadow-sm mr-2"
                                       @checked(request()->boolean('only_public'))>
                                Only public reviews
                            </label>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                            Filter
                        </button>

                        <a href="{{ route('trips.reviews.index', $trip) }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            @if($showCreate)
                <form method="POST"
                      action="{{ route('trips.reviews.store', $trip) }}"
                      id="trip-review-create-form"
                      class="space-y-6">
                    @csrf

                    @include('tripreviews._form', [
                        'trip' => $trip,
                        'review' => null,
                        'travellers' => $travellers,
                        'stays' => $stays,
                        'tripItems' => $tripItems,
                        'destinations' => $destinations,
                        'destinationItems' => $destinationItems,
                        'places' => $places,
                        'isCreate' => true,
                        'returnTo' => route('trips.reviews.index', $trip),
                    ])
                </form>
            @endif

            @if($reviews->isEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">No Trip Reviews Yet</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Add traveller feedback, ratings, and return-interest notes for this trip.
                            </p>
                        </div>

                        @unless($showCreate)
                            <a href="{{ route('trips.reviews.index', ['trip' => $trip->id, 'show_create' => 1]) }}"
                               class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                Add First Review
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
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Date</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Traveller</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Context</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Title & comments</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Overall</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Return</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach($reviews as $review)
                                    <tr>
                                        <td class="px-4 py-3 align-top text-gray-700">
                                            {{ $review->reviewdate ? $review->reviewdate->format('d/m/Y') : '—' }}
                                        </td>

                                        <td class="px-4 py-3 align-top">
                                            @if($review->traveller)
                                                <div class="text-gray-900">
                                                    {{ $review->traveller->firstname }} {{ $review->traveller->lastname }}
                                                </div>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 align-top text-xs text-gray-600 space-y-1">
                                            @if($review->stay)
                                                <div>Stay: {{ $review->stay->stayname ?: 'Stay #'.$review->stay->id }}</div>
                                            @endif
                                            @if($review->tripItem)
                                                <div>Item: {{ $review->tripItem->title ?: 'Item #'.$review->tripItem->id }}</div>
                                            @endif
                                            @if($review->place)
                                                <div>Place: {{ $review->place->placename }}</div>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 align-top">
                                            <div class="font-medium text-gray-900">
                                                {{ $review->title ?: 'Untitled review' }}
                                            </div>

                                            @if($review->comments)
                                                <div class="mt-1 text-xs text-gray-600 line-clamp-3">
                                                    {{ $review->comments }}
                                                </div>
                                            @endif

                                            @if($review->isprivate)
                                                <div class="mt-1 inline-flex items-center px-2 py-0.5 rounded-full bg-yellow-50 text-yellow-700 border border-yellow-200 text-[11px]">
                                                    Private
                                                </div>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 align-top text-gray-800">
                                            @if(!is_null($review->ratingoverall))
                                                {{ $review->ratingoverall }}/10
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 align-top text-gray-800 text-xs">
                                            @if(!is_null($review->returninterestlevel))
                                                <div>Interest: {{ $review->returninterestlevel }}/5</div>
                                            @endif
                                            @if(!is_null($review->wouldreturn))
                                                <div class="mt-1">
                                                    {{ $review->wouldreturn ? 'Would return' : 'Would not return' }}
                                                </div>
                                            @endif
                                            @if(is_null($review->returninterestlevel) && is_null($review->wouldreturn))
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 align-top">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('trips.reviews.edit', [$trip, $review]) }}"
                                                   class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs">
                                                    Edit
                                                </a>

                                                <form method="POST"
                                                      action="{{ route('trips.reviews.destroy', [$trip, $review]) }}"
                                                      onsubmit="return confirm('Delete this review?');">
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
                        {{ $reviews->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('trip-review-create-form');
            if (!form) return;

            let isDirty = false;
            let isSubmitting = false;

            form.querySelectorAll('input, select, textarea').forEach((element) => {
                element.addEventListener('change', () => isDirty = true);
                element.addEventListener('input', () => isDirty = true);
            });

            form.addEventListener('submit', function () {
                isSubmitting = true;
                isDirty = false;
            });

            window.addEventListener('beforeunload', function (event) {
                if (isDirty && !isSubmitting) {
                    event.preventDefault();
                    event.returnValue = '';
                }
            });

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