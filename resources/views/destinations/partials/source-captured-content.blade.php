<div class="mt-4 border-t border-gray-200 pt-4"
     data-capture-panel
     data-capture-url="{{ route('destination-sources.capture-page', [
         'destinationSource' => $source->id,
     ]) }}"
     data-copy-summary-url="{{ route('destination-sources.copy-summary-to-content', [
         'destinationSource' => $source->id,
     ]) }}"
     data-has-content="{{ filled($source->rawcontent) ? '1' : '0' }}">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h4 class="text-xs font-semibold text-gray-900">
                Captured Page Content
            </h4>

            <p class="mt-1 text-xs text-gray-500">
                Fetched page text or manually supplied research text.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            @if (filled($source->importedsummary))
                <button type="button"
                        data-capture-action="copy-summary"
                        class="px-2.5 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-xs">
                    Use Imported Summary
                </button>
            @endif

            @if (filled($source->sourceurl))
                <button type="button"
                        data-capture-action="fetch"
                        class="px-2.5 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs">
                    {{ filled($source->rawcontent) ? 'Fetch Again' : 'Fetch Page Content' }}
                </button>
            @endif
        </div>
    </div>

    <div data-capture-feedback
         role="status"
         aria-live="polite"
         class="hidden mt-3 rounded-md border px-3 py-2 text-xs whitespace-pre-line">
    </div>

    <p data-capture-empty
       class="{{ filled($source->rawcontent) ? 'hidden' : '' }} mt-2 text-xs text-gray-500">
        No content saved here yet. Fetch a webpage or use the saved Imported Summary.
    </p>

    <details data-capture-details
             class="{{ filled($source->rawcontent) ? '' : 'hidden' }} mt-3">

        <summary class="cursor-pointer text-xs font-medium text-blue-700">
            Show captured text
        </summary>

        <div class="mt-3 space-y-3">
            <p class="text-xs text-gray-500">
                Select a relevant passage, then create a field extract
                or Destination Item candidate.
            </p>

            <textarea data-captured-text
                      readonly
                      rows="14"
                      spellcheck="false"
                      aria-label="Captured or manually supplied source content"
                      class="block w-full rounded-md border-gray-300 shadow-sm text-sm">{{ $source->rawcontent }}</textarea>

            <div class="flex flex-wrap gap-2">
                <button type="button"
                        data-capture-action="use-selection"
                        class="px-3 py-1.5 bg-green-600 text-white rounded hover:bg-green-700 text-xs">
                    Use Selected Text in New Extract
                </button>

                <button type="button"
                        data-capture-action="use-candidate"
                        class="px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs">
                    Use Selected Text in New Item Candidate
                </button>
            </div>
        </div>
    </details>
</div>