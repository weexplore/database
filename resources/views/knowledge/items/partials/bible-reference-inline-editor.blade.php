@php
    $draftReference = $draftReference ?? 'newReference';
    $saveAction = $saveAction ?? 'saveNewReference()';
    $cancelAction = $cancelAction ?? 'cancelNewReference()';
    $saveLabel = $saveLabel ?? 'Save Bible Reference';
@endphp

<form class="space-y-5"
      @submit.prevent="{{ $saveAction }}">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div>
            <label for="versionid" class="mb-1 block text-sm font-medium text-gray-700">
                Version
            </label>

            <select
                id="versionid"
                x-ref="versionSelect"
                x-model="{{ $draftReference }}.versionid"
                class="w-full rounded-md border-gray-300 shadow-sm text-sm"
            >
                <option value="">Select</option>

                <template x-for="version in versions" :key="version.id">
                    <option :value="version.id" x-text="version.name"></option>
                </template>
            </select>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">
                Book
            </label>

            <select x-model="{{ $draftReference }}.bookid"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                    required>
                <option value="">Select</option>

                <template x-for="book in books" :key="book.id">
                    <option :value="book.id"
                            x-text="book.name">
                    </option>
                </template>
            </select>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">
                Chapter from
            </label>

            <input type="number"
                   min="1"
                   x-model="{{ $draftReference }}.chapterfrom"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                   required>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">
                Verse from
            </label>

            <input type="number"
                   min="1"
                   x-model="{{ $draftReference }}.versefrom"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">
                Chapter to
            </label>

            <input type="number"
                   min="1"
                   x-model="{{ $draftReference }}.chapterto"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">
                Verse to
            </label>

            <input type="number"
                   min="1"
                   x-model="{{ $draftReference }}.verseto"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-medium text-gray-700">
                Reference Label
            </label>

            <input type="text"
                   maxlength="100"
                   x-model="{{ $draftReference }}.referencelabel"
                   placeholder="Optional; built automatically if blank"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">
            Notes
        </label>

        <textarea x-model="{{ $draftReference }}.notes"
                  rows="5"
                  placeholder="Context, commentary, cross-reference notes, or why this passage matters. Markdown supported."
                  class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </textarea>

        <p class="mt-1 text-xs text-gray-500">
            Markdown is rendered after saving.
        </p>
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