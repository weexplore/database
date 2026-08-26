{{-- resources/views/trips/edit.blade.php --}}
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
                <p class="mt-1 text-sm text-gray-500">
                    Status: {{ ucfirst($trip->tripstatus) }} ·
                    Start: {{ optional($trip->startdate)->format('d M Y') ?? '—' }} ·
                    End: {{ optional($trip->enddate)->format('d M Y') ?? '—' }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('trips.book', $trip) }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                    Print Trip Book
                </a>

                <a href="{{ route('trips.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                    Back to Trips
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            @php
                $tabs = [
                    'details' => 'Details',
                    'notes' => 'Notes',
                    'budget' => 'Budget',
                    'vehicles' => 'Vehicles',
                    'travellers' => 'Travellers',
                    'workflow' => 'Workflow',
                ];

                $activeTab = $activeTab ?? 'details';

                $oldTripVehicles = old('tripvehicles');
                $tripVehicleRows = is_array($oldTripVehicles)
                    ? $oldTripVehicles
                    : $trip->tripVehicles->map(function ($tripVehicle) {
                        return [
                            'vehicleid' => $tripVehicle->vehicleid,
                            'vehiclerole' => $tripVehicle->vehiclerole,
                            'sortorder' => $tripVehicle->sortorder,
                            'isdefaultforlegs' => $tripVehicle->isdefaultforlegs,
                            'notes' => $tripVehicle->notes,
                        ];
                    })->values()->all();

                if (empty($tripVehicleRows)) {
                    $tripVehicleRows = [
                        ['vehicleid' => '', 'vehiclerole' => 'towvehicle', 'sortorder' => 1, 'isdefaultforlegs' => 1, 'notes' => ''],
                        ['vehicleid' => '', 'vehiclerole' => 'caravan', 'sortorder' => 2, 'isdefaultforlegs' => 1, 'notes' => ''],
                    ];
                }
            @endphp

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Trip
                            </div>
                            <h3 class="mt-1 text-lg font-semibold text-gray-900 break-words">
                                {{ $trip->tripname }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Status: {{ ucfirst($trip->tripstatus) }} ·
                                Start: {{ optional($trip->startdate)->format('d M Y') ?? '—' }} ·
                                End: {{ optional($trip->enddate)->format('d M Y') ?? '—' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="px-4 sm:px-6 py-3 border-b border-gray-200">
                    <nav class="flex flex-wrap gap-2" aria-label="Trip sections">
                        @foreach($tabs as $tabKey => $tabLabel)
                            <a href="{{ route('trips.edit', ['trip' => $trip, 'tab' => $tabKey]) }}"
                               class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium {{ $activeTab === $tabKey ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                {{ $tabLabel }}
                            </a>
                        @endforeach
                    </nav>
                </div>
            </div>

            <form method="POST"
                  action="{{ route('trips.update', $trip) }}"
                  id="trip-edit-form"
                  class="space-y-6">
                @csrf
                @method('PUT')

                <input type="hidden" name="tab" value="{{ $activeTab }}">

                <div class="{{ $activeTab === 'details' ? 'block' : 'hidden' }}">
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

                            <div>
                            <label for="triplegsearchprofileid" class="block text-sm font-medium text-gray-700 mb-1">
                                Default Leg Search Profile
                            </label>
                            <select
                                name="triplegsearchprofileid"
                                id="triplegsearchprofileid"
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                            >
                                <option value="">Standard / none</option>
                                @foreach ($tripLegSearchProfiles as $profile)
                                    <option
                                        value="{{ $profile->id }}"
                                        @selected((string) old('triplegsearchprofileid', $trip->triplegsearchprofileid) === (string) $profile->id)
                                    >
                                        {{ $profile->profilename }}
                                        @if(!empty($profile->profiletype))
                                            — {{ ucfirst($profile->profiletype) }}
                                        @endif
                                        @if((int) $profile->tripid === (int) $trip->id)
                                            — Trip
                                        @else
                                            — Shared
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                Used as the default route-discovery/search profile for trip legs unless a leg overrides it.
                            </p>
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
                </div>

                <div class="{{ $activeTab === 'notes' ? 'block' : 'hidden' }}">
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Trip Notes</h3>
                        </div>

                        <div class="space-y-6">
                            <x-forms.markdown-display-editor
                                name="summary"
                                id="summary"
                                :value="old('summary', $trip->summary)"
                                label="Summary"
                                :rows="3"
                                placeholder="Short Markdown summary for this trip"
                                help="Markdown supported, including headings, lists, links, emphasis, and tables."
                                preview-title="Summary Preview"
                            />

                            <x-forms.markdown-display-editor
                                name="planningnotes"
                                id="planningnotes"
                                :value="old('planningnotes', $trip->planningnotes)"
                                label="Planning Notes"
                                :rows="6"
                                placeholder="Planning notes, ideas, reminders, route thinking, bookings to make, and preparation details"
                                help="Markdown supported, including headings, lists, links, emphasis, and tables."
                                preview-title="Planning Notes Preview"
                            />

                            <x-forms.markdown-display-editor
                                name="actualnotes"
                                id="actualnotes"
                                :value="old('actualnotes', $trip->actualnotes)"
                                label="Actual Notes"
                                :rows="6"
                                placeholder="What actually happened during the trip, lessons learned, changes, and observations"
                                help="Markdown supported, including headings, lists, links, emphasis, and tables."
                                preview-title="Actual Notes Preview"
                            />
                        </div>
                    </div>
                </div>

                <div class="{{ $activeTab === 'budget' ? 'block' : 'hidden' }}">
                    <div class="space-y-6">
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
                                           value="{{ old('estimatedtotaldistancekm', $calculatedEstimatedDistance) }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                           readonly>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Calculated from Trip Leg distances.
                                    </p>
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
                    </div>
                </div>

                <div class="{{ $activeTab === 'vehicles' ? 'block' : 'hidden' }}">
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Default Vehicles</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    These vehicles are the default setup for this trip and will be copied to new trip legs.
                                </p>
                            </div>

                            <button type="button"
                                    id="add-trip-vehicle-row"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                Add Vehicle
                            </button>
                        </div>

                        <div id="trip-vehicle-rows" class="space-y-4">
                            @foreach($tripVehicleRows as $index => $row)
                                <div class="trip-vehicle-row grid grid-cols-1 md:grid-cols-12 gap-4 items-start border border-gray-200 rounded-lg p-4">
                                    <div class="md:col-span-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Vehicle
                                        </label>
                                        <select name="tripvehicles[{{ $index }}][vehicleid]"
                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                            <option value="">Select vehicle</option>
                                            @foreach($vehicles as $vehicle)
                                                <option value="{{ $vehicle->id }}"
                                                    @selected((string) ($row['vehicleid'] ?? '') === (string) $vehicle->id)>
                                                    {{ $vehicle->vehiclename }}
                                                    @if($vehicle->registrationnumber)
                                                        ({{ $vehicle->registrationnumber }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="md:col-span-3">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Role
                                        </label>
                                        <select name="tripvehicles[{{ $index }}][vehiclerole]"
                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                            <option value="">Select role</option>
                                            @foreach($vehicleRoleOptions as $role)
                                                <option value="{{ $role }}"
                                                    @selected(($row['vehiclerole'] ?? '') === $role)>
                                                    {{ ucfirst(str_replace('vehicle', ' vehicle', $role)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Sort Order
                                        </label>
                                        <input type="number"
                                               name="tripvehicles[{{ $index }}][sortorder]"
                                               value="{{ $row['sortorder'] ?? '' }}"
                                               min="1"
                                               class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Use for new legs
                                        </label>
                                        <div class="flex items-center h-10">
                                            <input type="hidden" name="tripvehicles[{{ $index }}][isdefaultforlegs]" value="0">
                                            <input type="checkbox"
                                                   name="tripvehicles[{{ $index }}][isdefaultforlegs]"
                                                   value="1"
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm"
                                                   @checked(!empty($row['isdefaultforlegs']))>
                                        </div>
                                    </div>

                                    <div class="md:col-span-1 flex items-end justify-end">
                                        <button type="button"
                                                class="remove-trip-vehicle-row inline-flex items-center px-3 py-2 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm">
                                            Remove
                                        </button>
                                    </div>

                                    <div class="md:col-span-12">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Notes
                                        </label>
                                        <textarea name="tripvehicles[{{ $index }}][notes]"
                                                  rows="3"
                                                  class="js-auto-expand js-markdown-editor w-full min-h-[96px] rounded-md border-gray-300 shadow-sm text-sm resize-none overflow-hidden"
                                                  placeholder="Optional Markdown notes for this trip vehicle setup">{{ $row['notes'] ?? '' }}</textarea>
                                        <p class="mt-1 text-xs text-gray-500">
                                            Markdown supported.
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <template id="trip-vehicle-row-template">
                            <div class="trip-vehicle-row grid grid-cols-1 md:grid-cols-12 gap-4 items-start border border-gray-200 rounded-lg p-4">
                                <div class="md:col-span-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Vehicle
                                    </label>
                                    <select name="tripvehicles[__INDEX__][vehicleid]"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        <option value="">Select vehicle</option>
                                        @foreach($vehicles as $vehicle)
                                            <option value="{{ $vehicle->id }}">
                                                {{ $vehicle->vehiclename }}
                                                @if($vehicle->registrationnumber)
                                                    ({{ $vehicle->registrationnumber }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Role
                                    </label>
                                    <select name="tripvehicles[__INDEX__][vehiclerole]"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        <option value="">Select role</option>
                                        @foreach($vehicleRoleOptions as $role)
                                            <option value="{{ $role }}">
                                                {{ ucfirst(str_replace('vehicle', ' vehicle', $role)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Sort Order
                                    </label>
                                    <input type="number"
                                           name="tripvehicles[__INDEX__][sortorder]"
                                           value=""
                                           min="1"
                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Use for new legs
                                    </label>
                                    <div class="flex items-center h-10">
                                        <input type="hidden" name="tripvehicles[__INDEX__][isdefaultforlegs]" value="0">
                                        <input type="checkbox"
                                               name="tripvehicles[__INDEX__][isdefaultforlegs]"
                                               value="1"
                                               class="rounded border-gray-300 text-blue-600 shadow-sm"
                                               checked>
                                    </div>
                                </div>

                                <div class="md:col-span-1 flex items-end justify-end">
                                    <button type="button"
                                            class="remove-trip-vehicle-row inline-flex items-center px-3 py-2 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm">
                                        Remove
                                    </button>
                                </div>

                                <div class="md:col-span-12">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Notes
                                    </label>
                                    <textarea name="tripvehicles[__INDEX__][notes]"
                                              rows="3"
                                              class="js-auto-expand js-markdown-editor w-full min-h-[96px] rounded-md border-gray-300 shadow-sm text-sm resize-none overflow-hidden"
                                              placeholder="Optional Markdown notes for this trip vehicle setup"></textarea>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Markdown supported.
                                    </p>
                                </div>
                            </div>
                        </template>

                        <p class="text-xs text-gray-500">
                            Keep one row for the tow vehicle and one for the caravan where applicable.
                        </p>
                    </div>
                </div>

                <div class="{{ $activeTab === 'travellers' ? 'block' : 'hidden' }}">
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
                </div>

                <div class="{{ $activeTab === 'workflow' ? 'block' : 'hidden' }}">
                    <div class="space-y-6">
                        <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Trip Workflow</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Open the related planning, movement, stay, item, booking, review, and fuel screens for this trip.
                                </p>
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
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <a href="{{ route('trips.index') }}"
                       class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Back to Trips
                    </a>

                    @if($activeTab !== 'workflow')
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md text-sm font-semibold text-white hover:bg-green-700">
                            Save Changes
                        </button>
                    @endif
                </div>
            </form>

            <div class="{{ $activeTab === 'workflow' ? 'block' : 'hidden' }}">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Shift Planner Dates</h3>
                        <p class="mt-1 text-xs text-gray-600">
                            Use the Trip Start Date as the master start point and shift all planner dates by the same offset.
                            This is only intended for trips still in Planned status.
                        </p>
                    </div>

                    <div class="p-4">
                        <form method="POST"
                            action="{{ route('trips.shiftPlannerDates', ['trip' => $trip->id]) }}"
                            onsubmit="return confirm('Shift all planner dates to align with the Trip Start Date?');"
                            class="space-y-4">
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="tab" value="workflow">

                            <label class="flex items-start gap-3">
                                <input type="checkbox"
                                    name="regenerate_outputs"
                                    value="1"
                                    class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm">
                                <span class="text-sm text-gray-700">
                                    Also regenerate legs, stays, items and leg points from the updated planner dates.
                                </span>
                            </label>

                            <div class="flex items-center justify-end">
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                    Shift Planner Dates from Trip Start
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4 border border-amber-200">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Planner rebuild</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Delete existing planning items for this trip and rebuild them from Trip Legs, Leg Points, Trip Stays, and Trip Items.
                        </p>
                    </div>

                    <form method="POST"
                        action="{{ route('trips.planner.rebuildFromOutputs', $trip) }}"
                        onsubmit="return confirm('Rebuild the planner for this trip? Existing planning items will be deleted and replaced with rebuilt entries. This cannot be undone.');">
                        @csrf

                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700 text-sm">
                            Rebuild Planner from Outputs
                        </button>
                    </form>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-red-200">
                    <div class="px-4 py-3 border-b border-red-200 bg-red-50">
                        <h3 class="text-sm font-semibold text-red-800">Delete Trip</h3>
                        <p class="mt-1 text-xs text-red-700">
                            This permanently removes this trip record.
                        </p>
                    </div>

                    <div class="p-4">
                        <form method="POST"
                            action="{{ route('trips.destroy', $trip) }}"
                            onsubmit="return confirm('Delete this trip? This cannot be undone.');">
                            @csrf
                            @method('DELETE')

                            @if (!empty($returnTo))
                                <input type="hidden" name="return_to" value="{{ $returnTo }}">
                            @endif

                            <div class="flex items-center justify-end">
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md text-xs font-semibold text-red-700 bg-white uppercase tracking-widest hover:bg-red-50">
                                    Delete Trip
                                </button>
                            </div>
                        </form>
                    </div>
                </div>    

            </div>      
        </div>
    </div>

    @include('partials.markdown.markdown-styles')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('trip-edit-form');
            if (!form) return;

            let isDirty = false;
            let isSubmitting = false;

            function resizeTextarea(textarea) {
                textarea.style.height = 'auto';
                textarea.style.overflowY = 'hidden';
                textarea.style.height = textarea.scrollHeight + 'px';
            }

            function bindAutoExpand(scope = document) {
                const textareas = Array.from(scope.querySelectorAll('.js-auto-expand'));

                textareas.forEach(textarea => {
                    if (textarea.dataset.autoExpandBound === '1') {
                        resizeTextarea(textarea);
                        return;
                    }

                    resizeTextarea(textarea);

                    textarea.addEventListener('input', function () {
                        resizeTextarea(textarea);
                    });

                    textarea.dataset.autoExpandBound = '1';
                });
            }

            function bindDirtyTracking(scope = document) {
                scope.querySelectorAll('input, select, textarea').forEach((element) => {
                    if (element.dataset.dirtyBound === '1') {
                        return;
                    }

                    const type = (element.getAttribute('type') || '').toLowerCase();

                    if (type === 'hidden' || element.hasAttribute('readonly') || element.disabled) {
                        return;
                    }

                    element.addEventListener('change', () => isDirty = true);
                    element.addEventListener('input', () => isDirty = true);
                    element.dataset.dirtyBound = '1';
                });
            }

            bindAutoExpand(document);
            bindDirtyTracking(document);

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

            const tabNav = document.querySelector('[aria-label="Trip sections"]');
            if (tabNav) {
                tabNav.querySelectorAll('a').forEach((tabLink) => {
                    tabLink.addEventListener('click', function (event) {
                        if (!isDirty || isSubmitting) {
                            return;
                        }

                        const currentUrl = new URL(window.location.href);
                        const currentTab = currentUrl.searchParams.get('tab') || 'details';

                        const targetUrl = new URL(tabLink.href);
                        const targetTab = targetUrl.searchParams.get('tab') || 'details';

                        if (currentTab === targetTab) {
                            return;
                        }

                        event.preventDefault();

                        const confirmed = window.confirm(
                            'You have unsaved changes on this tab. Discard changes and switch tabs?'
                        );

                        if (confirmed) {
                            window.location.href = tabLink.href;
                        }
                    });
                });
            }

            const vehicleRowsContainer = document.getElementById('trip-vehicle-rows');
            const addVehicleRowButton = document.getElementById('add-trip-vehicle-row');
            const vehicleRowTemplate = document.getElementById('trip-vehicle-row-template');

            if (vehicleRowsContainer && addVehicleRowButton && vehicleRowTemplate) {
                let nextVehicleIndex = vehicleRowsContainer.querySelectorAll('.trip-vehicle-row').length;

                addVehicleRowButton.addEventListener('click', function () {
                    const html = vehicleRowTemplate.innerHTML.replaceAll('__INDEX__', String(nextVehicleIndex));
                    vehicleRowsContainer.insertAdjacentHTML('beforeend', html);

                    const newRow = vehicleRowsContainer.lastElementChild;
                    if (newRow) {
                        bindAutoExpand(newRow);
                        bindDirtyTracking(newRow);
                    }

                    nextVehicleIndex += 1;
                    isDirty = true;
                });

                vehicleRowsContainer.addEventListener('click', function (event) {
                    const removeButton = event.target.closest('.remove-trip-vehicle-row');
                    if (!removeButton) {
                        return;
                    }

                    const row = removeButton.closest('.trip-vehicle-row');
                    if (!row) {
                        return;
                    }

                    row.remove();
                    isDirty = true;
                });
            }
        });
    </script>
</x-app-layout>