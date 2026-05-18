@props([
    'formSelector' => 'form',
    'tabLinkSelector' => '[data-tab-link]',
    'message' => 'You have unsaved changes. Leave this page without saving?',
])

<script>
document.addEventListener('DOMContentLoaded', function () {
    const forms = Array.from(document.querySelectorAll(@json($formSelector)));

    if (!forms.length) {
        return;
    }

    let isDirty = false;
    let isSubmitting = false;

    const trackedTypes = new Set([
        'text', 'textarea', 'number', 'email', 'url', 'tel', 'search',
        'date', 'datetime-local', 'month', 'week', 'time',
        'select-one', 'select-multiple', 'checkbox', 'radio'
    ]);

    const markDirty = function () {
        if (!isSubmitting) {
            isDirty = true;
        }
    };

    const clearDirty = function () {
        isDirty = false;
    };

    forms.forEach(function (form) {
        const fields = form.querySelectorAll('input, textarea, select');

        fields.forEach(function (field) {
            if (field.disabled) {
                return;
            }

            const fieldType = (field.type || field.tagName || '').toLowerCase();

            if (!trackedTypes.has(fieldType) && field.tagName.toLowerCase() !== 'textarea' && field.tagName.toLowerCase() !== 'select') {
                return;
            }

            field.addEventListener('input', markDirty);
            field.addEventListener('change', markDirty);
        });

        form.addEventListener('submit', function () {
            isSubmitting = true;
            clearDirty();
        });
    });

    window.addEventListener('beforeunload', function (event) {
        if (!isDirty || isSubmitting) {
            return;
        }

        event.preventDefault();
        event.returnValue = @json($message);
        return @json($message);
    });

    document.querySelectorAll(@json($tabLinkSelector)).forEach(function (link) {
        link.addEventListener('click', function (event) {
            if (!isDirty || isSubmitting) {
                return;
            }

            if (!window.confirm(@json($message))) {
                event.preventDefault();
            } else {
                clearDirty();
            }
        });
    });

    document.querySelectorAll('a[href]').forEach(function (link) {
        if (link.matches(@json($tabLinkSelector))) {
            return;
        }

        link.addEventListener('click', function (event) {
            if (!isDirty || isSubmitting) {
                return;
            }

            const href = link.getAttribute('href');

            if (!href || href.startsWith('#') || href.startsWith('javascript:')) {
                return;
            }

            if (!window.confirm(@json($message))) {
                event.preventDefault();
            } else {
                clearDirty();
            }
        });
    });
});
</script>