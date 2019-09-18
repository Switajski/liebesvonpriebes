/* 
 * This script contains functions needed during the checkout with amazon.
 */

$(document).ready(function () {

    if (typeof lpa_other_url_complete_localized === "undefined" || typeof lang_please_wait === "undefined" || typeof lpa_ajax_url_confirm_order === "undefined") {
        return;
    }

    $('body').append('<div id="lpa-checkout-overlay"></div><div id="lpa-checkout-overlay-content">' + lang_please_wait + '</div>');

    /*
     * This handles the order confirmation by the customer.
     */
    $('#lpa-confirm-order-form').submit(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        /**
         * PSD2 Compliance
         */
        var orid = $('#lpa-orid-input').val();
        var sellerId = $('#lpa-seller-id-input').val();
        if(typeof sellerId === 'string' && sellerId !== '') {
            if (typeof orid === 'string' && orid !== '') {
                OffAmazonPayments.initConfirmationFlow(
                    sellerId,
                    orid,
                    function (confirmationFlow) {
                        lpa_placeOrder(confirmationFlow);
                    }
                );
            } else {
                console.error('Error: Order Reference ID is not set');
            }
        } else {
            console.error('Error: Seller ID is not set');
        }

    });
});

function lpa_placeOrder(confirmationFlow) {
    $('#lpa-confirm-message').hide();
    $('#lpa-confirm-payment-message').hide();

    var $form = $('#lpa-confirm-order-form');
    var $inputs = $form.find("input, select, button, textarea");
    var formData = $form.serialize() + "&lpa_ajax=1";
    var $overlay = $('#lpa-checkout-overlay, #lpa-checkout-overlay-content');

    /* disable inputs while submitting */
    $inputs.prop("disabled", true);
    $overlay.show();
    $form.css({'cursor': 'wait'});
    var request = $.ajax({
        url: lpa_ajax_url_confirm_order,
        type: "post",
        dataType: "json",
        data: formData
    });
    request.done(function (data) {
        if (data.state === 'error') {
            console.log(data.error);
            /* re-enable the disabled inputs (although they may be invisible now) */
            $inputs.prop("disabled", false);
            $overlay.hide();
            $form.css({'cursor': 'default'});
            lpa_handleOrderConfirmationError(data.error);
            confirmationFlow.error();
        } else if (data.state === 'success') {
            confirmationFlow.success();
        } else {
            alert('Unerwartetes Ergebnis: ' + data);
            /* re-enable the disabled inputs (although they may be invisible now) */
            $inputs.prop("disabled", false);
            $overlay.hide();
            $form.css({'cursor': 'default'});
            confirmationFlow.error();
        }
    });
    request.fail(function (jqXHR, textStatus, errorThrown) {
        console.log('Failed: ' + jqXHR + "," + textStatus + "," + errorThrown);
        /* re-enable the disabled inputs (although they may be invisible now) */
        $inputs.prop("disabled", false);
        $overlay.hide();
        $form.css({'cursor': 'default'});
        confirmationFlow.error();
    });
}

/*
 * Sends an AJAX call to the respective script that returns the available selection for
 * delivery types.
 */
function lpa_updateDeliverySelection(orderReference) {
    $('#shippingMethodSelectionDiv').hide();
    $('#lpa-checkout-nextstep').hide();
    var request = $.ajax({
        url: lpa_ajax_url_update_delivery_selection,
        type: "post",
        dataType: "json",
        data: {'lpa_ajax': '1', 'orid': orderReference.getAmazonOrderReferenceId()}
    });
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
    request.fail(function (jqXHR, textStatus, errorThrown) {
        console.log('Failed: ' + jqXHR + "," + textStatus + "," + errorThrown);
    });
    request.always(function () {

    });
}

/*
 * Sets the selected shipping method, including updating the total amount, such that the walletWidget is updated as well.
 */
function lpa_updateSelectedShippingMethod(selectedShippingMethod, orderReference) {
    /* remove error/info field */
    $('#shippingmethodform').find('.alert').hide();
    $('#lpa-checkout-nextstep').hide();
    $('body').css('cursor', 'wait');

    var request = $.ajax({
        url: lpa_ajax_url_update_selected_shipping_method,
        type: "post",
        dataType: "json",
        data: {
            'lpa_ajax': '1', 'orid': orderReference.getAmazonOrderReferenceId(),
            'kVersandart': selectedShippingMethod
        }
    });
    request.done(function (data) {
        /* initialize the wallet widget if we didnt initialize it before */
        if (!window.walletInitialized) {
            window.walletInitFunc();
            window.walletInitialized = true;
        }
        lpa_updatePaymentSelection();
    });
    request.fail(function (jqXHR, textStatus, errorThrown) {
        console.log('Failed: ' + jqXHR + "," + textStatus + "," + errorThrown);
    });
    request.always(function () {
        $('body').css('cursor', 'auto');
    });
}

function lpa_updatePaymentSelection() {
    /* Payment method selected, show the checkout button if also a delivery method was selected */
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
    var $confirmMessageField = $('#lpa-confirm-message');
    if (type === 'InvalidPaymentMethod' || type === 'PaymentMethodNotAllowed') {
        /* soft decline */
        var $confirmPaymentMessageField = $('#lpa-confirm-payment-message');
        if($confirmPaymentMessageField.length) {
            $confirmPaymentMessageField.text(message);
            $confirmPaymentMessageField.show();
        } else {
            $confirmMessageField.text(message);
            $confirmMessageField.show();
        }

        $('#readOnlyWalletWidgetDiv').hide();
        $('#editWalletWidgetDiv').show();

        $('#lpa-confirm-order-form').append('<input type="hidden" name="retryAuth" value="1" />');
    } else if (type === 'AmazonRejected') {
        /* hard decline */
        $confirmMessageField.text(message);
        $confirmMessageField.show();
        $('#lpa-confirm-order-form input[type="submit"]').hide();

    } else if (type === 'Plausi') {
        $confirmMessageField.text(message);
        $confirmMessageField.show();
    } else if (type === 'Checksum') {
        $confirmMessageField.text(message);
        $confirmMessageField.show();
    } else {
        $confirmMessageField.text(type + ': ' + message);
        $confirmMessageField.show();
    }

    $(window).scrollTop(0);

}
