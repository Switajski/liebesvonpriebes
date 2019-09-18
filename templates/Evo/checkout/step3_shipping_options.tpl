{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}
{if !empty($hinweis)}
    <div class="alert alert-danger">
        {$hinweis}
    </div>
{/if}
{if !empty($cFehler)}
    <div class="alert alert-danger">{$cFehler}</div>
{/if}
<div class="row">
    <div class="col-xs-12">
        {if !isset($Versandarten)}
            <div class="alert alert-danger">{lang key="noShippingMethodsAvailable" section="checkout"}</div>
        {else}
            <form method="post" action="{get_static_route id='bestellvorgang.php'}" class="form evo-validate">
                {$jtl_token}
                <div class="panel-wrap">
                    <fieldset id="checkout-shipping-payment">
                        <legend>{lang section='global' key='shippingOptions'}</legend>
                        <div class="row bottom15 form-group">
                            {foreach name=shipment from=$Versandarten item=versandart}
                            <div id="shipment_{$versandart->kVersandart}" class="col-xs-12">
                                <div class="radio">
                                    <label for="del{$versandart->kVersandart}" class="btn-block">
                                        <input name="Versandart" value="{$versandart->kVersandart}" type="radio" class="radio-checkbox" id="del{$versandart->kVersandart}"{if $Versandarten|@count == 1 || $AktiveVersandart == $versandart->kVersandart} checked{/if}{if $smarty.foreach.shipment.first} required{/if}>
                                        <span class="control-label label-default">
                                            <span class="content">
                                                <span class="title">{$versandart->angezeigterName|trans}</span>
                                                <small class="desc text-info">{$versandart->cLieferdauer|trans}</small>
                                            </span>
                                            {if $versandart->cBild}
                                                <img class="img-responsive-width img-sm" src="{$versandart->cBild}" alt="{$versandart->angezeigterName|trans}">
                                            {/if}
                                            <span class="content text-muted">
                                                {$versandart->angezeigterHinweistext|trans}
                                            </span>
                                            <span class="badge pull-right">{$versandart->cPreisLocalized}</span>
                                            {if isset($versandart->specificShippingcosts_arr)}
                                                {foreach name=specificShippingcosts from=$versandart->specificShippingcosts_arr item=specificShippingcosts}
                                                    <div class="row">
                                                        <div class="col-xs-8 col-md-9 col-lg-9">
                                                            <ul>
                                                                <li>
                                                                    <small>{$specificShippingcosts->cName|trans}</small>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <div class="col-xs-4 col-md-3 col-lg-3 text-right">
                                                            <small>
                                                                {$specificShippingcosts->cPreisLocalized}
                                                            </small>
                                                        </div>
                                                    </div>
                                                {/foreach}
                                            {/if}
                                            {if !empty($versandart->Zuschlag->fZuschlag)}
                                            <span class="btn-block">
                                                <small>{$versandart->Zuschlag->angezeigterName|trans}
                                                    (+{$versandart->Zuschlag->cPreisLocalized})
                                                </small>
                                            </span>
                                            {/if}
                                            {if !empty($versandart->cLieferdauer|trans) && $Einstellungen.global.global_versandermittlung_lieferdauer_anzeigen === 'Y'}
                                            <span class="btn-block">
                                                <small>{lang key="shippingTimeLP" section="global"}
                                                    : {$versandart->cLieferdauer|trans}</small>
                                            </span>
                                            {/if}
                                        </span>
                                    </label>
                                </div>
                            </div>
                            {/foreach}
                        </div>
                    </fieldset>
                </div>
                <div class="panel-wrap">
                    <fieldset>
                        {if isset($Verpackungsarten) && $Verpackungsarten|@count > 0}
                        <legend>{lang section='checkout' key='additionalPackaging'}</legend>
                        <div class="row bottom15 form-group">
                            {foreach name=zusatzverpackungen from=$Verpackungsarten item=oVerpackung}
                            <div id="packaging_{$oVerpackung->kVerpackung}" class="col-xs-12">
                                <div class="checkbox">
                                    <label for="pac{$oVerpackung->kVerpackung}" class="btn-block">
                                        <input name="kVerpackung[]" type="checkbox" class="radio-checkbox" value="{$oVerpackung->kVerpackung}" id="pac{$oVerpackung->kVerpackung}" {if isset($oVerpackung->bWarenkorbAktiv) && $oVerpackung->bWarenkorbAktiv === true || (isset($AktiveVerpackung[$oVerpackung->kVerpackung]) && $AktiveVerpackung[$oVerpackung->kVerpackung] === 1)}checked{/if}/>
                                        <span class="control-label label-default">
                                            <span class="content">
                                                <span class="title">{$oVerpackung->cName}</span>
                                            </span>
                                            <span class="badge pull-right">
                                                {if $oVerpackung->nKostenfrei == 1}{lang key="ExemptFromCharges" section="global"}{else}{$oVerpackung->fBruttoLocalized}{/if}
                                            </span>
                                            <span class="btn-block">
                                                <small>{$oVerpackung->cBeschreibung}</small>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            {/foreach}
                        </div>
                        {/if}
                    </fieldset>
                </div>
                <div class="panel-wrap">
                    <fieldset id="fieldset-payment">
                        <legend>{lang section='global' key='paymentOptions'}</legend>
                        {$step4_payment_content}
                    </fieldset>
                </div>
                {if isset($Versandarten)}
                <div class="text-right">
                    <input type="hidden" name="versandartwahl" value="1" />
                    <input type="hidden" name="zahlungsartwahl" value="1" />
                    <input type="submit" value="{lang key="continueOrder" section="account data"}" class="submit btn btn-lg submit-once btn-primary hidden" />
                </div>
                {/if}
            </form>
        {/if}
    </div>
</div>
{if isset($smarty.get.editZahlungsart)}
{literal}
    <script type="text/javascript">
        $(document).ready(function () {
            $.evo.extended().smoothScrollToAnchor('#fieldset-payment');
        });
    </script>
{/literal}
{/if}