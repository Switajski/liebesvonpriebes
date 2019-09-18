{config_load file="$lang.conf" section="bestellungen"}
<div class="container-full">
    {if $message}
        <div class="alert alert-{key($message)}" role="alert">
            {reset($message)}
        </div>
    {/if}

    {if $orders|@count > 0 && $orders}
        {include file='pagination.tpl' cSite='1' cUrl='plugin.php' cParams='&kPlugin='|cat:$oPlugin->kPlugin oBlaetterNavi=$pagination hash=$hash}
        <form method="post" action="{$post_url}">
            {$jtl_token}
            <div class="panel panel-default">
                <table class="list table table-hover">
                    <thead>
                    <tr>
                        <th class="tleft">{#orderNumber#}</th>
                        <th class="tleft">{#orderCostumer#}</th>
                        <th class="tleft">{#orderPaymentName#}</th>
                        <th class="text-center">{#orderSum#}</th>
                        <th class="text-center">Status</th>
                        <th class="text-right"></th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach $orders as $order}
                        {$payment = null}
                        {if isset($payments[$order->kBestellung])}
                            {$payment = $payments[$order->kBestellung]}
                        {/if}
                        <tr class="text-vcenter">
                            <td>
                                <div>{$order->cBestellNr}</div>
                                <small class="text-muted" title="{$order->dErstelldatum_de}" data-toggle="tooltip" data-placement="left"><i class="fa fa-clock-o" aria-hidden="true"></i> {$order->dErstelldatum_de|date_format:"%d.%m.%Y"}</small>
                            </td>
                            <td>
                                {if isset($order->oKunde->cVorname) || isset($order->oKunde->cNachname) || isset($order->oKunde->cFirma)}
                                    <div>
                                        {$order->oKunde->cVorname} {$order->oKunde->cNachname}
                                        {if isset($order->oKunde->cFirma) && $order->oKunde->cFirma|strlen > 0} ({$order->oKunde->cFirma}){/if}
                                    </div>
                                    <small class="text-muted"><i class="fa fa-user" aria-hidden="true"></i> {$order->oKunde->cMail}</small>
                                {else}
                                    <i class="fa fa-user-secret" aria-hidden="true"></i> {#noAccount#}
                                {/if}
                            </td>
                            <td>
                                <div>{$order->cZahlungsartName}</div>
                                {if $payment}
                                    <small class="text-muted"><i class="fa fa-paypal text-info" aria-hidden="true"></i> {$payment->cHinweis}</small>
                                {/if}
                            </td>
                            <td class="text-center">{$order->WarensummeLocalized[0]}</td>
                            <td class="text-center">
                                <small class="{if $order->cStatus < 0}label label-danger{elseif $order->cStatus > 0 && $order->cStatus < 3}text-muted{else}label label-success{/if}">{$order->Status}</small>
                            </td>
                            <th class="text-right no-flow">
                                {if $payment}
                                    <div class="btn-group btn-group-xs" role="group" aria-label="Extra-small button group">
                                        <a href="https://www.sandbox.paypal.com/activity/payment/{$payment->cHinweis}" target="_blank" class="btn btn-default">Sandbox</a>
                                        <a href="https://www.paypal.com/activity/payment/{$payment->cHinweis}" target="_blank" class="btn btn-default active">Live</a>
                                    </div>
                                {/if}
                            </th>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </form>
    {else}
        <div class="alert alert-info"><i class="fa fa-info-circle"></i> Keine Daten vorhanden.</div>
    {/if}
</div>