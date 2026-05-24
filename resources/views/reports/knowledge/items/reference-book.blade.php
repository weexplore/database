<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $reportTitle ?? 'Knowledge Item Report' }}
                </h2>
                @if(!empty($reportSubtitle))
                    <p class="mt-1 text-sm text-gray-500">{{ $reportSubtitle }}</p>
                @endif
            </div>

            <div class="flex items-center gap-2">
                <button type="button"
                        onclick="window.close(); setTimeout(() => history.back(), 150);"
                        class="inline-flex items-center rounded-md bg-gray-200 px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-300">
                    Close
                </button>

                <button type="button"
                        onclick="window.print()"
                        class="inline-flex items-center rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                    Print
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">

            <div class="bg-white shadow-sm sm:rounded-lg border border-slate-200">
                <div class="border-b border-slate-200 px-6 py-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-semibold text-slate-900">
                                {{ $knowledgeItem->itemname }}
                            </h1>

                            <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-600">
                                @if($knowledgeItem->primaryCategory)
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1">
                                        Category: {{ $knowledgeItem->primaryCategory->categoryname }}
                                    </span>
                                @endif

                                @if($knowledgeItem->primaryCategory?->domain)
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1">
                                        Domain: {{ $knowledgeItem->primaryCategory->domain->domainname }}
                                    </span>
                                @endif

                                @if($knowledgeItem->itemType)
                                    <span class="rounded-full bg-blue-50 px-2.5 py-1 text-blue-700">
                                        Type: {{ $knowledgeItem->itemType->typename ?? $knowledgeItem->itemtype }}
                                    </span>
                                @endif

                                @if($knowledgeItem->itemstatus)
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-emerald-700">
                                        Status: {{ ucfirst($knowledgeItem->itemstatus) }}
                                    </span>
                                @endif

                                @if($knowledgeItem->nextreviewdate)
                                    <span class="rounded-full bg-amber-50 px-2.5 py-1 text-amber-700">
                                        Review: {{ \Illuminate\Support\Carbon::parse($knowledgeItem->nextreviewdate)->format('j M Y') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-5 space-y-8">
                    @if($knowledgeItem->summary)
                        <section>
                            <h3 class="text-base font-semibold text-slate-900">Summary</h3>
                            <div class="mt-2 text-sm leading-6 text-slate-700 whitespace-pre-line">
                                {{ $knowledgeItem->summary }}
                            </div>
                        </section>
                    @endif

                    @if($knowledgeItem->significance)
                        <section>
                            <h3 class="text-base font-semibold text-slate-900">Significance</h3>
                            <div class="mt-2 text-sm leading-6 text-slate-700 whitespace-pre-line">
                                {{ $knowledgeItem->significance }}
                            </div>
                        </section>
                    @endif

                    @if($knowledgeItem->content)
                        <section>
                            <h3 class="text-base font-semibold text-slate-900">Content</h3>
                            <div class="mt-2 text-sm leading-6 text-slate-700 whitespace-pre-line">
                                {{ $knowledgeItem->content }}
                            </div>
                        </section>
                    @endif

                    @if($knowledgeItem->personFacts->isNotEmpty())
                        <section>
                            <h3 class="text-base font-semibold text-slate-900">Person Facts</h3>
                            <div class="mt-3 space-y-3">
                                @foreach($knowledgeItem->personFacts as $fact)
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
                                        <div class="text-sm font-medium text-slate-900">
                                            {{ ucfirst($fact->facttype) }}
                                            @if($fact->ispreferred)
                                                <span class="ml-2 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">
                                                    Preferred
                                                </span>
                                            @endif
                                            {{ $fact->datetext ?: 'No date text' }}
                                            @if($fact->place)
                                                -  {{ $fact->place->placename }}
                                            @endif
                                            @if($fact->notes)
                                                <div class="mt-2 whitespace-pre-line">{{ $fact->notes }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if($knowledgeItem->reportRelationships->isNotEmpty())
                        <section>
                            <h3 class="text-base font-semibold text-slate-900">Relationships</h3>
                            <div class="mt-3 space-y-4">
                                @foreach($knowledgeItem->reportRelationships as $entry)
                                    <div class="rounded-lg border border-slate-200 px-4 py-4">
                                        <div class="text-sm font-semibold text-slate-900">
                                            {{ $entry['displayTypeLabel'] }}
                                            {{ $entry['relatedItem']?->itemname ?? 'Unknown item' }}
                                        </div>

                                        @if(!empty($entry['relationshipFacts']) && $entry['relationshipFacts']->isNotEmpty())
                                            <div class="mt-3 space-y-2">
                                                @foreach($entry['relationshipFacts'] as $fact)
                                                    <div class="rounded-md bg-slate-50 px-3 py-2 text-sm text-slate-700">
                                                        <div class="font-medium text-slate-900">
                                                            {{ ucfirst($fact->facttype) }}
                                                            {{ $fact->datetext ?: 'No date text' }}
                                                            @if($fact->place)
                                                                · {{ $fact->place->placename }}
                                                            @endif
                                                            @if($fact->notes)
                                                                - {{ $fact->notes }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

@if($knowledgeItem->notes->isNotEmpty())
    <section>
        <h3 class="text-base font-semibold text-slate-900">Notes</h3>
        <div class="mt-3 space-y-3">
            @foreach($knowledgeItem->notes as $note)
                <div class="rounded-lg border border-slate-200 px-4 py-3">
                    <div class="flex flex-wrap items-center gap-2">
                        @if($note->title)
                            <div class="text-sm font-medium text-slate-900">
                                {{ $note->title }}
                            </div>
                        @endif

                        @if($note->notetype)
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">
                                {{ \App\Models\KnowledgeNote::typeOptions()[$note->notetype] ?? ucfirst($note->notetype) }}
                            </span>
                        @endif

                        @if($note->reviewdate)
                            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700">
                                Review {{ $note->reviewdate->format('j M Y') }}
                            </span>
                        @endif

                        @if($note->isprivate)
                            <span class="rounded-full bg-rose-50 px-2 py-0.5 text-xs font-medium text-rose-700">
                                Private
                            </span>
                        @endif
                    </div>

                    @if($note->notecontent)
                        <div class="mt-2 text-sm text-slate-700 whitespace-pre-line">
                            {{ $note->notecontent }}
                        </div>
                    @endif

                    <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-500">
                        @if(!is_null($note->stance) && $note->stance !== '')
                            <span>Stance: {{ $note->stance }}</span>
                        @endif

                        @if(!is_null($note->convictionlevel))
                            <span>Conviction: {{ $note->convictionlevel }}</span>
                        @endif

                        <span>Sort: {{ $note->sortorder ?? 0 }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif

                    @if($knowledgeItem->sources->isNotEmpty())
                        <section>
                            <h3 class="text-base font-semibold text-slate-900">Sources</h3>
                            <div class="mt-3 space-y-3">
                                @foreach($knowledgeItem->sources as $source)
                                    <div class="rounded-lg border border-slate-200 px-4 py-3">
                                        <div class="text-sm font-medium text-slate-900">
                                            {{ $source->title ?? $source->sourcetitle ?? 'Source' }}
                                        </div>
                                        @if(!empty($source->url))
                                            <div class="mt-1 text-sm">
                                                <a href="{{ $source->url }}"
                                                   target="_blank"
                                                   rel="noopener noreferrer"
                                                   class="text-blue-600 hover:text-blue-800 hover:underline">
                                                    {{ $source->url }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if($knowledgeItem->reviewLogs->isNotEmpty())
                        <section>
                            <h3 class="text-base font-semibold text-slate-900">Review Log</h3>
                            <div class="mt-3 space-y-3">
                                @foreach($knowledgeItem->reviewLogs as $log)
                                    <div class="rounded-lg border border-slate-200 px-4 py-3 text-sm text-slate-700">
                                        @if($log->reviewdate)
                                            <div class="font-medium text-slate-900">
                                                {{ \Illuminate\Support\Carbon::parse($log->reviewdate)->format('j M Y') }}
                                            </div>
                                        @endif
                                        @if($log->notes)
                                            <div class="mt-1 whitespace-pre-line">{{ $log->notes }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
