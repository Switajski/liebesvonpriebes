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
        ppplus: 'https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js',

        constructor: PayPal,

        init: function (options) {
            this.options = $.extend({}, { selector: '' }, options || {});
        },

        loadPaymentWall: function(loaded) {
            if (typeof PAYPAL != 'undefined') {
                return (typeof loaded == 'function') ? loaded() : true;
            }
            this.getScript(this.ppplus).done(function() {
                var validate = function() {
                    if (typeof PAYPAL == 'undefined') {
                        window.setTimeout(function() {
                            validate();
                        }, 100);
                    }
                    else if (typeof loaded == 'function') {
                        loaded();
                    }
                }
                validate();
            });
        },

        getInstallments: function (amount, currency) {
            $.evo.io().call('jtl_paypal_get_presentment', [amount, currency], this, function(error, data) {
                if (error) return;
                $(data.options.selector)
                    .html(data.response || '');
            });
        },

        getScript: function (url, options) {
            return jQuery.ajax($.extend(options || {}, {
                dataType: "script",
                cache: true,
                url: url
            }));
        }
    };

    var jtl_paypal = function(options) {
        return new PayPal(options);
    };

    if (!window.paypal) {
        window.paypal = jtl_paypal;
    }

    $(document).on("evo:changed.price.article", function(e, price) {
        paypal({ selector: '.ppf-loader' }).getInstallments(price);
    });
})(jQuery);
