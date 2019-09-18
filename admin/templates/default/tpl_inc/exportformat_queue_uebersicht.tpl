{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: exportformat_queue_uebersicht.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}

{include file="tpl_inc/seite_header.tpl" cTitel=#exportformat# cBeschreibung=#exportformatDesc# cDokuURL=#exportformatUrl#}
<div id="content">
	
	{if isset($hinweis) && $hinweis|count_characters > 0}			
		 <p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}			
		 <p class="box_error">{$fehler}</p>
	{/if}
	
	<form method="POST" action="exportformat_queue.php">
	<input type="hidden" name="{$session_name}" value="{$session_id}">
		<input name="navigation" type="hidden" value="1">
		<input name="submitErstellen" type="submit" value="{#exportformatAdd#}" class="button add">                        
        <input name="submitFertiggestellt" type="submit" value="{#exportformatTodaysWork#}" class="button">
        <input name="submitCronTriggern" type="submit" value="{#exportformatTriggerCron#}" class="button">
	</form>
				
{if $oExportformatCron_arr|@count > 0 && $oExportformatCron_arr}
		<!-- Übersicht ExportformatQueue -->
		<form method="POST" action="exportformat_queue.php">
		<input type="hidden" name="{$session_name}" value="{$session_id}">
		<input name="loeschen" type="hidden" value="1">
	 
		<div class="category">
			{#exportformatQueue#}
	</div>
	
	  <div id="payment">
		<div id="tabellenLivesuche">
		<table>
			<tr>
				<th class="tleft" style="width: 10px;">&nbsp;</th>
				<th class="tleft">{#exportformatFormatSingle#}</th>
				<th class="tleft">{#exportformatOptions#}</th>
				<th class="tcenter">{#exportformatStart#}</th>
				<th class="tcenter">{#exportformatEveryXHourShort#}</th>
				<th class="tcenter">{#exportformatExported#}</th>
				<th class="tcenter">{#exportformatLastStart#}</th>
                <th class="tcenter">{#exportformatNextStart#}</th>
				<th class="tcenter">&nbsp;</th>
			</tr>
		{foreach name=exportformatqueue from=$oExportformatCron_arr item=oExportformatCron}
			<tr class="tab_bg{$smarty.foreach.exportformatqueue.iteration%2}">
				<td class="tleft"><input name="kCron[]" type="checkbox" value="{$oExportformatCron->kCron}"></td>
				<td class="tleft">{$oExportformatCron->cName}</td>
				<td class="tleft">{$oExportformatCron->Sprache->cNameDeutsch} / {$oExportformatCron->Waehrung->cName} / {$oExportformatCron->Kundengruppe->cName}</td>
				<td class="tcenter">{$oExportformatCron->dStart_de}</td>
				<td class="tcenter">{$oExportformatCron->cAlleXStdToDays}</td>
				<td class="tcenter">{if isset($oExportformatCron->oJobQueue->nLimitN) && $oExportformatCron->oJobQueue->nLimitN > 0}{$oExportformatCron->oJobQueue->nLimitN}{else}0{/if} von {if $oExportformatCron->nSpecial == "1"}{$oExportformatCron->nAnzahlArtikelYatego->nAnzahl}{else}{$oExportformatCron->nAnzahlArtikel->nAnzahl}{/if}</td>
				<td class="tcenter">{$oExportformatCron->dLetzterStart_de}</td>
                <td class="tcenter">{$oExportformatCron->dNaechsterStart_de}</td>
				<td class="tcenter"><a href="exportformat_queue.php?{$session_name}={$session_id}&editieren=1&kCron={$oExportformatCron->kCron}" class="button edit">{#exportformatEdit#}</a></td>
			</tr>
		{/foreach}
				 <tr>
					  <td class="TD1"><input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);"></td>
					  <td colspan="7" class="TD7">{#globalSelectAll#}</td>
				 </tr>
		</table>
		</div>
		
		<p class="submit"><input name="submitloeschen" type="submit" value="{#exportformatDelete#}" class="button orange"></p>
	</div>
	</form>
{/if}	

</div>