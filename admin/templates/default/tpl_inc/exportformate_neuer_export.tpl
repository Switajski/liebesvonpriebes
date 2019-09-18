{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: exportformate_neuer_export.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: JTL-Software-GmbH
	http://www.jtl-software.de
	
	Copyright (c) 2007 JTL-Software
    

-------------------------------------------------------------------------------
*}
{if !$Exportformat->kExportformat}
{include file="tpl_inc/seite_header.tpl" cTitel=#newExportformat#}
{else}
{include file="tpl_inc/seite_header.tpl" cTitel=#modifyExportformat#}{/if}
<div id="content">
 
{if isset($hinweis) && $hinweis|count_characters > 0}
	<p class="box_success">{$hinweis}</p>
{/if}
{if isset($fehler) && $fehler|count_characters > 0}
	<p class="box_error">{$fehler}</p>
{/if}

<form name="wxportformat_erstellen" method="post" action="exportformate.php">
<div class="container">
 <input type="hidden" name="{$session_name}" value="{$session_id}" />
 <input type="hidden" name="neu_export" value="1" />
 <input type="hidden" name="kExportformat" value="{$Exportformat->kExportformat}" />
{if isset($Exportformat->bPluginContentFile) && $Exportformat->bPluginContentFile}
 <input type="hidden" name="bPluginContentFile" value="1" />
{/if}
<div class="settings">
  <p><label for="cName">{#name#}</label>
		<input type="text" name="cName" id="cName" value="{if isset($cPostVar_arr.cName)}{$cPostVar_arr.cName}{elseif isset($Exportformat->cName)}{$Exportformat->cName}{/if}" tabindex="1" />
	{if isset($cPlausiValue_arr.cName)}<font class="fillout">{#FillOut#}</font>{/if}</p>
		 <p><label for="kSprache">{#language#}</label>
  <select name="kSprache" id="kSprache">
{foreach name=sprache from=$sprachen item=sprache}
<option value="{$sprache->kSprache}" {if $Exportformat->kSprache==$sprache->kSprache || (isset($cPlausiValue_arr.kSprache) && $cPlausiValue_arr.kSprache == $sprache->kSprache)}selected{/if}>{$sprache->cNameDeutsch}</option>
{/foreach}
</select></p> 
			 <p><label for="kWaehrung">{#currency#}</label>
  <select name="kWaehrung" id="kWaehrung">
	{foreach name=waehrung from=$waehrungen item=waehrung}
 <option value="{$waehrung->kWaehrung}" {if $Exportformat->kWaehrung==$waehrung->kWaehrung || (isset($cPlausiValue_arr.kWaehrung) && $cPlausiValue_arr.cName == $waehrung->kWaehrung)}selected{/if}>{$waehrung->cName}</option>
 {/foreach}
</select></p>
<p><label for="kKampagne">{#campaigns#}</label>
  <select name="kKampagne" id="kKampagne">
 <option value="0"></option>
	{foreach name=kampagnen from=$oKampagne_arr item=oKampagne}
 <option value="{$oKampagne->kKampagne}" {if $Exportformat->kKampagne == $oKampagne->kKampagne || (isset($cPlausiValue_arr.kKampagne) && $cPlausiValue_arr.kKampagne == $oKampagne->kKampagne)}selected{/if}>{$oKampagne->cName}</option>
 {/foreach}
</select></p>		
			 <p><label for="kKundengruppe">{#customerGruop#}</label>
  <select name="kKundengruppe" id="kKundengruppe">
	{foreach name=kdgrp from=$kundengruppen item=kdgrp}
 <option value="{$kdgrp->kKundengruppe}" {if $Exportformat->kKundengruppe==$kdgrp->kKundengruppe || (isset($cPlausiValue_arr.kKundengruppe) && $cPlausiValue_arr.kKundengruppe == $kdgrp->kKundengruppe)}selected{/if}>{$kdgrp->cName}</option>
{/foreach}
</select></p>        
<p><label for="cKodierung">{#encoding#}</label>
  <select name="cKodierung" id="cKodierung">
	 <option value="ASCII" {if $Exportformat->cKodierung == "ASCII" || (isset($cPlausiValue_arr.cKodierung) && $cPlausiValue_arr.cKodierung == "ASCII")}selected{/if}>ASCII</option>
	 <option value="UTF-8" {if $Exportformat->cKodierung == "UTF-8" || (isset($cPlausiValue_arr.cKodierung) && $cPlausiValue_arr.cKodierung == "UTF-8")}selected{/if}>UTF-8</option>
</select></p>

<p><label for="nVarKombiOption">{#varikombiOption#}</label>
  <select name="nVarKombiOption" id="nVarKombiOption">
	 <option value="1" {if $Exportformat->nVarKombiOption == 1 || (isset($cPlausiValue_arr.nVarKombiOption) && $cPlausiValue_arr.nVarKombiOption == 1)}selected{/if}>{#varikombiOption1#}</option>
	 <option value="2" {if $Exportformat->nVarKombiOption == 2 || (isset($cPlausiValue_arr.nVarKombiOption) && $cPlausiValue_arr.nVarKombiOption == 2)}selected{/if}>{#varikombiOption2#}</option>
	 <option value="3" {if $Exportformat->nVarKombiOption == 3 || (isset($cPlausiValue_arr.nVarKombiOption) && $cPlausiValue_arr.nVarKombiOption == 3)}selected{/if}>{#varikombiOption3#}</option>
</select></p>

<p><label for="nSplitgroesse">{#splitSize#}</label>
  <input type="text" name="nSplitgroesse" id="nSplitgroesse"  value="{if isset($cPostVar_arr.nSplitgroesse)}{$cPostVar_arr.nSplitgroesse}{elseif isset($Exportformat->nSplitgroesse)}{$Exportformat->nSplitgroesse}{/if}" tabindex="2" /></p>

	 <p><label for="cDateiname">{#filename#}</label>
  <input type="text" name="cDateiname" id="cDateiname" class="{if isset($cPlausiValue_arr.cDateiname)}fieldfillout{/if}" value="{if isset($cPostVar_arr.cDateiname)}{$cPostVar_arr.cDateiname}{elseif isset($Exportformat->cDateiname)}{$Exportformat->cDateiname}{/if}" tabindex="2" />
{if isset($cPlausiValue_arr.cDateiname)}<font class="fillout">{#FillOut#}</font>{/if}</p>

{if !isset($Exportformat->bPluginContentFile)|| !$Exportformat->bPluginContentFile}
 	<p><label for="cKopfzeile">{#header#} <img src="{$currentTemplateDir}gfx/help.png" alt="{#onlyIfNeeded#}" title="{#onlyIfNeeded#}" style="vertical-align:middle; cursor:help;" /></label>
	<textarea name="cKopfzeile" id="cKopfzeile" class="codemirror smarty field">{if isset($cPostVar_arr.cKopfzeile)}{$cPostVar_arr.cKopfzeile}{elseif isset($Exportformat->cKopfzeile)}{$Exportformat->cKopfzeile}{/if}</textarea></p>
			

	<p><label for="cContent">{#template#} <img src="{$currentTemplateDir}gfx/help.png" alt="{#smartyRules#}" title="{#smartyRules#}" style="vertical-align:middle; cursor:help;" /></label>
	<textarea name="cContent" id="cContent" class="codemirror smarty field{if isset($oSmartyError)}fillout{/if}" >{if isset($cPostVar_arr.cContent)}{$cPostVar_arr.cContent}{elseif isset($Exportformat->cContent)}{$Exportformat->cContent}{/if}</textarea></p>

	<p><label for="cFusszeile">{#footer#} <img src="{$currentTemplateDir}gfx/help.png" alt="{#onlyIfNeededFooter#}" title="{#onlyIfNeededFooter#}" style="vertical-align:middle; cursor:help;" /></label>
	<textarea name="cFusszeile" id="cFusszeilet" class="codemirror smarty field">{if isset($cPostVar_arr.cFusszeile)}{$cPostVar_arr.cFusszeile}{elseif isset($Exportformat->cFusszeile)}{$Exportformat->cFusszeile}{/if}</textarea></p>
{else}
	<input name="cContent" type="hidden" value="{if isset($Exportformat->cContent)}{$Exportformat->cContent}{/if}" />
{/if}		
		<h3 style="text-align:center;">{#settings#}</h3>
		
{foreach name=conf from=$Conf item=cnf}
{if $cnf->cConf=="Y"}
<p><label for="{$wert->cWert}">{$cnf->cName} {if $cnf->cBeschreibung}<img src="{$currentTemplateDir}gfx/help.png" alt="{$cnf->cBeschreibung}" title="{$cnf->cBeschreibung}" style="vertical-align:middle; cursor:help;" />{/if}</label>
{if $cnf->cInputTyp=="selectbox"}
		<select name="{$cnf->cWertName}" id="{$wert->cWert}">
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
<p class="submit" style="margin-top:10px;"><input type="submit" class="button orange" value="{if !$Exportformat->kExportformat}{#newExportformatSave#}{else}{#modifyExportformatSave#}{/if}" /></p>
 </div>
	 </form>