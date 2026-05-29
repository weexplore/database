{{-- resources/views/components/markdown/rendered-block.blade.php --}}
@props([
    'content' => '',
    'id' => null,
    'collapsible' => true,
    'collapsedLines' => 5,
])

{{-- resources/views/components/markdown/rendered-block.blade.php --}}
@props([
    'content' => '',
    'id' => null,
    'collapsible' => true,
    'collapsedLines' => 5,
])

@php
    use Illuminate\Support\Str;

    $raw = (string) $content;
    $normalised = str_replace(["\r\n", "\r"], "\n", $raw);
    $renderedMarkdown = Str::markdown($normalised);

    $containsComplexBlocks =
        str_contains($renderedMarkdown, '<table') ||
        str_contains($renderedMarkdown, '<pre') ||
        str_contains($renderedMarkdown, '<blockquote') ||
        str_contains($renderedMarkdown, '<h1') ||
        str_contains($renderedMarkdown, '<h2') ||
        str_contains($renderedMarkdown, '<h3') ||
        str_contains($renderedMarkdown, '<ul') ||
        str_contains($renderedMarkdown, '<ol');

    $isActuallyCollapsible = $collapsible && ! $containsComplexBlocks && filled($id);
    $startCollapsed = $isActuallyCollapsible ? 'true' : 'false';
@endphp

<div class="space-y-2">
    <div
        @if(filled($id)) id="{{ $id }}" @endif
        class="knowledge-note-content markdown-content text-gray-700 {{ $containsComplexBlocks ? 'knowledge-note-content--complex' : '' }}"
        data-collapsed="{{ $startCollapsed }}"
        data-collapsed-lines="{{ $collapsedLines }}">
        {!! $renderedMarkdown !!}
    </div>

    @if($isActuallyCollapsible)
        <button type="button"
                class="knowledge-note-toggle hidden inline-flex items-center px-2.5 py-1 text-xs font-medium text-blue-700 bg-blue-50 rounded hover:bg-blue-100"
                data-target="{{ $id }}"
                aria-expanded="false"
                aria-controls="{{ $id }}">
            Show more
        </button>
    @endif
</div>