<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Trip Legs
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

                <a href="{{ route('trips.legs.index', array_merge(['trip' => $trip->id], request()->query(), ['show_create' => 1])) }}"
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                    Add Trip Leg
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('trips.legs.index', $trip) }}">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="destination_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Destination
                            </label>
                            <select name="destination_id" id="destination_id" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All destinations</option>
                                @foreach($destinations as $destination)
                                    <option value="{{ $destination->id }}" @selected((string) request('destination_id') === (string) $destination->id)>
                                        {{ $destination->destinationname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="fromplace_id" class="block text-sm font-medium text-gray-700 mb-1">
                                From Place
                            </label>
                            <select name="fromplace_id" id="fromplace_id" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All origin places</option>
                                @foreach($places as $place)
                                    <option value="{{ $place->id }}" @selected((string) request('fromplace_id') === (string) $place->id)>
                                        {{ $place->placename }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="toplace_id" class="block text-sm font-medium text-gray-700 mb-1">
                                To Place
                            </label>
                            <select name="toplace_id" id="toplace_id" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All destination places</option>
                                @foreach($places as $place)
                                    <option value="{{ $place->id }}" @selected((string) request('toplace_id') === (string) $place->id)>
                                        {{ $place->placename }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                Filter
                            </button>

                            <a href="{{ route('trips.legs.index', $trip) }}"
                               class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            @if($showCreate)
                <form method="POST"
                      action="{{ route('trips.legs.store', $trip) }}"
                      id="trip-leg-create-form"
                      class="space-y-6">
                    @csrf

                    @php
                        $tripLeg = null;
                    @endphp

                    @include('trip-legs._form', [
                        'trip' => $trip,
                        'tripLeg' => $tripLeg,
                        'places' => $places,
                        'destinations' => $destinations,
                        'selectedDestinationId' => $selectedDestinationId ?? null,
                        'selectedFromPlaceId' => $selectedFromPlaceId ?? null,
                        'selectedToPlaceId' => $selectedToPlaceId ?? null,
                        'isCreate' => true,
                    ])
                </form>
            @endif

            @if($legs->isEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">No Trip Legs Yet</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Start building the itinerary by adding the first leg for this trip.
                            </p>
                        </div>

                        @unless($showCreate)
                            <a href="{{ route('trips.legs.index', ['trip' => $trip->id, 'show_create' => 1]) }}"
                               class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                Add First Leg
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
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Leg</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Dates</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">From</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">To</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Destination</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Distance</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Title</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach($legs as $leg)
                                    <tr>
                                        <td class="px-4 py-3">
                                            {{ $leg->legnumber ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div>{{ $leg->startdate ? \Illuminate\Support\Carbon::parse($leg->startdate)->format('d/m/Y') : '—' }}</div>
                                            <div class="text-xs text-gray-500">{{ $leg->enddate ? \Illuminate\Support\Carbon::parse($leg->enddate)->format('d/m/Y') : '—' }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ $leg->fromPlace?->placename ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ $leg->toPlace?->placename ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ $leg->destination?->destinationname ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ $leg->distancekm !== null ? number_format((float) $leg->distancekm, 1) . ' km' : '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ $leg->title ?: '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('trips.legs.edit', ['trip' => $trip, 'tripLeg' => $leg]) }}"
                                                   class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs">
                                                    Edit
                                                </a>

                                                <form method="POST"
                                                      action="{{ route('trips.legs.destroy', ['trip' => $trip, 'tripLeg' => $leg]) }}"
                                                      onsubmit="return confirm('Delete this trip leg?');">
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
            const form = document.getElementById('trip-leg-create-form');
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
        const vehicleRows = document.getElementById('vehicle-rows');
        const addVehicleRowButton = document.getElementById('add-vehicle-row');

        function reindexVehicleRows() {
            if (!vehicleRows) return;

            vehicleRows.querySelectorAll('.vehicle-row').forEach((row, index) => {
                row.querySelectorAll('select, input').forEach((field) => {
                    if (field.name.includes('[vehicleid]')) {
                        field.name = `vehicles[${index}][vehicleid]`;
                    } else if (field.name.includes('[vehiclerole]')) {
                        field.name = `vehicles[${index}][vehiclerole]`;
                    } else if (field.name.includes('[sortorder]')) {
                        field.name = `vehicles[${index}][sortorder]`;
                    }
                });
            });
        }

        if (addVehicleRowButton && vehicleRows) {
            addVehicleRowButton.addEventListener('click', function () {
                const index = vehicleRows.querySelectorAll('.vehicle-row').length;
                const template = document.createElement('div');
                template.className = 'grid grid-cols-1 md:grid-cols-12 gap-3 vehicle-row';
                template.innerHTML = `
                    <div class="md:col-span-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle</label>
                        <select name="vehicles[${index}][vehicleid]" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">Select vehicle</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}">{{ $vehicle->vehiclename }}{{ $vehicle->registrationnumber ? ' (' . $vehicle->registrationnumber . ')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <input type="text"
                            name="vehicles[${index}][vehiclerole]"
                            class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                            placeholder="Tow vehicle, caravan, support vehicle">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sort</label>
                        <input type="number"
                            name="vehicles[${index}][sortorder]"
                            value="${index + 1}"
                            class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                            min="0">
                    </div>

                    <div class="md:col-span-1 flex items-end">
                        <button type="button"
                                class="remove-vehicle-row inline-flex items-center px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-xs w-full justify-center">
                            Remove
                        </button>
                    </div>
                `;

                vehicleRows.appendChild(template);
                isDirty = true;
            });

            vehicleRows.addEventListener('click', function (event) {
                const button = event.target.closest('.remove-vehicle-row');
                if (!button) return;

                const rows = vehicleRows.querySelectorAll('.vehicle-row');
                if (rows.length === 1) {
                    rows[0].querySelectorAll('input').forEach(input => input.value = '');
                    rows[0].querySelectorAll('select').forEach(select => select.selectedIndex = 0);
                    return;
                }

                button.closest('.vehicle-row').remove();
                reindexVehicleRows();
                isDirty = true;
            });
        }
    </script>
</x-app-layout>