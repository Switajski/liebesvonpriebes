{if !is_object($oVersion) || $oVersion->nType == -2}
   <div class="version critical">
      Version konnte nicht ermittelt werden
   </div>
{elseif $oVersion->nType == -1}
   <div class="version">
      Aktuellste Version bereits vorhanden
   </div>
{elseif $oVersion->nType == -3}
   <div class="version">
      Entwicklung (Version: {$oVersion->nVersion})
   </div>
{elseif $oVersion->nType >= 0}
   <div class="version {if $oVersion->nType == 2}critical{else}new_version{/if}">
      <a href="{$oVersion->cURL|urldecode}" target="_blank">
      {if $oVersion->nType == 0}
         Empfohlenes Update 
      {elseif $oVersion->nType == 1}
         Neue Features 
      {elseif $oVersion->nType == 2}
         Wichtiges Update 
      {/if}
      verf&uuml;gbar (Version: {$oVersion->nVersion})
      </a>
   </div>
{/if}