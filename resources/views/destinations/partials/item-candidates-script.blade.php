<script>
document.addEventListener('DOMContentLoaded', function () {
    const root = document.getElementById('destination-item-candidates');
    if (!root) return;
    const sourceRoot = document.getElementById('destination-internet-sources');
    const researchTab = root.closest('[data-destination-panel]');
    const createForm = root.querySelector('[data-candidate-create-form]');
    const list = root.querySelector('[data-candidate-list]');
    const dirtyForms = new Set();
    const candidates = new Map();
    let options = { sources: [], types: [], items: [] };
    let loaded = false;
    let busy = false;
    const csrfToken = @js(csrf_token());

    function feedback(text, error = false) {
        const element = root.querySelector('[data-candidate-feedback]');
        element.textContent = text;
        element.className = 'rounded-md border px-3 py-2 text-sm whitespace-pre-line ' +
            (error ? 'border-red-200 bg-red-50 text-red-800' : 'border-blue-200 bg-blue-50 text-blue-800');
    }
    function option(value, label) { const node = document.createElement('option'); node.value = value; node.textContent = label; return node; }
    function types(select, multiple = false) {
        select.replaceChildren();
        if (!multiple) select.appendChild(option('', 'Not specified'));
        options.types.forEach(type => select.appendChild(option(multiple ? type.id : type.slug, type.label)));
    }
    function fill(form, data = {}) {
        types(form.elements.namedItem('itemtype'));
        if (data.itemtype && !options.types.some(type => type.slug === data.itemtype)) {
            form.elements.namedItem('itemtype').appendChild(option(data.itemtype, data.itemtype + ' (inactive type)'));
        }
        for (const field of form.elements) {
            if (!field.name || field.name === 'destinationsourceid') continue;
            if (field.type === 'checkbox') field.checked = !!data[field.name];
            else field.value = data[field.name] ?? (field.name === 'sortorder' ? 0 : '');
        }
    }
    function payload(form) {
        const data = {};
        for (const [key, value] of new FormData(form).entries()) data[key] = value === '' ? null : value;
        data.bookingrequired = form.elements.namedItem('bookingrequired').checked;
        return data;
    }
    function url(action, id) {
        const keys = { update: 'updateUrl', approve: 'approveUrl', reject: 'rejectUrl', delete: 'deleteUrl', 'create-item': 'createUrl', 'link-item': 'linkUrl' };
        const result = new URL(root.dataset[keys[action]], window.location.href);
        const number = Number(id);
        if (!Number.isSafeInteger(number) || number < 1) throw new Error('Invalid candidate ID.');
        result.pathname = result.pathname.replace(/\/item-candidates\/0(?=\/|$)/, '/item-candidates/' + number);
        return result.toString();
    }
    async function request(url, method = 'GET', data = null) {
        const response = await fetch(url, {
            method, credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            ...(data !== null ? { body: JSON.stringify(data) } : {}),
        });
        if (response.status === 204) return null;
        const result = (response.headers.get('content-type') || '').includes('application/json') ? await response.json() : null;
        if (!response.ok) throw new Error(result?.errors ? Object.values(result.errors).flat().join('\n') : result?.message || 'The request failed. Your draft has been preserved.');
        if (!result) throw new Error('Unexpected server response.');
        return result;
    }
    async function run(callback) {
        if (busy) return;
        busy = true;
        const previous = root.inert;
        root.inert = true; root.setAttribute('aria-busy', 'true');
        try { await callback(); } catch (error) { feedback(error.message, true); }
        finally { root.inert = previous; root.removeAttribute('aria-busy'); busy = false; }
    }
    function linkOptions(select) {
        const value = select.value;
        select.replaceChildren(option('', 'Select an existing item'));
        options.items.forEach(item => select.appendChild(option(item.id, item.itemname)));
        select.value = value;
    }
    function render(candidate) {
        const card = root.querySelector('[data-candidate-template]').content.firstElementChild.cloneNode(true);
        card.dataset.candidateId = String(candidate.id);
        const resolved = ['created', 'linked'].includes(candidate.candidatestatus);
        card.querySelector('[data-candidate-name]').textContent = candidate.itemname;
        card.querySelector('[data-candidate-status]').textContent = candidate.status_label;
        const type = options.types.find(type => type.slug === candidate.itemtype);
        card.querySelector('[data-candidate-source]').textContent = candidate.source_title + ' · ' + (type?.label || candidate.itemtype || 'Type not specified');
        card.querySelector('[data-candidate-description]').textContent = candidate.applicable_description || '';
        card.querySelector('[data-candidate-controls]').classList.toggle('hidden', resolved);
        card.querySelector('[data-candidate-resolve-controls]').classList.toggle('hidden', candidate.candidatestatus !== 'approved');
        card.querySelector('[data-candidate-action="approve"]').classList.toggle('hidden', candidate.candidatestatus === 'approved');
        card.querySelector('[data-candidate-action="reject"]').classList.toggle('hidden', candidate.candidatestatus === 'rejected');
        const itemLink = card.querySelector('[data-candidate-item-link]');
        if (candidate.item_edit_url) {
            itemLink.href = candidate.item_edit_url;
            itemLink.textContent = 'Open Destination Item: ' + (candidate.item_name || candidate.itemname);
            itemLink.classList.remove('hidden');
        }
        const note = card.querySelector('[data-candidate-resolved-note]');
        note.classList.toggle('hidden', !resolved);
        note.textContent = resolved ? (candidate.appliedon_display ? 'Resolved ' + candidate.appliedon_display + '. Read-only.' : 'Resolved. Read-only.') : '';
        fill(card.querySelector('[data-candidate-edit-form]'), candidate);
        const typeSelect = card.querySelector('[data-candidate-create-types]');
        types(typeSelect, true);
        [...typeSelect.options].forEach(item => item.selected = String(item.value) === String(type?.id));
        linkOptions(card.querySelector('[data-candidate-link-item]'));
        return card;
    }
    function upsert(candidate) {
        const old = list.querySelector(`[data-candidate-id="${Number(candidate.id)}"]`);
        const wasOpen = old?.open || false;
        if (old) dirtyForms.delete(old.querySelector('[data-candidate-edit-form]'));
        const card = render(candidate); card.open = wasOpen;
        candidates.set(String(candidate.id), candidate);
        if (old) old.replaceWith(card); else list.appendChild(card);
        [...candidates.values()].sort((a,b) => Number(a.sortorder || 0) - Number(b.sortorder || 0) || Number(a.id) - Number(b.id))
            .forEach(item => list.appendChild(list.querySelector(`[data-candidate-id="${Number(item.id)}"]`)));
        root.querySelector('[data-candidate-empty]').classList.toggle('hidden', candidates.size > 0);
    }
    async function load() {
        const data = await request(root.dataset.indexUrl);
        options = { sources: data.sources, types: data.types, items: data.items };
        const sourceSelect = createForm.elements.namedItem('destinationsourceid');
        sourceSelect.replaceChildren(option('', 'Select a source'));
        options.sources.filter(source => !['rejected', 'archived'].includes(source.status)).forEach(source => sourceSelect.appendChild(option(source.id, source.title)));
        fill(createForm);
        candidates.clear(); list.replaceChildren();
        data.candidates.forEach(upsert);
        const empty = root.querySelector('[data-candidate-empty]');
        empty.textContent = 'No item candidates yet. Select webpage text or use Add Candidate.';
        empty.classList.toggle('hidden', candidates.size > 0);
        loaded = true;
    }
    function refreshItems(data) {
        if (!data.items_html) return;
        const panel = document.getElementById('destination-panel-destination-items');
        if (panel) panel.innerHTML = data.items_html;
        const badge = document.querySelector('[data-tab-count="destination-items"]');
        if (badge) badge.textContent = data.item_count;
        options.items = data.items;
        root.querySelectorAll('[data-candidate-link-item]').forEach(linkOptions);
    }
    root.addEventListener('input', function (event) { const form = event.target.closest('form'); if (form) dirtyForms.add(form); });
    root.addEventListener('change', function (event) { const form = event.target.closest('form'); if (form) dirtyForms.add(form); });
    window.addEventListener('beforeunload', function (event) {
        if (busy || [...dirtyForms].some(form => form.isConnected)) { event.preventDefault(); event.returnValue = ''; }
    });
    root.addEventListener('click', async function (event) {
        if (!(event.target instanceof Element) || busy) return;
        const button = event.target.closest('[data-candidate-action]');
        if (!button) return;
        const action = button.dataset.candidateAction;
        if (action === 'refresh') {
            if (dirtyForms.size > 0 && !confirm('Discard unsaved candidate drafts and refresh?')) return;
            await run(async () => { await load(); dirtyForms.clear(); createForm.classList.add('hidden'); feedback('Candidates refreshed.'); });
            return;
        }
        if (!loaded) { feedback('Candidates have not loaded. Click Refresh and check the message.', true); return; }
        if (action === 'toggle-create') { createForm.classList.toggle('hidden'); return; }
        if (action === 'cancel-create') {
            if (dirtyForms.has(createForm) && !confirm('Discard this unsaved candidate?')) return;
            fill(createForm); dirtyForms.delete(createForm); createForm.classList.add('hidden'); return;
        }
        const card = button.closest('[data-candidate-card]');
        if (!card) return;
        const candidate = candidates.get(card.dataset.candidateId);
        const form = card.querySelector('[data-candidate-edit-form]');
        if (action === 'edit') { form.classList.toggle('hidden'); return; }
        if (action === 'cancel-edit') {
            if (dirtyForms.has(form) && !confirm('Discard unsaved candidate changes?')) return;
            fill(form, candidate); dirtyForms.delete(form); form.classList.add('hidden'); return;
        }
        if (dirtyForms.has(form) && action !== 'delete') { feedback('Save or cancel candidate edits before this action.', true); return; }
        if (action === 'delete' && !confirm('Delete this unresolved candidate?')) return;
        let body = null;
        if (action === 'create-item') {
            if (!confirm('Create a new Destination Item from this approved candidate?')) return;
            body = {
                itemtype_ids: [...card.querySelector('[data-candidate-create-types]').selectedOptions].map(item => Number(item.value)),
                expected_updatedat: candidate.updatedat, confirmed: true,
            };
        }
        if (action === 'link-item') {
            const id = card.querySelector('[data-candidate-link-item]').value;
            if (!id) { feedback('Choose an existing Destination Item first.', true); return; }
            if (!confirm('Link this candidate to the selected item without changing that item?')) return;
            body = { destinationitemid: Number(id), expected_updatedat: candidate.updatedat, confirmed: true };
        }
        await run(async () => {
            const data = await request(url(action, candidate.id), action === 'delete' ? 'DELETE' : ['approve', 'reject'].includes(action) ? 'PATCH' : 'POST', body);
            if (action === 'delete') {
                dirtyForms.delete(form); candidates.delete(String(candidate.id)); card.remove();
                root.querySelector('[data-candidate-empty]').classList.toggle('hidden', candidates.size > 0);
                feedback('Candidate deleted.');
            } else { upsert(data.candidate); refreshItems(data); feedback(data.message); }
        });
    });
    root.addEventListener('submit', async function (event) {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        event.preventDefault();
        if (busy || !loaded) return;
        const creating = form.matches('[data-candidate-create-form]');
        const id = form.closest('[data-candidate-card]')?.dataset.candidateId;
        await run(async () => {
            const data = await request(creating ? root.dataset.storeUrl : url('update', id), creating ? 'POST' : 'PUT', payload(form));
            dirtyForms.delete(form); upsert(data.candidate);
            if (creating) { fill(form); form.classList.add('hidden'); }
            feedback(data.message);
        });
    });
    sourceRoot?.addEventListener('source-item-candidate-selected', async function (event) {
        if (busy) { feedback('Wait for the current candidate operation to finish.', true); return; }
        const text = String(event.detail?.text || '').trim();
        if (!text) return;
        if (dirtyForms.has(createForm) && !confirm('Replace the current unsaved new-candidate draft with this passage?')) return;
        if (!loaded) {
            await run(load);
            if (!loaded) return;
        }
        const sourceSelect = createForm.elements.namedItem('destinationsourceid');
        const sourceId = String(event.detail.sourceId);
        if (![...sourceSelect.options].some(item => item.value === sourceId)) {
            sourceSelect.appendChild(option(sourceId, event.detail.sourceTitle || 'Source ' + sourceId));
        }
        fill(createForm);
        sourceSelect.value = sourceId;
        createForm.elements.namedItem('shortdescription').value = text;
        createForm.elements.namedItem('website').value = event.detail.sourceUrl || '';
        dirtyForms.add(createForm); createForm.classList.remove('hidden');
        researchTab.querySelector('[data-research-section="candidates"]')?.click();
        createForm.elements.namedItem('itemname').focus();
        feedback('Selected text copied. Enter an item name and suggested type, then save for review.');
    });
    run(load);
});
</script>
