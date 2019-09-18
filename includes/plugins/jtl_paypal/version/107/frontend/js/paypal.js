/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

(function () {
    'use strict';

    var PayPal = function (options) {
        this.init(options);
    };

    PayPal.prototype = {

        constructor: PayPal,

        init: function (options) {
            this.options = $.extend({}, { selector: '' }, options);
        },

        getInstallments: function (amount, currency) {
            $.evo.io().call('jtl_paypal_get_presentment', [amount, currency], this, function(error, data) {
                if (error) return;
                $(data.options.selector)
                    .replaceWith(data.response);
            });
        }
    };

    var jtl_paypal = function(options) {
        return new PayPal(options);
    };

    if (!window.paypal) {
        window.paypal = jtl_paypal;
    }

})(jQuery);