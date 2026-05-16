{{-- resources/views/knowledge/items/partials/info-panel.blade.php --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-1 space-y-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-5 py-4 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">Item Summary</h3>
            </div>

            <div class="p-5 space-y-4 text-sm">
                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Primary Category</div>
                    <div class="mt-1 text-gray-900">
                        {{ $knowledgeItem->primaryCategory?->categoryname ?? '—' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Type</div>
                    <div class="mt-1 text-gray-900">
                        {{ $knowledgeItem->itemType?->typename ?? '—' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Status</div>
                    <div class="mt-1 text-gray-900">
                        {{ $knowledgeItem->itemstatus ?: '—' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Parent Item</div>
                    <div class="mt-1 text-gray-900">
                        {{ $knowledgeItem->parentItem?->itemname ?? 'None' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Featured</div>
                    <div class="mt-1 text-gray-900">
                        {{ $knowledgeItem->isfeatured ? 'Yes' : 'No' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Active</div>
                    <div class="mt-1 text-gray-900">
                        {{ $knowledgeItem->isactive ? 'Yes' : 'No' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-5 py-4 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">Dates and Review</h3>
            </div>

            <div class="p-5 space-y-4 text-sm">
                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Start Date</div>
                    <div class="mt-1 text-gray-900">
                        {{ $knowledgeItem->startdate?->format('d M Y') ?? '—' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">End Date</div>
                    <div class="mt-1 text-gray-900">
                        {{ $knowledgeItem->enddate?->format('d M Y') ?? '—' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Next Review</div>
                    <div class="mt-1 text-gray-900">
                        {{ $knowledgeItem->nextreviewdate?->format('d M Y') ?? '—' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Sort Order</div>
                    <div class="mt-1 text-gray-900">
                        {{ $knowledgeItem->sortorder ?? 0 }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="xl:col-span-2 space-y-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-5 py-4 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">Linked Records</h3>
            </div>

            <div class="p-5 space-y-4 text-sm">
                <div class="flex items-center justify-between gap-4">
                    <span class="text-gray-600">Child items</span>
                    <span class="font-medium text-gray-900">{{ $knowledgeItem->childItems->count() }}</span>
                </div>

                <div class="flex items-center justify-between gap-4">
                    <span class="text-gray-600">Notes</span>
                    <span class="font-medium text-gray-900">{{ $knowledgeItem->notes->count() }}</span>
                </div>

                <div class="flex items-center justify-between gap-4">
                    <span class="text-gray-600">Sources</span>
                    <span class="font-medium text-gray-900">{{ $knowledgeItem->sources->count() }}</span>
                </div>

                <div class="flex items-center justify-between gap-4">
                    <span class="text-gray-600">Attachments</span>
                    <span class="font-medium text-gray-900">{{ $knowledgeItem->attachments->count() }}</span>
                </div>

                <div class="flex items-center justify-between gap-4">
                    <span class="text-gray-600">Review logs</span>
                    <span class="font-medium text-gray-900">{{ $knowledgeItem->reviewLogs->count() }}</span>
                </div>

                <div class="flex items-center justify-between gap-4">
                    <span class="text-gray-600">Relationships</span>
                    <span class="font-medium text-gray-900">{{ $knowledgeItem->relationships->count() }}</span>
                </div>
            </div>
        </div>

        @include('knowledge.items.partials.info-danger-zone', [
            'knowledgeItem' => $knowledgeItem,
        ])
    </div>
</div>