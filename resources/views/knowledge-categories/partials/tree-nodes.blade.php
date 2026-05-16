@foreach($nodes as $node)
    <a href="{{ route('knowledge-categories.index', ['domainid' => $domainId, 'categoryid' => $node->id]) }}"
       class="flex items-center gap-2 rounded-md px-2 py-2 text-sm {{ (int) $selectedId === (int) $node->id ? 'bg-sky-900 text-sky-100' : 'text-slate-200 hover:bg-slate-800' }}"
       style="margin-left: {{ $depth * 16 }}px;">
        <span class="text-slate-500 w-4 text-center">{{ $node->children->isNotEmpty() ? '▾' : '•' }}</span>
        <span>📁</span>
        <span class="truncate">{{ $node->categoryname }}</span>
    </a>

    @if($node->children->isNotEmpty())
        @include('knowledge-categories.partials.tree-nodes', [
            'nodes' => $node->children,
            'selectedId' => $selectedId,
            'depth' => $depth + 1,
            'domainId' => $domainId,
        ])
    @endif
@endforeach