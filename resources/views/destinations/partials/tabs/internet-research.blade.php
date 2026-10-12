<div class="space-y-4">
    <div class="bg-white shadow-sm sm:rounded-lg px-6 py-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-lg font-medium text-gray-900">Internet Research</h3>
            <p class="mt-1 text-xs text-gray-500">Capture sources, compose Destination notes and review proposed Things to Do.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" data-research-section="sources" aria-pressed="true" class="px-3 py-2 bg-blue-600 text-white rounded text-sm">Sources</button>
            <button type="button" data-research-section="compose" aria-pressed="false" class="px-3 py-2 bg-gray-100 text-gray-800 rounded text-sm">Compose Fields</button>
            <button type="button" data-research-section="candidates" aria-pressed="false" class="px-3 py-2 bg-gray-100 text-gray-800 rounded text-sm">Item Candidates</button>
        </div>
    </div>
    <div data-research-panel="sources" class="space-y-4">
        <div class="flex justify-end">
            <button type="button" id="suggest-from-web-button" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">Find Internet Sources</button>
        </div>
        @include('destinations.partials.internet-sources', [
            'destination' => $destination,
            'sourceReturnTo' => $tabReturnUrls['internet-research'],
        ])
    </div>
    <div data-research-panel="compose" hidden>
        @include('destinations.partials.compose-fields')
    </div>
    <div data-research-panel="candidates" hidden>
        @include('destinations.partials.item-candidates')
    </div>
</div>
