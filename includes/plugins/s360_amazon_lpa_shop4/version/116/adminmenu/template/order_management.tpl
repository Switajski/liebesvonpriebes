<!-- jQuery Confirm -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.js"></script>
<script type="text/javascript" src="{$oPlugin->cAdminmenuPfadURL}js/admin-order-management.js"></script>
<link type="text/css" href="{$oPlugin->cAdminmenuPfadURL}css/admin.css" rel="stylesheet" media="screen">
{if count($lpa_warnings) > 0}
    <div class="row">
        <div class="col-xs-12">
            <div class="lpa-admin-heading">Hinweise / Warnungen</div>
            {foreach from=$lpa_warnings item=$warning}
                <div class="alert alert-warning">{$warning}</div>
            {/foreach}
        </div>
    </div>
{/if}
<div class="lpa-admin-navsearch col-xs-12">
    {if isset($lpa_pagination)}
        <form style="display:none" method="post" action="{$pluginAdminUrl}cPluginTab=Bestellungen" id="lpa-admin-pagination-form">
            {$s360_jtl_token}
            <input type="hidden" name="page" value=""/>
            <input type="hidden" name="lpa_pagination" value="1"/>
        </form>
        <div class="btn-group lpa-admin-pagination col-xs-12 col-md-3">
            <button class="btn btn-sm btn-default lpa-admin-pagination-first" title="Zur ersten Seite" data-page="0"><i class="fa fa-fast-backward" aria-hidden="true"></i></button>
            <button class="btn btn-sm btn-default lpa-admin-pagination-prev" title="Vorherige Seite" data-page="{$lpa_pagination.prevPage}"><i class="fa fa-backward" aria-hidden="true"></i></button>
            <button class="btn btn-sm btn-default disabled lpa-admin-pagination-current" title="aktuelle Seite">{$lpa_pagination.currentPage}</button>
            <button class="btn btn-sm btn-default lpa-admin-pagination-next" title="Nächste Seite" data-page="{$lpa_pagination.nextPage}"><i class="fa fa-forward" aria-hidden="true"></i></button>
        </div>
    {/if}
    {if isset($lpa_search)}
        <div class="lpa-admin-search-wrapper col-xs-12 col-md-9">
            <form method="post" action="{$pluginAdminUrl}cPluginTab=Bestellungen" id="lpa-admin-search-form">
                {$s360_jtl_token}
                <input type="hidden" name="search" value=""/>
                <input type="hidden" name="lpa_search" value="1"/>

                <div class="form-group col-xs-12 lpa-admin-search">
                    <div class="col-xs-6">
                        <input type="text" name="search" class="form-control" placeholder="Bestellnummer oder Order-Referenz" value="{if isset($lpa_search.lastsearch)}{$lpa_search.lastsearch}{/if}"/>
                    </div>
                    <div class="btn-group col-xs-6">
                        <button id="search-by-number" type="submit" class="btn btn-success"><i class="fa fa-search"></i>&nbsp;Suchen</button>
                        <button class="btn btn-danger" onclick="window.location.href='{$pluginAdminUrl}cPluginTab=Bestellungen';return false;"><i class="fa fa-times"></i>&nbsp;Zurücksetzen</button>
                    </div>
                </div>
                {if isset($lpa_search.message)}
                    <div class="col-xs-12 alert alert-warning">
                        {$lpa_search.message}
                    </div>
                {/if}
            </form>
        </div>
    {/if}
</div>
<div id="orders">
    {if isset($lpa_error_message)}<p class="alert alert-danger col-xs-12"><b>FEHLER:&nbsp;</b>{$lpa_error_message}</p>{/if}
    {if isset($lpa_status_message)}<p class="alert alert-info col-xs-12"><b>INFO:&nbsp;</b>{$lpa_status_message}</p>{/if}
    {assign var=orderErrorLimit value=604800}{* Critical warn when order expires in less than a week *}
    {assign var=orderWarnLimit value=2592000}{* Warn when order expires in less than a month (30 days) *}
    {assign var=authErrorLimit value=2073600}{* Critical warn when auth expires in less than 24 days (i.e. it has 1 day for guaranteed capture left) *}
    {assign var=authWarnLimit value=2419200}{* Warn when auth expires in less than 4 weeks (28 days) *}
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
                        <td>{$order->cOrderReferenceId}<br/>
                            <button type="button" class="btn btn-xs btn-default">Autorisierungen zeigen</button>
                        </td>
                        <td class="{$order->displayState}">{$order->cOrderStatus}{if isset($order->cOrderStatusReason) && $order->cOrderStatusReason|count_characters > 0} ({$order->cOrderStatusReason}){/if}</td>
                        <td>{$order->fOrderAmount} {$order->cOrderCurrencyCode}</td>
                        <td>{if $order->bSandbox == 1}Ja{elseif $order->bSandbox == 0}Nein{else}Error{/if}</td>
                        <td {if $order->displayState !== 'success'}class="{if $order->expiresIn < $orderErrorLimit}danger{elseif $order->expiresIn < $orderWarnLimit}warning{/if}"{/if}>{$order->expiresOnString}</td>
                        <td>
                            {if in_array('authorize', $order->actions)}
                                <div class="input-group">
                                    <input type="text" class="form-control" name="amount" value="{$order->fOrderAmount}"/>
                                    <span class="input-group-btn">
                                        <button class="lpa-admin-order-authorize btn btn-danger">Autorisieren</button>
                                    </span>
                                </div>
                            {/if}
                            <button class="lpa-admin-order-refresh btn btn-xs btn-primary">Refresh</button>
                            {if in_array('cancel', $order->actions)}
                                <button class="lpa-admin-order-cancel btn btn-xs btn-danger">Abbrechen</button>
                            {/if}
                            {if in_array('close', $order->actions)}
                                <button class="lpa-admin-order-close btn btn-xs btn-default">Schlie&szlig;en</button>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
            </table>
        </div>
    {else}
        {if !isset($lpa_pagination) || $lpa_pagination.currentPage == 1}
            <div id="lpa-order-table-hint" class="alert alert-info">Es sind noch keine Amazon Payment Bestellungen vorhanden.</div>
        {else}
            <div id="lpa-order-table-hint" class="alert alert-info">Keine weiteren Bestellungen vorhanden - bitte navigieren Sie auf eine vorherige Seite zur&uuml;ck.</div>
        {/if}
    {/if}
    <br/>
    <div class="lpa-admin-heading" id="lpa-auth-table-anchor">Autorisierungen</div>
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
                    <td>{$auth->cAuthorizationId}<br/>
                        <button type="button" class="btn btn-xs btn-default">Zahlungseinz&uuml;ge zeigen</button>
                    </td>
                    <td class="{$auth->displayState}">{$auth->cAuthorizationStatus}{if isset($auth->cAuthorizationStatusReason) && $auth->cAuthorizationStatusReason|count_characters > 0} ({$auth->cAuthorizationStatusReason}){/if}</td>
                    <td>{$auth->fAuthorizationAmount} {$auth->cAuthorizationCurrencyCode}</td>
                    <td>{$auth->fCapturedAmount} {$auth->cCapturedCurrencyCode}</td>
                    <td>{if $auth->bSandbox == 1}Ja{elseif $auth->bSandbox == 0}Nein{else}Error{/if}</td>
                    <td {if $auth->displayState !== 'success'}class="{if $auth->expiresIn < $authErrorLimit}danger{elseif $auth->expiresIn < $authWarnLimit}warning{/if}"{/if}>{$auth->expiresOnString}</td>
                    <td>
                        {if in_array('capture', $auth->actions)}
                            <div class="input-group">
                                <input type="text" name="amount" class="form-control" value="{$auth->fAuthorizationAmount}"/>
                                <span class="input-group-btn">
                                    <button class="lpa-admin-auth-capture btn btn-danger">Einziehen</button>
                                </span>
                            </div>
                        {/if}
                        {if in_array('close', $auth->actions)}
                            <button class="lpa-admin-auth-close btn btn-xs btn-default">Schlie&szlig;en</button>
                        {/if}
                    </td>
                </tr>
            {/foreach}
        </table>
    {else}
        <div id="lpa-auth-table-hint" class="alert alert-info">Es sind keine Autorisierungen vorhanden.</div>
    {/if}
    <br/>
    <div class="lpa-admin-heading" id="lpa-cap-table-anchor">Zahlungseinz&uuml;ge</div>
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
                    <td>{$cap->cCaptureId}<br/>
                        <button type="button" class="btn btn-xs btn-default">Erstattungen zeigen</button>
                    </td>
                    <td class="{$cap->displayState}">{$cap->cCaptureStatus}{if isset($cap->cCaptureStatusReason) && $cap->cCaptureStatusReason|count_characters > 0} ({$cap->cCaptureStatusReason}){/if}</td>
                    <td>{$cap->fCaptureAmount} {$cap->cCaptureCurrencyCode}</td>
                    <td>{$cap->fRefundedAmount} {$cap->cRefundedCurrencyCode}</td>
                    <td>{if $cap->bSandbox == 1}Ja{elseif $cap->bSandbox == 0}Nein{else}Error{/if}</td>
                    <td>
                        {if in_array('refund', $cap->actions)}
                            <div class="input-group">
                                <input type="text" name="amount" class="form-control" value="{$cap->fCaptureAmount}"/>
                                <span class="input-group-btn">
                                    <button class="lpa-admin-cap-refund btn btn-danger">Erstatten</button>
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
    <br/>
    <div class="lpa-admin-heading" id="lpa-refund-table-anchor">R&uuml;ckerstattungen</div>
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
    <input type="hidden" name="lpa_type" value=""/>
    <input type="hidden" name="lpa_action" value=""/>
    <input type="hidden" name="lpa_orid" value=""/>
    <input type="hidden" name="lpa_id" value=""/>
    <input type="hidden" name="lpa_amount" value=""/>
    <input type="hidden" name="lpa_management" value="1"/>
</form>

