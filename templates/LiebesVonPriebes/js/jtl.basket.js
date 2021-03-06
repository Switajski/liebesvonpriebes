/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
(function() {
    'use strict';
    console.log('my own basket!');
    var BasketClass = function(options) {
        this.init(options);
    };

    BasketClass.DEFAULTS = {
        input: {
            id: 'a',
            quantity: 'anzahl'
        },
        selector: {
            list: {
                main: '*[data-toggle="basket-add"]',
                form: 'form.form-basket',
                quantity: 'input.quantity',
                submit: '*[type="submit"]',
                loading: 'io-loading'
            },
            cart: {
                container: '*[data-toggle="basket-items"]'
            }
        }
    };

    BasketClass.prototype = {

        constructor: BasketClass,

        init: function(options) {
            this.options = $.extend({}, BasketClass.DEFAULTS, options);
        },

        addToBasket: function($form) {
            var $main = $form;
            var data = $form.serializeObject();

            var productId = parseInt(data[this.options.input.id]);
            var quantity = parseFloat(
                data[this.options.input.quantity]
            );

            if (productId > 0 && quantity > 0) {
                this.pushToBasket($main, productId, quantity, data);
            }
        },

        pushToBasket: function($main, productId, quantity, data) {
            var that = this;

            that.toggleState($main, true);

            $.evo.io().call('pushToBasket', [productId, quantity, data], that, function(error, data) {

                that.toggleState($main, false);

                if (error) {
                    return;
                }

                var response = data.response;

                if (response) {
                    switch (response.nType) {
                        case 0: // error
                            that.error(response);
                            break;
                        case 1: // forwarding
                            that.redirectTo(response);
                            break;
                        case 2: // added to basket
                            that.updateCart();
                            that.pushedToBasket(response);
                            break;
                    }
                }
            });
        },

        toggleState: function($main, loading) {
            var cls = this.options.selector.list.loading;
            if (loading) {
                $main.addClass(cls);
            } else {
                $main.removeClass(cls);
            }
        },

        redirectTo: function(response) {
            window.location.href = response.cLocation;
        },

        error: function(response) {
            var errorlist = '<ul><li>' + response.cHints.join('</li><li>') + '</li></ul>';
            $.evo.extended().showNotify({
                text: errorlist,
                title: response.cLabel
            });
        },

        pushedToBasket: function(response) {
            $.evo.extended().showNotify({
                text: response.cPopup,
                title: response.cNotification
            });
        },

        updateCart: function(type) {
            var that = this,
                t = parseInt(type);

            $.evo.io().call('getBasketItems', [t], this, function(error, data) {
                if (error) {
                    return;
                }

                var tpl = data.response.cTemplate;

                $(that.options.selector.cart.container)
                    .empty()
                    .append(tpl);
            });
        }
    };

    // PLUGIN DEFINITION
    // =================

    $.evo.basket = function() {
        return new BasketClass();
    };

    // PLUGIN DATA-API
    // ===============

    $('*[data-toggle="basket-add"]').on('submit', function(event) {
        event.preventDefault();
        $.evo.basket().addToBasket($(this));
    });

    $('*[data-toggle="basket-items"]').on('show.bs.dropdown', function (event) {
        $.evo.basket().updateCart();
    });
})(jQuery);