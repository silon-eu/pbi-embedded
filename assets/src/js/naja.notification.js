import Naja from 'naja';
import * as bootstrap from 'bootstrap';
Naja.addEventListener('complete', function (event) {
    initNotifications();
});

export function initNotifications() {
    const toastElList = document.querySelectorAll('.flash-toast');
    const toastList = [...toastElList].map(toastEl => new bootstrap.Toast(toastEl,{}).show());
}