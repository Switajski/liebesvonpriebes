{if !isset($lpa_express_button) || !$lpa_express_button}
    <div class="clearfix"></div>
{/if}
{assign var="lpa_timestamp_tag" value=$smarty.now}
<div class="{$lpa_button_type_class}{if $lpa_button_type_class === 'lpa-pay-button'} lpa-tooltip{else} text-center{/if}{if isset($lpa_express_button) && $lpa_express_button} lpa-pay-button-express{/if}" id="AmazonPay{if isset($lpa_express_button) && $lpa_express_button}Express{/if}Button_{$lpa_button_idx}{if isset($lpa_express_button) && $lpa_express_button}_{$lpa_timestamp_tag}{/if}" {if $lpa_general_hiddenbuttons_active == 1}style="visibility:hidden;display:none;"{/if} data-lpa-tooltip-text="{$lpa_button_tooltip}">
    {if isset($lpa_express_button) && $lpa_express_button}
        <div class="text-danger lpa-pay-button-express-feedback"></div>
    {/if}
</div>
<script type="text/javascript">
    var amazonPaymentsButtonFunc = function () {ldelim}
        var authRequest;
        OffAmazonPayments.Button("AmazonPay{if isset($lpa_express_button) && $lpa_express_button}Express{/if}Button_{$lpa_button_idx}{if isset($lpa_express_button) && $lpa_express_button}_{$lpa_timestamp_tag}{/if}", "{$lpa_seller_id}", {ldelim}
            type: "{$lpa_button_type}",
            color: "{$lpa_button_color}",
            size: "{$lpa_button_size}",
            language: "{$lpa_language_code}",
            useAmazonAddressBook: true,
            authorization: function () {ldelim}
                {if isset($lpa_express_button) && $lpa_express_button}
                {* Use this to add the current article to the basket -

                    productId = kArtikel, or the input field with the name "a",
                    quantity = value of input with name "anzahl"
                    data = serialized buy_form ($form.serializeObject)


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
                *}
                {literal}
                try {
                    var idName = 'a';
                    var childIdName = 'VariKindArtikel';
                    var qtyName = 'anzahl';
                    var buyFormSelector = '#buy_form{/literal}{if $lpa_express_button_type === 'listing'}_{$lpa_express_button_key}{/if}{literal}';
                    var $buyForm = $(buyFormSelector);
                    var $button = $('#AmazonPayExpressButton_{/literal}{$lpa_button_idx}_{$lpa_timestamp_tag}{literal}');

                    // check if all required fields in the form are filled
                    if ($buyForm.find(':invalid').length) {
                        $button.find('.lpa-pay-button-express-feedback').html('{/literal}{$lpa_required_fields_message}{literal}');
                        console.log('LPA: Required form fields missing.');
                        return false;
                    }

                    var data = $buyForm.serializeObject();
                    var productId = 0;
                    if (typeof data[childIdName] !== 'undefined') {
                        productId = parseInt(data[childIdName]);
                    } else {
                        productId = parseInt(data[idName]);
                    }
                    var quantity = parseFloat(data[qtyName]);
                    var that = $.evo.basket();
                    $.evo.io().call('{/literal}{$lpa_express_push_method}{literal}', [productId, quantity, data], that, function (error, data) {
                        $button.css('cursor', 'initial');
                        if (error) {
                            // an error occurred during the io call, do nothing
                            console.log(error);
                            return false;
                        }
                        var response = data.response;
                        if (response) {
                            switch (response.nType) {
                                case 0: // error
                                    console.log(response);
                                    if (typeof response.cHints !== 'undefined') {
                                        var hints = '';
                                        for (var i = 0; i < response.cHints.length; i++) {
                                            hints = hints + response.cHints[i] + "\n";
                                        }
                                        $button.find('.lpa-pay-button-express-feedback').html(hints);
                                    }
                                    return false;
                                case 1:
                                    // we don't forward to basket in any way but to the lpa checkout, hence 1 is equal to 2 in our case
                                case 2:
                                    // added to basket, we don't want to show a notification and we don't need to update the cart dropdown either
                                    // instead we now fire the authorize method to get the lpa checkout going
                                {/literal}
                                    loginOptions = {ldelim}scope: "{$lpa_button_scope}", popup:{$lpa_button_popup}{rdelim};
                                    authRequest = amazon.Login.authorize(loginOptions, "{$lpa_login_redirect_uri}");
                                    return true;
                            {literal}
                            }
                        }
                        // we should not have gotten here
                        console.log(response);
                        return false;
                    });
                } catch (err) {
                    // on any error, prevent anything from happening
                    console.log(err);
                    return false;
                }
                {/literal}
                {else}
                loginOptions = {ldelim}scope: "{$lpa_button_scope}", popup:{$lpa_button_popup}{rdelim};
                authRequest = amazon.Login.authorize(loginOptions, "{$lpa_login_redirect_uri}");
                {/if}
                {rdelim},
            onError: function (error) {ldelim}
                console.log(error);
                {rdelim}
            {rdelim});
        {rdelim};
    {literal}
    if (typeof window.lpaCallbacksExecuted === "undefined" || !window.lpaCallbacksExecuted) {
        if (typeof window.lpaCallbacks === "undefined") {
            window.lpaCallbacks = [];
        }
        window.lpaCallbacks.push(amazonPaymentsButtonFunc);
    } else {
        /* callbacks were already executed, pushing us to the callback array would be too late now
         however, this implies that Amazon Pay Ready Event was fired and we can safely initialize directly */
        amazonPaymentsButtonFunc();
    }
    {/literal}
</script>