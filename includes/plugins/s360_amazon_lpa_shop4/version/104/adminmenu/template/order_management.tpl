<script type="text/javascript" src="{$oPlugin->cAdminmenuPfadURL}js/admin-order-management.js"></script>
<link type="text/css" href="{$oPlugin->cAdminmenuPfadURL}css/admin.css" rel="stylesheet" media="screen">
<div id="orders">
    {if isset($lpa_error_message)}<p class="alert alert-danger"><b>FEHLER:&nbsp;</b>{$lpa_error_message}</p>{/if}
    {if isset($lpa_status_message)}<p class="alert alert-info"><b>INFO:&nbsp;</b>{$lpa_status_message}</p>{/if}
    {assign var=orderErrorLimit value=604800}{* Critical warn when order expires in less than a week *}
    {assign var=orderWarnLimit value=2592000}{* Warn when order expires in less than a month (30 days) *}
    {assign var=authErrorLimit value=2073600}{* Critical warn when order expires in less than 24 days (i.e. it has 1 day for guaranteed capture left) *}
    {assign var=authWarnLimit value=2419200}{* Warn when order expires in less than 4 weeks (28 days) *}
    <div class="lpa-admin-heading">Bestellungen</div>
    {if $lpa_management.orders && count($lpa_management.orders) > 0}
        <div id="lpa-order-window">
            <table id="lpa-order-table" class="table">
                <tr>
                    <th>Nummer (Shop)</th>
                    <th>Status (Shop)</th>
                    <th>Order-Referenz (Amazon)</th>
                    <th>Order-Status (Amazon)</th>
                    <th>Betrag</th>
                    <th>Sandbox</th>
                    <th>Ablaufdatum</th>
                    <th>Aktion</th>
                </tr>
                {foreach item=order from=$lpa_management.orders}
                    <tr class="lpa-admin-order-entry" data-orderid="{$order->cOrderReferenceId}">
                        <td>{if isset($order->bestellung)}{$order->bestellung->cBestellNr}{else}unbekannt{/if}</td>
                        <td>{if isset($order->bestellung)}{if $order->bestellung->cStatus === '-1'}Storno{elseif $order->bestellung->cStatus === '1'}Offen{elseif $order->bestellung->cStatus === '2'}In Bearbeitung{elseif $order->bestellung->cStatus === '3'}Bezahlt{elseif $order->bestellung->cStatus === '4'}Versandt{elseif $order->bestellung->cStatus === '5'}Teilversandt{else}Unbekannt ({$order->bestellung->cStatus}){/if}{else}unbekannt{/if} </td>
                        <td>{$order->cOrderReferenceId}</td>
                        <td class="{$order->displayState}">{$order->cOrderStatus}{if isset($order->cOrderStatusReason) && $order->cOrderStatusReason|count_characters > 0} ({$order->cOrderStatusReason}){/if}</td>
                        <td>{$order->fOrderAmount} {$order->cOrderCurrencyCode}</td>
                        <td>{if $order->bSandbox == 1}Ja{elseif $order->bSandbox == 0}Nein{else}Error{/if}</td>
                        <td {if $order->displayState !== 'success'}class="{if $order->expiresIn < $orderErrorLimit}danger{elseif $order->expiresIn < $orderWarnLimit}warning{/if}"{/if}>{$order->expiresOnString}</td>
                        <td>
                            {if in_array('authorize', $order->actions)}
                                <div class="input-group">
                                    <input type="text" class="form-control input-sm" name="amount" value="{$order->fOrderAmount}"/>
                                    <span class="input-group-btn">
                                        <button class="lpa-admin-order-authorize btn btn-sm btn-danger">Autorisieren</button>
                                    </span>
                                </div>
                            {/if}
                            <button class="lpa-admin-order-refresh btn btn-xs btn-primary">Refresh</button>
                            {if in_array('cancel', $order->actions)}<button class="lpa-admin-order-cancel btn btn-xs btn-danger">Abbrechen</button>{/if}
                            {if in_array('close', $order->actions)}<button class="lpa-admin-order-close btn btn-xs btn-default">Schlie&szlig;en</button>{/if}
                        </td>
                    </tr>
                {/foreach}
            </table>
        </div>
    {else}
        <div id="lpa-order-table-hint" class="alert alert-info">Es sind noch keine Amazon Payment Bestellungen vorhanden.</div>
    {/if}
    <br />
    <div class="lpa-admin-heading">Autorisierungen</div>
    {if $lpa_management.authorizations && count($lpa_management.authorizations) > 0}
        <div id="lpa-auth-table-hint" class="alert alert-info">Bitte w&auml;hlen Sie eine Bestellung aus.</div>
        <table id="lpa-auth-table" style="display:none;" class="table">
            <tr>
                <th>ID (Amazon)</th>
                <th>Status (Amazon)</th>
                <th>Betrag (autorisiert)</th>
                <th>Betrag (eingezogen)</th>
                <th>Sandbox</th>
                <th>Ablaufdatum</th>
                <th>Aktion</th>
            </tr>
            {foreach item=auth from=$lpa_management.authorizations}
                <tr class="lpa-admin-auth-entry" data-authid="{$auth->cAuthorizationId}" data-orderid="{$auth->cOrderReferenceId}" style="display:none;">
                    <td>{$auth->cAuthorizationId}</td>
                    <td class="{$auth->displayState}">{$auth->cAuthorizationStatus}{if isset($auth->cAuthorizationStatusReason) && $auth->cAuthorizationStatusReason|count_characters > 0} ({$auth->cAuthorizationStatusReason}){/if}</td>
                    <td>{$auth->fAuthorizationAmount} {$auth->cAuthorizationCurrencyCode}</td>
                    <td>{$auth->fCapturedAmount} {$auth->cCapturedCurrencyCode}</td>
                    <td>{if $auth->bSandbox == 1}Ja{elseif $auth->bSandbox == 0}Nein{else}Error{/if}</td>
                    <td {if $auth->displayState !== 'success'}class="{if $auth->expiresIn < $authErrorLimit}danger{elseif $auth->expiresIn < $authWarnLimit}warning{/if}"{/if}>{$auth->expiresOnString}</td>
                    <td>
                        {if in_array('capture', $auth->actions)}
                            <div class="input-group">
                                <input type="text" name="amount" class="form-control input-sm" value="{$auth->fAuthorizationAmount}"/>
                                <span class="input-group-btn">
                                    <button class="lpa-admin-auth-capture btn btn-sm btn-danger">Einziehen</button>
                                </span>
                            </div>
                        {/if}
                        {if in_array('close', $auth->actions)}<button class="lpa-admin-auth-close btn btn-xs btn-default">Schlie&szlig;en</button>{/if}
                    </td>
                </tr>
            {/foreach}
        </table>
    {else}
        <div id="lpa-auth-table-hint" class="alert alert-info">Es sind keine Autorisierungen vorhanden.</div>
    {/if}
    <br />
    <div class="lpa-admin-heading">Zahlungseinz&uuml;ge</div>
    {if $lpa_management.captures && count($lpa_management.captures) > 0}
        <div id="lpa-cap-table-hint" class="alert alert-info">Bitte w&auml;hlen Sie eine Autorisierung aus.</div>
        <table id="lpa-cap-table" style="display: none;" class="table">
            <tr>
                <th>ID (Amazon)</th>
                <th>Status (Amazon)</th>
                <th>Betrag (eingezogen)</th>
                <th>Betrag (erstattet)</th>
                <th>Sandbox</th>
                <th>Aktion</th>
            </tr>
            {foreach item=cap from=$lpa_management.captures}
                <tr class="lpa-admin-cap-entry" data-capid="{$cap->cCaptureId}" data-authid="{$cap->cAuthorizationId}" style="display:none;">
                    <td>{$cap->cCaptureId}</td>
                    <td class="{$cap->displayState}">{$cap->cCaptureStatus}{if isset($cap->cCaptureStatusReason) && $cap->cCaptureStatusReason|count_characters > 0} ({$cap->cCaptureStatusReason}){/if}</td>
                    <td>{$cap->fCaptureAmount} {$cap->cCaptureCurrencyCode}</td>
                    <td>{$cap->fRefundedAmount} {$cap->cRefundedCurrencyCode}</td>
                    <td>{if $cap->bSandbox == 1}Ja{elseif $cap->bSandbox == 0}Nein{else}Error{/if}</td>
                    <td>
                        {if in_array('refund', $cap->actions)}
                            <div class="input-group">
                                <input type="text" name="amount" class="form-control input-sm" value="{$cap->fCaptureAmount}"/>
                                <span class="input-group-btn">
                                    <button class="lpa-admin-cap-refund btn btn-sm btn-danger">Erstatten</button>
                                </span>
                            </div>
                        {/if}
                    </td>
                </tr>
            {/foreach}
        </table>
    {else}
        <div id="lpa-cap-table-hint" class="alert alert-info">Es sind keine Zahlungseinz&uuml;ge vorhanden.</div>
    {/if}
    <br />
    <div class="lpa-admin-heading">R&uuml;ckerstattungen</div>
    {if $lpa_management.refunds && count($lpa_management.refunds) > 0}
        <div id="lpa-refund-table-hint" class="alert alert-info">Bitte w&auml;hlen Sie einen Zahlungseinzug aus.</div>
        <table id="lpa-refund-table" style="display:none;" class="table">
            <tr>
                <th>ID (Amazon)</th>
                <th>Status (Amazon)</th>
                <th>Betrag (erstattet)</th>
                <th>Typ</th>
                <th>Sandbox</th>
            </tr>
            {foreach item=refund from=$lpa_management.refunds}
                <tr class="lpa-admin-refund-entry" data-refundid="{$refund->cRefundId}" data-capid="{$refund->cCaptureId}" style="display:none;">
                    <td>{$refund->cRefundId}</td>
                    <td class="{$refund->displayState}">{$refund->cRefundStatus}{if isset($refund->cRefundStatusReason) && $refund->cRefundStatusReason|count_characters > 0} ({$refund->cRefundStatusReason}){/if}</td>
                    <td>{$refund->fRefundAmount} {$refund->cRefundCurrencyCode}</td>
                    <td>{$refund->cRefundType}</td>
                    <td>{if $refund->bSandbox == 1}Ja{elseif $refund->bSandbox == 0}Nein{else}Error{/if}</td>
                </tr>
            {/foreach}
        </table>
    {else}
        <div id="lpa-refund-table-hint" class="alert alert-info">Es sind keine R&uuml;ckerstattungen vorhanden.</div>
    {/if}
</div>
<form style="display:none" method="post" action="{$pluginAdminUrl}cPluginTab=Bestellungen" id="lpa-order-management-form">
    {$s360_jtl_token}
    <input type="hidden" name="lpa_type" value="" />
    <input type="hidden" name="lpa_action" value="" />
    <input type="hidden" name="lpa_orid" value="" />
    <input type="hidden" name="lpa_id" value="" />
    <input type="hidden" name="lpa_amount" value="" />
    <input type="hidden" name="lpa_management" value="1" />
</form>

