<div class="clearfix" />
<div class="{$lpa_button_type_class}{if $lpa_button_type_class === 'lpa-pay-button'} lpa-tooltip{else}text-center{/if}" id="AmazonPayButton_{$lpa_button_idx}" {if $lpa_general_hiddenbuttons_active == 1}style="visibility:hidden;display:none;"{/if} data-lpa-tooltip-text="{$lpa_button_tooltip}"/>
<script type="text/javascript">
    var amazonPaymentsButtonFunc = function () {ldelim}
            var authRequest;
            OffAmazonPayments.Button("AmazonPayButton_{$lpa_button_idx}", "{$lpa_seller_id}", {ldelim}
                        type: "{$lpa_button_type}",
                        color: "{$lpa_button_color}",
                        size: "{$lpa_button_size}",
                        language: "{$lpa_language_code}",
                        useAmazonAddressBook: true,
                        authorization: function () {ldelim}
                                        loginOptions = {ldelim}scope: "{$lpa_button_scope}", popup:{$lpa_button_popup}{rdelim};
                                                        authRequest = amazon.Login.authorize(loginOptions, "{$lpa_login_redirect_uri}");
    {rdelim},
                onError: function (error) {ldelim}
                                // error handling code
                                console.log(error);
    {rdelim}
    {rdelim});
    {rdelim};
    {literal}
        if (typeof window.lpaCallbacks === "undefined") {
            window.lpaCallbacks = [];
        }
        window.lpaCallbacks.push(amazonPaymentsButtonFunc);
    {/literal}
</script>