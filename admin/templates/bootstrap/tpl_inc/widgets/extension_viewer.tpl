<div class="widget-custom-data">
    <ul class="infolist clearall">
        {foreach name=modules from=$oModul_arr item=oModul}
            {if $oModul->cDefine !== 'SHOP_ERWEITERUNG_RMA'}
                <li class="{if $smarty.foreach.modules.first}first{elseif $smarty.foreach.modules.last}last{/if}">
                    <p class="key">{$oModul->cName}
                        <span class="value {if $oModul->bActive}success{/if}">
                            {if $oModul->bActive}
                                <span class="label label-success pull-right">Aktiv</span>
                            {else}
                                <a href="{$oModul->cURL}" target="_blank">Jetzt kaufen</a>
                            {/if}
                        </span>
                    </p>
                </li>
            {/if}
        {/foreach}
    </ul>
</div>
