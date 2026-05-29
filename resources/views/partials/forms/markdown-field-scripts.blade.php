{{-- resources/views/partials/forms/markdown-field-scripts.blade.php --}}
<script src="https://cdn.jsdelivr.net/npm/marked/lib/marked.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.1.6/dist/purify.min.js"></script>

<script>
(function () {
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

    function renderMarkdownToTarget(textarea) {
        const targetId = textarea.dataset.markdownRenderTargetId;
        if (!targetId) return;

        const target = document.getElementById(targetId);
        const container = document.getElementById(targetId + '-container');
        if (!target || !container) return;

        const moreButtonWrapper = container.nextElementSibling;
        const moreButton = moreButtonWrapper ? moreButtonWrapper.querySelector('.js-markdown-rendered-toggle') : null;

        const rawValue = textarea.value || '';
        const trimmedValue = rawValue.trim();
        const emptyHtml = target.dataset.emptyHtml || '<p class="markdown-rendered-empty">No content yet.</p>';

        if (!trimmedValue) {
            target.innerHTML = emptyHtml;
            container.classList.remove('is-collapsed');

            if (moreButton) {
                moreButton.hidden = true;
                moreButton.textContent = 'Show more';
            }

            return;
        }

        const rendered = marked.parse(rawValue, {
            gfm: true,
            breaks: true
        });

        target.innerHTML = DOMPurify.sanitize(rendered);

        requestAnimationFrame(function () {
            if (container.hidden) {
                if (moreButton) {
                    moreButton.hidden = true;
                    moreButton.textContent = 'Show more';
                }
                return;
            }

            const shouldCollapse = target.scrollHeight > 260;
            container.classList.toggle('is-collapsed', shouldCollapse);

            if (moreButton) {
                moreButton.hidden = !shouldCollapse;
                moreButton.textContent = shouldCollapse ? 'Show more' : 'Show less';
            }
        });
    }

    function initialiseMarkdownFields(root = document) {
        const textareas = Array.from(root.querySelectorAll('.js-auto-resize-textarea, .js-markdown-source'));

        textareas.forEach(function (textarea) {
            if (textarea.dataset.markdownInitialised === '1') {
                return;
            }

            textarea.dataset.markdownInitialised = '1';

            autoResize(textarea);
            renderMarkdownToTarget(textarea);

            textarea.addEventListener('input', function () {
                autoResize(textarea);
                renderMarkdownToTarget(textarea);
            });

            textarea.addEventListener('change', function () {
                autoResize(textarea);
                renderMarkdownToTarget(textarea);
            });
        });
    }

    document.addEventListener('click', function (event) {
        const previewToggle = event.target.closest('.js-markdown-preview-toggle');

        if (previewToggle) {
            event.preventDefault();

            const targetId = previewToggle.dataset.target;
            if (!targetId) return;

            const container = document.getElementById(targetId);
            if (!container) return;

            const isHidden = container.hasAttribute('hidden');

            if (isHidden) {
                container.removeAttribute('hidden');
                previewToggle.textContent = 'Hide preview';
                previewToggle.setAttribute('aria-expanded', 'true');

                const fieldWrapper = previewToggle.closest('.space-y-3');
                const textarea = fieldWrapper ? fieldWrapper.querySelector('.js-markdown-source') : null;

                if (textarea) {
                    renderMarkdownToTarget(textarea);
                }
            } else {
                container.setAttribute('hidden', 'hidden');
                container.classList.remove('is-collapsed');
                previewToggle.textContent = 'Show preview';
                previewToggle.setAttribute('aria-expanded', 'false');

                const moreButtonWrapper = container.nextElementSibling;
                const moreButton = moreButtonWrapper ? moreButtonWrapper.querySelector('.js-markdown-rendered-toggle') : null;

                if (moreButton) {
                    moreButton.hidden = true;
                    moreButton.textContent = 'Show more';
                }
            }

            return;
        }

        const moreToggle = event.target.closest('.js-markdown-rendered-toggle');

        if (moreToggle) {
            event.preventDefault();

            const targetId = moreToggle.dataset.target;
            if (!targetId) return;

            const container = document.getElementById(targetId);
            if (!container || container.hidden) return;

            const isCollapsed = container.classList.contains('is-collapsed');
            container.classList.toggle('is-collapsed', !isCollapsed);
            moreToggle.textContent = isCollapsed ? 'Show less' : 'Show more';
        }
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initialiseMarkdownFields(document);
        });
    } else {
        initialiseMarkdownFields(document);
    }

    window.addEventListener('resize', function () {
        document.querySelectorAll('.js-auto-resize-textarea, .js-markdown-source').forEach(function (textarea) {
            autoResize(textarea);
            renderMarkdownToTarget(textarea);
        });
    });
})();
</script>