/* 
 * This script contains functions needed during the checkout with amazon.
 */

$(document).ready(function () {

    if (typeof lpa_other_url_complete_localized === "undefined" || typeof lang_please_wait === "undefined" || typeof lpa_ajax_url_confirm_order === "undefined") {
        // console.log("LPA: Warnung: Nicht alle benötigten Variablen wurden initialisiert!");
        return;
    }

    $('body').append('<div id="lpa-checkout-overlay"></div><div id="lpa-checkout-overlay-content">' + lang_please_wait + '</div>');

    /*
     * This handles the order confirmation by the customer.
     */
    $('#lpa-confirm-order-form').submit(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        $('#lpa-confirm-message').hide();

        var $this = $(this);
        var $inputs = $this.find("input, select, button, textarea");
        var formData = $this.serialize();
        var $overlay = $('#lpa-checkout-overlay, #lpa-checkout-overlay-content');

        // disable inputs while submitting
        $inputs.prop("disabled", true);
        $overlay.show();
        // change cursor to waiting
        $this.css({'cursor': 'wait'});
        // send request to our form
        var request = $.ajax({
            url: lpa_ajax_url_confirm_order,
            type: "post",
            dataType: "json",
            data: formData
        });
        // callback handler that will be called on success
        request.done(function (data) {
            if (data.state === 'error') {
                console.log(data.error);
                // re-enable the disabled inputs (although they may be invisible now)
                $inputs.prop("disabled", false);
                $overlay.hide();
                // change cursor to default
                $this.css({'cursor': 'default'});
                lpa_handleOrderConfirmationError(data.error);
            } else if (data.state === 'success') {
                location.replace(lpa_other_url_complete_localized);
            } else {
                alert('Unerwartetes Ergebnis: ' + data);
                // re-enable the disabled inputs (although they may be invisible now)
                $inputs.prop("disabled", false);
                $overlay.hide();
                // change cursor to default
                $this.css({'cursor': 'default'});
            }
        });
        // callback handler that will be called on failure
        request.fail(function (jqXHR, textStatus, errorThrown) {
            console.log('Failed: ' + jqXHR + "," + textStatus + "," + errorThrown);
            // re-enable the disabled inputs (although they may be invisible now)
            $inputs.prop("disabled", false);
            $overlay.hide();
            // change cursor to default
            $this.css({'cursor': 'default'});
        });

    });
});

/*
 * Sends an AJAX call to the respective script that returns the available selection for
 * delivery types.
 */
function lpa_updateDeliverySelection(orderReference) {
    // send request to our form (set via script provider)
    $('#shippingMethodSelectionDiv').hide();
    $('#lpa-checkout-nextstep').hide();
    var request = $.ajax({
        url: lpa_ajax_url_update_delivery_selection,
        type: "post",
        dataType: "json",
        data: {'orid': orderReference.getAmazonOrderReferenceId()}
    });
    // callback handler that will be called on success
    request.done(function (data) {
        if (data.status === 'success') {
            $('.lpa-error-message').hide();
            $('#shippingMethodSelectionDiv').html(data.html);
            $('#shippingMethodSelectionDiv').show();
        } else {
            $('#lpa-error-' + data.code).show();
            $('#shippingMethodSelectionDiv').hide();
        }
    });
    // callback handler that will be called on failure
    request.fail(function (jqXHR, textStatus, errorThrown) {
        console.log('Failed: ' + jqXHR + "," + textStatus + "," + errorThrown);
    });
    // callback handler that will be called regardless
    // if the request failed or succeeded
    request.always(function () {

    });
}

/*
 * Sets the selected shipping method, including updating the total amount, such that the walletWidget is updated as well.
 */
function lpa_updateSelectedShippingMethod(selectedShippingMethod, orderReference) {
    // remove error/info field
    $('#shippingmethodform').find('.alert').hide();
    $('#lpa-checkout-nextstep').hide();
    $('body').css('cursor', 'wait');

    // send request to our form (set via script provider)
    var request = $.ajax({
        url: lpa_ajax_url_update_selected_shipping_method,
        type: "post",
        dataType: "json",
        data: {'orid': orderReference.getAmazonOrderReferenceId(),
            'kVersandart': selectedShippingMethod}
    });
    // callback handler that will be called on success
    request.done(function (data) {
        // console.log(data.amount);
        // also: update the content of the wk pos field
        $('#warenkorbPositionenDiv').html(data.wkpos);
        window.walletInitFunc();
    });
    // callback handler that will be called on failure
    request.fail(function (jqXHR, textStatus, errorThrown) {
        console.log('Failed: ' + jqXHR + "," + textStatus + "," + errorThrown);
    });
    // callback handler that will be called regardless
    // if the request failed or succeeded
    request.always(function () {
        $('body').css('cursor', 'auto');
    });
}

function lpa_updatePaymentSelection(orderReference) {
    // Payment method selected, show the checkout button if also a delivery method was selected
    if ($('#shippingMethodSelectionDiv').is(':visible') && $('#shippingmethodform input[name="Versandart"]:checked').length) {
        $('#lpa-checkout-nextstep').show();
    }
}

/*
 * Handles errors from the order confirmation.
 */
function lpa_handleOrderConfirmationError(error) {
    var type = error.type;
    var message = error.message;
    if (type === 'InvalidPaymentMethod') {
        /* soft decline */
        $('#lpa-confirm-message').text(message);
        $('#lpa-confirm-message').show();

        $('#readOnlyWalletWidgetDiv').hide();
        $('#editWalletWidgetDiv').show();
        
        /*
         * rerender the widgets
         */
        for (var i = 0; i < window.lpaCallbacks.length; i++) {
            window.lpaCallbacks[i]();
        }

        $('#lpa-confirm-order-form').append('<input type="hidden" name="retryAuth" value="1" />');
    } else if (type === 'AmazonRejected') {
        /* hard decline */
        $('#lpa-confirm-message').text(message);
        $('#lpa-confirm-message').show();
        $('#lpa-confirm-order-form input[type="submit"]').hide();
        
    } else if (type === 'Plausi') {
        $('#lpa-confirm-message').text(message);
        $('#lpa-confirm-message').show();
    } else {
        $('#lpa-confirm-message').text(type + ': ' + message);
        $('#lpa-confirm-message').show();
    }
    
    $(window).scrollTop(0);

}
