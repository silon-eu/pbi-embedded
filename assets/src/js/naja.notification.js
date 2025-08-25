import Naja from 'naja';
import * as bootstrap from 'bootstrap';
Naja.addEventListener('complete', function (event) {
    initNotifications();
});

Naja.addEventListener('error', function (event) {
    console.warn('AJAX error:');
    console.warn(event.detail.error.message);
    showNotification('An error occurred during the request. Please try it again later.', 'danger');
});

export function initNotifications() {
    const toastElList = document.querySelectorAll('.flash-toast');
    const toastList = [...toastElList].map(toastEl => new bootstrap.Toast(toastEl,{}).show());
}

function showNotification(message, type = 'info') {
    const toastElList = document.querySelector('.toast-container');
    if (!toastElList) {
        console.warn('Toast container not found!');
        return;
    }

    const toastEl = document.createElement('div');
    toastEl.className = `toast flash-toast align-items-center text-bg-${type} border-0`;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

    toastElList.appendChild(toastEl);
}