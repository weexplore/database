@php
    /**
     * Required:
     * - $reminder
     * - $today
     *
     * $reminder structure:
     * - type: item_review | note_review | review_followup
     * - dueDate: Carbon date
     * - knowledgeItem: KnowledgeItem
     * - title: string
     * - detail: string|null
     * - tab: string
     */
    $dueDate = $reminder['dueDate'];
    $knowledgeItem = $reminder['knowledgeItem'];
    $daysOverdue = $dueDate->isBefore($today)
        ? $dueDate->diffInDays($today)
        : 0;
@endphp

<div class="border-b border-gray-200 py-3 last:border-b-0">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-[260px] flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex min-w-0 items-baseline gap-1.5">
                    <a
                        href="{{ route('knowledge.items.edit', [
                            'knowledgeItem' => $knowledgeItem,
                            'tab' => $reminder['tab'] ?? 'details',
                            'return_to' => request()->fullUrl(),
                        ]) }}"
                        class="shrink-0 text-sm font-semibold text-indigo-700 hover:underline"
                    >
                        {{ $knowledgeItem->itemname }}
                    </a>

                    @if (filled($reminder['summaryPreview'] ?? null))
                        <span class="min-w-0 truncate text-sm text-gray-700">
                            — {{ $reminder['summaryPreview'] }}
                        </span>
                    @endif
                </div>

                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-700">
                    {{ $reminder['title'] }}
                </span>
            </div>

            <p class="mt-1 text-xs text-gray-500">
                {{ $knowledgeItem->primaryCategory?->categoryname ?? 'Uncategorised' }}
                · {{ $knowledgeItem->itemtype ?: 'Knowledge item' }}

                @if(filled($reminder['detail']))
                    · {{ $reminder['detail'] }}
                @endif
            </p>
        </div>

        <div class="shrink-0 text-right">
            <div class="text-xs font-medium text-gray-800">
                {{ $dueDate->format('d M Y') }}
            </div>

            @if($daysOverdue > 0)
                <div class="mt-1 text-[11px] font-medium text-red-700">
                    {{ $daysOverdue }}
                    day{{ $daysOverdue === 1 ? '' : 's' }}
                    overdue
                </div>
            @elseif($dueDate->isSameDay($today))
                <div class="mt-1 text-[11px] font-medium text-amber-700">
                    Due today
                </div>
            @endif
        </div>
    </div>
</div>