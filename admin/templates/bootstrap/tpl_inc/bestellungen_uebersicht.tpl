{include file='tpl_inc/seite_header.tpl' cTitel=#order# cBeschreibung=#orderDesc# cDokuURL=#orderURL#}
<div id="content" class="container-fluid">
    {include file='tpl_inc/pagination.tpl' oPagination=$oPagination cParam_arr=['cSuche'=>$cSuche]}
    <div class=" block clearall">
        <div class="right">
            <form name="bestellungen" method="post" action="bestellungen.php">
                {$jtl_token}
                <input type="hidden" name="Suche" value="1" />
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="orderSearch">{#orderSearchItem#}:</label>
                    </span>
                    <input class="form-control" name="cSuche" type="text" value="{if isset($cSuche)}{$cSuche}{/if}" id="orderSearch" />
                    <span class="input-group-btn">
                        <button name="submitSuche" type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Suchen</button>
                    </span>
                </div>
            </form>
        </div>
    </div>
    {if $oBestellung_arr|@count > 0 && $oBestellung_arr}
        <form name="bestellungen" method="post" action="bestellungen.php">
            {$jtl_token}
            <input type="hidden" name="zuruecksetzen" value="1" />
            {if isset($cSuche) && $cSuche|strlen > 0}
                <input type="hidden" name="cSuche" value="{$cSuche}" />
            {/if}
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#order#}</h3>
                </div>
                <div class="table-responsive">
                    <table class="list table">
                        <thead>
                        <tr>
                            <th></th>
                            <th class="tleft">{#orderNumber#}</th>
                            <th class="tleft">{#orderCostumer#}</th>
                            <th class="tleft">{#orderShippingName#}</th>
                            <th class="tleft">{#orderPaymentName#}</th>
                            <th>{#orderWawiPickedUp#}</th>
                            <th>{#orderSum#}</th>
                            <th class="tright">{#orderDate#}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach name=bestellungen from=$oBestellung_arr item=oBestellung}
                            <tr class="tab_bg{$smarty.foreach.bestellungen.iteration%2}">
                                <td class="check">{if $oBestellung->cAbgeholt === 'Y' && $oBestellung->cZahlungsartName !== 'Amazon Payment' && $oBestellung->oKunde !== null}
                                    <input type="checkbox" name="kBestellung[]" value="{$oBestellung->kBestellung}" />{/if}
                                </td>
                                <td>{$oBestellung->cBestellNr}</td>
                                <td>{if isset($oBestellung->oKunde->cVorname) || isset($oBestellung->oKunde->cNachname) || isset($oBestellung->oKunde->cFirma)}{$oBestellung->oKunde->cVorname} {$oBestellung->oKunde->cNachname}{if isset($oBestellung->oKunde->cFirma) && $oBestellung->oKunde->cFirma|strlen > 0} ({$oBestellung->oKunde->cFirma}){/if}{else}{#noAccount#}{/if}</td>
                                <td>{$oBestellung->cVersandartName}</td>
                                <td>{$oBestellung->cZahlungsartName}</td>
                                <td class="tcenter">{if $oBestellung->cAbgeholt === 'Y'}{#yes#}{else}{#no#}{/if}</td>
                                <td class="tcenter">{$oBestellung->WarensummeLocalized[0]}</td>
                                <td class="tright">{$oBestellung->dErstelldatum_de}</td>
                            </tr>
                        {/foreach}
                        </tbody>
                        <tfoot>
                        <tr>
                            <td class="check">
                                <input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);" />
                            </td>
                            <td colspan="8"><label for="ALLMSGS">Alle ausw&auml;hlen</label></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="panel-footer">
                    <button name="zuruecksetzenBTN" type="submit" class="btn btn-danger"><i class="fa fa-refresh"></i> {#orderPickedUpResetBTN#}</button>
                </div>
            </div>
        </form>
    {else}
        <div class="alert alert-info"><i class="fa fa-info-circle"></i> Keine Daten vorhanden.</div>
    {/if}
</div>