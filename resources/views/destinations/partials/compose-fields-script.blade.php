<script>
document.addEventListener('DOMContentLoaded', function () {
    const root = document.getElementById('destination-composition');
    if (!root) return;
    const researchTab = root.closest('[data-destination-panel]');
    const sourcesRoot = document.getElementById('destination-internet-sources');
    const form = root.querySelector('[data-compose-form]');
    const draft = root.querySelector('[data-compose-draft]');
    const current = root.querySelector('[data-compose-current]');
    const confirmBox = root.querySelector('[data-compose-confirm]');
    const list = root.querySelector('[data-compose-extracts]');
    const savedValues = JSON.parse(root.dataset.savedValues);
    const csrfToken = @js(csrf_token());
    let session = null;
    let busy = false;
    let dirty = false;

    function message(text, error = false) {
        const element = root.querySelector('[data-compose-feedback]');
        element.textContent = text;
        element.className = 'rounded-md border px-3 py-2 text-sm whitespace-pre-line ' +
            (error ? 'border-red-200 bg-red-50 text-red-800' : 'border-blue-200 bg-blue-50 text-blue-800');
    }
    function editor(column) {
        const element = document.querySelector(`[data-markdown-field="${column}"]`);
        if (!element || !window.Alpine || !element._x_dataStack) {
            throw new Error('The Destination Markdown editor is not initialised. Reload the page before composing.');
        }
        return { element, state: window.Alpine.$data(element) };
    }
    async function jsonRequest(url, method, payload = null) {
        const response = await fetch(url, {
            method, credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            ...(payload !== null ? { body: JSON.stringify(payload) } : {}),
        });
        const data = (response.headers.get('content-type') || '').includes('application/json')
            ? await response.json() : null;
        if (!response.ok) {
            throw new Error(data?.errors ? Object.values(data.errors).flat().join('\n')
                : data?.message || 'The composition request failed. Your draft is preserved.');
        }
        if (!data) throw new Error('Unexpected server response.');
        return data;
    }
    function selected() {
        return [...list.querySelectorAll('input[data-compose-select]:checked')]
            .map(input => session.extracts.find(item => String(item.id) === input.value));
    }
    function markDraftDirty() { dirty = true; confirmBox.checked = false; }
    function renderExtracts(extracts) {
        list.replaceChildren();
        if (extracts.length === 0) {
            const note = document.createElement('p');
            note.className = 'text-sm text-gray-500';
            note.textContent = 'No approved, unapplied extracts for this field. Approve extracts in Sources, then reopen this field.';
            list.appendChild(note);
        }
        extracts.forEach(function (extract) {
            const label = document.createElement('label');
            label.className = 'block rounded-md border border-gray-200 bg-gray-50 p-3 cursor-pointer';
            const heading = document.createElement('div');
            heading.className = 'flex items-start gap-2';
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox'; checkbox.value = String(extract.id);
            checkbox.dataset.composeSelect = ''; checkbox.className = 'mt-0.5 rounded border-gray-300';
            const title = document.createElement('span');
            title.className = 'text-sm font-medium text-gray-900';
            title.textContent = extract.source_title + (extract.confidence ? ` · Confidence ${extract.confidence}/5` : '');
            const text = document.createElement('div');
            text.className = 'mt-2 text-xs text-gray-600 whitespace-pre-wrap break-words';
            text.textContent = extract.text;
            heading.append(checkbox, title); label.append(heading, text); list.appendChild(label);
        });
    }
    async function openField(target) {
        if (busy) return;
        if (dirty && !confirm('Discard the current unsaved composition draft and open this field?')) return;
        busy = true;
        const buttons = [...root.querySelectorAll('button')];
        buttons.forEach(button => button.disabled = true);
        try {
            const url = new URL(root.dataset.dataUrl, window.location.href);
            url.searchParams.set('targetfield', target);
            const data = await jsonRequest(url, 'GET');
            const value = String(editor(data.column).state.content ?? '');
            if (String(data.saved_value) !== String(savedValues[data.column])) {
                throw new Error('The saved field changed since this page loaded. Preserve your unsaved work and reload before composing this field.');
            }
            session = { ...data, editor_value: value };
            current.value = value; draft.value = value;
            confirmBox.checked = false; dirty = false;
            renderExtracts(data.extracts);
            root.querySelector('[data-compose-heading]').textContent = 'Compose ' + data.label;
            root.querySelector('[data-compose-save]').textContent = 'Save Composed ' + data.label;
            root.querySelector('[data-compose-placeholder]').classList.add('hidden');
            form.classList.remove('hidden');
            message('Current editor text loaded. Select extracts, prepare the draft, then review it before saving.');
        } catch (error) { message(error.message, true); }
        finally { busy = false; buttons.forEach(button => button.disabled = false); }
    }
    researchTab.addEventListener('click', function (event) {
        if (!(event.target instanceof Element)) return;
        const sectionButton = event.target.closest('[data-research-section]');
        if (!sectionButton) return;
        const key = sectionButton.dataset.researchSection;
        researchTab.querySelectorAll('[data-research-panel]').forEach(panel => panel.hidden = panel.dataset.researchPanel !== key);
        researchTab.querySelectorAll('[data-research-section]').forEach(button => {
            const active = button.dataset.researchSection === key;
            button.setAttribute('aria-pressed', String(active));
            button.className = 'px-3 py-2 rounded text-sm ' + (active ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-800');
        });
    });
    root.addEventListener('click', function (event) {
        if (!(event.target instanceof Element) || busy) return;
        const field = event.target.closest('[data-compose-field]');
        if (field) { openField(field.dataset.composeField); return; }
        const build = event.target.closest('[data-compose-build]');
        if (build && session) {
            const parts = selected().map(item => item.text.trim()).filter(Boolean);
            if (parts.length === 0) { message('Select at least one extract before assembling a draft.', true); return; }
            if (dirty && !confirm('Rebuild the draft? This replaces any edits in Final composed text.')) return;
            if (build.dataset.composeBuild === 'keep' && session.editor_value.trim()) parts.unshift(session.editor_value.trim());
            draft.value = parts.join('\n\n'); markDraftDirty();
            message('Draft assembled. Remove repeated information and confirm which selected extracts actually contribute.');
            return;
        }
        if (event.target.closest('[data-compose-cancel]')) {
            if (dirty && !confirm('Discard this unsaved composition draft?')) return;
            session = null; dirty = false; form.classList.add('hidden');
            root.querySelector('[data-compose-placeholder]').classList.remove('hidden');
            message('Composition closed. No further changes were saved.');
        }
    });
    draft.addEventListener('input', markDraftDirty);
    list.addEventListener('change', markDraftDirty);
    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        if (busy || !session) return;
        let targetEditor;
        try {
            targetEditor = editor(session.column);
            if (String(targetEditor.state.content ?? '') !== session.editor_value) {
                throw new Error('The Destination editor changed after this draft was opened. Your draft is preserved; reconcile those edits before saving.');
            }
        } catch (error) { message(error.message, true); return; }
        const contributions = selected();
        const beforeSave = new CustomEvent('destination-composition-before-save', {
            cancelable: true, detail: { ids: contributions.map(item => item.id) },
        });
        if (sourcesRoot && !sourcesRoot.dispatchEvent(beforeSave)) {
            message('Save or cancel edits to contributing extracts and wait for pending source actions before composing.', true);
            return;
        }
        busy = true;
        window.destinationCompositionBusy = true;
        const editorWasInert = targetEditor.element.inert;
        const sourcesWereInert = sourcesRoot?.inert;
        targetEditor.element.inert = true;
        if (sourcesRoot) sourcesRoot.inert = true;
        const controls = [...root.querySelectorAll('button, textarea, input')];
        controls.forEach(control => control.disabled = true);
        const tokens = {};
        contributions.forEach(item => tokens[item.id] = item.token);
        try {
            const data = await jsonRequest(root.dataset.saveUrl, 'POST', {
                targetfield: session.targetfield,
                text: draft.value,
                expected_token: session.saved_token,
                extract_ids: contributions.map(item => item.id),
                extract_tokens: tokens,
                confirmed: confirmBox.checked,
            });
            savedValues[data.column] = data.text;
            window.dispatchEvent(new CustomEvent('destination-markdown-set', {
                detail: { field: data.column, value: data.text, html: data.rendered_html },
            }));
            window.dispatchEvent(new CustomEvent('destination-field-saved', {
                detail: { field: data.column, value: data.text },
            }));
            sourcesRoot?.dispatchEvent(new CustomEvent('destination-composition-saved', {
                detail: { extracts: data.applied_extracts },
            }));
            const appliedIds = new Set(contributions.map(item => Number(item.id)));
            session.extracts = session.extracts.filter(item => !appliedIds.has(Number(item.id)));
            session.saved_value = data.text; session.saved_token = data.saved_token; session.editor_value = data.text;
            draft.value = data.text; current.value = data.text;
            renderExtracts(session.extracts); confirmBox.checked = false; dirty = false;
            message(data.message);
        } catch (error) { message(error.message, true); }
        finally {
            controls.forEach(control => { if (control.isConnected) control.disabled = false; });
            targetEditor.element.inert = editorWasInert;
            if (sourcesRoot) sourcesRoot.inert = sourcesWereInert;
            busy = false; window.destinationCompositionBusy = false;
        }
    });
    const destinationForm = document.getElementById('destination-edit-form');
    destinationForm?.addEventListener('submit', function (event) {
        if (!window.destinationCompositionBusy) return;
        event.preventDefault(); event.stopImmediatePropagation();
        message('Wait for the composed field save to finish before saving the whole Destination.', true);
    }, true);
    window.addEventListener('beforeunload', function (event) {
        if (dirty || busy) { event.preventDefault(); event.returnValue = ''; }
    });
});
</script>
