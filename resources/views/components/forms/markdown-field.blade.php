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
    'startCollapsed' => true,
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
            height: 12rem;
            min-height: 12rem;
            max-height: 24rem;
            box-sizing: border-box;
            resize: vertical;
            overflow-y: auto;
            overflow-x: hidden;
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

    {{-- Editor panel: collapsed by default --}}
    <div
        id="{{ $fieldId }}-editor"
        class="rounded-lg border border-gray-200 bg-white p-4 space-y-4 js-markdown-editor-container"
        @if($startCollapsed) hidden @endif
    >
        <textarea
            name="{{ $fieldName }}"
            id="{{ $fieldId }}"
            rows="{{ $rows }}"
            data-min-rows="{{ $minRows }}"
            data-max-rows="{{ $maxRows }}"
            data-markdown-render-target-id="{{ $renderedId }}"
            placeholder="{{ $placeholder }}"
            class="js-markdown-source markdown-editor-textarea block w-full rounded-md border-gray-300 shadow-sm text-sm"
        >{{ $fieldValue }}</textarea>

        @if($help)
            <p class="text-xs text-gray-500">
                {{ $help }}
            </p>
        @endif
    </div>

    {{-- Toggle button for editor --}}
    <button type="button"
            class="markdown-field-toggle"
            data-target="{{ $fieldId }}-editor"
            onclick="
                (function(btn) {
                    var panel = document.getElementById(btn.dataset.target);
                    if (!panel) return;

                    var textarea = panel.querySelector('.markdown-editor-textarea');

                    if (panel.hasAttribute('hidden')) {
                        panel.removeAttribute('hidden');

                        if (textarea) {
                            textarea.style.overflowY = 'scroll';
                            textarea.style.height = '12rem';

                            requestAnimationFrame(function () {
                                textarea.dispatchEvent(new Event('input', { bubbles: true }));
                                textarea.focus();
                            });
                        }
                    } else {
                        panel.setAttribute('hidden', 'hidden');
                    }

                    btn.textContent = panel.hasAttribute('hidden')
                        ? 'Edit text'
                        : 'Hide editor';
                })(this);
            ">
        {{ $startCollapsed ? 'Edit text' : 'Hide editor' }}
    </button>

    {{-- Always-visible preview --}}
    <div id="{{ $renderedId }}-container"
         class="rounded-lg border border-gray-200 bg-gray-50 p-4 markdown-preview-panel js-markdown-rendered-block">
        <div id="{{ $renderedId }}"
             class="markdown-content text-sm text-gray-700 js-markdown-render-target"
             data-empty-html="<p class='markdown-rendered-empty'>No content yet.</p>"></div>
    </div>

    @error($fieldName)
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.js-markdown-editor-toggle').forEach(btn => {
                const targetId = btn.dataset.target;
                const panel = document.getElementById(targetId);
                if (!panel) return;

                function updateLabel() {
                    if (panel.hasAttribute('hidden')) {
                        btn.textContent = 'Edit text';
                    } else {
                        btn.textContent = 'Hide editor';
                    }
                }

                btn.addEventListener('click', () => {
                    if (panel.hasAttribute('hidden')) {
                        panel.removeAttribute('hidden');
                    } else {
                        panel.setAttribute('hidden', 'hidden');
                    }
                    updateLabel();
                });

                updateLabel();
            });
        });
    </script>
@endonce