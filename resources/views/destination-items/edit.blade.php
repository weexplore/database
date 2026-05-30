<x-app-layout>
    @php
        $returnTo = request('return_to', route('destination-items.index'));
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Destination Item
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $destinationItem->itemname ?: 'Destination Item' }}
                </p>
            </div>

            <a href="{{ $returnTo }}"
               class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                Back
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                {{-- Main form --}}
                <div class="xl:col-span-2">
                    <form method="POST"
                          action="{{ route('destination-items.update', $destinationItem) }}"
                          id="destination-item-edit-form"
                          class="space-y-6">
                        @csrf
                        @method('PUT')

                        <input type="hidden" name="return_to" value="{{ $returnTo }}">

                        {{-- Core Details --}}
                        <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Core Details</h3>
                            </div>

                            @include('destination-items._form', [
                                'destinationItem' => $destinationItem,
                                'destinations' => $destinations,
                                'places' => $places,
                                'itemTypes' => $itemTypes,
                                'selectedDestinationId' => $selectedDestinationId ?? null,
                            ])
                        </div>

                        {{-- Location & Contact --}}
                        <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Location & Contact</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label for="addressline1" class="block text-sm font-medium text-gray-700 mb-1">
                                        Address line 1
                                    </label>
                                    <input type="text"
                                        name="addressline1"
                                        id="addressline1"
                                        value="{{ old('addressline1', $destinationItem->addressline1) }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>

                                <div class="md:col-span-2">
                                    <label for="addressline2" class="block text-sm font-medium text-gray-700 mb-1">
                                        Address line 2
                                    </label>
                                    <input type="text"
                                        name="addressline2"
                                        id="addressline2"
                                        value="{{ old('addressline2', $destinationItem->addressline2) }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>

                                <div class="md:col-span-2">
                                    <label for="addressline3" class="block text-sm font-medium text-gray-700 mb-1">
                                        Address line 3
                                    </label>
                                    <input type="text"
                                        name="addressline3"
                                        id="addressline3"
                                        value="{{ old('addressline3', $destinationItem->addressline3) }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>

                                <div>
                                    <label for="postcode" class="block text-sm font-medium text-gray-700 mb-1">
                                        Postcode
                                    </label>
                                    <input type="text"
                                        name="postcode"
                                        id="postcode"
                                        value="{{ old('postcode', $destinationItem->postcode) }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>

                                <div>
                                    <label for="telephone" class="block text-sm font-medium text-gray-700 mb-1">
                                        Telephone
                                    </label>
                                    <input type="text"
                                        name="telephone"
                                        id="telephone"
                                        value="{{ old('telephone', $destinationItem->telephone) }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                                <input type="hidden" name="open_website_after_save" id="open-website-after-save" value="">

                                <input type="hidden" id="open-website-after-save-url" value="{{ session('open_website_after_save_url', '') }}">

                                <div class="md:col-span-2">
                                    <label for="website" class="block text-sm font-medium text-gray-700 mb-1">
                                        Website
                                    </label>

                                    @php
                                        $websiteValue = old('website', $destinationItem->website ?? '');
                                    @endphp

                                    <div class="flex items-center gap-2">
                                        <input type="url"
                                            name="website"
                                            id="website"
                                            value="{{ $websiteValue }}"
                                            class="flex-1 rounded-md border-gray-300 shadow-sm text-sm">

                                        <button type="button"
                                                id="website-open-button"
                                                class="inline-flex items-center px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed">
                                            Open
                                        </button>
                                    </div>

                                    <p id="website-open-help" class="mt-1 text-xs text-gray-500">
                                        Enter a valid website to enable this button. If there are unsaved changes, the record will save first and stay on this page.
                                    </p>
                                </div>
                            </div>

                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-4">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900">Location picker</h3>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Search for a location, click the map, or drag the marker to update latitude and longitude.
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-[1fr_auto_auto] gap-3">
                                    <div>
                                        <label for="map-search" class="block text-sm font-medium text-gray-700">
                                            Search place or address
                                        </label>
                                        <input type="text"
                                            id="map-search"
                                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm"
                                            placeholder="Search by address, town, or place name">
                                    </div>

                                    <div class="flex items-end">
                                        <button type="button"
                                                id="map-search-button"
                                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                            Search Map
                                        </button>
                                    </div>

                                    <div class="flex items-end">
                                        <button type="button"
                                                id="use-my-location"
                                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                            Use My Location
                                        </button>
                                    </div>
                                </div>

                                <div id="destination-item-map" class="h-96 w-full rounded-lg border border-gray-300 overflow-hidden"></div>

                                <div class="flex flex-wrap gap-2">
                                    <button type="button"
                                            id="sync-from-fields"
                                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                        Move Marker to Coordinates
                                    </button>

                                    <a href="#"
                                       id="open-in-google-maps"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                        Open in Google Maps
                                    </a>
                                </div>

                                <p id="map-status" class="text-xs text-gray-500"></p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="latitude" class="block text-sm font-medium text-gray-700 mb-1">
                                        Latitude
                                    </label>
                                    <input type="number"
                                        step="0.0000001"
                                        name="latitude"
                                        id="latitude"
                                        value="{{ old('latitude', $destinationItem->latitude) }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>

                                <div>
                                    <label for="longitude" class="block text-sm font-medium text-gray-700 mb-1">
                                        Longitude
                                    </label>
                                    <input type="number"
                                        step="0.0000001"
                                        name="longitude"
                                        id="longitude"
                                        value="{{ old('longitude', $destinationItem->longitude) }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>

                                <div class="md:col-span-2">
                                    <label for="internetsearch" class="block text-sm font-medium text-gray-700 mb-1">
                                        Internet search terms
                                    </label>
                                    <input type="text"
                                        name="internetsearch"
                                        id="internetsearch"
                                        value="{{ old('internetsearch', $destinationItem->internetsearch) }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ $returnTo }}"
                               class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                                Cancel
                            </a>

                            <button type="submit"
                                    class="inline-flex items-center px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                Save Destination Item
                            </button>
                        </div>
                    </form>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-red-200">
                        <div class="px-4 py-3 border-b border-red-200 bg-red-50">
                            <h3 class="text-sm font-semibold text-red-800">Delete Destination Item</h3>
                            <p class="mt-1 text-xs text-red-700">
                                This permanently removes the destination item from the register.
                            </p>
                        </div>

                        <div class="p-4">
                            <form method="POST"
                                action="{{ route('destination-items.destroy', $destinationItem) }}"
                                onsubmit="return confirm('Delete this destination item? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="return_to" value="{{ $returnTo }}">

                                <div class="flex items-center justify-end">
                                    <button type="submit"
                                            class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md text-xs font-semibold text-red-700 bg-white uppercase tracking-widest hover:bg-red-50">
                                        Delete Destination Item
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="xl:col-span-1 space-y-6">
                    @if ($destinationItem->destination)
                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="px-4 py-3 border-b border-gray-200 flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900">Linked Destination</h3>
                                    <p class="mt-1 text-xs text-gray-500">Parent destination record</p>
                                </div>

                                <a href="{{ route('destinations.edit', [
                                        'destination' => $destinationItem->destination,
                                        'return_to' => url()->full(),
                                    ]) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-xs">
                                    Edit Destination
                                </a>
                            </div>

                            <div class="p-4">
                                <dl class="space-y-3 text-sm">
                                    <div>
                                        <dt class="text-gray-500">Destination</dt>
                                        <dd class="text-gray-900">{{ $destinationItem->destination->destinationname ?: '—' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500">Type</dt>
                                        <dd class="text-gray-900">{{ $destinationItem->destination->destinationtype ?: '—' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500">Linked Place</dt>
                                        <dd class="text-gray-900">{{ $destinationItem->destination->place?->placename ?: '—' }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    @endif

                    @if ($destinationItem->place)
                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="px-4 py-3 border-b border-gray-200 flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900">Linked Place</h3>
                                    <p class="mt-1 text-xs text-gray-500">Reusable location record</p>
                                </div>

                                <a href="{{ route('places.edit', [
                                        'place' => $destinationItem->place,
                                        'return_to' => url()->full(),
                                    ]) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-xs">
                                    Edit Place
                                </a>
                            </div>

                            <div class="p-4">
                                <dl class="space-y-3 text-sm">
                                    <div>
                                        <dt class="text-gray-500">Place</dt>
                                        <dd class="text-gray-900">{{ $destinationItem->place->placename ?: '—' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500">Type</dt>
                                        <dd class="text-gray-900">{{ $destinationItem->place->placetype ?: '—' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500">Locality</dt>
                                        <dd class="text-gray-900">{{ $destinationItem->place->locality ?: '—' }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    @endif
                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">Internet Sources</h3>
                                <p class="mt-1 text-xs text-gray-500">
                                    Curated links and summaries for this destination item
                                </p>
                            </div>

                            <button type="button"
                                    id="toggle-item-source-create"
                                    class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded hover:bg-green-700 text-xs">
                                Add Source
                            </button>
                        </div>

                        <div class="p-4 space-y-4">
                            <form method="POST"
                                action="{{ route('destination-items.sources.store', $destinationItem) }}"
                                id="destination-item-source-create-form"
                                class="hidden border border-gray-200 rounded-lg p-4 space-y-3 bg-gray-50">
                                @csrf
                                <input type="hidden" name="return_to" value="{{ url()->full() }}">

                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Source type</label>
                                    <select name="sourcetype" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        @foreach($sourceTypeOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Title</label>
                                    <input type="text" name="sourcetitle" class="w-full rounded-md border-gray-300 shadow-sm text-sm" required>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Publisher</label>
                                    <input type="text" name="sourcepublisher" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">URL</label>
                                    <input type="url" name="sourceurl" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Retrieved on</label>
                                        <input type="date" name="retrievedon" value="{{ now()->toDateString() }}"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                                        <select name="importstatus" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                            @foreach($sourceImportStatusOptions as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Imported summary</label>
                                    <textarea name="importedsummary" rows="3" class="w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Imported notes</label>
                                    <textarea name="importednotes" rows="2" class="w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Internal notes</label>
                                    <textarea name="internalnotes" rows="2" class="w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
                                </div>

                                <div class="flex items-center justify-end gap-2">
                                    <button type="button"
                                            id="cancel-item-source-create"
                                            class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-xs">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                            class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded hover:bg-green-700 text-xs">
                                        Save Source
                                    </button>
                                </div>
                            </form>

                            <div class="space-y-3">
                                @forelse($destinationItem->sources as $source)
                                    <div class="border border-gray-200 rounded-md px-3 py-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $source->sourcetitle ?: 'Source' }}
                                                </div>

                                                <div class="mt-1 text-xs text-gray-500">
                                                    {{ $sourceTypeOptions[$source->sourcetype] ?? ucfirst($source->sourcetype ?? 'other') }}
                                                    @if($source->sourcepublisher)
                                                        · {{ $source->sourcepublisher }}
                                                    @endif
                                                    @if($source->retrievedon)
                                                        · Retrieved {{ $source->retrievedon->format('d M Y') }}
                                                    @endif
                                                    @if($source->importstatus)
                                                        · Status: {{ $sourceImportStatusOptions[$source->importstatus] ?? $source->importstatus }}
                                                    @endif
                                                </div>

                                                @if($source->sourceurl)
                                                    <div class="mt-1">
                                                        <a href="{{ $source->sourceurl }}"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="text-xs text-blue-600 hover:underline">
                                                            Open source
                                                        </a>
                                                    </div>
                                                @endif

                                                @if($source->importedsummary)
                                                    <div class="mt-2 text-xs text-gray-600 whitespace-pre-line">
                                                        {{ $source->importedsummary }}
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="flex items-center gap-2 shrink-0">
                                                <button type="button"
                                                        class="toggle-item-source-edit inline-flex items-center px-2.5 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs"
                                                        data-target="item-source-edit-{{ $source->id }}">
                                                    Edit
                                                </button>

                                                <form method="POST"
                                                    action="{{ route('destination-items.sources.destroy', [$destinationItem, $source]) }}"
                                                    onsubmit="return confirm('Delete this internet source?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="return_to" value="{{ url()->full() }}">
                                                    <button type="submit"
                                                            class="inline-flex items-center px-2.5 py-1.5 bg-red-600 text-white rounded hover:bg-red-700 text-xs">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <form method="POST"
                                            action="{{ route('destination-items.sources.update', [$destinationItem, $source]) }}"
                                            id="item-source-edit-{{ $source->id }}"
                                            class="hidden mt-3 border-t border-gray-200 pt-3 space-y-3">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="return_to" value="{{ url()->full() }}">

                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Source type</label>
                                                <select name="sourcetype" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                    @foreach($sourceTypeOptions as $value => $label)
                                                        <option value="{{ $value }}" @selected($source->sourcetype === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Title</label>
                                                <input type="text" name="sourcetitle" value="{{ $source->sourcetitle }}"
                                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm" required>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Publisher</label>
                                                <input type="text" name="sourcepublisher" value="{{ $source->sourcepublisher }}"
                                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                            </div>

                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">URL</label>
                                                <input type="url" name="sourceurl" value="{{ $source->sourceurl }}"
                                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Retrieved on</label>
                                                    <input type="date" name="retrievedon"
                                                        value="{{ optional($source->retrievedon)->format('Y-m-d') }}"
                                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                                                    <select name="importstatus" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                        @foreach($sourceImportStatusOptions as $value => $label)
                                                            <option value="{{ $value }}" @selected($source->importstatus === $value)>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Imported summary</label>
                                                <textarea name="importedsummary" rows="3"
                                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ $source->importedsummary }}</textarea>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Imported notes</label>
                                                <textarea name="importednotes" rows="2"
                                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ $source->importednotes }}</textarea>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Internal notes</label>
                                                <textarea name="internalnotes" rows="2"
                                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ $source->internalnotes }}</textarea>
                                            </div>

                                            <div class="flex items-center justify-end gap-2">
                                                <button type="button"
                                                        class="toggle-item-source-edit inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-xs"
                                                        data-target="item-source-edit-{{ $source->id }}">
                                                    Cancel
                                                </button>
                                                <button type="submit"
                                                        class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs">
                                                    Save Changes
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">
                                        No internet sources are currently linked to this destination item.
                                    </p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">Destination Item Reviews</h3>
                                <p class="mt-1 text-xs text-gray-500">Reviews linked to this destination item</p>
                            </div>
                        </div>

                        <div class="p-4 space-y-4">
                            <dl class="space-y-3 text-sm">
                                <div>
                                    <dt class="text-gray-500">Review Count</dt>
                                    <dd class="text-gray-900">{{ $reviewCount }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Average Overall Rating</dt>
                                    <dd class="text-gray-900">
                                        {{ $averageOverallRating !== null ? number_format($averageOverallRating, 1) . ' / 5' : '—' }}
                                    </dd>
                                </div>
                            </dl>

                            @if ($latestReviews->isNotEmpty())
                                <div class="space-y-3">
                                    @foreach ($latestReviews as $review)
                                        <div class="border border-gray-200 rounded-md p-3">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $review->title ?: 'Untitled review' }}
                                                    </div>
                                                    <div class="mt-1 text-xs text-gray-500">
                                                        {{ optional($review->reviewdate)->format('d M Y') ?: 'No date' }}
                                                        @if ($review->traveller)
                                                            · {{ $review->traveller->fullname ?? $review->traveller->name ?? 'Traveller' }}
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="text-xs font-semibold text-gray-700 whitespace-nowrap">
                                                    {{ $review->ratingoverall ? $review->ratingoverall . '/5' : '—' }}
                                                </div>
                                            </div>

                                            @if ($review->comments)
                                                <p class="mt-2 text-xs text-gray-600 line-clamp-3">
                                                    {{ \Illuminate\Support\Str::limit($review->comments, 140) }}
                                                </p>
                                            @endif

                                            @if ($review->trip)
                                                <div class="mt-2 text-xs text-gray-500">
                                                    Trip: {{ $review->trip->tripname ?: '—' }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500">No reviews are currently linked to this destination item.</p>
                            @endif
                        </div>
                    </div>
                    <div class="xl:col-span-1 space-y-6">
                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="px-4 py-3 border-b border-gray-200">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900">Attachments</h3>
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $destinationItem->attachments->count() }} linked record{{ $destinationItem->attachments->count() === 1 ? '' : 's' }}
                                    </p>
                                </div>
                            </div>

                            <div class="p-4 space-y-4">
                                <dl class="space-y-3 text-sm">
                                    <div>
                                        <dt class="text-gray-500">Linked record</dt>
                                        <dd class="text-gray-900">{{ $destinationItem->itemname ?: 'Destination Item' }}</dd>
                                    </div>

                                    @if ($destinationItem->destination)
                                        <div>
                                            <dt class="text-gray-500">Destination</dt>
                                            <dd class="text-gray-900">{{ $destinationItem->destination->destinationname ?: '—' }}</dd>
                                        </div>
                                    @endif

                                    @if ($destinationItem->place)
                                        <div>
                                            <dt class="text-gray-500">Place</dt>
                                            <dd class="text-gray-900">{{ $destinationItem->place->placename ?: '—' }}</dd>
                                        </div>
                                    @endif
                                </dl>

                                @if ($destinationItem->attachments->isNotEmpty())
                                    <div class="space-y-3">
                                        @foreach ($destinationItem->attachments as $attachment)
                                            <div class="border border-gray-200 rounded-md px-3 py-2">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $attachment->description ?: $attachment->originalfilename ?: 'Attachment' }}
                                                </div>

                                                <div class="mt-1 text-xs text-gray-500">
                                                    {{ $attachment->attachmenttype ?: 'File' }}
                                                    @if ($attachment->uploadedat)
                                                        · {{ \Illuminate\Support\Carbon::parse($attachment->uploadedat)->format('d M Y') }}
                                                    @endif
                                                    @if ($attachment->isprimary)
                                                        · Primary
                                                    @endif
                                                </div>

                                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                                    @if (Route::has('attachments.view'))
                                                        <a href="{{ route('attachments.view', $attachment) }}"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="inline-flex items-center px-2.5 py-1.5 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-xs">
                                                            View
                                                        </a>
                                                    @endif

                                                    @if (Route::has('attachments.edit'))
                                                        <a href="{{ route('attachments.edit', [
                                                            'attachment' => $attachment,
                                                            'return_to' => url()->full(),
                                                        ]) }}"
                                                        class="inline-flex items-center px-2.5 py-1.5 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-xs">
                                                            Edit
                                                        </a>
                                                    @endif

                                                    @if (Route::has('attachments.destroy'))
                                                        <form method="POST"
                                                            action="{{ route('attachments.destroy', $attachment) }}"
                                                            onsubmit="return confirm('Delete this attachment? This cannot be undone.');"
                                                            class="inline-flex">
                                                            @csrf
                                                            @method('DELETE')
                                                            <input type="hidden" name="return_to" value="{{ url()->full() }}">

                                                            <button type="submit"
                                                                    class="inline-flex items-center px-2.5 py-1.5 border border-red-300 rounded-md text-xs font-semibold text-red-700 bg-white hover:bg-red-50">
                                                                Delete
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500">
                                        No attachments are currently linked to this destination item.
                                    </p>
                                @endif

                                @if (Route::has('attachments.index'))
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('attachments.index', [
                                            'linkedtype' => 'destination_item',
                                            'linkedid' => $destinationItem->id,
                                            'return_to' => url()->full(),
                                        ]) }}"
                                        class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-sm">
                                            View Attachments
                                        </a>

                                        <a href="{{ route('attachments.index', [
                                            'linkedtype' => 'destination_item',
                                            'linkedid' => $destinationItem->id,
                                            'show_create' => 1,
                                            'return_to' => url()->full(),
                                        ]) }}"
                                        class="inline-flex items-center px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                            Add Attachment
                                        </a>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500">Attachments module not yet enabled.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Record Summary</h3>
                        <dl class="mt-3 space-y-2 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Item ID</dt>
                                <dd class="text-gray-900">{{ $destinationItem->id }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Types</dt>
                                <dd class="text-gray-900">
                                    {{ $destinationItem->itemTypes->pluck('typename')->join(', ') ?: '—' }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Created</dt>
                                <dd class="text-gray-900">{{ optional($destinationItem->createdat)->format('d M Y') ?: '—' }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Updated</dt>
                                <dd class="text-gray-900">{{ optional($destinationItem->updatedat)->format('d M Y') ?: '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>

<script defer
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

@include('partials.forms.markdown-field-scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {
    const placeSelect = document.getElementById('placeid');
    const destinationSelect = document.getElementById('destinationid');
    const toggleButton = document.getElementById('toggle-item-types-panel');
    const panel = document.getElementById('item-types-panel');
    const summary = document.getElementById('selected-item-types-summary');

    const destinationOptions = destinationSelect
        ? Array.from(destinationSelect.querySelectorAll('option'))
            .filter(option => option.value !== '')
            .map(option => ({
                value: option.value,
                label: option.textContent.trim(),
                placeId: option.dataset.placeId ? String(option.dataset.placeId) : '',
            }))
        : [];

    function rebuildDestinationOptions() {
        if (!placeSelect || !destinationSelect) {
            return;
        }

        const selectedPlaceId = placeSelect.value ? String(placeSelect.value) : '';
        const previousDestinationId = destinationSelect.value || destinationSelect.dataset.selectedDestinationId || '';

        destinationSelect.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = selectedPlaceId ? 'Select destination' : 'Select place first';
        destinationSelect.appendChild(placeholder);

        const matchingDestinations = destinationOptions.filter(option => {
            if (!selectedPlaceId) {
                return false;
            }
            return option.placeId === selectedPlaceId;
        });

        matchingDestinations.forEach(option => {
            const el = document.createElement('option');
            el.value = option.value;
            el.textContent = option.label;

            if (String(option.value) === String(previousDestinationId)) {
                el.selected = true;
            }

            destinationSelect.appendChild(el);
        });

        const selectedStillExists = matchingDestinations.some(option => String(option.value) === String(previousDestinationId));

        if (!selectedStillExists) {
            destinationSelect.value = '';
        }

        destinationSelect.dataset.selectedDestinationId = destinationSelect.value || '';
        destinationSelect.disabled = !selectedPlaceId;
    }

    if (placeSelect && destinationSelect) {
        placeSelect.addEventListener('change', rebuildDestinationOptions);
        rebuildDestinationOptions();
    }

    if (!toggleButton || !panel || !summary) {
        return;
    }

    const checkboxes = Array.from(
        document.querySelectorAll('.destination-item-type-checkbox')
    );

    function updateSummary() {
        const selectedLabels = checkboxes
            .filter(checkbox => checkbox.checked)
            .map(checkbox => checkbox.closest('label')?.querySelector('span')?.textContent?.trim())
            .filter(Boolean);

        summary.innerHTML = '';

        if (selectedLabels.length === 0) {
            summary.classList.add('hidden');
            return;
        }

        summary.classList.remove('hidden');

        selectedLabels.forEach(label => {
            const chip = document.createElement('span');
            chip.className = 'inline-flex items-center px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-medium border border-blue-200';
            chip.textContent = label;
            summary.appendChild(chip);
        });
    }

    function updateToggleLabel() {
        toggleButton.textContent = panel.classList.contains('hidden')
            ? 'Add or change types'
            : 'Hide types';
    }

    toggleButton.addEventListener('click', function () {
        panel.classList.toggle('hidden');
        updateToggleLabel();
    });

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSummary);
    });

    updateSummary();
    updateToggleLabel();
});
</script>

<script>
window.addEventListener('load', function () {
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const searchInput = document.getElementById('map-search');
    const searchButton = document.getElementById('map-search-button');
    const useMyLocationButton = document.getElementById('use-my-location');
    const syncFromFieldsButton = document.getElementById('sync-from-fields');
    const mapStatus = document.getElementById('map-status');
    const googleMapsLink = document.getElementById('open-in-google-maps');
    const mapElement = document.getElementById('destination-item-map');

    const placeSelect = document.getElementById('placeid');
    const address1Input = document.getElementById('addressline1');
    const address3Input = document.getElementById('addressline3');

    if (!latInput || !lngInput || !mapElement) {
        return;
    }

    if (typeof window.L === 'undefined') {
        if (mapStatus) {
            mapStatus.textContent = 'Leaflet map library did not load.';
        }
        return;
    }

    let searchTouchedManually = false;

    function coordsAreBlank() {
        return latInput.value.trim() === '' && lngInput.value.trim() === '';
    }

    function buildPreferredSearchText() {
        const parts = [];

        const addr1 = address1Input ? address1Input.value.trim() : '';
        const addr3 = address3Input ? address3Input.value.trim() : '';

        if (addr1 !== '') {
            parts.push(addr1);
        }
        if (addr3 !== '') {
            parts.push(addr3);
        }

        if (parts.length > 0) {
            return parts.join(', ');
        }

        if (placeSelect && placeSelect.value) {
            const opt = placeSelect.options[placeSelect.selectedIndex];
            if (opt) {
                const placeName = opt.text.trim();
                if (placeName !== '') {
                    return placeName;
                }
            }
        }

        return '';
    }

    function shouldAutoFillSearch() {
        return searchInput && coordsAreBlank();
    }

    function syncSearchFromContext(force = false) {
        if (!searchInput) return;
        if (!shouldAutoFillSearch()) return;

        const preferred = buildPreferredSearchText();
        const current = searchInput.value.trim();

        if (preferred === '') {
            return;
        }

        if (
            force ||
            !searchTouchedManually ||
            current === '' ||
            current === preferred
        ) {
            searchInput.value = preferred;
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const preferred = buildPreferredSearchText();
            const current = searchInput.value.trim();
            searchTouchedManually = current !== '' && current !== preferred;
        });
    }

    if (placeSelect) {
        placeSelect.addEventListener('change', function () {
            searchTouchedManually = false;
            syncSearchFromContext();
        });
    }

    if (address1Input) {
        address1Input.addEventListener('input', function () {
            syncSearchFromContext();
        });
    }

    if (address3Input) {
        address3Input.addEventListener('input', function () {
            syncSearchFromContext();
        });
    }

    // initial sync on load
    syncSearchFromContext(true);

    const hasCoords = latInput.value !== '' && lngInput.value !== '';
    const defaultLat = parseFloat(latInput.value || '-37.8136');
    const defaultLng = parseFloat(lngInput.value || '144.9631');
    const defaultZoom = hasCoords ? 15 : 6;

    const map = L.map('destination-item-map').setView([defaultLat, defaultLng], defaultZoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const marker = L.marker([defaultLat, defaultLng], {
        draggable: true
    }).addTo(map);

    function setStatus(message) {
        if (mapStatus) {
            mapStatus.textContent = message;
        }
    }

    function updateGoogleMapsLink(lat, lng) {
        if (googleMapsLink) {
            googleMapsLink.href = `https://www.google.com/maps?q=${lat},${lng}`;
        }
    }

    function updateFields(lat, lng) {
        latInput.value = Number(lat).toFixed(7);
        lngInput.value = Number(lng).toFixed(7);
        updateGoogleMapsLink(latInput.value, lngInput.value);
    }

    function updateMarker(lat, lng, zoom = null) {
        marker.setLatLng([lat, lng]);
        map.panTo([lat, lng]);

        if (zoom !== null) {
            map.setZoom(zoom);
        }

        updateFields(lat, lng);
    }

    marker.on('dragend', function () {
        const position = marker.getLatLng();
        updateFields(position.lat, position.lng);
        setStatus('Marker moved. Coordinates updated.');
    });

    map.on('click', function (event) {
        updateMarker(event.latlng.lat, event.latlng.lng);
        setStatus('Map clicked. Coordinates updated.');
    });

    latInput.addEventListener('change', function () {
        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);

        if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
            updateMarker(lat, lng);
            setStatus('Marker moved to typed coordinates.');
        }
    });

    lngInput.addEventListener('change', function () {
        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);

        if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
            updateMarker(lat, lng);
            setStatus('Marker moved to typed coordinates.');
        }
    });

    syncFromFieldsButton?.addEventListener('click', function () {
        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);

        if (Number.isNaN(lat) || Number.isNaN(lng)) {
            setStatus('Enter both latitude and longitude first.');
            return;
        }

        updateMarker(lat, lng, 15);
        setStatus('Marker moved to entered coordinates.');
    });

    searchButton?.addEventListener('click', async function () {
        const query = searchInput?.value.trim();

        if (!query) {
            setStatus('Enter a place or address to search.');
            return;
        }

        setStatus('Searching map...');

        try {
            const url = `https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=${encodeURIComponent(query)}`;

            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`Search request failed with status ${response.status}`);
            }

            const results = await response.json();

            if (!results.length) {
                setStatus('No matching location found.');
                return;
            }

            const result = results[0];
            const lat = parseFloat(result.lat);
            const lng = parseFloat(result.lon);

            updateMarker(lat, lng, 15);
            setStatus(`Found: ${result.display_name}`);
        } catch (error) {
            setStatus(`Unable to search location right now. ${error.message}`);
        }
    });

    searchInput?.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            searchButton?.click();
        }
    });

    useMyLocationButton?.addEventListener('click', function () {
        if (!navigator.geolocation) {
            setStatus('Geolocation is not supported in this browser.');
            return;
        }

        setStatus('Finding your location...');

        navigator.geolocation.getCurrentPosition(
            function (position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                updateMarker(lat, lng, 15);
                setStatus('Current location loaded.');
            },
            function () {
                setStatus('Unable to retrieve your location.');
            },
            {
                enableHighAccuracy: true,
                timeout: 10000
            }
        );
    });

    updateGoogleMapsLink(defaultLat, defaultLng);
    setStatus(hasCoords ? 'Loaded saved coordinates.' : 'Click the map or search to set coordinates.');

    requestAnimationFrame(() => {
        map.invalidateSize();
    });

    setTimeout(() => {
        map.invalidateSize();
    }, 300);
});
</script>
@pushOnce('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('destination-item-edit-form');
    const websiteInput = document.getElementById('website');
    const openButton = document.getElementById('website-open-button');

    const toggleItemSourceCreateButton = document.getElementById('toggle-item-source-create');
    const itemSourceCreateForm = document.getElementById('destination-item-source-create-form');
    const cancelItemSourceCreateButton = document.getElementById('cancel-item-source-create');

    if (toggleItemSourceCreateButton && itemSourceCreateForm) {
        toggleItemSourceCreateButton.addEventListener('click', function () {
            itemSourceCreateForm.classList.toggle('hidden');
        });
    }

    if (cancelItemSourceCreateButton && itemSourceCreateForm) {
        cancelItemSourceCreateButton.addEventListener('click', function () {
            itemSourceCreateForm.classList.add('hidden');
        });
    }

    document.querySelectorAll('.toggle-item-source-edit').forEach((button) => {
        button.addEventListener('click', function () {
            const panel = document.getElementById(button.dataset.target);
            if (panel) {
                panel.classList.toggle('hidden');
            }
        });
    });

    if (!form || !websiteInput || !openButton) {
        return;
    }

    let isDirty = false;

    function normaliseUrl(value) {
        const trimmed = value.trim();
        if (!trimmed) return '';
        if (/^https?:\/\//i.test(trimmed)) return trimmed;
        return 'https://' + trimmed;
    }

    function isValidUrl(value) {
        const href = normaliseUrl(value);
        if (!href) return false;

        try {
            const url = new URL(href);
            return ['http:', 'https:'].includes(url.protocol) && !!url.hostname;
        } catch (error) {
            return false;
        }
    }

    function updateOpenButtonState() {
        openButton.disabled = !isValidUrl(websiteInput.value);
    }

    form.querySelectorAll('input, select, textarea').forEach((element) => {
        element.addEventListener('input', function () {
            isDirty = true;
            updateOpenButtonState();
        });

        element.addEventListener('change', function () {
            isDirty = true;
            updateOpenButtonState();
        });
    });

openButton.addEventListener('click', function () {
    if (!isValidUrl(websiteInput.value)) {
        return;
    }

    const href = normaliseUrl(websiteInput.value);

    window.open(href, '_blank', 'noopener,noreferrer');
});

    updateOpenButtonState();
});
</script>
@endPushOnce
</x-app-layout>