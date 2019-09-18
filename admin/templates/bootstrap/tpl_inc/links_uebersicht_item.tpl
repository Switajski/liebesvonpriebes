{foreach name=linkgrupp from=$list item=link}
    <tr {if isset($kPlugin) && $kPlugin > 0 && $kPlugin == $link->kPlugin}class="highlight"{/if}{if $link->nLevel == 0}class="main"{/if}>
        {math equation="a * b" a=$link->nLevel-1 b=20 assign=fac}
        <td style="width: 40%">
            <div style="margin-left:{if $fac > 0}{$fac}px{else}0{/if}; padding-top: 7px" {if $link->nLevel > 0 && $link->kVaterLink > 0}class="sub"{/if}>
                {$link->cName}
            </div>
        </td>
        <td class="tcenter floatforms" style="width: 50%">
            <form class="navbar-form2 p33 left" method="post" action="links.php" name="aenderlinkgruppe_{$link->kLink}_{$link->kLinkgruppe}">
                {$jtl_token}
                <input type="hidden" name="aender_linkgruppe" value="1" />
                <input type="hidden" name="kLink" value="{$link->kLink}" />
                <input type="hidden" name="kLinkgruppeAlt" value="{$link->kLinkgruppe}" />
                {if isset($kPlugin) && $kPlugin > 0}
                    <input type="hidden" name="kPlugin" value="{$kPlugin}" />
                {/if}
                <select class="form-control" name="kLinkgruppe" onchange="document.forms['aenderlinkgruppe_{$link->kLink}_{$link->kLinkgruppe}'].submit();">
                    <option value="-1">{#linkGroupMove#}</option>
                    {foreach name=aenderlinkgruppe from=$linkgruppen item=linkgruppeTMP}
                        {if $linkgruppeTMP->kLinkgruppe != $id}
                            <option value="{$linkgruppeTMP->kLinkgruppe}">{$linkgruppeTMP->cName}</option>
                        {/if}
                    {/foreach}
                </select>
            </form>
            <form class="navbar-form2 p33 left" method="post" action="links.php" name="kopiereinlinkgruppe_{$link->kLink}_{$link->kLinkgruppe}">
                {$jtl_token}
                <input type="hidden" name="kopiere_in_linkgruppe" value="1" />
                <input type="hidden" name="kLink" value="{$link->kLink}" />
                {if isset($kPlugin) && $kPlugin > 0}
                    <input type="hidden" name="kPlugin" value="{$kPlugin}" />
                {/if}
                <select class="form-control" name="kLinkgruppe" onchange="document.forms['kopiereinlinkgruppe_{$link->kLink}_{$link->kLinkgruppe}'].submit();">
                    <option value="-1">{#linkGroupCopy#}</option>
                    {foreach name=kopiereinlinkgruppe from=$linkgruppen item=linkgruppeTMP}
                        {if $linkgruppeTMP->kLinkgruppe != $id}
                            <option value="{$linkgruppeTMP->kLinkgruppe}">{$linkgruppeTMP->cName}</option>
                        {/if}
                    {/foreach}
                </select>
            </form>
            <form class="navbar-form2 p33 left" method="post" action="links.php" name="aenderlinkvater_{$link->kLink}_{$link->kLinkgruppe}">
                {$jtl_token}
                <input type="hidden" name="aender_linkvater" value="1" />
                <input type="hidden" name="kLink" value="{$link->kLink}" />
                <input type="hidden" name="kLinkgruppe" value="{$link->kLinkgruppe}" />
                {if isset($kPlugin) && $kPlugin > 0}
                    <input type="hidden" name="kPlugin" value="{$kPlugin}" />
                {/if}

                <select class="form-control" name="kVaterLink" onchange="document.forms['aenderlinkvater_{$link->kLink}_{$link->kLinkgruppe}'].submit();">
                    <option value="-1">Unter Link einordnen</option>
                    <option value="0">-- Root --</option>
                    {foreach from=$linkgruppe->links_nh item=linkTMP}
                        {if $linkTMP->kLink != $link->kLink && $linkTMP->kLink != $link->kVaterLink && $linkTMP->kVaterLink !== $link->kLink}
                            <option value="{$linkTMP->kLink}">{$linkTMP->cName}</option>
                        {/if}
                    {/foreach}
                </select>
            </form>
        </td>
        <td class="tcenter" style="width: 10%;min-width: 95px;">
            <form method="post" action="links.php">
                {$jtl_token}
                {if isset($kPlugin) && $kPlugin > 0}
                    <input type="hidden" name="kPlugin" value="{$kPlugin}" />
                {/if}
                <input type="hidden" name="kLinkgruppe" value="{$link->kLinkgruppe}" />
                <div class="btn-group">
                    <button name="kLink" value="{$link->kLink}" class="btn btn-default" title="{#modify#}"><i class="fa fa-edit"></i></button>
                    <button name="dellink" value="{$link->kLink}" class="btn btn-danger{if isset($link->kPlugin) && !empty($link->kPlugin)} disabled{/if}" onclick="return confirmDelete();" title="{#delete#}"><i class="fa fa-trash"></i></button>
                </div>
            </form>
        </td>
    </tr>
    {if $link->oSub_arr|@count > 0}
        {include file="tpl_inc/links_uebersicht_item.tpl" list=$link->oSub_arr id=$id}
    {/if}
{/foreach}