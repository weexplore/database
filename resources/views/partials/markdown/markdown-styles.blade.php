{{-- resources/views/partials/forms/markdown-field-scripts.blade.php --}}
<script src="https://cdn.jsdelivr.net/npm/marked/lib/marked.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.1.6/dist/purify.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const textareas = Array.from(document.querySelectorAll('.js-auto-resize-textarea'));
    const markdownSources = Array.from(document.querySelectorAll('.js-markdown-source'));
    const editorToggles = Array.from(document.querySelectorAll('.js-markdown-editor-toggle'));
    const renderedToggles = Array.from(document.querySelectorAll('.js-markdown-rendered-toggle'));

    function getLineHeight(textarea) {
        const computed = window.getComputedStyle(textarea);
        return parseFloat(computed.lineHeight) || 20;
    }

    function getVerticalExtras(textarea) {
        const computed = window.getComputedStyle(textarea);
        const paddingTop = parseFloat(computed.paddingTop) || 0;
        const paddingBottom = parseFloat(computed.paddingBottom) || 0;
        const borderTop = parseFloat(computed.borderTopWidth) || 0;
        const borderBottom = parseFloat(computed.borderBottomWidth) || 0;

        return paddingTop + paddingBottom + borderTop + borderBottom;
    }

    function getMinHeight(textarea) {
        const minRows = parseInt(textarea.dataset.minRows || textarea.getAttribute('rows') || 4, 10);
        return (getLineHeight(textarea) * minRows) + getVerticalExtras(textarea);
    }

    function getMaxHeight(textarea) {
        const maxRows = parseInt(textarea.dataset.maxRows || 14, 10);
        return (getLineHeight(textarea) * maxRows) + getVerticalExtras(textarea);
    }

    function autoResize(textarea) {
        const minHeight = getMinHeight(textarea);
        const maxHeight = getMaxHeight(textarea);

        textarea.style.height = 'auto';

        const nextHeight = Math.min(Math.max(textarea.scrollHeight, minHeight), maxHeight);

        textarea.style.height = nextHeight + 'px';
        textarea.style.overflowY = textarea.scrollHeight > maxHeight ? 'auto' : 'hidden';
        textarea.style.resize = 'vertical';
    }

    function debounce(fn, delay) {
        let timeoutId = null;

        return function (...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    function renderMarkdownToTarget(textarea) {
        const targetId = textarea.dataset.markdownRenderTargetId;
        if (!targetId) return;

        const target = document.getElementById(targetId);
        const container = document.getElementById(targetId + '-container');

        if (!target || !container) return;

        const toggleWrapper = container.nextElementSibling;
        const toggle = toggleWrapper ? toggleWrapper.querySelector('.js-markdown-rendered-toggle') : null;

        const rawValue = textarea.value || '';
        const value = rawValue.trim();
        const emptyHtml = target.dataset.emptyHtml || '<p class="markdown-rendered-empty">No content yet.</p>';

        if (!value) {
            target.innerHTML = emptyHtml;
            container.classList.remove('is-collapsed');
            if (toggle) {
                toggle.hidden = true;
                toggle.textContent = 'Show more';
            }
            return;
        }

        const rendered = marked.parse(rawValue, {
            gfm: true,
            breaks: true
        });

        target.innerHTML = DOMPurify.sanitize(rendered);

        requestAnimationFrame(function () {
            const shouldCollapse = target.scrollHeight > 260;
            container.classList.toggle('is-collapsed', shouldCollapse);

            if (toggle) {
                toggle.hidden = !shouldCollapse;
                toggle.textContent = shouldCollapse ? 'Show more' : 'Show less';
            }
        });
    }

    editorToggles.forEach(function (button) {
        const targetId = button.dataset.target;
        const panel = document.getElementById(targetId);

        if (!panel) return;

        button.addEventListener('click', function () {
            const isHidden = panel.hasAttribute('hidden');

            if (isHidden) {
                panel.removeAttribute('hidden');
                button.textContent = 'Hide editor';
                button.setAttribute('aria-expanded', 'true');

                const textarea = panel.querySelector('.js-auto-resize-textarea');
                if (textarea) {
                    autoResize(textarea);
                    textarea.focus();
                }
            } else {
                panel.setAttribute('hidden', 'hidden');
                button.textContent = 'Show editor';
                button.setAttribute('aria-expanded', 'false');
            }
        });
    });

    renderedToggles.forEach(function (button) {
        const toggleWrapper = button.parentElement;
        const container = toggleWrapper ? toggleWrapper.previousElementSibling : null;

        if (!container || !container.classList.contains('js-markdown-rendered-block')) return;

        button.addEventListener('click', function () {
            const isCollapsed = container.classList.contains('is-collapsed');
            container.classList.toggle('is-collapsed', !isCollapsed);
            button.textContent = isCollapsed ? 'Show less' : 'Show more';
        });
    });

    textareas.forEach(function (textarea) {
        autoResize(textarea);

        textarea.addEventListener('input', function () {
            autoResize(textarea);
        });
    });

    markdownSources.forEach(function (textarea) {
        const updateRendered = debounce(function () {
            renderMarkdownToTarget(textarea);
        }, 60);

        renderMarkdownToTarget(textarea);

        textarea.addEventListener('input', function () {
            updateRendered();
        });

        textarea.addEventListener('change', function () {
            renderMarkdownToTarget(textarea);
        });

        textarea.addEventListener('keyup', function () {
            updateRendered();
        });
    });

    window.addEventListener('resize', function () {
        textareas.forEach(autoResize);
        markdownSources.forEach(renderMarkdownToTarget);
    });
});
</script>
