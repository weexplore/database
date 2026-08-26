@php
    $draftReference = $draftReference ?? 'newNote';
    $saveAction = $saveAction ?? 'saveNewNote()';
    $cancelAction = $cancelAction ?? 'cancelNewNote()';
    $saveLabel = $saveLabel ?? 'Save Note';
@endphp

<form class="space-y-4"
      @submit.prevent="{{ $saveAction }}">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Note type
            </label>

            <select x-model="{{ $draftReference }}.notetype"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                    required>
                <option value="">Select note type</option>

                <template x-for="[value, label] in Object.entries(noteTypes)" :key="value">
                    <option :value="value" x-text="label"></option>
                </template>
            </select>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Title
            </label>

            <input type="text"
                   x-model="{{ $draftReference }}.title"
                   maxlength="255"
                   placeholder="Optional note title"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Note content
        </label>

        <textarea x-model="{{ $draftReference }}.notecontent"
                  rows="8"
                  placeholder="Write the note content in Markdown..."
                  class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </textarea>

        <p class="mt-1 text-xs text-gray-500">
            Markdown is supported, including headings, lists, emphasis, links, and tables.
        </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Stance
            </label>

            <input type="text"
                   x-model="{{ $draftReference }}.stance"
                   maxlength="30"
                   placeholder="watch, buy, hold"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Conviction
            </label>

            <input type="number"
                   x-model="{{ $draftReference }}.convictionlevel"
                   min="1"
                   max="5"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Review date
            </label>

            <input type="date"
                   x-model="{{ $draftReference }}.reviewdate"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Sort order
            </label>

            <input type="number"
                   x-model="{{ $draftReference }}.sortorder"
                   min="0"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
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