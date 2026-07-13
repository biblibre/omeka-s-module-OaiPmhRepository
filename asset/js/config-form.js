'use strict';

/**
 * Show or hide the config-form fields that only make sense for a given choice,
 * so the form shows just the relevant options.
 */
(function () {
    var PREFIX = 'oaipmhrepository_';

    function radioValue(name) {
        var checked = document.querySelector('input[name="' + name + '"]:checked');
        return checked ? checked.value : null;
    }

    function toggle(id, show) {
        var element = document.getElementById(id);
        if (!element) {
            return;
        }
        var field = element.closest('.field');
        if (field) {
            field.style.display = show ? '' : 'none';
        }
    }

    function apply() {
        var global = radioValue(PREFIX + 'global_repository');
        toggle(PREFIX + 'list_item_sets', global === 'list_item_sets');
        toggle(PREFIX + 'sets_queries', global === 'queries');
        toggle(PREFIX + 'append_identifier_global', global !== null && global !== 'disabled');

        var bySite = radioValue(PREFIX + 'by_site_repository');
        toggle(PREFIX + 'append_identifier_site', bySite !== null && bySite !== 'disabled');

        var dcClass = radioValue(PREFIX + 'oai_dc_class_type');
        var dctermsClass = radioValue(PREFIX + 'oai_dcterms_class_type');
        toggle(PREFIX + 'oai_table_class_type', dcClass === 'table' || dctermsClass === 'table');

        // Master switch: the file options only apply when media is exposed.
        var exposeMedia = document.getElementById(PREFIX + 'expose_media');
        exposeMedia = exposeMedia ? exposeMedia.checked : false;
        var fileLink = radioValue(PREFIX + 'oai_dc_file_link');
        var manifest = radioValue(PREFIX + 'oai_dc_iiif_manifest');
        toggle(PREFIX + 'oai_dc_file_link', exposeMedia);
        toggle(PREFIX + 'oai_dc_iiif_manifest', exposeMedia);
        toggle(PREFIX + 'oai_dc_file_property', exposeMedia && fileLink !== null && fileLink !== 'none');
        toggle(PREFIX + 'oai_dc_iiif_manifest_property', exposeMedia && manifest !== null && manifest !== 'none');
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.addEventListener('change', function (event) {
            if (event.target && event.target.name && event.target.name.indexOf(PREFIX) === 0) {
                apply();
            }
        });
        apply();
    });
})();
