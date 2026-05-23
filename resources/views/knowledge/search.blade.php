{{-- resources/views/knowledge/search.blade.php --}}

<x-app-layout>
    @php
        $returnTo = request('return_to', route('knowledge-categories.index', [
            'domainid' => $selectedDomainId,
        ]));
    @endphp
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Knowledge Domain Search
            </h2>

            <a href="{{ $returnTo }}"
            class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
                Back to Knowledge Categories
            </a>
        </div>
    </x-slot>
    <div class="py-6">
        <div class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 space-y-6">
            {{-- Flash messages --}}
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Validation summary --}}
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                    <div class="font-semibold mb-1">Please fix the following:</div>
                    <ul class="list-disc list-inside text-sm space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Search card --}}
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        Search within a Knowledge Domain
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Search across items, notes, sources, and review logs for a selected domain.
                    </p>
                </div>

                <div class="px-6 py-4">
                    <form method="GET" action="{{ route('knowledge.search') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- Domain selector --}}
                            <div>
                                <label for="domainid" class="block text-sm font-medium text-gray-700">
                                    Domain
                                </label>
                                <select name="domainid" id="domainid"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm
                                               focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="">Select a domain…</option>
                                    @foreach ($domains as $domain)
                                        <option value="{{ $domain->id }}"
                                            @selected($selectedDomainId == $domain->id)>
                                            {{ $domain->domainname ?? ('Domain #' . $domain->id) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Keyword --}}
                            <div class="md:col-span-2">
                                <label for="q" class="block text-sm font-medium text-gray-700">
                                    Keywords
                                </label>
                                <input type="text" name="q" id="q" value="{{ $q }}"
                                       placeholder="Search text…"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm
                                              focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                        </div>

                        {{-- Type filters --}}
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                            @php
                                $typeFilter = $typeFilter ?? [];
                            @endphp

                            <label class="inline-flex items-center text-sm text-gray-700">
                                <input type="checkbox" name="types[]" value="items"
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm
                                              focus:border-indigo-500 focus:ring-indigo-500"
                                       @checked(empty($typeFilter) || in_array('items', $typeFilter, true))>
                                <span class="ml-2">Items</span>
                            </label>

                            <label class="inline-flex items-center text-sm text-gray-700">
                                <input type="checkbox" name="types[]" value="notes"
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm
                                              focus:border-indigo-500 focus:ring-indigo-500"
                                       @checked(empty($typeFilter) || in_array('notes', $typeFilter, true))>
                                <span class="ml-2">Notes</span>
                            </label>

                            <label class="inline-flex items-center text-sm text-gray-700">
                                <input type="checkbox" name="types[]" value="sources"
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm
                                              focus:border-indigo-500 focus:ring-indigo-500"
                                       @checked(empty($typeFilter) || in_array('sources', $typeFilter, true))>
                                <span class="ml-2">Sources</span>
                            </label>

                            <label class="inline-flex items-center text-sm text-gray-700">
                                <input type="checkbox" name="types[]" value="reviews"
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm
                                              focus:border-indigo-500 focus:ring-indigo-500"
                                       @checked(empty($typeFilter) || in_array('reviews', $typeFilter, true))>
                                <span class="ml-2">Review logs</span>
                            </label>
                            <label class="inline-flex items-center text-sm text-gray-700">
                                <input type="checkbox" name="types[]" value="categories"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm
                                            focus:border-indigo-500 focus:ring-indigo-500"
                                    @checked(empty($typeFilter) || in_array('categories', $typeFilter, true))>
                                <span class="ml-2">Categories</span>
                            </label>

                            <label class="inline-flex items-center text-sm text-gray-700">
                                <input type="checkbox" name="types[]" value="relationships"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm
                                            focus:border-indigo-500 focus:ring-indigo-500"
                                    @checked(empty($typeFilter) || in_array('relationships', $typeFilter, true))>
                                <span class="ml-2">Relationships</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <a href="{{ route('knowledge.search') }}"
                               class="inline-flex items-center px-3 py-1.5 border border-gray-300
                                      rounded-md text-sm text-gray-700 bg-white hover:bg-gray-50">
                                Clear
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-1.5 border border-transparent
                                           rounded-md text-sm font-medium text-white bg-indigo-600
                                           hover:bg-indigo-700 focus:outline-none focus:ring-2
                                           focus:ring-offset-2 focus:ring-indigo-500">
                                Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Results --}}
            @if ($selectedDomainId && $q !== '')
                <div class="space-y-6">
                    {{-- Categories --}}
                    @if ($categories->isNotEmpty())
                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="px-6 py-3 border-b border-gray-200 flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-gray-800">
                                    Categories ({{ $categories->count() }})
                                </h3>
                            </div>
                            <div class="divide-y divide-gray-100">
                                @foreach ($categories as $category)
                                    <a href="{{ route('knowledge-categories.index', [
                                            'domainid' => $category->domainid,
                                            'categoryid' => $category->id,
                                        ]) }}"
                                    class="block px-6 py-3 hover:bg-gray-50">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $category->categoryname }}
                                        </div>
                                        @if ($category->description)
                                            <div class="mt-1 text-xs text-gray-700 line-clamp-2">
                                                {{ $category->description }}
                                            </div>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    {{-- Items --}}
                    @if ($items->isNotEmpty())
                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="px-6 py-3 border-b border-gray-200 flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-gray-800">
                                    Items ({{ $items->count() }})
                                </h3>
                            </div>
                            <div class="divide-y divide-gray-100">
                                @foreach ($items as $item)
                                    <a href="{{ route('knowledge.items.edit', [
                                            'knowledgeItem' => $item,
                                            'return_to' => url()->full(),
                                        ]) }}"
                                       class="block px-6 py-3 hover:bg-gray-50">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $item->itemname }}
                                        </div>
                                        @if ($item->summary)
                                            <div class="mt-1 text-xs text-gray-600 line-clamp-2">
                                                {{ $item->summary }}
                                            </div>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Notes --}}
                    @if ($notes->isNotEmpty())
                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="px-6 py-3 border-b border-gray-200 flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-gray-800">
                                    Notes ({{ $notes->count() }})
                                </h3>
                            </div>
                            <div class="divide-y divide-gray-100">
                                @foreach ($notes as $note)
                                    <a href="{{ route('knowledge.items.edit', [
                                        'knowledgeItem' => $note->knowledgeItem,
                                        'return_to' => url()->full(),
                                    ]) }}#notes"
                                       class="block px-6 py-3 hover:bg-gray-50">
                                        <div class="text-xs text-gray-500 mb-1">
                                            Note: {{ $note->knowledgeItem->itemname ?? 'Note #' . $note->knowledgeitemid }}
                                        </div>
                                        <div class="text-sm text-gray-800 line-clamp-3">
                                            {{ $note->notecontent }}
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Sources --}}
                    @if ($sources->isNotEmpty())
                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="px-6 py-3 border-b border-gray-200 flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-gray-800">
                                    Sources ({{ $sources->count() }})
                                </h3>
                            </div>
                            <div class="divide-y divide-gray-100">
                                @foreach ($sources as $source)
                                    <a href="{{ route('knowledge.items.edit', [
                                        'knowledgeItem' => $source->knowledgeItem,
                                        'return_to' => url()->full(),
                                    ]) }}#sources"
                                       class="block px-6 py-3 hover:bg-gray-50">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $source->sourcetitle ?? $source->sourceurl }}
                                        </div>
                                        @if ($source->sourcepublisher)
                                            <div class="text-xs text-gray-500">
                                                {{ $source->sourcepublisher }}
                                            </div>
                                        @endif
                                        @if ($source->importedsummary)
                                            <div class="mt-1 text-xs text-gray-700 line-clamp-2">
                                                {{ $source->importedsummary }}
                                            </div>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    {{-- Relationships --}}
{{-- Relationships --}}
@if ($relationships->isNotEmpty())
    <div class="bg-white shadow-sm sm:rounded-lg">
        <div class="px-6 py-3 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-800">
                Relationships ({{ $relationships->count() }})
            </h3>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach ($relationships as $row)
                <a href="{{ route('knowledge.items.edit', [
                        'knowledgeItem' => $row['targetItem'],
                        'return_to' => url()->full(),
                        'tab' => 'relationships',
                    ]) }}"
                class="block px-6 py-3 hover:bg-gray-50">
                    <div class="text-sm font-medium text-gray-900">
                        <span>{{ $row['fromText'] }}</span>
                        <span class="mx-1 text-gray-400"> - </span>
                        <span class="font-semibold text-gray-800">{{ $row['displayTypeLabel'] }}</span>
                        <span class="mx-1 text-gray-400"> - </span>
                        <span>{{ $row['toText'] }}</span>
                    </div>

                    <div class="mt-1 text-xs text-gray-500">
                        @if($row['relationship']->effective_date)
                            Effective: {{ $row['relationship']->effective_date->format('d M Y') }}
                        @endif
                        @if(!is_null($row['relationship']->sortorder))
                            · Sort: {{ $row['relationship']->sortorder }}
                        @endif
                        · Direction: {{ ucfirst($row['direction']) }}
                    </div>

                    @if ($row['relationship']->notes)
                        <div class="mt-1 text-xs text-gray-700 line-clamp-2">
                            {{ $row['relationship']->notes }}
                        </div>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
@endif

                    {{-- Reviews / logs --}}
                    @if ($reviews->isNotEmpty())
                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="px-6 py-3 border-b border-gray-200 flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-gray-800">
                                    Review logs ({{ $reviews->count() }})
                                </h3>
                            </div>
                            <div class="divide-y divide-gray-100">
                                @foreach ($reviews as $review)
                                    <a href="{{ route('knowledge.items.edit', [
                                        'knowledgeItem' => $review->knowledgeItem,
                                        'return_to' => url()->full(),
                                    ]) }}#reviews"
                                    class="block px-6 py-3 hover:bg-gray-50">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $review->reviewtype ?: 'Review #' . $review->id }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $review->reviewdate }}
                                            @if($review->outcome)
                                                · {{ $review->outcome }}
                                            @endif
                                        </div>
                                        @if ($review->summary)
                                            <div class="mt-1 text-xs text-gray-700 line-clamp-2">
                                                {{ $review->summary }}
                                            </div>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if (
                        $categories->isEmpty()
                        && $items->isEmpty()
                        && $notes->isEmpty()
                        && $sources->isEmpty()
                        && $reviews->isEmpty()
                        && $relationships->isEmpty()
                    )
                        <div class="text-sm text-gray-500">
                            No results found for “{{ $q }}” in this domain.
                        </div>
                    @endif
                </div>
            @elseif(request()->has('q') || request()->has('domainid'))
                <div class="text-sm text-gray-500">
                    Please select a domain and enter keywords, then search.
                </div>
            @endif
        </div>
    </div>
</x-app-layout>