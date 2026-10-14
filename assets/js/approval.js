document.querySelectorAll('[data-open]').forEach(button => {
    button.addEventListener('click', () => document.getElementById(button.dataset.open)?.showModal());
});
document.querySelectorAll('[data-close]').forEach(button => {
    button.addEventListener('click', () => button.closest('dialog')?.close());
});

// Server-side locks/CSRF still enforce correctness; avoid accidental double clicks.
const confirmationForms = document.querySelectorAll('dialog form');
const pendingButtons = new Set();
confirmationForms.forEach(form => {
    form.addEventListener('submit', event => {
        if (event.defaultPrevented) return;
        if (form.dataset.submitting === 'true') {
            event.preventDefault();
            return;
        }
        form.dataset.submitting = 'true';
        form.querySelectorAll('button[type="submit"]').forEach(button => {
            if (!button.disabled) {
                pendingButtons.add(button);
                button.disabled = true;
            }
        });
    });
});
window.addEventListener('pageshow', () => {
    confirmationForms.forEach(form => {
        delete form.dataset.submitting;
    });
    pendingButtons.forEach(button => { button.disabled = false; });
    pendingButtons.clear();
});
