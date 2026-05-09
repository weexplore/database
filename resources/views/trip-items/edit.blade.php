<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Trip Item - {{ $trip->tripname }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @if(session('success'))
                <div class="rounded-md bg-green-50 p-4 text-green-800 border border-green-200">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-md bg-red-50 p-4 text-red-800 border border-red-200">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="rounded-md bg-red-50 p-4 text-red-800 border border-red-200">
                    <div class="font-medium mb-2">Please fix the following:</div>
                    <ul class="list-disc pl-5 space-y-1 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('trips.items.update', ['trip' => $trip->id, 'tripItem' => $tripItem->id]) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    @php($selectedTripLegId = $selectedTripLegId ?? null)
                    @php($selectedTripStayId = $selectedTripStayId ?? null)
                    @php($selectedDestinationId = $selectedDestinationId ?? null)
                    @php($selectedDestinationItemId = $selectedDestinationItemId ?? null)
                    @php($selectedPlaceId = $selectedPlaceId ?? null)
                    @php($selectedBookingId = $selectedBookingId ?? null)
                    @php($selectedItemType = $selectedItemType ?? null)
                    @php($selectedItemDate = $selectedItemDate ?? null)

                    @include('trip-items._form')

                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                        <a href="{{ route('trips.items.index', $trip) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                            Back to Trip Items
                        </a>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('trips.edit', $trip) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                                Back to Trip
                            </a>

                            <button type="submit" class="inline-flex items-center px-5 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>