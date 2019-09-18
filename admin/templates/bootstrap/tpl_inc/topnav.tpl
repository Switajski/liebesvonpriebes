<ul id="topmenu">
    {foreach name=linkobergruppen from=$oLinkOberGruppe_arr item=oLinkOberGruppe}
        <li class="topmenu {if $smarty.foreach.linkobergruppen.first}topfirst{elseif $smarty.foreach.linkobergruppen.last}toplast{/if}">
            <a href="#" class="parent"><span class="link-icon"></span><span class="link-text">{$oLinkOberGruppe->cName}</span></a>
            <ul>
                {foreach name=linkuntergruppen from=$oLinkOberGruppe->oLinkGruppe_arr item=oLinkGruppe}
                    <li {if $smarty.foreach.linkuntergruppen.first}class="subfirst"{/if}>
                        <a href="#"><span>{$oLinkGruppe->cName}</span></a>
                        {if $oLinkGruppe->oLink_arr|@count > 0}
                            <ul>
                                {foreach name=linkgruppenlinks from=$oLinkGruppe->oLink_arr item=oLink}
                                    <li class="{if $smarty.foreach.linkgruppenlinks.first}subfirst {if !$oLink->cRecht|permission}noperm{/if}{/if}">
                                        <a href="{$oLink->cURL}">{$oLink->cLinkname}</a></li>
                                {/foreach}
                            </ul>
                        {/if}
                    </li>
                {/foreach}
                {foreach name=linkuntergruppenlinks from=$oLinkOberGruppe->oLink_arr item=oLink}
                    <li class="{if $smarty.foreach.linkuntergruppenlinks.first}subfirst{/if} {if !$oLink->cRecht|permission}noperm{/if}">
                        <a href="{$oLink->cURL}">{$oLink->cLinkname}</a>
                    </li>
                {/foreach}
            </ul>
        </li>
    {/foreach}
</ul>