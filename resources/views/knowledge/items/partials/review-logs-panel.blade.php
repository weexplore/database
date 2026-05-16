{{-- resources/views/knowledge/items/partials/review-logs-panel.blade.php --}}
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Review Logs</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Capture review activity, outcomes, and next review dates for this item.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-medium">
                    {{ $knowledgeItem->reviewLogs->count() }} total
                </span>

                @if(!($showAddReviewLog ?? false))
                    <a href="{{ route('knowledge.items.edit', [
                            'knowledgeItem' => $knowledgeItem,
                            'tab' => 'review-logs',
                            'show_add_review_log' => 1,
                        ]) }}"
                       class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                        Add Review Log
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if($showAddReviewLog ?? false)
        <div class="p-6 border-b border-gray-200 space-y-4">
            <div class="flex items-center justify-between gap-4">
                <h4 class="text-sm font-semibold text-gray-900">Add Review Log</h4>

                <a href="{{ route('knowledge.items.edit', [
                    'knowledgeItem' => $knowledgeItem,
                    'tab' => 'review-logs',
                ]) }}"
                   class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded text-sm hover:bg-gray-300">
                    Cancel
                </a>
            </div>

            <form method="POST"
                  action="{{ route('knowledge.items.review-logs.store', $knowledgeItem) }}"
                  class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="review_reviewdate" class="block text-sm font-medium text-gray-700 mb-1">
                            Review Date
                        </label>
                        <input type="date"
                               name="reviewdate"
                               id="review_reviewdate"
                               value="{{ old('reviewdate') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                               required>
                    </div>

                    <div>
                        <label for="review_reviewtype" class="block text-sm font-medium text-gray-700 mb-1">
                            Review Type
                        </label>
                        <select name="reviewtype"
                                id="review_reviewtype"
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                required>
                            <option value="">Select review type</option>
                            @foreach($reviewTypeOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('reviewtype') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="review_outcome" class="block text-sm font-medium text-gray-700 mb-1">
                            Outcome
                        </label>
                        <input type="text"
                               name="outcome"
                               id="review_outcome"
                               value="{{ old('outcome') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                               placeholder="reinforced, revised, archived">
                    </div>
                </div>

                <div>
                    <label for="review_summary" class="block text-sm font-medium text-gray-700 mb-1">
                        Summary
                    </label>
                    <textarea name="summary"
                              id="review_summary"
                              rows="3"
                              class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('summary') }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="review_nextreviewdate" class="block text-sm font-medium text-gray-700 mb-1">
                            Next Review Date
                        </label>
                        <input type="date"
                               name="nextreviewdate"
                               id="review_nextreviewdate"
                               value="{{ old('nextreviewdate') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                        Save Review Log
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="divide-y divide-gray-200">
        @forelse($knowledgeItem->reviewLogs->sortByDesc('reviewdate') as $log)
            <div class="p-4 space-y-3">
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-900">
                            {{ $log->reviewtype ?: 'Review' }} on
                            {{ $log->reviewdate?->format('d M Y') ?? '—' }}
                        </div>

                        <div class="text-xs text-gray-500">
                            Outcome: {{ $log->outcome ?: '—' }}
                            @if($log->nextreviewdate)
                                · Next review: {{ $log->nextreviewdate->format('d M Y') }}
                            @endif
                        </div>

                        @if($log->summary)
                            <div class="text-sm text-gray-700 line-clamp-2">
                                {{ $log->summary }}
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-col items-end gap-2 text-xs text-gray-500 whitespace-nowrap">
                        <div>ID: {{ $log->id }}</div>

                        <div class="flex items-center gap-2 mt-1">
                            <a href="{{ route('knowledge.items.edit', [
                                    'knowledgeItem' => $knowledgeItem,
                                    'tab' => 'review-logs',
                                    'editing_review_log_id' => $log->id,
                                ]) }}"
                               class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded text-xs hover:bg-gray-300">
                                Edit
                            </a>

                            <form method="POST"
                                  action="{{ route('knowledge.items.review-logs.destroy', [$knowledgeItem, $log]) }}"
                                  onsubmit="return confirm('Delete this review log?');">
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

                @if(isset($editingReviewLogId) && (int) $editingReviewLogId === $log->id)
                    <div class="mt-4 border-t border-gray-200 pt-4">
                        <form method="POST"
                              action="{{ route('knowledge.items.review-logs.update', [$knowledgeItem, $log]) }}"
                              class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Review Date</label>
                                    <input type="date"
                                           name="reviewdate"
                                           value="{{ optional($log->reviewdate)->format('Y-m-d') }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                           required>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Review Type</label>
                                    <select name="reviewtype"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                            required>
                                        <option value="">Select review type</option>
                                        @foreach($reviewTypeOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('reviewtype', $log->reviewtype) === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Outcome</label>
                                    <input type="text"
                                           name="outcome"
                                           value="{{ old('outcome', $log->outcome) }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Summary</label>
                                <textarea name="summary"
                                          rows="3"
                                          class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('summary', $log->summary) }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Next Review Date</label>
                                    <input type="date"
                                           name="nextreviewdate"
                                           value="{{ optional($log->nextreviewdate)->format('Y-m-d') }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('knowledge.items.edit', [
                                    'knowledgeItem' => $knowledgeItem,
                                    'tab' => 'review-logs',
                                ]) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded text-xs hover:bg-gray-300">
                                    Cancel
                                </a>

                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                    Save Review Log
                                </button>
                            </div>
                        </form>
                        <form method="POST"
                            action="{{ route('knowledge.items.review-logs.destroy', [$knowledgeItem, $knowledgeReviewLog]) }}"
                            class="inline">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200 text-xs"
                                    onclick="return confirm('Delete this review log? This cannot be undone.');">
                                Delete
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <div class="p-6 text-sm text-gray-500">
                No review logs recorded for this knowledge item yet.
            </div>
        @endforelse
    </div>
</div>