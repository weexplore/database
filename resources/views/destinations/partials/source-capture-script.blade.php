<script>
document.addEventListener('DOMContentLoaded', function () {
    const root = document.getElementById(
        'destination-internet-sources'
    );

    if (!root) return;

    const csrfToken = @js(csrf_token());

    function feedback(panel, message, isError = false) {
        const element = panel.querySelector(
            '[data-capture-feedback]'
        );

        element.textContent = message;

        element.className = isError
            ? 'mt-3 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-800 whitespace-pre-line'
            : 'mt-3 rounded-md border border-blue-200 bg-blue-50 px-3 py-2 text-xs text-blue-800 whitespace-pre-line';
    }

    root.addEventListener('click', async function (event) {
        if (!(event.target instanceof Element)) return;

        const button = event.target.closest(
            '[data-capture-action]'
        );

        if (!button) return;

        const panel = button.closest('[data-capture-panel]');
        const card = button.closest('[data-source-card]');

        if (
            !panel
            || !card
            || panel.dataset.busy === 'true'
        ) {
            return;
        }

        const action = button.dataset.captureAction;

        const textarea = panel.querySelector(
            '[data-captured-text]'
        );

        /*
         * Selected text → field extract or item candidate.
         */
        if (['use-selection', 'use-candidate'].includes(action)) {
            const selectedText = textarea.value
                .slice(
                    textarea.selectionStart,
                    textarea.selectionEnd
                )
                .trim();

            if (!selectedText) {
                feedback(
                    panel,
                    'Select a passage in the captured text first.',
                    true
                );
                return;
            }

            const sourceLink = card.querySelector(
                'a[target="_blank"]'
            );

            card.dispatchEvent(new CustomEvent(
                action === 'use-candidate'
                    ? 'source-item-candidate-selected'
                    : 'source-content-selected',
                {
                    bubbles: true,
                    detail: {
                        text: selectedText,
                        sourceId: card.dataset.sourceId,
                        sourceTitle:
                            card.querySelector('h4')
                                ?.textContent.trim()
                            || 'Source',
                        sourceUrl: sourceLink?.href || '',
                    },
                }
            ));

            return;
        }

        if (!['fetch', 'copy-summary'].includes(action)) {
            return;
        }

        /*
         * Use the existing check for unsaved source/extract
         * edits and pending extract requests.
         */
        const beforeCapture = new CustomEvent(
            'source-capture-before',
            {
                bubbles: true,
                cancelable: true,
            }
        );

        if (!card.dispatchEvent(beforeCapture)) {
            feedback(
                panel,
                'Save source/extract edits and wait for pending actions first. The summary-copy action uses the saved summary.',
                true
            );
            return;
        }

        const hasContent = panel.dataset.hasContent === '1';
        const copyingSummary = action === 'copy-summary';

        if (hasContent) {
            const confirmation = copyingSummary
                ? 'Replace the existing captured content with the saved Imported Summary? Existing extracts and item candidates will not change.'
                : 'Replace the existing captured content by fetching the webpage again? Existing extracts and item candidates will not change.';

            if (!confirm(confirmation)) return;
        }

        const endpoint = copyingSummary
            ? panel.dataset.copySummaryUrl
            : panel.dataset.captureUrl;

        if (!endpoint) {
            feedback(
                panel,
                'The required endpoint is missing from this source card.',
                true
            );
            return;
        }

        panel.dataset.busy = 'true';
        panel.setAttribute('aria-busy', 'true');

        const originalLabel = button.textContent;
        const cardWasInert = card.inert;

        button.textContent = copyingSummary
            ? 'Copying summary...'
            : 'Fetching page...';

        card.inert = true;

        feedback(
            panel,
            copyingSummary
                ? 'Copying the saved Imported Summary...'
                : 'Fetching and extracting readable page text...'
        );

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    replace: hasContent,
                }),
            });

            const contentType = response.headers.get(
                'content-type'
            ) ?? '';

            const data = contentType.includes(
                'application/json'
            )
                ? await response.json()
                : null;

            if (!response.ok) {
                const errors = data?.errors
                    ? Object.values(data.errors)
                        .flat()
                        .join('\n')
                    : null;

                throw new Error(
                    errors
                    || data?.message
                    || 'The content could not be saved. Check your session and server logs.'
                );
            }

            if (typeof data?.text !== 'string') {
                throw new Error(
                    'The server returned no content.'
                );
            }

            textarea.value = data.text;
            panel.dataset.hasContent = '1';

            panel.querySelector('[data-capture-empty]')
                .classList.add('hidden');

            const details = panel.querySelector(
                '[data-capture-details]'
            );

            details.classList.remove('hidden');
            details.open = true;

            const fetchButton = panel.querySelector(
                '[data-capture-action="fetch"]'
            );

            if (fetchButton) {
                fetchButton.textContent = 'Fetch Again';
            }

            let message = data.message
                || 'Content saved.';

            message += '\nCharacters: ' + data.characters;

            if (data.final_url) {
                message += '\nFinal URL: ' + data.final_url;
            }

            feedback(panel, message);
        } catch (error) {
            feedback(
                panel,
                error.message || 'The action failed.',
                true
            );
        } finally {
            if (copyingSummary) {
                button.textContent = originalLabel;
            } else {
                button.textContent =
                    panel.dataset.hasContent === '1'
                        ? 'Fetch Again'
                        : originalLabel;
            }

            card.inert = cardWasInert;
            panel.removeAttribute('aria-busy');
            delete panel.dataset.busy;
        }
    });
});
</script>