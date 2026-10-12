{{-- resources/views/knowledge/items/partials/research-panel.blade.php --}}
{{-- Keep the existing sources-panel below this partial. --}}

@php
    $researchData = [
        'baseUrl' => url('/knowledge/items/'.$knowledgeItem->id.'/research'),
        'csrf' => csrf_token(),
        'defaultTopic' => $knowledgeItem->itemname,
        'today' => now()->format('Y-m-d'),
        'fetchUrl' => old('fetch_url', ''),
        'openFetchPanel' => request()->boolean('show_fetch_source')
            || filled(old('fetch_url')),
        'fetchedDraft' => session('knowledge_research_draft'),

        'sources' => $knowledgeItem->sources
            ->sortBy(fn ($source) => mb_strtolower($source->sourcetitle ?? ''))
            ->values()
            ->map(fn ($source) => [
                'id' => $source->id,
                'title' => $source->sourcetitle ?: 'Untitled source',
            ]),

        'sourceTypes' => \App\Models\KnowledgeSource::typeOptions(),
        'noteTypes' => \App\Models\KnowledgeNote::typeOptions(),
        'reviewTypes' => \App\Models\KnowledgeReviewLog::typeOptions(),
    ];
@endphp

<script id="knowledge-research-data" type="application/json">
{!! json_encode(
    $researchData,
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
) !!}
</script>

<div id="knowledge-research-workspace"
     class="rounded-lg bg-white shadow-sm"
     x-data="knowledgeResearchWorkspace()"
     @knowledge-research-open-source.window="openSource($event.detail.id)">

    {{-- Header --}}
    <div class="overflow-x-auto border-b border-gray-200">
        <div class="flex min-w-max items-center justify-between gap-6 px-4 py-4 sm:px-6">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">
                    Research
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Search, import a webpage, or paste text.
                    Then select passages to create records.
                </p>
            </div>

            <div class="flex shrink-0 flex-nowrap items-center gap-2">
                <button type="button"
                        @click="toggleSearchPanel()"
                        :disabled="busy"
                        class="inline-flex shrink-0 items-center justify-center whitespace-nowrap rounded bg-slate-700 px-3 py-1.5 text-sm text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50">
                    Search Google
                </button>

                <button type="button"
                        @click="startFetch()"
                        :disabled="busy"
                        class="inline-flex shrink-0 items-center justify-center whitespace-nowrap rounded bg-slate-700 px-3 py-1.5 text-sm text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50">
                    Import from URL
                </button>

                <button type="button"
                        @click="startManual()"
                        :disabled="busy"
                        class="inline-flex shrink-0 items-center justify-center whitespace-nowrap rounded bg-blue-600 px-3 py-1.5 text-sm text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">
                    Paste Text
                </button>
                <button type="button"
                        @click="startReference()"
                        :disabled="busy"
                        class="inline-flex shrink-0 items-center justify-center whitespace-nowrap rounded bg-gray-200 px-3 py-1.5 text-sm text-gray-800 hover:bg-gray-300 disabled:cursor-not-allowed disabled:opacity-50">
                    Add Reference
                </button>
            </div>
        </div>
    </div>

    <div x-show="
        error ||
        success ||
        showSearchPanel ||
        showFetchPanel ||
        sourceDraft ||
        actionDraft
     "
     x-cloak
     class="space-y-4 p-4 sm:p-6">

        {{-- Messages --}}
        <p x-show="error"
           x-cloak
           x-text="error"
           role="alert"
           class="rounded border border-red-200 bg-red-50 p-3 text-sm text-red-800">
        </p>

        <p x-show="success"
           x-cloak
           x-text="success"
           role="status"
           class="rounded border border-green-200 bg-green-50 p-3 text-sm text-green-800">
        </p>
        
        {{-- Search Google --}}
        <div x-show="showSearchPanel"
            x-cloak
            class="rounded-lg border border-gray-200 bg-gray-50 p-4">

            <form method="GET"
                action="https://www.google.com/search"
                target="_blank"
                rel="noopener noreferrer">

                <label for="research-web-topic"
                    class="mb-1 block text-sm font-medium text-gray-700">
                    Topic or research question
                </label>

                <div class="overflow-x-auto">
                    <div class="flex min-w-max flex-nowrap items-center gap-2">
                        <input id="research-web-topic"
                            type="text"
                            name="q"
                            x-model="webTopic"
                            maxlength="500"
                            required
                            placeholder="Enter the topic you want to research"
                            class="min-w-48 flex-1 rounded-md border-gray-300 text-sm">

                        <button type="submit"
                                class="inline-flex shrink-0 items-center whitespace-nowrap rounded bg-slate-700 px-4 py-2 text-sm text-white hover:bg-slate-800">
                            Open Google Results
                        </button>

                        <button type="button"
                                @click="showSearchPanel = false"
                                class="inline-flex shrink-0 items-center whitespace-nowrap rounded bg-gray-200 px-3 py-2 text-sm text-gray-700 hover:bg-gray-300">
                            Close
                        </button>
                    </div>
                </div>
            </form>

            <p class="mt-2 text-xs text-gray-600">
                Choose a page in the results and copy its address.
                Return here and select Import from URL.
            </p>
        </div>   

        {{-- Import from URL --}}
        <div x-show="showFetchPanel"
            x-cloak
            class="rounded-lg border border-gray-200 bg-gray-50 p-4">

            <form method="POST"
                action="{{ route('knowledge.items.sources.fetch', $knowledgeItem) }}">
                @csrf

                <label for="research-fetch-url"
                    class="mb-1 block text-sm font-medium text-gray-700">
                    Address of the webpage to import
                </label>

                <div class="overflow-x-auto">
                    <div class="flex min-w-max flex-nowrap items-center gap-2">
                        <input id="research-fetch-url"
                            type="url"
                            name="fetch_url"
                            x-model="fetchUrl"
                            maxlength="255"
                            placeholder="https://"
                            required
                            class="min-w-48 flex-1 rounded-md border-gray-300 text-sm">

                        <button type="submit"
                                class="inline-flex shrink-0 items-center whitespace-nowrap rounded bg-slate-700 px-4 py-2 text-sm text-white hover:bg-slate-800">
                            Load Page Text
                        </button>

                        <button type="button"
                                @click="showFetchPanel = false"
                                class="inline-flex shrink-0 items-center whitespace-nowrap rounded bg-gray-200 px-3 py-2 text-sm text-gray-700 hover:bg-gray-300">
                            Close
                        </button>
                    </div>
                </div>
            </form>

            <p class="mt-2 text-xs text-gray-600">
                Review the imported text below, then save it as a source.
            </p>
        </div>

        {{-- Imported content editor --}}
        <template x-if="sourceDraft">
            <div class="space-y-4 rounded-lg border border-blue-200 bg-blue-50 p-4">

                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h4 class="text-sm font-semibold text-blue-900"
                        x-text="sourceDraft.id
                            ? 'Saved Source Content'
                            : 'New Source — Review Before Saving'"
                    </h4>

                    <p class="mt-1 text-sm text-gray-500">
                        Search, import a webpage, or paste text.
                        Then select passages to create records.
                    </p>
                </div>

                

                <fieldset :disabled="busy || !!actionDraft"
                          class="space-y-4">

                    <div>
                        <label for="research-question"
                               class="mb-1 block text-sm font-medium text-gray-700">
                            Research topic / question
                        </label>

                        <input id="research-question"
                               type="text"
                               x-model="sourceDraft.researchquery"
                               maxlength="500"
                               placeholder="The topic or question researched"
                               class="w-full rounded-md border-gray-300 text-sm">
                    </div>

                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                        <div class="md:col-span-2">
                            <label for="research-source-title"
                                   class="mb-1 block text-sm font-medium text-gray-700">
                                Source title
                            </label>

                            <input id="research-source-title"
                                   type="text"
                                   x-model="sourceDraft.sourcetitle"
                                   maxlength="255"
                                   placeholder="A title identifying this research"
                                   class="w-full rounded-md border-gray-300 text-sm">
                        </div>

                        <div>
                            <label for="research-source-type"
                                   class="mb-1 block text-sm font-medium text-gray-700">
                                Source type
                            </label>

                            <select id="research-source-type"
                                    x-model="sourceDraft.sourcetype"
                                    class="w-full rounded-md border-gray-300 text-sm">
                                <template
                                    x-for="[value, label] in Object.entries(sourceTypes)"
                                    :key="value">
                                    <option :value="value"
                                            x-text="label">
                                    </option>
                                </template>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label for="research-source-url"
                                   class="mb-1 block text-sm font-medium text-gray-700">
                                Source URL — optional
                            </label>

                            <input id="research-source-url"
                                   type="url"
                                   x-model="sourceDraft.sourceurl"
                                   maxlength="255"
                                   placeholder="https://"
                                   class="w-full rounded-md border-gray-300 text-sm">
                        </div>

                        <div>
                            <label for="research-source-publisher"
                                   class="mb-1 block text-sm font-medium text-gray-700">
                                Publisher / origin
                            </label>

                            <input id="research-source-publisher"
                                   type="text"
                                   x-model="sourceDraft.sourcepublisher"
                                   maxlength="150"
                                   placeholder="Perplexity Desktop"
                                   class="w-full rounded-md border-gray-300 text-sm">
                        </div>

                        <div>
                            <label for="research-retrieved-on"
                                   class="mb-1 block text-sm font-medium text-gray-700">
                                Retrieved on
                            </label>

                            <input id="research-retrieved-on"
                                   type="date"
                                   x-model="sourceDraft.retrievedon"
                                   class="w-full rounded-md border-gray-300 text-sm">
                        </div>

                        <div>
                            <label for="research-content-format"
                                   class="mb-1 block text-sm font-medium text-gray-700">
                                Content format
                            </label>

                            <select id="research-content-format"
                                    x-model="sourceDraft.contentformat"
                                    class="w-full rounded-md border-gray-300 text-sm">
                                <option value="markdown">Markdown</option>
                                <option value="text">Plain text</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="research-content"
                               class="mb-1 block text-sm font-medium text-gray-700">
                            Research text — select a passage after savingImported research content
                        </label>

                        <textarea id="research-content"
                                  x-model="sourceDraft.rawcontent"
                                  @select="captureSelection($event.target)"
                                  @keyup="captureSelection($event.target)"
                                  @mouseup="captureSelection($event.target)"
                                  @input="selection = ''"
                                  rows="16"
                                  placeholder="Paste the research answer or document text here, including references you want to retain."
                                  class="w-full rounded-md border-gray-300 font-mono text-sm">
                        </textarea>

                        <p class="mt-1 text-xs text-gray-600">
                            Save the source first, then select a passage in this
                            editor. The selection retains the original text and
                            Markdown characters.
                        </p>
                    </div>
                </fieldset>

                <div class="flex flex-wrap items-center gap-3">
                    <button type="button"
                            @click="saveSource()"
                            :disabled="busy || !!actionDraft"
                            class="rounded bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50">
                            <span x-text="busy
                                ? 'Please wait…'
                                : 'Save Source'">
                            </span>
                    </button>

                    <p class="text-xs text-gray-600">
                        Saving content marks the source pending review.
                    </p>
                </div>

                {{-- Selection actions --}}
                <div class="flex flex-nowrap items-center gap-2 overflow-x-auto border-t border-blue-200 pt-3">
                    <button type="button"
                            @mousedown.prevent
                            @click="prepareAction('note')"
                            :disabled="busy || !!actionDraft || !canApply()"
                            class="shrink-0 whitespace-nowrap rounded bg-blue-600 px-3 py-2 text-sm text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">
                        Create Note
                    </button>

                    <button type="button"
                            @mousedown.prevent
                            @click="prepareAction('review')"
                            :disabled="busy || !!actionDraft || !canApply()"
                            class="shrink-0 whitespace-nowrap rounded bg-indigo-600 px-3 py-2 text-sm text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50">
                        Create Review Log
                    </button>

                    <button type="button"
                            @mousedown.prevent
                            @click="prepareAction('detailednotes')"
                            :disabled="busy || !!actionDraft || !canApply()"
                            class="shrink-0 whitespace-nowrap rounded bg-slate-700 px-3 py-2 text-sm text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50">
                        Add to Detailed Notes
                    </button>

                    <span class="text-xs text-gray-600"
                          x-text="selection.length + ' characters selected'">
                    </span>
                </div>
            </div>
        </template>

        

        {{-- Review the selected passage before saving --}}
        <template x-if="actionDraft">
            <form @submit.prevent="applySelection()"
                  class="rounded-lg border border-indigo-200 bg-indigo-50 p-4">

                <fieldset :disabled="busy" class="space-y-4">
                    <h4 class="text-sm font-semibold text-indigo-900"
                        x-text="actionLabel()">
                    </h4>

                    <div x-show="actionDraft.target !== 'review'">
                        <label for="research-action-title"
                               class="mb-1 block text-sm font-medium text-gray-700"
                               x-text="actionDraft.target === 'note'
                                   ? 'Note title — optional'
                                   : 'Heading for addition — optional'">
                        </label>

                        <input id="research-action-title"
                               type="text"
                               x-model="actionDraft.title"
                               maxlength="255"
                               class="w-full rounded-md border-gray-300 text-sm">
                    </div>

                    <div x-show="actionDraft.target === 'note'"
                         class="flex flex-wrap items-end gap-4">

                        <div>
                            <label for="research-note-type"
                                   class="mb-1 block text-sm font-medium text-gray-700">
                                Note type
                            </label>

                            <select id="research-note-type"
                                    x-model="actionDraft.notetype"
                                    class="rounded-md border-gray-300 text-sm">
                                <template
                                    x-for="[value, label] in Object.entries(noteTypes)"
                                    :key="value">
                                    <option :value="value"
                                            x-text="label">
                                    </option>
                                </template>
                            </select>
                        </div>

                        <label class="inline-flex items-center gap-2 pb-2 text-sm text-gray-700">
                            <input type="checkbox"
                                   x-model="actionDraft.isprivate"
                                   class="rounded border-gray-300 text-blue-600">
                            Private
                        </label>
                    </div>

                    <div x-show="actionDraft.target === 'review'"
                         class="grid grid-cols-1 gap-3 md:grid-cols-2">

                        <div>
                            <label for="research-review-date"
                                   class="mb-1 block text-sm font-medium text-gray-700">
                                Review date
                            </label>

                            <input id="research-review-date"
                                   type="date"
                                   x-model="actionDraft.reviewdate"
                                   class="w-full rounded-md border-gray-300 text-sm">
                        </div>

                        <div>
                            <label for="research-review-type"
                                   class="mb-1 block text-sm font-medium text-gray-700">
                                Review type
                            </label>

                            <select id="research-review-type"
                                    x-model="actionDraft.reviewtype"
                                    class="w-full rounded-md border-gray-300 text-sm">
                                <template
                                    x-for="[value, label] in Object.entries(reviewTypes)"
                                    :key="value">
                                    <option :value="value"
                                            x-text="label">
                                    </option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label for="research-review-outcome"
                                   class="mb-1 block text-sm font-medium text-gray-700">
                                Outcome — optional
                            </label>

                            <input id="research-review-outcome"
                                   type="text"
                                   x-model="actionDraft.outcome"
                                   maxlength="50"
                                   class="w-full rounded-md border-gray-300 text-sm">
                        </div>

                        <div>
                            <label for="research-next-review-date"
                                   class="mb-1 block text-sm font-medium text-gray-700">
                                Next review date — optional
                            </label>

                            <input id="research-next-review-date"
                                   type="date"
                                   x-model="actionDraft.nextreviewdate"
                                   class="w-full rounded-md border-gray-300 text-sm">
                        </div>
                    </div>

                    <div>
                        <label for="research-selected-content"
                               class="mb-1 block text-sm font-medium text-gray-700">
                            Selected content — edit before saving
                        </label>

                        <textarea id="research-selected-content"
                                  x-model="actionDraft.content"
                                  rows="10"
                                  class="w-full rounded-md border-gray-300 font-mono text-sm">
                        </textarea>
                    </div>

                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox"
                               x-model="actionDraft.include_source"
                               class="rounded border-gray-300 text-blue-600">
                        Include source attribution in the saved text
                    </label>

                    <details x-show="actionDraft.target === 'detailednotes'">
                        <summary class="cursor-pointer text-sm text-gray-700">
                            Show existing Detailed Notes
                        </summary>

                        <pre x-text="existingDetailedNotes || '(Empty)'"
                             class="mt-2 max-h-72 overflow-auto whitespace-pre-wrap break-words rounded bg-white p-3 font-mono text-xs"></pre>

                        <p class="mt-2 text-xs text-gray-600">
                            Existing text is retained. Your selected content
                            and optional heading will be appended.
                        </p>
                    </details>

                    <div class="flex justify-end gap-2 border-t border-indigo-200 pt-4">
                        <button type="button"
                                @click="cancelAction()"
                                class="rounded border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50">
                            Cancel
                        </button>

                        <button type="submit"
                                class="rounded bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-700 disabled:opacity-50"
                                x-text="busy ? 'Saving…' : actionLabel()">
                        </button>
                    </div>
                </fieldset>
            </form>
        </template>
    </div>
</div>

<style>
    [x-cloak] {
        display: none !important;
    }
</style>

<script>
    function knowledgeResearchWorkspace() {
        return {
            baseUrl: '',
            csrf: '',
            today: '',
            defaultTopic: '',

            showSearchPanel: false,
            showFetchPanel: false,
            webTopic: '',
            fetchUrl: '',

            sources: [],
            sourceTypes: {},
            noteTypes: {},
            reviewTypes: {},

            busy: false,
            error: '',
            success: '',
            selectedId: '',

            sourceDraft: null,
            baseline: '',
            selection: '',

            actionDraft: null,
            existingDetailedNotes: '',
            unloadHandler: null,

            init() {
                try {
                    const element = document.getElementById(
                        'knowledge-research-data'
                    );

                    const data = JSON.parse(element.textContent);

                    this.baseUrl = data.baseUrl;
                    this.csrf = data.csrf;
                    this.today = data.today;
                    this.defaultTopic = data.defaultTopic || '';

                    this.webTopic = this.defaultTopic;
                    this.fetchUrl = data.fetchUrl || '';
                    this.showSearchPanel = false;
                    this.showFetchPanel = Boolean(data.openFetchPanel);

                    this.sources = data.sources || [];
                    this.sourceTypes = data.sourceTypes || {};
                    this.noteTypes = data.noteTypes || {};
                    this.reviewTypes = data.reviewTypes || {};

                    if (data.fetchedDraft) {
                        this.setDraft(data.fetchedDraft);
                        this.showSearchPanel = false;
                        this.showFetchPanel = false;
                    }

                    this.unloadHandler = event => {
                        if (!this.sourceDirty() && !this.actionDraft) {
                            return;
                        }

                        event.preventDefault();
                        event.returnValue = '';
                    };

                    window.addEventListener(
                        'beforeunload',
                        this.unloadHandler
                    );
                } catch (error) {
                    this.error =
                        'Unable to initialise the research workspace.';

                    console.error(
                        'Knowledge research initialisation failed:',
                        error
                    );
                }
            },

            destroy() {
                if (this.unloadHandler) {
                    window.removeEventListener(
                        'beforeunload',
                        this.unloadHandler
                    );
                }
            },

            sourceDirty() {
                return !!this.sourceDraft
                    && JSON.stringify(this.sourceDraft) !== this.baseline;
            },

            canApply() {
                return !!this.sourceDraft?.id
                    && !this.sourceDirty()
                    && !!this.selection.trim();
            },

            confirmDiscard() {
                if (!this.sourceDirty() && !this.actionDraft) {
                    return true;
                }

                return window.confirm(
                    'Discard the unsaved source changes or selection draft?'
                );
            },

            clearMessages() {
                this.error = '';
                this.success = '';
            },

            setDraft(draft, saved = false) {
                this.sourceDraft = {
                    id: draft.id ?? null,
                    version: draft.version ?? null,
                    sourcetype: draft.sourcetype || 'document',
                    sourcetitle: draft.sourcetitle || '',
                    sourceurl: draft.sourceurl || '',
                    sourcepublisher: draft.sourcepublisher || '',
                    retrievedon: draft.retrievedon || '',
                    rawcontent: draft.rawcontent || '',
                    contentformat: draft.contentformat || 'markdown',
                    researchquery: draft.researchquery || '',
                };

                this.baseline = saved
                    ? JSON.stringify(this.sourceDraft)
                    : '';

                this.selection = '';
                this.actionDraft = null;
                this.existingDetailedNotes = '';
            },

            emptySource() {
                return {
                    id: null,
                    version: null,
                    sourcetype: 'document',
                    sourcetitle: this.defaultTopic,
                    sourceurl: '',
                    sourcepublisher: 'Perplexity Desktop',
                    retrievedon: this.today,
                    rawcontent: '',
                    contentformat: 'markdown',
                    researchquery: this.defaultTopic,
                };
            },

            toggleSearchPanel() {
                if (this.busy) {
                    return;
                }

                this.showSearchPanel = !this.showSearchPanel;
                this.showFetchPanel = false;

                if (this.showSearchPanel) {
                    this.$nextTick(() => {
                        document.getElementById(
                            'research-web-topic'
                        )?.focus();
                    });
                }
            },

            startFetch() {
                if (this.busy || !this.confirmDiscard()) {
                    return;
                }

                this.clearMessages();

                this.sourceDraft = null;
                this.baseline = '';
                this.selection = '';
                this.actionDraft = null;
                this.existingDetailedNotes = '';
                this.selectedId = '';

                this.showSearchPanel = false;
                this.showFetchPanel = true;

                this.$nextTick(() => {
                    document.getElementById(
                        'research-fetch-url'
                    )?.focus();
                });
            },
  
            startReference() {
                if (this.busy || !this.confirmDiscard()) {
                    return;
                }

                this.clearMessages();

                this.sourceDraft = null;
                this.baseline = '';
                this.selection = '';
                this.actionDraft = null;
                this.existingDetailedNotes = '';
                this.selectedId = '';

                this.showSearchPanel = false;
                this.showFetchPanel = false;

                this.$dispatch('knowledge-source-add-reference');
            },

            startManual() {
                if (this.busy || !this.confirmDiscard()) {
                    return;
                }

                this.clearMessages();
                this.selectedId = '';

                this.showSearchPanel = false;
                this.showFetchPanel = false;

                this.setDraft(this.emptySource());

                this.$nextTick(() => {
                    document.getElementById(
                        'research-content'
                    )?.focus();
                });
            },

            async request(url, method = 'GET', body = null) {
                const options = {
                    method,
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                    },
                };

                if (body !== null) {
                    options.body = JSON.stringify(body);
                }

                const response = await fetch(url, options);

                const data = await response.json().catch(() => null);

                if (!response.ok) {
                    const message = data?.errors
                        ? Object.values(data.errors).flat().join(' ')
                        : data?.message;

                    throw new Error(
                        message
                        || `Request failed (${response.status}). Your draft has been retained.`
                    );
                }

                if (data === null) {
                    throw new Error(
                        'The server returned an unexpected response. Your draft has been retained.'
                    );
                }

                return data;
            },

            async openSource(sourceId = this.selectedId) {
                if (
                    this.busy
                    || !sourceId
                    || !this.confirmDiscard()
                ) {
                    return;
                }

                this.busy = true;
                this.clearMessages();

                try {
                    const data = await this.request(
                        `${this.baseUrl}/sources/${sourceId}`
                    );

                    this.setDraft(data.source, true);
                    this.selectedId = String(data.source.id);

                    this.showSearchPanel = false;
                    this.showFetchPanel = false;

                    this.$nextTick(() => {
                        document.getElementById(
                            'knowledge-research-workspace'
                        )?.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start',
                        });
                    });
                } catch (error) {
                    this.error = error.message;
                } finally {
                    this.busy = false;
                }
            },
            async saveSource() {
                if (this.busy || !this.sourceDraft) {
                    return;
                }

                if (this.actionDraft) {
                    this.error =
                        'Save or cancel the selection draft first.';
                    return;
                }

                this.clearMessages();

                if (
                    !this.sourceDraft.sourcetitle.trim()
                    || !this.sourceDraft.rawcontent.trim()
                ) {
                    this.error =
                        'A source title and research content are required.';
                    return;
                }

                this.busy = true;

                const id = this.sourceDraft.id;

                try {
                    const body = {
                        ...this.sourceDraft,
                        sourceurl: this.sourceDraft.sourceurl || null,
                        sourcepublisher:
                            this.sourceDraft.sourcepublisher || null,
                        retrievedon:
                            this.sourceDraft.retrievedon || null,
                        researchquery:
                            this.sourceDraft.researchquery || null,
                    };

                    const data = await this.request(
                        `${this.baseUrl}/sources${id ? '/' + id : ''}`,
                        id ? 'PUT' : 'POST',
                        body
                    );

                    this.setDraft(data.source, true);

                    const row = {
                        id: data.source.id,
                        title: data.source.sourcetitle,
                    };

                    const index = this.sources.findIndex(
                        source => String(source.id) === String(row.id)
                    );

                    if (index === -1) {
                        this.sources.push(row);
                    } else {
                        this.sources.splice(index, 1, row);
                    }

                    this.sources.sort(
                        (left, right) =>
                            left.title.localeCompare(right.title)
                    );

                    this.selectedId = String(row.id);

                    this.success =
                        'Source content saved. Select a passage to create a Note, review log, or Detailed Notes addition.';
                } catch (error) {
                    this.error = error.message;
                } finally {
                    this.busy = false;
                }
            },

            captureSelection(input) {
                if (!input || this.busy || this.actionDraft) {
                    return;
                }

                this.selection = input.value.slice(
                    input.selectionStart,
                    input.selectionEnd
                );
            },

            async prepareAction(target) {
                if (
                    this.busy
                    || this.actionDraft
                    || !this.canApply()
                ) {
                    return;
                }

                this.clearMessages();
                this.busy = true;

                try {
                    let expectedHash = null;

                    if (target === 'detailednotes') {
                        const data = await this.request(
                            `${this.baseUrl}/detailed-notes`
                        );

                        this.existingDetailedNotes = data.content;
                        expectedHash = data.hash;
                    }

                    this.actionDraft = {
                        target,
                        sourceId: this.sourceDraft.id,
                        content: this.selection,
                        title: target === 'note'
                            ? (this.sourceDraft.sourcetitle || '')
                            : '',
                        include_source: true,
                        notetype: 'research',
                        isprivate: true,
                        reviewtype: 'deep-dive',
                        reviewdate: this.today,
                        outcome: '',
                        nextreviewdate: '',
                        expected_hash: expectedHash,
                    };
                } catch (error) {
                    this.error = error.message;
                } finally {
                    this.busy = false;
                }
            },

            actionLabel() {
                const labels = {
                    note: 'Create Note',
                    review: 'Create Review Log',
                    detailednotes: 'Add to Detailed Notes',
                };

                return labels[this.actionDraft?.target] || '';
            },

            cancelAction() {
                if (this.busy) {
                    return;
                }

                if (window.confirm('Discard this selection draft?')) {
                    this.actionDraft = null;
                }
            },

            async applySelection() {
                if (this.busy || !this.actionDraft) {
                    return;
                }

                this.clearMessages();

                if (this.sourceDirty()) {
                    this.error =
                        'The source has unsaved changes. Cancel the selection draft and save the source first.';
                    return;
                }

                if (!this.actionDraft.content.trim()) {
                    this.error = 'Enter some selected content.';
                    return;
                }

                if (
                    this.actionDraft.target === 'review'
                    && (
                        !this.actionDraft.reviewdate
                        || !this.actionDraft.reviewtype
                    )
                ) {
                    this.error =
                        'Review date and review type are required.';
                    return;
                }

                this.busy = true;

                try {
                    const body = {
                        ...this.actionDraft,
                        outcome: this.actionDraft.outcome || null,
                        nextreviewdate:
                            this.actionDraft.nextreviewdate || null,
                    };

                    const data = await this.request(
                        `${this.baseUrl}/sources/${this.actionDraft.sourceId}/apply`,
                        'POST',
                        body
                    );

                    this.actionDraft = null;
                    this.selection = '';
                    this.existingDetailedNotes = '';

                    this.success =
                        data.message
                        + ' Select another passage, or open the destination tab to view it.';
                } catch (error) {
                    this.error =
                        error.message
                        + ' If a connection failed after submission, check the destination before retrying to avoid duplicates.';
                } finally {
                    this.busy = false;
                }
            },
        };
    }
</script>