@php
    $draftReference = $draftReference ?? 'newSource';
    $saveAction = $saveAction ?? 'saveNewSource()';
    $cancelAction = $cancelAction ?? 'cancelNewSource()';
    $saveLabel = $saveLabel ?? 'Save Source';
@endphp

<form class="space-y-4"
      @submit.prevent="{{ $saveAction }}">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Source type
            </label>

            <select x-model="{{ $draftReference }}.sourcetype"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                    required>
                <option value="">Select source type</option>

                <template x-for="[value, label] in Object.entries(sourceTypes)" :key="value">
                    <option :value="value" x-text="label"></option>
                </template>
            </select>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Title
            </label>

            <input type="text"
                   x-model="{{ $draftReference }}.sourcetitle"
                   maxlength="255"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                   required>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                URL
            </label>

            <input type="url"
                   x-model="{{ $draftReference }}.sourceurl"
                   placeholder="https://"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Publisher / source
            </label>

            <input type="text"
                   x-model="{{ $draftReference }}.sourcepublisher"
                   maxlength="150"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Retrieved on
            </label>

            <input type="date"
                   x-model="{{ $draftReference }}.retrievedon"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Import status
            </label>

            <input type="text"
                   x-model="{{ $draftReference }}.importstatus"
                   maxlength="30"
                   placeholder="pendingreview, approved, rejected"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                   required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Reviewed on
            </label>

            <input type="date"
                   x-model="{{ $draftReference }}.reviewedon"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Imported summary
        </label>

        <textarea x-model="{{ $draftReference }}.importedsummary"
                  rows="4"
                  placeholder="Short imported summary from the source. Markdown supported."
                  class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Imported notes
        </label>

        <textarea x-model="{{ $draftReference }}.importednotes"
                  rows="7"
                  placeholder="Imported notes captured from review or fetch workflow. Markdown supported."
                  class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </textarea>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Reviewed by
            </label>

            <input type="text"
                   x-model="{{ $draftReference }}.reviewedby"
                   maxlength="100"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Internal notes
            </label>

            <textarea x-model="{{ $draftReference }}.internalnotes"
                      rows="4"
                      placeholder="Internal curation or review notes. Markdown supported."
                      class="w-full rounded-md border-gray-300 shadow-sm text-sm">
            </textarea>
        </div>
    </div>

<div class="flex items-center justify-end gap-2 border-t border-gray-200 pt-4">
    <button type="button"
            @click="{{ $cancelAction }}"
            :disabled="saving"
            class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">
        Cancel
    </button>

    <button type="submit"
            :disabled="saving"
            class="inline-flex items-center justify-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">
        <span x-show="!saving">{{ $saveLabel }}</span>
        <span x-show="saving">Saving…</span>
    </button>
</div>
</form>