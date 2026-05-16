@foreach($nodes as $node)
    @php
        $hasChildren = $node->children->isNotEmpty();
        $isExpanded = in_array((int) $node->id, $expandedIds ?? [], true);

        $nextExpanded = collect($expandedIds ?? []);
        if ($isExpanded) {
            $nextExpanded = $nextExpanded
                ->reject(fn ($id) => (int) $id === (int) $node->id)
                ->values();
        } else {
            $nextExpanded = $nextExpanded
                ->push((int) $node->id)
                ->unique()
                ->values();
        }
    @endphp

    <div class="flex items-center gap-1"
         style="margin-left: {{ $depth * 16 }}px;">

        @if($hasChildren)
            <a href="{{ route('knowledge-categories.index', [
                    'domainid' => $domainId,
                    'categoryid' => $node->id,
                    'search' => request('search'),
                    'knowledgeitemtypeid' => request('knowledgeitemtypeid'),
                    'itemstatus' => request('itemstatus'),
                    'expanded' => $nextExpanded->all(),
                ]) }}"
               class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-slate-300 hover:bg-slate-800 hover:text-white"
               title="{{ $isExpanded ? 'Collapse' : 'Expand' }}">
                <span class="text-base leading-none font-semibold">{{ $isExpanded ? '▾' : '▸' }}</span>
            </a>
        @else
            <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center text-slate-600">
                <span class="text-sm leading-none">•</span>
            </span>
        @endif

        <a href="{{ route('knowledge-categories.index', [
                'domainid' => $domainId,
                'categoryid' => $node->id,
                'search' => request('search'),
                'knowledgeitemtypeid' => request('knowledgeitemtypeid'),
                'itemstatus' => request('itemstatus'),
                'expanded' => $expandedIds ?? [],
            ]) }}"
           class="flex min-w-0 flex-1 items-center gap-2 rounded-md px-2 py-2 text-sm {{ (int) $selectedId === (int) $node->id ? 'bg-sky-900 text-sky-100' : 'text-slate-200 hover:bg-slate-800' }}">
            <span class="text-base leading-none">📁</span>
            <span class="truncate">{{ $node->categoryname }}</span>
        </a>
    </div>

    @if($hasChildren && $isExpanded)
        @include('knowledge-categories.partials.tree-nodes', [
            'nodes' => $node->children,
            'selectedId' => $selectedId,
            'depth' => $depth + 1,
            'domainId' => $domainId,
            'expandedIds' => $expandedIds ?? [],
        ])
    @endif
@endforeach