/* 
 * Solution 360 GmbH
 */
jQuery(document).ready(function () {

    // execute initial checks
    displayOrHideCustomEndpointInput();

    // register functions
    jQuery('select[name=mws_country]').change(displayOrHideCustomEndpointInput);
    jQuery('#check-mws-access-button').click(checkMWSAccess);
});

/*
 * Displays custom endpoint inputs iff the
 * selected country is "other".
 */
function displayOrHideCustomEndpointInput() {
    var country = jQuery('select[name=mws_country]').val();
    if (country === 'other') {
        jQuery('.lpa-endpoint-other-input').show();
    } else {
        jQuery('.lpa-endpoint-other-input').hide();
    }
}

/*
 * Checks if the given MWS data is valid by issuing a GetOrderReferenceDetails
 * request on S00-0000000-0000000.
 */
function checkMWSAccess() {

    var $feedback = jQuery('#check-mws-access-feedback');
    var $form = jQuery('#lpa-account-settings-form');
    $feedback.hide();
    $feedback.html('');

    var errorHtml = '';

    var merchantId = jQuery.trim(jQuery('input[name=merchant_id]').val());
    jQuery('input[name=merchant_id]').val(merchantId);
    if (!merchantId) {
        errorHtml += "<br />Bitte geben Sie die Merchant-ID an.";
    }
    var mwsAccessKey = jQuery.trim(jQuery('input[name=mws_access_key]').val());
    jQuery('input[name=mws_access_key]').val(mwsAccessKey);
    if (!mwsAccessKey) {
        errorHtml += "<br />Bitte geben Sie den MWS-Access-Key an.";
    }
    var mwsSecretKey = jQuery.trim(jQuery('input[name=mws_secret_key]').val());
    jQuery('input[name=mws_secret_key]').val(mwsSecretKey);
    if (!mwsSecretKey) {
        errorHtml += "<br />Bitte geben Sie den MWS-Secret-Key an.";
    }
    var environment = jQuery('select[name=mws_environment]').val();
    if (!environment) {
        errorHtml += "<br />Bitte w&auml;hlen Sie den Betriebsmodus aus.";
    }
    var region = jQuery('select[name=mws_region]').val();
    var manual_endpoint_service = jQuery.trim(jQuery('input[name=mws_custom_endpoint_service]').val());
    jQuery('input[name=mws_custom_endpoint_service]').val(manual_endpoint_service);
    var manual_endpoint_widget = jQuery.trim(jQuery('input[name=mws_custom_endpoint_widget]').val());
    jQuery('input[name=mws_custom_endpoint_widget]').val(manual_endpoint_widget);
    if (!region) {
        errorHtml += "<br />Bitte w&auml;hlen Sie das Land des MWS-Accounts aus.";
    } else if (region === 'other' && (!manual_endpoint_service || !manual_endpoint_widget)) {
        errorHtml += "<br />Bitte geben Sie beide manuellen Endpoints an.";
    }
    var clientId = jQuery.trim(jQuery('input[name=lpa_client_id]').val());
    jQuery('input[name=lpa_client_id]').val(clientId);
    if (!clientId) {
        errorHtml += "<br />Bitte geben Sie die Client-ID an.";
    }

    if (errorHtml) {
        $feedback.html(errorHtml);
        $feedback.show();
        return;
    }

    /*
     * The user entered potentially valid data. Start the check.
     */
    $feedback.removeClass('success');
    $feedback.removeClass('failure');
    $feedback.html('<br />Bitte warten...');
    $feedback.show();
    $form.css('cursor', 'wait');

    var ajaxURL = window.s360_lpa_admin_url + 'php/check_mws_access.php';

    // send request to our form (set via script provider)
    var request = jQuery.ajax({
        url: ajaxURL,
        type: "post",
        dataType: "json",
        data: {
            'merchant_id': merchantId,
            'mws_access_key': mwsAccessKey,
            'mws_secret_key': mwsSecretKey,
            'mws_environment': environment,
            'mws_region': region,
            'mws_custom_endpoint_service': manual_endpoint_service,
            'mws_custom_endpoint_widget': manual_endpoint_widget,
            'lpa_client_id': clientId
        }
    });
    // callback handler that will be called on success
    request.done(function (data) {
        if (data.status === 'success') {
            if (!$feedback.hasClass('success')) {
                $feedback.addClass('success');
            }
            $feedback.html('<br />Pr&uuml;fung erfolgreich! Bitte speichern Sie die Einstellungen, um die Daten permanent zu &uuml;bernehmen.');
        } else {
            if (!$feedback.hasClass('failure')) {
                $feedback.addClass('failure');
            }
            if (data.error === 'accessKey') {
                $feedback.html('Fehler: Der eingegebene AccessKey ist falsch.');
            } else if (data.error === 'secretKey') {
                $feedback.html('Fehler: Der eingegebene SecretKey ist falsch.');
            } else if (data.error === 'merchantId') {
                $feedback.html('Fehler: Die eingegebene Merchant ID ist falsch.');
            } else {
                $feedback.html('Fehler: Unbekannter Fehler. Bitte pr&uuml;fen Sie das Log.');
            }
        }
    });
    // callback handler that will be called on failure
    request.fail(function (jqXHR, textStatus, errorThrown) {
        console.log('Failed: ' + jqXHR + "," + textStatus + "," + errorThrown);
        if (!$feedback.hasClass('failure')) {
            $feedback.addClass('failure');
        }
        $feedback.html('Fehler: Ein technischer Fehler ist aufgetreten. Bitte pr&uuml;fen Sie das Log.');
    });
    // callback handler that will be called regardless
    // if the request failed or succeeded
    request.always(function () {
        // change cursor to default
        $form.css({'cursor': 'default'});
    });
}