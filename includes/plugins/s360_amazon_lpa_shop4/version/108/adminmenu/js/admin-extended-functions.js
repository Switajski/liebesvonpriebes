/* 
 * Solution 360 GmbH
 */
jQuery(document).ready(function () {
    /* register functions */
    jQuery('#update-frontend-links-button').click(updateFrontendLinks);
    jQuery('#db-export-button').click(exportTables);
    jQuery('#db-import-button').click(importTables);
    jQuery('#db-migrate-button').click(migrateTables);
});

/*
 * Sets the frontendlinks for the plugin correctly.
 */
function updateFrontendLinks() {

    var $feedback = jQuery('#extended-functions-feedback');
    $feedback.hide();
    $feedback.html('');

    /*
     * The user entered potentially valid data. Start the check.
     */
    $feedback.removeClass('success');
    $feedback.removeClass('failure');
    $feedback.html('<br />Bitte warten...');
    $feedback.show();
    jQuery('body').css('cursor', 'wait');

    var ajaxURL = window.s360_lpa_admin_url + 'php/update_frontend_links.php';

    var request = jQuery.ajax({
        url: ajaxURL,
        type: "post",
        dataType: "json",
        data: {}
    });
    request.done(function (data) {
        if (data.status === 'success') {
            if (!$feedback.hasClass('success')) {
                $feedback.addClass('success');
            }
            var feedback = '';
            for (var index = 0; index < data.messages.length; ++index) {
                feedback = feedback + data.messages[index] + '<br />';
            }
            feedback = feedback + 'Frontendlinks erfolgreich angepasst.';
            $feedback.html(feedback);
        } else {
            if (!$feedback.hasClass('failure')) {
                $feedback.addClass('failure');
            }
            var feedback = '';
            if (typeof data.messages === "undefined") {
                feedback = 'Es ist ein allgemeiner Fehler aufgetreten. Prüfen Sie das Systemlog.';
            } else {
                for (var index = 0; index < data.messages.length; ++index) {
                    feedback = feedback + data.messages[index] + '<br />';
                }
            }
            $feedback.html(feedback);
        }
    });
    request.fail(function (jqXHR, textStatus, errorThrown) {
        console.log('Failed: ' + jqXHR + "," + textStatus + "," + errorThrown);
        if (!$feedback.hasClass('failure')) {
            $feedback.addClass('failure');
        }
        $feedback.html('Fehler: Ein technischer Fehler ist aufgetreten. Bitte pr&uuml;fen Sie das Browser-Log (F12) und ggf. das Server-Log.');
    });
    request.always(function () {
        jQuery('body').css({'cursor': 'default'});
    });
}

/*
 * AJAX Function, die die Plugintabellen exportiert.
 */
function exportTables() {

    var $feedback = jQuery('#extended-functions-feedback');
    $feedback.hide();
    $feedback.html('');

    /*
     * The user entered potentially valid data. Start the check.
     */
    $feedback.removeClass('success');
    $feedback.removeClass('failure');
    $feedback.html('<br />Bitte warten...');
    $feedback.show();
    jQuery('body').css('cursor', 'wait');

    var ajaxURL = window.s360_lpa_admin_url + 'php/backup_tables.php';

    var request = jQuery.ajax({
        url: ajaxURL,
        type: "post",
        dataType: "json",
        data: {
            'operation': 'export'
        }
    });
    request.done(function (data) {
        if (data.status === 'success') {
            if (!$feedback.hasClass('success')) {
                $feedback.addClass('success');
            }
            var feedback = '';
            for (var index = 0; index < data.messages.length; ++index) {
                feedback = feedback + data.messages[index] + '<br />';
            }
            $feedback.html(feedback + '<br />Ansicht wird in 3 Sekunden neu geladen...');
            setTimeout(function () {
                window.location.reload(true);
            }, 3000);
        } else {
            if (!$feedback.hasClass('failure')) {
                $feedback.addClass('failure');
            }
            var feedback = '';
            for (var index = 0; index < data.messages.length; ++index) {
                feedback = feedback + data.messages[index] + '<br />';
            }
            $feedback.html(feedback);
        }
    });
    request.fail(function (jqXHR, textStatus, errorThrown) {
        console.log('Failed: ' + jqXHR + "," + textStatus + "," + errorThrown);
        if (!$feedback.hasClass('failure')) {
            $feedback.addClass('failure');
        }
        $feedback.html('Fehler: Ein technischer Fehler ist aufgetreten. Bitte pr&uuml;fen Sie das Browser-Log (F12) und ggf. das Server-Log.');
    });
    request.always(function () {
        jQuery('body').css({'cursor': 'default'});
    });
}

/**
 * AJAX Function, die die Plugintabellen exportiert.
 */
function importTables() {

    var $feedback = jQuery('#extended-functions-feedback');
    var id = jQuery('select[name="lpa_import_path"]').val();
    $feedback.hide();
    $feedback.html('');

    /*
     * The user entered potentially valid data. Start the check.
     */
    $feedback.removeClass('success');
    $feedback.removeClass('failure');
    $feedback.html('<br />Bitte warten...');
    $feedback.show();
    jQuery('body').css('cursor', 'wait');

    var ajaxURL = window.s360_lpa_admin_url + 'php/backup_tables.php';

    var request = jQuery.ajax({
        url: ajaxURL,
        type: "post",
        dataType: "json",
        data: {
            'operation': 'import',
            'id': id
        }
    });
    request.done(function (data) {
        if (data.status === 'success') {
            if (!$feedback.hasClass('success')) {
                $feedback.addClass('success');
            }
            var feedback = '';
            for (var index = 0; index < data.messages.length; ++index) {
                feedback = feedback + data.messages[index] + '<br />';
            }
            $feedback.html(feedback + '<br />Ansicht wird in 3 Sekunden neu geladen...');
            setTimeout(function () {
                window.location.reload(true);
            }, 3000);
        } else {
            if (!$feedback.hasClass('failure')) {
                $feedback.addClass('failure');
            }
            var feedback = '';
            for (var index = 0; index < data.messages.length; ++index) {
                feedback = feedback + data.messages[index] + '<br />';
            }
            $feedback.html(feedback);
        }
    });
    request.fail(function (jqXHR, textStatus, errorThrown) {
        console.log('Failed: ' + jqXHR + "," + textStatus + "," + errorThrown);
        if (!$feedback.hasClass('failure')) {
            $feedback.addClass('failure');
        }
        $feedback.html('Fehler: Ein technischer Fehler ist aufgetreten. Bitte pr&uuml;fen Sie das Browser-Log (F12) und ggf. das Server-Log.');
    });
    request.always(function () {
        jQuery('body').css({'cursor': 'default'});
    });
}


/**
 * AJAX Function, die die Plugintabellen migriert.
 */
function migrateTables() {

    var $feedback = jQuery('#extended-functions-feedback');
    $feedback.hide();
    $feedback.html('');

    /*
     * The user entered potentially valid data. Start the check.
     */
    $feedback.removeClass('success');
    $feedback.removeClass('failure');
    $feedback.html('<br />Bitte warten...');
    $feedback.show();
    jQuery('body').css('cursor', 'wait');

    var ajaxURL = window.s360_lpa_admin_url + 'php/migrate_tables.php';

    var request = jQuery.ajax({
        url: ajaxURL,
        type: "post",
        dataType: "json",
        data: {
            'operation': 'migrate',
        }
    });
    request.done(function (data) {
        if (data.status === 'success') {
            if (!$feedback.hasClass('success')) {
                $feedback.addClass('success');
            }
            var feedback = '';
            for (var index = 0; index < data.messages.length; ++index) {
                feedback = feedback + data.messages[index] + '<br />';
            }
            $feedback.html(feedback);
        } else {
            if (!$feedback.hasClass('failure')) {
                $feedback.addClass('failure');
            }
            var feedback = '';
            for (var index = 0; index < data.messages.length; ++index) {
                feedback = feedback + data.messages[index] + '<br />';
            }
            $feedback.html(feedback);
        }
    });
    request.fail(function (jqXHR, textStatus, errorThrown) {
        console.log('Failed: ' + jqXHR + "," + textStatus + "," + errorThrown);
        if (!$feedback.hasClass('failure')) {
            $feedback.addClass('failure');
        }
        $feedback.html('Fehler: Ein technischer Fehler ist aufgetreten. Bitte pr&uuml;fen Sie das Browser-Log (F12) und ggf. das Server-Log.');
    });
    request.always(function () {
        jQuery('body').css({'cursor': 'default'});
    });
}
