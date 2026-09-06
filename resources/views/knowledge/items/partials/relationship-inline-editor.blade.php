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
            <label for="new-relationship-relateditemid"
                class="mb-1 block text-sm font-medium text-gray-700">
                Related Item
            </label>

            <select
                id="new-relationship-relateditemid"
                x-ref="newRelationshipRelatedItem"
                x-model="{{ $draftReference }}.relateditemid"
                class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                required
            >
                <option value="">Select related item</option>

                <template x-for="item in relationshipItems" :key="item.id">
                    <option
                        :value="item.id"
                        x-text="`${item.category}: ${item.name}`"
                    ></option>
                </template>
            </select>

            <p class="mt-1 text-xs text-gray-500"
            x-show="{{ $draftReference }}.direction === 'incoming'">
                This relationship is displayed as incoming, so the selected item is
                the source side of the stored relationship.
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Relationship Type
            </label>

            <select x-model="{{ $draftReference }}.relationshiptype"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                    required>
                <option value="">Select relationship type</option>

                <template x-for="[value, label] in Object.entries(relationshipTypes)" :key="value">
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
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Notes
        </label>

        <textarea x-model="{{ $draftReference }}.notes"
                  rows="6"
                  placeholder="Relationship notes, context, evidence, and rationale. Markdown supported."
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
    </form>>