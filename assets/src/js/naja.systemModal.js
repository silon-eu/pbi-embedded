import Naja from 'naja';
import * as bootstrap from 'bootstrap';
document.addEventListener('DOMContentLoaded', function() {

    document.querySelectorAll('[data-toggle="systemModal"]').forEach(function(element) {
        element.addEventListener('click', function(e) {
            e.preventDefault();
        });
    });

    /*Naja.addEventListener('before', function (event) {
        changeTitle('');
        changeBody('<p>Loading content ...</p>');
    });*/

    Naja.addEventListener('success', function (event) {
        if (event.detail.payload.modalTitle !== undefined) {
            changeTitle(event.detail.payload.modalTitle);
        }
        if (event.detail.payload.modalBody !== undefined) {
            changeBody(event.detail.payload.modalBody);
        }
    });

    Naja.addEventListener('error', function (event) {
        changeTitle('');
        changeBody('<div class="alert alert-danger">Can\'t load the content - Internal server error</div>');
        //modalInstance.handleUpdate();
    });

    Naja.addEventListener('complete', function (event) {
        const modalElement = document.getElementById('systemModal');
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);

        if (event.detail.payload.closeModal !== undefined && event.detail.payload.closeModal) {
            modalInstance.hide();
        } else if (event.detail.payload.modalTitle !== undefined || event.detail.payload.modalBody !== undefined || event.detail.payload.openModal !== undefined) {
            modalInstance.show();
        }
    });

    function changeTitle(title)
    {
        document.querySelector('#systemModal .modal-title').innerHTML = title;
    }

    function changeBody(body)
    {
        document.querySelector('#systemModal .modal-body').innerHTML = body;
    }

});