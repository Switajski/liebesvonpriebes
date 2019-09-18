<div class="widget_settings">
   {foreach from=$oAvailableWidget_arr item=oAvailableWidget}
      <div class="widget_item">
         <p class="title">{$oAvailableWidget->cTitle}</p>
         <p class="desc">{$oAvailableWidget->cDescription}</p>
         <a href="#" class="add" ref="{$oAvailableWidget->kWidget}"></a>
      </div>
   {/foreach}
   {if $oAvailableWidget_arr|@count == 0}
      <div class="widget_item">
         <p class="title">Keine weiteren Widgets vorhanden.</p>
      </div>
   {/if}
</div>
