{foreach name=linkgrupp from=$list item=link}
<tr {if isset($kPlugin) && $kPlugin > 0 && $kPlugin == $link->kPlugin}class="highlight"{/if}>
	{math equation="a * b" a=$link->nLevel-1 b=20 assign=fac}
	<td>
		<div style="margin-left:{if $fac > 0}{$fac}{else}0{/if}px" {if $link->nLevel > 0 && $link->kVaterLink > 0}class="sub"{/if}>
			{$link->cName}
		</div>
	</td>
	<td class="tcenter"><a href="links.php?kLink={$link->kLink}{if isset($kPlugin) && $kPlugin > 0}&kPlugin={$kPlugin}{/if}" class="button edit">{#modify#}</a></td>
	<td class="tcenter"><a href="links.php?dellink={$link->kLink}{if isset($kPlugin) && $kPlugin > 0}&kPlugin={$kPlugin}{/if}" class="button remove" onclick="return confirmDelete();">{#delete#}</a></td>
	<td class="tcenter floatforms" style="width: 400px">
		
		 <form method="POST" action="links.php" name="aenderlinkgruppe_{$link->kLink}">
			  <input type="hidden" name="{$session_name}" value="{$session_id}" />
			  <input type="hidden" name="aender_linkgruppe" value="1" />
			  <input type="hidden" name="kLink" value="{$link->kLink}" />
			  {if isset($kPlugin) && $kPlugin > 0}
			  <input type="hidden" name="kPlugin" value="{$kPlugin}" />
			  {/if}                     
			  <select name="kLinkgruppe" onchange="javascript:document.forms['aenderlinkgruppe_{$link->kLink}'].submit();">
					<option value="-1">{#linkGruopMove#}</option>
					{foreach name=aenderlinkgruppe from=$linkgruppen item=linkgruppeTMP}
						 {if $linkgruppeTMP->kLinkgruppe != $id}
							  <option value="{$linkgruppeTMP->kLinkgruppe}">{$linkgruppeTMP->cName}</option>
						 {/if}
					{/foreach}
			  </select>
		 </form>
		 
		 <form method="POST" action="links.php" name="aenderlinkvater_{$link->kLink}">
			  <input type="hidden" name="{$session_name}" value="{$session_id}" />
			  <input type="hidden" name="aender_linkvater" value="1" />
			  <input type="hidden" name="kLink" value="{$link->kLink}" />
			  {if isset($kPlugin) && $kPlugin > 0}
			  <input type="hidden" name="kPlugin" value="{$kPlugin}" />
			  {/if}                     
			  <select name="kVaterLink" onchange="javascript:document.forms['aenderlinkvater_{$link->kLink}'].submit();">
					<option value="-1">Unter Link einordnen</option>
					<option value="0">-- Root --</option>
					{foreach from=$linkgruppe->links_nh item=linkTMP}
						 {if $linkTMP->kLink != $link->kLink && $linkTMP->kLink != $link->kVaterLink}
							  <option value="{$linkTMP->kLink}">{$linkTMP->cName}</option>
						 {/if}
					{/foreach}
			  </select>
		 </form>
	</td>
</tr>

{if $link->oSub_arr|@count > 0}
	{include file="tpl_inc/links_uebersicht_item.tpl" list=$link->oSub_arr id=$id}
{/if}

{/foreach}