import Naja from 'naja';
document.addEventListener('DOMContentLoaded', function() {

    Naja.uiHandler.addEventListener('interaction', (event) => {
        //if (event.detail.element.hasAttribute('data-spinner-target')) {
            event.detail.options.spinner = event.detail.element;
        //}
    });

    Naja.addEventListener('before', (event) => {
        console.log(event);
        event.detail.options.spinner.appendChild('<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>');
    });

    Naja.addEventListener('complete', (event) => {
        console.log(event);
        event.detail.options.spinner.removeChild(event.detail.options.spinner.querySelector('.spinner-border'));
    });

});