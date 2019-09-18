{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: news_uebersicht.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
{include file="tpl_inc/seite_header.tpl" cTitel=#agbwrb# cDokuURL=#agbwrbURL#}
<div id="content">
	
	{if isset($hinweis) && $hinweis|count_characters > 0}			
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}			
		<p class="box_error">{$fehler}</p>
	{/if}
	
	<form name="sprache" method="post" action="agbwrb.php">
	<div class="block tcenter">
	<label for="{#changeLanguage#}">{#changeLanguage#}:</strong></label>
	<input type="hidden" name="sprachwechsel" value="1" />
	<select id="{#changeLanguage#}" name="kSprache" class="selectBox" onchange="javascript:document.sprache.submit();">
	{foreach name=sprachen from=$Sprachen item=sprache}
	<option value="{$sprache->kSprache}" {if $sprache->kSprache==$smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
	{/foreach}
	</select>
	</div>
	</form>
		
	<table class="list container">
		<thead>
		<tr>
			<th class="tleft">{#agbwrbCustomerGrp#}</th>
			<th>{#agbwrbStandard#}</th>
			<th></th>
		</tr>
		</thead>
		<tbody>
		{foreach name=kundengruppe from=$oKundengruppe_arr item=oKundengruppe}
			{assign var=kKundengruppe value=$oKundengruppe->kKundengruppe}
			<tr class="tab_bg{$smarty.foreach.kundengruppe.iteration%2}">
				<td class="">{$oKundengruppe->cName}</td>
				<td class="tcenter">{if isset($oAGBWRB_arr[$kKundengruppe]->nStandard) && $oAGBWRB_arr[$kKundengruppe]->nStandard > 0}*{else}{/if}</td>
				<td class="tcenter"><a href="agbwrb.php?agbwrb=1&agbwrb_edit=1&kKundengruppe={$oKundengruppe->kKundengruppe}&{$session_name}={$session_id}" class="button edit">{#agbwrbEdit#}</a></td>
			</tr>
		{/foreach}
		</tbody>
	</table>
</div>