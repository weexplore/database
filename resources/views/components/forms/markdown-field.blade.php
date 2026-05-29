{{-- resources/views/components/forms/markdown-field.blade.php --}}
@props([
    'name',
    'id' => null,
    'label' => null,
    'value' => '',
    'rows' => 6,
    'minRows' => 4,
    'maxRows' => 14,
    'placeholder' => '',
    'help' => null,
])

@php
    $fieldId = $id ?? $name;
    $fieldName = $name;
    $fieldValue = old($fieldName, $value);
    $renderedId = $fieldId . '-markdown-rendered';
@endphp

@once
    <style>
        .markdown-field-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.25rem 0.625rem;
            border-radius: 0.375rem;
            background: rgb(243 244 246);
            color: rgb(55 65 81);
            font-size: 0.75rem;
            font-weight: 500;
            line-height: 1rem;
            border: 1px solid rgb(229 231 235);
        }

        .markdown-field-toggle:hover {
            background: rgb(229 231 235);
        }

        .markdown-editor-textarea {
            width: 100%;
            min-height: 8rem;
            max-height: 24rem;
            resize: vertical;
            overflow-y: auto;
        }

        .markdown-preview-panel[hidden] {
            display: none !important;
        }

        .markdown-preview-panel {
            max-height: 22rem;
            overflow-y: auto;
            overflow-x: hidden;
            padding-bottom: 0.25rem;
        }

        .markdown-preview-panel .markdown-content {
            max-width: none;
        }

        .markdown-rendered-empty {
            color: rgb(107 114 128);
            font-style: italic;
        }
    </style>
@endonce

<div class="space-y-3">
    @if($label)
        <label for="{{ $fieldId }}" class="block text-sm font-medium text-gray-700">
            {{ $label }}
        </label>
    @endif

    <div class="rounded-lg border border-gray-200 bg-white p-4 space-y-4">
        <textarea
            name="{{ $fieldName }}"
            id="{{ $fieldId }}"
            rows="{{ $rows }}"
            data-min-rows="{{ $minRows }}"
            data-max-rows="{{ $maxRows }}"
            data-markdown-render-target-id="{{ $renderedId }}"
            placeholder="{{ $placeholder }}"
            class="js-auto-resize-textarea js-markdown-source markdown-editor-textarea block w-full rounded-md border-gray-300 shadow-sm text-sm"
        >{{ $fieldValue }}</textarea>

        @if($help)
            <p class="text-xs text-gray-500">
                {{ $help }}
            </p>
        @endif

        <div class="flex items-center justify-start gap-3">
            <button type="button"
                    class="markdown-field-toggle js-markdown-preview-toggle"
                    data-target="{{ $renderedId }}-container"
                    aria-expanded="false"
                    aria-controls="{{ $renderedId }}-container">
                Show preview
            </button>
        </div>
    </div>

    <div id="{{ $renderedId }}-container"
         class="rounded-lg border border-gray-200 bg-gray-50 p-4 markdown-preview-panel js-markdown-rendered-block"
         hidden>
        <div id="{{ $renderedId }}"
             class="markdown-content text-sm text-gray-700 js-markdown-render-target"
             data-empty-html="<p class='markdown-rendered-empty'>No content yet.</p>"></div>
    </div>

    @error($fieldName)
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>