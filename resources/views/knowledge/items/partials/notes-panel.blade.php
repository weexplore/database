{{-- resources/views/knowledge/items/partials/notes-panel.blade.php --}}

<style>
    .knowledge-note-content {
        position: relative;
        overflow: hidden;
    }

    .knowledge-note-content[data-collapsed="true"] {
        max-height: 10rem; /* adjust as needed */
    }

    .knowledge-note-content[data-collapsed="false"] {
        max-height: none;
    }

</style>

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
                    <x-forms.markdown-field
                        name="notecontent"
                        id="note_notecontent"
                        label="Note Content"
                        :value="old('notecontent', '')"
                        rows="8"
                        minRows="4"
                        maxRows="18"
                        placeholder="Write the note content in Markdown..."
                        help="Markdown supported, including headings, lists, emphasis, links, and tables."
                        :startCollapsed="false"
                    />
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

    <div id="knowledge-notes-list" class="divide-y divide-gray-200">
        @forelse($knowledgeItem->notes->sortBy('sortorder') as $note)
            @if(isset($editingNoteId) && (int) $editingNoteId === (int) $note->id)
                <div class="p-4 bg-blue-50/40 space-y-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900">Edit Note</h4>
                            <p class="text-xs text-gray-500">
                                Updating note: {{ $note->title ?: 'Untitled note' }}
                            </p>
                        </div>

                        <a href="{{ route('knowledge.items.edit', [
                                'knowledgeItem' => $knowledgeItem,
                                'tab' => 'notes',
                            ]) }}"
                           class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded text-xs hover:bg-gray-300">
                            Cancel
                        </a>
                    </div>

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
                            <x-forms.markdown-field
                                name="notecontent"
                                id="note_notecontent_{{ $note->id }}"
                                label="Note Content"
                                :value="old('notecontent', $note->notecontent ?? '')"
                                rows="8"
                                minRows="4"
                                maxRows="18"
                                placeholder="Write the note content in Markdown..."
                                help="Markdown supported, including headings, lists, emphasis, links, and tables."
                                :startCollapsed="false"
                            />
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
                                       value="{{ old('reviewdate', optional($note->reviewdate)->format('Y-m-d')) }}"
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
                                       @checked(old('isprivate', $note->isprivate))>
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
                </div>
            @else
                @php
                    $raw = $note->notecontent ?? '';
                    $normalised = str_replace(["\r\n", "\r"], "\n", $raw);
                    $contentId = 'knowledge-note-content-' . $note->id;
                    $previewText = trim($normalised);

                    $looksComplex =
                        str_contains($previewText, '|') ||
                        str_contains($previewText, '```') ||
                        preg_match('/^\s*#{1,6}\s+/m', $previewText) ||
                        preg_match('/^\s*[-*+]\s+/m', $previewText) ||
                        preg_match('/^\s*\d+\.\s+/m', $previewText) ||
                        preg_match('/^\s*>\s+/m', $previewText);

                    $contentClass = $looksComplex
                        ? 'knowledge-note-content knowledge-note-content--complex markdown-content text-gray-700'
                        : 'knowledge-note-content markdown-content text-gray-700';

                    $startCollapsed = 'true';
                @endphp

                <div class="p-4 space-y-3 knowledge-note-row" data-note-id="{{ $note->id }}">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3 min-w-0 flex-1">
                            <button type="button"
                                    class="knowledge-note-drag-handle inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 cursor-move shrink-0 mt-0.5"
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

                            <div class="space-y-2 min-w-0 flex-1">
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $note->title ?: 'Untitled note' }}
                                </div>

                                <div class="text-xs text-gray-500">
                                    Type: {{ $note->notetype ?: '—' }}
                                    · Sort: <span class="knowledge-note-sort-label">{{ $note->sortorder ?? 0 }}</span>
                                    · {{ $note->isprivate ? 'Private' : 'Shared' }}
                                </div>

                                <div id="{{ $contentId }}"
                                    class="{{ $contentClass }}"
                                    data-collapsed="{{ $startCollapsed }}">
                                    @include('partials.markdown.rendered-block', [
                                        'content' => $normalised,
                                    ])
                                </div>

                                <button type="button"
                                    class="knowledge-note-toggle hidden inline-flex items-center px-2.5 py-1 text-xs font-medium text-blue-700 bg-blue-50 rounded hover:bg-blue-100"
                                    data-target="{{ $contentId }}"
                                    aria-expanded="false"
                                    aria-controls="{{ $contentId }}">
                                    Show more
                                </button>
                            </div>
                        </div>

                        <div class="flex flex-col items-end gap-2 text-xs text-gray-500 shrink-0">
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
                </div>
            @endif
        @empty
            <div class="p-6 text-sm text-gray-500">
                No notes recorded for this knowledge item yet.
            </div>
        @endforelse
    </div>

    <form method="POST"
          action="{{ route('knowledge.items.notes.reorder', $knowledgeItem) }}"
          id="knowledge-notes-reorder-form"
          class="hidden">
        @csrf
        <div id="knowledge-notes-reorder-fields"></div>
    </form>
</div>

@if(($activeTab ?? null) === 'notes')
    @include('partials.markdown.markdown-styles')
    @include('partials.forms.markdown-field-scripts')

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const list = document.getElementById('knowledge-notes-list');
        const reorderFields = document.getElementById('knowledge-notes-reorder-fields');
        const reorderForm = document.getElementById('knowledge-notes-reorder-form');
        const noteContentBlocks = Array.from(document.querySelectorAll('.knowledge-note-content'));

        function updateNoteToggleVisibility(content) {
            const button = document.querySelector('.knowledge-note-toggle[data-target="' + content.id + '"]');
            if (!button) {
                return;
            }

            const wasCollapsed = content.dataset.collapsed === 'true';

            content.dataset.collapsed = 'true';
            const isTruncated = content.scrollHeight > content.clientHeight + 1;

            if (isTruncated) {
                button.classList.remove('hidden');
            } else {
                button.classList.add('hidden');
                button.setAttribute('aria-expanded', 'false');
                button.textContent = 'Show more';
            }

            content.dataset.collapsed = wasCollapsed ? 'true' : 'false';
        }

        function syncExpandedState(content, expanded) {
            const button = document.querySelector('.knowledge-note-toggle[data-target="' + content.id + '"]');
            if (!button) {
                return;
            }

            content.dataset.collapsed = expanded ? 'false' : 'true';
            button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            button.textContent = expanded ? 'Show less' : 'Show more';
        }

        function syncNoteSortOrderLabels() {
            if (!list) {
                return;
            }

            const rows = Array.from(list.querySelectorAll('.knowledge-note-row'));

            rows.forEach((row, index) => {
                const sortLabel = row.querySelector('.knowledge-note-sort-label');
                if (sortLabel) {
                    sortLabel.textContent = index + 1;
                }
            });
        }

        function buildReorderFields() {
            if (!list || !reorderFields) {
                return;
            }

            const rows = Array.from(list.querySelectorAll('.knowledge-note-row'));
            reorderFields.innerHTML = '';

            rows.forEach((row, index) => {
                const noteId = row.dataset.noteId;
                if (!noteId) {
                    return;
                }

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'note_order[' + noteId + ']';
                input.value = index + 1;
                reorderFields.appendChild(input);
            });
        }

        noteContentBlocks.forEach((content) => {
            updateNoteToggleVisibility(content);
        });

        document.querySelectorAll('.knowledge-note-toggle').forEach((button) => {
            button.addEventListener('click', function () {
                const content = document.getElementById(this.dataset.target);
                if (!content) {
                    return;
                }

                const expand = content.dataset.collapsed === 'true';
                syncExpandedState(content, expand);
            });
        });

        window.addEventListener('resize', function () {
            noteContentBlocks.forEach((content) => {
                updateNoteToggleVisibility(content);
            });
        });

        if (list && reorderFields && reorderForm && typeof Sortable !== 'undefined') {
            let isSubmitting = false;

            Sortable.create(list, {
                animation: 150,
                handle: '.knowledge-note-drag-handle',
                draggable: '.knowledge-note-row',
                ghostClass: 'bg-blue-50',
                chosenClass: 'bg-slate-50',
                onEnd: function () {
                    if (isSubmitting) {
                        return;
                    }

                    syncNoteSortOrderLabels();
                    buildReorderFields();
                    isSubmitting = true;
                    reorderForm.submit();
                }
            });

            syncNoteSortOrderLabels();
            buildReorderFields();
        }
    });
    </script>
@endif