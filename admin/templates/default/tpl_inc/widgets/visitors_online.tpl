<div class="widget-custom-data widget-visitors">
   {if $oVisitorsInfo->nAll > 0}
      <p class="headinfo">
         Insgesamt: <span class="value">{$oVisitorsInfo->nAll}</span>
         Kunden: <span class="value">{$oVisitorsInfo->nCustomer}</span>
         G&auml;ste: <span class="value">{$oVisitorsInfo->nUnknown}</span>
      </p>
   {else}
      <p class="container tcenter"><span class="error">Momentan befinden sich keine Besucher im Shop.</span></p>
   {/if}
   
   {if is_array($oVisitors_arr) && $oVisitors_arr|@count > 0}
      <ul class="togglelist">
      {foreach from=$oVisitors_arr item=oVisitor}
         {if $oVisitor->kKunde > 0}
            <li>
               <p class="title" onclick="$(this).parent().toggleClass('active')">{$oVisitor->cVorname} {$oVisitor->cNachname}</p>
               <div class="more" id="visitor{$oVisitor->kBesucher}">
                  {if $oVisitor->cBrowser|count_characters > 0}
                     <p><strong>Browser:</strong> {$oVisitor->cBrowser}</p>
                  {/if}
                  {if $oVisitor->cReferer|count_characters > 0}
                     <p><strong>Herkunft:</strong> {$oVisitor->cReferer}</p>
                  {/if}
                  {if $oVisitor->cIP|count_characters > 0}
                     <p><strong>IP-Adresse:</strong> {$oVisitor->cIP}</p>
                  {/if}
                  {if $oVisitor->cEinstiegsseite|count_characters > 0}
                     <p><strong>Einstiegsseite:</strong> <a href="{$oVisitor->cEinstiegsseite}" target="_blank">{$oVisitor->cEinstiegsseite}</a></p>
                  {/if}
                  {if $oVisitor->cAusstiegsseite|count_characters > 0}
                     <p><strong>Zuletzt angesehen:</strong> <a href="{$oVisitor->cAusstiegsseite}" target="_blank">{$oVisitor->cAusstiegsseite}</a></p>
                  {/if}
                  {if $oVisitor->dLetzteAktivitaet|count_characters > 0}
                     <p><strong>Letzte Aktivit&auml;t:</strong> {$oVisitor->dLetzteAktivitaet|date_format:"%H:%M:%S"}
                  {/if}
                  {if $oVisitor->dErstellt|count_characters > 0}
                     <p><strong>Registriert seit:</strong> {$oVisitor->dErstellt|date_format:"%d.%m.%Y"}
                  {/if}
               </div>
               <p class="info">
                  <span class="basket {if $oVisitor->kBestellung > 0}active{/if}" {if $oVisitor->kBestellung > 0}title="Bestellwert: {$oVisitor->fGesamtsumme}"{/if}></span>
                  <span class="newsletter {if $oVisitor->cNewsletter == 'Y'}active{/if}" {if $oVisitor->cNewsletter == 'Y'}title="Newsletter Abonnent"{/if}></span>
               </p>
            </li>
         {/if}
      {/foreach}
      </ul>
   {/if}
</div>