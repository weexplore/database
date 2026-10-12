<script>
document.addEventListener('DOMContentLoaded', function () {
    const workspace = document.getElementById('destination-tab-workspace');
    if (!workspace) return;
    const tabs = [...workspace.querySelectorAll('[data-destination-tab]')];
    const panels = [...workspace.querySelectorAll('[data-destination-panel]')];
    const keys = tabs.map(tab => tab.dataset.destinationTab);
    const label = document.getElementById('destination-active-tab-label');

    function setTab(key, updateUrl = true, focus = false) {
        if (!keys.includes(key)) key = 'destination';
        workspace.dataset.activeTab = key;
        tabs.forEach(function (tab) {
            const selected = tab.dataset.destinationTab === key;
            tab.setAttribute('aria-selected', String(selected));
            tab.tabIndex = selected ? 0 : -1;
            tab.className = 'inline-flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium '
                + (selected ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200');
            const count = tab.querySelector('[data-tab-count]');
            if (count) count.className = 'inline-flex min-w-5 items-center justify-center rounded-full px-1.5 py-0.5 text-[11px] font-semibold '
                + (selected ? 'bg-white/20 text-white' : 'bg-white text-gray-700 ring-1 ring-inset ring-gray-300');
            if (selected) {
                label.textContent = tab.dataset.tabLabel;
                if (focus) tab.focus();
            }
        });
        panels.forEach(panel => panel.hidden = panel.dataset.destinationPanel !== key);
        workspace.querySelectorAll('[data-active-tab-input]').forEach(input => input.value = key);
        if (updateUrl) {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', key);
            history.replaceState(history.state, '', url);
        }
        requestAnimationFrame(function () {
            const panel = panels.find(item => item.dataset.destinationPanel === key);
            if (!panel) return;
            panel.querySelectorAll('.markdown-content').forEach(element => window.renderMarkdownMath?.(element));
            panel.querySelectorAll('[x-data]').forEach(function (element) {
                if (!window.Alpine || !element._x_dataStack) return;
                const state = window.Alpine.$data(element);
                if (typeof state.measureOverflow === 'function') {
                    state.measureOverflow(element.querySelector('[x-ref="renderedContent"]'));
                }
            });
        });
    }
    workspace.addEventListener('click', function (event) {
        if (!(event.target instanceof Element)) return;
        const link = event.target.closest('[data-destination-tab], [data-destination-tab-shortcut]');
        if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        setTab(link.dataset.destinationTab || link.dataset.destinationTabShortcut);
    });
    tabs.forEach(function (tab, index) {
        tab.addEventListener('keydown', function (event) {
            let next = index;
            if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
            else if (event.key === 'ArrowLeft') next = (index - 1 + tabs.length) % tabs.length;
            else if (event.key === 'Home') next = 0;
            else if (event.key === 'End') next = tabs.length - 1;
            else return;
            event.preventDefault();
            setTab(tabs[next].dataset.destinationTab, true, true);
        });
    });
    workspace.addEventListener('invalid', function (event) {
        const panel = event.target.closest('[data-destination-panel]');
        if (panel) setTab(panel.dataset.destinationPanel);
    }, true);
    window.addEventListener('popstate', function () {
        setTab(new URL(window.location.href).searchParams.get('tab') || 'destination', false);
    });
    setTab(workspace.dataset.activeTab);

    const form = document.getElementById('destination-edit-form');
    let isSubmitting = false;
    let savedBaseline = {};
    function destinationValues() {
        if (!form) return {};
        const values = {};
        for (const [key, value] of new FormData(form).entries()) {
            if (!['_token', '_method', 'return_to', 'tab'].includes(key)) values[key] = String(value);
        }
        form.querySelectorAll('[data-markdown-field]').forEach(function (element) {
            if (window.Alpine && element._x_dataStack) {
                values[element.dataset.markdownField] = String(window.Alpine.$data(element).content ?? '');
            }
        });
        return values;
    }
    savedBaseline = destinationValues();
    window.addEventListener('destination-field-saved', function (event) {
        if (Object.prototype.hasOwnProperty.call(savedBaseline, event.detail.field)) {
            savedBaseline[event.detail.field] = String(event.detail.value ?? '');
        }
    });
    if (form) {
        form.addEventListener('submit', function () {
            if (!window.destinationCompositionBusy) isSubmitting = true;
        });
    }
    window.addEventListener('beforeunload', function (event) {
        const values = destinationValues();
        const dirty = Object.keys(savedBaseline).some(key => values[key] !== savedBaseline[key]);
        if (dirty && !isSubmitting) {
            event.preventDefault(); event.returnValue = '';
        }
    });
    const sourceList = document.getElementById('destination-sources-panel');
    const sourceCount = workspace.querySelector('[data-tab-count="internet-research"]');
    if (sourceList && sourceCount) {
        const refreshCount = () => sourceCount.textContent = sourceList.querySelectorAll('[data-source-card]').length;
        new MutationObserver(refreshCount).observe(sourceList, { childList: true });
        refreshCount();
    }
});
</script>
