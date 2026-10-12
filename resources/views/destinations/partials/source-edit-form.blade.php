@php
    $editValue = fn (string $field, $default = '') =>
        $isEditingSource ? old($field, $default) : $default;
@endphp

<form method="POST"
      action="{{ route('destinations.sources.update', [$destination, $source]) }}"
      id="source-edit-{{ $source->id }}"
      data-source-form
      class="{{ $isEditingSource ? '' : 'hidden' }} mt-3 border-t border-gray-200 pt-3 space-y-3">

    @csrf
    @method('PUT')

    <input type="hidden"
           name="_source_form_action"
           value="edit">

    <input type="hidden"
           name="_source_form_id"
           value="{{ $source->id }}">

    <input type="hidden"
           name="return_to"
           value="{{ $sourceReturnTo }}">

    <div>
        <label for="source_title_{{ $source->id }}"
               class="block text-xs font-medium text-gray-700 mb-1">
            Title
        </label>

        <input type="text"
               name="sourcetitle"
               id="source_title_{{ $source->id }}"
               value="{{ $editValue('sourcetitle', $source->sourcetitle) }}"
               maxlength="255"
               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
               required>
    </div>

    <div>
        <label for="source_publisher_{{ $source->id }}"
               class="block text-xs font-medium text-gray-700 mb-1">
            Publisher
        </label>

        <input type="text"
               name="sourcepublisher"
               id="source_publisher_{{ $source->id }}"
               value="{{ $editValue('sourcepublisher', $source->sourcepublisher) }}"
               maxlength="150"
               class="w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>

    <div>
        <label for="source_url_{{ $source->id }}"
               class="block text-xs font-medium text-gray-700 mb-1">
            URL
        </label>

        <input type="url"
               name="sourceurl"
               id="source_url_{{ $source->id }}"
               value="{{ $editValue('sourceurl', $source->sourceurl) }}"
               maxlength="255"
               class="w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
            <label for="source_retrievedon_{{ $source->id }}"
                   class="block text-xs font-medium text-gray-700 mb-1">
                Retrieved on
            </label>

            <input type="date"
                   name="retrievedon"
                   id="source_retrievedon_{{ $source->id }}"
                   value="{{ $editValue('retrievedon', $source->retrievedon?->format('Y-m-d')) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="source_status_{{ $source->id }}"
                   class="block text-xs font-medium text-gray-700 mb-1">
                Status
            </label>

            <select name="importstatus"
                    id="source_status_{{ $source->id }}"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">

                @foreach (\App\Models\DestinationSource::importStatusOptions() as $value => $label)
                    <option value="{{ $value }}"
                            @selected($editValue('importstatus', $source->importstatus) === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <details class="rounded-md border border-gray-200 bg-white" open>
            <summary class="cursor-pointer px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50">
                Imported Summary
                <span class="ml-2 font-normal text-gray-500">
                    Click to expand or minimise
                </span>
            </summary>

            <div class="border-t border-gray-200 p-3">
                <label for="source_summary_{{ $source->id }}"
                    class="sr-only">
                    Imported Summary
                </label>

                <textarea name="importedsummary"
                        id="source_summary_{{ $source->id }}"
                        rows="6"
                        class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ $editValue('importedsummary', $source->importedsummary) }}</textarea>
            </div>
        </details>
    </div>

    <div>
        <label for="source_notes_{{ $source->id }}"
               class="block text-xs font-medium text-gray-700 mb-1">
            Imported notes
        </label>

        <textarea name="importednotes"
                  id="source_notes_{{ $source->id }}"
                  rows="3"
                  class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ $editValue('importednotes', $source->importednotes) }}</textarea>
    </div>

    <div class="flex items-center justify-end gap-2">
        <button type="button"
                data-source-toggle
                data-target="source-edit-{{ $source->id }}"
                class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-xs">
            Cancel
        </button>

        <button type="submit"
                class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs">
            Save Changes
        </button>
    </div>
</form>