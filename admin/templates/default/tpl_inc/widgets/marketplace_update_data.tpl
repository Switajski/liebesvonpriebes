{if count($oMarketplaceUpdates_arr) > 0}
   
   <p class="linklist_head">Plugin Name <span class="right">Version</p>
   <ul class="linklist padded infolist">
      
   {foreach name="marketplace_updates" from=$oMarketplaceUpdates_arr item=oMarketplaceUpdate}
      <li>
         <a href="{$oMarketplaceUpdate->cUrl}" target="_blank">
             <p>{$oMarketplaceUpdate->cName|truncate:'50':'...'} <span class="date">{$oMarketplaceUpdate->cVersion}</span></p>
         </a>
      </li>
   {/foreach}
   </ul>
{else}
   <p class="box_info container">Alle Plugins sind auf einem aktuellen Stand.</p>
{/if}