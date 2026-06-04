{{-- resources/views/reports/knowledge/items/reference-book.blade.php --}}
<x-app-layout>
    @php
        $title = $reportTitle ?? 'Knowledge Item Report';
        $subtitle = $reportSubtitle ?? 'Compiled reference report for a single knowledge item';
        $knowledgeItem = $knowledgeItem ?? null;
        $showPersonFacts = (bool) ($knowledgeItem?->primaryCategory?->domain?->hasfamilyhistorytools ?? false);
    @endphp

    <x-slot name="header">
        <style>
            @media print {
                .print-hide {
                    display: none !important;
                }
                .break-inside-avoid {
                    break-inside: avoid;
                    page-break-inside: avoid;
                }
                a {
                    color: #000 !important;
                    text-decoration: none !important;
                }
            }
            .break-inside-avoid {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        </style>

        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $title }}
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $subtitle }}
                </p>

                @if(!empty($reviewOnly))
                    <p class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full bg-yellow-50 text-yellow-700 text-xs border border-yellow-200">
                        Showing only items with a review date.
                    </p>
                @endif
            </div>

            <div class="flex items-center gap-3 print-hide">
                <button
                    type="button"
                    onclick="window.print()"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm"
                >
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

            @if(!$knowledgeItem)
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 text-sm text-gray-500">
                        No knowledge item was found.
                    </div>
                </div>
            @else
                <section class="bg-white shadow-sm sm:rounded-lg overflow-hidden break-inside-avoid">
                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-gray-200 flex items-start justify-between gap-4">
                        <div class="space-y-2 min-w-0">
                            <div class="flex items-center gap-2">
                                <h1 class="text-2xl font-bold text-gray-900 truncate">
                                    {{ $knowledgeItem->itemname ?? 'Untitled knowledge item' }}
                                </h1>

                                @if($knowledgeItem->itemstatus)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-xs border border-gray-200">
                                        {{ $knowledgeItem->itemstatus }}
                                    </span>
                                @endif

                                @if($knowledgeItem->itemType)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 text-xs border border-blue-200">
                                        {{ $knowledgeItem->itemType->typename }}
                                    </span>
                                @endif

                                @if($knowledgeItem->isfeatured)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-yellow-50 text-yellow-700 text-xs border border-yellow-200">
                                        Featured
                                    </span>
                                @endif
                            </div>

                            <div class="flex flex-wrap gap-3 text-xs text-gray-500">
                                @if($knowledgeItem->primaryCategory)
                                    <span>
                                        Category:
                                        {{ $knowledgeItem->primaryCategory->categoryname }}
                                    </span>
                                @endif

                                @if($knowledgeItem->primaryCategory?->domain)
                                    <span>
                                        Domain:
                                        {{ $knowledgeItem->primaryCategory->domain->domainname }}
                                    </span>
                                @endif

                                @if($knowledgeItem->parentItem)
                                    <span>
                                        Parent:
                                        {{ $knowledgeItem->parentItem->itemname }}
                                    </span>
                                @endif

                                @if($knowledgeItem->place)
                                    <span>
                                        Place:
                                        {{ $knowledgeItem->place->placename }}
                                    </span>
                                @endif

                                @if($knowledgeItem->startdate)
                                    <span>
                                        Start:
                                        {{ $knowledgeItem->startdate->format('d M Y') }}
                                    </span>
                                @endif

                                @if($knowledgeItem->enddate)
                                    <span>
                                        End:
                                        {{ $knowledgeItem->enddate->format('d M Y') }}
                                    </span>
                                @endif

                                @if($knowledgeItem->nextreviewdate)
                                    <span>
                                        Next review:
                                        {{ $knowledgeItem->nextreviewdate->format('d M Y') }}
                                    </span>
                                @endif

                                <span>
                                    Sort:
                                    {{ $knowledgeItem->sortorder ?? 0 }}
                                </span>
                            </div>
                        </div>

                        {{-- Quick stats / attachments --}}
                        <div class="shrink-0 text-xs text-gray-500 space-y-1 text-right">
                            @if($knowledgeItem->attachments?->count())
                                <div>Attachments: {{ $knowledgeItem->attachments->count() }}</div>
                            @endif

                            @if($knowledgeItem->notes?->count())
                                <div>Notes: {{ $knowledgeItem->notes->count() }}</div>
                            @endif

                            @if($knowledgeItem->sources?->count())
                                <div>Sources: {{ $knowledgeItem->sources->count() }}</div>
                            @endif

                            @if($knowledgeItem->reviewLogs?->count())
                                <div>Reviews: {{ $knowledgeItem->reviewLogs->count() }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- Main content --}}
                    <div class="px-6 py-5 space-y-5">
                        {{-- Summary / significance --}}
                        @if($knowledgeItem->summary || $knowledgeItem->significance)
                            <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                                @if($knowledgeItem->summary)
                                    <div class="rounded-lg border border-gray-200 p-4">
                                        <h3 class="text-sm font-semibold text-gray-900 mb-2">Summary</h3>
                                        <div class="text-sm text-gray-700 markdown-content">
                                            @include('partials.markdown.rendered-block', [
                                                'content' => $knowledgeItem->summary,
                                            ])
                                        </div>
                                    </div>
                                @endif

                                @if($knowledgeItem->significance)
                                    <div class="rounded-lg border border-gray-200 p-4">
                                        <h3 class="text-sm font-semibold text-gray-900 mb-2">Significance</h3>
                                        <div class="text-sm text-gray-700 markdown-content">
                                            @include('partials.markdown.rendered-block', [
                                                'content' => $knowledgeItem->significance,
                                            ])
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Detailed / review notes --}}
                        @if($knowledgeItem->detailednotes || $knowledgeItem->reviewnotes)
                            <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                                @if($knowledgeItem->detailednotes)
                                    <div class="rounded-lg border border-gray-200 p-4 xl:col-span-2">
                                        <h3 class="text-sm font-semibold text-gray-900 mb-2">Detailed Notes</h3>
                                        <div class="text-sm text-gray-700 markdown-content">
                                            @include('partials.markdown.rendered-block', [
                                                'content' => $knowledgeItem->detailednotes,
                                            ])
                                        </div>
                                    </div>
                                @endif

                                @if($knowledgeItem->reviewnotes)
                                    <div class="rounded-lg border border-gray-200 p-4 xl:col-span-2">
                                        <h3 class="text-sm font-semibold text-gray-900 mb-2">Review Notes</h3>
                                        <div class="text-sm text-gray-700 markdown-content">
                                            @include('partials.markdown.rendered-block', [
                                                'content' => $knowledgeItem->reviewnotes,
                                            ])
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Bible references --}}
                        @if($knowledgeItem->bibleReferences?->isNotEmpty())
                            <div class="rounded-lg border border-gray-200 p-4 space-y-3">
                                <h3 class="text-sm font-semibold text-gray-900">Bible References</h3>

                                <div class="space-y-3">
                                    @foreach($knowledgeItem->bibleReferences as $reference)
                                        @php
                                            $bookName = $reference->book?->bookname ?? 'Unknown book';
                                            $chapterFrom = $reference->chapterfrom;
                                            $verseFrom = $reference->versefrom;
                                            $chapterTo = $reference->chapterto;
                                            $verseTo = $reference->verseto;
                                        @endphp

                                        <div class="border-t border-gray-100 pt-3 first:border-t-0 first:pt-0 space-y-2">
                                            <div class="flex flex-wrap gap-2 text-xs">
                                                @if($reference->version)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                        {{ $reference->version->versionname ?? $reference->version->abbreviation ?? 'Version' }}
                                                    </span>
                                                @endif

                                                @if(!empty($reference->cachedreferencetext))
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                                        {{ $reference->cachedreferencetext }}
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="text-sm text-gray-800">
                                                <span class="font-medium">{{ $bookName }}</span>
                                                @if(!is_null($chapterFrom))
                                                    .
                                                    {{ $chapterFrom }}
                                                    @if(!is_null($verseFrom))
                                                        :{{ $verseFrom }}
                                                    @endif

                                                    @if(!is_null($chapterTo) || !is_null($verseTo))
                                                        –
                                                        {{ $chapterTo ?? $chapterFrom }}
                                                        @if(!is_null($verseTo))
                                                            :{{ $verseTo }}
                                                        @endif
                                                    @endif
                                                @endif
                                            </div>

                                            @if(!empty($reference->cachedpassagetext))
                                                <div class="rounded-md bg-gray-50 border border-gray-200 px-3 py-2">
                                                    <div class="text-xs font-medium text-gray-500 mb-1">
                                                        Cached Passage Text
                                                    </div>
                                                    <div class="text-sm text-gray-700 whitespace-pre-line">
                                                        {{ $reference->cachedpassagetext }}
                                                    </div>
                                                </div>
                                            @endif

                                            @if(filled($reference->notes))
                                                <div class="rounded-md bg-white border border-gray-200 px-3 py-2">
                                                    <div class="text-xs font-medium text-gray-500 mb-1">
                                                        Notes
                                                    </div>
                                                    <div class="text-sm text-gray-700 markdown-content">
                                                        @include('partials.markdown.rendered-block', [
                                                            'content' => $reference->notes,
                                                        ])
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Display relationships --}}
                        @if($knowledgeItem->displayRelationships?->isNotEmpty())
                            <div class="rounded-lg border border-gray-200 p-4 space-y-3">
                                <h3 class="text-sm font-semibold text-gray-900">Relationships</h3>

                                <ul class="space-y-1 text-sm text-gray-700">
                                    @foreach($knowledgeItem->displayRelationships as $entry)
                                        @php
                                            $relatedItem = $entry['relatedItem'] ?? null;
                                        @endphp

                                        <li>
                                            <span class="font-medium">
                                                {{ $entry['displayTypeLabel'] ?? 'Related' }}
                                            </span>

                                            @if(!empty($entry['direction']))
                                                <span class="text-xs text-gray-500">
                                                    ({{ $entry['direction'] === 'incoming' ? 'incoming' : 'outgoing' }})
                                                </span>
                                            @endif

                                            @if($relatedItem)
                                                – {{ $relatedItem->itemname }}
                                            @else
                                                <span class="text-xs text-gray-500">
                                                    Missing related item
                                                </span>
                                            @endif

                                            @if($entry['effectiveDate'] ?? null)
                                                <span class="text-xs text-gray-500">
                                                    Effective {{ $entry['effectiveDate']->format('d M Y') }}
                                                </span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Relationship facts --}}
                        @if($knowledgeItem->reportRelationships?->isNotEmpty())
                            <div class="rounded-lg border border-gray-200 p-4 space-y-4">
                                <h3 class="text-sm font-semibold text-gray-900">Relationship Facts</h3>

                                @foreach($knowledgeItem->reportRelationships as $entry)
                                    @php
                                        $relatedItem = $entry['relatedItem'] ?? null;
                                        $relationshipFacts = $entry['relationshipFacts'] ?? collect();
                                    @endphp

                                    <div class="border-top border-gray-100 pt-4 first:border-t-0 first:pt-0 space-y-2">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $entry['displayTypeLabel'] ?? 'Related' }}
                                            @if($relatedItem)
                                                – {{ $relatedItem->itemname }}
                                            @endif
                                        </div>

                                        <div class="flex flex-wrap gap-2 text-xs text-gray-500">
                                            @if(!empty($entry['direction']))
                                                <span>
                                                    {{ $entry['direction'] === 'incoming' ? 'Incoming' : 'Outgoing' }}
                                                </span>
                                            @endif

                                            <span>Sort {{ $entry['sortorder'] ?? 0 }}</span>

                                            @if($entry['effectiveDate'] ?? null)
                                                <span>
                                                    Effective {{ $entry['effectiveDate']->format('d M Y') }}
                                                </span>
                                            @endif
                                        </div>

                                        @if($relationshipFacts->isNotEmpty())
                                            <div class="space-y-2">
                                                @foreach($relationshipFacts as $fact)
                                                    <div class="rounded-md bg-gray-50 border border-gray-200 px-3 py-2">
                                                        <div class="flex flex-wrap items-center gap-2">
                                                            <div class="text-sm font-medium text-gray-800">
                                                                {{ $fact->factTypeLabel() ?? ucfirst($fact->facttype) }}
                                                            </div>

                                                            @if($fact->ispreferred)
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs border border-emerald-200">
                                                                    Preferred
                                                                </span>
                                                            @endif

                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-xs border border-gray-200">
                                                                Sort {{ $fact->sortorder ?? 0 }}
                                                            </span>
                                                        </div>

                                                        <div class="mt-1 text-sm text-gray-700">
                                                            @if($fact->datetext)
                                                                {{ $fact->datetext }}
                                                            @elseif($fact->datefrom)
                                                                {{ $fact->datefrom->format('d M Y') }}
                                                            @else
                                                                No date recorded
                                                            @endif

                                                            @if($fact->datequalifier)
                                                                – {{ $fact->dateQualifierLabel() ?? ucfirst($fact->datequalifier) }}
                                                            @endif

                                                            @if($fact->place)
                                                                – {{ $fact->place->placename }}
                                                                @if($fact->place->locality)
                                                                    , {{ $fact->place->locality }}
                                                                @endif
                                                            @endif

                                                            @if($fact->proofstatus)
                                                                – {{ $fact->proofStatusLabel() ?? ucfirst($fact->proofstatus) }}
                                                            @endif
                                                        </div>

                                                        @if(filled($fact->notes))
                                                            <div class="mt-2 text-sm text-gray-600 markdown-content">
                                                                @include('partials.markdown.rendered-block', [
                                                                    'content' => $fact->notes,
                                                                ])
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-xs text-gray-500">
                                                No relationship facts recorded.
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Person facts (for family-history domains) --}}
                        @if($showPersonFacts && $knowledgeItem->personFacts?->isNotEmpty())
                            <div class="rounded-lg border border-gray-200 p-4 space-y-3">
                                <h3 class="text-sm font-semibold text-gray-900">Person Facts</h3>

                                <div class="space-y-3">
                                    @foreach($knowledgeItem->personFacts as $fact)
                                        <div class="border-t border-gray-100 pt-3 first:border-t-0 first:pt-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <div class="text-sm font-medium text-gray-800">
                                                    {{ $fact->factLabel() ?? ucfirst($fact->facttype) }}
                                                </div>

                                                @if($fact->ispreferred)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs border border-emerald-200">
                                                        Preferred
                                                    </span>
                                                @endif

                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-xs border border-gray-200">
                                                    Sort {{ $fact->sortorder ?? 0 }}
                                                </span>
                                            </div>

                                            <div class="mt-1 text-sm text-gray-700">
                                                @if($fact->datetext)
                                                    {{ $fact->datetext }}
                                                @elseif($fact->datefrom)
                                                    {{ $fact->datefrom->format('d M Y') }}
                                                @else
                                                    No date recorded
                                                @endif

                                                @if($fact->datequalifier)
                                                    – {{ $fact->dateQualifierLabel() ?? ucfirst($fact->datequalifier) }}
                                                @endif

                                                @if($fact->place)
                                                    – {{ $fact->place->placename }}
                                                    @if($fact->place->locality)
                                                        , {{ $fact->place->locality }}
                                                    @endif
                                                @endif

                                                @if($fact->proofstatus)
                                                    – {{ $fact->proofStatusLabel() ?? ucfirst($fact->proofstatus) }}
                                                @endif
                                            </div>

                                            @if($fact->valuetext)
                                                <div class="mt-1 text-sm text-gray-700 markdown-content">
                                                    @include('partials.markdown.rendered-block', [
                                                        'content' => $fact->valuetext,
                                                    ])
                                                </div>
                                            @endif

                                            @if($fact->notes)
                                                <div class="mt-2 text-sm text-gray-600 markdown-content">
                                                    @include('partials.markdown.rendered-block', [
                                                        'content' => $fact->notes,
                                                    ])
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Knowledge notes --}}
                        @if($knowledgeItem->notes?->isNotEmpty())
                            <div class="rounded-lg border border-gray-200 p-4 space-y-3">
                                <h3 class="text-sm font-semibold text-gray-900">Knowledge Notes</h3>

                                @foreach($knowledgeItem->notes as $note)
                                    <div class="border-t border-gray-100 pt-3 first:border-t-0 first:pt-0">
                                        <div class="flex flex-wrap gap-2 text-xs mb-2">
                                            @if($note->notetype)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                                                    {{ $noteTypeOptions[$note->notetype] ?? ucfirst(str_replace(['-', '_'], ' ', $note->notetype)) }}
                                                </span>
                                            @endif

                                            @if($note->reviewdate)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                                                    Review {{ $note->reviewdate->format('d M Y') }}
                                                </span>
                                            @endif

                                            @if($note->isprivate)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-red-50 text-red-700 border border-red-200">
                                                    Private
                                                </span>
                                            @endif
                                        </div>

                                        @if($note->title)
                                            <div class="text-sm font-medium text-gray-800">
                                                {{ $note->title }}
                                            </div>
                                        @endif

                                        @if($note->notecontent)
                                            <div class="mt-1 text-sm text-gray-700 markdown-content">
                                                @include('partials.markdown.rendered-block', [
                                                    'content' => $note->notecontent,
                                                ])
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Sources --}}
                        @if($knowledgeItem->sources?->isNotEmpty())
                            <section>
                                <h3 class="text-base font-semibold text-slate-900">Sources</h3>

                                <div class="mt-3 space-y-3">
                                    @foreach($knowledgeItem->sources as $source)
                                        @php
                                            $sourceTitle = $source->title
                                                ?? $source->sourcetitle
                                                ?? $source->pagetitle
                                                ?? 'Source';

                                            $sourceUrl = $source->url
                                                ?? $source->sourceurl
                                                ?? $source->canonicalurl
                                                ?? null;

                                            $importedSummary = $source->importedsummary ?? $source->summary ?? null;
                                            $importedNotes = $source->importednotes ?? $source->notes ?? null;
                                        @endphp

                                        @if(
                                            filled($sourceTitle)
                                            || filled($sourceUrl)
                                            || filled($importedSummary)
                                            || filled($importedNotes)
                                            || filled($source->sourcepublisher)
                                        )
                                            <div class="rounded-lg border border-slate-200 px-4 py-3 space-y-3">
                                                <div class="text-sm font-medium text-slate-900">
                                                    {{ $sourceTitle }}
                                                </div>

                                                @if(filled($sourceUrl))
                                                    <div>
                                                        <div class="text-xs font-medium text-slate-500 mb-1">
                                                            URL
                                                        </div>
                                                        <div class="text-sm break-all">
                                                            <a href="{{ $sourceUrl }}"
                                                               target="_blank"
                                                               rel="noopener noreferrer"
                                                               class="text-blue-600 hover:text-blue-800 hover:underline">
                                                                {{ $sourceUrl }}
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if(filled($source->sourcepublisher))
                                                    <div>
                                                        <div class="text-xs font-medium text-slate-500 mb-1">
                                                            Publisher
                                                        </div>
                                                        <div class="text-sm text-slate-700">
                                                            {{ $source->sourcepublisher }}
                                                        </div>
                                                    </div>
                                                @endif

                                                @if(filled($importedSummary))
                                                    <div>
                                                        <div class="text-xs font-medium text-slate-500 mb-1">
                                                            Imported Summary
                                                        </div>
                                                        <div class="text-sm text-slate-700 markdown-content">
                                                            @include('partials.markdown.rendered-block', [
                                                                'content' => $importedSummary,
                                                            ])
                                                        </div>
                                                    </div>
                                                @endif

                                                @if(filled($importedNotes))
                                                    <div>
                                                        <div class="text-xs font-medium text-slate-500 mb-1">
                                                            Imported Notes
                                                        </div>
                                                        <div class="text-sm text-slate-700 markdown-content">
                                                            @include('partials.markdown.rendered-block', [
                                                                'content' => $importedNotes,
                                                            ])
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        {{-- Attachments --}}
                        @includeWhen(
                            $knowledgeItem->attachments?->isNotEmpty(),
                            'reports.knowledge.partials.attachments',
                            [
                                'attachments' => $knowledgeItem->attachments,
                                'heading' => 'Attachments',
                            ]
                        )

                        {{-- Review history --}}
                        @if($knowledgeItem->reviewLogs?->isNotEmpty())
                            <div class="rounded-lg border border-gray-200 p-4 space-y-3">
                                <h3 class="text-sm font-semibold text-gray-900">Review History</h3>

                                @foreach($knowledgeItem->reviewLogs as $log)
                                    <div class="border-t border-gray-100 pt-3 first:border-t-0 first:pt-0">
                                        <div class="flex flex-wrap gap-2 text-xs mb-2">
                                            @if($log->reviewdate)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                                                    {{ $log->reviewdate->format('d M Y') }}
                                                </span>
                                            @endif

                                            @if($log->reviewtype)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                                                    {{ $log->reviewTypeLabel() ?? $log->reviewtype }}
                                                </span>
                                            @endif
                                        </div>

                                        @if($log->reviewnotes)
                                            <div class="mt-1 text-sm text-gray-700 markdown-content">
                                                @include('partials.markdown.rendered-block', [
                                                    'content' => $log->reviewnotes,
                                                ])
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>
            @endif
        </div>

        @include('partials.markdown.markdown-styles')
    </div>
</x-app-layout>