<div class="widget-custom-data widget-visitors">
    {if $oVisitorsInfo->nAll > 0}
        <div class="row">
            <div class="col-xs-4"><strong><i class="fa fa-users" aria-hidden="true"></i> Kunden:</strong> <span class="value">{$oVisitorsInfo->nCustomer}</span></div>
            <div class="col-xs-4"><strong><i class="fa fa-user-secret" aria-hidden="true"></i> G&auml;ste:</strong> <span class="value">{$oVisitorsInfo->nUnknown}</span></div>
            <div class="col-xs-4 text-right"><strong>Insgesamt:</strong> <span class="value">{$oVisitorsInfo->nAll}</span></div>
        </div>
        <hr>
    {else}
        <div class="widget-container"><div class="alert alert-info">Momentan befinden sich keine Besucher im Shop.</div></div>
    {/if}

    {if is_array($oVisitors_arr) && $oVisitors_arr|@count > 0}
        <table class="table table-condensed table-hover table-blank">
            <thead>
                <th>Kunde</th><th>Info</th><th class="text-center">Letzte Aktivität</th><th class="text-right">Warenkorb (Netto)</th>
            </thead>
            <tbody>
            {foreach from=$oVisitors_arr item=oVisitor}
                {if !empty($oVisitor->kKunde)}
                    <tr>
                        <td class="customer" onclick="$(this).parent().toggleClass('active')">
                            
                            {$oVisitor->cVorname} {$oVisitor->cNachname}
                        </td>
                        <td>
                            {if $oVisitor->cBrowser|strlen > 0}
                                <a href="#" data-toggle="tooltip" data-placement="top" title="{if $oVisitor->dErstellt|strlen > 0}Kunde seit {$oVisitor->dErstellt|date_format:"%d.%m.%Y"}{/if} | Browser: {$oVisitor->cBrowser}{if $oVisitor->cIP|strlen > 0} | IP: {$oVisitor->cIP}{/if}"><i class="fa fa-user"></i><span class="sr-only">Details</span></a>
                            {/if}
                            {if $oVisitor->cEinstiegsseite|strlen > 0}
                                <a href="{$oVisitor->cEinstiegsseite}"  target="_blank" data-toggle="tooltip" data-placement="top" title="Einstiegsseite: {$oVisitor->cEinstiegsseite}{if $oVisitor->cReferer|strlen > 0} | Herkunft: {$oVisitor->cReferer|escape:'html'}{/if}"><i class="fa fa-globe"></i><span class="sr-only">Einstiegsseite</span></a>
                            {/if}
                            {if $oVisitor->cNewsletter === 'Y'}
                                <a href="#" data-toggle="tooltip" data-placement="top" title="Newsletter-Abonnent"><i class="fa fa-envelope-o"></i><span class="sr-only">Newsletter-Abonnent</span></a>
                            {/if}
                        </td>
                        <td class="text-muted text-center">
                            {if $oVisitor->dLetzteAktivitaet|strlen > 0}
                                 {if $oVisitor->cAusstiegsseite|strlen > 0}
                                    <a href="{$oVisitor->cAusstiegsseite}" target="_blank" data-toggle="tooltip" data-placement="top" title="{$oVisitor->cAusstiegsseite}">
                                        {$oVisitor->dLetzteAktivitaet|date_format:"%H:%M:%S"}
                                     </a>
                                 {else}
                                    {$oVisitor->dLetzteAktivitaet|date_format:"%H:%M:%S"}
                                 {/if}
                            {/if}
                        </td>
                        <td class="basket text-right">
                            {if $oVisitor->kBestellung > 0}
                                <i class="fa fa-shopping-cart" aria-hidden="true"></i> {$oVisitor->fGesamtsumme}
                            {else}
                                <span class="text-muted"><i class="fa fa-shopping-cart" aria-hidden="true"></i> -</span>
                            {/if}
                        </td>
                    </tr>
                {/if}
            {/foreach}
            </tbody>
        </table>
    {/if}
</div>