{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: gratisgeschenk.tpl, smarty template inc file
	
	preisverlauf page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2007 JTL-Software
-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="gratisgeschenk"}

<script type="text/javascript" src="templates/default/js/versandart_bruttonetto.js"></script>

{assign var=cFunAttrib value="`$ART_ATTRIBUT_GRATISGESCHENKAB`"}

{include file="tpl_inc/seite_header.tpl" cTitel=#ggHeader# cDokuURL=#ggURL#}
<div id="content">

{if isset($hinweis) && $hinweis|count_characters > 0}			
	 <p class="box_success">{$hinweis}</p>
{/if}
{if isset($fehler) && $fehler|count_characters > 0}			
	 <p class="box_error">{$fehler}</p>
{/if}

	<div class="tabber">
	
		<div class="tabbertab{if isset($cTab) && $cTab == 'aktivegeschenke'} tabbertabdefault{/if}">
	
			<h2>{#ggActiveProducts#}</h2>
		{if isset($oAktiveGeschenk_arr) && $oAktiveGeschenk_arr|@count > 0}
		
			{if $oBlaetterNaviAktiv->nAktiv == 1}
			<div class="container">
					<p>
					{$oBlaetterNaviAktiv->nVon} - {$oBlaetterNaviAktiv->nBis} {#from#} {$oBlaetterNaviAktiv->nAnzahl}
					{if $oBlaetterNaviAktiv->nAktuelleSeite == 1}
						&laquo; {#back#}
					{else}
						<a href="gratisgeschenk.php?s1={$oBlaetterNaviAktiv->nVoherige}&tab=aktivegeschenke">&laquo; {#back#}</a>
					{/if}
					
					{if $oBlaetterNaviAktiv->nAnfang != 0}<a href="gratisgeschenk.php?s1={$oBlaetterNaviAktiv->nAnfang}&tab=aktivegeschenke">{$oBlaetterNaviAktiv->nAnfang}</a> ... {/if}
					{foreach name=blaetternavi from=$oBlaetterNaviAktiv->nBlaetterAnzahl_arr item=Blatt}
						{if $oBlaetterNaviAktiv->nAktuelleSeite == $Blatt}[{$Blatt}]
						{else}
							<a href="gratisgeschenk.php?s1={$Blatt}&tab=aktivegeschenke">{$Blatt}</a>
						{/if}
					{/foreach}
					
					{if $oBlaetterNaviAktiv->nEnde != 0} ... <a href="gratisgeschenk.php?s1={$oBlaetterNaviAktiv->nEnde}&tab=aktivegeschenke">{$oBlaetterNaviAktiv->nEnde}</a>{/if}
					
					{if $oBlaetterNaviAktiv->nAktuelleSeite == $oBlaetterNaviAktiv->nSeiten}
						{#next#} &raquo;
					{else}
						<a href="gratisgeschenk.php?s1={$oBlaetterNaviAktiv->nNaechste}&tab=aktivegeschenke">{#next#} &raquo;</a>
					{/if}
					
					</p>
			</div>
			{/if}
		
               
				<table>
					<thead>
					<tr>
						<th class="tleft">{#ggProductName#}</th>
						<th class="th-2">{#ggOrderValue#}</th>
						<th class="th-3">{#ggDate#}</th>
					</tr>
					</thead>
					<tbody>
				{foreach name=aktivegeschenke from=$oAktiveGeschenk_arr item=oAktiveGeschenk}
					<tr class="tab_bg{$smarty.foreach.aktivegeschenke.iteration%2}">
						<td class="TD1"><a href="../../index.php?a={$oAktiveGeschenk->kArtikel}" target="_blank">{$oAktiveGeschenk->cName}</a></td>
						<td class="tcenter">{getCurrencyConversionSmarty fPreisBrutto=$oAktiveGeschenk->FunktionsAttribute[$cFunAttrib]}</td>
						<td class="tcenter">{$oAktiveGeschenk->dErstellt_de}</td>
					</tr>
				{/foreach}
					</tbody>
				</table>                    
		{else}
			<div class="box_info container">{#noDataAvailable#}</div>
		{/if}
			
		</div>
		
		<div class="tabbertab{if isset($cTab) && $cTab == 'haeufigegeschenke'} tabbertabdefault{/if}">
	
			<h2>{#ggCommonBuyedProducts#}</h2>
			
		{if isset($oHaeufigGeschenk_arr) && $oHaeufigGeschenk_arr|@count > 0}
		
			{if $oBlaetterNaviHaeufig->nAktiv == 1}
			<div class="container">
					<p>
					{$oBlaetterNaviHaeufig->nVon} - {$oBlaetterNaviHaeufig->nBis} {#from#} {$oBlaetterNaviHaeufig->nAnzahl}
					{if $oBlaetterNaviHaeufig->nAktuelleSeite == 1}
						&laquo; {#back#}
					{else}
						<a href="gratisgeschenk.php?s2={$oBlaetterNaviHaeufig->nVoherige}&tab=haeufigegeschenke">&laquo; {#back#}</a>
					{/if}
					
					{if $oBlaetterNaviHaeufig->nAnfang != 0}<a href="gratisgeschenk.php?s2={$oBlaetterNaviHaeufig->nAnfang}&tab=haeufigegeschenke">{$oBlaetterNaviHaeufig->nAnfang}</a> ... {/if}
					{foreach name=blaetternavi from=$oBlaetterNaviHaeufig->nBlaetterAnzahl_arr item=Blatt}
						{if $oBlaetterNaviHaeufig->nAktuelleSeite == $Blatt}[{$Blatt}]
						{else}
							<a href="gratisgeschenk.php?s2={$Blatt}&tab=haeufigegeschenke">{$Blatt}</a>
						{/if}
					{/foreach}
					
					{if $oBlaetterNaviHaeufig->nEnde != 0} ... <a href="gratisgeschenk.php?s2={$oBlaetterNaviHaeufig->nEnde}&tab=haeufigegeschenke">{$oBlaetterNaviHaeufig->nEnde}</a>{/if}
					
					{if $oBlaetterNaviHaeufig->nAktuelleSeite == $oBlaetterNaviHaeufig->nSeiten}
						{#next#} &raquo;
					{else}
						<a href="gratisgeschenk.php?s2={$oBlaetterNaviHaeufig->nNaechste}&tab=haeufigegeschenke">{#next#} &raquo;</a>
					{/if}
					
					</p>
			</div>
			{/if}
		
				<table>
					<thead>
					<tr>
						<th class="tleft">{#ggProductName#}</th>
						<th class="th-2">{#ggOrderValue#}</th>
						<th class="th-3">{#ggCount#}</th>
						<th class="th-4">{#ggDate#}</th>
					</tr>
					</thead>
					<tbody>
				{foreach name=haeufigegeschenke from=$oHaeufigGeschenk_arr item=oHaeufigGeschenk}
					<tr class="tab_bg{$smarty.foreach.haeufigegeschenke.iteration%2}">
						<td class="TD1"><a href="../../index.php?a={$oHaeufigGeschenk->kArtikel}" target="_blank">{$oHaeufigGeschenk->cName}</a></td>
						<td class="tcenter">{$oHaeufigGeschenk->FunktionsAttribute[$cFunAttrib]}</td>
						<td class="tcenter">{$oHaeufigGeschenk->nGGAnzahl} mal</td>
						<td class="tcenter">{$oHaeufigGeschenk->dErstellt_de}</td>
					</tr>
				{/foreach}
				</tbody>
				</table>                    
		{else}
			<div class="box_info container">{#noDataAvailable#}</div>
		{/if}
		
		</div>
		
		<div class="tabbertab{if isset($cTab) && $cTab == 'letzten100geschenke'} tabbertabdefault{/if}">
	
			<h2>{#ggLast100Products#}</h2>
		{if isset($oLetzten100Geschenk_arr) && $oLetzten100Geschenk_arr|@count > 0}
		
			{if $oBlaetterNaviLetzten100->nAktiv == 1}
			<div class="container">
					<p>
					{$oBlaetterNaviLetzten100->nVon} - {$oBlaetterNaviLetzten100->nBis} {#from#} {$oBlaetterNaviLetzten100->nAnzahl}
					{if $oBlaetterNaviLetzten100->nAktuelleSeite == 1}
						&laquo; {#back#}
					{else}
						<a href="gratisgeschenk.php?s3={$oBlaetterNaviLetzten100->nVoherige}&tab=letzten100geschenke">&laquo; {#back#}</a>
					{/if}
					
					{if $oBlaetterNaviLetzten100->nAnfang != 0}<a href="gratisgeschenk.php?s3={$oBlaetterNaviLetzten100->nAnfang}&tab=letzten100geschenke">{$oBlaetterNaviLetzten100->nAnfang}</a> ... {/if}
					{foreach name=blaetternavi from=$oBlaetterNaviLetzten100->nBlaetterAnzahl_arr item=Blatt}
						{if $oBlaetterNaviLetzten100->nAktuelleSeite == $Blatt}[{$Blatt}]
						{else}
							<a href="gratisgeschenk.php?s3={$Blatt}&tab=letzten100geschenke">{$Blatt}</a>
						{/if}
					{/foreach}
					
					{if $oBlaetterNaviLetzten100->nEnde != 0} ... <a href="gratisgeschenk.php?s3={$oBlaetterNaviLetzten100->nEnde}&tab=letzten100geschenke">{$oBlaetterNaviLetzten100->nEnde}</a>{/if}
					
					{if $oBlaetterNaviLetzten100->nAktuelleSeite == $oBlaetterNaviLetzten100->nSeiten}
						{#next#} &raquo;
					{else}
						<a href="gratisgeschenk.php?s3={$oBlaetterNaviLetzten100->nNaechste}&tab=letzten100geschenke">{#next#} &raquo;</a>
					{/if}
					
					</p>
			</div>
			{/if}
			
			<div id="payment">
				<div id="tabellenLivesuche">                    
				<table>
					<thead>
					<tr>
						<th class="tleft">{#ggProductName#}</th>
						<th class="th-2">{#ggOrderValue#}</th>
						<th class="th-3">{#ggCount#}</th>
						<th class="th-4">{#ggDate#}</th>
					</tr>
					</thead>
					<tbody>
				{foreach name=letzten100geschenke from=$oLetzten100Geschenk_arr item=oLetzten100Geschenk}
					<tr class="tab_bg{$smarty.foreach.letzten100geschenke.iteration%2}">
						<td class="TD1"><a href="../../index.php?a={$oLetzten100Geschenk->kArtikel}" target="_blank">{$oLetzten100Geschenk->cName}</a></td>
						<td class="tcenter">{$oLetzten100Geschenk->FunktionsAttribute[$cFunAttrib]}</td>
						<td class="tcenter">{$oLetzten100Geschenk->nGGAnzahl} mal</td>
						<td class="tcenter">{$oLetzten100Geschenk->dErstellt_de}</td>
					</tr>
				{/foreach}
				</tbody>
				</table>                    
				</div>
			</div>
		{else}
			<div class="box_info container">{#noDataAvailable#}</div>
		{/if}
			
		</div>
	
		<div class="tabbertab{if isset($cTab) && $cTab == 'einstellungen'} tabbertabdefault{/if}">
					
			<h2>{#ggSettings#}</h2>
			<form name="einstellen" method="post" action="gratisgeschenk.php">
			<input type="hidden" name="{$session_name}" value="{$session_id}">
			<input type="hidden" name="einstellungen" value="1">
			<input type="hidden" name="tab" value="einstellungen">
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
						<select name="{$oConfig->cWertName}[]" id="{$oConfig->cWertName}" multiple="multiple" class="combo" style="width: 250px; height: 150px;"> 
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
			
			<p class="submit"><input type="submit" value="{#save#}" class="button orange" /></p>
			</form>
			
		</div>
		
	</div>
		
 </div>

{include file='tpl_inc/footer.tpl'}