// Import our custom CSS
import '../scss/styles.scss'

// Import all of Bootstrap's JS
import * as bootstrap from 'bootstrap'

// https://github.com/contributte/live-form-validation/tree/master/.docs
import * as LiveFormValidation from 'live-form-validation';

// https://doc.nette.org/cs/application/ajax#toc-naja
import Naja from 'naja';

import * as najaSystemModal from './naja.systemModal.js';
import * as najaConfirm from './naja.confirm.js';
import * as najaNotification from './naja.notification.js';
import * as netteFormsDependency from './netteForms.dependency.js';

document.addEventListener('DOMContentLoaded', function() {
    Naja.initialize();
    netteFormsDependency.initFormsDependecies();
    najaNotification.initNotifications();

    // initialize Bootstrap tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
});
