<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Trip Stays
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $trip->tripname }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('trips.edit', $trip) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                    Back to Trip
                </a>

                <a href="{{ route('trips.stays.index', array_merge(['trip' => $trip->id], request()->query(), ['show_create' => 1])) }}"
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                    Add Trip Stay
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('trips.stays.index', $trip) }}">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label for="tripleg_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Trip Leg
                            </label>
                            <select name="tripleg_id" id="tripleg_id" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All legs</option>
                                @foreach($tripLegs as $leg)
                                    <option value="{{ $leg->id }}" @selected((string) request('tripleg_id') === (string) $leg->id)>
                                        Leg {{ $leg->legnumber }}{{ $leg->title ? ' - ' . $leg->title : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="place_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Place
                            </label>
                            <select name="place_id" id="place_id" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All places</option>
                                @foreach($places as $place)
                                    <option value="{{ $place->id }}" @selected((string) request('place_id') === (string) $place->id)>
                                        {{ $place->placename }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="travelledfromplace_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Travelled From
                            </label>
                            <select name="travelledfromplace_id" id="travelledfromplace_id" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All origin places</option>
                                @foreach($places as $place)
                                    <option value="{{ $place->id }}" @selected((string) request('travelledfromplace_id') === (string) $place->id)>
                                        {{ $place->placename }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="staytype" class="block text-sm font-medium text-gray-700 mb-1">
                                Stay Type
                            </label>
                            <select name="staytype" id="staytype" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All stay types</option>
                                @foreach($stayTypes as $stayType)
                                    <option value="{{ $stayType }}" @selected((string) request('staytype') === (string) $stayType)>
                                        {{ ucwords(str_replace('_', ' ', $stayType)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                Filter
                            </button>

                            <a href="{{ route('trips.stays.index', $trip) }}"
                               class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            @if($showCreate)
                <form method="POST"
                      action="{{ route('trips.stays.store', $trip) }}"
                      id="trip-stay-create-form"
                      class="space-y-6">
                    @csrf

                    @php
                        $tripStay = null;
                    @endphp

                    @include('trip-stays._form', [
                        'trip' => $trip,
                        'tripStay' => $tripStay,
                        'places' => $places,
                        'tripLegs' => $tripLegs,
                        'stayTypes' => $stayTypes,
                        'selectedTripLegId' => $selectedTripLegId ?? null,
                        'selectedPlaceId' => $selectedPlaceId ?? null,
                        'selectedTravelledFromPlaceId' => $selectedTravelledFromPlaceId ?? null,
                        'isCreate' => true,
                    ])
                </form>
            @endif

            @if($stays->isEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">No Trip Stays Yet</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Add planned or actual accommodation records for this trip.
                            </p>
                        </div>

                        @unless($showCreate)
                            <a href="{{ route('trips.stays.index', ['trip' => $trip->id, 'show_create' => 1]) }}"
                               class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                Add First Stay
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
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Stay</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Type</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Place</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Trip Leg</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Dates</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Nights</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Estimated</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Actual</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach($stays as $stay)
                                    <tr>
                                        <td class="px-4 py-3">
                                            {{ $stay->stayname ?: '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @php
                                                $typeValue = $stay->staytype;
                                            @endphp

                                            {{ $typeValue !== null && isset($stayTypes[$typeValue])
                                                ? $stayTypes[$typeValue]
                                                : '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ $stay->place?->placename ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($stay->tripLeg)
                                                <div class="text-sm text-gray-900">
                                                    {{ $stay->tripLeg->fromPlace?->placename ?? '—' }}
                                                    –
                                                    {{ $stay->tripLeg->toPlace?->placename ?? '—' }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    @if($stay->tripLeg->startdate || $stay->tripLeg->enddate)
                                                        {{ $stay->tripLeg->startdate ? $stay->tripLeg->startdate->format('d/m/Y') : '—' }}
                                                        –
                                                        {{ $stay->tripLeg->enddate ? $stay->tripLeg->enddate->format('d/m/Y') : '—' }}
                                                    @else
                                                        —
                                                    @endif
                                                </div>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div>{{ $stay->checkindate ? \Illuminate\Support\Carbon::parse($stay->checkindate)->format('d/m/Y') : '—' }}</div>
                                            <div class="text-xs text-gray-500">{{ $stay->checkoutdate ? \Illuminate\Support\Carbon::parse($stay->checkoutdate)->format('d/m/Y') : '—' }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ $stay->nights ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ $stay->estimatedtotalcost !== null ? '$' . number_format((float) $stay->estimatedtotalcost, 2) : '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ $stay->actualtotalcost !== null ? '$' . number_format((float) $stay->actualtotalcost, 2) : '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('trips.stays.edit', ['trip' => $trip, 'tripStay' => $stay]) }}"
                                                   class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs">
                                                    Edit
                                                </a>

                                                <form method="POST"
                                                      action="{{ route('trips.stays.destroy', ['trip' => $trip, 'tripStay' => $stay]) }}"
                                                      onsubmit="return confirm('Delete this trip stay?');">
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
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('trip-stay-create-form');
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