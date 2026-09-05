{{-- resources/views/reports/knowledge/category-report.blade.php --}}
<x-app-layout>
    @php
        $title = $reportTitle ?? 'Knowledge Category Report';
        $subtitle = $reportSubtitle ?? 'Compiled reference report by category';
        $currentParentHeading = null;
    @endphp

    <x-slot name="header">
        <style>
            @media print {
                @page {
                    size: A4 portrait;
                    margin: 10mm;
                }

                html,
                body {
                    font-size: 9px !important;
                    line-height: 1.2 !important;
                    color: #000 !important;
                    background: #fff !important;
                }

                .print-hide,
                form,
                button {
                    display: none !important;
                }

                a {
                    color: #000 !important;
                    text-decoration: none !important;
                }

                /*
                * The overall report, category cards, and long item content
                * must be able to continue naturally across printed pages.
                */
                .report-category-card,
                .report-item,
                .report-long-section,
                .report-section-content,
                .markdown-content,
                .markdown-content p,
                .markdown-content ul,
                .markdown-content ol,
                .markdown-content li {
                    break-inside: auto !important;
                    page-break-inside: auto !important;
                }

                /*
                * Keep a heading with the beginning of the content below it
                * where there is enough room, but permit long text to continue.
                */
                .report-parent-heading,
                .report-category-heading,
                .report-item-heading,
                .report-section-heading,
                .markdown-content h1,
                .markdown-content h2,
                .markdown-content h3,
                .markdown-content h4,
                .markdown-content h5,
                .markdown-content h6 {
                    break-after: avoid-page;
                    page-break-after: avoid;
                }

                .report-section-heading + .report-section-content {
                    break-before: avoid-page;
                    page-break-before: avoid;
                }

                /*
                * Keep only genuinely compact blocks together.
                * Do not apply this class to the complete item record.
                */
                .break-inside-avoid {
                    break-inside: avoid;
                    page-break-inside: avoid;
                }

                .report-item.break-inside-avoid {
                    break-inside: auto !important;
                    page-break-inside: auto !important;
                }

                /*
                * Compact page and container spacing.
                */
                .py-6 {
                    padding-top: 0 !important;
                    padding-bottom: 0 !important;
                }

                .space-y-6 > :not([hidden]) ~ :not([hidden]) {
                    margin-top: 6px !important;
                }

                .space-y-5 > :not([hidden]) ~ :not([hidden]) {
                    margin-top: 6px !important;
                }

                .space-y-4 > :not([hidden]) ~ :not([hidden]) {
                    margin-top: 5px !important;
                }

                .space-y-3 > :not([hidden]) ~ :not([hidden]) {
                    margin-top: 4px !important;
                }

                .space-y-2 > :not([hidden]) ~ :not([hidden]) {
                    margin-top: 3px !important;
                }

                .px-4,
                .sm\:px-6,
                .lg\:px-8,
                .xl\:px-10,
                .\32xl\:px-12 {
                    padding-left: 0 !important;
                    padding-right: 0 !important;
                }

                .shadow-sm {
                    box-shadow: none !important;
                }

                .rounded-lg,
                .rounded-md,
                .rounded-full,
                .sm\:rounded-lg {
                    border-radius: 0 !important;
                }

                .px-6 {
                    padding-left: 7px !important;
                    padding-right: 7px !important;
                }

                .py-5 {
                    padding-top: 7px !important;
                    padding-bottom: 7px !important;
                }

                .py-4 {
                    padding-top: 5px !important;
                    padding-bottom: 5px !important;
                }

                .p-4 {
                    padding: 7px !important;
                }

                .px-3 {
                    padding-left: 5px !important;
                    padding-right: 5px !important;
                }

                .py-2 {
                    padding-top: 3px !important;
                    padding-bottom: 3px !important;
                }

                .pt-4 {
                    padding-top: 5px !important;
                }

                .pt-3 {
                    padding-top: 4px !important;
                }

                .mt-2 {
                    margin-top: 3px !important;
                }

                .mt-1 {
                    margin-top: 2px !important;
                }

                .mb-2 {
                    margin-bottom: 3px !important;
                }

                .mb-1 {
                    margin-bottom: 2px !important;
                }

                .gap-5 {
                    gap: 7px !important;
                }

                .gap-4 {
                    gap: 5px !important;
                }

                .gap-3 {
                    gap: 4px !important;
                }

                .gap-2 {
                    gap: 3px !important;
                }

                /*
                * Compact print typography.
                */
                h1,
                .text-2xl {
                    font-size: 15px !important;
                    line-height: 1.1 !important;
                }

                h2,
                .text-xl {
                    font-size: 12px !important;
                    line-height: 1.15 !important;
                }

                h3,
                h4,
                h5,
                .text-base {
                    font-size: 10px !important;
                    line-height: 1.15 !important;
                }

                .text-sm,
                .text-sm.font-semibold {
                    font-size: 9px !important;
                    line-height: 1.2 !important;
                }

                .text-xs {
                    font-size: 7.5px !important;
                    line-height: 1.15 !important;
                }

                /*
                * The included Markdown stylesheet may otherwise restore
                * larger paragraph, heading, and list typography.
                */
                .markdown-content,
                .markdown-content p,
                .markdown-content li,
                .markdown-content td,
                .markdown-content th,
                .markdown-content blockquote {
                    font-size: 9px !important;
                    line-height: 1.2 !important;
                }

                .markdown-content h1 {
                    font-size: 13px !important;
                }

                .markdown-content h2 {
                    font-size: 11.5px !important;
                }

                .markdown-content h3 {
                    font-size: 10.5px !important;
                }

                .markdown-content h4,
                .markdown-content h5,
                .markdown-content h6 {
                    font-size: 10px !important;
                }

                .markdown-content p {
                    margin-top: 0 !important;
                    margin-bottom: 3px !important;
                    orphans: 3;
                    widows: 3;
                }

                .markdown-content ul,
                .markdown-content ol {
                    margin-top: 2px !important;
                    margin-bottom: 3px !important;
                    padding-left: 14px !important;
                }

                .markdown-content li {
                    margin-bottom: 1px !important;
                }

                .markdown-content h1,
                .markdown-content h2,
                .markdown-content h3,
                .markdown-content h4,
                .markdown-content h5,
                .markdown-content h6 {
                    margin-top: 5px !important;
                    margin-bottom: 2px !important;
                    line-height: 1.1 !important;
                }

                /*
                * Keep inherently compact or difficult-to-split Markdown objects
                * together, without applying the rule to all bordered report cards.
                */
                .markdown-content table,
                .markdown-content pre,
                .markdown-content blockquote {
                    break-inside: avoid;
                    page-break-inside: avoid;
                }

                /*
                * Compact pills used extensively in item metadata and notes.
                */
                .inline-flex.items-center {
                    padding: 1px 4px !important;
                }
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

                @if(!empty($selectedCategoryIds))
                    <p class="mt-1 text-xs text-gray-500">
                        Categories:
                        {{ implode(', ', $selectedCategoryIds) }}
                    </p>
                @endif

                @if(!empty($reviewOnly))
                    <p class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full bg-yellow-50 text-yellow-700 text-xs border border-yellow-200">
                        Showing only items with a review date.
                    </p>
                @endif
            </div>

            <div class="flex items-center gap-3">
                <form method="GET"
                      action="{{ request()->url() }}"
                      class="flex items-center gap-2">
                    @foreach(request()->except(['review_only']) as $name => $value)
                        @if(is_array($value))
                            @foreach($value as $v)
                                <input type="hidden" name="{{ $name }}[]" value="{{ $v }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endif
                    @endforeach

                    <label class="inline-flex items-center gap-1 text-xs text-gray-700">
                        <input type="checkbox"
                               name="review_only"
                               value="1"
                               @checked(!empty($reviewOnly))
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        Review dates only
                    </label>

                    <button type="submit"
                            class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-xs">
                        Apply
                    </button>
                </form>

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
                @php
                    $parentHeading = $category->parentCategory?->categoryname ?: $category->categoryname;
                    $isParentHeadingChanged = $currentParentHeading !== $parentHeading;
                @endphp

                @if($isParentHeadingChanged)
                    <section class="report-category-break">
                        <div class="px-1 pt-2 pb-1">
                            <h2 class="text-2xl font-bold text-gray-900 report-parent-heading">
                                {{ $parentHeading }}
                            </h2>
                        </div>
                    </section>

                    @php
                        $currentParentHeading = $parentHeading;
                    @endphp
                @endif

                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden report-category-card">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 report-category-heading">
                                {{ $category->domain?->domainname ?? 'Unassigned Domain' }} – {{ $category->categoryname }}
                            </h2>
                            <p class="mt-1 text-xs text-gray-500">
                                Category ID: {{ $category->id }}
                                @if($category->parentCategory)
                                    · Parent: {{ $category->parentCategory->categoryname }}
                                @endif
                                @if(!empty($category->categorytype))
                                    · Type: {{ ucfirst($category->categorytype) }}
                                @endif
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500">
                            <span>Sort: {{ $category->sortorder ?? 0 }}</span>
                            <span>Items: {{ $category->knowledgeItems->count() }}</span>
                        </div>
                    </div>

                    @if($category->knowledgeItems->isEmpty())
                        <div class="px-6 py-4 text-sm text-gray-500">
                            No knowledge items in this category for the current filters.
                        </div>
                    @else
                        <div class="divide-y divide-gray-200">
                            @foreach($category->knowledgeItems as $knowledgeItem)
                                @php
                                    $showPersonFacts = (bool) ($knowledgeItem->primaryCategory?->domain?->hasfamilyhistorytools ?? false);
                                @endphp

                                <div class="px-6 py-5 space-y-4 report-item">
                                    <div class="flex flex-wrap items-start justify-between gap-4">
                                        <div class="space-y-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <h4 class="text-base font-semibold text-gray-900 report-item-heading">
                                                    {{ $knowledgeItem->itemname }}
                                                </h4>

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
                                                @if($knowledgeItem->parentItem)
                                                    <span>Parent: {{ $knowledgeItem->parentItem->itemname }}</span>
                                                @endif

                                                @if($knowledgeItem->place)
                                                    <span>Place: {{ $knowledgeItem->place->placename }}</span>
                                                @endif

                                                @if($knowledgeItem->startdate)
                                                    <span>Start: {{ $knowledgeItem->startdate->format('d M Y') }}</span>
                                                @endif

                                                @if($knowledgeItem->enddate)
                                                    <span>End: {{ $knowledgeItem->enddate->format('d M Y') }}</span>
                                                @endif

                                                @if($knowledgeItem->nextreviewdate)
                                                    <span>Next review: {{ $knowledgeItem->nextreviewdate->format('d M Y') }}</span>
                                                @endif

                                                <span>Sort: {{ $knowledgeItem->sortorder ?? 0 }}</span>
                                            </div>
                                        </div>

                                        @if($knowledgeItem->attachments->isNotEmpty())
                                            <div class="flex flex-wrap gap-2 text-xs text-gray-500">
                                                <span>Attachments: {{ $knowledgeItem->attachments->count() }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    

                                    @include('reports.knowledge.partials.attachments', [
                                        'attachments' => $knowledgeItem->attachments,
                                        'heading' => 'Attachments',
                                    ])

                                    @if($knowledgeItem->bibleReferences->isNotEmpty())
                                        <div class="rounded-lg border border-gray-200 p-4 space-y-3">
                                            <h5 class="text-sm font-semibold text-gray-900">Bible References</h5>

                                            <div class="space-y-3">
                                                @foreach($knowledgeItem->bibleReferences as $reference)
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
                                                            @php
                                                                $bookName = $reference->book?->bookname ?? 'Unknown book';
                                                                $chapterFrom = $reference->chapterfrom;
                                                                $verseFrom = $reference->versefrom;
                                                                $chapterTo = $reference->chapterto;
                                                                $verseTo = $reference->verseto;
                                                            @endphp

                                                            <span class="font-medium">{{ $bookName }}</span>

                                                            @if(!is_null($chapterFrom))
                                                                {{ ' ' . $chapterFrom }}
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
                                                                <div class="text-xs font-medium text-gray-500 mb-1">Cached Passage Text</div>
                                                                <div class="text-sm text-gray-700 whitespace-pre-line">
                                                                    {{ $reference->cachedpassagetext }}
                                                                </div>
                                                            </div>
                                                        @endif

                                                        @if(filled($reference->notes))
                                                            <div class="rounded-md bg-white border border-gray-200 px-3 py-2">
                                                                <div class="text-xs font-medium text-gray-500 mb-1">Notes</div>
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

                                   @if (
                                        filled($knowledgeItem->summary)
                                        || filled($knowledgeItem->significance)
                                        || filled($knowledgeItem->detailednotes)
                                        || filled($knowledgeItem->reviewnotes)
                                    )
                                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                                            @if (filled($knowledgeItem->summary))
                                                <div class="rounded-lg border border-gray-200 p-4">
                                                    <h5 class="text-sm font-semibold text-gray-900 mb-2">Summary</h5>
                                                    <div class="text-sm text-gray-700 markdown-content">
                                                        @include('partials.markdown.rendered-block', [
                                                            'content' => $knowledgeItem->summary,
                                                        ])
                                                    </div>
                                                </div>
                                            @endif

                                            @if (filled($knowledgeItem->significance))
                                                <div class="rounded-lg border border-gray-200 p-4">
                                                    <h5 class="text-sm font-semibold text-gray-900 mb-2">Significance</h5>
                                                    <div class="text-sm text-gray-700 markdown-content">
                                                        @include('partials.markdown.rendered-block', [
                                                            'content' => $knowledgeItem->significance,
                                                        ])
                                                    </div>
                                                </div>
                                            @endif

                                            @if (filled($knowledgeItem->detailednotes))
                                                <div class="rounded-lg border border-gray-200 p-4 xl:col-span-2">
                                                    <h5 class="text-sm font-semibold text-gray-900 mb-2">Detailed Notes</h5>

                                                    <div class="text-sm text-gray-700 markdown-content">
                                                        @include('partials.markdown.rendered-block', [
                                                            'content' => $knowledgeItem->detailednotes,
                                                        ])
                                                    </div>
                                                </div>
                                            @endif

                                            @if (filled($knowledgeItem->reviewnotes))
                                                <div class="rounded-lg border border-gray-200 p-4 xl:col-span-2 report-long-section">
                                                    <h5 class="text-sm font-semibold text-gray-900 mb-2 report-section-heading">
                                                        Review Notes
                                                    </h5>

                                                    <div class="text-sm text-gray-700 markdown-content report-section-content">
                                                        @include('partials.markdown.rendered-block', [
                                                            'content' => $knowledgeItem->reviewnotes,
                                                        ])
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    @if($knowledgeItem->displayRelationships?->isNotEmpty())
                                        <div class="rounded-lg border border-gray-200 p-4 space-y-3">
                                            <h5 class="text-sm font-semibold text-gray-900">Relationships</h5>

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
                                                        —
                                                        @if($relatedItem)
                                                            {{ $relatedItem->itemname }}
                                                        @else
                                                            <span class="text-xs text-gray-500">Missing related item</span>
                                                        @endif
                                                        @if($entry['effectiveDate'])
                                                            <span class="text-xs text-gray-500">
                                                                · Effective {{ $entry['effectiveDate']->format('d M Y') }}
                                                            </span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    @if($showPersonFacts && $knowledgeItem->personFacts->isNotEmpty())
                                        <div class="rounded-lg border border-gray-200 p-4 space-y-3">
                                            <h5 class="text-sm font-semibold text-gray-900">Person Facts</h5>

                                            <div class="space-y-3">
                                                @foreach($knowledgeItem->personFacts as $fact)
                                                    <div class="border-t border-gray-100 pt-3 first:border-t-0 first:pt-0">
                                                        <div class="flex flex-wrap items-center gap-2">
                                                            <div class="text-sm font-medium text-gray-800">
                                                                {{ $fact->factlabel ?: (\App\Models\KnowledgePersonFact::factTypeOptions()[$fact->facttype] ?? ucfirst($fact->facttype)) }}
                                                            </div>

                                                            @if($fact->ispreferred)
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs border border-emerald-200">
                                                                    Preferred
                                                                </span>
                                                            @endif

                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-xs border border-gray-200">
                                                                Sort: {{ $fact->sortorder ?? 0 }}
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
                                                                · {{ \App\Models\KnowledgePersonFact::dateQualifierOptions()[$fact->datequalifier] ?? ucfirst($fact->datequalifier) }}
                                                            @endif

                                                            @if($fact->place)
                                                                · {{ $fact->place->placename }}@if($fact->place->locality), {{ $fact->place->locality }}@endif
                                                            @endif

                                                            @if($fact->proofstatus)
                                                                · {{ \App\Models\KnowledgePersonFact::proofStatusOptions()[$fact->proofstatus] ?? ucfirst($fact->proofstatus) }}
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

                                    @if($knowledgeItem->reportRelationships->isNotEmpty())
                                        <div class="rounded-lg border border-gray-200 p-4 space-y-4">
                                            <h5 class="text-sm font-semibold text-gray-900">Relationship Facts</h5>

                                            @foreach($knowledgeItem->reportRelationships as $entry)
                                                @php
                                                    $relatedItem = $entry['relatedItem'] ?? null;
                                                    $relationshipFacts = $entry['relationshipFacts'] ?? collect();
                                                @endphp

                                                <div class="border-t border-gray-100 pt-4 first:border-t-0 first:pt-0 space-y-2">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $entry['displayTypeLabel'] ?? 'Related' }}
                                                        @if($relatedItem)
                                                            — {{ $relatedItem->itemname }}
                                                        @endif
                                                    </div>

                                                    <div class="flex flex-wrap gap-2 text-xs text-gray-500">
                                                        <span>{{ $entry['direction'] === 'incoming' ? 'Incoming' : 'Outgoing' }}</span>
                                                        <span>Sort: {{ $entry['sortorder'] ?? 0 }}</span>
                                                        @if(!empty($entry['effectiveDate']))
                                                            <span>Effective: {{ $entry['effectiveDate']->format('d M Y') }}</span>
                                                        @endif
                                                    </div>

                                                    @if($relationshipFacts->isNotEmpty())
                                                        <div class="space-y-2">
                                                            @foreach($relationshipFacts as $fact)
                                                                <div class="rounded-md bg-gray-50 border border-gray-200 px-3 py-2">
                                                                    <div class="flex flex-wrap items-center gap-2">
                                                                        <div class="text-sm font-medium text-gray-800">
                                                                            {{ \App\Models\KnowledgeRelationshipFact::factTypeOptions()[$fact->facttype] ?? ucfirst($fact->facttype) }}
                                                                        </div>

                                                                        @if($fact->ispreferred)
                                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs border border-emerald-200">
                                                                                Preferred
                                                                            </span>
                                                                        @endif

                                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-xs border border-gray-200">
                                                                            Sort: {{ $fact->sortorder ?? 0 }}
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
                                                                            · {{ \App\Models\KnowledgePersonFact::dateQualifierOptions()[$fact->datequalifier] ?? ucfirst($fact->datequalifier) }}
                                                                        @endif

                                                                        @if($fact->place)
                                                                            · {{ $fact->place->placename }}@if($fact->place->locality), {{ $fact->place->locality }}@endif
                                                                        @endif

                                                                        @if($fact->proofstatus)
                                                                            · {{ \App\Models\KnowledgePersonFact::proofStatusOptions()[$fact->proofstatus] ?? ucfirst($fact->proofstatus) }}
                                                                        @endif
                                                                    </div>

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
                                                    @else
                                                        <div class="text-xs text-gray-500">
                                                            No relationship facts recorded.
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
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
                                                            @if (filled($note->stance))
                                                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-violet-50 text-violet-700 border border-violet-200">
                                                                    Stance: {{ ucfirst($note->stance) }}
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

                                        @if ($knowledgeItem->sources->isNotEmpty())
                                            <section class="rounded-lg border border-gray-200 p-4 space-y-3">
                                                <h5 class="text-sm font-semibold text-gray-900">Sources</h5>

                                                @foreach ($knowledgeItem->sources as $source)
                                                    @php
                                                        $sourceTitle = $source->sourcetitle ?: 'Source';
                                                    @endphp

                                                    <div class="border-t border-gray-100 pt-3 first:border-t-0 first:pt-0 space-y-3">
                                                        <div>
                                                            <div class="text-sm font-medium text-gray-900">
                                                                {{ $sourceTitle }}
                                                            </div>

                                                            <div class="mt-1 flex flex-wrap gap-2 text-xs">
                                                                @if (filled($source->sourcetype))
                                                                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                                                                        {{ ucfirst($source->sourcetype) }}
                                                                    </span>
                                                                @endif

                                                                @if (filled($source->importstatus))
                                                                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                                                        {{ ucfirst($source->importstatus) }}
                                                                    </span>
                                                                @endif

                                                                @if ($source->retrievedon)
                                                                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                                                                        Retrieved: {{ $source->retrievedon->format('d M Y') }}
                                                                    </span>
                                                                @endif

                                                                @if ($source->reviewedon)
                                                                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                                                                        Reviewed: {{ $source->reviewedon->format('d M Y') }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        @if (filled($source->sourceurl))
                                                            <div>
                                                                <div class="text-xs font-medium text-gray-500 mb-1">URL</div>
                                                                <div class="text-sm break-all">
                                                                    <a href="{{ $source->sourceurl }}"
                                                                    target="_blank"
                                                                    rel="noopener noreferrer"
                                                                    class="text-blue-600 hover:text-blue-800 hover:underline">
                                                                        {{ $source->sourceurl }}
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        @if (filled($source->sourcepublisher))
                                                            <div>
                                                                <div class="text-xs font-medium text-gray-500 mb-1">Publisher</div>
                                                                <div class="text-sm text-gray-700">
                                                                    {{ $source->sourcepublisher }}
                                                                </div>
                                                            </div>
                                                        @endif

                                                        @if (filled($source->reviewedby))
                                                            <div>
                                                                <div class="text-xs font-medium text-gray-500 mb-1">Reviewed by</div>
                                                                <div class="text-sm text-gray-700">
                                                                    {{ $source->reviewedby }}
                                                                </div>
                                                            </div>
                                                        @endif

                                                        @if (filled($source->importedsummary))
                                                            <div>
                                                                <div class="text-xs font-medium text-gray-500 mb-1">Imported Summary</div>
                                                                <div class="text-sm text-gray-700 markdown-content">
                                                                    @include('partials.markdown.rendered-block', [
                                                                        'content' => $source->importedsummary,
                                                                    ])
                                                                </div>
                                                            </div>
                                                        @endif

                                                        @if (filled($source->importednotes))
                                                            <div>
                                                                <div class="text-xs font-medium text-gray-500 mb-1">Imported Notes</div>
                                                                <div class="text-sm text-gray-700 markdown-content">
                                                                    @include('partials.markdown.rendered-block', [
                                                                        'content' => $source->importednotes,
                                                                    ])
                                                                </div>
                                                            </div>
                                                        @endif

                                                        @if (filled($source->internalnotes))
                                                            <div class="rounded-md bg-amber-50 border border-amber-200 px-3 py-2">
                                                                <div class="text-xs font-medium text-amber-800 mb-1">Internal Notes</div>
                                                                <div class="text-sm text-amber-900 markdown-content">
                                                                    @include('partials.markdown.rendered-block', [
                                                                        'content' => $source->internalnotes,
                                                                    ])
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </section>
                                        @endif
                                    </div>

                                    @if($knowledgeItem->reviewLogs->isNotEmpty())
                                        <div class="rounded-lg border border-gray-200 p-4 space-y-3">
                                            <h5 class="text-sm font-semibold text-gray-900">Review History</h5>

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
                                                                {{ \App\Models\KnowledgeReviewLog::TYPE_OPTIONS[$log->reviewtype] ?? $log->reviewtype }}
                                                            </span>
                                                        @endif

                                                        @if ($log->outcome)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                                                {{ ucfirst($log->outcome) }}
                                                            </span>
                                                        @endif

                                                        @if ($log->nextreviewdate)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-yellow-50 text-yellow-700 border border-yellow-200">
                                                                Next review: {{ $log->nextreviewdate->format('d M Y') }}
                                                            </span>
                                                        @endif
                                                    </div>

                                                    @if (filled($log->summary))
                                                        <div class="mt-1 text-sm text-gray-700 markdown-content">
                                                            @include('partials.markdown.rendered-block', [
                                                                'content' => $log->summary,
                                                            ])
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 text-sm text-gray-500">
                        No categories or items found for the current filters.
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    @include('partials.markdown.markdown-styles')
</x-app-layout>