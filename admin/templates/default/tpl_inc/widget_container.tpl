<ul id="{$eContainer}" class="column">
   {foreach from=$oActiveWidget_arr item=oWidget}
      {if $oWidget->eContainer == $eContainer}
         <li class="widget" ref="{$oWidget->kWidget}">
            <div class="widget-head"> 
               <h3>{$oWidget->cTitle}</h3>
               <p></p>
            </div> 
            <div class="widget-content {if !$oWidget->bExpanded}hidden{/if}"> 
               {$oWidget->cContent}
            </div>
         </li>
      {/if}
   {/foreach}
</ul>