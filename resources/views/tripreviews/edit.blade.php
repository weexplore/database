<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Review - {{ $trip->tripname }}
                </h2>
            </div>

            <a href="{{ route('trips.reviews.index', $trip) }}"
               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Back to Trip Reviews
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
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
                            >{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('trips.reviews.update', [$trip, $review]) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="reviewdate" class="block text-sm font-medium text-gray-700">Review date</label>
                            <input type="date" name="reviewdate" id="reviewdate"
                                   value="{{ old('reviewdate', optional($review->reviewdate)->format('Y-m-d')) }}"
                                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div>
                            <label for="travellerid" class="block text-sm font-medium text-gray-700">Traveller</label>
                            <select id="travellerid" name="travellerid"
                                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">None</option>
                                @foreach($travellers as $traveller)
                                    <option value="{{ $traveller->id }}" @selected((string) old('travellerid', $review->travellerid) === (string) $traveller->id)>
                                        {{ $traveller->firstname }} {{ $traveller->lastname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="tripstayid" class="block text-sm font-medium text-gray-700">Stay</label>
                            <select id="tripstayid" name="tripstayid"
                                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">None</option>
                                @foreach($stays as $stay)
                                    <option value="{{ $stay->id }}" @selected((string) old('tripstayid', $review->tripstayid) === (string) $stay->id)>
                                        {{ $stay->stayname ?: 'Stay #'.$stay->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="tripitemid" class="block text-sm font-medium text-gray-700">Trip item</label>
                            <select id="tripitemid" name="tripitemid"
                                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">None</option>
                                @foreach($tripItems as $item)
                                    <option value="{{ $item->id }}" @selected((string) old('tripitemid', $review->tripitemid) === (string) $item->id)>
                                        {{ $item->title ?: 'Item #'.$item->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="destinationid" class="block text-sm font-medium text-gray-700">Destination</label>
                            <select id="destinationid" name="destinationid"
                                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">None</option>
                                @foreach($destinations as $destination)
                                    <option value="{{ $destination->id }}" @selected((string) old('destinationid', $review->destinationid) === (string) $destination->id)>
                                        {{ $destination->destinationname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="destinationitemid" class="block text-sm font-medium text-gray-700">Destination item</label>
                            <select id="destinationitemid" name="destinationitemid"
                                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">None</option>
                                @foreach($destinationItems as $destinationItem)
                                    <option value="{{ $destinationItem->id }}" @selected((string) old('destinationitemid', $review->destinationitemid) === (string) $destinationItem->id)>
                                        {{ $destinationItem->itemname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="placeid" class="block text-sm font-medium text-gray-700">Place</label>
                            <select id="placeid" name="placeid"
                                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">None</option>
                                @foreach($places as $place)
                                    <option value="{{ $place->id }}" @selected((string) old('placeid', $review->placeid) === (string) $place->id)>
                                        {{ $place->placename }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        <div>
                            <label for="ratingoverall" class="block text-sm font-medium text-gray-700">
                                Overall rating (1–10)
                            </label>
                            <input type="number" name="ratingoverall" id="ratingoverall"
                                   min="1" max="10" step="1"
                                   value="{{ old('ratingoverall', $review->ratingoverall) }}"
                                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <label for="ratingvalue" class="block text-sm font-medium text-gray-700">
                                Value (1–10)
                            </label>
                            <input type="number" name="ratingvalue" id="ratingvalue"
                                   min="1" max="10" step="1"
                                   value="{{ old('ratingvalue', $review->ratingvalue) }}"
                                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <label for="ratingfacility" class="block text-sm font-medium text-gray-700">
                                Facilities (1–10)
                            </label>
                            <input type="number" name="ratingfacility" id="ratingfacility"
                                   min="1" max="10" step="1"
                                   value="{{ old('ratingfacility', $review->ratingfacility) }}"
                                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <label for="ratingaccess" class="block text-sm font-medium text-gray-700">
                                Access (1–10)
                            </label>
                            <input type="number" name="ratingaccess" id="ratingaccess"
                                   min="1" max="10" step="1"
                                   value="{{ old('ratingaccess', $review->ratingaccess) }}"
                                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <label for="ratingambience" class="block text-sm font-medium text-gray-700">
                                Ambience (1–10)
                            </label>
                            <input type="number" name="ratingambience" id="ratingambience"
                                   min="1" max="10" step="1"
                                   value="{{ old('ratingambience', $review->ratingambience) }}"
                                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="returninterestlevel" class="block text-sm font-medium text-gray-700">
                                Return interest (1–5)
                            </label>
                            <input type="number" name="returninterestlevel" id="returninterestlevel"
                                   min="1" max="5" step="1"
                                   value="{{ old('returninterestlevel', $review->returninterestlevel) }}"
                                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div class="flex items-end">
                            <div class="space-y-2">
                                <label class="inline-flex items-center text-sm text-gray-700">
                                    <input type="checkbox" name="wouldreturn" value="1"
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm mr-2"
                                           @checked(old('wouldreturn', $review->wouldreturn))>
                                    Would return
                                </label>

                                <label class="inline-flex items-center text-sm text-gray-700">
                                    <input type="checkbox" name="isprivate" value="1"
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm mr-2"
                                           @checked(old('isprivate', $review->isprivate))>
                                    Private review
                                </label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" name="title" id="title" maxlength="150"
                               value="{{ old('title', $review->title) }}"
                               class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>

                    <div>
                        <label for="comments" class="block text-sm font-medium text-gray-700">Comments</label>
                        <textarea name="comments" id="comments" rows="5"
                                  class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('comments', $review->comments) }}</textarea>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                        <a href="{{ route('trips.reviews.index', $trip) }}"
                           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Back to Trip Reviews
                        </a>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('trips.edit', $trip) }}"
                               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                Back to Trip
                            </a>

                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md text-sm font-semibold text-white hover:bg-green-700">
                                Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>