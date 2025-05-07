import Naja from 'naja';
document.addEventListener('DOMContentLoaded', function() {

    Naja.uiHandler.addEventListener('interaction', (event) => {
        const {element} = event.detail;
        const question = element.dataset.confirm;
        if (question && ! window.confirm(question)) {
            event.preventDefault();
        }
    });

});