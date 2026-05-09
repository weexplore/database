<script>
    (() => {
        const form = document.getElementById(@json($formId));
        if (!form) return;

        const filterForm = document.getElementById(@json($filterFormId ?? ''));
        const deleteButtons = document.querySelectorAll(@json($deleteButtonSelector ?? '.js-delete-record'));
        const deleteForm = document.getElementById(@json($deleteFormId ?? ''));
        const dirtyMessage = @json($dirtyMessage ?? 'You have unsaved changes. Continue and lose those changes?');
        const deleteDirtyMessage = @json($deleteDirtyMessage ?? 'You have unsaved changes. Delete anyway and lose those changes?');
        const deleteConfirmPrefix = @json($deleteConfirmPrefix ?? 'Delete');
        const deleteConfirmSuffix = @json($deleteConfirmSuffix ?? 'This cannot be undone.');

        let isDirty = false;
        let isSubmitting = false;

        form.querySelectorAll('input, select, textarea').forEach(el => {
            el.addEventListener('change', () => {
                isDirty = true;
            });

            el.addEventListener('input', () => {
                isDirty = true;
            });
        });

        form.addEventListener('submit', () => {
            isSubmitting = true;
            isDirty = false;
        });

        if (filterForm) {
            filterForm.addEventListener('submit', (e) => {
                if (isDirty && !confirm(dirtyMessage)) {
                    e.preventDefault();
                }
            });
        }

        window.addEventListener('beforeunload', (e) => {
            if (!isDirty || isSubmitting) return;
            e.preventDefault();
            e.returnValue = '';
        });

        if (deleteForm && deleteButtons.length) {
            deleteButtons.forEach(button => {
                button.addEventListener('click', function () {
                    if (isDirty && !confirm(deleteDirtyMessage)) {
                        return;
                    }

                    const recordId = this.dataset.id;
                    const recordName = this.dataset.name || 'this record';
                    const action = this.dataset.action;

                    if (!confirm(`${deleteConfirmPrefix} ${recordName}? ${deleteConfirmSuffix}`)) {
                        return;
                    }

                    deleteForm.action = action;
                    deleteForm.submit();
                });
            });
        }
    })();
</script>