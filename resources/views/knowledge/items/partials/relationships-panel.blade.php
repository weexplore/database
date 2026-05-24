{{-- resources/views/knowledge/items/partials/relationships-panel.blade.php --}}
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Relationships</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Link this knowledge item to related items in the same domain. Relationships are shown from both sides.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-medium">
                    {{ $displayRelationships->count() }} total
                </span>

                @if($displayRelationships->count() > 1 && !isset($editingRelationshipId) && !($showAddRelationship ?? false))
                    <button type="button"
                            id="save-knowledge-relationships-order-button"
                            class="inline-flex items-center px-3 py-1.5 bg-slate-700 text-white rounded text-sm hover:bg-slate-800">
                        Save Order
                    </button>
                @endif

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
                                    {{ $item->primaryCategory?->categoryname ?? 'Uncategorised' }}: {{ $item->itemname }}
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
                    <label for="relationship_effective_date" class="block text-sm font-medium text-gray-700 mb-1">
                        Effective Date
                    </label>
                    <input type="date"
                           name="effective_date"
                           id="relationship_effective_date"
                           value="{{ old('effective_date') }}"
                           class="w-full rounded-md border-gray-300 shadow-sm text-sm">
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

    <div id="knowledge-relationships-list" class="divide-y divide-gray-200">
        @forelse($displayRelationships as $entry)
            @php
                $relationship = $entry['relationship'];
                $relatedItem = $entry['relatedItem'];
                $displayTypeLabel = $entry['displayTypeLabel'];
                $direction = $entry['direction'];
                $displaySortOrder = (int) ($entry['sortorder'] ?? 0);
                $isEditingThis = isset($editingRelationshipId) && (int) $editingRelationshipId === (int) $relationship->id;
            @endphp

            <div class="p-4 space-y-3 {{ !$isEditingThis ? 'knowledge-relationship-row' : '' }}"
                 @if(!$isEditingThis) data-relationship-id="{{ $relationship->id }}" @endif>
                @if(! $isEditingThis)
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3 min-w-0 flex-1">
                            <button type="button"
                                    class="knowledge-relationship-drag-handle inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 cursor-move shrink-0 mt-0.5"
                                    title="Drag to reorder"
                                    aria-label="Drag to reorder">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-5 h-5"
                                     viewBox="0 0 20 20"
                                     fill="currentColor"
                                     aria-hidden="true">
                                    <path d="M3 5h14a1 1 0 110 2H3a1 1 0 110-2Zm0 4h14a1 1 0 110 2H3a1 1 0 110-2Zm0 4h14a1 1 0 110 2H3a1 1 0 110-2Z" />
                                </svg>
                            </button>

                            <div class="space-y-1 min-w-0 flex-1">
                                <div class="text-sm font-semibold text-gray-900">
                                    @if($relatedItem)
                                        {{ $relatedItem->primaryCategory?->categoryname ?? 'Uncategorised' }}: {{ $relatedItem->itemname }}
                                    @else
                                        Missing related item
                                    @endif
                                </div>

                                <div class="text-xs text-gray-500">
                                    Type: {{ $displayTypeLabel ?: '—' }}
                                    · Direction: {{ ucfirst($direction) }}
                                    · Effective: {{ $relationship->effective_date ? $relationship->effective_date->format('d M Y') : '—' }}
                                    · Sort: <span class="knowledge-relationship-sort-label">{{ $displaySortOrder }}</span>
                                </div>

                                @if($relationship->notes)
                                    <div class="text-sm text-gray-700 line-clamp-2">
                                        {{ $relationship->notes }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-col items-end gap-2 text-xs text-gray-500 whitespace-nowrap shrink-0">
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
                @else
                    <div class="mt-4 border-t border-gray-200 pt-4">
                        <div class="mb-3 text-xs text-gray-500">
                            Editing the stored relationship record. For incoming rows, the displayed label may be the inverse view.
                        </div>

                        <form method="POST"
                              action="{{ route('knowledge.items.relationships.update', [$knowledgeItem, $relationship]) }}"
                              class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Related Item</label>

                                    @if($direction === 'incoming')
                                        <select name="fromitemid"
                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                required>
                                            <option value="">Select related item</option>
                                            @foreach($relationshipItems as $item)
                                                <option value="{{ $item->id }}"
                                                    @selected((string) old('fromitemid', $relationship->fromitemid) === (string) $item->id)>
                                                    {{ $item->primaryCategory?->categoryname ?? 'Uncategorised' }}: {{ $item->itemname }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <select name="toitemid"
                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                required>
                                            <option value="">Select related item</option>
                                            @foreach($relationshipItems as $item)
                                                <option value="{{ $item->id }}"
                                                    @selected((string) old('toitemid', $relationship->toitemid) === (string) $item->id)>
                                                    {{ $item->primaryCategory?->categoryname ?? 'Uncategorised' }}: {{ $item->itemname }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
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
                                <label class="block text-sm font-medium text-gray-700 mb-1">Effective Date</label>
                                <input type="date"
                                       name="effective_date"
                                       value="{{ old('effective_date', optional($relationship->effective_date)->format('Y-m-d')) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea name="notes"
                                          rows="3"
                                          class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('notes', $relationship->notes) }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Sort Order ({{ ucfirst($direction) }} view)
                                    </label>
                                    <input type="number"
                                           name="sortorder"
                                           value="{{ old('sortorder', $displaySortOrder) }}"
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
                    </div>
                @endif
            </div>
        @empty
            <div class="p-6 text-sm text-gray-500">
                No relationships recorded for this knowledge item yet.
            </div>
        @endforelse
    </div>

    <form method="POST"
          action="{{ route('knowledge.items.relationships.reorder', $knowledgeItem) }}"
          id="knowledge-relationships-reorder-form"
          class="hidden">
        @csrf
        <div id="knowledge-relationships-reorder-fields"></div>
    </form>
</div>

@if(($activeTab ?? null) === 'relationships')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const list = document.getElementById('knowledge-relationships-list');
        const reorderFields = document.getElementById('knowledge-relationships-reorder-fields');
        const saveOrderButton = document.getElementById('save-knowledge-relationships-order-button');

        function syncRelationshipSortLabels() {
            if (!list) {
                return;
            }

            const rows = Array.from(list.querySelectorAll('.knowledge-relationship-row'));

            rows.forEach((row, index) => {
                const sortLabel = row.querySelector('.knowledge-relationship-sort-label');
                if (sortLabel) {
                    sortLabel.textContent = index + 1;
                }
            });
        }

        function buildRelationshipReorderFields() {
            if (!list || !reorderFields) {
                return;
            }

            const rows = Array.from(list.querySelectorAll('.knowledge-relationship-row'));
            reorderFields.innerHTML = '';

            rows.forEach((row, index) => {
                const relationshipId = row.dataset.relationshipId;
                if (!relationshipId) {
                    return;
                }

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'relationship_order[' + relationshipId + ']';
                input.value = index + 1;
                reorderFields.appendChild(input);
            });
        }

        if (list && typeof Sortable !== 'undefined') {
            Sortable.create(list, {
                animation: 150,
                handle: '.knowledge-relationship-drag-handle',
                draggable: '.knowledge-relationship-row',
                ghostClass: 'bg-blue-50',
                chosenClass: 'bg-slate-50',
                onEnd: function () {
                    syncRelationshipSortLabels();
                    buildRelationshipReorderFields();

                    const reorderForm = document.getElementById('knowledge-relationships-reorder-form');
                    if (reorderForm) {
                        reorderForm.submit();
                    }
                }
            });

            syncRelationshipSortLabels();
            buildRelationshipReorderFields();
        }

        if (saveOrderButton) {
            saveOrderButton.addEventListener('click', function () {
                buildRelationshipReorderFields();
                document.getElementById('knowledge-relationships-reorder-form').submit();
            });
        }
    });
    </script>
@endif