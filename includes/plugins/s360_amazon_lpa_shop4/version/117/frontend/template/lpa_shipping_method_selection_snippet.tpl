{* Selection of the shipping method, according to selected payment and delivery address *}
<div class="form panel panel-default lpa-shipping-method-selection-snippet" id="shippingmethodform">
    <div class="panel-heading">
        <h3 class="panel-title">{lang key="shippingOptions" section="global"} </h3>
    </div>
    <div class="panel-body">
        {if count($oVersandart_arr) < 1}
            <div class="alert alert-danger">{lang key="noShippingMethodsAvailable" section="checkout"}</div>
        {/if}
        <ul class="list-group">
            {foreach name=versand from=$oVersandart_arr item=versandart}
                <li id="shipment_{$versandart->kVersandart}" class="list-group-item">
                    <div class="radio">
                        <label for="del{$versandart->kVersandart}" class="btn-block">
                            <input name="Versandart" value="{$versandart->kVersandart}" type="radio" id="del{$versandart->kVersandart}"{if $smarty.foreach.versand.first} checked="checked"{/if}>
                            &nbsp;{if $versandart->cBild}
                            <img src="{$versandart->cBild}" alt="{$versandart->angezeigterName[$smarty.session.cISOSprache]}">
                            {else}
                                <strong>{$versandart->angezeigterName[$smarty.session.cISOSprache]}</strong>
                                {/if}
                                    <span class="badge pull-right">{$versandart->cPreisLocalized}</span>{if isset($versandart->angezeigterHinweistext[$smarty.session.cISOSprache]) && !empty($versandart->angezeigterHinweistext[$smarty.session.cISOSprache])}
                                    <p>
                                        <small>{$versandart->angezeigterHinweistext[$smarty.session.cISOSprache]}</small>
                                    </p>
                                    {/if}

                                        {if isset($versandart->specificShippingcosts_arr)}
                                            {foreach name=specificShippingcosts from=$versandart->specificShippingcosts_arr item=specificShippingcosts}
                                                <div class="row">
                                                    <div class="col-xs-8 col-md-9 col-lg-9">
                                                        <ul>
                                                            <li>
                                                                {if $lpa_shop_version > 402}
                                                                    <small>{$specificShippingcosts->cName|trans}</small>
                                                                {else}
                                                                    {* The EVO template from Shop Version 4.02 does not know |trans *}
                                                                    <small>{$specificShippingcosts->cName}</small>
                                                                {/if}
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
                                            <p>
                                                <small>{$versandart->Zuschlag->angezeigterName[$smarty.session.cISOSprache]}
                                                    (+{$versandart->Zuschlag->cPreisLocalized})
                                                </small>
                                            </p>
                                        {/if}

                                        {if isset($versandart->cLieferdauer[$smarty.session.cISOSprache]) && !empty($versandart->cLieferdauer[$smarty.session.cISOSprache]) && $Einstellungen.global.global_versandermittlung_lieferdauer_anzeigen === 'Y'}
                                            <p>
                                                <small>{lang key="shippingTimeLP" section="global"}
                                                    : {$versandart->cLieferdauer[$smarty.session.cISOSprache]}</small>
                                            </p>
                                        {/if}
                                    </label>
                                </div>
                            </li>

                            {/foreach}
                            </ul>
                        </div>
                    </div>
                    {literal}
                        <script type="text/javascript">
                            function lpa_shippingMethodSelected() {
                                var selectedShippingMethod = $('#shippingmethodform input[name="Versandart"]:checked').val();
                                lpa_updateSelectedShippingMethod(selectedShippingMethod, window.currentOrderReference);
                            }

                            if ($('#shippingmethodform input[name="Versandart"]:checked').length) {
                                lpa_shippingMethodSelected();
                            }

                            $('#shippingmethodform input[type=radio]').click(function () {
                                lpa_shippingMethodSelected();
                            });
                        </script>
                    {/literal}