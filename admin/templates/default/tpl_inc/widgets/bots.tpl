<div class="widget-custom-data widget-bots">
   {if is_array($oBots_arr) && count($oBots_arr) > 0}
      <ul class="infolist clearall">
      {foreach name="bots" from=$oBots_arr item=oBots}
         <li {if $smarty.foreach.bots.first}class="first"{elseif $smarty.foreach.bots.last}class="last"{/if}>
            {if isset($oBots->cName) && $oBots->cName|count_characters > 0}
               {$oBots->cName}
            {elseif isset($oBots->cUserAgent) && $oBots->cUserAgent|count_characters > 0}
               {$oBots->cUserAgent}
            {else}
               Unbekannt
            {/if}
            <span class="value">{$oBots->nCount}</span>
         </li>
      {/foreach}      
      </ul>
   {else}
      <p class="container tcenter"><span class="error">Keine Statistiken gefunden</span></p>
   {/if}
</div>