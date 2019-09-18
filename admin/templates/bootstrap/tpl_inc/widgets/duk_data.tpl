{if is_object($oDuk)}
    <p class="duk">{$oDuk->cText}</p>
{else}
    <div class="widget-container"><div class="alert alert-info error">Keine Daten verf&uuml;gbar</div></div>
{/if}