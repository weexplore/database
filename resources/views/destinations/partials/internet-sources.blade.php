@php
    $sourceReturnTo = $sourceReturnTo ?? url()->full();

    $sourceFormAction = old('_source_form_action');
    $sourceFormId = old('_source_form_id');
@endphp

<div class="bg-white shadow-sm sm:rounded-lg"
     id="destination-internet-sources">

    <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-gray-900">
                Internet Sources
            </h3>

            <p class="mt-1 text-xs text-gray-500">
                Discover sources, capture notes and review proposed text.
            </p>
        </div>

        <button type="button"
                id="toggle-source-create"
                aria-controls="destination-source-create-form"
                aria-expanded="{{ $sourceFormAction === 'create' ? 'true' : 'false' }}"
                class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded hover:bg-green-700 text-xs">
            Add Source
        </button>
    </div>

    <div class="p-4 space-y-4">
        <div class="rounded-md border border-blue-100 bg-blue-50 px-3 py-2 text-xs text-blue-800">
            Internet search currently imports search-result snippets,
            not the full linked webpage.
            Destination notes are not changed by searching.
        </div>

        <div id="destination-search-feedback"
             role="status"
             aria-live="polite"
             class="hidden rounded-md border px-3 py-2 text-xs whitespace-pre-line">
        </div>

        @include('destinations.partials.source-create-form', [
            'destination' => $destination,
            'sourceReturnTo' => $sourceReturnTo,
            'sourceFormAction' => $sourceFormAction,
        ])

        <div id="destination-sources-panel" class="space-y-3">
            @include('destinations.partials.source-list', [
                'destination' => $destination,
                'sourceReturnTo' => $sourceReturnTo,
                'sourceFormAction' => $sourceFormAction,
                'sourceFormId' => $sourceFormId,
            ])
        </div>
    </div>
</div>