@props([
    'name',
    'id' => null,
    'label',
    'value' => '',
    'rows' => 6,
    'placeholder' => '',
    'help' => null,
    'startOpen' => false,
])

@php
    $fieldId = $id ?? $name;
    $fieldValue = old($name, $value ?? '');
    $hasContent = filled($fieldValue);
@endphp

<div class="rounded-lg border border-gray-200 bg-white"
     x-data="{
         editing: @js($startOpen || !$hasContent),
         content: @js($fieldValue),
     }">

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
    <div x-show="!editing" class="p-4">
        <div
            class="markdown-content prose prose-sm max-w-none text-gray-700"
            x-show="content.trim() !== ''"
            x-init="$nextTick(() => window.renderMarkdownMath($el))"
        >
            @include('partials.markdown.rendered-block', [
                'content' => $fieldValue,
                'collapsible' => false,
            ])
        </div>

        <p x-show="content.trim() === ''"
        class="text-sm italic text-gray-500">
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