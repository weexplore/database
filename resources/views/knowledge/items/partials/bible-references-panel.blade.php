{{-- resources/views/knowledge/items/partials/bible-references-panel.blade.php --}}

@php
    $referencesPayload = $knowledgeItem->bibleReferences
        ->sortBy([
            ['version.versionname', 'asc'],
            ['book.sortorder', 'asc'],
            ['chapterfrom', 'asc'],
            ['versefrom', 'asc'],
            ['id', 'asc'],
        ])
        ->map(function ($reference) {
            return [
                'id' => $reference->id,
                'versionid' => $reference->versionid,
                'versionname' => $reference->version?->versionname ?? '—',
                'bookid' => $reference->bookid,
                'bookname' => $reference->book?->bookname ?? 'Book',
                'chapterfrom' => (int) $reference->chapterfrom,
                'versefrom' => $reference->versefrom ? (int) $reference->versefrom : null,
                'chapterto' => $reference->chapterto ? (int) $reference->chapterto : null,
                'verseto' => $reference->verseto ? (int) $reference->verseto : null,
                'referencelabel' => $reference->referencelabel,
                'notes' => $reference->notes ?? '',
                'notes_html' => filled($reference->notes)
                    ? app(\Illuminate\Mail\Markdown::class)
                        ->parse($reference->notes)
                        ->toHtml()
                    : '',
                'cachedpassagetext' => $reference->cachedpassagetext ?? '',
                'cachedreferencetext' => $reference->cachedreferencetext ?? '',
                'passagefetchedat' => $reference->passagefetchedat?->format('d M Y H:i'),
            ];
        })
        ->values();

    $booksPayload = ($books ?? collect())
        ->map(fn ($book) => [
            'id' => $book->id,
            'name' => $book->bookname,
            'sortorder' => (int) $book->sortorder,
        ])
        ->values();

    $versionsPayload = ($versions ?? collect())
        ->map(fn ($version) => [
            'id' => $version->id,
            'name' => $version->versionname,
        ])
        ->values();
@endphp

<script id="knowledge-bible-references-initial-data" type="application/json">
{!! json_encode([
    'references' => $referencesPayload,
    'books' => $booksPayload,
    'versions' => $versionsPayload,
    'storeUrl' => route('knowledge.items.bible-references.store', $knowledgeItem),

    'updateUrlTemplate' => route(
        'knowledge.items.bible-references.update',
        ['bibleReference' => '__BIBLE_REFERENCE_ID__']
    ),

    'deleteUrlTemplate' => route(
        'knowledge.items.bible-references.destroy',
        ['bibleReference' => '__BIBLE_REFERENCE_ID__']
    ),

    'fetchPassageUrlTemplate' => route(
        'knowledge.items.bible-references.fetch-passage',
        ['bibleReference' => '__BIBLE_REFERENCE_ID__']
    ),

    'csrfToken' => csrf_token(),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
</script>

<div class="overflow-hidden bg-white shadow-sm sm:rounded-lg"
     x-data="knowledgeBibleReferencesPanel()">

    <div class="border-b border-gray-200 px-4 py-4 sm:px-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Bible References</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Link scripture references to this knowledge item.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                    <span x-text="references.length"></span> total
                </span>

                <button type="button"
                        @click="startNewReference()"
                        class="inline-flex items-center rounded bg-blue-600 px-3 py-1.5 text-sm text-white hover:bg-blue-700">
                    + Add Bible Reference
                </button>
            </div>
        </div>
    </div>

    <template x-if="errorMessage">
        <div class="mx-4 mt-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 sm:mx-6"
             x-text="errorMessage">
        </div>
    </template>

    <template x-if="successMessage">
        <div class="mx-4 mt-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 sm:mx-6"
             x-text="successMessage">
        </div>
    </template>

    <template x-if="newReference">
        <div class="m-4 rounded-lg border border-blue-200 bg-blue-50 p-4 sm:m-6 sm:p-5">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <h4 class="text-sm font-semibold text-blue-900">Add Bible Reference</h4>
                    <p class="mt-1 text-xs text-blue-700">
                        Leave Reference Label blank to generate it from the selected book, chapter, and verses.
                    </p>
                </div>

                <button type="button"
                        @click="cancelNewReference()"
                        class="inline-flex shrink-0 items-center rounded border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
            </div>

            @include('knowledge.items.partials.bible-reference-inline-editor', [
                'draftReference' => 'newReference',
                'saveAction' => 'saveNewReference()',
                'cancelAction' => 'cancelNewReference()',
                'saveLabel' => 'Save Bible Reference',
            ])
        </div>
    </template>

    <div class="divide-y divide-gray-200">
        <template x-for="reference in references" :key="reference.id">
            <div class="p-4 sm:p-6">
                <template x-if="!reference.editing">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h4 class="text-sm font-semibold text-gray-900"
                                    x-text="reference.referencelabel || formatReference(reference)">
                                </h4>

                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700"
                                      x-text="reference.versionname">
                                </span>
                            </div>

                            <div class="mt-1 text-xs text-gray-500"
                                 x-text="formatReference(reference)">
                            </div>

                            <template x-if="reference.cachedpassagetext">
                                <div class="mt-3 whitespace-pre-line break-words text-sm leading-6 text-gray-700"
                                     x-text="reference.cachedpassagetext">
                                </div>
                            </template>

                            <template x-if="!reference.cachedpassagetext">
                                <div class="mt-3 text-sm text-gray-400">
                                    No cached passage text.
                                </div>
                            </template>

                            <template x-if="reference.notes_html">
                                <div class="markdown-content prose prose-sm mt-4 max-w-none text-gray-700"
                                     x-html="reference.notes_html">
                                </div>
                            </template>
                        </div>

                        <div class="flex shrink-0 flex-wrap items-center gap-2 lg:justify-end">
                            <form method="POST"
                                :action="fetchPassageUrl(reference.id)"
                                @submit="fetchingReferenceId = reference.id">
                                @csrf

                                <button type="submit"
                                        :disabled="fetchingReferenceId === reference.id"
                                        class="inline-flex items-center rounded bg-indigo-600 px-3 py-1.5 text-xs text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60">
                                    <span x-show="fetchingReferenceId !== reference.id">
                                        Fetch Passage
                                    </span>

                                    <span x-show="fetchingReferenceId === reference.id">
                                        Fetching…
                                    </span>
                                </button>
                            </form>

                            <button type="button"
                                    @click="startEditReference(reference)"
                                    :disabled="fetchingReferenceId === reference.id"
                                    class="inline-flex items-center rounded bg-gray-200 px-3 py-1.5 text-xs text-gray-800 hover:bg-gray-300 disabled:cursor-not-allowed disabled:opacity-60">
                                Edit
                            </button>

                            <button type="button"
                                    @click="deleteReference(reference)"
                                    :disabled="fetchingReferenceId === reference.id"
                                    class="inline-flex items-center rounded bg-red-600 px-3 py-1.5 text-xs text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60">
                                Delete
                            </button>
                        </div>
                    </div>
                </template>

                <template x-if="reference.editing">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <div class="mb-4 flex items-start justify-between gap-3">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900">Edit Bible Reference</h4>
                                <p class="mt-1 text-xs text-gray-500">
                                    Changing the selected version, book, chapter, or verse range clears the cached passage text.
                                </p>
                            </div>

                            <button type="button"
                                    @click="cancelEditReference(reference)"
                                    class="inline-flex shrink-0 items-center rounded border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                        </div>

                        @include('knowledge.items.partials.bible-reference-inline-editor', [
                            'draftReference' => 'reference.draft',
                            'saveAction' => 'saveExistingReference(reference)',
                            'cancelAction' => 'cancelEditReference(reference)',
                            'saveLabel' => 'Save Changes',
                        ])
                    </div>
                </template>
            </div>
        </template>

        <template x-if="references.length === 0 && !newReference">
            <div class="p-6 text-sm text-gray-500">
                No Bible references found.
            </div>
        </template>
    </div>
</div>

@include('partials.markdown.markdown-styles')

<script>
    function knowledgeBibleReferencesPanel() {
        return {
            references: [],
            books: [],
            versions: [],
            storeUrl: '',
            updateUrlTemplate: '',
            deleteUrlTemplate: '',
            fetchPassageUrlTemplate: '',
            csrfToken: '',
            newReference: null,
            saving: false,
            fetchingReferenceId: null,
            errorMessage: '',
            successMessage: '',

            init() {
                try {
                    const data = JSON.parse(
                        document.getElementById(
                            'knowledge-bible-references-initial-data'
                        ).textContent
                    );

                    this.references = (data.references || []).map(
                        reference => this.prepareReference(reference)
                    );

                    this.books = data.books || [];
                    this.versions = data.versions || [];
                    this.storeUrl = data.storeUrl;
                    this.updateUrlTemplate = data.updateUrlTemplate;
                    this.deleteUrlTemplate = data.deleteUrlTemplate;
                    this.csrfToken = data.csrfToken;
                    this.baseUrl = data.baseUrl;
                    this.fetchPassageUrlTemplate = data.fetchPassageUrlTemplate;
                    this.csrfToken = data.csrfToken;

                    this.sortReferences();
                } catch (error) {
                    this.errorMessage = error.message
                        || 'Unable to load Bible references.';
                }
            },

            prepareReference(reference) {
                return {
                    ...reference,
                    editing: false,
                    draft: null,
                };
            },

            emptyDraft() {
                return {
                    versionid: '',
                    bookid: '',
                    chapterfrom: '',
                    versefrom: '',
                    chapterto: '',
                    verseto: '',
                    referencelabel: '',
                    notes: '',
                };
            },

            startNewReference() {
                this.clearMessages();
                this.closeEditors();
                this.newReference = this.emptyDraft();
            },

            cancelNewReference() {
                this.newReference = null;
            },

            startEditReference(reference) {
                this.clearMessages();
                this.newReference = null;
                this.closeEditors();

                reference.draft = {
                    versionid: reference.versionid || '',
                    bookid: reference.bookid || '',
                    chapterfrom: reference.chapterfrom || '',
                    versefrom: reference.versefrom || '',
                    chapterto: reference.chapterto || '',
                    verseto: reference.verseto || '',
                    referencelabel: reference.referencelabel || '',
                    notes: reference.notes || '',
                };

                reference.editing = true;
            },

            cancelEditReference(reference) {
                reference.editing = false;
                reference.draft = null;
            },

            closeEditors() {
                this.references.forEach(reference => {
                    reference.editing = false;
                    reference.draft = null;
                });
            },

            referenceUrl(template, referenceId) {
                return template.replace(
                    '__BIBLE_REFERENCE_ID__',
                    encodeURIComponent(referenceId)
                );
            },

            fetchPassageUrl(referenceId) {
                return this.referenceUrl(
                    this.fetchPassageUrlTemplate,
                    referenceId
                );
            },

            clearMessages() {
                this.errorMessage = '';
                this.successMessage = '';
            },

            async saveNewReference() {
                if (!this.newReference) {
                    return;
                }

                await this.saveReference(this.newReference, null);
            },

            async saveExistingReference(reference) {
                await this.saveReference(reference.draft, reference);
            },

            async saveReference(payload, existingReference) {
                this.clearMessages();

                if (!payload.bookid || !payload.chapterfrom) {
                    this.errorMessage = 'Book and starting chapter are required.';
                    return;
                }

                this.saving = true;

                try {
                    const isNew = !existingReference;

                    const response = await fetch(
                        isNew
                            ? this.storeUrl
                            : this.referenceUrl(
                                this.updateUrlTemplate,
                                existingReference.id
                            ),
                        {
                            method: isNew ? 'POST' : 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                versionid: payload.versionid || null,
                                bookid: Number(payload.bookid),
                                chapterfrom: Number(payload.chapterfrom),
                                versefrom: payload.versefrom
                                    ? Number(payload.versefrom)
                                    : null,
                                chapterto: payload.chapterto
                                    ? Number(payload.chapterto)
                                    : null,
                                verseto: payload.verseto
                                    ? Number(payload.verseto)
                                    : null,
                                referencelabel: payload.referencelabel || null,
                                notes: payload.notes || null,
                            }),
                        }
                    );

                    if (!response.ok) {
                        throw new Error(await this.responseMessage(response));
                    }

                    const data = await response.json();
                    const savedReference = this.prepareReference(data.reference);

                    if (isNew) {
                        this.references.push(savedReference);
                        this.newReference = null;
                        this.successMessage = 'Bible reference added.';
                    } else {
                        const index = this.references.findIndex(
                            item => Number(item.id) === Number(existingReference.id)
                        );

                        if (index !== -1) {
                            this.references.splice(index, 1, savedReference);
                        }

                        this.successMessage = data.message || 'Bible reference updated.';
                    }

                    this.sortReferences();
                } catch (error) {
                    this.errorMessage = error.message
                        || 'Unable to save the Bible reference.';
                } finally {
                    this.saving = false;
                }
            },

            async deleteReference(reference) {
                if (!window.confirm(
                    'Delete this Bible reference? This cannot be undone.'
                )) {
                    return;
                }

                this.clearMessages();
                this.saving = true;

                try {
                    const response = await fetch(
                        this.referenceUrl(
                            this.deleteUrlTemplate,
                            reference.id
                        ),
                        {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json',
                            },
                        }
                    );

                    if (!response.ok) {
                        throw new Error(await this.responseMessage(response));
                    }

                    this.references = this.references.filter(
                        item => Number(item.id) !== Number(reference.id)
                    );

                    this.successMessage = 'Bible reference deleted.';
                } catch (error) {
                    this.errorMessage = error.message
                        || 'Unable to delete the Bible reference.';
                } finally {
                    this.saving = false;
                }
            },

            sortReferences() {
                this.references.sort((left, right) => {
                    const versionComparison = String(left.versionname || '')
                        .localeCompare(String(right.versionname || ''));

                    if (versionComparison !== 0) {
                        return versionComparison;
                    }

                    const leftBook = this.bookSortOrder(left.bookid);
                    const rightBook = this.bookSortOrder(right.bookid);

                    if (leftBook !== rightBook) {
                        return leftBook - rightBook;
                    }

                    if (Number(left.chapterfrom) !== Number(right.chapterfrom)) {
                        return Number(left.chapterfrom) - Number(right.chapterfrom);
                    }

                    if (Number(left.versefrom || 0) !== Number(right.versefrom || 0)) {
                        return Number(left.versefrom || 0) - Number(right.versefrom || 0);
                    }

                    return Number(left.id) - Number(right.id);
                });
            },

            bookSortOrder(bookId) {
                return Number(
                    this.books.find(book => Number(book.id) === Number(bookId))
                        ?.sortorder || 9999
                );
            },

            formatReference(reference) {
                const from = `${reference.bookname} ${reference.chapterfrom}`
                    + (reference.versefrom ? `:${reference.versefrom}` : '');

                if (reference.chapterto) {
                    return from
                        + `-${reference.chapterto}`
                        + (reference.verseto ? `:${reference.verseto}` : '');
                }

                if (reference.verseto && reference.versefrom) {
                    return `${from}-${reference.chapterfrom}:${reference.verseto}`;
                }

                return from;
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