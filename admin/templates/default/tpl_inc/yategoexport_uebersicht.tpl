{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: yategoexport_uebersicht.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}

<div id="content">
	{if !$Exportformat->kExportformat}
		{include file="tpl_inc/seite_header.tpl" cTitel=#newExportformat# cDokuURL=#yategoURL#}
	{else}
		{include file="tpl_inc/seite_header.tpl" cTitel=#modifyExportformat# cDokuURL=#yategoURL#}
	{/if}
	
	{if isset($hinweis) && $hinweis|count_characters > 0}			
		<p class="container box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}			
		<p class="container box_error">{$fehler}</p>
	{/if}
        
    {if !$bYategoSchreibbar}
        <p class="container box_error">Leider ist das Verzeichnis "{$PFAD_EXPORT_YATEGO}" nicht beschreibbar. Bitte pr&uuml;fen Sie Ihre Schreibrechte!</p>
    {/if}

{if $bWaehrungsCheck}        
	<form name="wxportformat_erstellen" method="post" action="yatego.export.php">
	<div class="container">
		<input type="hidden" name="{$session_name}" value="{$session_id}" />
		<input type="hidden" name="yatego" value="1" />
		<input type="hidden" name="kExportformat" value="{$Exportformat->kExportformat}" />
		
		<div class="settings">
			<p><label for="cName">{#name#}</label>
			<input type="text" name="cName" id="cName"  value="{$Exportformat->cName}" tabindex="1" /></p>
			
			<p><label for="kSprache">{#language#}</label>
			<select name="kSprache" id="kSprache" class="combo">
			{foreach name=sprache from=$oSprachen item=sprache}
				<option value="{$sprache->kSprache}" {if $Exportformat->kSprache==$sprache->kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
			{/foreach}
			</select></p> 
			
            {*
			<p><label for="kWaehrung">{#currency#}</label>
			<select name="kWaehrung" id="kWaehrung" class="combo">
			{foreach name=waehrung from=$waehrungen item=waehrung}
				<option value="{$waehrung->kWaehrung}" {if $Exportformat->kWaehrung==$waehrung->kWaehrung}selected{/if}>{$waehrung->cName}</option>
			{/foreach}
			</select></p>
            *}
			
			<p><label for="kKampagne">{#campaigns#}</label>
				 <select name="kKampagne" id="kKampagne" class="combo">
				<option value="0"></option>
				  {foreach name=kampagnen from=$oKampagne_arr item=oKampagne}
				<option value="{$oKampagne->kKampagne}" {if $Exportformat->kKampagne == $oKampagne->kKampagne}selected{/if}>{$oKampagne->cName}</option>
				{/foreach}
			</select></p>
			
			<p><label for="kKundengruppe">{#customerGruop#}</label>
			<select name="kKundengruppe" id="kKundengruppe" class="combo">
			{foreach name=kdgrp from=$kundengruppen item=kdgrp}
				<option value="{$kdgrp->kKundengruppe}" {if $Exportformat->kKundengruppe==$kdgrp->kKundengruppe}selected{/if}>{$kdgrp->cName}</option>
			{/foreach}
			</select></p>
			<p><label for="cKodierung">{#encoding#}</label>
				 <select name="cKodierung" id="cKodierung" class="combo">
					<option value="ASCII" {if $Exportformat->cKodierung == "ASCII"}selected{/if}>ASCII</option>
					<option value="UTF-8" {if $Exportformat->cKodierung == "UTF-8"}selected{/if}>UTF-8</option>
			  </select></p>
              
             {* 
			  <p><label for="nVarKombiOption">{#varikombiOption#}</label>
				 <select name="nVarKombiOption" id="nVarKombiOption" class="combo">
					<option value="1" {if $Exportformat->nVarKombiOption == 1}selected{/if}>{#varikombiOption1#}</option>
					<option value="2" {if $Exportformat->nVarKombiOption == 2}selected{/if}>{#varikombiOption2#}</option>
					<option value="3" {if $Exportformat->nVarKombiOption == 3}selected{/if}>{#varikombiOption3#}</option>
			  </select></p>
              *}

			<div class="category">{#settings#}</div>
            
			
			{foreach name=conf from=$oConfig_arr item=cnf}
				{if $cnf->cConf=="Y"}
					<p><label for="{$cnf->cWertName}">{$cnf->cName} {if $cnf->cBeschreibung}<img src="{$currentTemplateDir}gfx/help.png" alt="{$cnf->cBeschreibung}" title="{$cnf->cBeschreibung}" style="vertical-align:middle; cursor:help;" />{/if}</label>
					{if $cnf->cInputTyp=="selectbox"}
						<select name="{$cnf->cWertName}" id="{$cnf->cWertName}" class="combo">
						{foreach name=selectfor from=$cnf->ConfWerte item=wert}
							<option value="{$wert->cWert}" {if $cnf->gesetzterWert==$wert->cWert}selected{/if}>{$wert->cName}</option>
						{/foreach}
						</select>
					{else}
						<input type="text" name="{$cnf->cWertName}" id="{$wert->cWert}"   value="{$cnf->gesetzterWert}" tabindex="3" />
					{/if}</p>
				{else}        
					<h3 style="text-align:center;">{$cnf->cName}</h3>
				{/if}
			{/foreach}           
		</div>   
		<div style="clear:both"></div>         
		<p class="submit"><input name="einstellungensubmit" type="submit" value="{#saveSettings#}" class="button orange" /> {if $bYategoSchreibbar}<input name="expotieresubmit" type="submit" value="{#createExportFile#}" class="button orange" />{/if}</p>
	</div>
	</form>
{else}
    <p class="container box_error">Sie ben&ouml;tigen die W&auml;hrung <b>EUR</b> damit der Yategoexport funktioniert. Bitte pr&uuml;fen Sie in der JTL-Wawi Ihre W&auml;hrungen!</p>
{/if}
</div>