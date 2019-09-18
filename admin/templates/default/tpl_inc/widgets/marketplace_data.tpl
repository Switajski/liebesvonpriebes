{if count($oMarketplace_arr) > 0}
   {foreach name="marketplace" from=$oMarketplace_arr item=oMarketplaceGroup}
      {if $oMarketplaceGroup->oExtension_arr|@count > 0}
         <p class="linklist_head">{$oMarketplaceGroup->cName|truncate:'50':'...'}</p>
         <ul class="linklist padded">
            {foreach from=$oMarketplaceGroup->oExtension_arr item=oExtension}
               <li {if $oExtension->bHighlight}class="highlight"{/if}>
                  <img src="{$oExtension->cLogoPfad}" />
                  <a href="{$oExtension->cUrl}" target="_blank">
                     <p>{$oExtension->cName|truncate:'50':'...'}</p>
                     {if $oExtension->cKurzBeschreibung|@count_characters > 0}
                        <p class="desc">{$oExtension->cKurzBeschreibung|truncate:'50':'...'}</p>
                     {/if}
                  </a>
               </li>
            {/foreach}
         </ul>
      {/if}
   {/foreach}
{else}
   <p class="box_info container">Zur Zeit stehen keine Erweiterungen zur Verf&uuml;gung</p>
{/if}
