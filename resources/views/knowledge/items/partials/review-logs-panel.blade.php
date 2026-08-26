{{-- resources/views/knowledge/items/partials/review-logs-panel.blade.php --}}

@php
    $reviewLogPayload = $knowledgeItem->reviewLogs
        ->sortByDesc('reviewdate')
        ->values()
        ->map(function ($log) {
            return [
                'id' => $log->id,
                'reviewdate' => $log->reviewdate?->format('Y-m-d'),
                'reviewdate_display' => $log->reviewdate?->format('d M Y'),
                'reviewtype' => $log->reviewtype,
                'reviewtype_label' => \App\Models\KnowledgeReviewLog::typeOptions()[$log->reviewtype]
                    ?? $log->reviewtype
                    ?? 'Review',
                'outcome' => $log->outcome ?? '',
                'summary' => $log->summary ?? '',
                'summary_html' => app(\Illuminate\Mail\Markdown::class)
                    ->parse($log->summary ?? '')
                    ->toHtml(),
                'nextreviewdate' => $log->nextreviewdate?->format('Y-m-d'),
                'nextreviewdate_display' => $log->nextreviewdate?->format('d M Y'),
            ];
        });
@endphp

<script id="review-logs-initial-data" type="application/json">
{!! json_encode([
    'reviewLogs' => $reviewLogPayload,
    'storeUrl' => route('knowledge.items.review-logs.store', $knowledgeItem),
    'baseUrl' => url('/knowledge/items/'.$knowledgeItem->id.'/review-logs'),
    'csrfToken' => csrf_token(),
    'reviewTypes' => $reviewTypeOptions ?? [],
    'knowledgeItemNextReviewDate' => $knowledgeItem->nextreviewdate?->format('Y-m-d'),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
</script>

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
     x-data="reviewLogsPanel()">

    <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Review Logs</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Record review activity, outcomes, and planned follow-up dates.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-medium">
                    <span x-text="reviewLogs.length"></span> total
                </span>

                <button type="button"
                        @click="startNewLog()"
                        class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                    + Add Review Log
                </button>
            </div>
        </div>
    </div>

    <template x-if="errorMessage">
        <div class="mx-4 sm:mx-6 mt-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
             x-text="errorMessage">
        </div>
    </template>

    <template x-if="knowledgeItemNextReviewDate">
        <div class="mx-4 sm:mx-6 mt-4 rounded-md border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
            Current knowledge-item next review date:
            <span class="font-semibold" x-text="formatDate(knowledgeItemNextReviewDate)"></span>
        </div>
    </template>

    {{-- New review-log editor --}}
    <template x-if="newLog">
        <div class="m-4 sm:m-6 rounded-lg border border-blue-200 bg-blue-50 p-4 sm:p-5">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h4 class="text-sm font-semibold text-blue-900">Add Review Log</h4>

                <button type="button"
                        @click="cancelNewLog()"
                        class="inline-flex items-center px-3 py-1.5 bg-white text-gray-700 border border-gray-300 rounded text-xs hover:bg-gray-50">
                    Cancel
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Review date
                    </label>
                    <input type="date"
                           x-model="newLog.reviewdate"
                           class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                           required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Review type
                    </label>
                    <select x-model="newLog.reviewtype"
                            class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                            required>
                        <option value="">Select review type</option>
                        <template x-for="[value, label] in Object.entries(reviewTypes)" :key="value">
                            <option :value="value" x-text="label"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Outcome
                    </label>
                    <input type="text"
                           x-model="newLog.outcome"
                           maxlength="50"
                           placeholder="unchanged, updated, archived"
                           class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Summary
                </label>
                <textarea x-model="newLog.summary"
                          rows="5"
                          placeholder="Review summary, findings, and reasoning. Markdown is supported."
                          class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                </textarea>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Next review date
                    </label>
                    <input type="date"
                           x-model="newLog.nextreviewdate"
                           class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                </div>
            </div>

            <div class="mt-5 flex justify-end gap-2">
                <button type="button"
                        @click="cancelNewLog()"
                        class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded text-sm hover:bg-gray-300">
                    Cancel
                </button>

                <button type="button"
                        @click="saveNewLog()"
                        :disabled="saving"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">
                    <span x-show="!saving">Save Review Log</span>
                    <span x-show="saving">Saving…</span>
                </button>
            </div>
        </div>
    </template>

    <div class="divide-y divide-gray-200">
        <template x-for="log in reviewLogs" :key="log.id">
            <div class="p-4 sm:p-6">
                {{-- Display mode --}}
                <template x-if="!log.editing">
                    <div>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-semibold text-gray-900"
                                          x-text="`${log.reviewtype_label} on ${log.reviewdate_display}`">
                                    </span>

                                    <template x-if="log.outcome">
                                        <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 border border-green-200"
                                              x-text="log.outcome">
                                        </span>
                                    </template>
                                </div>

                                <template x-if="log.nextreviewdate">
                                    <p class="mt-1 text-xs text-gray-500">
                                        Next review:
                                        <span x-text="log.nextreviewdate_display"></span>
                                    </p>
                                </template>

                                <template x-if="log.summary_html">
                                    <div class="mt-3 markdown-content prose prose-sm max-w-none text-gray-700"
                                         x-html="log.summary_html">
                                    </div>
                                </template>
                            </div>

                            <div class="flex shrink-0 items-center gap-2">
                                <button type="button"
                                        @click="startEditLog(log)"
                                        class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded text-xs hover:bg-gray-300">
                                    Edit
                                </button>

                                <button type="button"
                                        @click="deleteLog(log)"
                                        class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white rounded text-xs hover:bg-red-700">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Inline edit mode --}}
                <template x-if="log.editing">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <h4 class="text-sm font-semibold text-gray-900">
                                Edit Review Log
                            </h4>

                            <button type="button"
                                    @click="cancelEditLog(log)"
                                    class="inline-flex items-center px-3 py-1.5 bg-white text-gray-700 border border-gray-300 rounded text-xs hover:bg-gray-50">
                                Cancel
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Review date
                                </label>
                                <input type="date"
                                       x-model="log.draft.reviewdate"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                       required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Review type
                                </label>
                                <select x-model="log.draft.reviewtype"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                        required>
                                    <option value="">Select review type</option>
                                    <template x-for="[value, label] in Object.entries(reviewTypes)" :key="value">
                                        <option :value="value" x-text="label"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Outcome
                                </label>
                                <input type="text"
                                       x-model="log.draft.outcome"
                                       maxlength="50"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Summary
                            </label>
                            <textarea x-model="log.draft.summary"
                                      rows="5"
                                      class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </textarea>
                        </div>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Next review date
                                </label>
                                <input type="date"
                                       x-model="log.draft.nextreviewdate"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>
                        </div>

                        <div class="mt-5 flex justify-end gap-2">
                            <button type="button"
                                    @click="cancelEditLog(log)"
                                    class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded text-sm hover:bg-gray-300">
                                Cancel
                            </button>

                            <button type="button"
                                    @click="saveExistingLog(log)"
                                    :disabled="saving"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded text-sm hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60">
                                <span x-show="!saving">Save Review Log</span>
                                <span x-show="saving">Saving…</span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <template x-if="reviewLogs.length === 0 && !newLog">
            <div class="p-6 text-sm text-gray-500">
                No review logs recorded for this knowledge item yet.
            </div>
        </template>
    </div>
</div>

@include('partials.markdown.markdown-styles')

<script>
    function reviewLogsPanel() {
        return {
            reviewLogs: [],
            reviewTypes: {},
            storeUrl: '',
            baseUrl: '',
            csrfToken: '',
            knowledgeItemNextReviewDate: null,
            newLog: null,
            saving: false,
            errorMessage: '',

            init() {
                try {
                    const dataElement = document.getElementById('review-logs-initial-data');
                    const data = JSON.parse(dataElement.textContent);

                    this.reviewLogs = (data.reviewLogs || []).map(log => this.prepareLog(log));
                    this.reviewTypes = data.reviewTypes || {};
                    this.storeUrl = data.storeUrl;
                    this.baseUrl = data.baseUrl;
                    this.csrfToken = data.csrfToken;
                    this.knowledgeItemNextReviewDate = data.knowledgeItemNextReviewDate || null;
                } catch (error) {
                    this.errorMessage = error.message || 'Unable to load review logs.';
                    console.error('Review logs initialisation failed:', error);
                }
            },

            prepareLog(log) {
                return {
                    ...log,
                    editing: false,
                    draft: null,
                };
            },

            emptyDraft() {
                return {
                    reviewdate: new Date().toISOString().slice(0, 10),
                    reviewtype: '',
                    outcome: '',
                    summary: '',
                    nextreviewdate: '',
                };
            },

            startNewLog() {
                this.errorMessage = '';
                this.closeAllEditors();
                this.newLog = this.emptyDraft();
            },

            cancelNewLog() {
                this.newLog = null;
            },

            startEditLog(log) {
                this.errorMessage = '';
                this.newLog = null;
                this.closeAllEditors();

                log.draft = {
                    reviewdate: log.reviewdate || '',
                    reviewtype: log.reviewtype || '',
                    outcome: log.outcome || '',
                    summary: log.summary || '',
                    nextreviewdate: log.nextreviewdate || '',
                };

                log.editing = true;
            },

            cancelEditLog(log) {
                log.editing = false;
                log.draft = null;
            },

            closeAllEditors() {
                this.reviewLogs.forEach(log => {
                    log.editing = false;
                    log.draft = null;
                });
            },

            async saveNewLog() {
                if (!this.newLog) {
                    return;
                }

                await this.saveLog(this.newLog, null);
            },

            async saveExistingLog(log) {
                await this.saveLog(log.draft, log);
            },

            async saveLog(payload, existingLog) {
                this.errorMessage = '';

                if (!payload.reviewdate || !payload.reviewtype) {
                    this.errorMessage = 'Review date and review type are required.';
                    return;
                }

                this.saving = true;

                try {
                    const isNew = !existingLog;

                    const response = await fetch(
                        isNew ? this.storeUrl : `${this.baseUrl}/${existingLog.id}`,
                        {
                            method: isNew ? 'POST' : 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(payload),
                        }
                    );

                    if (!response.ok) {
                        throw new Error(await this.responseMessage(response));
                    }

                    const data = await response.json();

                    if (isNew) {
                        this.reviewLogs.unshift(this.prepareLog(data.reviewLog));
                        this.newLog = null;
                    } else {
                        const index = this.reviewLogs.findIndex(
                            log => Number(log.id) === Number(existingLog.id)
                        );

                        if (index !== -1) {
                            this.reviewLogs.splice(
                                index,
                                1,
                                this.prepareLog(data.reviewLog)
                            );
                        }
                    }

                    this.knowledgeItemNextReviewDate =
                        data.knowledgeItemNextReviewDate
                        || this.knowledgeItemNextReviewDate;

                    this.sortLogs();
                } catch (error) {
                    this.errorMessage = error.message || 'Unable to save the review log.';
                    console.error('Review log save failed:', error);
                } finally {
                    this.saving = false;
                }
            },

            async deleteLog(log) {
                if (!window.confirm('Delete this review log? This cannot be undone.')) {
                    return;
                }

                this.errorMessage = '';
                this.saving = true;

                try {
                    const response = await fetch(`${this.baseUrl}/${log.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        throw new Error(await this.responseMessage(response));
                    }

                    this.reviewLogs = this.reviewLogs.filter(
                        item => Number(item.id) !== Number(log.id)
                    );
                } catch (error) {
                    this.errorMessage = error.message || 'Unable to delete the review log.';
                    console.error('Review log delete failed:', error);
                } finally {
                    this.saving = false;
                }
            },

            sortLogs() {
                this.reviewLogs.sort((left, right) => {
                    return String(right.reviewdate || '').localeCompare(
                        String(left.reviewdate || '')
                    );
                });
            },

            formatDate(value) {
                if (!value) {
                    return '—';
                }

                const [year, month, day] = value.split('-').map(Number);

                return new Intl.DateTimeFormat('en-AU', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                }).format(new Date(year, month - 1, day));
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