import Naja from 'naja';
import * as bootstrap from 'bootstrap';
import * as netteFormsDependency from './netteForms.dependency.js';
import multi from "multi.js/dist/multi-es6.min";
import {EditorView, basicSetup} from "codemirror";
import {json} from "@codemirror/lang-json";

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
        if (event.detail.payload) {
            if ('modalTitle' in event.detail.payload) {
                changeTitle(event.detail.payload.modalTitle);
            }
            if ('modalBody' in event.detail.payload) {
                changeBody(event.detail.payload.modalBody);
            }
        }
    });

    Naja.addEventListener('error', function (event) {
        changeTitle('');
        changeBody('<div class="alert alert-danger">Can\'t load the content - Internal server error</div>');
        console.error('Error loading modal content:', event.detail);
        //modalInstance.handleUpdate();
    });

    Naja.addEventListener('complete', function (event) {
        const modalElement = document.getElementById('systemModal');
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);

        if (event.detail.payload && 'closeModal' in event.detail.payload && event.detail.payload.closeModal) {
            modalInstance.hide();
        } else if (event.detail.payload && ('modalTitle' in event.detail.payload || 'modalBody' in event.detail.payload || ('openModal' in event.detail.payload && event.detail.payload.openModal))) {
            netteFormsDependency.initFormsDependecies();
            document.querySelectorAll('.dual-listbox').forEach(function(element) {
                multi(element, {
                    enable_search: true,
                    search_placeholder: 'Search...',
                    non_selected_header: 'Available items',
                    selected_header: 'Selected items',
                    hide_empty_groups: true
                });
            });

            document.querySelectorAll('.codemirror-json').forEach(function(textarea) {
                let view = new EditorView({doc: textarea.value,extensions:  [basicSetup, json()]})
                textarea.parentNode.insertBefore(view.dom, textarea)
                textarea.style.display = "none"
                if (textarea.form) textarea.form.addEventListener("submit", () => {
                    textarea.value = view.state.doc.toString()
                })
            });

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