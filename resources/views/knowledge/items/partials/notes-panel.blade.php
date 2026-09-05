{{-- resources/views/knowledge/items/partials/notes-panel.blade.php --}}

@php
    $notesPayload = $knowledgeItem->notes
        ->sortBy(fn ($note) => [
            (int) ($note->sortorder ?? 0),
            (int) $note->id,
        ])
        ->values()
        ->map(function ($note) {
            return [
                'id' => $note->id,
                'notetype' => $note->notetype,
                'notetype_label' => \App\Models\KnowledgeNote::typeOptions()[$note->notetype]
                    ?? $note->notetype
                    ?? 'Note',
                'title' => $note->title ?? '',
                'notecontent' => $note->notecontent ?? '',
                'notecontent_html' => app(\Illuminate\Mail\Markdown::class)
                    ->parse($note->notecontent ?? '')
                    ->toHtml(),
                'stance' => $note->stance ?? '',
                'convictionlevel' => $note->convictionlevel,
                'reviewdate' => $note->reviewdate?->format('Y-m-d'),
                'reviewdate_display' => $note->reviewdate?->format('d M Y'),
                'isprivate' => (bool) $note->isprivate,
                'sortorder' => (int) ($note->sortorder ?? 0),
            ];
        });
@endphp

<script id="knowledge-notes-initial-data" type="application/json">
{!! json_encode([
    'notes' => $notesPayload,
    'storeUrl' => route('knowledge.items.notes.store', $knowledgeItem),
    'baseUrl' => url('/knowledge/items/'.$knowledgeItem->id.'/notes'),
    'reorderUrl' => route('knowledge.items.notes.reorder', $knowledgeItem),
    'csrfToken' => csrf_token(),
    'noteTypes' => $noteTypeOptions ?? [],
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
</script>

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
     x-data="knowledgeNotesPanel()">

    <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Notes</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Add editorial, research, and review notes for this knowledge item.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-medium">
                    <span x-text="notes.length"></span> total
                </span>

                <button type="button"
                        @click="startNewNote()"
                        class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                    + Add Note
                </button>
            </div>
        </div>
    </div>

    <template x-if="errorMessage">
        <div class="mx-4 sm:mx-6 mt-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
             x-text="errorMessage">
        </div>
    </template>

    {{-- Add note --}}
    <template x-if="newNote">
        <div class="m-4 sm:m-6 rounded-lg border border-blue-200 bg-blue-50 p-4 sm:p-5">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h4 class="text-sm font-semibold text-blue-900">Add Note</h4>
                    <p class="mt-1 text-xs text-blue-700">
                        Use Markdown in the note content. It will be rendered after saving.
                    </p>
                </div>

                <button type="button"
                        @click="cancelNewNote()"
                        class="inline-flex items-center px-3 py-1.5 bg-white text-gray-700 border border-gray-300 rounded text-xs hover:bg-gray-50">
                    Cancel
                </button>
            </div>

            @include('knowledge.items.partials.note-inline-editor', [
                'draftReference' => 'newNote',
                'saveAction' => 'saveNewNote()',
                'cancelAction' => 'cancelNewNote()',
                'saveLabel' => 'Save New Note',
            ])
        </div>
    </template>

    <div id="knowledge-notes-list"
         class="divide-y divide-gray-200"
         x-ref="notesList">

        <template x-for="note in notes" :key="note.id">
            <div class="p-4 sm:p-6 knowledge-note-row"
                 :data-note-id="note.id">

                {{-- Display mode --}}
                <template x-if="!note.editing">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex min-w-0 flex-1 items-start gap-3">
                            <button type="button"
                                    class="knowledge-note-drag-handle inline-flex h-8 w-8 shrink-0 touch-none select-none cursor-grab items-center justify-center text-gray-400 hover:text-gray-600 active:cursor-grabbing"
                                    style="touch-action: none;"
                                    title="Drag to reorder"
                                    aria-label="Drag to reorder">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="h-5 w-5"
                                     viewBox="0 0 20 20"
                                     fill="currentColor"
                                     aria-hidden="true">
                                    <path d="M3 5h14a1 1 0 110 2H3a1 1 0 110-2Zm0 4h14a1 1 0 110 2H3a1 1 0 110-2Zm0 4h14a1 1 0 110 2H3a1 1 0 110-2Z" />
                                </svg>
                            </button>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="text-sm font-semibold text-gray-900 break-words"
                                        x-text="note.title || 'Untitled note'">
                                    </h4>

                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700"
                                          x-text="note.notetype_label">
                                    </span>

                                    <template x-if="note.isprivate">
                                        <span class="inline-flex items-center rounded-full bg-amber-50 border border-amber-200 px-2 py-0.5 text-xs font-medium text-amber-700">
                                            Private
                                        </span>
                                    </template>
                                </div>

                                <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs text-gray-500">
                                    <template x-if="note.stance">
                                        <span>
                                            Stance:
                                            <span x-text="note.stance"></span>
                                        </span>
                                    </template>

                                    <template x-if="note.convictionlevel">
                                        <span>
                                            Conviction:
                                            <span x-text="`${note.convictionlevel}/5`"></span>
                                        </span>
                                    </template>

                                    <template x-if="note.reviewdate">
                                        <span>
                                            Review:
                                            <span x-text="note.reviewdate_display"></span>
                                        </span>
                                    </template>

                                    <span>
                                        Sort:
                                        <span x-text="note.sortorder"></span>
                                    </span>
                                </div>

                                <template x-if="note.notecontent_html">
                                    <div
                                        class="mt-3 markdown-content prose prose-sm max-w-none text-gray-700"
                                        x-html="note.notecontent_html"
                                        x-init="$nextTick(() => window.renderMarkdownMath($el))"
                                        x-effect="$nextTick(() => window.renderMarkdownMath($el))"
                                    ></div>
                                </template>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-2 self-end sm:self-start">
                            <button type="button"
                                    @click="startEditNote(note)"
                                    class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded text-xs hover:bg-gray-300">
                                Edit
                            </button>

                            <button type="button"
                                    @click="deleteNote(note)"
                                    class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white rounded text-xs hover:bg-red-700">
                                Delete
                            </button>
                        </div>
                    </div>
                </template>

                {{-- Edit mode --}}
                <template x-if="note.editing">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900">Edit Note</h4>
                                <p class="mt-1 text-xs text-gray-500">
                                    Markdown is rendered after saving.
                                </p>
                            </div>

                            <button type="button"
                                    @click="cancelEditNote(note)"
                                    class="inline-flex items-center px-3 py-1.5 bg-white text-gray-700 border border-gray-300 rounded text-xs hover:bg-gray-50">
                                Cancel
                            </button>
                        </div>

                        @include('knowledge.items.partials.note-inline-editor', [
                            'draftReference' => 'note.draft',
                            'saveAction' => 'saveExistingNote(note)',
                            'cancelAction' => 'cancelEditNote(note)',
                            'saveLabel' => 'Save Note',
                        ])
                    </div>
                </template>
            </div>
        </template>

        <template x-if="notes.length === 0 && !newNote">
            <div class="p-6 text-sm text-gray-500">
                No notes recorded for this knowledge item yet.
            </div>
        </template>
    </div>
</div>

@include('partials.markdown.markdown-styles')

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>

<script>
    function knowledgeNotesPanel() {
        return {
            notes: [],
            noteTypes: {},
            storeUrl: '',
            baseUrl: '',
            reorderUrl: '',
            csrfToken: '',
            newNote: null,
            saving: false,
            reordering: false,
            errorMessage: '',
            sortable: null,

            init() {
                try {
                    const dataElement = document.getElementById('knowledge-notes-initial-data');
                    const data = JSON.parse(dataElement.textContent);

                    this.notes = (data.notes || []).map(note => this.prepareNote(note));
                    this.noteTypes = data.noteTypes || {};
                    this.storeUrl = data.storeUrl;
                    this.baseUrl = data.baseUrl;
                    this.reorderUrl = data.reorderUrl;
                    this.csrfToken = data.csrfToken;

                    this.$nextTick(() => this.setupSortable());
                } catch (error) {
                    this.errorMessage = error.message || 'Unable to load notes.';
                    console.error('Knowledge notes initialisation failed:', error);
                }
            },

            prepareNote(note) {
                return {
                    ...note,
                    editing: false,
                    draft: null,
                };
            },

            emptyDraft() {
                return {
                    notetype: '',
                    title: '',
                    notecontent: '',
                    stance: '',
                    convictionlevel: '',
                    reviewdate: '',
                    isprivate: true,
                    sortorder: this.nextSortOrder(),
                };
            },

            nextSortOrder() {
                if (this.notes.length === 0) {
                    return 1;
                }

                return Math.max(
                    ...this.notes.map(note => Number(note.sortorder) || 0)
                ) + 1;
            },

            startNewNote() {
                this.errorMessage = '';
                this.closeAllEditors();
                this.newNote = this.emptyDraft();
            },

            cancelNewNote() {
                this.newNote = null;
            },

            startEditNote(note) {
                this.errorMessage = '';
                this.newNote = null;
                this.closeAllEditors();

                note.draft = {
                    notetype: note.notetype || '',
                    title: note.title || '',
                    notecontent: note.notecontent || '',
                    stance: note.stance || '',
                    convictionlevel: note.convictionlevel || '',
                    reviewdate: note.reviewdate || '',
                    isprivate: Boolean(note.isprivate),
                    sortorder: note.sortorder || 0,
                };

                note.editing = true;
                this.disableSortable();
            },

            cancelEditNote(note) {
                note.editing = false;
                note.draft = null;
                this.enableSortable();
            },

            closeAllEditors() {
                this.notes.forEach(note => {
                    note.editing = false;
                    note.draft = null;
                });

                this.enableSortable();
            },

            async saveNewNote() {
                if (!this.newNote) {
                    return;
                }

                await this.saveNote(this.newNote, null);
            },

            async saveExistingNote(note) {
                await this.saveNote(note.draft, note);
            },

            async saveNote(payload, existingNote) {
                this.errorMessage = '';

                if (!payload.notetype || !payload.notecontent?.trim()) {
                    this.errorMessage = 'Note type and note content are required.';
                    return;
                }

                this.saving = true;

                try {
                    const isNew = !existingNote;

                    const response = await fetch(
                        isNew ? this.storeUrl : `${this.baseUrl}/${existingNote.id}`,
                        {
                            method: isNew ? 'POST' : 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                ...payload,
                                convictionlevel: payload.convictionlevel || null,
                                reviewdate: payload.reviewdate || null,
                                sortorder: Number(payload.sortorder) || 0,
                                isprivate: Boolean(payload.isprivate),
                            }),
                        }
                    );

                    if (!response.ok) {
                        throw new Error(await this.responseMessage(response));
                    }

                    const data = await response.json();

                    if (isNew) {
                        this.notes.push(this.prepareNote(data.note));
                        this.newNote = null;
                    } else {
                        const index = this.notes.findIndex(
                            item => Number(item.id) === Number(existingNote.id)
                        );

                        if (index !== -1) {
                            this.notes.splice(
                                index,
                                1,
                                this.prepareNote(data.note)
                            );
                        }
                    }

                    this.sortNotes();
                    this.enableSortable();
                } catch (error) {
                    this.errorMessage = error.message || 'Unable to save the note.';
                    console.error('Knowledge note save failed:', error);
                } finally {
                    this.saving = false;
                }
            },

            async deleteNote(note) {
                if (!window.confirm('Delete this note? This cannot be undone.')) {
                    return;
                }

                this.errorMessage = '';
                this.saving = true;

                try {
                    const response = await fetch(`${this.baseUrl}/${note.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        throw new Error(await this.responseMessage(response));
                    }

                    this.notes = this.notes.filter(
                        item => Number(item.id) !== Number(note.id)
                    );

                    this.sortNotes();
                } catch (error) {
                    this.errorMessage = error.message || 'Unable to delete the note.';
                    console.error('Knowledge note delete failed:', error);
                } finally {
                    this.saving = false;
                }
            },

            sortNotes() {
                this.notes.sort((left, right) => {
                    const leftSort = Number(left.sortorder) || 0;
                    const rightSort = Number(right.sortorder) || 0;

                    if (leftSort !== rightSort) {
                        return leftSort - rightSort;
                    }

                    return Number(left.id) - Number(right.id);
                });
            },

            setupSortable() {
                if (
                    !this.$refs.notesList
                    || typeof Sortable === 'undefined'
                    || this.sortable
                ) {
                    return;
                }

                this.sortable = Sortable.create(this.$refs.notesList, {
                    animation: 150,
                    handle: '.knowledge-note-drag-handle',
                    draggable: '.knowledge-note-row',
                    ghostClass: 'bg-blue-50',
                    chosenClass: 'bg-slate-50',
                    onEnd: () => this.persistReorder(),
                });
            },

            disableSortable() {
                if (this.sortable) {
                    this.sortable.option('disabled', true);
                }
            },

            enableSortable() {
                if (this.sortable) {
                    this.sortable.option('disabled', false);
                }
            },

            async persistReorder() {
                if (this.reordering || !this.$refs.notesList) {
                    return;
                }

                const rows = Array.from(
                    this.$refs.notesList.querySelectorAll('.knowledge-note-row')
                );

                const noteOrder = {};

                rows.forEach((row, index) => {
                    const noteId = row.dataset.noteId;

                    if (noteId) {
                        noteOrder[noteId] = index + 1;
                    }
                });

                this.notes.forEach(note => {
                    if (noteOrder[note.id]) {
                        note.sortorder = noteOrder[note.id];
                    }
                });

                this.reordering = true;
                this.errorMessage = '';

                try {
                    const response = await fetch(this.reorderUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            note_order: noteOrder,
                        }),
                    });

                    if (!response.ok) {
                        throw new Error(await this.responseMessage(response));
                    }

                    this.sortNotes();
                } catch (error) {
                    this.errorMessage = error.message || 'Unable to save the note order.';
                    console.error('Knowledge note reorder failed:', error);
                } finally {
                    this.reordering = false;
                }
            },

            async responseMessage(response) {
                const data = await response.json().catch(() => null);

                if (data?.message) {
                    return data.message;
                }

                if (data?.errors) {
                    return Object.values(data.errors).flat().join(' ');
                }

                return 'The request could not be completed.';
            },
        };
    }
</script>