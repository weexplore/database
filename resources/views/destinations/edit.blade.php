<x-app-layout>
    @php
        $returnTo = request('return_to', route('destinations.index', request()->only([
            'placeid',
            'destinationtype',
            'featured',
            'search',
            'page',
        ])));
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Destination
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $destination->destinationname }}
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
                          action="{{ route('destinations.update', $destination) }}"
                          id="destination-edit-form"
                          class="space-y-6">
                        @csrf
                        @method('PUT')

                        <input type="hidden" name="return_to" value="{{ $returnTo }}">

                        {{-- Core Details --}}
                        <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Core Details</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label for="placeid" class="block text-sm font-medium text-gray-700 mb-1">
                                        Linked Place
                                    </label>
                                    <select name="placeid"
                                            id="placeid"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        <option value="">None</option>
                                        @foreach($places as $place)
                                            <option value="{{ $place->id }}"
                                                @selected((string) old('placeid', $destination->placeid) === (string) $place->id)>
                                                {{ $place->placename }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label for="destinationname" class="block text-sm font-medium text-gray-700 mb-1">
                                        Destination Name
                                    </label>
                                    <input type="text"
                                           name="destinationname"
                                           id="destinationname"
                                           value="{{ old('destinationname', $destination->destinationname) }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                           required>
                                </div>

                                <div>
                                    <label for="destinationtype" class="block text-sm font-medium text-gray-700 mb-1">
                                        Destination Type
                                    </label>
                                    <select name="destinationtype"
                                            id="destinationtype"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        @foreach($typeOptions as $type)
                                            <option value="{{ $type }}"
                                                @selected(old('destinationtype', $destination->destinationtype) === $type)>
                                                {{ ucfirst($type) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="bestseason" class="block text-sm font-medium text-gray-700 mb-1">
                                        Best Season
                                    </label>
                                    <input type="text"
                                           name="bestseason"
                                           id="bestseason"
                                           value="{{ old('bestseason', $destination->bestseason) }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>

                                <div>
                                    <label for="revisitinterestlevel" class="block text-sm font-medium text-gray-700 mb-1">
                                        Revisit Interest Level
                                    </label>
                                    <input type="number"
                                           name="revisitinterestlevel"
                                           id="revisitinterestlevel"
                                           value="{{ old('revisitinterestlevel', $destination->revisitinterestlevel) }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                           min="0"
                                           max="10">
                                </div>

                                <div class="flex items-end">
                                    <label class="inline-flex items-center gap-2">
                                        <input type="hidden" name="isfeatured" value="0">
                                        <input type="checkbox"
                                               name="isfeatured"
                                               value="1"
                                               class="rounded border-gray-300 text-blue-600 shadow-sm"
                                               @checked(old('isfeatured', $destination->isfeatured))>
                                        <span class="text-sm text-gray-700">Featured destination</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Editorial Overview --}}
                        <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Editorial Overview</h3>
                            </div>

                            <div>
                                <label for="overview" class="block text-sm font-medium text-gray-700 mb-1">
                                    Overview
                                </label>
                                <textarea name="overview"
                                          id="overview"
                                          rows="5"
                                          class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('overview', $destination->overview) }}</textarea>
                            </div>

                            <div>
                                <label for="travelnotes" class="block text-sm font-medium text-gray-700 mb-1">
                                    Travel Notes
                                </label>
                                <textarea name="travelnotes"
                                          id="travelnotes"
                                          rows="5"
                                          class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('travelnotes', $destination->travelnotes) }}</textarea>
                            </div>
                        </div>

                        {{-- Suitability and Access --}}
                        <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Suitability and Access</h3>
                            </div>

                            <div>
                                <label for="suitability" class="block text-sm font-medium text-gray-700 mb-1">
                                    Suitability
                                </label>
                                <textarea name="suitability"
                                          id="suitability"
                                          rows="4"
                                          class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('suitability', $destination->suitability) }}</textarea>
                            </div>

                            <div>
                                <label for="accessnotes" class="block text-sm font-medium text-gray-700 mb-1">
                                    Access Notes
                                </label>
                                <textarea name="accessnotes"
                                          id="accessnotes"
                                          rows="4"
                                          class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('accessnotes', $destination->accessnotes) }}</textarea>
                            </div>
                        </div>

                        {{-- Personal Commentary --}}
                        <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Personal Commentary</h3>
                            </div>

                            <div>
                                <label for="personalcommentary" class="block text-sm font-medium text-gray-700 mb-1">
                                    Personal Commentary
                                </label>
                                <textarea name="personalcommentary"
                                          id="personalcommentary"
                                          rows="6"
                                          class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('personalcommentary', $destination->personalcommentary) }}</textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <button type="button"
                                    id="suggest-from-web-button"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                Suggest from web
                            </button>

                            <a href="{{ $returnTo }}"
                               class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                                Cancel
                            </a>

                            <button type="submit"
                                    class="inline-flex items-center px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                Save Destination
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Sidebar --}}
                <div class="xl:col-span-1 space-y-6">
                    @if ($destination->place)
                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="px-4 py-3 border-b border-gray-200 flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900">
                                        Linked Place
                                    </h3>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Reusable location record
                                    </p>
                                </div>

                                <a
                                    href="{{ route('places.edit', [
                                        'place' => $destination->place,
                                        'return_to' => url()->full(),
                                    ]) }}"
                                    class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-xs"
                                >
                                    Edit Place
                                </a>
                            </div>

                            <div class="p-4">
                                <dl class="space-y-3 text-sm">
                                    <div>
                                        <dt class="text-gray-500">Place</dt>
                                        <dd class="text-gray-900">{{ $destination->place->placename ?: '—' }}</dd>
                                    </div>

                                    <div>
                                        <dt class="text-gray-500">Type</dt>
                                        <dd class="text-gray-900">{{ $destination->place->placetype ?: '—' }}</dd>
                                    </div>

                                    <div>
                                        <dt class="text-gray-500">Locality</dt>
                                        <dd class="text-gray-900">{{ $destination->place->locality ?: '—' }}</dd>
                                    </div>

                                    <div>
                                        <dt class="text-gray-500">Postcode</dt>
                                        <dd class="text-gray-900">{{ $destination->place->postcode ?: '—' }}</dd>
                                    </div>

                                    <div>
                                        <dt class="text-gray-500">Access notes</dt>
                                        <dd class="text-gray-900 whitespace-pre-line">{{ $destination->place->accessnotes ?: '—' }}</dd>
                                    </div>

                                    <div>
                                        <dt class="text-gray-500">General notes</dt>
                                        <dd class="text-gray-900 whitespace-pre-line">{{ $destination->place->generalnotes ?: '—' }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    @endif

                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900">
                                Destination Items
                            </h3>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ $destination->items->count() }} linked record{{ $destination->items->count() === 1 ? '' : 's' }}
                            </p>
                        </div>

                        <div class="p-4">
                            @if ($destination->items->isNotEmpty())
                                <ul class="space-y-3">
                                    @foreach ($destination->items as $item)
                                        <li>
                                            <a
                                                href="{{ route('destination-items.edit', [
                                                    'destinationItem' => $item,
                                                    'return_to' => url()->full(),
                                                ]) }}"
                                                class="block border border-gray-200 rounded-md px-3 py-2 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            >
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $item->itemname }}
                                                </div>
                                                <div class="mt-1 text-xs text-gray-500">
                                                    Type:
                                                    {{ $item->itemTypes->pluck('typename')->join(', ') ?: '—' }}
                                                </div>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-gray-500">
                                    No destination items are currently linked to this destination.
                                </p>
                            @endif

<a href="{{ route('destination-items.create-from-destination', [
        'destination' => $destination,
        'return_to' => url()->full(),
    ]) }}"
    class="inline-flex items-center px-3 py-1.5 bg-green-50 text-green-700 rounded hover:bg-green-100 text-xs">
    + Add Destination Item
</a>
                        </div>
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">
                                    Internet Sources
                                </h3>
                                <p class="mt-1 text-xs text-gray-500">
                                    Curated links and summaries for this destination
                                </p>
                            </div>
                        </div>

                        <div class="p-4">
                            <div id="destination-sources-panel">
                                @forelse($destination->sources as $source)
                                    <div class="border border-gray-200 rounded-md px-3 py-2 mb-2">
                                        <div class="text-xs font-semibold text-gray-900">
                                            {{ $source->sourcetitle ?: 'Source' }}
                                        </div>
                                        <div class="mt-1 text-xs text-gray-500">
                                            {{ $source->sourcepublisher ?: '—' }}
                                            @if($source->retrievedon)
                                                • Retrieved {{ \Illuminate\Support\Carbon::parse($source->retrievedon)->format('d M Y') }}
                                            @endif
                                            @if($source->importstatus)
                                                • Status: {{ $source->importstatus }}
                                            @endif
                                        </div>
                                        @if($source->sourceurl)
                                            <div class="mt-1">
                                                <a href="{{ $source->sourceurl }}"
                                                   target="_blank"
                                                   rel="noopener"
                                                   class="text-xs text-blue-600 hover:underline">
                                                    Open source
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-xs text-gray-500">
                                        No internet sources recorded yet. Use “Suggest from web” to add one.
                                    </p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Record summary</h3>
                        <dl class="mt-3 space-y-2 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Destination ID</dt>
                                <dd class="text-gray-900">{{ $destination->id }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Type</dt>
                                <dd class="text-gray-900">{{ ucfirst($destination->destinationtype) }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Linked Place</dt>
                                <dd class="text-gray-900">{{ $destination->place?->placename ?: '—' }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Created</dt>
                                <dd class="text-gray-900">{{ optional($destination->createdat)->format('d M Y') ?: '—' }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Updated</dt>
                                <dd class="text-gray-900">{{ optional($destination->updatedat)->format('d M Y') ?: '—' }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">Featured</dt>
                                <dd class="text-gray-900">{{ $destination->isfeatured ? 'Yes' : 'No' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('destination-edit-form');
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

        const suggestButton = document.getElementById('suggest-from-web-button');
        if (!suggestButton) return;

        suggestButton.addEventListener('click', function () {
            suggestButton.disabled = true;
            suggestButton.textContent = 'Fetching suggestions...';

            fetch("{{ route('destinations.suggest-from-web', $destination) }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({}),
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to fetch suggestions');
                }
                return response.json();
            })
            .then(data => {
                if (data.fields) {
                    if (data.fields.overview && !document.getElementById('overview').value) {
                        document.getElementById('overview').value = data.fields.overview;
                    }
                    if (data.fields.travelnotes && !document.getElementById('travelnotes').value) {
                        document.getElementById('travelnotes').value = data.fields.travelnotes;
                    }
                    if (data.fields.bestseason && !document.getElementById('bestseason').value) {
                        document.getElementById('bestseason').value = data.fields.bestseason;
                    }
                    if (data.fields.suitability && !document.getElementById('suitability').value) {
                        document.getElementById('suitability').value = data.fields.suitability;
                    }
                    if (data.fields.accessnotes && !document.getElementById('accessnotes').value) {
                        document.getElementById('accessnotes').value = data.fields.accessnotes;
                    }

                    isDirty = true;
                }

                if (data.sources && data.sources.length) {
                    const panel = document.getElementById('destination-sources-panel');
                    if (panel) {
                        panel.innerHTML = '';
                        data.sources.forEach(source => {
                            const item = document.createElement('div');
                            item.className = 'border border-gray-200 rounded-md p-2 mb-2 text-xs';
                            item.innerHTML = `
                                <div class="font-semibold text-gray-900">${source.sourcetitle || 'Source'}</div>
                                <div class="text-gray-500 mt-1">
                                    ${source.sourcepublisher || ''}
                                    ${source.retrievedon ? ' • ' + source.retrievedon : ''}
                                </div>
                                ${source.sourceurl ? `<div class="mt-1"><a href="${source.sourceurl}" target="_blank" rel="noopener" class="text-blue-600 hover:underline">Open</a></div>` : ''}
                            `;
                            panel.appendChild(item);
                        });
                    }
                }
            })
            .catch(() => {
                alert('Could not fetch suggestions right now.');
            })
            .finally(() => {
                suggestButton.disabled = false;
                suggestButton.textContent = 'Suggest from web';
            });
        });
    });
    </script>
</x-app-layout>