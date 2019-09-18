{include file='tpl_inc/seite_header.tpl' cTitel=#payments#|cat:$oZahlungsart->cName cBeschreibung=#paymentsDesc# cDokuURL=#paymentsURL#}
<div id="content" class="container-fluid">
    {include file='tpl_inc/filtertools.tpl' oFilter=$oFilter cParam_arr=['a'=>'payments',
        'token'=>$smarty.session.jtl_token, 'kZahlungsart'=>$oZahlungsart->kZahlungsart]}
    {include file='tpl_inc/pagination.tpl' oPagination=$oPagination cParam_arr=['a'=>'payments',
        'token'=>$smarty.session.jtl_token, 'kZahlungsart'=>$oZahlungsart->kZahlungsart]}
    <form method="post" action="zahlungsarten.php">
        {$jtl_token}
        <input type="hidden" name="a" value="payments" />
        <input type="hidden" name="kZahlungsart" value="{$oZahlungsart->kZahlungsart}" />
        <div class="panel panel-default">
            {if $oZahlunseingang_arr|@count > 0}
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Datum/Zeit</th>
                                <th>Bestell-Nr.</th>
                                <th>Kunde</th>
                                <th>Gezahlter Betrag</th>
                                <th>Zahlungsgeb&uuml;hr</th>
                                <th>W&auml;hrung</th>
                                <th>Abgeholt durch Wawi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $oZahlunseingang_arr as $oZahlungseingang}
                                <tr>
                                    <td>
                                        <input type="checkbox" name="kEingang_arr[]"
                                               id="eingang-{$oZahlungseingang->kZahlungseingang}"
                                               value="{$oZahlungseingang->kZahlungseingang}">
                                    </td>
                                    <td>
                                        <label for="eingang-{$oZahlungseingang->kZahlungseingang}">{$oZahlungseingang->dZeit}</label>
                                    </td>
                                    <td>{$oZahlungseingang->cBestellNr}</td>
                                    <td>
                                        {$oZahlungseingang->cVorname} {$oZahlungseingang->cNachname}<br>
                                        {if !empty($oZahlungseingang->cZahler)}&lt;{$oZahlungseingang->cZahler}&gt;{/if}
                                    </td>
                                    <td>
                                        {$oZahlungseingang->fBetrag|number_format:2:',':'.'}
                                    </td>
                                    <td>
                                        {$oZahlungseingang->fZahlungsgebuehr|number_format:2:',':'.'}
                                    </td>
                                    <td>{$oZahlungseingang->cISO}</td>
                                    <td>
                                        {if $oZahlungseingang->cAbgeholt === 'Y'}
                                            <span class="label label-success" title="Aktiv"><i class="fa fa-check fa-fw"></i></span>
                                        {elseif $oZahlungseingang->cAbgeholt === 'N'}
                                            <span class="label label-danger" title="Inaktiv"><i class="fa fa-times fa-fw"></i></span>
                                        {/if}
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td><input type="checkbox" name="ALLMSGS" id="ALLMSGS" onclick="AllMessages(this.form);"></td>
                                <td colspan="7"><label for="ALLMSGS">Alle ausw&auml;hlen</label></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            {else}
                <div class="alert alert-info" role="alert">
                    {#noDataAvailable#}
                </div>
            {/if}
            <div class="panel-footer">
                <div class="btn-group">
                    <button type="submit" name="action" value="paymentwawireset" class="btn btn-danger">
                        <i class="fa fa-refresh"></i>
                        Wawi-Abholung zur&uuml;cksetzen
                    </button>
                    <a class="btn btn-primary" href="zahlungsarten.php">{#goBack#}</a>
                </div>
            </div>
        </div>
    </form>
</div>