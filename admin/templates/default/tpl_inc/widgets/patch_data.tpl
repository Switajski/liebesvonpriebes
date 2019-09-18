{if count($oPatch_arr) > 0}
   {foreach name="patch" from=$oPatch_arr item=oPatch}
      <li>
         {if $oPatch->cIconURL|count_characters > 0}
            <img src="{$oPatch->cIconURL|urldecode}" alt="" title="{$oPatch->cTitle}" />
         {/if}
         <a href="{$oPatch->cURL}" title="{$oPatch->cTitle}" target="_blank">
            <p>{$oPatch->cTitle|truncate:'50':'...'}</p>
            <p class="desc">{$oPatch->cDescription}</p>
         </a>
      </li>
   {/foreach}
{else}
   <p class="box_info container">Zur Zeit stehen keine Patches zur Verf&uuml;gung</p>
{/if}
