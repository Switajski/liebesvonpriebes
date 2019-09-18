<div class="widget-custom-data">
    {if $kRequestCountTotal > 0}
        <ul class="infolist">
            {foreach from=$oRequestGroup_arr item='oRequestGroup'}
                {if $oRequestGroup->kRequestCount > 0}
                    <li>
                        <p>
                            <strong>{$oRequestGroup->cGroupName}:</strong>
                            <span class="value">{$oRequestGroup->kRequestCount}</span>
                        </p>
                    </li>
                {/if}
            {/foreach}
            <li>
                Verwalten Sie ausstehende Anfragen in der <a href="freischalten.php">Freischaltzentrale</a>.
            </li>
        </ul>
    {else}
        <div class="alert alert-info">
            Zur Zeit gibt es keine ausstehenden Anfragen die freigeschaltet werden m&uuml;ssen.
        </div>
    {/if}
</div>