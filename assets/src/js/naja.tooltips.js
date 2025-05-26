import Naja from 'naja';
import * as bootstrap from 'bootstrap';
Naja.addEventListener('complete', function (event) {
    initTooltips();
});

export function initTooltips() {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
}