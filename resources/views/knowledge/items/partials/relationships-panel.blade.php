{{-- resources/views/knowledge/items/partials/relationships-panel.blade.php --}}
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Relationships</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Link this knowledge item to related items in the same domain.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-medium">
                    {{ $knowledgeItem->relationships->count() }} total
                </span>

                @if(!($showAddRelationship ?? false))
                    <a href="{{ route('knowledge.items.edit', [
                            'knowledgeItem' => $knowledgeItem,
                            'tab' => 'relationships',
                            'show_add_relationship' => 1,
                        ]) }}"
                       class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                        Add Relationship
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if($showAddRelationship ?? false)
        <div class="p-6 border-b border-gray-200 space-y-4">
            <div class="flex items-center justify-between gap-4">
                <h4 class="text-sm font-semibold text-gray-900">Add Relationship</h4>

                <a href="{{ route('knowledge.items.edit', [
                    'knowledgeItem' => $knowledgeItem,
                    'tab' => 'relationships',
                ]) }}"
                   class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded text-sm hover:bg-gray-300">
                    Cancel
                </a>
            </div>

            <form method="POST"
                  action="{{ route('knowledge.items.relationships.store', $knowledgeItem) }}"
                  class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label for="relationship_toitemid" class="block text-sm font-medium text-gray-700 mb-1">
                            Related Item
                        </label>
                        <select name="toitemid"
                                id="relationship_toitemid"
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                required>
                            <option value="">Select related item</option>
                            @foreach($relationshipItems as $item)
                                <option value="{{ $item->id }}" @selected(old('toitemid') == $item->id)>
                                    {{ $item->itemname }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="relationship_relationshiptype" class="block text-sm font-medium text-gray-700 mb-1">
                            Relationship Type
                        </label>
                        <select name="relationshiptype"
                                id="relationship_relationshiptype"
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                required>
                            <option value="">Select relationship type</option>
                            @foreach($relationshipTypeOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('relationshiptype') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label for="relationship_notes" class="block text-sm font-medium text-gray-700 mb-1">
                        Notes
                    </label>
                    <textarea name="notes"
                              id="relationship_notes"
                              rows="3"
                              class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('notes') }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="relationship_sortorder" class="block text-sm font-medium text-gray-700 mb-1">
                            Sort Order
                        </label>
                        <input type="number"
                               name="sortorder"
                               id="relationship_sortorder"
                               value="{{ old('sortorder', 0) }}"
                               min="0"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                        Save New Relationship
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="divide-y divide-gray-200">
        @forelse($knowledgeItem->relationships->sortBy('sortorder') as $relationship)
            <div class="p-4 space-y-3">
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-900">
                            {{ $relationship->toItem?->itemname ?: 'Missing related item' }}
                        </div>

                        <div class="text-xs text-gray-500">
                            Type: {{ $relationship->relationshiptype ?: '—' }}
                            · Sort: {{ $relationship->sortorder ?? 0 }}
                        </div>

                        @if($relationship->notes)
                            <div class="text-sm text-gray-700 line-clamp-2">
                                {{ $relationship->notes }}
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-col items-end gap-2 text-xs text-gray-500 whitespace-nowrap">
                        <div>ID: {{ $relationship->id }}</div>

                        <div class="flex items-center gap-2 mt-1">
                            <a href="{{ route('knowledge.items.edit', [
                                    'knowledgeItem' => $knowledgeItem,
                                    'tab' => 'relationships',
                                    'editing_relationship_id' => $relationship->id,
                                ]) }}"
                               class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded text-xs hover:bg-gray-300">
                                Edit
                            </a>

                            <form method="POST"
                                  action="{{ route('knowledge.items.relationships.destroy', [$knowledgeItem, $relationship]) }}"
                                  onsubmit="return confirm('Delete this relationship?');">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white rounded text-xs hover:bg-red-700">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                @if(isset($editingRelationshipId) && (int) $editingRelationshipId === $relationship->id)
                    <div class="mt-4 border-t border-gray-200 pt-4">
                        <form method="POST"
                              action="{{ route('knowledge.items.relationships.update', [$knowledgeItem, $relationship]) }}"
                              class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Related Item</label>
                                    <select name="toitemid"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                            required>
                                        <option value="">Select related item</option>
                                        @foreach($relationshipItems as $item)
                                            <option value="{{ $item->id }}"
                                                @selected((string) old('toitemid', $relationship->toitemid) === (string) $item->id)>
                                                {{ $item->itemname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Relationship Type</label>
                                    <select name="relationshiptype"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                            required>
                                        <option value="">Select relationship type</option>
                                        @foreach($relationshipTypeOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('relationshiptype', $relationship->relationshiptype) === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea name="notes"
                                          rows="3"
                                          class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('notes', $relationship->notes) }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                                    <input type="number"
                                           name="sortorder"
                                           value="{{ old('sortorder', $relationship->sortorder ?? 0) }}"
                                           min="0"
                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('knowledge.items.edit', [
                                    'knowledgeItem' => $knowledgeItem,
                                    'tab' => 'relationships',
                                ]) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded text-xs hover:bg-gray-300">
                                    Cancel
                                </a>

                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                    Save Relationship
                                </button>
                            </div>
                        </form>
                        <form method="POST"
                            action="{{ route('knowledge.items.relationships.destroy', [$knowledgeItem, $knowledgeRelationship]) }}"
                            class="inline">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200 text-xs"
                                    onclick="return confirm('Delete this relationship? This cannot be undone.');">
                                Delete
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <div class="p-6 text-sm text-gray-500">
                No relationships recorded for this knowledge item yet.
            </div>
        @endforelse
    </div>
</div>