<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                <div class="border border-blue-200 bg-blue-50 rounded-md p-4 text-sm text-gray-800">
                    <div class="font-semibold text-blue-800 mb-1">
                        Welcome to Caravan Travel Planner
                    </div>
                    <div class="text-sm text-gray-700">
                        Manage reusable travel data first, then build destinations, itineraries, fuel tracking, bookings, and reviews around that foundation.
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">Phase 1 foundation</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        <a href="{{ route('countries.index') }}" class="block p-4 bg-white border border-gray-200 rounded-lg hover:shadow-md transition">
                            <h4 class="text-sm font-semibold text-gray-900 mb-1">Countries</h4>
                            <p class="text-xs text-gray-600">Maintain the core country validation list.</p>
                        </a>

                        <a href="{{ route('states.index') }}" class="block p-4 bg-white border border-gray-200 rounded-lg hover:shadow-md transition">
                            <h4 class="text-sm font-semibold text-gray-900 mb-1">States</h4>
                            <p class="text-xs text-gray-600">Maintain states and territories linked to countries.</p>
                        </a>

                        <a href="{{ route('regions.index') }}" class="block p-4 bg-white border border-gray-200 rounded-lg hover:shadow-md transition">
                            <h4 class="text-sm font-semibold text-gray-900 mb-1">Regions</h4>
                            <p class="text-xs text-gray-600">Manage travel and tourism regions for grouping and filtering.</p>
                        </a>

                        <a href="{{ route('places.index') }}" class="block p-4 bg-white border border-gray-200 rounded-lg hover:shadow-md transition">
                            <h4 class="text-sm font-semibold text-gray-900 mb-1">Places</h4>
                            <p class="text-xs text-gray-600">Maintain reusable towns, stops, camps, and mapped places.</p>
                        </a>

                        <a href="{{ route('travellers.index') }}" class="block p-4 bg-white border border-gray-200 rounded-lg hover:shadow-md transition">
                            <h4 class="text-sm font-semibold text-gray-900 mb-1">Travellers</h4>
                            <p class="text-xs text-gray-600">Manage Ian, Heather, and any future travellers.</p>
                        </a>

                        <a href="{{ route('trips.index') }}" class="block p-4 bg-white border border-gray-200 rounded-lg hover:shadow-md transition">
                            <h4 class="text-sm font-semibold text-gray-900 mb-1">Trips</h4>
                            <p class="text-xs text-gray-600">Create and manage trips with dates, notes, and defaults.</p>
                        </a>
                        <a href="{{ route('vehicles.index') }}" class="block p-4 bg-white border border-gray-200 rounded-lg hover:shadow-md transition">
                            <h4 class="text-sm font-semibold text-gray-900 mb-1">Vehicles</h4>
                            <p class="text-xs text-gray-600">Create and manage vehicles.</p>
                        </a>
                    </div>
                </div>

                <div>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        <a href="{{ route('destinations.index') }}" class="block p-4 bg-white border border-gray-200 rounded-lg hover:shadow-md transition">
                            <h4 class="text-sm font-semibold text-gray-900 mb-1">Destinations</h4>
                            <p class="text-xs text-gray-600">Manage editorial destination records linked to places or localities.</p>
                        </a>

                        <a href="{{ route('destination-items.index') }}" class="block p-4 bg-white border border-gray-200 rounded-lg hover:shadow-md transition">
                            <h4 class="text-sm font-semibold text-gray-700 mb-1">Destination Items</h4>
                            <p class="text-xs text-gray-500">Attractions, walks, dump points, water points, drives, and more.</p>
                        </a>
                    </div>
                </div>
                
                <div>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        <a href="{{ route('fuel-stops.index') }}"
                        class="block p-4 bg-white border border-gray-200 rounded-lg hover:shadow-md transition">
                            <h4 class="text-sm font-semibold text-gray-900 mb-1">Fuel Stops</h4>
                            <p class="text-xs text-gray-600">
                                Reusable fuel stop records with caravan access notes.
                            </p>
                        </a>

                        <a href="{{ route('fuel-price-observations.index') }}"
                        class="block p-4 bg-white border border-gray-200 rounded-lg hover:shadow-md transition">
                            <h4 class="text-sm font-semibold text-gray-700 mb-1">Fuel Price Observations</h4>
                            <p class="text-xs text-gray-500">
                                Keep price history by stop, date, and fuel type.
                            </p>
                        </a>
                    </div>
                </div>

<div>
    <h3 class="text-sm font-semibold text-gray-800 mb-3">Reports</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <a href="{{ route('trips.index') }}"
           class="block p-4 bg-white border border-gray-200 rounded-lg hover:shadow-md transition">
            <h4 class="text-sm font-semibold text-gray-900 mb-1">Trip Book</h4>
            <p class="text-xs text-gray-600">
                Go to Trips, open a trip, and print the Trip Book report.
            </p>
        </a>

        @if (Route::has('reports.reference-book'))
            <a href="{{ route('reports.reference-book') }}"
               class="block p-4 bg-white border border-gray-200 rounded-lg hover:shadow-md transition">
                <h4 class="text-sm font-semibold text-gray-900 mb-1">Reference Book</h4>
                <p class="text-xs text-gray-600">
                    Browse and print a filtered reference book of places, destinations, items, and fuel stops.
                </p>
            </a>
        @else
            <div class="p-4 border border-dashed border-gray-300 rounded-lg bg-gray-50">
                <h4 class="text-sm font-semibold text-gray-700 mb-1">Reference Book</h4>
                <p class="text-xs text-gray-500">
                    Reference Book report is not wired yet.
                </p>
            </div>
        @endif
    </div>
</div>

                <div>
                
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">

                        <div class="p-4 border border-dashed border-gray-300 rounded-lg bg-gray-50">
                            <h4 class="text-sm font-semibold text-gray-700 mb-1">Bookings</h4>
                            <p class="text-xs text-gray-500">Providers, confirmations, notes, dates, and costs.</p>
                        </div>

                        <div class="p-4 border border-dashed border-gray-300 rounded-lg bg-gray-50">
                            <h4 class="text-sm font-semibold text-gray-700 mb-1">Reviews</h4>
                            <p class="text-xs text-gray-500">Record feedback, reuse decisions, and revisit interest.</p>
                        </div>

                        <div class="p-4 border border-dashed border-gray-300 rounded-lg bg-gray-50">
                            <h4 class="text-sm font-semibold text-gray-700 mb-1">Attachments</h4>
                            <p class="text-xs text-gray-500">Receipts, confirmations, maps, and reference files.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>