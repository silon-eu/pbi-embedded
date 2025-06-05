import Naja from 'naja';
import * as bootstrap from 'bootstrap';
Naja.addEventListener('complete', function (event) {
    initTooltips();
});

export function initTooltips() {
    // select only tooltips that are not initalized
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]:not([data-tooltip-initialized])');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    // mark tooltips as initialized
    tooltipTriggerList.forEach(el => {
        el.setAttribute('data-tooltip-initialized', 'true');
    });
}