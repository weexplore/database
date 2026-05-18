<x-app-layout>
    @php
        $title = $reportTitle ?? 'Knowledge Category Report';
        $subtitle = $reportSubtitle ?? 'Compiled reference report by category';
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $title }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $subtitle }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                <button type="button"
                        onclick="window.print()"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                    Print Report
                </button>
                @if(!empty($returnTo))
                    <a href="{{ $returnTo }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                        Back
                    </a>
                @endif


            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            @include('partials.admin.flash-messages')
            @include('partials.admin.validation-summary')


            @forelse($categories as $category)
                <section class="bg-white shadow-sm sm:rounded-lg report-category-break">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">
                                    {{ $category->categoryname }}
                                </h3>

                                <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                    @if($category->domain)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                                            Domain: {{ $category->domain->domainname }}
                                        </span>
                                    @endif

                                    @if($category->parentCategory)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                                            Parent: {{ $category->parentCategory->categoryname }}
                                        </span>
                                    @endif

                                    @if($category->categorytype)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                            Type: {{ $category->categorytype }}
                                        </span>
                                    @endif

                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-green-50 text-green-700 border border-green-200">
                                        {{ $category->knowledgeItems->count() }} item(s)
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        @forelse($category->knowledgeItems as $knowledgeItem)
                            <article class="border border-gray-200 rounded-lg p-5 space-y-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <h4 class="text-lg font-semibold text-gray-900">
                                            {{ $knowledgeItem->itemname }}
                                        </h4>

                                        <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                            @if($knowledgeItem->itemType)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                                    Item Type: {{ $knowledgeItem->itemType->typename }}
                                                </span>
                                            @endif

                                            @if($knowledgeItem->itemstatus)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                                                    Status: {{ $knowledgeItem->itemstatus }}
                                                </span>
                                            @endif

                                            @if($knowledgeItem->place)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-yellow-50 text-yellow-800 border border-yellow-200">
                                                    Place: {{ $knowledgeItem->place->placename }}
                                                </span>
                                            @endif

                                            @if($knowledgeItem->nextreviewdate)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-purple-50 text-purple-700 border border-purple-200">
                                                    Next review: {{ $knowledgeItem->nextreviewdate->format('d M Y') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    @if(Route::has('knowledge.items.edit'))
                                        <a href="{{ route('knowledge.items.edit', $knowledgeItem) }}"
                                           class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-sm">
                                            Open
                                        </a>
                                    @endif
                                </div>

                                @if($knowledgeItem->summary || $knowledgeItem->detailednotes || $knowledgeItem->reviewnotes || $knowledgeItem->significance)
                                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                                        @if($knowledgeItem->summary)
                                            <div class="rounded-lg border border-gray-200 p-4">
                                                <h5 class="text-sm font-semibold text-gray-900 mb-2">Summary</h5>
                                                <div class="text-sm text-gray-700 whitespace-pre-line">{{ $knowledgeItem->summary }}</div>
                                            </div>
                                        @endif

                                        @if($knowledgeItem->significance)
                                            <div class="rounded-lg border border-gray-200 p-4">
                                                <h5 class="text-sm font-semibold text-gray-900 mb-2">Significance</h5>
                                                <div class="text-sm text-gray-700 whitespace-pre-line">{{ $knowledgeItem->significance }}</div>
                                            </div>
                                        @endif

                                        @if($knowledgeItem->detailednotes)
                                            <div class="rounded-lg border border-gray-200 p-4 xl:col-span-2">
                                                <h5 class="text-sm font-semibold text-gray-900 mb-2">Detailed Notes</h5>
                                                <div class="text-sm text-gray-700 whitespace-pre-line">{{ $knowledgeItem->detailednotes }}</div>
                                            </div>
                                        @endif

                                        @if($knowledgeItem->reviewnotes)
                                            <div class="rounded-lg border border-gray-200 p-4 xl:col-span-2">
                                                <h5 class="text-sm font-semibold text-gray-900 mb-2">Review Notes</h5>
                                                <div class="text-sm text-gray-700 whitespace-pre-line">{{ $knowledgeItem->reviewnotes }}</div>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                                    @if($knowledgeItem->notes->isNotEmpty())
                                        <div class="rounded-lg border border-gray-200 p-4 space-y-3">
                                            <h5 class="text-sm font-semibold text-gray-900">Knowledge Notes</h5>

                                            @foreach($knowledgeItem->notes as $note)
                                                <div class="border-t border-gray-100 pt-3 first:border-t-0 first:pt-0">
                                                    <div class="flex flex-wrap gap-2 text-xs mb-2">
                                                        @if($note->notetype)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                                                                {{ \App\Models\KnowledgeNote::TYPE_OPTIONS[$note->notetype] ?? $note->notetype }}
                                                            </span>
                                                        @endif

                                                        @if($note->reviewdate)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                                                                Review: {{ $note->reviewdate->format('d M Y') }}
                                                            </span>
                                                        @endif

                                                        @if($note->isprivate)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-red-50 text-red-700 border border-red-200">
                                                                Private
                                                            </span>
                                                        @endif
                                                    </div>

                                                    @if($note->title)
                                                        <div class="text-sm font-medium text-gray-800">{{ $note->title }}</div>
                                                    @endif

                                                    @if($note->notecontent)
                                                        <div class="mt-1 text-sm text-gray-700 whitespace-pre-line">{{ $note->notecontent }}</div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if($knowledgeItem->sources->isNotEmpty())
                                        <div class="rounded-lg border border-gray-200 p-4 space-y-3">
                                            <h5 class="text-sm font-semibold text-gray-900">Knowledge Sources</h5>

                                            @foreach($knowledgeItem->sources as $source)
                                                <div class="border-t border-gray-100 pt-3 first:border-t-0 first:pt-0">
                                                    <div class="flex flex-wrap gap-2 text-xs mb-2">
                                                        @if($source->sourcetype)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                                                {{ \App\Models\KnowledgeSource::TYPE_OPTIONS[$source->sourcetype] ?? $source->sourcetype }}
                                                            </span>
                                                        @endif

                                                        @if($source->retrievedon)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                                                                Retrieved: {{ $source->retrievedon->format('d M Y') }}
                                                            </span>
                                                        @endif

                                                        @if($source->reviewedon)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                                                                Reviewed: {{ $source->reviewedon->format('d M Y') }}
                                                            </span>
                                                        @endif
                                                    </div>

                                                    @if($source->sourcetitle)
                                                        <div class="text-sm font-medium text-gray-800">{{ $source->sourcetitle }}</div>
                                                    @endif

                                                    @if($source->sourcepublisher)
                                                        <div class="mt-1 text-sm text-gray-600">{{ $source->sourcepublisher }}</div>
                                                    @endif

                                                    @if($source->sourceurl)
                                                        <div class="mt-1 text-sm">
                                                            <a href="{{ $source->sourceurl }}"
                                                               target="_blank"
                                                               rel="noopener noreferrer"
                                                               class="text-blue-600 hover:underline break-all">
                                                                {{ $source->sourceurl }}
                                                            </a>
                                                        </div>
                                                    @endif

                                                    @if($source->importedsummary)
                                                        <div class="mt-2 text-sm text-gray-700 whitespace-pre-line">{{ $source->importedsummary }}</div>
                                                    @endif

                                                    @if($source->importednotes)
                                                        <div class="mt-2 text-sm text-gray-700 whitespace-pre-line">{{ $source->importednotes }}</div>
                                                    @endif

                                                    @if($source->internalnotes)
                                                        <div class="mt-2 text-sm text-gray-700 whitespace-pre-line">{{ $source->internalnotes }}</div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if($knowledgeItem->reviewLogs->isNotEmpty())
                                        <div class="rounded-lg border border-gray-200 p-4 space-y-3">
                                            <h5 class="text-sm font-semibold text-gray-900">Knowledge Review Logs</h5>

                                            @foreach($knowledgeItem->reviewLogs as $log)
                                                <div class="border-t border-gray-100 pt-3 first:border-t-0 first:pt-0">
                                                    <div class="flex flex-wrap gap-2 text-xs mb-2">
                                                        @if($log->reviewtype)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                                                {{ \App\Models\KnowledgeReviewLog::TYPE_OPTIONS[$log->reviewtype] ?? $log->reviewtype }}
                                                            </span>
                                                        @endif

                                                        @if($log->reviewdate)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                                                                {{ $log->reviewdate->format('d M Y') }}
                                                            </span>
                                                        @endif

                                                        @if($log->nextreviewdate)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-purple-50 text-purple-700 border border-purple-200">
                                                                Next: {{ $log->nextreviewdate->format('d M Y') }}
                                                            </span>
                                                        @endif
                                                    </div>

                                                    @if($log->outcome)
                                                        <div class="text-sm font-medium text-gray-800">{{ $log->outcome }}</div>
                                                    @endif

                                                    @if($log->summary)
                                                        <div class="mt-1 text-sm text-gray-700 whitespace-pre-line">{{ $log->summary }}</div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if($knowledgeItem->relationships->isNotEmpty())
                                        <div class="rounded-lg border border-gray-200 p-4 space-y-3">
                                            <h5 class="text-sm font-semibold text-gray-900">Knowledge Relationships</h5>

                                            @foreach($knowledgeItem->relationships as $relationship)
                                                <div class="border-t border-gray-100 pt-3 first:border-t-0 first:pt-0">
                                                    <div class="flex flex-wrap gap-2 text-xs mb-2">
                                                        @if($relationship->relationshiptype)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                                                {{ \App\Models\KnowledgeRelationship::TYPE_OPTIONS[$relationship->relationshiptype] ?? $relationship->relationshiptype }}
                                                            </span>
                                                        @endif

                                                        @if($relationship->effective_date)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                                                                {{ $relationship->effective_date->format('d M Y') }}
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="text-sm text-gray-700">
                                                        <span class="font-medium text-gray-800">To:</span>
                                                        @if($relationship->toItem)
                                                            {{ $relationship->toItem->primaryCategory?->categoryname ?? 'Uncategorised' }}: {{ $relationship->toItem->itemname }}
                                                        @else
                                                            Unknown item
                                                        @endif
                                                    </div>

                                                    @if($relationship->notes)
                                                        <div class="mt-1 text-sm text-gray-700 whitespace-pre-line">{{ $relationship->notes }}</div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if($knowledgeItem->attachments->isNotEmpty())
                                        <div class="rounded-lg border border-gray-200 p-4 space-y-3">
                                            <h5 class="text-sm font-semibold text-gray-900">Knowledge Attachments</h5>

                                            @foreach($knowledgeItem->attachments as $attachment)
                                                <div class="border-t border-gray-100 pt-3 first:border-t-0 first:pt-0">
                                                    <div class="flex flex-wrap gap-2 text-xs mb-2">
                                                        @if($attachment->attachmenttype)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                                                                {{ $attachment->attachmenttype }}
                                                            </span>
                                                        @endif

                                                        @if($attachment->uploadedat)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                                                                {{ $attachment->uploadedat->format('d M Y H:i') }}
                                                            </span>
                                                        @endif

                                                        @if($attachment->isprimary)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-green-50 text-green-700 border border-green-200">
                                                                Primary
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="text-sm font-medium text-gray-800">
                                                        {{ $attachment->originalfilename ?: $attachment->filename }}
                                                    </div>

                                                    @if($attachment->description)
                                                        <div class="mt-1 text-sm text-gray-700 whitespace-pre-line">{{ $attachment->description }}</div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if($knowledgeItem->bibleReferences->isNotEmpty())
                                        <div class="rounded-lg border border-gray-200 p-4 space-y-3 xl:col-span-2">
                                            <h5 class="text-sm font-semibold text-gray-900">Bible Notes</h5>

                                            @foreach($knowledgeItem->bibleReferences as $reference)
                                                <div class="border-t border-gray-100 pt-3 first:border-t-0 first:pt-0">
                                                    <div class="flex flex-wrap gap-2 text-xs mb-2">
                                                        @if($reference->version)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                                                {{ $reference->version->versioncode ?: $reference->version->versionname }}
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="text-sm font-medium text-gray-800">
                                                        {{ $reference->referencelabel ?: $reference->buildReferenceText() }}
                                                    </div>

                                                    @if($reference->notes)
                                                        <div class="mt-1 text-sm text-gray-700 whitespace-pre-line">{{ $reference->notes }}</div>
                                                    @endif

                                                    @if($reference->cachedreferencetext)
                                                        <div class="mt-2 text-sm text-gray-700 whitespace-pre-line">{{ $reference->cachedreferencetext }}</div>
                                                    @endif
                                                    @if($reference->cachedpassagetext)
                                                        <div class="mt-3 rounded-md border border-gray-200 bg-gray-50 px-3 py-3">
                                                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">
                                                                Cached Passage Text
                                                            </div>
                                                            <div class="text-sm text-gray-700 whitespace-pre-line">{{ $reference->cachedpassagetext }}</div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                @if($knowledgeItem->instrument)
                                    <div class="rounded-lg border border-green-200 bg-green-50 p-5 space-y-4">
                                        <div>
                                            <h5 class="text-sm font-semibold text-green-900">Instrument</h5>
                                            <div class="mt-2 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3 text-sm">
                                                <div>
                                                    <div class="text-xs uppercase tracking-wide text-green-800">Name</div>
                                                    <div class="text-green-950">{{ $knowledgeItem->instrument->instrumentname }}</div>
                                                </div>
                                                <div>
                                                    <div class="text-xs uppercase tracking-wide text-green-800">Symbol</div>
                                                    <div class="text-green-950">{{ $knowledgeItem->instrument->symbol ?: '—' }}</div>
                                                </div>
                                                <div>
                                                    <div class="text-xs uppercase tracking-wide text-green-800">Type</div>
                                                    <div class="text-green-950">{{ $knowledgeItem->instrument->instrumentType?->typename ?: '—' }}</div>
                                                </div>
                                                <div>
                                                    <div class="text-xs uppercase tracking-wide text-green-800">Exchange</div>
                                                    <div class="text-green-950">{{ $knowledgeItem->instrument->exchange?->exchangename ?? $knowledgeItem->instrument->exchange?->name ?? '—' }}</div>
                                                </div>
                                            </div>

                                            @if($knowledgeItem->instrument->notes)
                                                <div class="mt-3 text-sm text-green-950 whitespace-pre-line">{{ $knowledgeItem->instrument->notes }}</div>
                                            @endif
                                        </div>

                                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                                            @if($knowledgeItem->instrument->aliases->isNotEmpty())
                                                <div class="rounded-lg border border-green-200 bg-white p-4 space-y-3">
                                                    <h6 class="text-sm font-semibold text-gray-900">Instrument Aliases</h6>

                                                    @foreach($knowledgeItem->instrument->aliases as $alias)
                                                        <div class="border-t border-gray-100 pt-3 first:border-t-0 first:pt-0">
                                                            <div class="flex flex-wrap gap-2 text-xs mb-2">
                                                                @if($alias->aliastype)
                                                                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                                                                        {{ $alias->aliastype }}
                                                                    </span>
                                                                @endif
                                                            </div>

                                                            <div class="text-sm text-gray-700">{{ $alias->aliasvalue }}</div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            @if($knowledgeItem->instrument->priceObservations->isNotEmpty())
                                                <div class="rounded-lg border border-green-200 bg-white p-4 space-y-3">
                                                    <h6 class="text-sm font-semibold text-gray-900">Instrument Price Observations</h6>

                                                    @foreach($knowledgeItem->instrument->priceObservations as $observation)
                                                        <div class="border-t border-gray-100 pt-3 first:border-t-0 first:pt-0 text-sm text-gray-700">
                                                            <div class="font-medium text-gray-800">
                                                                {{ $observation->observedon ? $observation->observedon->format('d M Y') : 'Observation' }}
                                                            </div>
                                                            <div class="mt-1">Open: {{ $observation->priceopen ?? '—' }}</div>
                                                            <div>High: {{ $observation->pricehigh ?? '—' }}</div>
                                                            <div>Low: {{ $observation->pricelow ?? '—' }}</div>
                                                            <div>Close: {{ $observation->priceclose ?? '—' }}</div>
                                                            @if($observation->adjustedclose)
                                                                <div>Adjusted Close: {{ $observation->adjustedclose }}</div>
                                                            @endif
                                                            @if($observation->volume)
                                                                <div>Volume: {{ number_format($observation->volume) }}</div>
                                                            @endif
                                                            @if($observation->observationnotes)
                                                                <div class="mt-1 whitespace-pre-line">{{ $observation->observationnotes }}</div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            @if($knowledgeItem->instrument->corporateActions->isNotEmpty())
                                                <div class="rounded-lg border border-green-200 bg-white p-4 space-y-3">
                                                    <h6 class="text-sm font-semibold text-gray-900">Instrument Corporate Actions</h6>

                                                    @foreach($knowledgeItem->instrument->corporateActions as $action)
                                                        <div class="border-t border-gray-100 pt-3 first:border-t-0 first:pt-0 text-sm text-gray-700">
                                                            <div class="font-medium text-gray-800">
                                                                {{ $action->actiontype ?: 'Corporate action' }}
                                                            </div>
                                                            @if($action->actiondate)
                                                                <div class="mt-1 text-xs text-gray-500">
                                                                    {{ $action->actiondate->format('d M Y') }}
                                                                </div>
                                                            @endif
                                                            @if($action->ratiofrom || $action->ratioto)
                                                                <div class="mt-1">
                                                                    Ratio: {{ $action->ratiofrom ?? '—' }} : {{ $action->ratioto ?? '—' }}
                                                                </div>
                                                            @endif
                                                            @if($action->oldvalue || $action->newvalue)
                                                                <div>
                                                                    Value: {{ $action->oldvalue ?? '—' }} → {{ $action->newvalue ?? '—' }}
                                                                </div>
                                                            @endif
                                                            @if($action->source?->sourcetitle)
                                                                <div class="mt-1">
                                                                    Source: {{ $action->source->sourcetitle }}
                                                                </div>
                                                            @endif
                                                            @if($action->notes)
                                                                <div class="mt-1 whitespace-pre-line">{{ $action->notes }}</div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            @if($knowledgeItem->instrument->transactions->isNotEmpty())
                                                <div class="rounded-lg border border-green-200 bg-white p-4 space-y-3">
                                                    <h6 class="text-sm font-semibold text-gray-900">Instrument Transactions</h6>

                                                    @foreach($knowledgeItem->instrument->transactions as $transaction)
                                                        <div class="border-t border-gray-100 pt-3 first:border-t-0 first:pt-0 text-sm text-gray-700">
                                                            <div class="font-medium text-gray-800">
                                                                {{ $transaction->transactiontype ?: 'Transaction' }}
                                                            </div>
                                                            @if($transaction->transactiondate)
                                                                <div class="mt-1 text-xs text-gray-500">
                                                                    {{ $transaction->transactiondate->format('d M Y') }}
                                                                </div>
                                                            @endif
                                                            @if($transaction->portfolio)
                                                                <div class="mt-1">
                                                                    Portfolio: {{ $transaction->portfolio->portfolioname ?? $transaction->portfolio->name ?? '—' }}
                                                                </div>
                                                            @endif
                                                            @if($transaction->quantity)
                                                                <div>Quantity: {{ $transaction->quantity }}</div>
                                                            @endif
                                                            @if($transaction->priceperunit)
                                                                <div>Price per unit: {{ $transaction->priceperunit }}</div>
                                                            @endif
                                                            @if($transaction->grossamount)
                                                                <div>Gross amount: {{ $transaction->grossamount }}</div>
                                                            @endif
                                                            @if($transaction->netcashamount)
                                                                <div>Net cash amount: {{ $transaction->netcashamount }}</div>
                                                            @endif
                                                            @if($transaction->notes)
                                                                <div class="mt-1 whitespace-pre-line">{{ $transaction->notes }}</div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </article>
                        @empty
                            <p class="text-sm text-gray-500">
                                No knowledge items found for this category.
                            </p>
                        @endforelse
                    </div>
                </section>
            @empty
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <p class="text-sm text-gray-500">
                            No categories matched the current filter.
                        </p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <style>
        @media print {
            header,
            nav,
            .no-print {
                display: none !important;
            }

            .report-category-break {
                page-break-before: always;
            }

            .report-category-break:first-of-type {
                page-break-before: auto;
            }

            .shadow-sm,
            .sm\:rounded-lg {
                box-shadow: none !important;
                border-radius: 0 !important;
            }

            a[href]:after {
                content: '' !important;
            }
        }
    </style>
</x-app-layout>