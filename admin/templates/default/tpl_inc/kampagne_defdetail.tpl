{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: kampagne_defdetail.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2010 JTL-Software
-------------------------------------------------------------------------------
*}

{include file="tpl_inc/seite_header.tpl" cTitel=#kampagneDetailStats#}
<div id="content">
	
	{if isset($hinweis) && $hinweis|count_characters > 0}			
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}			
		<p class="box_error">{$fehler}</p>
	{/if}
	
	<div id="tabellenLivesuche">
		<table>
			<tr>
				<th class="tleft"><strong>{$oKampagneDef->cName}</strong></th> 
			</tr>
			<tr>
				<td class="TD1">
					{#kampagnePeriod#}: {$cStampText}<br />
					{#kampagneOverall#}: {$nGesamtAnzahlDefDetail}
				</td>
			</tr>
		</table>
	</div>
					
	<div id="payment">											
	{if isset($oKampagneStat_arr) && $oKampagneStat_arr|@count > 0 && isset($oKampagneDef->kKampagneDef) && $oKampagneDef->kKampagneDef > 0}
		{if $oBlaetterNaviDefDetail->nAktiv == 1}
		<div class="container">
				<p>
				{$oBlaetterNaviDefDetail->nVon} - {$oBlaetterNaviDefDetail->nBis} {#from#} {$oBlaetterNaviDefDetail->nAnzahl}
				{if $oBlaetterNaviDefDetail->nAktuelleSeite == 1}
					&laquo; {#back#}
				{else}
					<a href="kampagne.php?s1={$oBlaetterNaviDefDetail->nVoherige}&kKampagne={$oKampagne->kKampagne}&defdetail=1&kKampagneDef={$oKampagneDef->kKampagneDef}&cStamp={$cStamp}">&laquo; {#back#}</a>
				{/if}
				
				{if $oBlaetterNaviDefDetail->nAnfang != 0}<a href="kampagne.php?s1={$oBlaetterNaviDefDetail->nAnfang}&kKampagne={$oKampagne->kKampagne}&defdetail=1&kKampagneDef={$oKampagneDef->kKampagneDef}&cStamp={$cStamp}">{$oBlaetterNaviDefDetail->nAnfang}</a> ... {/if}
				{foreach name=blaetternavi from=$oBlaetterNaviDefDetail->nBlaetterAnzahl_arr item=Blatt}
					{if $oBlaetterNaviDefDetail->nAktuelleSeite == $Blatt}[{$Blatt}]
					{else}
						<a href="kampagne.php?s1={$Blatt}&kKampagne={$oKampagne->kKampagne}&defdetail=1&kKampagneDef={$oKampagneDef->kKampagneDef}&cStamp={$cStamp}">{$Blatt}</a>
					{/if}
				{/foreach}
				
				{if $oBlaetterNaviDefDetail->nEnde != 0} ... <a href="kampagne.php?s1={$oBlaetterNaviDefDetail->nEnde}&kKampagne={$oKampagne->kKampagne}&defdetail=1&kKampagneDef={$oKampagneDef->kKampagneDef}&cStamp={$cStamp}">{$oBlaetterNaviDefDetail->nEnde}</a>{/if}
				
				{if $oBlaetterNaviDefDetail->nAktuelleSeite == $oBlaetterNaviDefDetail->nSeiten}
					{#next#} &raquo;
				{else}
					<a href="kampagne.php?s1={$oBlaetterNaviDefDetail->nNaechste}&kKampagne={$oKampagne->kKampagne}&defdetail=1&kKampagneDef={$oKampagneDef->kKampagneDef}&cStamp={$cStamp}">{#next#} &raquo;</a>
				{/if}				
				</p>
		</div>
		{/if}
	
		<div id="tabellenLivesuche">
			<table>
				<tr>
				{foreach name="kampagnendefs" from=$cMember_arr key=cMember item=cMemberAnzeige}
					<th class="th-2">{$cMemberAnzeige|truncate:50:"..."}</th>
				{/foreach}
				</tr>
				
				{foreach name="kampagnenstats" from=$oKampagneStat_arr item=oKampagneStat}
					<tr class="tab_bg{$smarty.foreach.kampagnenstats.iteration%2}">
					{foreach name="kampagnendefs" from=$cMember_arr key=cMember item=cMemberAnzeige}
						<td class="TD1" style="text-align: center;">{$oKampagneStat->$cMember|wordwrap:40:"<br />":true}</td>
					{/foreach}
					</tr>
				{/foreach}
			</table>
		</div>
		
		{if $oBlaetterNaviDefDetail->nAktiv == 1}
		<div class="content">
				<p>
				{$oBlaetterNaviDefDetail->nVon} - {$oBlaetterNaviDefDetail->nBis} {#from#} {$oBlaetterNaviDefDetail->nAnzahl}
				{if $oBlaetterNaviDefDetail->nAktuelleSeite == 1}
					&laquo; {#back#}
				{else}
					<a href="kampagne.php?s1={$oBlaetterNaviDefDetail->nVoherige}&kKampagne={$oKampagne->kKampagne}&defdetail=1&kKampagneDef={$oKampagneDef->kKampagneDef}&cStamp={$cStamp}">&laquo; {#back#}</a>
				{/if}
				
				{if $oBlaetterNaviDefDetail->nAnfang != 0}<a href="kampagne.php?s1={$oBlaetterNaviDefDetail->nAnfang}&kKampagne={$oKampagne->kKampagne}&defdetail=1&kKampagneDef={$oKampagneDef->kKampagneDef}&cStamp={$cStamp}">{$oBlaetterNaviDefDetail->nAnfang}</a> ... {/if}
				{foreach name=blaetternavi from=$oBlaetterNaviDefDetail->nBlaetterAnzahl_arr item=Blatt}
					{if $oBlaetterNaviDefDetail->nAktuelleSeite == $Blatt}[{$Blatt}]
					{else}
						<a href="kampagne.php?s1={$Blatt}&kKampagne={$oKampagne->kKampagne}&defdetail=1&kKampagneDef={$oKampagneDef->kKampagneDef}&cStamp={$cStamp}">{$Blatt}</a>
					{/if}
				{/foreach}
				
				{if $oBlaetterNaviDefDetail->nEnde != 0} ... <a href="kampagne.php?s1={$oBlaetterNaviDefDetail->nEnde}&kKampagne={$oKampagne->kKampagne}&defdetail=1&kKampagneDef={$oKampagneDef->kKampagneDef}&cStamp={$cStamp}">{$oBlaetterNaviDefDetail->nEnde}</a>{/if}
				
				{if $oBlaetterNaviDefDetail->nAktuelleSeite == $oBlaetterNaviDefDetail->nSeiten}
					{#next#} &raquo;
				{else}
					<a href="kampagne.php?s1={$oBlaetterNaviDefDetail->nNaechste}&kKampagne={$oKampagne->kKampagne}&defdetail=1&kKampagneDef={$oKampagneDef->kKampagneDef}&cStamp={$cStamp}">{#next#} &raquo;</a>
				{/if}				
				</p>
		</div>
		{/if}
	{else}
		<div class="box_info">{#noDataAvailable#}</div>
		<div class="container">
			<a href="kampagne.php?{$session_name}={$session_id}&kKampagne={$oKampagne->kKampagne}&detail=1">{#kampagneBackBTN#}</a>
		</div>
	{/if}
	</div>
</div>