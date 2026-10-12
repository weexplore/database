@php
    $draftReference = $draftReference ?? 'newRelationship';
    $saveAction = $saveAction ?? 'saveNewRelationship()';
    $cancelAction = $cancelAction ?? 'cancelNewRelationship()';
    $saveLabel = $saveLabel ?? 'Save Relationship';
@endphp

<form class="space-y-4"
      @submit.prevent="{{ $saveAction }}">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-medium text-gray-700">
                Related Item
            </label>

            <div
                class="relative"
                @click.outside="{{ $draftReference }}.relatedSearchOpen = false"
                @keydown.escape.stop="{{ $draftReference }}.relatedSearchOpen = false"
            >
                <input
                    type="search"
                    x-ref="newRelationshipRelatedItem"
                    x-model="{{ $draftReference }}.relatedSearch"
                    @input="relatedSearchChanged({{ $draftReference }})"
                    @focus="{{ $draftReference }}.relatedSearchOpen = true"
                    @keydown.enter.prevent
                    :disabled="saving"
                    autocomplete="off"
                    placeholder="Type at least 4 characters to search all knowledge items"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                    required
                >

                <div
                    x-show="{{ $draftReference }}.relatedSearchOpen"
                    x-cloak
                    class="absolute inset-x-0 top-full z-30 mt-1 max-h-80 overflow-y-auto rounded-md border border-gray-200 bg-white shadow-lg"
                >
                    <div
                        x-show="[...{{ $draftReference }}.relatedSearch.trim()].length < 4"
                        class="px-3 py-3 text-sm text-gray-500"
                    >
                        Enter at least 4 characters to search the whole knowledge base.
                    </div>

                    <div
                        x-show="{{ $draftReference }}.relatedSearching"
                        class="px-3 py-3 text-sm text-gray-500"
                    >
                        Searching…
                    </div>

                    <div
                        x-show="{{ $draftReference }}.relatedSearchError"
                        x-text="{{ $draftReference }}.relatedSearchError"
                        class="px-3 py-3 text-sm text-red-700"
                    ></div>

                    <template
                        x-for="item in {{ $draftReference }}.relatedResults"
                        :key="item.id"
                    >
                        <button
                            type="button"
                            @click="selectRelatedItem({{ $draftReference }}, item)"
                            class="block w-full border-b border-gray-100 px-3 py-2 text-left text-sm text-gray-800 last:border-b-0 hover:bg-blue-50 focus:bg-blue-50 focus:outline-none"
                            x-text="item.label"
                        ></button>
                    </template>

                    <div
                        x-show="
                            [...{{ $draftReference }}.relatedSearch.trim()].length >= 4
                            && !{{ $draftReference }}.relatedSearching
                            && !{{ $draftReference }}.relatedSearchError
                            && !{{ $draftReference }}.relateditemid
                            && {{ $draftReference }}.relatedResults.length === 0
                        "
                        class="px-3 py-3 text-sm text-gray-500"
                    >
                        No matching knowledge items.
                    </div>

                    <div
                        x-show="{{ $draftReference }}.relatedSearchHasMore"
                        class="border-t border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-600"
                    >
                        Showing the first 50 matches. Add more characters to narrow the search.
                    </div>

                    <div
                        x-show="{{ $draftReference }}.relateditemid"
                        class="px-3 py-2 text-xs text-green-700"
                    >
                        An item is selected. Change the search text to find another.
                    </div>
                </div>
            </div>

            <p class="mt-1 text-xs text-gray-500">
                Searches item and category names across all domains.
                Select a result before saving.
            </p>

            <p
                class="mt-1 text-xs text-gray-500"
                x-show="{{ $draftReference }}.direction === 'incoming'"
            >
                The type describes the current item's relationship to the selected item.
                Saving preserves the stored incoming direction.
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Related Item Type
            </label>

            <select x-model="{{ $draftReference }}.relationshiptype"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                    required>
                <option value="">Select related item type</option>

                <template
                    x-for="[value, label] in Object.entries(typeOptionsFor({{ $draftReference }}.direction))"
                    :key="value"
                >
                    <option :value="value" x-text="label"></option>
                </template>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Effective Date
            </label>

            <input type="date"
                   x-model="{{ $draftReference }}.effective_date"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Sort Order
            </label>

            <input type="number"
                   x-model="{{ $draftReference }}.sortorder"
                   min="0"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">
            Notes
        </label>

        <textarea
            x-model="{{ $draftReference }}.notes"
            rows="6"
            data-min-rows="6"
            data-max-rows="14"
            placeholder="Related Items notes, context, evidence, and rationale."
            class="js-alpine-markdown-textarea w-full rounded-md border-gray-300 text-sm shadow-sm"
            x-init="$nextTick(() => window.autoResizeMarkdownTextarea?.($el))"
            @input="window.autoResizeMarkdownTextarea?.($el)"
        ></textarea>

        <p class="mt-1 text-xs text-gray-500">
            Markdown is supported, including headings, lists, emphasis, links, tables, and mathematics.
            It is rendered after saving.
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