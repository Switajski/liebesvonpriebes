<div class="jtlsearch_actioncolumn">
    <div class="jtlsearch_inner">
        {*
        {if $cServereinstellungenURL}
        <script type="text/javascript">
            $(document).ready(function() {ldelim}
            window.location.href = '{$cServereinstellungenURL}';
            {rdelim});
        </script>
        {/if}
        <a class="button orange" href="plugin.php?kPlugin={$oPlugin->kPlugin}&a=createtmplogin" target="_blank">Servereinstellungen</a>*}
    </div>
</div>
<div class="jtlsearch_infocolumn">
    <div class="jtlsearch_inner">
        {if $xIndexStatus_arr|@count > 0}
        <p>Ihre Shop-ID: <strong>{$xIndexStatus_arr.0->kUserShop}</strong></p><br />

        <ul class="infolist">
            {foreach from=$xIndexStatus_arr item=xIndexStatus}
            {if $xIndexStatus->nItemCount > 0}
            <li>
                <span class="success">Suchindex für Sprache "{$xIndexStatus->cLanguageISO}" ist verfügbar!</span>
            </li>
            {else}
            <li>
                <span class="info">Für den Suchindex "{$xIndexStatus->cLanguageISO}" wurden noch keine Daten importiert!</span>
            </li>
            {/if}
            {/foreach}
        </ul>
        {else}
        Für Ihren Shop wurden auf dem Suchserver noch keine Daten indiziert. Bitte Export starten.
        {/if}
    </div>
</div>
<div class="jtlsearch_clear"></div>
