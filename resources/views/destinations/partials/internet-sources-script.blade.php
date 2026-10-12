<script>
document.addEventListener('DOMContentLoaded', function () {
    const root = document.getElementById('destination-internet-sources');

    if (!root) return;

    const csrfToken = @js(csrf_token());

    const sourceCreateForm =
        document.getElementById('destination-source-create-form');

    const sourceCreateButton =
        document.getElementById('toggle-source-create');

    const dirtyForms = new Set();
    const panelStates = new WeakMap();

    function markDirty(event) {
        const target = event.target;

        if (!(target instanceof Element)) return;

        const form = target.closest(
            'form[data-source-form], ' +
            'form[data-extract-create-form], ' +
            'form[data-extract-edit-form]'
        );

        if (form && root.contains(form)) {
            dirtyForms.add(form);
        }
    }

    root.addEventListener('input', markDirty);
    root.addEventListener('change', markDirty);

    window.addEventListener('beforeunload', function (event) {
        if (dirtyForms.size === 0) return;

        event.preventDefault();
        event.returnValue = '';
    });

    function setSourceCreateVisible(visible) {
        if (!sourceCreateForm) return;

        sourceCreateForm.classList.toggle('hidden', !visible);

        sourceCreateButton?.setAttribute(
            'aria-expanded',
            String(visible)
        );

        if (visible) {
            sourceCreateForm
                .querySelector('[name="sourcetitle"]')
                ?.focus();
        }
    }

    function showFeedback(panel, message, isError = false) {
        const feedback = panel.querySelector('[data-extract-feedback]');

        feedback.textContent = message;

        feedback.className = isError
            ? 'mt-3 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-800 whitespace-pre-line'
            : 'mt-3 rounded-md border border-green-200 bg-green-50 px-3 py-2 text-xs text-green-800 whitespace-pre-line';
    }

    function setCreateVisible(panel, visible) {
        const form = panel.querySelector('[data-extract-create-form]');

        const button = panel.querySelector(
            '[data-extract-action="toggle-create"]'
        );

        if (visible) {
            const originalText = form.elements.namedItem('extractedtext');
            const summary = panel.dataset.importedSummary ?? '';

            if (
                originalText.value.trim() === ''
                && summary.trim() !== ''
            ) {
                originalText.value = summary;

                originalText.dispatchEvent(
                    new Event('input', { bubbles: true })
                );
            }
        }

        form.classList.toggle('hidden', !visible);
        button.setAttribute('aria-expanded', String(visible));

        if (visible) {
            form.querySelector('[name="extractedtext"]')?.focus();
        }
    }

    function fillEditForm(form, extract) {
        for (const field of [
            'targetfield',
            'extractedtext',
            'editedtext',
            'confidence',
            'sortorder',
        ]) {
            form.elements.namedItem(field).value =
                extract[field] ?? '';
        }
    }

    function setEditVisible(card, visible) {
        const form = card.querySelector('[data-extract-edit-form]');
        const button = card.querySelector(
            '[data-extract-action="toggle-edit"]'
        );

        form.classList.toggle('hidden', !visible);
        button.setAttribute('aria-expanded', String(visible));

        if (visible) {
            form.querySelector('[name="editedtext"]')?.focus();
        }
    }

    function statusClass(status) {
        const classes = {
            pendingreview: 'bg-amber-100 text-amber-800',
            approved: 'bg-green-100 text-green-800',
            rejected: 'bg-red-100 text-red-800',
            applied: 'bg-blue-100 text-blue-800',
        };

        return classes[status] ?? 'bg-gray-100 text-gray-800';
    }

    function buildCard(panel, extract) {
        const template = panel.querySelector(
            '[data-extract-card-template]'
        );

        const card = template.content.firstElementChild.cloneNode(true);

        card.dataset.extractId = String(extract.id);

        card.querySelector('[data-extract-target]').textContent =
            extract.targetfield_label ?? extract.targetfield;

        const status = card.querySelector('[data-extract-status]');

        status.textContent =
            extract.extractstatus_label ?? extract.extractstatus;

        status.className =
            'mt-1 inline-flex rounded px-2 py-0.5 text-xs ' +
            statusClass(extract.extractstatus);

        card.querySelector('[data-extract-confidence]').textContent =
            extract.confidence
                ? `Confidence: ${extract.confidence}/5`
                : 'Confidence: not rated';

        card.querySelector('[data-extract-original]').textContent =
            extract.extractedtext ?? '';

        const hasEditedText =
            String(extract.editedtext ?? '').trim() !== '';

        card.querySelector('[data-extract-edited-section]')
            .classList.toggle('hidden', !hasEditedText);

        card.querySelector('[data-extract-edited]').textContent =
            extract.editedtext ?? '';

        const isApplied = extract.extractstatus === 'applied';

        card.querySelector('[data-extract-actions]')
            .classList.toggle('hidden', isApplied);

        const appliedNote = card.querySelector('[data-extract-applied]');

        appliedNote.classList.toggle('hidden', !isApplied);
        appliedNote.textContent = extract.appliedon_display
            ? `Applied ${extract.appliedon_display}. This extract is read-only.`
            : 'Applied. This extract is read-only.';

        card.querySelector('[data-extract-approved-note]')
            .classList.toggle(
                'hidden',
                extract.extractstatus !== 'approved'
            );

        card.querySelector('[data-extract-action="approve"]')
            .classList.toggle(
                'hidden',
                extract.extractstatus === 'approved'
            );

        card.querySelector('[data-extract-action="reject"]')
            .classList.toggle(
                'hidden',
                extract.extractstatus === 'rejected'
            );

        fillEditForm(
            card.querySelector('[data-extract-edit-form]'),
            extract
        );

        return card;
    }

    function reorderCards(panel) {
        const state = panelStates.get(panel);
        const list = panel.querySelector('[data-extract-list]');

        const extracts = [...state.extracts.values()].sort(
            (a, b) =>
                Number(a.sortorder ?? 0) - Number(b.sortorder ?? 0)
                || Number(a.id) - Number(b.id)
        );

        for (const extract of extracts) {
            const card = list.querySelector(
                `[data-extract-id="${Number(extract.id)}"]`
            );

            if (card) list.appendChild(card);
        }

        panel.querySelector('[data-extract-empty]')
            .classList.toggle('hidden', extracts.length > 0);

        const sourceCard = panel.closest('[data-source-card]');

        const countLabel = sourceCard?.querySelector(
        '[data-source-extract-count]'
        );

        if (countLabel) {
        countLabel.textContent = String(extracts.length);
        }
    }

    function upsertCard(panel, extract) {
        const state = panelStates.get(panel);
        const list = panel.querySelector('[data-extract-list]');

        state.extracts.set(String(extract.id), extract);

        const existing = list.querySelector(
            `[data-extract-id="${Number(extract.id)}"]`
        );

        const replacement = buildCard(panel, extract);

        if (existing) {
            const oldForm = existing.querySelector(
                '[data-extract-edit-form]'
            );

            dirtyForms.delete(oldForm);
            existing.replaceWith(replacement);
        } else {
            list.appendChild(replacement);
        }

        reorderCards(panel);
    }

    function removeCard(panel, id) {
        const state = panelStates.get(panel);

        state.extracts.delete(String(id));

        const card = panel.querySelector(
            `[data-extract-id="${Number(id)}"]`
        );

        if (card) {
            dirtyForms.delete(
                card.querySelector('[data-extract-edit-form]')
            );

            card.remove();
        }

        reorderCards(panel);
    }

    function extractUrl(panel, action, id) {
        const keys = {
            update: 'updateUrl',
            approve: 'approveUrl',
            reject: 'rejectUrl',
            delete: 'deleteUrl',
        };

        const template = panel.dataset[keys[action]];
        const numericId = Number(id);

        if (!template || !Number.isSafeInteger(numericId) || numericId < 1) {
            throw new Error('The extract endpoint could not be resolved.');
        }

        const url = new URL(template, window.location.href);

        url.pathname = url.pathname.replace(
            /\/extracts\/0(?=\/|$)/,
            `/extracts/${numericId}`
        );

        return url.toString();
    }

    function formPayload(form) {
        const data = new FormData(form);

        return {
            targetfield: data.get('targetfield'),
            extractedtext: data.get('extractedtext'),
            editedtext: data.get('editedtext') || null,
            confidence: data.get('confidence') || null,
            sortorder: data.get('sortorder') || 0,
        };
    }

    async function requestJson(url, method, payload = null) {
        const options = {
            method,
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        };

        if (payload !== null) {
            options.body = JSON.stringify(payload);
        }

        const response = await fetch(url, options);

        if (response.status === 204) return null;

        const contentType = response.headers.get('content-type') ?? '';

        const data = contentType.includes('application/json')
            ? await response.json()
            : null;

        if (!response.ok) {
            const messages = data?.errors
                ? Object.values(data.errors).flat().join('\n')
                : null;

            const fallbacks = {
                401: 'Your session has expired. Sign in again.',
                403: 'You are not authorised to perform this action.',
                404: 'This source or extract could not be found.',
                419: 'Your session has expired. Save other work before refreshing.',
            };

            throw new Error(
                messages
                || fallbacks[response.status]
                || (response.status >= 500
                    ? 'The server could not complete the request.'
                    : data?.message)
                || 'The request could not be completed.'
            );
        }

        if (!data) {
            throw new Error('The server returned an unexpected response.');
        }

        return data;
    }

    async function runPanelAction(panel, callback) {
        const state = panelStates.get(panel);

        if (!state || state.busy) return;

        state.busy = true;
        panel.setAttribute('aria-busy', 'true');

        const controls = [
            ...panel.querySelectorAll('button, input, select, textarea')
        ];

        const previouslyDisabled = controls.map(
            control => control.disabled
        );

        controls.forEach(control => control.disabled = true);

        try {
            await callback();
        } catch (error) {
            showFeedback(
                panel,
                error.message || 'The request could not be completed.',
                true
            );
        } finally {
            controls.forEach((control, index) => {
                if (control.isConnected) {
                    control.disabled = previouslyDisabled[index];
                }
            });

            state.busy = false;
            panel.removeAttribute('aria-busy');
        }
    }

    function initialiseExtractPanels(container) {
        container.querySelectorAll('[data-extract-panel]')
            .forEach(function (panel) {
                if (panelStates.has(panel)) return;

                panelStates.set(panel, {
                    extracts: new Map(),
                    busy: false,
                });

                try {
                    const extracts = JSON.parse(
                        panel.dataset.extracts || '[]'
                    );

                    extracts.forEach(
                        extract => upsertCard(panel, extract)
                    );
                } catch (error) {
                    showFeedback(
                        panel,
                        'The existing extracts could not be displayed. Refresh the page.',
                        true
                    );
                }
            });
    }

    initialiseExtractPanels(root);

    root.addEventListener('destination-composition-before-save', function (event) {
        if (root.querySelector('[data-capture-panel][data-busy="true"]')) {
            event.preventDefault();
            return;
        }
        if (document.getElementById('suggest-from-web-button')?.disabled) {
            event.preventDefault();
            return;
        }
        const selectedIds = new Set(event.detail.ids.map(String));
        for (const panel of root.querySelectorAll('[data-extract-panel]')) {
            const state = panelStates.get(panel);
            if (state?.busy) {
                event.preventDefault();
                return;
            }
            for (const form of panel.querySelectorAll('[data-extract-edit-form]')) {
                const card = form.closest('[data-extract-card]');
                if (selectedIds.has(card.dataset.extractId) && dirtyForms.has(form)) {
                    event.preventDefault();
                    return;
                }
            }
        }
    });

    root.addEventListener('destination-composition-saved', function (event) {
        for (const extract of event.detail.extracts) {
            const panel = [...root.querySelectorAll('[data-extract-panel]')].find(
                item => String(item.dataset.sourceId) === String(extract.destinationsourceid)
            );
            if (panel && panelStates.has(panel)) upsertCard(panel, extract);
        }
    });


    root.addEventListener('source-capture-before', function (event) {
        const card = event.target.closest('[data-source-card]');

        if (!card) {
            event.preventDefault();
            return;
        }

        const hasUnsavedEdits = [...dirtyForms].some(
            form => form?.isConnected && card.contains(form)
        );

        const hasPendingExtractAction = [
            ...card.querySelectorAll('[data-extract-panel]')
        ].some(
            panel => panelStates.get(panel)?.busy
        );

        if (hasUnsavedEdits || hasPendingExtractAction) {
            event.preventDefault();
        }
    });

    root.addEventListener('source-content-selected', function (event) {
        const card = event.target.closest('[data-source-card]');
        const panel = card?.querySelector('[data-extract-panel]');
        const state = panel ? panelStates.get(panel) : null;

        if (!panel || !state || state.busy) return;

        const form = panel.querySelector('[data-extract-create-form]');
        const originalText = form.elements.namedItem('extractedtext');
        const selectedText = String(event.detail?.text ?? '').trim();

        if (!selectedText) return;

        if (
            originalText.value.trim() !== ''
            && originalText.value !== selectedText
            && !confirm(
                'Replace the new extract’s Original Text with the selected passage?'
            )
        ) {
            return;
        }

        originalText.value = selectedText;

        originalText.dispatchEvent(
            new Event('input', { bubbles: true })
        );

        setCreateVisible(panel, true);

        form.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest',
        });

        originalText.focus();

        showFeedback(
            panel,
            'Selected page text copied. Choose a target field and review before saving.'
        );
    });

    const searchButton = document.getElementById(
        'suggest-from-web-button'
    );

    const sourceList = document.getElementById(
        'destination-sources-panel'
    );

    const searchFeedback = document.getElementById(
        'destination-search-feedback'
    );

    let searchBusy = false;

    function showSearchFeedback(message, isError = false) {
        if (!searchFeedback) return;

        searchFeedback.textContent = message;

        searchFeedback.className = isError
            ? 'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-800 whitespace-pre-line'
            : 'rounded-md border border-blue-200 bg-blue-50 px-3 py-2 text-xs text-blue-800 whitespace-pre-line';
    }

    if (searchButton && sourceList) {
        searchButton.addEventListener('click', async function () {
            if (searchBusy) return;

            if (root.querySelector('[data-capture-panel][data-busy="true"]')) {
                showSearchFeedback(
                    'Wait for webpage capture to finish before searching.',
                    true
                );
                return;
            }

            /*
            * Source-list replacement must not discard source/extract drafts.
            * Main Destination edits are unaffected because we do not reload
            * the page or modify Destination fields.
            */
            const hasUnsavedSourceWork = [...dirtyForms].some(
                form => form?.isConnected
            );

            if (hasUnsavedSourceWork) {
                showSearchFeedback(
                    'Save your source or extract changes before searching. The source list needs to refresh.',
                    true
                );
                return;
            }

            const hasPendingExtractRequest = [
                ...root.querySelectorAll('[data-extract-panel]')
            ].some(
                panel => panelStates.get(panel)?.busy
            );

            if (hasPendingExtractRequest) {
                showSearchFeedback(
                    'Wait for the current extract action to finish before searching.',
                    true
                );
                return;
            }

            searchBusy = true;

            const previousButtonText = searchButton.textContent;
            const rootWasInert = root.inert;

            searchButton.disabled = true;
            searchButton.textContent = 'Finding sources...';

            root.inert = true;
            root.setAttribute('aria-busy', 'true');

            showSearchFeedback('Searching for internet sources...');

            try {
                const data = await requestJson(
                    @js(route('destinations.suggest-from-web', $destination)),
                    'POST',
                    {}
                );

                if (typeof data.source_list_html !== 'string') {
                    throw new Error(
                        'The server did not return the refreshed source list.'
                    );
                }

                sourceList.innerHTML = data.source_list_html;

                initialiseExtractPanels(sourceList);

                for (const form of [...dirtyForms]) {
                    if (!form.isConnected) {
                        dirtyForms.delete(form);
                    }
                }

                let message = data.message || 'Search completed.';

                if (data.query) {
                    message += '\nSearch: ' + data.query;
                }

                showSearchFeedback(message);
            } catch (error) {
                showSearchFeedback(
                    error.message || 'Internet search could not be completed.',
                    true
                );
            } finally {
                root.inert = rootWasInert;
                root.removeAttribute('aria-busy');

                searchButton.disabled = false;
                searchButton.textContent = previousButtonText;

                searchBusy = false;
            }
        });
    }

    root.addEventListener('click', async function (event) {
    const target = event.target;

    if (!(target instanceof Element)) return;

    /*
     * Source create form.
     */
    if (target.closest('#toggle-source-create')) {
        setSourceCreateVisible(
            sourceCreateForm?.classList.contains('hidden')
        );
        return;
    }

    if (target.closest('#cancel-source-create')) {
        setSourceCreateVisible(false);
        return;
    }

    /*
     * Source edit forms.
     */
    const sourceToggle = target.closest('[data-source-toggle]');

    if (sourceToggle) {
        const panelId = sourceToggle.dataset.target;
        const sourceEditPanel = document.getElementById(panelId);

        if (!sourceEditPanel || !root.contains(sourceEditPanel)) {
            return;
        }

        const visible = sourceEditPanel.classList.contains('hidden');
        if (visible) {
            const card = sourceToggle.closest('[data-source-card]');

            const workspace = card?.querySelector(
                '[data-source-workspace]'
            );

            if (workspace) {
                workspace.open = true;
            }
        }

        sourceEditPanel.classList.toggle('hidden', !visible);

        root.querySelectorAll('[data-source-toggle]')
            .forEach(function (button) {
                if (button.dataset.target === panelId) {
                    button.setAttribute(
                        'aria-expanded',
                        String(visible)
                    );
                }
            });

        if (visible) {
            sourceEditPanel
                .querySelector('[name="sourcetitle"]')
                ?.focus();
        }

        return;
    }

    /*
     * Resolve extract button, panel, state and action before
     * handling any extract action.
     */
    const button = target.closest('[data-extract-action]');

    if (!button) return;

    const panel = button.closest('[data-extract-panel]');

    if (!panel) return;

    const state = panelStates.get(panel);

    if (!state || state.busy) return;

    const action = button.dataset.extractAction;

    /*
     * New extract form.
     */
    if (action === 'toggle-create') {
        const form = panel.querySelector(
            '[data-extract-create-form]'
        );

        if (!form) return;

        setCreateVisible(
            panel,
            form.classList.contains('hidden')
        );

        return;
    }

    if (action === 'cancel-create') {
        setCreateVisible(panel, false);
        return;
    }

    /*
     * Copy the source summary into the new extract.
     */
    if (action === 'use-summary') {
        const form = panel.querySelector(
            '[data-extract-create-form]'
        );

        if (!form) return;

        const originalText = form.elements.namedItem(
            'extractedtext'
        );

        if (!originalText) return;

        const summary = panel.dataset.importedSummary ?? '';

        if (summary.trim() === '') {
            showFeedback(
                panel,
                'This source has no imported summary to copy.',
                true
            );
            return;
        }

        if (
            originalText.value.trim() !== ''
            && originalText.value !== summary
            && !confirm(
                'Replace the current Original Text with the imported summary?'
            )
        ) {
            return;
        }

        originalText.value = summary;

        originalText.dispatchEvent(
            new Event('input', { bubbles: true })
        );

        originalText.focus();

        showFeedback(
            panel,
            'Imported summary copied into Original Text. Review it before saving.'
        );

        return;
    }

    /*
     * Existing extract actions.
     */
    const card = button.closest('[data-extract-card]');

    if (!card) return;

    const id = card.dataset.extractId;
    const extract = state.extracts.get(id);

    const form = card.querySelector(
        '[data-extract-edit-form]'
    );

    if (!extract || !form) return;

    if (action === 'toggle-edit') {
        setEditVisible(
            card,
            form.classList.contains('hidden')
        );
        return;
    }

    if (action === 'cancel-edit') {
        if (
            dirtyForms.has(form)
            && !confirm(
                'Discard unsaved changes to this extract?'
            )
        ) {
            return;
        }

        fillEditForm(form, extract);
        dirtyForms.delete(form);
        setEditVisible(card, false);

        return;
    }

    if (!['approve', 'reject', 'delete'].includes(action)) {
        return;
    }

    if (dirtyForms.has(form) && action !== 'delete') {
        showFeedback(
            panel,
            'Save or cancel the extract edits before changing its status.',
            true
        );
        return;
    }

    if (
        action === 'delete'
        && !confirm(
            'Delete this extract? This cannot be undone.'
        )
    ) {
        return;
    }

    await runPanelAction(panel, async function () {
        const data = await requestJson(
            extractUrl(panel, action, id),
            action === 'delete' ? 'DELETE' : 'PATCH'
        );

        if (action === 'delete') {
            removeCard(panel, id);
            showFeedback(panel, 'Extract deleted.');
            return;
        }

        upsertCard(panel, data.extract);

        showFeedback(
            panel,
            data.message || 'Extract updated.'
        );
    });
});

    root.addEventListener('submit', async function (event) {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) return;

        /*
        * Delete a source without navigating away.
        */
        if (form.matches('[data-source-delete-form]')) {
            event.preventDefault();

            const card = form.closest('[data-source-card]');

            if (!card || form.dataset.busy === 'true') return;

            const extractPanels = [
                ...card.querySelectorAll('[data-extract-panel]')
            ];

            const hasPendingRequest = extractPanels.some(
                panel => panelStates.get(panel)?.busy
            );

            if (hasPendingRequest) {
                showSearchFeedback(
                    'Wait for the current extract action to finish before deleting this source.',
                    true
                );
                return;
            }

            const hasUnsavedDrafts = [...dirtyForms].some(
                dirtyForm => dirtyForm?.isConnected
                    && card.contains(dirtyForm)
            );

            const confirmation = hasUnsavedDrafts
                ? 'Delete this internet source and its unapplied extracts? Unsaved edits within this source will also be discarded. Sources containing applied history cannot be deleted.'
                : 'Delete this internet source and its unapplied extracts? Sources containing applied history cannot be deleted.';

            if (!confirm(confirmation)) return;

            form.dataset.busy = 'true';

            const cardWasInert = card.inert;

            card.inert = true;
            card.setAttribute('aria-busy', 'true');

            try {
                await requestJson(
                    form.action,
                    'DELETE'
                );

                /*
                * Clear dirty state only for forms belonging to the
                * successfully deleted source.
                */
                for (const dirtyForm of [...dirtyForms]) {
                    if (card.contains(dirtyForm)) {
                        dirtyForms.delete(dirtyForm);
                    }
                }

                card.remove();

                const list = document.getElementById(
                    'destination-sources-panel'
                );

                if (
                    list
                    && !list.querySelector('[data-source-card]')
                ) {
                    const emptyMessage = document.createElement('p');

                    emptyMessage.className = 'text-xs text-gray-500';
                    emptyMessage.textContent =
                        'No internet sources recorded yet. Use “Find Internet Sources” or Add Source.';

                    list.replaceChildren(emptyMessage);
                }

                showSearchFeedback(
                    'Internet source deleted. Other unsaved edits have been preserved.'
                );
            } catch (error) {
                showSearchFeedback(
                    error.message || 'The internet source could not be deleted.',
                    true
                );
            } finally {
                if (card.isConnected) {
                    card.inert = cardWasInert;
                    card.removeAttribute('aria-busy');
                }

                delete form.dataset.busy;
            }

            return;
        }

        /*
        * Normal source create/edit submission.
        */
        if (form.matches('[data-source-form]')) {
            dirtyForms.delete(form);
            return;
        }

        /*
        * Extract create/edit submission.
        */
        const isCreate = form.matches('[data-extract-create-form]');
        const isEdit = form.matches('[data-extract-edit-form]');

        if (!isCreate && !isEdit) return;

        event.preventDefault();

        const panel = form.closest('[data-extract-panel]');
        const payload = formPayload(form);

        await runPanelAction(panel, async function () {
            const card = form.closest('[data-extract-card]');

            const url = isCreate
                ? panel.dataset.storeUrl
                : extractUrl(panel, 'update', card.dataset.extractId);

            const data = await requestJson(
                url,
                isCreate ? 'POST' : 'PUT',
                payload
            );

            dirtyForms.delete(form);
            upsertCard(panel, data.extract);

            if (isCreate) {
                form.reset();
                setCreateVisible(panel, false);
            }

            showFeedback(
                panel,
                data.message || 'Extract saved.'
            );
        });
    });
});
</script>