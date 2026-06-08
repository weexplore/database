@foreach($nodes as $node)
    @php
        $nodeId = (int) $node->id;
        $hasChildren = $node->children->isNotEmpty();
        $isExpanded = isset($expandedIdLookup[$nodeId]);

        // Build nextExpanded array without collections
        $nextExpanded = $expandedIds ?? [];
        if ($isExpanded) {
            // Collapse: remove this node id
            $nextExpanded = array_values(array_filter(
                $nextExpanded,
                fn ($id) => (int) $id !== $nodeId
            ));
        } else {
            // Expand: add this node id if not present
            if (!in_array($nodeId, $nextExpanded, true)) {
                $nextExpanded[] = $nodeId;
            }
        }

        // Route params reused for both links
        $baseParams = array_merge($baseFilters, [
            'domainid' => $domainId,
            'categoryid' => $nodeId,
        ]);
    @endphp

    <div class="flex items-center gap-1"
         style="margin-left: {{ $depth * 16 }}px;">

        @if($hasChildren)
            <a href="{{ route('knowledge-categories.index', array_merge($baseParams, [
                    'expanded' => $nextExpanded,
                ])) }}"
               class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-slate-300 hover:bg-slate-800 hover:text-white"
               title="{{ $isExpanded ? 'Collapse' : 'Expand' }}">
                <span class="text-base leading-none font-semibold">
                    {{ $isExpanded ? '▾' : '▸' }}
                </span>
            </a>
        @else
            <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center text-slate-600">
                <span class="text-sm leading-none">•</span>
            </span>
        @endif

        <a href="{{ route('knowledge-categories.index', array_merge($baseParams, [
                'expanded' => $expandedIds ?? [],
            ])) }}"
           class="flex min-w-0 flex-1 items-center gap-2 rounded-md px-2 py-2 text-sm {{ (int) $selectedId === $nodeId ? 'bg-sky-900 text-sky-100' : 'text-slate-200 hover:bg-slate-800' }}">
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
            'expandedIds' => $expandedIds,
            'expandedIdLookup' => $expandedIdLookup,
            'baseFilters' => $baseFilters,
        ])
    @endif
@endforeach