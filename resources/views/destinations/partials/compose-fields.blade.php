@php
    $compositionSavedValues = [
        'overview' => (string) ($destination->overview ?? ''),
        'travelnotes' => (string) ($destination->travelnotes ?? ''),
        'suitability' => (string) ($destination->suitability ?? ''),
        'accessnotes' => (string) ($destination->accessnotes ?? ''),
    ];
@endphp
<div id="destination-composition"
     class="bg-white shadow-sm sm:rounded-lg"
     data-saved-values="{{ json_encode($compositionSavedValues) }}"
     data-data-url="{{ route('destinations.composition.data', $destination) }}"
     data-save-url="{{ route('destinations.composition.save', $destination) }}">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Compose Destination Fields</h3>
        <p class="mt-1 text-xs text-gray-500">Combine your current text with approved extracts from multiple sources. Review the draft before saving.</p>
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach (['overview' => 'Overview', 'travelnotes' => 'Travel Notes', 'suitability' => 'Suitability', 'access' => 'Access Notes'] as $field => $label)
                <button type="button" data-compose-field="{{ $field }}"
                        class="px-3 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-sm">{{ $label }}</button>
            @endforeach
        </div>
    </div>
    <div class="p-6 space-y-4">
        <div data-compose-feedback role="status" aria-live="polite" class="hidden rounded-md border px-3 py-2 text-sm whitespace-pre-line"></div>
        <p data-compose-placeholder class="text-sm text-gray-500">Choose a field to begin. No content is changed until you save.</p>
        <form data-compose-form class="hidden space-y-4">
            <h4 data-compose-heading class="text-base font-semibold text-gray-900"></h4>
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                <div class="space-y-4">
                    <details class="rounded-md border border-gray-200 p-3">
                        <summary class="cursor-pointer text-sm font-medium text-gray-700">Current editor text, including unsaved edits</summary>
                        <textarea data-compose-current readonly rows="7" aria-label="Current Destination field content"
                                  class="mt-3 w-full rounded-md border-gray-300 text-sm"></textarea>
                    </details>
                    <div>
                        <h5 class="text-sm font-medium text-gray-700">Approved, unapplied extracts</h5>
                        <p class="mt-1 text-xs text-gray-500">Sources marked rejected or archived are excluded. Only tick extracts that contribute to your final text.</p>
                        <div data-compose-extracts class="mt-3 space-y-3 max-h-[36rem] overflow-y-auto"></div>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" data-compose-build="keep" class="px-3 py-2 bg-blue-100 text-blue-800 rounded hover:bg-blue-200 text-xs">Keep Current + Add Selected</button>
                        <button type="button" data-compose-build="replace" class="px-3 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-xs">Start From Selected Only</button>
                    </div>
                    <label class="block text-sm font-medium text-gray-700">
                        Final composed text
                        <textarea data-compose-draft rows="20" required
                                  class="mt-2 w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
                    </label>
                    <p class="text-xs text-gray-500">Markdown supported. These buttons assemble paragraphs, not an automatic intelligent merge. Remove repetition and resolve conflicting information yourself.</p>
                </div>
            </div>
            <label class="flex items-start gap-2 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                <input type="checkbox" data-compose-confirm required class="mt-0.5 rounded border-gray-300">
                <span>I have reviewed the final text. Every selected extract contributes to it and may be marked applied.</span>
            </label>
            <div class="flex flex-wrap justify-end gap-2">
                <button type="button" data-compose-cancel class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">Close Draft</button>
                <button type="submit" data-compose-save class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">Save Composed Field</button>
            </div>
        </form>
    </div>
</div>
