@php
    $isSourceCreate = $sourceFormAction === 'create';

    $createValue = fn (string $field, $default = '') =>
        $isSourceCreate ? old($field, $default) : $default;
@endphp

<form method="POST"
      action="{{ route('destinations.sources.store', $destination) }}"
      id="destination-source-create-form"
      data-source-form
      class="{{ $isSourceCreate ? '' : 'hidden' }} border border-gray-200 rounded-lg bg-gray-50 p-4 space-y-3">

    @csrf

    <input type="hidden"
           name="_source_form_action"
           value="create">

    <input type="hidden"
           name="return_to"
           value="{{ $sourceReturnTo }}">

    <div>
        <label for="new_source_title"
               class="block text-xs font-medium text-gray-700 mb-1">
            Title
        </label>

        <input type="text"
               name="sourcetitle"
               id="new_source_title"
               value="{{ $createValue('sourcetitle') }}"
               maxlength="255"
               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
               required>
    </div>

    <div>
        <label for="new_source_publisher"
               class="block text-xs font-medium text-gray-700 mb-1">
            Publisher
        </label>

        <input type="text"
               name="sourcepublisher"
               id="new_source_publisher"
               value="{{ $createValue('sourcepublisher') }}"
               maxlength="150"
               class="w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>

    <div>
        <label for="new_source_url"
               class="block text-xs font-medium text-gray-700 mb-1">
            URL
        </label>

        <input type="url"
               name="sourceurl"
               id="new_source_url"
               value="{{ $createValue('sourceurl') }}"
               maxlength="255"
               class="w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
            <label for="new_source_retrievedon"
                   class="block text-xs font-medium text-gray-700 mb-1">
                Retrieved on
            </label>

            <input type="date"
                   name="retrievedon"
                   id="new_source_retrievedon"
                   value="{{ $createValue('retrievedon', now()->toDateString()) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label for="new_source_importstatus"
                   class="block text-xs font-medium text-gray-700 mb-1">
                Status
            </label>

            <select name="importstatus"
                    id="new_source_importstatus"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm">

                @foreach (\App\Models\DestinationSource::importStatusOptions() as $value => $label)
                    <option value="{{ $value }}"
                            @selected($createValue('importstatus', 'pendingreview') === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <label for="new_source_summary"
               class="block text-xs font-medium text-gray-700 mb-1">
            Imported summary
        </label>

        <textarea name="importedsummary"
                  id="new_source_summary"
                  rows="3"
                  class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ $createValue('importedsummary') }}</textarea>
    </div>

    <div>
        <label for="new_source_notes"
               class="block text-xs font-medium text-gray-700 mb-1">
            Imported notes
        </label>

        <textarea name="importednotes"
                  id="new_source_notes"
                  rows="3"
                  class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ $createValue('importednotes') }}</textarea>
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