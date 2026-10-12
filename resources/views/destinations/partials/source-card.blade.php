@php
    $statusLabel = \App\Models\DestinationSource::importStatusOptions()[
        $source->importstatus
    ] ?? $source->importstatus;

    $isEditingSource =
        $sourceFormAction === 'edit'
        && (string) $sourceFormId === (string) $source->id;

    $sourceLinkScheme = strtolower(
        (string) parse_url(
            $source->sourceurl ?? '',
            PHP_URL_SCHEME
        )
    );

    $hasSourceLink = in_array(
        $sourceLinkScheme,
        ['http', 'https'],
        true
    );

    $extractCount = $source->extracts->count();
    $hasCapturedContent = filled($source->rawcontent);
@endphp

<div class="border border-gray-200 rounded-lg bg-white"
     data-source-card
     data-source-id="{{ $source->id }}">

    {{-- Compact source header --}}
    <div class="px-4 py-3">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <h4 class="text-sm font-semibold text-gray-900 break-words">
                    {{ $source->sourcetitle ?: 'Source' }}
                </h4>

                <div class="mt-1 text-xs text-gray-500">
                    {{ $source->sourcepublisher ?: 'Publisher not specified' }}

                    @if ($source->retrievedon)
                        · Retrieved {{ $source->retrievedon->format('d M Y') }}
                    @endif

                    @if ($statusLabel)
                        · {{ $statusLabel }}
                    @endif
                </div>

                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                    @if ($hasSourceLink)
                        <a href="{{ $source->sourceurl }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="text-blue-600 hover:underline">
                            Open source
                        </a>
                    @endif

                    <span class="text-gray-500">
                        {{ $hasCapturedContent ? 'Page content captured' : 'No page content captured' }}
                    </span>

                    <span class="inline-flex items-center rounded bg-gray-100 px-2 py-0.5 text-gray-700">
                        <span data-source-extract-count>{{ $extractCount }}</span>
                        <span class="ml-1">extracts</span>
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button type="button"
                        data-source-toggle
                        data-target="source-edit-{{ $source->id }}"
                        aria-controls="source-edit-{{ $source->id }}"
                        aria-expanded="{{ $isEditingSource ? 'true' : 'false' }}"
                        class="inline-flex items-center px-2.5 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs">
                    Edit
                </button>

                <form method="POST"
                      action="{{ route('destinations.sources.destroy', [$destination, $source]) }}"
                      data-source-delete-form>

                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="inline-flex items-center px-2.5 py-1.5 bg-red-600 text-white rounded hover:bg-red-700 text-xs">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Native disclosure: collapsing does not discard form values --}}
    <details data-source-workspace
             class="group border-t border-gray-200"
             @if ($isEditingSource) open @endif>

        <summary class="cursor-pointer px-4 py-2.5 bg-gray-50 text-xs font-medium text-blue-700 hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-blue-500">
            <span class="group-open:hidden">
                Show source details and research
            </span>

            <span class="hidden group-open:inline">
                Hide source details and research
            </span>
        </summary>

        <div class="px-4 py-4 space-y-3">
            @if (filled($source->importedsummary))
                <details class="rounded-md border border-gray-200 bg-gray-50">
                    <summary class="cursor-pointer px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-100">
                        Imported Summary
                        <span class="ml-2 font-normal text-gray-500">
                            Click to expand or collapse
                        </span>
                    </summary>

                    <div class="border-t border-gray-200 px-3 py-3">
                        <div class="max-h-80 overflow-y-auto text-xs text-gray-600 whitespace-pre-line break-words">{{ $source->importedsummary }}</div>
                    </div>
                </details>
            @endif

            @if (filled($source->importednotes))
                <div>
                    <p class="text-xs font-medium text-gray-700">
                        Imported notes
                    </p>

                    <div class="mt-1 text-xs text-gray-600 whitespace-pre-line">{{ $source->importednotes }}</div>
                </div>
            @endif

            @include('destinations.partials.source-edit-form', [
                'destination' => $destination,
                'source' => $source,
                'sourceReturnTo' => $sourceReturnTo,
                'isEditingSource' => $isEditingSource,
            ])

            @include('destinations.partials.source-captured-content', [
                'source' => $source,
            ])

            @include('destinations.partials.source-extracts', [
                'destination' => $destination,
                'source' => $source,
            ])
        </div>
    </details>
</div>