// Import our custom CSS
import '../scss/styles.scss';
import "multi.js/dist/multi.min.css";

// Import all of Bootstrap's JS
import * as bootstrap from 'bootstrap'

// https://github.com/contributte/live-form-validation/tree/master/.docs
import * as LiveFormValidation from 'live-form-validation';

// https://doc.nette.org/cs/application/ajax#toc-naja
import Naja from 'naja';

import * as najaSystemModal from './naja.systemModal.js';
import * as najaConfirm from './naja.confirm.js';
import * as najaNotification from './naja.notification.js';
import * as najaTooltips from './naja.tooltips.js';
import * as netteFormsDependency from './netteForms.dependency.js';
import * as najaSetReportPage from './naja.setReportPage.js';
import * as najaResetReportPageFilters from './naja.resetReportPageFilters.js';

/* Import TinyMCE */
import tinymce from 'tinymce';

/* Default icons are required. After that, import custom icons if applicable */
import 'tinymce/icons/default/icons.min.js';

/* Required TinyMCE components */
import 'tinymce/themes/silver/theme.min.js';
import 'tinymce/models/dom/model.min.js';

/* Import a skin (can be a custom skin instead of the default) */
import 'tinymce/skins/ui/oxide/skin.js';

/* Import plugins */
import 'tinymce/plugins/advlist';
import 'tinymce/plugins/code';
import 'tinymce/plugins/emoticons';
import 'tinymce/plugins/emoticons/js/emojis';
import 'tinymce/plugins/link';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/table';

/* Import premium plugins */
/* NOTE: Download separately and add these to /src/plugins */
/* import './plugins/<plugincode>'; */

/* content UI CSS is required */
import 'tinymce/skins/ui/oxide/content.js';

/* The default content CSS can be changed or replaced with appropriate CSS for the editor content. */
import 'tinymce/skins/content/default/content.js';

document.addEventListener('DOMContentLoaded', function() {
    Naja.initialize();
    netteFormsDependency.initFormsDependecies();
    najaNotification.initNotifications();
    najaTooltips.initTooltips();

    let newsModalElement = document.getElementById('newsModal');
    if (newsModalElement) {
        let newsModal = new bootstrap.Modal(newsModalElement, {
            keyboard: false
        });
        newsModal.show();
    }

    tinymce.init({
        selector: 'textarea.tinymce',
        license_key: 'gpl',
        plugins: 'advlist code emoticons link lists table',
        toolbar: 'bold italic | bullist numlist | link emoticons',
        skin_url: 'default',
        content_css: 'default',
    });
});

/* Initialize TinyMCE */
/*export function render () {

};*/