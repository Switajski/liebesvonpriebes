<div class="widget-custom-data widget-visitors">   
   {if is_array($oVisitors_arr) && $oVisitors_arr|@count > 0}
      <ul class="togglelist">
      {foreach from=$oVisitors_arr item=oVisitor}
         {if $oVisitor->kKunde > 0}
            <li>
               <p class="title" onclick="$(this).parent().toggleClass('active')">{$oVisitor->cVorname} {$oVisitor->cNachname}</p>
               <div class="more" id="visitor{$oVisitor->kBesucher}">
                  <p><strong>Browser:</strong> {$oVisitor->cBrowser}</p>
                  <p><strong>Herkunft:</strong> {$oVisitor->cReferer}</p>
                  <p><strong>IP-Adresse:</strong> {$oVisitor->cIP}</p>
                  <p><strong>Einstiegsseite:</strong> {$oVisitor->cEinstiegsseite}</p>
                  <p><strong>Registriert seit:</strong> {$oVisitor->dErstellt|date_format:"%d.%m.%Y"}
               </div>
               <p class="info">
                  <span class="basket {if $oVisitor->kBestellung > 0}active{/if}" {if $oVisitor->kBestellung > 0}title="Bestellwert: {$oVisitor->fGesamtsumme}"{/if}></span>
                  <span class="newsletter {if $oVisitor->cNewsletter == 'Y'}active{/if}" {if $oVisitor->cNewsletter == 'Y'}title="Newsletter Abonnent"{/if}></span>
               </p>
            </li>
         {/if}
      {/foreach}
      </ul>
   {else}
      <p>Momentan befinden sich keine Besucher im Shop.</p>
   {/if}
</div>