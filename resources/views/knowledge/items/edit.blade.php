{{-- resources/views/knowledge/items/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $pageTitle ?? 'Edit Knowledge Item' }}
            </h2>

            <a href="{{ route('knowledge.items.index', [
                    'domainid' => $domainId,
                    'categoryid' => $knowledgeItem->primarycategoryid,
                ]) }}"
               class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                Back to Knowledge Items
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Knowledge Item
                            </div>
                            <h3 class="mt-1 text-lg font-semibold text-gray-900 break-words">
                                {{ $knowledgeItem->itemname ?: 'Knowledge Item' }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                ID: {{ $knowledgeItem->id }}
                                · Updated: {{ optional($knowledgeItem->updatedat)->format('d M Y H:i') ?? '—' }}
                            </p>
                        </div>

                        <div class="shrink-0">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-medium">
                                {{ ucfirst($activeTab ?? 'details') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $tabs = [
                    'details' => 'Details',
                    'notes' => 'Notes',
                    'sources' => 'Sources',
                    'review-logs' => 'Review Logs',
                    'relationships' => 'Relationships',
                    'attachments' => 'Attachments',
                ];
            @endphp

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 px-4 sm:px-6">
                    <nav class="flex flex-wrap gap-2 py-3" aria-label="Knowledge item sections">
                        @foreach($tabs as $tabKey => $tabLabel)
                            <a href="{{ route('knowledge.items.edit', array_filter([
                                    'knowledgeItem' => $knowledgeItem,
                                    'tab' => $tabKey,
                                ])) }}"
                               class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium {{ ($activeTab ?? 'details') === $tabKey
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                {{ $tabLabel }}
                            </a>
                        @endforeach
                    </nav>
                </div>
            </div>

            @if(($activeTab ?? 'details') === 'details')
                <form method="POST"
                      action="{{ route('knowledge.items.update', $knowledgeItem) }}"
                      id="knowledge-item-form"
                      class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    @csrf
                    @method('PUT')

                    <div class="xl:col-span-2 space-y-6">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="mt-1 text-sm text-gray-500">
                                            Update the core item details, notes, review settings, and relationships.
                                        </p>
                                    </div>

                                    <div class="text-right text-xs text-gray-500 whitespace-nowrap">
                                        <div>ID: {{ $knowledgeItem->id }}</div>
                                        <div>Updated: {{ optional($knowledgeItem->updatedat)->format('d M Y H:i') ?? '—' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6">
                                @include('knowledge.items._form', [
                                    'knowledgeItem' => $knowledgeItem,
                                    'categories' => $categories,
                                    'parentItems' => $parentItems,
                                    'itemTypes' => $itemTypes,
                                    'itemStatusOptions' => $itemStatusOptions,
                                    'places' => $places,
                                ])
                            </div>

                            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between gap-3">
                                <p class="text-sm text-gray-500">
                                    Save after changing item details, review dates, or parent relationships.
                                </p>

                                <button type="submit"
                                        class="inline-flex items-center px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                    Save Changes
                                </button>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <a href="{{ route('knowledge.items.index', [
                                        'domainid' => $domainId,
                                        'categoryid' => $knowledgeItem->primarycategoryid,
                                    ]) }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                                    Back to Knowledge Items
                                </a>

                                <form method="POST"
                                    action="{{ route('knowledge.items.destroy', $knowledgeItem) }}"
                                    id="delete-knowledge-item-form"
                                    class="inline">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                                            onclick="return confirm('Delete knowledge item {{ addslashes($knowledgeItem->itemname ?: ('#' . $knowledgeItem->id)) }}? This cannot be undone.');">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>          
                    

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
                            </div>
                        </div>
                    </div>
                </form>
            @endif

            @if(($activeTab ?? 'details') === 'notes')
                @include('knowledge.items.partials.notes-panel', [
                    'knowledgeItem' => $knowledgeItem,
                    'editingNoteId' => $editingNoteId ?? null,
                    'showAddNote' => $showAddNote ?? false,
                    'noteTypeOptions' => $noteTypeOptions ?? [],
                ])
            @endif

            @if(($activeTab ?? 'details') === 'sources')
                @include('knowledge.items.partials.sources-panel', [
                    'knowledgeItem' => $knowledgeItem,
                    'editingSourceId' => $editingSourceId ?? null,
                    'showAddSource' => $showAddSource ?? false,
                    'sourceTypeOptions' => $sourceTypeOptions ?? [],
                ])
            @endif

            @if(($activeTab ?? 'details') === 'review-logs')
                @include('knowledge.items.partials.review-logs-panel', [
                    'knowledgeItem' => $knowledgeItem,
                    'editingReviewLogId' => $editingReviewLogId ?? null,
                    'showAddReviewLog' => $showAddReviewLog ?? false,
                    'reviewTypeOptions' => $reviewTypeOptions ?? [],
                ])
            @endif

            @if(($activeTab ?? 'details') === 'relationships')
                @include('knowledge.items.partials.relationships-panel', [
                    'knowledgeItem' => $knowledgeItem,
                    'editingRelationshipId' => $editingRelationshipId ?? null,
                    'showAddRelationship' => $showAddRelationship ?? false,
                    'relationshipItems' => $relationshipItems ?? collect(),
                    'relationshipTypeOptions' => $relationshipTypeOptions ?? [],
                ])
            @endif

            @if(($activeTab ?? 'details') === 'attachments')
                @include('knowledge.items.partials.attachments-panel', [
                    'knowledgeItem' => $knowledgeItem,
                ])
            @endif

        </div>
    </div>

    @if(($activeTab ?? 'details') === 'details')
        @include('partials.admin.dirty-form-script', [
            'formId' => 'knowledge-item-form',
            'dirtyMessage' => 'You have unsaved changes on this Knowledge Item. Continue and lose those changes?',
        ])
    @endif
</x-app-layout>