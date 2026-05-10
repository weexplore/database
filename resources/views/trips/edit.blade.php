<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Trip
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $trip->tripname }}
                </p>
            </div>

            <a href="{{ route('trips.book', $trip) }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                    Print Trip Book
            </a>

            <a href="{{ route('trips.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                Back to Trips
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <form method="POST"
                  action="{{ route('trips.update', $trip) }}"
                  id="trip-edit-form"
                  class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Core Details --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Core Details</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label for="tripname" class="block text-sm font-medium text-gray-700 mb-1">
                                Trip Name
                            </label>
                            <input type="text"
                                   name="tripname"
                                   id="tripname"
                                   value="{{ old('tripname', $trip->tripname) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                   required>
                        </div>

                        <div>
                            <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">
                                Slug
                            </label>
                            <input type="text"
                                   name="slug"
                                   id="slug"
                                   value="{{ old('slug', $trip->slug) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div>
                            <label for="tripstatus" class="block text-sm font-medium text-gray-700 mb-1">
                                Status
                            </label>
                            <select name="tripstatus"
                                    id="tripstatus"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                @foreach($statusOptions as $status)
                                    <option value="{{ $status }}"
                                        @selected(old('tripstatus', $trip->tripstatus) === $status)>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="startdate" class="block text-sm font-medium text-gray-700 mb-1">
                                Start Date
                            </label>
                            <input type="date"
                                   name="startdate"
                                   id="startdate"
                                   value="{{ old('startdate', optional($trip->startdate)->format('Y-m-d')) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div>
                            <label for="enddate" class="block text-sm font-medium text-gray-700 mb-1">
                                End Date
                            </label>
                            <input type="date"
                                   name="enddate"
                                   id="enddate"
                                   value="{{ old('enddate', optional($trip->enddate)->format('Y-m-d')) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div>
                            <label for="travellercount" class="block text-sm font-medium text-gray-700 mb-1">
                                Traveller Count
                            </label>
                            <input type="number"
                                   name="travellercount"
                                   id="travellercount"
                                   value="{{ old('travellercount', $trip->travellercount) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                   min="1"
                                   max="20">
                        </div>

                        <div class="flex items-end">
                            <label class="inline-flex items-center gap-2">
                                <input type="hidden" name="islocked" value="0">
                                <input type="checkbox"
                                       name="islocked"
                                       value="1"
                                       class="rounded border-gray-300 text-blue-600 shadow-sm"
                                       @checked(old('islocked', $trip->islocked))>
                                <span class="text-sm text-gray-700">Locked</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Trip Notes --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Trip Notes</h3>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="summary" class="block text-sm font-medium text-gray-700 mb-1">
                                Summary
                            </label>
                            <textarea name="summary"
                                      id="summary"
                                      rows="3"
                                      class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('summary', $trip->summary) }}</textarea>
                        </div>

                        <div>
                            <label for="planningnotes" class="block text-sm font-medium text-gray-700 mb-1">
                                Planning Notes
                            </label>
                            <textarea name="planningnotes"
                                      id="planningnotes"
                                      rows="6"
                                      class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('planningnotes', $trip->planningnotes) }}</textarea>
                        </div>

                        <div>
                            <label for="actualnotes" class="block text-sm font-medium text-gray-700 mb-1">
                                Actual Notes
                            </label>
                            <textarea name="actualnotes"
                                      id="actualnotes"
                                      rows="6"
                                      class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('actualnotes', $trip->actualnotes) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Budget Defaults --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Budget Defaults</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="defaultdailyfoodbudget" class="block text-sm font-medium text-gray-700 mb-1">
                                Daily Food Budget
                            </label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="defaultdailyfoodbudget"
                                   id="defaultdailyfoodbudget"
                                   value="{{ old('defaultdailyfoodbudget', $trip->defaultdailyfoodbudget) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div>
                            <label for="defaultdailymiscbudget" class="block text-sm font-medium text-gray-700 mb-1">
                                Daily Misc Budget
                            </label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="defaultdailymiscbudget"
                                   id="defaultdailymiscbudget"
                                   value="{{ old('defaultdailymiscbudget', $trip->defaultdailymiscbudget) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>
                    </div>
                </div>

                {{-- Fuel and Distance --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Fuel and Distance</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="defaultfuelpriceperlitre" class="block text-sm font-medium text-gray-700 mb-1">
                                Default Fuel Price per Litre
                            </label>
                            <input type="number"
                                   step="0.0001"
                                   min="0"
                                   name="defaultfuelpriceperlitre"
                                   id="defaultfuelpriceperlitre"
                                   value="{{ old('defaultfuelpriceperlitre', $trip->defaultfuelpriceperlitre) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div>
                            <label for="defaultfuelconsumptionlper100km" class="block text-sm font-medium text-gray-700 mb-1">
                                Fuel Consumption L/100km
                            </label>
                            <input type="number"
                                   step="0.0001"
                                   min="0"
                                   name="defaultfuelconsumptionlper100km"
                                   id="defaultfuelconsumptionlper100km"
                                   value="{{ old('defaultfuelconsumptionlper100km', $trip->defaultfuelconsumptionlper100km) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div>
                            <label for="estimatedtotaldistancekm" class="block text-sm font-medium text-gray-700 mb-1">
                                Estimated Distance (km)
                            </label>
                            <input type="number"
                                   step="0.1"
                                   min="0"
                                   name="estimatedtotaldistancekm"
                                   id="estimatedtotaldistancekm"
                                   value="{{ old('estimatedtotaldistancekm', $trip->estimatedtotaldistancekm) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>

                        <div>
                            <label for="actualtotaldistancekm" class="block text-sm font-medium text-gray-700 mb-1">
                                Actual Distance (km)
                            </label>
                            <input type="number"
                                   step="0.1"
                                   min="0"
                                   name="actualtotaldistancekm"
                                   id="actualtotaldistancekm"
                                   value="{{ old('actualtotaldistancekm', $trip->actualtotaldistancekm) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>
                    </div>
                </div>

                {{-- Travellers --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Travellers</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Assign the travellers linked to this trip.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($travellers as $traveller)
                            <label class="inline-flex items-center gap-2 rounded border border-gray-200 px-3 py-2">
                                <input type="checkbox"
                                       name="travellerids[]"
                                       value="{{ $traveller->id }}"
                                       class="rounded border-gray-300 text-blue-600 shadow-sm"
                                       @checked(in_array($traveller->id, old('travellerids', $selectedTravellers), true))>
                                <span class="text-sm text-gray-700">{{ $traveller->displayname }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    <a href="{{ route('trips.planner.index', $trip) }}"
       class="block p-4 bg-white border border-gray-200 rounded-lg hover:shadow-md transition">
        <h4 class="text-sm font-semibold text-gray-900 mb-1">Trip Planner</h4>
        <p class="text-xs text-gray-600">
            Add and sequence Places, Destinations, and Destination Items before creating legs and stays.
        </p>
    </a>

    <a href="{{ route('trips.legs.index', $trip) }}"
       class="block p-4 bg-white border border-gray-200 rounded-lg hover:shadow-md transition">
        <h4 class="text-sm font-semibold text-gray-900 mb-1">Trip Legs</h4>
        <p class="text-xs text-gray-600">
            Plan route structure, sequence, and driving notes.
        </p>
    </a>

    <a href="{{ route('trips.stays.index', $trip) }}"
       class="block p-4 bg-white border border-gray-200 rounded-lg hover:shadow-md transition">
        <h4 class="text-sm font-semibold text-gray-900 mb-1">Trip Stays</h4>
        <p class="text-xs text-gray-600">
            Record planned and actual stays, nights, and accommodation costs.
        </p>
    </a>

    <a href="{{ route('trips.items.index', $trip) }}"
       class="block p-4 bg-white border border-gray-200 rounded-lg hover:shadow-md transition">
        <h4 class="text-sm font-semibold text-gray-900 mb-1">Trip Items</h4>
        <p class="text-xs text-gray-600">
            Manage activities, tasks, events, bookings, meals, drives, and other trip items.
        </p>
    </a>

    <a href="{{ route('trips.bookings.index', $trip) }}"
       class="block p-4 bg-white border border-gray-200 rounded-lg hover:shadow-md transition">
        <h4 class="text-sm font-semibold text-gray-900 mb-1">Trip Bookings</h4>
        <p class="text-xs text-gray-600">
            Manage provider details, references, dates, payment status, and booking costs.
        </p>
    </a>

    <a href="{{ route('trips.reviews.index', $trip) }}"
       class="block p-4 bg-white border border-gray-200 rounded-lg hover:shadow-md transition">
        <h4 class="text-sm font-semibold text-gray-900 mb-1">Trip Reviews</h4>
        <p class="text-xs text-gray-600">
            Record review scores, comments, return interest, and private trip feedback.
        </p>
    </a>
</div>


<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    <a href="{{ route('trips.fuel-estimates.index', $trip) }}"
       class="block p-4 bg-white border border-gray-200 rounded-lg hover:shadow-md transition">
        <h4 class="text-sm font-semibold text-gray-900 mb-1">Fuel Price Estimates</h4>
        <p class="text-xs text-gray-600">
            Plan expected fuel stops, prices, and quantities for this trip.
        </p>
    </a>

    <a href="{{ route('trips.fuel-purchases.index', $trip) }}"
       class="block p-4 bg-white border border-gray-200 rounded-lg hover:shadow-md transition">
        <h4 class="text-sm font-semibold text-gray-900 mb-1">Trip Fuel Purchases</h4>
        <p class="text-xs text-gray-600">
            Record actual fuel fills, odometer, litres, and fuel costs during the trip.
        </p>
    </a>
</div>

<div class="flex items-center justify-between pt-4 border-t border-gray-200">
    <a href="{{ route('trips.index') }}"
       class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50">
        Back to Trips
    </a>

    <button type="submit"
            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md text-sm font-semibold text-white hover:bg-green-700">
        Save Changes
    </button>
</div>

            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('trip-edit-form');
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
        });
    </script>
</x-app-layout>