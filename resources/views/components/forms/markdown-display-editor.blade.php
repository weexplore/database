@props([
    'name',
    'id' => null,
    'label',
    'value' => '',
    'rows' => 6,
    'placeholder' => '',
    'help' => null,
    'startOpen' => false,
    'collapsible' => true,
    'collapsedHeight' => '18rem',
])

@php
    $fieldId = $id ?? $name;
    $fieldValue = old($name, $value ?? '');
    $hasContent = filled($fieldValue);
@endphp


    <div
        class="rounded-lg border border-gray-200 bg-white"
        x-data="{
            editing: @js($startOpen || !$hasContent),
            content: @js($fieldValue),
            expanded: false,
            collapsible: @js($collapsible),
            collapsedHeight: @js($collapsedHeight),
            isOverflowing: false,

            checkOverflow() {
                this.$nextTick(() => {
                    const content = this.$refs.renderedContent;

                    if (!content || !this.collapsible) {
                        this.isOverflowing = false;
                        return;
                    }

                    if (this.expanded) {
                        return;
                    }

                    this.isOverflowing = content.scrollHeight > content.clientHeight;
                });
            },

            toggleExpanded() {
                this.expanded = !this.expanded;

                this.$nextTick(() => {
                    window.renderMarkdownMath?.(this.$refs.renderedContent);
                });
            },
        }"
        x-init="$nextTick(() => checkOverflow())"
    >

    <div class="flex items-center justify-between gap-3 border-b border-gray-200 bg-gray-50 px-4 py-3">
        <div class="min-w-0">
            <h5 class="text-sm font-semibold text-gray-900">
                {{ $label }}
            </h5>

            @if($help)
                <p class="mt-0.5 text-xs text-gray-500">
                    {{ $help }}
                </p>
            @endif
        </div>

        <button type="button"
                @click="editing = !editing"
                class="inline-flex shrink-0 items-center rounded border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100"
                x-text="editing ? 'Hide editor' : 'Edit'">
        </button>
    </div>

    {{-- Display mode --}}
    <div
        x-show="!editing"
        x-cloak
        class="p-4"
        x-init="$nextTick(() => checkOverflow())"
    >
        <template x-if="content.trim() !== ''">
            <div>
                <div
                    class="relative"
                    :style="
                        collapsible && !expanded
                            ? { maxHeight: collapsedHeight, overflow: 'hidden' }
                            : {}
                    "
                >
                    <div
                        x-ref="renderedContent"
                        class="relative"
                        :style="
                            collapsible && !expanded
                                ? { maxHeight: collapsedHeight, overflow: 'hidden' }
                                : {}
                        "
                    >
                        <div
                            class="markdown-content prose prose-sm max-w-none text-gray-700"
                            x-init="$nextTick(() => {
                                window.renderMarkdownMath?.($el);
                                checkOverflow();
                            })"
                        >
                            @include('partials.markdown.rendered-block', [
                                'content' => $fieldValue,
                                'collapsible' => false,
                            ])
                        </div>

                        <div
                            x-show="collapsible && isOverflowing && !expanded"
                            x-cloak
                            class="pointer-events-none absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-white via-white/90 to-transparent"
                        ></div>
                    </div>

                    <div
                        x-show="collapsible && isOverflowing && !expanded"
                        x-cloak
                        class="pointer-events-none absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-white via-white/90 to-transparent"
                    ></div>
                </div>

                <button
                    type="button"
                    x-show="collapsible && isOverflowing"
                    x-cloak
                    @click="toggleExpanded()"
                    class="mt-3 inline-flex items-center rounded text-xs font-medium text-blue-600 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    :aria-expanded="expanded ? 'true' : 'false'"
                >
                    <span x-text="expanded ? 'Show less' : 'Show more'"></span>

                    <svg
                        class="ml-1 h-4 w-4 transition-transform"
                        :class="{ 'rotate-180': expanded }"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-hidden="true"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </button>
            </div>
        </template>

        <p
            x-show="content.trim() === ''"
            x-cloak
            class="text-sm italic text-gray-500"
        >
            No {{ strtolower($label) }} entered yet.
        </p>
    </div>

    {{-- Edit mode --}}
    <div x-show="editing"
         x-cloak
         class="space-y-3 p-4">
        <textarea name="{{ $name }}"
                  id="{{ $fieldId }}"
                  x-model="content"
                  rows="{{ $rows }}"
                  placeholder="{{ $placeholder }}"
                  class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </textarea>

        <p class="text-xs text-gray-500">
            Markdown is supported. Use <code>$$...$$</code> for a displayed
            mathematical, logical, or flow expression. Changes are saved with the
            main “Save Changes” button.
        </p>
    </div>

    {{-- Ensure the value is posted even when editor is collapsed --}}
    <template x-if="!editing">
        <input type="hidden"
               name="{{ $name }}"
               :value="content">
    </template>
</div>