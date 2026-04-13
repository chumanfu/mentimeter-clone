const MAX_CHOICES = 12;
const MIN_CHOICES = 2;

function initPresentationForm() {
    const container = document.getElementById('choices-container');
    const template = document.getElementById('choice-row-template');
    const addBtn = document.getElementById('add-choice-btn');

    if (!container || !template || !addBtn) {
        return;
    }

    const countRows = () => container.querySelectorAll('[data-choice-row]').length;

    addBtn.addEventListener('click', () => {
        if (countRows() >= MAX_CHOICES) {
            return;
        }
        container.appendChild(template.content.cloneNode(true));
    });

    container.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-remove-choice]');
        if (!btn) {
            return;
        }
        if (countRows() <= MIN_CHOICES) {
            return;
        }
        btn.closest('[data-choice-row]')?.remove();
    });
}

document.addEventListener('DOMContentLoaded', initPresentationForm);
