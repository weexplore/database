@props([
    'name', 'id' => null, 'label', 'value' => '', 'rows' => 6,
    'placeholder' => '', 'help' => null, 'startOpen' => false,
    'collapsible' => true, 'collapsedHeight' => '18rem',
])
@php
    $fieldId = $id ?? $name;
    $fieldValue = old($name, $value ?? '');
    $hasContent = filled($fieldValue);
@endphp
<style>
    .markdown-display-editor-collapsed {
        max-height: var(--markdown-collapsed-height);
        overflow: hidden;
    }
</style>
<div class="rounded-lg border border-gray-200 bg-white"
     data-markdown-field="{{ $name }}"
     x-data="{
        fieldName: @js($name),
        editing: @js($startOpen || !$hasContent),
        content: @js($fieldValue),
        composedPreviewHtml: null,
        expanded: false,
        collapsible: @js($collapsible),
        collapsedHeight: @js($collapsedHeight),
        hasOverflow: false,
        measureOverflow(container) {
            this.$nextTick(() => {
                if (!container || !this.collapsible) {
                    this.hasOverflow = false;
                    return;
                }
                const collapseClass = 'markdown-display-editor-collapsed';
                const wasCollapsed = !this.expanded;
                container.classList.add(collapseClass);
                const collapsedHeight = container.clientHeight;
                container.classList.remove(collapseClass);
                this.hasOverflow = container.scrollHeight > collapsedHeight + 2;
                container.classList.toggle(collapseClass, wasCollapsed);
            });
        },
        refreshDisplay() {
            this.$nextTick(() => {
                window.renderMarkdownMath?.(this.$refs.renderedContent);
                this.measureOverflow(this.$refs.renderedContent);
            });
        },
        toggleExpanded() {
            this.expanded = !this.expanded;
            this.refreshDisplay();
        },
        receiveComposed(detail) {
            if (detail.field !== this.fieldName) return;
            this.content = detail.value;
            this.composedPreviewHtml = detail.html;
            this.editing = false;
            this.expanded = false;
            this.refreshDisplay();
        },
     }"
     @destination-markdown-set.window="receiveComposed($event.detail)">
    <div class="flex items-center justify-between gap-3 border-b border-gray-200 bg-gray-50 px-4 py-3">
        <div class="min-w-0">
            <h5 class="text-sm font-semibold text-gray-900">{{ $label }}</h5>
            @if ($help)<p class="mt-0.5 text-xs text-gray-500">{{ $help }}</p>@endif
        </div>
        <button type="button" @click="editing = !editing; refreshDisplay()"
                class="inline-flex shrink-0 items-center rounded border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100"
                x-text="editing ? 'Hide editor' : 'Edit'"></button>
    </div>
    <div x-show="!editing" x-cloak class="p-4">
        <template x-if="content.trim() !== ''">
            <div>
                <div x-ref="renderedContent" class="relative"
                     :class="{ 'markdown-display-editor-collapsed': collapsible && !expanded }"
                     :style="{ '--markdown-collapsed-height': collapsedHeight }"
                     x-init="$nextTick(() => refreshDisplay())">
                    <div x-show="composedPreviewHtml === null"
                         class="markdown-content prose prose-sm max-w-none text-gray-700">
                        @include('partials.markdown.rendered-block', [
                            'content' => $fieldValue,
                            'collapsible' => false,
                        ])
                    </div>
                    <div x-show="composedPreviewHtml !== null"
                         x-html="composedPreviewHtml || ''"
                         class="markdown-content prose prose-sm max-w-none text-gray-700"></div>
                    <div x-show="collapsible && hasOverflow && !expanded" x-cloak
                         class="pointer-events-none absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-white via-white/90 to-transparent"></div>
                </div>
                <button type="button" x-show="collapsible && hasOverflow" x-cloak
                        @click="toggleExpanded()" :aria-expanded="expanded ? 'true' : 'false'"
                        class="mt-3 text-xs font-medium text-blue-600 hover:text-blue-800">
                    <span x-text="expanded ? 'Show less' : 'Show more'"></span>
                </button>
            </div>
        </template>
        <p x-show="content.trim() === ''" x-cloak class="text-sm italic text-gray-500">No {{ strtolower($label) }} entered yet.</p>
    </div>
    <div x-show="editing" x-cloak class="space-y-3 p-4">
        <textarea name="{{ $name }}" id="{{ $fieldId }}" x-model="content"
                  rows="{{ $rows }}" placeholder="{{ $placeholder }}"
                  class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
        <p class="text-xs text-gray-500">Markdown is supported. Changes are saved with the main save button. The display preview reflects saved content.</p>
    </div>
    {{-- x-show does not disable the textarea: it posts even when the editor is hidden. --}}
</div>
