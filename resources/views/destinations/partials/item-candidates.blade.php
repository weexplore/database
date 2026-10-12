<div id="destination-item-candidates" class="bg-white shadow-sm sm:rounded-lg"
     data-index-url="{{ route('destinations.item-candidates.index', $destination) }}"
     data-store-url="{{ route('destinations.item-candidates.store', $destination) }}"
     data-update-url="{{ route('destinations.item-candidates.update', ['destination' => $destination, 'candidate' => 0]) }}"
     data-approve-url="{{ route('destinations.item-candidates.approve', ['destination' => $destination, 'candidate' => 0]) }}"
     data-reject-url="{{ route('destinations.item-candidates.reject', ['destination' => $destination, 'candidate' => 0]) }}"
     data-delete-url="{{ route('destinations.item-candidates.destroy', ['destination' => $destination, 'candidate' => 0]) }}"
     data-create-url="{{ route('destinations.item-candidates.create-item', ['destination' => $destination, 'candidate' => 0]) }}"
     data-link-url="{{ route('destinations.item-candidates.link-item', ['destination' => $destination, 'candidate' => 0]) }}">
    <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div><h3 class="text-lg font-medium text-gray-900">Destination Item Candidates</h3>
            <p class="mt-1 text-xs text-gray-500">Review Things to Do from your sources, then create an item or link an existing one.</p></div>
        <div class="flex gap-2">
            <button type="button" data-candidate-action="refresh" class="px-3 py-2 bg-gray-100 text-gray-800 rounded text-sm">Refresh</button>
            <button type="button" data-candidate-action="toggle-create" class="px-3 py-2 bg-green-600 text-white rounded text-sm">Add Candidate</button>
        </div>
    </div>
    <div class="p-6 space-y-4">
        <div data-candidate-feedback role="status" aria-live="polite" class="hidden rounded-md border px-3 py-2 text-sm whitespace-pre-line"></div>
        <form data-candidate-create-form class="hidden rounded-md border border-gray-200 bg-gray-50 p-4 space-y-3">
            <label class="block text-xs font-medium text-gray-700">Source
                <select name="destinationsourceid" required class="mt-1 block w-full rounded-md border-gray-300 text-sm"><option value="">Select a source</option></select>
            </label>
            @include('destinations.partials.item-candidate-fields')
            <div class="flex justify-end gap-2">
                <button type="button" data-candidate-action="cancel-create" class="px-3 py-2 bg-gray-200 text-gray-800 rounded text-sm">Cancel</button>
                <button type="submit" class="px-3 py-2 bg-green-600 text-white rounded text-sm">Save Candidate</button>
            </div>
        </form>
        <div data-candidate-list class="space-y-3"></div>
        <p data-candidate-empty class="text-sm text-gray-500">Loading candidates...</p>
    </div>
    <template data-candidate-template>
        <details data-candidate-card class="rounded-md border border-gray-200 bg-gray-50">
            <summary class="cursor-pointer p-4">
                <span data-candidate-name class="text-sm font-semibold text-gray-900"></span>
                <span data-candidate-status class="ml-2 rounded bg-white px-2 py-0.5 text-xs text-gray-700"></span>
                <span data-candidate-source class="mt-1 block text-xs text-gray-500"></span>
            </summary>
            <div class="px-4 pb-4 space-y-3">
                <div data-candidate-description class="text-sm text-gray-700 whitespace-pre-wrap"></div>
                <a data-candidate-item-link class="hidden text-sm text-blue-600 hover:underline">Open Destination Item</a>
                <p data-candidate-resolved-note class="hidden text-xs text-gray-500"></p>
                <div data-candidate-controls class="flex flex-wrap gap-2">
                    @foreach (['edit' => 'Edit', 'approve' => 'Approve', 'reject' => 'Reject', 'delete' => 'Delete'] as $action => $label)
                        <button type="button" data-candidate-action="{{ $action }}" class="px-3 py-1.5 bg-white border border-gray-300 text-gray-800 rounded text-xs">{{ $label }}</button>
                    @endforeach
                </div>
                <form data-candidate-edit-form class="hidden border-t border-gray-200 pt-3 space-y-3">
                    @include('destinations.partials.item-candidate-fields')
                    <div class="flex justify-end gap-2">
                        <button type="button" data-candidate-action="cancel-edit" class="px-3 py-2 bg-gray-200 text-gray-800 rounded text-sm">Cancel</button>
                        <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded text-sm">Save Candidate</button>
                    </div>
                </form>
                <div data-candidate-resolve-controls class="hidden border-t border-gray-200 pt-3 grid grid-cols-1 xl:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="block text-xs font-medium text-gray-700">Types for the new Destination Item
                            <select data-candidate-create-types multiple size="5" aria-label="Types for new Destination Item" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></select>
                        </label>
                        <p class="text-xs text-gray-500">Suggested type is preselected. Use Ctrl/Cmd to select additional types.</p>
                        <button type="button" data-candidate-action="create-item" class="px-3 py-2 bg-green-600 text-white rounded text-sm">Create Destination Item</button>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-medium text-gray-700">Existing item in this Destination
                            <select data-candidate-link-item class="mt-1 block w-full rounded-md border-gray-300 text-sm"><option value="">Select an existing item</option></select>
                        </label>
                        <p class="text-xs text-gray-500">Linking records the association only. Existing item text and types are not changed.</p>
                        <button type="button" data-candidate-action="link-item" class="px-3 py-2 bg-blue-600 text-white rounded text-sm">Link Existing Item</button>
                    </div>
                </div>
            </div>
        </details>
    </template>
</div>
