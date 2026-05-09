<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Trip Reviews
                </h2>
                <p class="text-sm text-gray-500 mt-1">{{ $trip->tripname }}</p>
            </div>

            <a href="{{ route('trips.edit', $trip) }}"
               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Back to Trip Details
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
                        @foreach ($errors->all() as $erroror)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                {{-- Filters --}}
                <div class="p-4 border-b border-gray-200">
                    <form method="GET" action="{{ route('trips.reviews.index', $trip) }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div>
                                <label for="search" class="block text-xs font-medium text-gray-700 mb-1">Search</label>
                                <input type="text" name="search" id="search"
                                       value="{{ request('search') }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                       placeholder="Title or comments">
                            </div>

                            <div>
                                <label for="traveller_id" class="block text-xs font-medium text-gray-700 mb-1">Traveller</label>
                                <select name="traveller_id" id="traveller_id"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">All travellers</option>
                                    @foreach($travellers as $traveller)
                                        <option value="{{ $traveller->id }}" @selected((string) request('traveller_id') === (string) $traveller->id)>
                                            {{ $traveller->firstname }} {{ $traveller->lastname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="rating_min" class="block text-xs font-medium text-gray-700 mb-1">
                                    Minimum overall rating
                                </label>
                                <select name="rating_min" id="rating_min"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Any</option>
                                    @for($i = 1; $i <= 10; $i++)
                                        <option value="{{ $i }}" @selected((string) request('rating_min') === (string) $i)>
                                            {{ $i }}+
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="flex items-end">
                                <label class="inline-flex items-center text-xs font-medium text-gray-700">
                                    <input type="checkbox" name="only_public" value="1"
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm mr-2"
                                           @checked(request()->boolean('only_public'))>
                                    Only public reviews
                                </label>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-gray-900 border border-transparent rounded-md text-sm font-semibold text-white hover:bg-gray-800">
                                Filter
                            </button>
                            <a href="{{ route('trips.reviews.index', $trip) }}"
                               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                {{-- Inline create --}}
                <div class="p-4 border-b border-gray-200 bg-gray-50">
                    <form method="POST" action="{{ route('trips.reviews.store', $trip) }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div>
                                <label for="travellerid" class="block text-xs font-medium text-gray-700 mb-1">
                                    Traveller
                                </label>
                                <select name="travellerid" id="travellerid"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Optional</option>
                                    @foreach($travellers as $traveller)
                                        <option value="{{ $traveller->id }}" @selected((string) old('travellerid') === (string) $traveller->id)>
                                            {{ $traveller->firstname }} {{ $traveller->lastname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="tripstayid" class="block text-xs font-medium text-gray-700 mb-1">
                                    Stay
                                </label>
                                <select name="tripstayid" id="tripstayid"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Optional</option>
                                    @foreach($stays as $stay)
                                        <option value="{{ $stay->id }}" @selected((string) old('tripstayid') === (string) $stay->id)>
                                            {{ $stay->stayname ?: 'Stay #'.$stay->id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="tripitemid" class="block text-xs font-medium text-gray-700 mb-1">
                                    Trip item
                                </label>
                                <select name="tripitemid" id="tripitemid"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Optional</option>
                                    @foreach($tripItems as $item)
                                        <option value="{{ $item->id }}" @selected((string) old('tripitemid') === (string) $item->id)>
                                            {{ $item->title ?: 'Item #'.$item->id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="reviewdate" class="block text-xs font-medium text-gray-700 mb-1">
                                    Review date
                                </label>
                                <input type="date" name="reviewdate" id="reviewdate"
                                       value="{{ old('reviewdate') }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label for="ratingoverall" class="block text-xs font-medium text-gray-700 mb-1">
                                    Overall rating (1–10)
                                </label>
                                <input type="number" name="ratingoverall" id="ratingoverall"
                                       min="1" max="10" step="1"
                                       value="{{ old('ratingoverall') }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div class="md:col-span-2 xl:col-span-3">
                                <label for="title" class="block text-xs font-medium text-gray-700 mb-1">Title</label>
                                <input type="text" name="title" id="title" maxlength="150"
                                       value="{{ old('title') }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md text-sm font-semibold text-white hover:bg-green-700">
                                Add Review
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Reviews table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Date</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Traveller</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Context</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Title & comments</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Overall</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Return</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-600">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($reviews as $review)
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
                                    <div class="font-semibold text-gray-900">
                                        {{ $review->title ?: 'Untitled review' }}
                                    </div>
                                    @if($review->comments)
                                        <div class="mt-1 text-xs text-gray-600 line-clamp-3">
                                            {{ $review->comments }}
                                        </div>
                                    @endif
                                    @if($review->isprivate)
                                        <div class="mt-1 text-[11px] inline-flex items-center px-2 py-0.5 rounded-full bg-yellow-50 text-yellow-700 border border-yellow-200">
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
                                <td class="px-4 py-3 align-top text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('trips.reviews.edit', [$trip, $review]) }}"
                                           class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                                            Edit
                                        </a>
                                        <form method="POST"
                                              action="{{ route('trips.reviews.destroy', [$trip, $review]) }}"
                                              onsubmit="return confirm('Delete this review?');">
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
                                <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                                    No reviews recorded for this trip.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-4 border-t border-gray-200">
                    {{ $reviews->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>