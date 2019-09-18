<div class="widget-custom-data widget-bots">
    {if is_array($oBots_arr) && count($oBots_arr) > 0}
        <table class="table table-condensed table-hover table-blank">
            <thead>
                <tr>
                    <th>Name / User-Agent</th>
                    <th class="tright">Anzahl</th>
                </tr>
            </thead>
            <tbody>
                {foreach $oBots_arr as $oBots}
                    <tr>
                        <td>
                            {if isset($oBots->cName) && $oBots->cName|strlen > 0}
                                {$oBots->cName}
                            {elseif isset($oBots->cUserAgent) && $oBots->cUserAgent|strlen > 0}
                                {$oBots->cUserAgent}
                            {else}
                                Unbekannt
                            {/if}
                        </td>
                        <td class="tright">{$oBots->nCount}</td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
        Mehr Details unter <a href="statistik.php?s=3">Statistiken</a>
    {else}
        <div class="alert alert-info">Keine Statistiken gefunden</div>
    {/if}
</div>