<div class="col-xs-12 lpa-alternate-checkout-snippet">
    <div class="well panel-wrap" {if $lpa_general_hiddenbuttons_active == 1}style="visibility:hidden;display:none;"{/if}>
        <div class="panel-default panel">
            <div class="panel-heading">
                <h3 class="panel-title">{$oPlugin_s360_amazon_lpa_shop4->oPluginSprachvariableAssoc_arr.lpa_pay_with_amazon}</h3>
            </div>
            <div class="panel-body">
                <div class="col-xs-12 col-md-4 center lpa-checkout-hint-button">
                    {$lpa_button_snippet}
                </div>
                <div class="col-xs-12 col-md-8 lpa-checkout-hint-text">
                    {$oPlugin_s360_amazon_lpa_shop4->oPluginSprachvariableAssoc_arr.lpa_checkout_hint}
                </div>
            </div>
        </div>
    </div>
</div>