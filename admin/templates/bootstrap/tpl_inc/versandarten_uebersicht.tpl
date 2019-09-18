<script type="text/javascript">
    {literal}
    function confirmDelete(cName) {
        return confirm('Sind Sie sicher, dass Sie die Versandart "' + cName + '" löschen möchten?');
    }
    {/literal}
</script>

{include file='tpl_inc/seite_header.tpl' cTitel=#shippingmethods# cBeschreibung=#isleListsHint# cDokuURL=#shippingmethodsURL#}
<div id="content" class="container-fluid">
    {foreach name=versandarten from=$versandarten item=versandart}
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{$versandart->cName}</h3>
            </div>
            <table class="table table-list">
                <tbody>
                    <tr>
                        <td style="width:160px">{#shippingTypeName#}</td>
                        <td>
                            {foreach name=versandartsprache from=$versandart->oVersandartSprachen_arr item=oVersandartSprachen}
                                    {$oVersandartSprachen->cName}{if !$smarty.foreach.versandartsprache.last}, {/if}
                            {/foreach}
                        </td>
                    </tr>
                    <tr>
                        <td>{#countries#}</td>
                        <td>
                            {foreach name=laender from=$versandart->land_arr item=land}
                                <a href="versandarten.php?zuschlag=1&kVersandart={$versandart->kVersandart}&cISO={$land}&token={$smarty.session.jtl_token}"><span class="label label-{if isset($versandart->zuschlag_arr[$land])}success{else}default{/if}">{$land}</span></a>
                            {/foreach}
                        </td>
                    </tr>
                    <tr>
                        <td>{#shippingclasses#}</td>
                        <td>
                            {if $versandart->versandklassen|@count == 1 && $versandart->versandklassen[0] === 'Alle'}
                                {$versandart->versandklassen[0]}
                            {else}
                                {foreach name=versandklassen from=$versandart->versandklassen item=versandklasse}
                                    [{$versandklasse}] &nbsp;
                                {/foreach}
                            {/if}
                        </td>
                    </tr>
                    <tr>
                        <td>{#customerclass#}</td>
                        <td>
                            {foreach name=versandklassen from=$versandart->cKundengruppenName_arr item=cKundengruppenName}
                                {$cKundengruppenName}
                            {/foreach}
                        </td>
                    </tr>
                    <tr>
                        <td>{#taxshippingcosts#}</td>
                        <td>{if $versandart->eSteuer === 'netto'}{#net#}{else}{#gross#}{/if}</td>
                    </tr>
                    <tr>
                        <td>{#shippingtime#}</td>
                        <td>{$versandart->nMinLiefertage} - {$versandart->nMaxLiefertage} Tage</td>
                    </tr>
                    <tr>
                        <td>{#paymentMethods#}</td>
                        <td>
                            {foreach name=zahlungsarten from=$versandart->versandartzahlungsarten item=zahlungsart}
                                {$zahlungsart->zahlungsart->cName}{if isset($zahlungsart->zahlungsart->cAnbieter) &&
                                    $zahlungsart->zahlungsart->cAnbieter|strlen > 0} ({$zahlungsart->zahlungsart->cAnbieter}){/if} {if $zahlungsart->fAufpreis!=0}{if $zahlungsart->cAufpreisTyp != "%"}{getCurrencyConversionSmarty fPreisBrutto=$zahlungsart->fAufpreis bSteuer=false}{else}{$zahlungsart->fAufpreis}%{/if}{/if}
                                <br />
                            {/foreach}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {if $versandart->versandberechnung->cModulId === 'vm_versandberechnung_gewicht_jtl' || $versandart->versandberechnung->cModulId === 'vm_versandberechnung_warenwert_jtl' || $versandart->versandberechnung->cModulId === 'vm_versandberechnung_artikelanzahl_jtl'}
                                {#priceScale#}
                            {elseif $versandart->versandberechnung->cModulId === 'vm_versandkosten_pauschale_jtl'}
                                {#shippingPrice#}
                            {/if}
                        </td>
                        <td>
                            {if $versandart->versandberechnung->cModulId === 'vm_versandberechnung_gewicht_jtl' || $versandart->versandberechnung->cModulId === 'vm_versandberechnung_warenwert_jtl' || $versandart->versandberechnung->cModulId === 'vm_versandberechnung_artikelanzahl_jtl'}
                                {foreach name=preisstaffel from=$versandart->versandartstaffeln item=versandartstaffel}
                                    {if $versandartstaffel->fBis != 999999999}
                                        {#upTo#} {$versandartstaffel->fBis} {$versandart->einheit} {getCurrencyConversionSmarty fPreisBrutto=$versandartstaffel->fPreis bSteuer=false}
                                        <br />
                                    {/if}
                                {/foreach}
                            {elseif $versandart->versandberechnung->cModulId === 'vm_versandkosten_pauschale_jtl'}
                                {getCurrencyConversionSmarty fPreisBrutto=$versandart->fPreis bSteuer=false}
                            {/if}
                        </td>
                    </tr>
                    {if $versandart->fVersandkostenfreiAbX>0}
                        <tr>
                            <td>{#freeFrom#}</td>
                            <td>{getCurrencyConversionSmarty fPreisBrutto=$versandart->fVersandkostenfreiAbX bSteuer=false} ({if $versandart->eSteuer === 'netto'}{#net#}{else}{#gross#}{/if})</td>
                        </tr>
                    {/if}
                    {if $versandart->fDeckelung>0}
                        <tr>
                            <td>{#maxCostsUpTo#}</td>
                            <td>{getCurrencyConversionSmarty fPreisBrutto=$versandart->fDeckelung bSteuer=false}</td>
                        </tr>
                    {/if}
                </tbody>
            </table>
            <div class="panel-footer">
                <form method="post" action="versandarten.php">
                    {$jtl_token}
                    <div class="btn-group">
                        <button name="edit" value="{$versandart->kVersandart}" class="btn btn-primary"><i class="fa fa-edit"></i> Bearbeiten</button>
                        <button name="clone" value="{$versandart->kVersandart}" class="btn btn-default clone">Duplizieren</button>
                        <button name="del" value="{$versandart->kVersandart}" class="btn btn-danger" onclick="return confirmDelete('{$versandart->cName}');"><i class="fa fa-trash"></i> L&ouml;schen</button>
                    </div>
                </form>
            </div>
        </div>
    {/foreach}

    <div id="settings">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{#createShippingMethod#}</h3>
            </div>
            <form name="versandart_neu" method="post" action="versandarten.php">
                {$jtl_token}
                <div class="panel-body">
                    <input type="hidden" name="neu" value="1" />
                    {foreach name=versandberechnungen from=$versandberechnungen item=versandberechnung}
                        <div class="item">
                            <div class="for">
                                <input type="radio" id="l{$smarty.foreach.versandberechnungen.index}" name="kVersandberechnung" value="{$versandberechnung->kVersandberechnung}" {if $smarty.foreach.versandberechnungen.index == 0}checked="checked"{/if} />
                                <label for="l{$smarty.foreach.versandberechnungen.index}">{$versandberechnung->cName}</label>
                            </div>
                        </div>
                    {/foreach}
                </div>
                <div class="panel-footer">
                    <button type="submit" value="{#createShippingMethod#}" class="btn btn-primary"><i class="fa fa-share"></i> {#createShippingMethod#}</button>
                </div>
            </form>
        </div>
    </div>
</div>