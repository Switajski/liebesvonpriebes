{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: tagging_tagdetail.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}

{include file="tpl_inc/seite_header.tpl" cTitel=#taggingdetail# cBeschreibung=#taggingdetailDesc#}
<div id="content">
	
	{if isset($hinweis) && $hinweis|count_characters > 0}
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}
		<p class="box_error">{$fehler}</p>
	{/if}
	  
	  
	  <div class="container">
				 <p>{#taggingdetailTag#} <strong>{$cTagName}</strong></p>
	  </div>
	
{if $oTagArtikel_arr}        
	{if $oBlaetterNaviTagsDetail->nAktiv == 1}
		<div class="container">
				<p>
				{$oBlaetterNaviTagsDetail->nVon} - {$oBlaetterNaviTagsDetail->nBis} {#from#} {$oBlaetterNaviTagsDetail->nAnzahl}
				{if $oBlaetterNaviTagsDetail->nAktuelleSeite == 1}
					<< {#back#}
				{else}
					<a href="tagging.php?s2={$oBlaetterNaviTagsDetail->nVoherige}&tagdetail=1&kTag={$kTag}&{$SID}"><< {#back#}</a>
				{/if}
				
				{if $oBlaetterNaviTagsDetail->nAnfang != 0}<a href="tagging.php?s2={$oBlaetterNaviTagsDetail->nAnfang}&tagdetail=1&kTag={$kTag}&{$SID}">{$oBlaetterNaviTagsDetail->nAnfang}</a> ... {/if}
				{foreach name=blaetternavi from=$oBlaetterNaviTagsDetail->nBlaetterAnzahl_arr item=Blatt}
					{if $oBlaetterNaviTagsDetail->nAktuelleSeite == $Blatt}[{$Blatt}]
					{else}
						<a href="tagging.php?s2={$Blatt}&tagdetail=1&kTag={$kTag}&{$SID}">{$Blatt}</a>
					{/if}
				{/foreach}
				
				{if $oBlaetterNaviTagsDetail->nEnde != 0} ... <a href="tagging.php?s2={$oBlaetterNaviTagsDetail->nEnde}&tagdetail=1&kTag={$kTag}&{$SID}">{$oBlaetterNaviTagsDetail->nEnde}</a>{/if}
				
				{if $oBlaetterNaviTagsDetail->nAktuelleSeite == $oBlaetterNaviTagsDetail->nSeiten}
					{#next#} >>
				{else}
					<a href="tagging.php?s2={$oBlaetterNaviTagsDetail->nNaechste}&tagdetail=1&kTag={$kTag}&{$SID}">{#next#} >></a>
				{/if}
				
				</p>
		</div>
	{/if}
	
	<!-- Tag Detailansicht -->
	<form method="POST" action="tagging.php">
		<input name="detailloeschen" type="hidden" value="1">
	  <input name="tagdetail" type="hidden" value="1">
		<input type="hidden" name="{$session_name}" value="{$session_id}">
	  <input type="hidden" name="kTag" value="{$kTag}">
		
	  <div id="payment">
		<div id="tabellenLivesuche">
		<table>
			<tr>
				<th class="check" >&nbsp;</th>
				<th class="th-2" >{#taggingProduct#}</th>
			</tr>
		{foreach name=tagdetail from=$oTagArtikel_arr item=oTagArtikel}
			<tr class="tab_bg{$smarty.foreach.tagdetail.iteration%2}">
				<td class="check"><input name="kArtikel_arr[]" type="checkbox" value="{$oTagArtikel->kArtikel}"></td>
				<td class="TD2"><a href="{$oTagArtikel->cURL}">{$oTagArtikel->acName}</a></td>
			</tr>
		{/foreach}
				 <tr>
					  <td class="check"><input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);" /></td>
					  <td colspan="5" class="TD7">{#freischaltenSelectAll#}</td>
				 </tr>
		</table>
		</div>
	</div>
	<p class="submit"><input name="loeschen" type="submit" value="{#taggingdelete#}" class="button orange"></p>
	</form>
{/if}
</div>