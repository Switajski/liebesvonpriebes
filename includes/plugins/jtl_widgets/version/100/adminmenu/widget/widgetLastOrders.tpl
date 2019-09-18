<link rel="stylesheet" type="text/css" href="{$cAdminmenuPfadURL}css/lastOrders.css" />
<script type="text/javascript" src="{$cAdminmenuPfadURL}js/lastOrders.js"></script>

<div class="widget-custom-data">
    <div class="summary">
        <div class="table-responsive">
            <table id="table_last_orders" class="table">
                <thead>
                <tr>
                    <th class="tleft">Kunde</th>
                    <th class="tleft">Versandart</th>
                    <th class="tleft">Zahlungsart</th>
                    <th class="tright">Warensumme</th>
                </tr>
                </thead>
                <tbody>
                {foreach from=$oBestellung_arr item=oBestellung}
                    <tr id="last_order_row_{$oBestellung->kBestellung}" title="Bestellnummer: {$oBestellung->cBestellNr}">
                        <td class="tleft">{if $oBestellung->oKunde->cVorname || $oBestellung->oKunde->cNachname || $oBestellung->oKunde->cFirma}{$oBestellung->oKunde->cVorname} {$oBestellung->oKunde->cNachname}{if isset($oBestellung->oKunde->cFirma) && $oBestellung->oKunde->cFirma|count_characters > 0} ({$oBestellung->oKunde->cFirma}){/if}{else}{#noAccount#}{/if}</td>
                        <td class="tleft">{$oBestellung->cVersandartName}</td>
                        <td class="tleft">{$oBestellung->cZahlungsartName}</td>
                        <td class="tright">{$oBestellung->WarensummeLocalized[0]}
                            <div id="last_order_pop_{$oBestellung->kBestellung}" class="last_order_hidden">{include file=$cDetail oBestellung=$oBestellung}</div>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>