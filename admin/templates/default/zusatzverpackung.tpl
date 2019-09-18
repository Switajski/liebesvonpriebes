{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: zusatzverpackung.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
{config_load file="$lang.conf" section="zusatzverpackung"}
{include file='tpl_inc/header.tpl'}

<script type="text/javascript" src="templates/default/js/versandart_bruttonetto.js"></script>

{include file="tpl_inc/seite_header.tpl" cTitel=#zusatzverpackung# cBeschreibung=#zusatzverpackungDesc# cDokuURL=#zusatzverpackungURL#}
<div id="content">
	 {if isset($hinweis) && $hinweis|count_characters > 0}			
		  <p class="box_success">{$hinweis}</p>
	 {/if}
	 {if isset($fehler) && $fehler|count_characters > 0}			
		  <p class="box_error">{$fehler}</p>
	 {/if}
	<br>
	
{if $step == "anzeigen"}

		<table class="container list">
			<thead>
			<tr>
				<th class="th-1">{#zusatzverpackungName#}</th>
				<th class="th-2">{#zusatzverpackungISOLang#}</th>
				<th class="th-3">{#zusatzverpackungDescLang#}</th>
			</tr>
			</thead>
			<tbody>
		{foreach name=verpackungensprache from=$oVerpackungSprache_arr item=oVerpackungSprache}
			<tr class="tab_bg{$smarty.foreach.verpackungensprache.iteration%2}">
				<td class="TD1">{$oVerpackungSprache->cName}</td>
				<td class="TD2">{$oVerpackungSprache->cISOSprache}</td>
				<td class="TD3">{$oVerpackungSprache->cBeschreibung}</td>
			</tr>
		{/foreach}
		</tbody>
		</table>
	
{else}	

	<div class="container">
		<div class="category">{if isset($kVerpackung) && $kVerpackung > 0}{#zusatzverpackungEdit#}:{else}{#zusatzverpackungAdd#}:{/if}</div>
		<form name="zusatzverpackung" method="post" action="zusatzverpackung.php">
		<input type="hidden" name="{$session_name}" value="{$session_id}">
		<input type="hidden" name="eintragen" value="1">
			<input type="hidden" name="kVerpackung" value="{if isset($kVerpackung)}{$kVerpackung}{/if}">
		
		<table class="kundenfeld">
		{foreach name=sprachen from=$oSprache_arr item=oSprache}
			{assign var=cISO value=$oSprache->cISO}
			<tr>
				<td>{#zusatzverpackungName#} ({$oSprache->cNameDeutsch})</td>
				<td><input name="cName_{$oSprache->cISO}" type="text"  value="{if isset($oVerpackungEdit->oSprach_arr[$cISO]->cName)}{$oVerpackungEdit->oSprach_arr[$cISO]->cName}{/if}"></td>
			</tr>
		{/foreach}
			<tr>
				<td>{#zusatzverpackungPrice#} ({#zusatzverpackungGross#})</td>
				<td><input name="fBrutto" id="fBrutto" type="text"  value="{if isset($oVerpackungEdit->fBrutto)}{$oVerpackungEdit->fBrutto}{/if}" onKeyUp="setzePreisAjax(false, 'WertAjax', this)" /> <span id="WertAjax"></span></td>
			</tr>
			<tr>
				<td>{#zusatzverpackungMinValue#} ({#zusatzverpackungGross#})</td>
				<td><input name="fMindestbestellwert" id="fMindestbestellwert" type="text"  value="{if isset($oVerpackungEdit->fMindestbestellwert)}{$oVerpackungEdit->fMindestbestellwert}{/if}" onKeyUp="setzePreisAjax(false, 'MindestWertAjax', this)" /> <span id="MindestWertAjax"></span></td>
			</tr>
			<tr>
				<td>{#zusatzverpackungExemptFromCharge#} ({#zusatzverpackungGross#})</td>
				<td><input name="fKostenfrei" id="fKostenfrei" type="text"  value="{if isset($oVerpackungEdit->fKostenfrei)}{$oVerpackungEdit->fKostenfrei}{/if}" onKeyUp="setzePreisAjax(false, 'KostenfreiAjax', this)" /> <span id="KostenfreiAjax"></span></td>
			</tr>				
			{foreach name=sprachen from=$oSprache_arr item=oSprache}
				 {assign var=cISO value=$oSprache->cISO}
			<tr>
				<td>{#zusatzverpackungDescLang#} ({$oSprache->cNameDeutsch})</td>
				<td>
					<textarea name="cBeschreibung_{$cISO}" rows="5" cols="35" class="combo">{if isset($oVerpackungEdit->oSprach_arr[$cISO]->cBeschreibung)}{$oVerpackungEdit->oSprach_arr[$cISO]->cBeschreibung}{/if}</textarea>
				</td>
			</tr>
			{/foreach}
			<tr>
				<td>{#zusatzverpackungTaxClass#}</td>
				<td>
					<select name="kSteuerklasse" class="combo">
						<option value="-1">{#zusatzverpackungAutoTax#}</option>
					{foreach name=steuerklassen from=$oSteuerklasse_arr item=oSteuerklasse}
						<option value="{$oSteuerklasse->kSteuerklasse}">{$oSteuerklasse->cName}</option>
					{/foreach}
					</select>
				</td>
			</tr>
			<tr>
				<td>{#zusatzverpackungCustomerGrp#}</b>:</td>
				<td>
					<select name="kKundengruppe[]" multiple="multiple" class="combo"> 
						<option value="-1"{if isset($oVerpackungEdit) && $oVerpackungEdit->cKundengruppe == "-1"} selected{/if}>Alle</option>
					{foreach name=kundengruppen from=$oKundengruppe_arr item=oKundengruppe}
							{if (isset($oVerpackungEdit->cKundengruppe) && $oVerpackungEdit->cKundengruppe == "-1") || !isset($oVerpackungEdit) || !$oVerpackungEdit}
								 <option value="{$oKundengruppe->kKundengruppe}">{$oKundengruppe->cName}</option>
							{else}
						<option value="{$oKundengruppe->kKundengruppe}"{foreach name=verpackungkndgrp from=$oVerpackungEdit->kKundengruppe_arr item=kKundengruppe}{if isset($oKundengruppe->kKundengruppe) && $oKundengruppe->kKundengruppe == $kKundengruppe} selected{/if}{/foreach}>{$oKundengruppe->cName}</option>
							{/if}
					{/foreach}
					</select>
				</td>
			</tr>
			<tr>
				<td>{#zusatzverpackungActive#}</td>
				<td>
					<select name="nAktiv" class="combo">
						<option value="1">Ja</option>
						<option value="0">Nein</option>
					</select>
				</td>
			</tr>           
		</table>
		
		<div class="save_wrapper">
			<input class="button orange" name="speichern" type="button" value="{if isset($kVerpackung) && $kVerpackung > 0}{#zusatzverpackungEditBTN#}{else}{#zusatzverpackungSave#}{/if}" onclick="javascript:document.zusatzverpackung.submit();">
		</div>
		</form>	
		
		<div class="category">{#zusatzverpackungAdded#}</div>
		{if isset($oVerpackung_arr) && $oVerpackung_arr|@count > 0}
		<form method="POST" action="zusatzverpackung.php">
		<input type="hidden" name="{$session_name}" value="{$session_id}">
		<input type="hidden" name="bearbeiten" value="1">

		<table class="list">
			<thead>
			<tr>
				<th class="th-1"></th>
				<th class="th-2">{#zusatzverpackungName#}</th>
				<th class="th-3">{#zusatzverpackungPrice#}</th>
				<th class="th-4">{#zusatzverpackungMinValue#}</th>
				<th class="th-5">{#zusatzverpackungExemptFromCharge#}</th>
				<th class="th-6">{#zusatzverpackungCustomerGrp#}</th>
				<th class="th-7">{#zusatzverpackungActive#}</th>
					<th class="th-8">&nbsp;</th>
			</tr>
			</thead>
			<tbody>
		{foreach name=verpackungen from=$oVerpackung_arr item=oVerpackung}
			<tr class="tab_bg{$smarty.foreach.verpackungen.iteration%2}">
				<td class="TD1"><input type="checkbox" name="kVerpackung[]" value="{$oVerpackung->kVerpackung}"></td>
				<td class="TD2"><a href="zusatzverpackung.php?a={$oVerpackung->kVerpackung}">{$oVerpackung->cName}</a></td>
				<td class="TD3">{getCurrencyConversionSmarty fPreisBrutto=$oVerpackung->fBrutto}</td>
				<td class="TD4">{getCurrencyConversionSmarty fPreisBrutto=$oVerpackung->fMindestbestellwert}</td>
				<td class="TD5">{getCurrencyConversionSmarty fPreisBrutto=$oVerpackung->fKostenfrei}</td>
				<td class="TD6">
				{foreach name=kundengruppe from=$oVerpackung->cKundengruppe_arr item=cKundengruppe}
					{$cKundengruppe}{if !$smarty.foreach.kundengruppe.last},{/if}
				{/foreach}
				</td>                                    			
					<td class="TD7"><input name="nAktiv[]" type="checkbox" value="{$oVerpackung->kVerpackung}"{if $oVerpackung->nAktiv == 1} checked{/if}></td>
					<td class="TD8"><a href="zusatzverpackung.php?edit={$oVerpackung->kVerpackung}&{$session_name}={$session_id}" class="button edit">{#zusatzverpackungEditBTN#}</a></td>
		 </tr>
			</tr>
	{/foreach}
			</tbody>
		</table>
				
		<div class="save_wrapper">
			<input name="aktualisieren" type="submit" value="{#zusatzverpackungUpdate#}" class="button orange" />
			<input name="loeschen" type="submit" value="{#zusatzverpackungDelete#}" class="button orange" />
		</div>
		</form>
			{else}
				 <div class="box_info container">{#zusatzverpackungAddedNone#}</div>
		{/if}
		
	</div>		
{/if}
	
</div>
</div>

<script type="text/javascript">
xajax_getCurrencyConversionAjax(0, document.getElementById('fBrutto').value, 'WertAjax');
xajax_getCurrencyConversionAjax(0, document.getElementById('fMindestbestellwert').value,'MindestWertAjax');
xajax_getCurrencyConversionAjax(0, document.getElementById('fKostenfrei').value, 'KostenfreiAjax');
</script>


{include file='tpl_inc/footer.tpl'}