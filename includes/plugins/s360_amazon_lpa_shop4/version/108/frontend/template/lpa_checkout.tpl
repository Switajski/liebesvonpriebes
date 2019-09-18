{if isset($cError) && $cError}
    <p class="alert alert-warning">{$cError}</p>
{/if}
<div id="amazonpayments" class="lpa-checkout-wrapper">
    {if isset($lpa_not_logged_in) && $lpa_not_logged_in == 1}
        {* Error case - the checkout is rendered without a Kunde being in the session. *}
        <div class="col-xs-12">
            <p class="alert alert-info">{$oPlugin->oPluginSprachvariableAssoc_arr.lpa_not_logged_in}</p>
            <script type="text/javascript">
                jQuery(document).ready(function() {
                    lpa_logout();
                });
            </script>
            <div class="lpa-forced-pay-button"></div>
        </div>
    {else}

        {if !isset($confirmOrder)}
            {*
            Amazon provides two widgets: one for address selection, the other for payment option selection (wallet widget).
            The standard use case is as follows:
            If physical goods are in the cart:
            - Shop presents address widget, buyer selects address.
            - Shop then requests ZIP, city and country of the selected address from API, IFF that destination is supported, else error message.
            - If the shop should exclude Packstation-addresses (Plugin option!), the shop furthermore requests the full delivery address now and checks its concatenation for the string "packstation". If it is contained, error message.
            - Shop then shows all available shipping methods and prices.
            - Buyer selects shipping method, if more than one method is available.
            Shop sends transaction amount to Amazon, Amazon prefilters payment methods.
            Shop shows wallet widget.
            Buyer selects payment method in wallet widget.
            If charge-on-order is active (Plugin option!) the shop shows a *REQUIRED* checkbox and information that the user has to acknowledge.
            Buyer reviews order.
            Buyer places order.
            If synchronous authorization:
            - Shop shows "Waiting"-Icon and immediately tries to authorize the amount.
            - If that fails with a soft decline: buyer is presented with updated wallet widget and READ-ONLY address widget.
            - If that fails with a hard decline: buyer is informed that payment with amazon is not possible here and can then proceed to alternative normal checkout.
            If asynchronous authorization or on success with synchronous authorization:
            - Buyer is presented with success/thank you page.
        
            IFF Sandbox is enabled, the shop shows additional input fields to allow entering simulation strings.
            *}
            <form method="post" action="{if isset($lpa_checkout_url_localized)}{$lpa_checkout_url_localized}{/if}">
                <div class="col-xs-12 col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">{lang key="shippingAdress" section="checkout"}</h3>
                        </div>
                        <div class="panel-body">
                            <div id="addressBookWidgetDiv"></div>
                        </div>
                    </div>
                </div>

                <div class="col-xs-12 col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">{lang key="paymentMethod" section="checkout"}</h3>
                        </div>
                        <div class="panel-body">
                            <div id="walletWidgetDiv"></div>
                        </div>
                    </div>
                </div>

                <div class="col-xs-12">
                    <div class="box_notice alert alert-warning lpa-error-message" style="display:none;" id="lpa-error-packstation">{$oPlugin->oPluginSprachvariableAssoc_arr.lpa_packstation_disallowed}</div>
                    <div class="box_notice alert alert-warning lpa-error-message" style="display:none;" id="lpa-error-address">{$oPlugin->oPluginSprachvariableAssoc_arr.lpa_address_error}</div>
                </div>
                <div class="col-xs-12" id="shippingMethodSelectionDiv"></div>

                {if isset($lpa_charge_on_order) && ($lpa_charge_on_order === 'immediate')}
                    <div class="col-xs-12">
                        <input type="checkbox" name="lpa_charge_on_order_ack" id="lpa_charge_on_order_checkbox" />
                        &nbsp;
                        <label for="lpa_charge_on_order_checkbox">{$oPlugin->oPluginSprachvariableAssoc_arr.lpa_acknowledge_immediate_capture}</label>
                    </div>
                {/if}
                <div class="clear"></div>
                <div class="col-xs-6 pull-left lpa-cancel">
                    <a href="warenkorb.php?{$SID}" onclick="lpa_logout();">{$oPlugin->oPluginSprachvariableAssoc_arr.lpa_cancel_checkout}</a>
                </div>
                <div class="col-xs-6 pull-right lpa-next" id="lpa-checkout-nextstep" style="display:none;">
                    <input type="hidden" name="lpa_step" value="lpaselected" />
                    <input type="hidden" id="lpa-orid-input" name="orid" value="" />
                    <input type="submit" value="{lang key="nextStepCheckout" section="checkout"}" class="btn btn-primary btn-lg submit submit_once" />
                    <p><small>{$oPlugin->oPluginSprachvariableAssoc_arr.lpa_check_order}</small></p>
                </div>
            </form>


            <script type="text/javascript">
                {literal}
                    window.walletInitFunc = function () {
                        new OffAmazonPayments.Widgets.Wallet({
                            sellerId: '{/literal}{if isset($lpa_seller_id)}{$lpa_seller_id}{/if}{literal}',
                            design: {
                                designMode: 'responsive'
                            },
                            onPaymentSelect: function (orderReference) {
                                lpa_updatePaymentSelection();
                            },
                            onError: function (error) {
                                console.log(error.getErrorCode() + ': ' + error.getErrorMessage());
                            }
                        }).bind("walletWidgetDiv");
                    };
                {/literal}
            </script>
            <script type="text/javascript">
                {literal}
                    var addressBookInitFunc = function () {
                        new OffAmazonPayments.Widgets.AddressBook({
                            sellerId: '{/literal}{if isset($lpa_seller_id)}{$lpa_seller_id}{/if}{literal}',
                            onOrderReferenceCreate: function (orderReference) {
                                window.currentOrderReference = orderReference;
                                $('#lpa-orid-input').val(orderReference.getAmazonOrderReferenceId());
                            },
                            onAddressSelect: function (orderReference) {
                                lpa_updateDeliverySelection(window.currentOrderReference);
                            },
                            design: {
                                designMode: 'responsive'
                            },
                            onError: function (error) {
                                console.log(error.getErrorCode() + ': ' + error.getErrorMessage());
                            }
                        }).bind("addressBookWidgetDiv");
                    };

                    /* this array of init functions is saved in case the widgets need to be rerendered on invalid payments */
                    if (typeof window.lpaRerenderWidgets === "undefined") {
                        window.lpaRerenderWidgets = [];
                    }
                    window.lpaRerenderWidgets.push(addressBookInitFunc);

                    if (typeof window.lpaCallbacksExecuted === "undefined" || !window.lpaCallbacksExecuted) {
                        if (typeof window.lpaCallbacks === "undefined") {
                            window.lpaCallbacks = [];
                        }
                        window.lpaCallbacks.push(addressBookInitFunc);
                    } else {
                        addressBookInitFunc();
                    }
                {/literal}
            </script>
        {else}
            {* Confirmation of the order *}
            <div class="box_error alert alert-danger" id="lpa-confirm-message" style="display:none;"></div>
            {if isset($lpa_currency_hint)}
                <div class="box_info alert alert-info">{$lpa_currency_hint}</div>
            {/if}
            <form method="post" action="" id="lpa-confirm-order-form">
                <div class="col-xs-12 col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">{lang key="shippingAdress" section="checkout"}</h3>
                        </div>
                        <div class="panel-body">
                            <div id="readOnlyAddressBookWidgetDiv"></div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">{lang key="paymentMethod" section="checkout"}</h3>
                        </div>
                        <div class="panel-body">
                            <div id="readOnlyWalletWidgetDiv"></div>
                            <div id="editWalletWidgetDiv" style="display:none;"></div>
                        </div>
                    </div>
                </div>
                <script type="text/javascript">
                    {literal}
                        var addressBookInitFunc = function () {
                            new OffAmazonPayments.Widgets.AddressBook({
                                sellerId: '{/literal}{if isset($lpa_seller_id)}{$lpa_seller_id}{/if}{literal}',
                                amazonOrderReferenceId: '{/literal}{if isset($lpa_orid)}{$lpa_orid}{/if}{literal}',
                                displayMode: "Read",
                                design: {
                                    designMode: 'responsive'
                                },
                                onError: function (error) {
                                    console.log(error);
                                }
                            }).bind("readOnlyAddressBookWidgetDiv");
                        };

                        /* this array of init functions is saved in case the widgets need to be rerendered on invalid payments */
                        if (typeof window.lpaRerenderWidgets === "undefined") {
                            window.lpaRerenderWidgets = [];
                        }
                        window.lpaRerenderWidgets.push(addressBookInitFunc);

                        if (typeof window.lpaCallbacksExecuted === "undefined" || !window.lpaCallbacksExecuted) {
                            if (typeof window.lpaCallbacks === "undefined") {
                                window.lpaCallbacks = [];
                            }
                            window.lpaCallbacks.push(addressBookInitFunc);
                        } else {
                            addressBookInitFunc();
                        }
                    {/literal}
                </script>
                <script type="text/javascript">
                    {literal}
                        var walletInitFunc = function () {
                            new OffAmazonPayments.Widgets.Wallet({
                                sellerId: '{/literal}{if isset($lpa_seller_id)}{$lpa_seller_id}{/if}{literal}',
                                amazonOrderReferenceId: '{/literal}{if isset($lpa_orid)}{$lpa_orid}{/if}{literal}',
                                displayMode: "Read",
                                design: {
                                    designMode: 'responsive'
                                },
                                onError: function (error) {
                                    console.log(error);
                                }
                            }).bind("readOnlyWalletWidgetDiv");

                            new OffAmazonPayments.Widgets.Wallet({
                                sellerId: '{/literal}{if isset($lpa_seller_id)}{$lpa_seller_id}{/if}{literal}',
                                amazonOrderReferenceId: '{/literal}{if isset($lpa_orid)}{$lpa_orid}{/if}{literal}',
                                design: {
                                    designMode: 'responsive'
                                },
                                onPaymentSelect: function (orderReference) {
                                    lpa_updatePaymentSelection();
                                },
                                onError: function (error) {
                                    console.log(error);
                                }
                            }).bind("editWalletWidgetDiv");
                        };

                        /* this array of init functions is saved in case the widgets need to be rerendered on invalid payments */
                        if (typeof window.lpaRerenderWidgets === "undefined") {
                            window.lpaRerenderWidgets = [];
                        }
                        window.lpaRerenderWidgets.push(walletInitFunc);

                        if (typeof window.lpaCallbacksExecuted === "undefined" || !window.lpaCallbacksExecuted) {
                            if (typeof window.lpaCallbacks === "undefined") {
                                window.lpaCallbacks = [];
                            }
                            window.lpaCallbacks.push(walletInitFunc);
                        } else {
                            walletInitFunc();
                        }
                    {/literal}
                </script>
                <div class="col-xs-12 col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">{lang key="shippingOptions" section="global"}</h3>
                        </div>
                        <div class="panel-body">
                            {$smarty.session.Versandart->angezeigterName[$smarty.session.cISOSprache]}
                            <p><a href="{if isset($lpa_checkout_url_localized)}{$lpa_checkout_url_localized}{/if}?lpa_step=edit" class="button_edit">{lang key="modifyShippingOption" section="checkout"}</a></p>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">{lang key="paymentOptions" section="global"}</h3>
                        </div>
                        <div class="panel-body">
                            {$oPlugin->oPluginSprachvariableAssoc_arr.lpa_amazon_pay_name}
                            <p><a href="{if isset($lpa_checkout_url_localized)}{$lpa_checkout_url_localized}{/if}?lpa_step=edit" class="button_edit">{lang key="modifyPaymentOption" section="checkout"}</a></p>
                        </div>
                    </div>
                </div>


                <div class="col-xs-12">
                    <label for="comment">
                        {lang assign="orderCommentsTitle" key="orderComments" section="shipping payment"}
                        {$orderCommentsTitle}
                    </label>

                </div>
                <div class="col-xs-12">
                    <textarea class="form-control" title="{$orderCommentsTitle|escape:"html"}" name="kommentar" rows="3" id="comment">{if isset($smarty.session.kommentar)}{$smarty.session.kommentar}{/if}</textarea>
                </div>

                {if $lpaEinstellungen.kaufabwicklung.bestellvorgang_wrb_anzeigen==1}
                    <div class="col-xs-12" style="height:20px;"></div>
                    <div class="col-xs-12">
                        {lang key="cancellationPolicyNotice" section="checkout" assign="cancellationPolicyNotice"}
                        {lang key="wrb" section="checkout" assign="wrb"}
                        {if isset($AGB->kLinkWRB) && $AGB->kLinkWRB > 0}
                            <div class="box_info alert alert-info">{$cancellationPolicyNotice|replace:"#LINK_WRB#":"<a href='navi.php?s=`$AGB->kLinkWRB`' target='_blank'>`$wrb`</a>"}</div>
                        {else}
                            {if $AGB->cWRBContentHtml}
                                <div id="popupwrb" class="hidden tleft"><h1>{lang key="wrb" section="checkout"}</h1>{$AGB->cWRBContentHtml}</div>
                                <div class="box_info alert alert-info">{$cancellationPolicyNotice|replace:"#LINK_WRB#":"<a href='#' class='popup' id='wrb'>`$wrb`</a>"}</div>
                            {elseif $AGB->cWRBContentText}
                                <div id="popupwrb" class="hidden tleft"><h1>{lang key="wrb" section="checkout"}</h1>{$AGB->cWRBContentText|nl2br}</div>
                                <div class="box_info alert alert-info">{$cancellationPolicyNotice|replace:"#LINK_WRB#":"<a href='#' class='popup' id='wrb'>`$wrb`</a>"}</div>
                            {/if}
                        {/if}

                    </div>
                {/if}

                {if !isset($smarty.session.cPlausi_arr)}
                    {assign var=plausiArr value=array()}
                {else}
                    {assign var=plausiArr value=$smarty.session.cPlausi_arr}
                {/if}
                {hasCheckBoxForLocation bReturn="bCheckBox" nAnzeigeOrt=2 cPlausi_arr=$plausiArr cPost_arr=$cPost_arr}
                {if $bCheckBox}
                    <div class="col-xs-12">
                        {if $lpa_shop3_compatibility === "1"}
                            <ul style="list-style-type: none;">
                                {getCheckBoxForLocation nAnzeigeOrt=2 cPlausi_arr=$plausiArr cPost_arr=$cPost_arr}
                            </ul>
                        {else}
                            <hr>
                            {include file='snippets/checkbox.tpl' nAnzeigeOrt=2 cPlausi_arr=$plausiArr cPost_arr=$cPost_arr}
                            <hr>
                        {/if}
                    </div>
                {/if}

                {if $lpa_sandbox_mode == true}
                    <div class="col-xs-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Simulator-String f&uuml;r Authorization (nur im Sandbox-Modus sichtbar):</h3>
                            </div>
                            <div class="panel-body">
                                <select class="form-control" name="sandbox_auth">
                                    <option value="" selected>(empty)</option>
                                    <option value='{literal}{"SandboxSimulation": {"State":"Declined", "ReasonCode":"InvalidPaymentMethod", "PaymentMethodUpdateTimeInMins":5}}{/literal}'>Declined: InvalidPaymentMethod</option>
                                    <option value='{literal}{"SandboxSimulation": {"State":"Declined", "ReasonCode":"AmazonRejected"}}{/literal}'>Declined: AmazonRejected</option>
                                    <option value='{literal}{"SandboxSimulation": {"State":"Declined", "ReasonCode":"TransactionTimedOut"}}{/literal}'>Declined: TransactionTimedOut</option>
                                    <option value='{literal}{"SandboxSimulation": {"State":"Closed", "ReasonCode":"ExpiredUnused", "ExpirationTimeInMins":1}}{/literal}'>Closed: ExpiredUnused (nach 1 Minute)</option>
                                    <option value='{literal}{"SandboxSimulation": {"State":"Closed", "ReasonCode":"AmazonClosed"}}{/literal}'>Closed: AmazonClosed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                {/if}
                <div class="col-xs-12">

                    <div class="panel panel-primary" id="panel-submit-order">
                        <div class="panel-body">
                            <input type="hidden" name="abschluss" value="1" />
                            <input type="hidden" id="comment-hidden" name="kommentar" value="" />
                            {include file="checkout/inc_order_items.tpl" tplscope="confirmation"  Einstellungen=$lpaEinstellungen}
                            <div class="table left shippingTime">
                                <strong>{lang key="shippingTime" section="global"}</strong>: {$smarty.session.Warenkorb->cEstimatedDelivery}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clear"></div>
                <div class="col-xs-6 pull-left">
                    <a href="warenkorb.php?{$SID}" onclick="lpa_logout();">{$oPlugin->oPluginSprachvariableAssoc_arr.lpa_cancel_checkout}</a>
                </div>
                <div class="col-xs-6 lpa-next pull-right">
                    <input type="hidden" name="finish" value="1" />
                    <input type="hidden" name="orid" value="{$lpa_orid}" />
                    <input type="submit" value="{lang key="orderLiableToPay" section="checkout"}" id="complete-order-button" class="btn btn-primary btn-lg submit" />
                </div>
            </form>
        {/if}
    {/if}
</div>