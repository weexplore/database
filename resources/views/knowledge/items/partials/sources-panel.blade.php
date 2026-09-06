{{-- resources/views/knowledge/items/partials/sources-panel.blade.php --}}

@php
    $sourcesPayload = $knowledgeItem->sources
        ->sortBy(fn ($source) => mb_strtolower($source->sourcetitle ?? ''))
        ->values()
        ->map(function ($source) {
            return [
                'id' => $source->id,
                'sourcetype' => $source->sourcetype,
                'sourcetype_label' => \App\Models\KnowledgeSource::typeOptions()[$source->sourcetype]
                    ?? $source->sourcetype
                    ?? 'Source',
                'sourceurl' => $source->sourceurl ?? '',
                'sourcetitle' => $source->sourcetitle ?? '',
                'sourcepublisher' => $source->sourcepublisher ?? '',
                'retrievedon' => $source->retrievedon?->format('Y-m-d'),
                'retrievedon_display' => $source->retrievedon?->format('d M Y'),
                'importedsummary' => $source->importedsummary ?? '',
                'importedsummary_html' => app(\Illuminate\Mail\Markdown::class)
                    ->parse($source->importedsummary ?? '')
                    ->toHtml(),
                'importednotes' => $source->importednotes ?? '',
                'importednotes_html' => app(\Illuminate\Mail\Markdown::class)
                    ->parse($source->importednotes ?? '')
                    ->toHtml(),
                'importstatus' => $source->importstatus ?? '',
                'reviewedon' => $source->reviewedon?->format('Y-m-d'),
                'reviewedon_display' => $source->reviewedon?->format('d M Y'),
                'reviewedby' => $source->reviewedby ?? '',
                'internalnotes' => $source->internalnotes ?? '',
                'internalnotes_html' => app(\Illuminate\Mail\Markdown::class)
                    ->parse($source->internalnotes ?? '')
                    ->toHtml(),
            ];
        });

    /*
     * fetchFromInternet() redirects back with old input. The initial Alpine
     * state detects this payload and immediately opens a prefilled Add Source
     * editor, replacing the old show_add_source URL workflow.
     */
    $fetchedSourceDraft = [
        'sourcetype' => old('sourcetype'),
        'sourcetitle' => old('sourcetitle'),
        'sourceurl' => old('sourceurl'),
        'sourcepublisher' => old('sourcepublisher'),
        'retrievedon' => old('retrievedon'),
        'importstatus' => old('importstatus'),
        'importedsummary' => old('importedsummary'),
        'importednotes' => old('importednotes'),
        'reviewedon' => old('reviewedon'),
        'reviewedby' => old('reviewedby'),
        'internalnotes' => old('internalnotes'),
    ];

    $hasFetchedSourceDraft = collect($fetchedSourceDraft)
        ->filter(fn ($value) => filled($value))
        ->isNotEmpty();
@endphp

<script id="knowledge-sources-initial-data" type="application/json">
{!! json_encode([
    'sources' => $sourcesPayload,
    'storeUrl' => route('knowledge.items.sources.store', $knowledgeItem),
    'baseUrl' => url('/knowledge/items/'.$knowledgeItem->id.'/sources'),
    'csrfToken' => csrf_token(),
    'sourceTypes' => $sourceTypeOptions ?? [],
    'fetchSourceDraft' => $hasFetchedSourceDraft ? $fetchedSourceDraft : null,
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
</script>

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
     x-data="knowledgeSourcesPanel()">

    {{-- Header --}}
    <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Sources</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Track articles, books, documents, and web pages supporting this knowledge item.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-medium">
                    <span x-text="sources.length"></span> total
                </span>

                <button type="button"
                        @click="toggleFetchPanel()"
                        class="inline-flex items-center px-3 py-1.5 bg-slate-700 text-white rounded text-sm hover:bg-slate-800">
                    <span x-text="showFetchPanel ? 'Hide fetch' : 'Fetch from Internet'"></span>
                </button>

                <button type="button"
                        @click="startNewSource()"
                        class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                    + Add Source
                </button>
            </div>
        </div>
    </div>

    {{-- Error display --}}
    <template x-if="errorMessage">
        <div class="mx-4 sm:mx-6 mt-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
             x-text="errorMessage">
        </div>
    </template>

    {{-- Retained normal server-side fetch workflow --}}
    <div x-show="showFetchPanel"
         x-cloak
         class="border-b border-gray-200 bg-slate-50 p-4 sm:p-6">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div>
                <h4 class="text-sm font-semibold text-gray-900">Fetch Source from Internet</h4>
                <p class="mt-1 text-sm text-gray-500">
                    Fetch page metadata and imported text, then review it in the new source editor before saving.
                </p>
            </div>

            <button type="button"
                    @click="showFetchPanel = false"
                    class="inline-flex shrink-0 items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded text-xs hover:bg-gray-300">
                Cancel
            </button>
        </div>

        <form method="POST"
              action="{{ route('knowledge.items.sources.fetch', $knowledgeItem) }}"
              class="grid grid-cols-1 md:grid-cols-5 gap-4">
            @csrf

            <div class="md:col-span-4">
                <label for="fetch_url" class="block text-sm font-medium text-gray-700 mb-1">
                    Page URL
                </label>

                <input type="url"
                       name="fetch_url"
                       id="fetch_url"
                       value="{{ old('fetch_url') }}"
                       placeholder="https://"
                       required
                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>

            <div class="flex items-end">
                <button type="submit"
                        class="inline-flex items-center justify-center w-full px-4 py-2 bg-slate-700 text-white rounded hover:bg-slate-800 text-sm">
                    Fetch
                </button>
            </div>
        </form>
    </div>

    {{-- New source editor --}}
    <template x-if="newSource">
        <div class="m-4 sm:m-6 rounded-lg border border-blue-200 bg-blue-50 p-4 sm:p-5">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <h4 class="text-sm font-semibold text-blue-900">Add Source</h4>
                    <p class="mt-1 text-xs text-blue-700">
                        Markdown is rendered after saving. Imported content can be edited before it is retained.
                    </p>
                </div>

                <button type="button"
                        @click="cancelNewSource()"
                        class="inline-flex shrink-0 items-center px-3 py-1.5 bg-white text-gray-700 border border-gray-300 rounded text-xs hover:bg-gray-50">
                    Cancel
                </button>
            </div>

            @include('knowledge.items.partials.source-inline-editor', [
                'draftReference' => 'newSource',
                'saveAction' => 'saveNewSource()',
                'cancelAction' => 'cancelNewSource()',
                'saveLabel' => 'Save New Source',
            ])
        </div>
    </template>

    {{-- Source list --}}
    <div class="divide-y divide-gray-200">
        <template x-for="source in sources" :key="source.id">
            <div class="p-4 sm:p-6">

                {{-- Display mode --}}
                <template x-if="!source.editing">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h4 class="text-sm font-semibold text-gray-900 break-words"
                                    x-text="source.sourcetitle || 'Untitled source'">
                                </h4>

                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700"
                                      x-text="source.sourcetype_label">
                                </span>

                                <template x-if="source.importstatus">
                                    <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700"
                                          x-text="source.importstatus">
                                    </span>
                                </template>
                            </div>

                            <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs text-gray-500">
                                <template x-if="source.sourcepublisher">
                                    <span>
                                        Publisher:
                                        <span x-text="source.sourcepublisher"></span>
                                    </span>
                                </template>

                                <template x-if="source.retrievedon">
                                    <span>
                                        Retrieved:
                                        <span x-text="source.retrievedon_display"></span>
                                    </span>
                                </template>

                                <template x-if="source.reviewedon">
                                    <span>
                                        Reviewed:
                                        <span x-text="source.reviewedon_display"></span>
                                    </span>
                                </template>

                                <template x-if="source.reviewedby">
                                    <span>
                                        By:
                                        <span x-text="source.reviewedby"></span>
                                    </span>
                                </template>
                            </div>

                            <template x-if="source.sourceurl">
                                <div class="mt-2">
                                    <a :href="source.sourceurl"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="break-all text-xs text-blue-600 hover:underline"
                                       x-text="source.sourceurl">
                                    </a>
                                </div>
                            </template>

                            <template x-if="source.importedsummary_html">
                                <div class="mt-4">
                                    <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        Imported summary
                                    </p>

                                    <div
                                        class="markdown-content prose prose-sm max-w-none text-gray-700"
                                        x-html="source.importedsummary_html"
                                        x-init="$nextTick(() => window.renderMarkdownMath($el))"
                                        x-effect="$nextTick(() => window.renderMarkdownMath($el))"
                                    ></div>
                                </div>
                            </template>

                            <template x-if="source.importednotes_html">
                                <div class="mt-4">
                                    <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        Imported notes
                                    </p>

                                    <div
                                        class="markdown-content prose prose-sm max-w-none text-gray-700"
                                        x-html="source.importednotes_html"
                                        x-init="$nextTick(() => window.renderMarkdownMath($el))"
                                        x-effect="$nextTick(() => window.renderMarkdownMath($el))"
                                    ></div>
                                </div>
                            </template>

                            <template x-if="source.internalnotes_html">
                                <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-3">
                                    <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-amber-800">
                                        Internal notes
                                    </p>

                                    <div
                                        class="markdown-content prose prose-sm max-w-none text-amber-950"
                                        x-html="source.internalnotes_html"
                                        x-init="$nextTick(() => window.renderMarkdownMath($el))"
                                        x-effect="$nextTick(() => window.renderMarkdownMath($el))"
                                    ></div>
                                </div>
                            </template>
                        </div>

                        <div class="flex shrink-0 items-center gap-2 self-end sm:self-start">
                            <button type="button"
                                    @click="startEditSource(source)"
                                    class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded text-xs hover:bg-gray-300">
                                Edit
                            </button>

                            <button type="button"
                                    @click="deleteSource(source)"
                                    class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white rounded text-xs hover:bg-red-700">
                                Delete
                            </button>
                        </div>
                    </div>
                </template>

                {{-- Edit mode --}}
                <template x-if="source.editing">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <div class="mb-4 flex items-start justify-between gap-3">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900">Edit Source</h4>
                                <p class="mt-1 text-xs text-gray-500">
                                    Imported and internal Markdown is rendered after saving.
                                </p>
                            </div>

                            <button type="button"
                                    @click="cancelEditSource(source)"
                                    class="inline-flex shrink-0 items-center px-3 py-1.5 bg-white text-gray-700 border border-gray-300 rounded text-xs hover:bg-gray-50">
                                Cancel
                            </button>
                        </div>

                        @include('knowledge.items.partials.source-inline-editor', [
                            'draftReference' => 'source.draft',
                            'saveAction' => 'saveExistingSource(source)',
                            'cancelAction' => 'cancelEditSource(source)',
                            'saveLabel' => 'Save Source',
                        ])
                    </div>
                </template>
            </div>
        </template>

        <template x-if="sources.length === 0 && !newSource">
            <div class="p-6 text-sm text-gray-500">
                No sources recorded for this knowledge item yet.
            </div>
        </template>
    </div>
</div>

@include('partials.markdown.markdown-styles')

<script>
    function knowledgeSourcesPanel() {
        return {
            sources: [],
            sourceTypes: {},
            storeUrl: '',
            baseUrl: '',
            csrfToken: '',
            newSource: null,
            showFetchPanel: false,
            saving: false,
            errorMessage: '',

            init() {
                try {
                    const dataElement = document.getElementById('knowledge-sources-initial-data');
                    const data = JSON.parse(dataElement.textContent);

                    this.sources = (data.sources || []).map(
                        source => this.prepareSource(source)
                    );

                    this.sourceTypes = data.sourceTypes || {};
                    this.storeUrl = data.storeUrl;
                    this.baseUrl = data.baseUrl;
                    this.csrfToken = data.csrfToken;

                    if (data.fetchSourceDraft) {
                        this.newSource = {
                            ...this.emptyDraft(),
                            ...data.fetchSourceDraft,
                        };

                        this.showFetchPanel = false;
                    }
                } catch (error) {
                    this.errorMessage = error.message || 'Unable to load sources.';
                    console.error('Knowledge sources initialisation failed:', error);
                }
            },

            prepareSource(source) {
                return {
                    ...source,
                    editing: false,
                    draft: null,
                };
            },

            emptyDraft() {
                return {
                    sourcetype: '',
                    sourceurl: '',
                    sourcetitle: '',
                    sourcepublisher: '',
                    retrievedon: '',
                    importedsummary: '',
                    importednotes: '',
                    importstatus: 'pendingreview',
                    reviewedon: '',
                    reviewedby: '',
                    internalnotes: '',
                };
            },

            toggleFetchPanel() {
                this.errorMessage = '';
                this.newSource = null;
                this.closeAllEditors();
                this.showFetchPanel = !this.showFetchPanel;
            },

            startNewSource() {
                this.errorMessage = '';
                this.showFetchPanel = false;
                this.closeAllEditors();
                this.newSource = this.emptyDraft();

                this.$nextTick(() => {
                    this.$refs.newSourceTypeSelect?.focus();
                });
            },

            cancelNewSource() {
                this.newSource = null;
            },

            startEditSource(source) {
                this.errorMessage = '';
                this.showFetchPanel = false;
                this.newSource = null;
                this.closeAllEditors();

                source.draft = {
                    sourcetype: source.sourcetype || '',
                    sourceurl: source.sourceurl || '',
                    sourcetitle: source.sourcetitle || '',
                    sourcepublisher: source.sourcepublisher || '',
                    retrievedon: source.retrievedon || '',
                    importedsummary: source.importedsummary || '',
                    importednotes: source.importednotes || '',
                    importstatus: source.importstatus || 'pendingreview',
                    reviewedon: source.reviewedon || '',
                    reviewedby: source.reviewedby || '',
                    internalnotes: source.internalnotes || '',
                };

                source.editing = true;
            },

            cancelEditSource(source) {
                source.editing = false;
                source.draft = null;
            },

            closeAllEditors() {
                this.sources.forEach(source => {
                    source.editing = false;
                    source.draft = null;
                });
            },

            async saveNewSource() {
                if (!this.newSource) {
                    return;
                }

                await this.saveSource(this.newSource, null);
            },

            async saveExistingSource(source) {
                await this.saveSource(source.draft, source);
            },

            async saveSource(payload, existingSource) {
                this.errorMessage = '';

                if (!payload.sourcetype || !payload.sourcetitle?.trim()) {
                    this.errorMessage = 'Source type and title are required.';
                    return;
                }

                this.saving = true;

                try {
                    const isNew = !existingSource;

                    const response = await fetch(
                        isNew ? this.storeUrl : `${this.baseUrl}/${existingSource.id}`,
                        {
                            method: isNew ? 'POST' : 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                ...payload,
                                retrievedon: payload.retrievedon || null,
                                reviewedon: payload.reviewedon || null,
                            }),
                        }
                    );

                    if (!response.ok) {
                        throw new Error(await this.responseMessage(response));
                    }

                    const data = await response.json();

                    if (isNew) {
                        this.sources.push(this.prepareSource(data.source));
                        this.newSource = null;
                    } else {
                        const index = this.sources.findIndex(
                            source => Number(source.id) === Number(existingSource.id)
                        );

                        if (index !== -1) {
                            this.sources.splice(
                                index,
                                1,
                                this.prepareSource(data.source)
                            );
                        }
                    }

                    this.sortSources();
                } catch (error) {
                    this.errorMessage = error.message || 'Unable to save the source.';
                    console.error('Knowledge source save failed:', error);
                } finally {
                    this.saving = false;
                }
            },

            async deleteSource(source) {
                if (!window.confirm('Delete this source? This cannot be undone.')) {
                    return;
                }

                this.errorMessage = '';
                this.saving = true;

                try {
                    const response = await fetch(`${this.baseUrl}/${source.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        throw new Error(await this.responseMessage(response));
                    }

                    this.sources = this.sources.filter(
                        item => Number(item.id) !== Number(source.id)
                    );
                } catch (error) {
                    this.errorMessage = error.message || 'Unable to delete the source.';
                    console.error('Knowledge source delete failed:', error);
                } finally {
                    this.saving = false;
                }
            },

            sortSources() {
                this.sources.sort((left, right) => {
                    return String(left.sourcetitle || '').localeCompare(
                        String(right.sourcetitle || '')
                    );
                });
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