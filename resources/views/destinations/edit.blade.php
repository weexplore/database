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
                <div class="xl:col-span-2">
                    <form method="POST"
                          action="{{ route('destinations.update', $destination) }}"
                          id="destination-edit-form"
                          class="space-y-6">
                        @csrf
                        @method('PUT')

                        <input type="hidden" name="return_to" value="{{ $returnTo }}">

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
                                    @php
                                        $revisitOptions = [
                                            'very_likely' => 'Very Likely',
                                            'likely' => 'Likely',
                                            'neutral' => 'Neutral',
                                            'unlikely' => 'Unlikely',
                                            'very_unlikely' => 'Very Unlikely',
                                        ];
                                    @endphp

                                    <select name="revisitinterestlevel"
                                            id="revisitinterestlevel"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        <option value="">Select revisit interest</option>
                                        @foreach($revisitOptions as $value => $label)
                                            <option value="{{ $value }}"
                                                @selected(old('revisitinterestlevel', $destination->revisitinterestlevel) === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
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

                        <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Editorial Overview</h3>
                            </div>

                            <x-forms.markdown-field
                                name="overview"
                                id="overview"
                                label="Overview"
                                :value="old('overview', $destination->overview)"
                                rows="6"
                                minRows="4"
                                maxRows="16"
                                placeholder="Write an editorial overview for this destination..."
                                help="Markdown supported. Use Show preview to view formatted content."
                            />

                            <x-forms.markdown-field
                                name="travelnotes"
                                id="travelnotes"
                                label="Travel Notes"
                                :value="old('travelnotes', $destination->travelnotes)"
                                rows="6"
                                minRows="4"
                                maxRows="16"
                                placeholder="Add travel notes, route thoughts, timing, or practical commentary..."
                                help="Markdown supported. Use Show preview to view formatted content."
                            />
                        </div>

                        <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Suitability and Access</h3>
                            </div>

                            <x-forms.markdown-field
                                name="suitability"
                                id="suitability"
                                label="Suitability"
                                :value="old('suitability', $destination->suitability)"
                                rows="5"
                                minRows="4"
                                maxRows="14"
                                placeholder="Describe who this destination suits..."
                                help="Markdown supported. Use Show preview to view formatted content."
                            />

                            <x-forms.markdown-field
                                name="accessnotes"
                                id="accessnotes"
                                label="Access Notes"
                                :value="old('accessnotes', $destination->accessnotes)"
                                rows="5"
                                minRows="4"
                                maxRows="14"
                                placeholder="Record access conditions, roads, gates, turning areas, or cautions..."
                                help="Markdown supported. Use Show preview to view formatted content."
                            />
                        </div>

                        <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Personal Commentary</h3>
                            </div>

                            <x-forms.markdown-field
                                name="personalcommentary"
                                id="personalcommentary"
                                label="Personal Commentary"
                                :value="old('personalcommentary', $destination->personalcommentary)"
                                rows="7"
                                minRows="5"
                                maxRows="18"
                                placeholder="Add personal reflections, impressions, and revisit thoughts..."
                                help="Markdown supported. Use Show preview to view formatted content."
                            />
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

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-red-200">
                        <div class="px-4 py-3 border-b border-red-200 bg-red-50">
                            <h3 class="text-sm font-semibold text-red-800">Delete Destination</h3>
                            <p class="mt-1 text-xs text-red-700">
                                This permanently removes this destination record.
                            </p>
                        </div>

                        <div class="p-4">
                            <form method="POST"
                                  action="{{ route('destinations.destroy', $destination) }}"
                                  onsubmit="return confirm('Delete this destination? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="return_to" value="{{ $returnTo }}">

                                <div class="flex items-center justify-end">
                                    <button type="submit"
                                            class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md text-xs font-semibold text-red-700 bg-white uppercase tracking-widest hover:bg-red-50">
                                        Delete Destination
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

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

                            <a href="{{ route('destinations.destination-items.create-from-destination', [
                                    'destination' => $destination,
                                    'return_to' => url()->full(),
                                ]) }}"
                               class="inline-flex items-center px-3 py-1.5 bg-green-50 text-green-700 rounded hover:bg-green-100 text-xs">
                                + Add Destination Item
                            </a>
                        </div>
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900">Attachments</h3>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ $destination->attachments->count() }} linked record{{ $destination->attachments->count() === 1 ? '' : 's' }}
                            </p>
                        </div>

                        <div class="p-4 space-y-4">
                            @if ($destination->attachments->isNotEmpty())
                                <div class="space-y-3">
                                    @foreach ($destination->attachments as $attachment)
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

                                            <div class="mt-2 flex flex-wrap gap-2">
                                                @if (Route::has('attachments.view'))
                                                    <a href="{{ route('attachments.view', $attachment) }}"
                                                       target="_blank"
                                                       rel="noopener noreferrer"
                                                       class="inline-flex items-center px-2.5 py-1.5 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-xs">
                                                        View
                                                    </a>
                                                @endif

                                                @if (Route::has('attachments.download'))
                                                    <a href="{{ route('attachments.download', $attachment) }}"
                                                       class="inline-flex items-center px-2.5 py-1.5 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-xs">
                                                        Download
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
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500">No attachments are currently linked to this destination.</p>
                            @endif

                            @if (Route::has('attachments.index'))
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('attachments.index', [
                                        'linkedtype' => 'destination',
                                        'linkedid' => $destination->id,
                                        'return_to' => url()->full(),
                                    ]) }}"
                                       class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-sm">
                                        View Attachments
                                    </a>

                                    <a href="{{ route('attachments.index', [
                                        'linkedtype' => 'destination',
                                        'linkedid' => $destination->id,
                                        'show_create' => 1,
                                        'return_to' => url()->full(),
                                    ]) }}"
                                       class="inline-flex items-center px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                        Add Attachment
                                    </a>
                                </div>
                            @endif
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

                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between gap-3">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900">Internet Sources</h3>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Curated links and summaries for this destination
                                    </p>
                                </div>

                                <button type="button"
                                        id="toggle-source-create"
                                        class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded hover:bg-green-700 text-xs">
                                    Add Source
                                </button>
                            </div>

                            <div class="p-4 space-y-4">
                                <form method="POST"
                                    action="{{ route('destinations.sources.store', $destination) }}"
                                    id="destination-source-create-form"
                                    class="hidden border border-gray-200 rounded-lg p-4 space-y-3 bg-gray-50">
                                    @csrf
                                    <input type="hidden" name="return_to" value="{{ url()->full() }}">

                                    <div>
                                        <label for="new_source_title" class="block text-xs font-medium text-gray-700 mb-1">Title</label>
                                        <input type="text" name="sourcetitle" id="new_source_title"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm" required>
                                    </div>

                                    <div>
                                        <label for="new_source_publisher" class="block text-xs font-medium text-gray-700 mb-1">Publisher</label>
                                        <input type="text" name="sourcepublisher" id="new_source_publisher"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </div>

                                    <div>
                                        <label for="new_source_url" class="block text-xs font-medium text-gray-700 mb-1">URL</label>
                                        <input type="url" name="sourceurl" id="new_source_url"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div>
                                            <label for="new_source_retrievedon" class="block text-xs font-medium text-gray-700 mb-1">Retrieved on</label>
                                            <input type="date" name="retrievedon" id="new_source_retrievedon"
                                                value="{{ now()->toDateString() }}"
                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        </div>

                                        <div>
                                            <label for="new_source_importstatus" class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                                            <select name="importstatus" id="new_source_importstatus"
                                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                <option value="pendingreview">Pending review</option>
                                                <option value="approved">Approved</option>
                                                <option value="rejected">Rejected</option>
                                                <option value="archived">Archived</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <label for="new_source_summary" class="block text-xs font-medium text-gray-700 mb-1">Summary</label>
                                        <textarea name="summary" id="new_source_summary" rows="3"
                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
                                    </div>

                                    <div>
                                        <label for="new_source_notes" class="block text-xs font-medium text-gray-700 mb-1">Notes</label>
                                        <textarea name="notes" id="new_source_notes" rows="2"
                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
                                    </div>

                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button"
                                                id="cancel-source-create"
                                                class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-xs">
                                            Cancel
                                        </button>
                                        <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded hover:bg-green-700 text-xs">
                                            Save Source
                                        </button>
                                    </div>
                                </form>

                                <div id="destination-sources-panel" class="space-y-3">
                                    @forelse($destination->sources as $source)
                                        <div class="border border-gray-200 rounded-md px-3 py-3">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
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
                                                            rel="noopener noreferrer"
                                                            class="text-xs text-blue-600 hover:underline">
                                                                Open source
                                                            </a>
                                                        </div>
                                                    @endif

                                                    @if($source->summary)
                                                        <div class="mt-2 text-xs text-gray-600 whitespace-pre-line">
                                                            {{ $source->summary }}
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="flex items-center gap-2 shrink-0">
                                                    <button type="button"
                                                            class="toggle-source-edit inline-flex items-center px-2.5 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs"
                                                            data-target="source-edit-{{ $source->id }}">
                                                        Edit
                                                    </button>

                                                    <form method="POST"
                                                        action="{{ route('destinations.sources.destroy', [$destination, $source]) }}"
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
                                                action="{{ route('destinations.sources.update', [$destination, $source]) }}"
                                                id="source-edit-{{ $source->id }}"
                                                class="hidden mt-3 border-t border-gray-200 pt-3 space-y-3">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="return_to" value="{{ url()->full() }}">

                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Title</label>
                                                    <input type="text" name="sourcetitle"
                                                        value="{{ old("source_edit_{$source->id}.sourcetitle", $source->sourcetitle) }}"
                                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                        required>
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Publisher</label>
                                                    <input type="text" name="sourcepublisher"
                                                        value="{{ old("source_edit_{$source->id}.sourcepublisher", $source->sourcepublisher) }}"
                                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">URL</label>
                                                    <input type="url" name="sourceurl"
                                                        value="{{ old("source_edit_{$source->id}.sourceurl", $source->sourceurl) }}"
                                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                </div>

                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Retrieved on</label>
                                                        <input type="date" name="retrievedon"
                                                            value="{{ old("source_edit_{$source->id}.retrievedon", optional($source->retrievedon)->format('Y-m-d')) }}"
                                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                    </div>

                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                                                        <select name="importstatus" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                            @foreach(['pendingreview' => 'Pending review', 'approved' => 'Approved', 'rejected' => 'Rejected', 'archived' => 'Archived'] as $value => $label)
                                                                <option value="{{ $value }}" @selected($source->importstatus === $value)>
                                                                    {{ $label }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Summary</label>
                                                    <textarea name="summary" rows="3"
                                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ $source->summary }}</textarea>
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Notes</label>
                                                    <textarea name="notes" rows="2"
                                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ $source->notes }}</textarea>
                                                </div>

                                                <div class="flex items-center justify-end gap-2">
                                                    <button type="button"
                                                            class="toggle-source-edit inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-xs"
                                                            data-target="source-edit-{{ $source->id }}">
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
                                        <p class="text-xs text-gray-500">
                                            No internet sources recorded yet. Use “Suggest from web” or Add Source.
                                        </p>
                                    @endforelse
                                </div>
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

    @include('partials.forms.markdown-field-scripts')

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

        const toggleCreateButton = document.getElementById('toggle-source-create');
        const createForm = document.getElementById('destination-source-create-form');
        const cancelCreateButton = document.getElementById('cancel-source-create');

        if (toggleCreateButton && createForm) {
            toggleCreateButton.addEventListener('click', function () {
                createForm.classList.toggle('hidden');
            });
        }

        if (cancelCreateButton && createForm) {
            cancelCreateButton.addEventListener('click', function () {
                createForm.classList.add('hidden');
            });
        }

        document.querySelectorAll('.toggle-source-edit').forEach((button) => {
            button.addEventListener('click', function () {
                const targetId = button.getAttribute('data-target');
                const panel = document.getElementById(targetId);
                if (panel) {
                    panel.classList.toggle('hidden');
                }
            });
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
                        document.getElementById('overview').dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    if (data.fields.travelnotes && !document.getElementById('travelnotes').value) {
                        document.getElementById('travelnotes').value = data.fields.travelnotes;
                        document.getElementById('travelnotes').dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    if (data.fields.bestseason && !document.getElementById('bestseason').value) {
                        document.getElementById('bestseason').value = data.fields.bestseason;
                    }
                    if (data.fields.suitability && !document.getElementById('suitability').value) {
                        document.getElementById('suitability').value = data.fields.suitability;
                        document.getElementById('suitability').dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    if (data.fields.accessnotes && !document.getElementById('accessnotes').value) {
                        document.getElementById('accessnotes').value = data.fields.accessnotes;
                        document.getElementById('accessnotes').dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    if (data.fields.personalcommentary && !document.getElementById('personalcommentary').value) {
                        document.getElementById('personalcommentary').value = data.fields.personalcommentary;
                        document.getElementById('personalcommentary').dispatchEvent(new Event('input', { bubbles: true }));
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