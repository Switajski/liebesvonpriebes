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
    
    /*
     * Pagination functions
     */
    $('.lpa-admin-pagination button').click(function(e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        var targetPage = $(this).closest('.btn').data('page');
        $('#lpa-admin-pagination-form').find('input[name="page"]').val(targetPage);
        $('#lpa-admin-pagination-form').submit();
    });
});

/*
 * Triggers management functions by first filling the submit form and then submitting it (this also forces a reload from the database data to show up to date information).
 */
function lpaManage($object, type, action, amount) {
    var id;
    var orid;
    var confirmationTitle = 'Best&auml;tigung erforderlich';
    var confirmationDescription = 'Sind Sie sicher?';
    var confirmationRequired = true;
    if (type === 'order') {
        id = $object.closest('.lpa-admin-order-entry').data('orderid');
        orid = id;
        if(action === 'refresh') {
            confirmationRequired = false;
        } else if(action === 'authorize') {
            confirmationDescription = 'Sie sind im Begriff, eine <b>neue</b> Autorisierung  i.H.v. '+amount+' f&uuml;r die Bestellreferenz '+orid+' zu holen.';
        } else if(action === 'cancel') {
            confirmationDescription = 'Sie sind im Begriff, die Bestellreferenz '+orid+' abzubrechen. Alle zugeh&ouml;rigen Autorisierungen werden automatisch geschlossen.';
        } else if(action === 'close') {
            confirmationDescription = 'Sie sind im Begriff, die Bestellreferenz '+orid+' zu schlie&szlig;en. Sie k&ouml;nnen dann keine neuen Autorisierungen anfordern. Falls es noch offene Autorisierungen gibt, k&ouml;nnen gegen diese noch Zahlungseinz&uuml;ge durchgef&uuml;hrt werden.';
        }
    } else if (type === 'auth') {
        id = $object.closest('.lpa-admin-auth-entry').data('authid');
        orid = $object.closest('.lpa-admin-auth-entry').data('orderid');
        if(action === 'capture') {
            confirmationDescription = 'Sie sind im Begriff, einen Zahlungseinzug i.H.v. '+amount+' f&uuml;r die Bestellreferenz '+orid+' auszul&ouml;sen.';
        } else if(action === 'close') {
            confirmationDescription = 'Sie sind im Begriff, die Autorisierung '+id+' zu schlie&szlig;en. Gegen diese Autorisierung k&ouml;nnen dann keine Zahlungseinz&uuml;ge mehr ausgel&ouml;st werden.';
        }
    } else if (type === 'cap') {
        id = $object.closest('.lpa-admin-cap-entry').data('capid');
        var authid = $object.closest('.lpa-admin-cap-entry').data('authid');
        orid = $('#lpa-auth-table .lpa-admin-auth-entry[data-authid="' + authid + '"]').data('orderid');
        if(action === 'refund') {
            confirmationDescription = 'Sie sind im Begriff, einen Erstattung i.H.v. '+amount+' f&uuml;r die Bestellreferenz '+orid+' auszul&ouml;sen.';
        }
    }

    var submitFormFunc = function () {
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
    };

    if(confirmationRequired) {
        $.confirm({
            title: confirmationTitle,
            content: confirmationDescription+'<br/><br/>Sind Sie sicher?',
            buttons: {
                nok: {
                    text: 'Nein',
                    action: function () {
                        return true;
                    }
                },
                ok: {
                    text: 'Ja',
                    btnClass: 'btn-red',
                    action: submitFormFunc
                }
            }
        });
    } else {
        submitFormFunc();
    }
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

