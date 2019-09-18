{if !empty($hinweis)}
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
                {if not empty($ts_tpl)}
                    {include file=$ts_tpl}
                {/if}
                <input id="ppp-submit" type="submit" value="{lang key="continueOrder" section="account data"}" class="btn btn-primary submit btn-lg pull-right" />
            {else}
                {block name="checkout-payment-options-body"}
                <form id="zahlung" method="post" action="bestellvorgang.php" class="form">
                    {if isset($jtl_paypal_token)}
                        {$jtl_paypal_token}
                    {/if}
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

                        {if not empty($ts_tpl)}
                            {include file=$ts_tpl}
                        {/if}

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

<script type="text/javascript">
var submit   = '#ppp-submit';
var payments = 'input[name="Zahlungsart"]';

var tsProduct = null;
{if isset($oTrustedShops) && isset($oTrustedShops->oKaeuferschutzProdukte)}
    tsProduct = '{$oTrustedShops->oKaeuferschutzProdukte->item[0]->tsProductID}';
{/if}

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
    onContinue: function() {ldelim}
        if (ppIsThirdParty()) {ldelim}
            if (paymentMethod = ppGetThirdPartyMethod(ppp.getPaymentMethod())) {ldelim}
                url = paymentMethod.redirectUrl;
                if (ts = ppGetTrustedShops()) {ldelim}
                    url += '&ts=' + ts;
                {rdelim}
                window.location.href = url;
                return;
            {rdelim} else {ldelim}
                PAYPAL.apps.PPP.doCheckout();
            {rdelim}
        {rdelim} else {ldelim}
            $('#ppp-modal').modal();
            $(submit).attr('disabled', true);

            var params = {ldelim} s: "{$linkId}", a: "payment_patch", id: "{$paymentId}" {rdelim};

            if (ts = ppGetTrustedShops()) {ldelim}
                params.ts = ts;
            {rdelim}

            $.get("index.php", params)
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
    {if $styles}
        styles: {$styles|@json_encode},
    {/if}
    {if $thirdPartyPaymentMethods|@count > 0}
        thirdPartyPaymentMethods: {$thirdPartyPaymentMethods|@json_encode}
    {/if}
{rdelim};

var ppIsThirdParty = function() {ldelim}
    var method = ppp.getPaymentMethod();
    return (method.substr(0, 3) !== 'pp-');
{rdelim};

var ppGetTrustedShops = function() {ldelim}
    if ($('input[name="bTS"]:checked').length > 0) {ldelim}
        return $('[name="cKaeuferschutzProdukt"]').val()
            ? $('[name="cKaeuferschutzProdukt"]').val()
            : tsProduct;
        {rdelim}
    return null;
{rdelim};

var ppGetThirdPartyMethod = function(name) {ldelim}
    for (var p in ppConfig.thirdPartyPaymentMethods) {ldelim}
        var payment = ppConfig.thirdPartyPaymentMethods[p];
        if (payment.methodName === name)
            return payment;
        {rdelim}
    return null;
{rdelim};

var ppActive = function() {ldelim}
    return !parseInt($(payments + ':checked').val())
{rdelim};

var ppp = null;

$(document).ready(function() {ldelim}
    paypal().loadPaymentWall(function() {ldelim}
        try {ldelim}
            ppp = PAYPAL.apps.PPP(ppConfig);

            $(submit).click(function() {ldelim}
                if (!ppActive()) {ldelim}
                    return true;
                }
                ppp.doContinue();
                return false;
            {rdelim});

            $(payments).change(function() {ldelim}
                ppp.deselectPaymentMethod();
            {rdelim});
        {rdelim}
        catch (e) {ldelim}
            if (console) {ldelim}
                console.error(e.message);
            {rdelim}
        {rdelim}
    {rdelim});
{rdelim});
</script>
