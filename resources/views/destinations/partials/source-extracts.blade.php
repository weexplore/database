@php
    $extractTargetOptions = collect(
        \App\Models\DestinationSourceExtract::targetFieldOptions()
    )->except('bestseason')->all();

    $extractStatusOptions =
        \App\Models\DestinationSourceExtract::statusOptions();

    $extractPayloads = $source->extracts->map(
        function ($extract) use (
            $extractStatusOptions
        ) {
            $targetOptions =
                \App\Models\DestinationSourceExtract::targetFieldOptions();

            return [
                'id' => $extract->id,
                'targetfield' => $extract->targetfield,
                'targetfield_label' =>
                    $targetOptions[$extract->targetfield]
                    ?? $extract->targetfield,
                'extractedtext' => $extract->extractedtext,
                'editedtext' => $extract->editedtext,
                'confidence' => $extract->confidence,
                'sortorder' => (int) ($extract->sortorder ?? 0),
                'extractstatus' => $extract->extractstatus,
                'extractstatus_label' =>
                    $extractStatusOptions[$extract->extractstatus]
                    ?? $extract->extractstatus,
                'appliedon_display' =>
                    $extract->appliedon?->format('d M Y H:i'),
            ];
        }
    )->values()->all();

    /*
     * Numeric placeholder replaced by JavaScript using an extract ID
     * returned by the server.
     */
    $extractRouteParameters = [
        'destinationSource' => $source->id,
        'destinationSourceExtract' => 0,
    ];
@endphp

<div class="mt-4 border-t border-gray-200 pt-4"
     data-extract-panel
     data-source-id="{{ $source->id }}"
     data-imported-summary="{{ $source->importedsummary ?? '' }}"
     data-extracts="{{ json_encode($extractPayloads) }}"
     data-store-url="{{ route('destination-sources.extracts.store', [
         'destinationSource' => $source->id,
     ]) }}"
     data-update-url="{{ route('destination-sources.extracts.update', $extractRouteParameters) }}"
     data-approve-url="{{ route('destination-sources.extracts.approve', $extractRouteParameters) }}"
     data-reject-url="{{ route('destination-sources.extracts.reject', $extractRouteParameters) }}"
     data-delete-url="{{ route('destination-sources.extracts.destroy', $extractRouteParameters) }}">

    <div class="flex items-center justify-between gap-3">
        <div>
            <h4 class="text-xs font-semibold text-gray-900">
                Field Extracts
            </h4>

            <p class="mt-1 text-xs text-gray-500">
                Prepare and review proposed destination text.
                Applying extracts will be connected in the next stage.
            </p>
        </div>

        <button type="button"
                data-extract-action="toggle-create"
                aria-expanded="false"
                class="inline-flex shrink-0 items-center px-2.5 py-1.5 bg-green-600 text-white rounded hover:bg-green-700 text-xs">
            Add Extract
        </button>
    </div>

    <div data-extract-feedback
         role="status"
         aria-live="polite"
         class="hidden mt-3 rounded-md border px-3 py-2 text-xs whitespace-pre-line">
    </div>

    <form data-extract-create-form
          class="hidden mt-3 rounded-md border border-gray-200 bg-gray-50 p-3 space-y-3">

        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">
                Target field

                <select name="targetfield"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"
                        required>
                    @foreach ($extractTargetOptions as $value => $label)
                        <option value="{{ $value }}">
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </label>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">
                Original extracted text

                <textarea name="extractedtext"
                          rows="4"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"
                          required></textarea>
            </label>

            <div class="mt-2 flex flex-wrap items-center gap-2">
                <button type="button"
                        data-extract-action="use-summary"
                        class="inline-flex items-center px-2.5 py-1.5 bg-blue-100 text-blue-800 rounded hover:bg-blue-200 text-xs">
                    Use Imported Summary
                </button>

                <span class="text-xs text-gray-500">
                    The imported summary is a search snippet, not full webpage content.
                </span>
            </div>

            <p class="mt-2 text-xs text-gray-500">
                Preserve captured wording here.
                Put your revised version in Reviewer-edited text.
            </p>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">
                Reviewer-edited text

                <textarea name="editedtext"
                          rows="4"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
            </label>

            <p class="mt-1 text-xs text-gray-500">
                Optional. When supplied, this takes precedence over the original text.
            </p>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <label class="block text-xs font-medium text-gray-700">
                Confidence

                <select name="confidence"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">Not rated</option>
                    @foreach (range(1, 5) as $rating)
                        <option value="{{ $rating }}">{{ $rating }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block text-xs font-medium text-gray-700">
                Sort order

                <input type="number"
                       name="sortorder"
                       value="0"
                       min="0"
                       step="1"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </label>
        </div>

        <div class="flex justify-end gap-2">
            <button type="button"
                    data-extract-action="cancel-create"
                    class="px-3 py-1.5 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-xs">
                Cancel
            </button>

            <button type="submit"
                    class="px-3 py-1.5 bg-green-600 text-white rounded hover:bg-green-700 text-xs">
                Save Extract
            </button>
        </div>
    </form>

    <div data-extract-list class="mt-3 space-y-3"></div>

    <p data-extract-empty class="mt-3 text-xs text-gray-500">
        No extracts prepared for this source.
    </p>

    <noscript>
        <p class="mt-3 text-xs text-amber-700">
            JavaScript is required to display and manage extracts.
        </p>
    </noscript>

    <template data-extract-card-template>
        <div data-extract-card
             class="rounded-md border border-gray-200 bg-gray-50 p-3 space-y-3">

            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <h5 data-extract-target
                        class="text-xs font-semibold text-gray-900"></h5>

                    <p data-extract-status
                       class="mt-1 inline-flex rounded px-2 py-0.5 text-xs"></p>
                </div>

                <div data-extract-confidence
                     class="text-xs text-gray-500"></div>
            </div>

            <div>
                <p class="text-xs font-medium text-gray-700">
                    Original text
                </p>

                <div data-extract-original
                     class="mt-1 text-xs text-gray-600 whitespace-pre-wrap break-words"></div>
            </div>

            <div data-extract-edited-section class="hidden">
                <p class="text-xs font-medium text-gray-700">
                    Reviewer-edited text
                </p>

                <div data-extract-edited
                     class="mt-1 text-xs text-gray-600 whitespace-pre-wrap break-words"></div>
            </div>

            <p data-extract-applied
               class="hidden text-xs text-gray-500"></p>

            <p data-extract-approved-note
               class="hidden text-xs text-blue-700">
                Approved. Application to the Destination will be enabled
                in the next stage.
            </p>

            <div data-extract-actions class="flex flex-wrap gap-2">
                <button type="button"
                        data-extract-action="toggle-edit"
                        aria-expanded="false"
                        class="px-2.5 py-1.5 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-xs">
                    Edit
                </button>

                <button type="button"
                        data-extract-action="approve"
                        class="px-2.5 py-1.5 bg-green-600 text-white rounded hover:bg-green-700 text-xs">
                    Approve
                </button>

                <button type="button"
                        data-extract-action="reject"
                        class="px-2.5 py-1.5 bg-amber-600 text-white rounded hover:bg-amber-700 text-xs">
                    Reject
                </button>

                <button type="button"
                        data-extract-action="delete"
                        class="px-2.5 py-1.5 bg-red-600 text-white rounded hover:bg-red-700 text-xs">
                    Delete
                </button>
            </div>

            <form data-extract-edit-form
                  class="hidden border-t border-gray-200 pt-3 space-y-3">

                <label class="block text-xs font-medium text-gray-700">
                    Target field

                    <select name="targetfield"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"
                            required>
                        <option value="">Select a field</option>
                        @foreach ($extractTargetOptions as $value => $label)
                            <option value="{{ $value }}">
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="block text-xs font-medium text-gray-700">
                    Original extracted text

                    <textarea name="extractedtext"
                              rows="4"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"
                              required></textarea>
                </label>

                <label class="block text-xs font-medium text-gray-700">
                    Reviewer-edited text

                    <textarea name="editedtext"
                              rows="4"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
                </label>

                <div class="grid grid-cols-2 gap-3">
                    <label class="block text-xs font-medium text-gray-700">
                        Confidence

                        <select name="confidence"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">Not rated</option>
                            @foreach (range(1, 5) as $rating)
                                <option value="{{ $rating }}">{{ $rating }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block text-xs font-medium text-gray-700">
                        Sort order

                        <input type="number"
                               name="sortorder"
                               min="0"
                               step="1"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </label>
                </div>

                <p class="text-xs text-gray-500">
                    Saving an approved extract returns it to pending review.
                </p>

                <div class="flex justify-end gap-2">
                    <button type="button"
                            data-extract-action="cancel-edit"
                            class="px-3 py-1.5 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-xs">
                        Cancel
                    </button>

                    <button type="submit"
                            class="px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs">
                        Save Extract
                    </button>
                </div>
            </form>
        </div>
    </template>
</div>