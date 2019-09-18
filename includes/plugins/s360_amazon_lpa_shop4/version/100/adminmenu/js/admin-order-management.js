/* 
 * Solution 360 GmbH
 */
$(document).ready(function () {
    $('.lpa-admin-order-entry').click(function () {
        var $this = $(this).closest('.lpa-admin-order-entry');
        $('#lpa-order-table .lpa-admin-order-entry').removeClass('active');
        $this.addClass('active');
        lpaReset('auth');
        lpaReset('cap');
        lpaReset('refund');
        lpaShowFor('auth', 'order', $this.data('orderid'));
    });

    $('.lpa-admin-auth-entry').click(function () {
        var $this = $(this).closest('.lpa-admin-auth-entry');
        $('#lpa-auth-table .lpa-admin-auth-entry').removeClass('active');
        $this.addClass('active');
        lpaReset('cap');
        lpaReset('refund');
        lpaShowFor('cap', 'auth', $this.data('authid'));
    });

    $('.lpa-admin-cap-entry').click(function () {
        var $this = $(this).closest('.lpa-admin-cap-entry');
        $('#lpa-cap-table .lpa-admin-cap-entry').removeClass('active');
        $this.addClass('active');
        lpaReset('refund');
        lpaShowFor('refund', 'cap', $this.data('capid'));
    });

    /*
     * Management functions: Order
     */
    $('.lpa-admin-order-authorize').click(function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        var amount = $(this).closest('td').find('input[name="amount"]').val();
        lpaManage($(this), 'order', 'authorize', amount);
    });
    $('.lpa-admin-order-cancel').click(function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        lpaManage($(this), 'order', 'cancel');
    });
    $('.lpa-admin-order-close').click(function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        lpaManage($(this), 'order', 'close');
    });
    $('.lpa-admin-order-refresh').click(function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        lpaManage($(this), 'order', 'refresh');
    });

    /*
     * Management functions: Authorizations
     */
    $('.lpa-admin-auth-close').click(function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        lpaManage($(this), 'auth', 'close');
    });
    $('.lpa-admin-auth-capture').click(function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        var amount = $(this).closest('td').find('input[name="amount"]').val();
        lpaManage($(this), 'auth', 'capture', amount);
    });

    /*
     * Management functions: Captures
     */
    $('.lpa-admin-cap-refund').click(function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        var amount = $(this).closest('td').find('input[name="amount"]').val();
        lpaManage($(this), 'cap', 'refund', amount);
    });

});

/*
 * Triggers management functions by first filling the submit form and then submitting it (this also forces a reload from the database data to show up to date information).
 */
function lpaManage($object, type, action, amount) {
    var id;
    var orid;
    if (type === 'order') {
        id = $object.closest('.lpa-admin-order-entry').data('orderid');
        orid = id;
    } else if (type === 'auth') {
        id = $object.closest('.lpa-admin-auth-entry').data('authid');
        orid = $object.closest('.lpa-admin-auth-entry').data('orderid');
    } else if (type === 'cap') {
        id = $object.closest('.lpa-admin-cap-entry').data('capid');
        var authid = $object.closest('.lpa-admin-cap-entry').data('authid');
        orid = $('#lpa-auth-table .lpa-admin-auth-entry[data-authid="' + authid + '"]').data('orderid');
    }

    var $form = $('#lpa-order-management-form');
    $form.find('input[name="lpa_type"]').val(type);
    $form.find('input[name="lpa_id"]').val(id);
    $form.find('input[name="lpa_orid"]').val(orid);
    $form.find('input[name="lpa_action"]').val(action);
    if (typeof amount !== 'undefined') {
        amount = amount.replace(',', '.');
        $form.find('input[name="lpa_amount"]').val(amount);
    }
    $form.submit();
}

/*
 * Shows the corresponding entries for the given key and value.
 *
 * i.e.: lpaShowFor('auth', 'order', orderID) will show all authorization entries for the given order with that orderID.
 */
function lpaShowFor(object, key, value) {
    $('#lpa-' + object + '-table-hint').hide();
    $('#lpa-' + object + '-table').show();
    $('#lpa-' + object + '-table .lpa-admin-' + object + '-entry').hide();
    if ($('#lpa-' + object + '-table .lpa-admin-' + object + '-entry[data-' + key + 'id="' + value + '"]').length > 0) {
        $('#lpa-' + object + '-table .lpa-admin-' + object + '-entry[data-' + key + 'id="' + value + '"]').show();
    } else {
        $('#lpa-' + object + '-table-hint').show();
    }
}

function lpaReset(type) {
    $('#lpa-' + type + '-table-hint').show();
    $('#lpa-' + type + '-table .lpa-admin-' + type + '-entry').hide();
    $('#lpa-' + type + '-table .lpa-admin-' + type + '-entry').removeClass('active');
}

