<div class="clearall">
   <h1 {if isset($cBeschreibung) && $cBeschreibung|@count_characters == 0}class="nospacing"{/if}>{if $cTitel|@count_characters > 0}{$cTitel}{else}Unbekannt{/if}</h1>
   {if isset($cDokuUR) && $cDokuURL|@count_characters > 0}
      <div class="documentation">
         <a href="{$cDokuURL}" class="button" title="Dokumentation" target="_blank">Dokumentation zu {$cTitel}</a>
      </div>
   {/if}
</div>
{if isset($cBeschreibung) && $cBeschreibung|@count_characters > 0}
   <p class="description {if isset($cClass)}{$cClass}{/if}">
      <span><!-- right border --></span>
      {if isset($onClick)}<a href="#" onclick="{$onClick}">{/if}{$cBeschreibung}{if isset($onClick)}</a>{/if}
   </p>
{/if}