<script src="https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js" type="text/javascript"></script>    

{if $hinweis}
    <div class="alert alert-danger">{$hinweis}</div>
{/if}

<div class="row">    
    <div class="col-xs-12">
        <div class="panel-wrap">
            <fieldset>
            {if !empty($cFehler)}
                <div class="alert alert-danger">{$cFehler}</div>
            {/if}
            <div id="pp-plus">
                <div id="ppp-container"></div>
            </div>
            </fieldset>
            {if $embedded}
                <input id="ppp-submit" type="submit" value="{lang key="continueOrder" section="account data"}" class="btn btn-primary submit btn-lg pull-right" />
            {else}
                {block name="checkout-payment-options-body"}
                <form id="zahlung" method="post" action="bestellvorgang.php" class="form">
                    {$jtl_token}
                    <fieldset>
                        <ul class="list-group">
                            {foreach name=paymentmethod from=$Zahlungsarten item=zahlungsart}
                                <li id="{$zahlungsart->cModulId}" class="list-group-item">
                                    <div class="radio">
                                        <label for="payment{$zahlungsart->kZahlungsart}" class="btn-block">
                                            <input name="Zahlungsart" value="{$zahlungsart->kZahlungsart}" type="radio" id="payment{$zahlungsart->kZahlungsart}"{if $Zahlungsarten|@count == 1} checked{/if}{if $smarty.foreach.paymentmethod.first} required{/if}>
                                                {if $zahlungsart->cBild}
                                                    <img src="{$zahlungsart->cBild}" alt="{$zahlungsart->angezeigterName|trans}" class="vmiddle">
                                                {else}
                                                    <strong>{$zahlungsart->angezeigterName|trans}</strong>
                                                {/if}
                                            {if $zahlungsart->fAufpreis != 0}
                                                <span class="badge pull-right">
                                                {if $zahlungsart->cGebuehrname|has_trans}
                                                    <span>{$zahlungsart->cGebuehrname|trans} </span>
                                                {/if}
                                                {$zahlungsart->cPreisLocalized}
                                                </span>
                                            {/if}
                                            {if $zahlungsart->cHinweisText|has_trans}
                                                <p class="small text-muted">{$zahlungsart->cHinweisText|trans}</p>
                                            {/if}
                                        </label>
                                    </div>
                                </li>
                            {/foreach}
                        </ul>
                        
                        <!-- trusted shops? -->
                        
                        <input name="Zahlungsart" value="0" type="radio" id="payment0" class="hidden" checked="checked">
                        <input type="hidden" name="zahlungsartwahl" value="1" />
                    </fieldset>
                    <input id="ppp-submit" type="submit" value="{lang key="continueOrder" section="account data"}" class="btn btn-primary submit btn-lg pull-right" />
                </form>
                {/block}
            {/if}
        </div>
    </div>
</div>

<div class="modal modal-center fade" id="ppp-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <h2 id="pp-loading-body"><i class="fa fa-spinner fa-spin fa-fw"></i> {lang key="redirect"}</h2>
            </div>
        </div>
    </div>
</div>

<script type="application/javascript">
var submit = '#ppp-submit';
var payments = '#zahlung input[name="Zahlungsart"]';
var thirdPartyPayment = false;
var ppActive = function() {ldelim}
    return !parseInt($(payments + ':checked').val())
{rdelim}

var ppConfig = {ldelim}
    approvalUrl: "{$approvalUrl}",
    placeholder: "ppp-container",
    mode: "{$mode}",
{if $mode == 'sandbox'}
    showPuiOnSandbox: true,
{/if}
    buttonLocation: "outside",
    preselection: "paypal",
    disableContinue: function() {ldelim}
        if (ppActive()) {ldelim}
            $(payments + ':first')
                .prop('checked', true);
        {rdelim}
    {rdelim},
    enableContinue: function() {ldelim}
        $('#payment0')
            .prop('checked', true);
    {rdelim},
    showLoadingIndicator: true,
    language: "{$language}",
    country: "{$country}",
    onThirdPartyPaymentMethodSelected: function(data) {ldelim}
        thirdPartyPayment = true;
    {rdelim},
    onThirdPartyPaymentMethodDeselected: function(data) {ldelim}
        thirdPartyPayment = false;
    {rdelim},
    onContinue: function() {ldelim}
        if (thirdPartyPayment) {ldelim}
            PAYPAL.apps.PPP.doCheckout();
        {rdelim} else {ldelim}
            $(submit).attr('disabled', true);
            $.get("index.php", {ldelim} s: "{$linkId}", a: "payment_patch", id: "{$paymentId}" {rdelim})
                .success(function() {ldelim}
                    PAYPAL.apps.PPP.doCheckout();
                {rdelim})
                .fail(function(res) {ldelim}
                    $(submit).attr('disabled', false);
                    $('#ppp-modal')
                        .find('.modal-content')
                        .replaceWith($(res.responseText));
                    $('#ppp-modal').modal('handleUpdate');
                {rdelim});
        {rdelim}
    {rdelim},
    showLoadingIndicator: true,
    {if $styles}
        styles: {$styles|@json_encode},
    {/if}
    {if $thirdPartyPaymentMethods|@count > 0}
        thirdPartyPaymentMethods: {$thirdPartyPaymentMethods|@json_encode}
    {/if}
{rdelim};

try {
    var ppp = PAYPAL.apps.PPP(ppConfig);
} catch (d) { }

$(document).ready(function() {ldelim}
    $(submit).click(function() {ldelim}
        if (!ppActive()) {ldelim}
            return true;
        {rdelim}
        $('#ppp-modal').modal();
        ppp.doContinue();
        return false;
    {rdelim});
    
    $(payments).change(function() {ldelim}
        ppp.deselectPaymentMethod();
    {rdelim});
{rdelim});
</script>
