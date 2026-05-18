{{-- resources/views/knowledge/items/partials/notes-panel.blade.php --}}
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Notes</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Add editorial, research, and review notes for this knowledge item.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-medium">
                    {{ $knowledgeItem->notes->count() }} total
                </span>

                @if(!($showAddNote ?? false))
                    <a href="{{ route('knowledge.items.edit', [
                            'knowledgeItem' => $knowledgeItem,
                             'tab' => 'notes',
                            'show_add_note' => 1,
                        ]) }}"
                       class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                        Add Note
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if($showAddNote ?? false)
        <div class="p-6 border-b border-gray-200 space-y-4">
            <div class="flex items-center justify-between gap-4">
                <h4 class="text-sm font-semibold text-gray-900">Add Note</h4>

                    <a href="{{ route('knowledge.items.edit', [
                        'knowledgeItem' => $knowledgeItem,
                        'tab' => 'notes',
                    ]) }}"
                   class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded text-sm hover:bg-gray-300">
                    Cancel
                </a>
            </div>

            <form method="POST"
                  action="{{ route('knowledge.items.notes.store', $knowledgeItem) }}"
                  class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="note_notetype" class="block text-sm font-medium text-gray-700 mb-1">
                            Note Type
                        </label>
                        <select name="notetype"
                                id="note_notetype"
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                required>
                            <option value="">Select note type</option>
                            @foreach($noteTypeOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('notetype') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="note_title" class="block text-sm font-medium text-gray-700 mb-1">
                            Title
                        </label>
                        <input type="text"
                               name="title"
                               id="note_title"
                               value="{{ old('title') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                               maxlength="255"
                               placeholder="Optional note title">
                    </div>
                </div>

                <div>
                    <label for="note_notecontent" class="block text-sm font-medium text-gray-700 mb-1">
                        Note Content
                    </label>
                    <textarea name="notecontent"
                            id="note_notecontent"
                            rows="4"
                            class="js-auto-resize-textarea w-full rounded-md border-gray-300 shadow-sm text-sm"
                            data-min-rows="4"
                            required>{{ old('notecontent') }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="note_stance" class="block text-sm font-medium text-gray-700 mb-1">
                            Stance
                        </label>
                        <input type="text"
                               name="stance"
                               id="note_stance"
                               value="{{ old('stance') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>

                    <div>
                        <label for="note_convictionlevel" class="block text-sm font-medium text-gray-700 mb-1">
                            Conviction
                        </label>
                        <input type="number"
                               name="convictionlevel"
                               id="note_convictionlevel"
                               value="{{ old('convictionlevel') }}"
                               min="1"
                               max="5"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>

                    <div>
                        <label for="note_reviewdate" class="block text-sm font-medium text-gray-700 mb-1">
                            Review Date
                        </label>
                        <input type="date"
                               name="reviewdate"
                               id="note_reviewdate"
                               value="{{ old('reviewdate') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>

                    <div>
                        <label for="note_sortorder" class="block text-sm font-medium text-gray-700 mb-1">
                            Sort Order
                        </label>
                        <input type="number"
                               name="sortorder"
                               id="note_sortorder"
                               value="{{ old('sortorder', 0) }}"
                               min="0"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>
                </div>

                <div class="flex items-center justify-between gap-4">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="hidden" name="isprivate" value="0">
                        <input type="checkbox"
                               name="isprivate"
                               value="1"
                               class="rounded border-gray-300 text-blue-600 shadow-sm"
                               @checked(old('isprivate', false))>
                        Private note
                    </label>

                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                        Save New Note
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="divide-y divide-gray-200">
        @forelse($knowledgeItem->notes->sortBy('sortorder') as $note)
            <div class="p-4 space-y-3">
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-1">
                        <div class="text-sm font-semibold text-gray-900">
                            {{ $note->title ?: 'Untitled note' }}
                        </div>
                        <div class="text-xs text-gray-500">
                            Type: {{ $note->notetype ?: '—' }}
                            · Sort: {{ $note->sortorder ?? 0 }}
                            · {{ $note->isprivate ? 'Private' : 'Shared' }}
                        </div>
                        <div class="text-sm text-gray-700 line-clamp-2">
                            {{ $note->notecontent }}
                        </div>
                    </div>

                    <div class="flex flex-col items-end gap-2 text-xs text-gray-500">
                        <div>ID: {{ $note->id }}</div>
                        <div>Review: {{ $note->reviewdate?->format('d M Y') ?? '—' }}</div>

                        <div class="flex items-center gap-2 mt-1">
                            <a href="{{ route('knowledge.items.edit', [
                                    'knowledgeItem' => $knowledgeItem,
                                    'tab' => 'notes',
                                    'editing_note_id' => $note->id,
                                ]) }}"
                               class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded text-xs hover:bg-gray-300">
                                Edit
                            </a>

                            <form method="POST"
                                  action="{{ route('knowledge.items.notes.destroy', [$knowledgeItem, $note]) }}"
                                  onsubmit="return confirm('Delete this note?');">
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

                @if(isset($editingNoteId) && (int) $editingNoteId === $note->id)
                    <div class="mt-4 border-t border-gray-200 pt-4">
                        <form method="POST"
                              action="{{ route('knowledge.items.notes.update', [$knowledgeItem, $note]) }}"
                              class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Note Type</label>
                                    <select name="notetype"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                            required>
                                        <option value="">Select note type</option>
                                        @foreach($noteTypeOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('notetype', $note->notetype) === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                    <input type="text"
                                           name="title"
                                           value="{{ old('title', $note->title) }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Note Content</label>
                                <textarea name="notecontent"
                                        rows="4"
                                        class="js-auto-resize-textarea w-full rounded-md border-gray-300 shadow-sm text-sm"
                                        data-min-rows="4"
                                        required>{{ old('notecontent', $note->notecontent) }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Stance</label>
                                    <input type="text"
                                           name="stance"
                                           value="{{ old('stance', $note->stance) }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Conviction</label>
                                    <input type="number"
                                           name="convictionlevel"
                                           value="{{ old('convictionlevel', $note->convictionlevel) }}"
                                           min="1"
                                           max="5"
                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Review Date</label>
                                    <input type="date"
                                           name="reviewdate"
                                           value="{{ optional($note->reviewdate)->format('Y-m-d') }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                                    <input type="number"
                                           name="sortorder"
                                           value="{{ old('sortorder', $note->sortorder ?? 0) }}"
                                           min="0"
                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="hidden" name="isprivate" value="0">
                                    <input type="checkbox"
                                           name="isprivate"
                                           value="1"
                                           class="rounded border-gray-300 text-blue-600 shadow-sm"
                                           @checked($note->isprivate)>
                                    Private note
                                </label>

                                <div class="flex items-center gap-2">
                                    <a href="{{ route('knowledge.items.edit', [
                                        'knowledgeItem' => $knowledgeItem,
                                        'tab' => 'notes',
                                    ]) }}"
                                       class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded text-xs hover:bg-gray-300">
                                        Cancel
                                    </a>

                                    <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                        Save Note
                                    </button>
                                </div>
                            </div>
                        </form>
                        <form method="POST"
                            action="{{ route('knowledge.items.notes.destroy', [$knowledgeItem, $note]) }}"
                            class="inline">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200 text-xs"
                                    onclick="return confirm('Delete this note? This cannot be undone.');">
                                Delete
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <div class="p-6 text-sm text-gray-500">
                No notes recorded for this knowledge item yet.
            </div>
        @endforelse
    </div>
</div>
@if(($activeTab ?? null) === 'notes')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const textareas = Array.from(document.querySelectorAll('.js-auto-resize-textarea'));

        function getMinHeight(textarea) {
            const computed = window.getComputedStyle(textarea);
            const lineHeight = parseFloat(computed.lineHeight) || 20;
            const paddingTop = parseFloat(computed.paddingTop) || 0;
            const paddingBottom = parseFloat(computed.paddingBottom) || 0;
            const borderTop = parseFloat(computed.borderTopWidth) || 0;
            const borderBottom = parseFloat(computed.borderBottomWidth) || 0;
            const minRows = parseInt(textarea.dataset.minRows || textarea.getAttribute('rows') || 4, 10);

            return (lineHeight * minRows) + paddingTop + paddingBottom + borderTop + borderBottom;
        }

        function autoResize(textarea) {
            const minHeight = getMinHeight(textarea);

            textarea.style.overflowY = 'hidden';
            textarea.style.resize = 'vertical';
            textarea.style.height = 'auto';

            const nextHeight = Math.max(textarea.scrollHeight, minHeight);
            textarea.style.height = nextHeight + 'px';
        }

        textareas.forEach((textarea) => {
            autoResize(textarea);

            textarea.addEventListener('input', function () {
                autoResize(textarea);
            });
        });

        window.addEventListener('resize', function () {
            textareas.forEach(autoResize);
        });
    });
    </script>
@endif