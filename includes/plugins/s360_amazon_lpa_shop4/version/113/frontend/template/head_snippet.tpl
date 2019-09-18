<script type='text/javascript'>
    window.lpaCallbacksExecuted = false;
    window.onAmazonLoginReady = function () {ldelim}
            amazon.Login.setClientId('{$lpa_client_id}');
            amazon.Login.setUseCookie(true);
            amazon.Login.setSandboxMode({$lpa_sandbox_mode});
    {rdelim};
        window.onAmazonPaymentsReady = function () {ldelim}
                /* prevents later scripts from registering too late for the event if the async was loaded "too fast" */
                window.lpaCallbacksExecuted = true; 
                if (typeof window.lpaCallbacks === "undefined") {ldelim}
                    window.lpaCallbacks = [];
                {rdelim}
                for (var i = 0; i < window.lpaCallbacks.length; i++) {ldelim}
                    window.lpaCallbacks[i]();
                {rdelim}
    {rdelim};
</script>
<script type="text/javascript">
    /* AJAX URL definitions */
    var lpa_ajax_url_update_delivery_selection = '{$lpa_ajax_urls.delivery_selection}';
    var lpa_ajax_url_update_selected_shipping_method = '{$lpa_ajax_urls.update_selected_shipping_method}';
    var lpa_ajax_url_confirm_order = '{$lpa_ajax_urls.confirm_order}';
    var lpa_ajax_url_select_account_address = '{$lpa_ajax_urls.select_account_address}';
    /* other URLs */
    var lpa_other_url_checkout = '{$lpa_other_urls.checkout}';
    var lpa_other_url_complete_localized = '{$lpa_other_urls.complete_localized}';
    /* other variables */
    var lang_please_wait = '{$oPlugin_s360_amazon_lpa_shop4->oPluginSprachvariableAssoc_arr.lpa_please_wait}';
</script>
