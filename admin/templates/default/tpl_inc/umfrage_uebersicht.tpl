{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: umfrage_uebersicht.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
{include file="tpl_inc/seite_header.tpl" cTitel=#umfrage# cBeschreibung=#umfrageDesc#}
<div id="content">	
	{if isset($hinweis) && $hinweis|count_characters > 0}			
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}			
		<p class="box_error">{$fehler}</p>
	{/if}

	{if !isset($noModule) || !$noModule}
		<form name="sprache" method="post" action="umfrage.php">
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
	
		<div class="container">
			<div class="tabber">
				<div class="tabbertab{if isset($cTab) && $cTab == 'umfrage'} tabbertabdefault{/if}">
				<h2>{#umfrageOverview#}</h2>
	
				<form name="erstellen" method="POST" action="umfrage.php" class="container">
					<input type="hidden" name="{$session_name}" value="{$session_id}" />
					<input type="hidden" name="umfrage" value="1" />
					<input type="hidden" name="umfrage_erstellen" value="1" />
					<input type="hidden" name="tab" value="umfrage" />
					<input type="hidden" name="s1" value="{$oBlaetterNaviUmfrage->nAktuelleSeite}" />
					<p class="tcenter">
						<input name="umfrageerstellen" type="submit" value="{#umfrageAdd#}" class="button orange" />
					</p>
				</form>
			
				{if $oUmfrage_arr|@count > 0 && $oUmfrage_arr}
					<form name="umfrage" method="post" action="umfrage.php">
					<input type="hidden" name="{$session_name}" value="{$session_id}" />
					<input type="hidden" name="umfrage" value="1" />
					<input type="hidden" name="umfrage_loeschen" value="1" />
					<input type="hidden" name="tab" value="umfrage" />
					<input type="hidden" name="s1" value="{$oBlaetterNaviUmfrage->nAktuelleSeite}" />
					
					{if $oBlaetterNaviUmfrage->nAktiv == 1}
						<p>
							{$oBlaetterNaviUmfrage->nVon} - {$oBlaetterNaviUmfrage->nBis} {#from#} {$oBlaetterNaviUmfrage->nAnzahl}
							{if $oBlaetterNaviUmfrage->nAktuelleSeite == 1}
								<< {#back#}
							{else}
								<a href="umfrage.php?s1={$oBlaetterNaviUmfrage->nVoherige}&tab=umfrage"><< {#back#}</a>
							{/if}
							
							{if $oBlaetterNaviUmfrage->nAnfang != 0}<a href="umfrage.php?s1={$oBlaetterNaviUmfrage->nAnfang}&tab=umfrage">{$oBlaetterNaviUmfrage->nAnfang}</a> ... {/if}
							{foreach name=blaetternavi from=$oBlaetterNaviUmfrage->nBlaetterAnzahl_arr item=Blatt}
								{if $oBlaetterNaviUmfrage->nAktuelleSeite == $Blatt}
									[{$Blatt}]
								{else}
									<a href="umfrage.php?s1={$Blatt}&tab=umfrage">{$Blatt}</a>
								{/if}
							{/foreach}
						
							{if $oBlaetterNaviUmfrage->nEnde != 0} ... <a href="umfrage.php?s1={$oBlaetterNaviUmfrage->nEnde}&tab=umfrage">{$oBlaetterNaviUmfrage->nEnde}</a>{/if}
						
							{if $oBlaetterNaviUmfrage->nAktuelleSeite == $oBlaetterNaviUmfrage->nSeiten}
								{#next#} >>
							{else}
								<a href="umfrage.php?s1={$oBlaetterNaviUmfrage->nNaechste}&tab=umfrage">{#next#} >></a>
							{/if}
						</p>
					{/if}
					
					<div id="payment">
					<div id="tabellenLivesuche">
					<table>
					<tr>
					<th class="th-1"></th>
					<th class="th-2">{#umfrageName#}</th>
					<th class="th-3">{#umfrageCustomerGrp#}</th>
					<th class="th-4">{#umfrageValidation#}</th>
					<th class="th-5">{#umfrageActive#}</th>
					<th class="th-6">{#umfrageQCount#}</th>
					<th class="th-7">{#umfrageDate#}</th>
					<th class="th-8"></th>
					</tr>
					{foreach name=umfrage from=$oUmfrage_arr item=oUmfrage}
					<tr class="tab_bg{$smarty.foreach.umfrage.iteration%2}">
					<td class="TD1"><input type="checkbox" name="kUmfrage[]" value="{$oUmfrage->kUmfrage}" /></td>
					<td class="TD2"><a href="umfrage.php?umfrage=1{if $oBlaetterNaviUmfrage->nAktuelleSeite}&s1={$oBlaetterNaviUmfrage->nAktuelleSeite}{/if}&ud=1&kUmfrage={$oUmfrage->kUmfrage}&{$SID}&tab=umfrage">{$oUmfrage->cName}</a></td>
					<td class="TD3">
					{foreach name=kundengruppen from=$oUmfrage->cKundengruppe_arr item=cKundengruppe}    
						 {$cKundengruppe}{if !$smarty.foreach.kundengruppen.last},{/if}
					{/foreach}
					</td>
					<td class="TD4">{$oUmfrage->dGueltigVon_de}-{if $oUmfrage->dGueltigBis|truncate:10:"" == "0000-00-00"}{#umfrageInfinite#}{else}{$oUmfrage->dGueltigBis_de}{/if}</td>
					<td class="TD5">{$oUmfrage->nAktiv}</td>
					<td class="TD6">{$oUmfrage->nAnzahlFragen}</td>
					<td class="TD7">{$oUmfrage->dErstellt_de}</td>
					<td class="TD8"><a href="umfrage.php?umfrage=1{if $oBlaetterNaviUmfrage->nAktuelleSeite}&s1={$oBlaetterNaviUmfrage->nAktuelleSeite}{/if}&umfrage_editieren=1&kUmfrage={$oUmfrage->kUmfrage}&{$session_name}={$session_id}&tab=umfrage" class="button orange">{#umfrageEdit#}</a> <a href="umfrage.php?umfrage=1&kUmfrage={$oUmfrage->kUmfrage}&umfrage_statistik=1" class="button orange">{#umfrageStats#}</a></td>
					</tr>
					{/foreach}
					</table>
					</div>
					</div>                    
					<p class="submit"><input name="loeschen" type="submit" class="button orange" value="{#umfrageDelete#}" /></p>
					</form>
				{else}
					<p class="box_info">{#noDataAvailable#}</p>
				{/if}
				</div>
				
				{if isset($oConfig_arr) && $oConfig_arr|@count > 0}
					<div class="tabbertab{if isset($cTab) && $cTab == 'einstellungen'} tabbertabdefault{/if}">
						<h2>{#umfrageSettings#}</h2>
						<form name="einstellen" method="post" action="umfrage.php">
							<input type="hidden" name="{$session_name}" value="{$session_id}" />
							<input type="hidden" name="einstellungen" value="1" />
							<input type="hidden" name="tab" value="einstellungen" />
							<div class="settings">
								{foreach name=conf from=$oConfig_arr item=oConfig}
								{if $oConfig->cConf == "Y"}
								<p><label for="{$oConfig->cWertName}">({$oConfig->kEinstellungenConf}) {$oConfig->cName} {if $oConfig->cBeschreibung}<img src="{$currentTemplateDir}gfx/help.png" alt="{$oConfig->cBeschreibung}" title="{$oConfig->cBeschreibung}" style="vertical-align:middle; cursor:help;" /></label>
								{/if}
								{if $oConfig->cInputTyp=="selectbox"}
								<select name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" class="combo"> 
								{foreach name=selectfor from=$oConfig->ConfWerte item=wert}
									 <option value="{$wert->cWert}" {if $oConfig->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
								{/foreach}
								</select>
								{elseif $oConfig->cInputTyp=="listbox"}
								<select name="{$oConfig->cWertName}[]" id="{$oConfig->cWertName}" multiple="multiple" class="combo"> 
								{foreach name=selectfor from=$oConfig->ConfWerte item=wert}
									 <option value="{$wert->kKundengruppe}" {foreach name=werte from=$oConfig->gesetzterWert item=gesetzterWert}{if $gesetzterWert->cWert == $wert->kKundengruppe}selected{/if}{/foreach}>{$wert->cName}</option>
								{/foreach}
								</select>
								{else}
								<input type="text" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}"  value="{$oConfig->gesetzterWert}" tabindex="1" /></p>
								{/if}
								{else}
								{if $oConfig->cName}<h3 style="text-align:center;">({$oConfig->kEinstellungenConf}) {$oConfig->cName}</h3>{/if}
								{/if}
								{/foreach}
							</div>
							<p class="submit"><input type="submit" value="{#umfrageSave#}" class="button orange" /></p>
						</form>
					</div>
				{/if}
			</div>
		</div>
	{else}
		<p class="box_error">{#noModuleAvailable#}</p>
	{/if}
</div>