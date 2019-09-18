{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: warenkorbpers.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="warenkorbpers"}
{include file="tpl_inc/seite_header.tpl" cTitel=#warenkorbpers# cBeschreibung=#warenkorbpersDesc# cDokuURL=#warenkorbpersURL#}
<div id="content">

{if isset($hinweis) && $hinweis|count_characters > 0}			
	 <p class="box_success">{$hinweis}</p>
{/if}
{if isset($fehler) && $fehler|count_characters > 0}			
	 <p class="box_error">{$fehler}</p>
{/if}

{if $step == "uebersicht"}

<div class="tabber">
	 
	 <div class="tabbertab{if isset($cTab) && $cTab == 'warenkorbpers'} tabbertabdefault{/if}">
	 
		  <h2>{#warenkorbpers#}</h2>
		 
		  <form name="suche" method="POST" action="warenkorbpers.php">
		  <input type="hidden" name="{$session_name}" value="{$session_id}" />
		  <input type="hidden" name="Suche" value="1" />
		  <input type="hidden" name="tab" value="warenkorbpers" />
		  <input type="hidden" name="s1" value="{$oBlaetterNaviKunde->nAktuelleSeite}" />
		  {if isset($cSuche) && $cSuche|count_characters > 0}
				<input type="hidden" name="cSuche" value="{$cSuche}" />
		  {/if}

		  <div class="block">
  			  <strong>{#warenkorbpersClientName#}:</strong> <input name="cSuche" type="text" value="{if isset($cSuche) && $cSuche|count_characters > 0}{$cSuche}{/if}" />
				<input name="submitSuche" type="submit" value="{#warenkorbpersSearchBTN#}" class="button blue" />
		  </div>
		  
		  </form>
		  
		  {if isset($oKunde_arr) && $oKunde_arr|@count > 0}
	 
		  {if $oBlaetterNaviKunde->nAktiv == 1}
		  <div class="container">
				<p>
				{$oBlaetterNaviKunde->nVon} - {$oBlaetterNaviKunde->nBis} {#from#} {$oBlaetterNaviKunde->nAnzahl}
				{if $oBlaetterNaviKunde->nAktuelleSeite == 1}
					 << {#back#}
				{else}
					 <a href="warenkorbpers.php?s1={$oBlaetterNaviKunde->nVoherige}&tab=warenkorbpers{if isset($cSuche) && $cSuche|count_characters > 0}&cSuche={$cSuche}{/if}&{$session_name}={$session_id}"><< {#back#}</a>
				{/if}
				
				{if $oBlaetterNaviKunde->nAnfang != 0}<a href="warenkorbpers.php?s1={$oBlaetterNaviKunde->nAnfang}&tab=warenkorbpers{if isset($cSuche) && $cSuche|count_characters > 0}&cSuche={$cSuche}{/if}&{$session_name}={$session_id}">{$oBlaetterNaviKunde->nAnfang}</a> ... {/if}
				{foreach name=blaetternavi from=$oBlaetterNaviKunde->nBlaetterAnzahl_arr item=Blatt}
					 {if $oBlaetterNaviKunde->nAktuelleSeite == $Blatt}[{$Blatt}]
					 {else}
						  <a href="warenkorbpers.php?s1={$Blatt}&tab=warenkorbpers{if isset($cSuche) && $cSuche|count_characters > 0}&cSuche={$cSuche}{/if}&{$session_name}={$session_id}">{$Blatt}</a>
					 {/if}
				{/foreach}
				
				{if $oBlaetterNaviKunde->nEnde != 0} ... <a href="warenkorbpers.php?s1={$oBlaetterNaviKunde->nEnde}&tab=warenkorbpers{if isset($cSuche) && $cSuche|count_characters > 0}&cSuche={$cSuche}{/if}&{$session_name}={$session_id}">{$oBlaetterNaviKunde->nEnde}</a>{/if}
				
				{if $oBlaetterNaviKunde->nAktuelleSeite == $oBlaetterNaviKunde->nSeiten}
					 {#next#} >>
				{else}
					 <a href="warenkorbpers.php?s1={$oBlaetterNaviKunde->nNaechste}&tab=warenkorbpers{if isset($cSuche) && $cSuche|count_characters > 0}&cSuche={$cSuche}{/if}&{$session_name}={$session_id}">{#next#} >></a>
				{/if}
				
				</p>
		  </div>
		  {/if}
	  
		  <div class="category">{#warenkorbpers#} {#warenkorbpersSearch#}</div>
				<table>
					 <thead>
					 <tr>
						  <th class="tleft">{#warenkorbpersCompany#}</th>
						  <th class="tleft">{#warenkorbpersClientName#}</th>
						  <th class="th-3">{#warenkorbpersCount#}</th>
						  <th class="th-4">{#warenkorbpersDate#}</th>
						  <th class="th-5">{#wishlistReceiverCount#}</th>
					 </tr>
					 </thead>
					 <tbody>
				{foreach name=warenkorbkunden from=$oKunde_arr item=oKunde}
					 <tr class="tab_bg{$smarty.foreach.warenkorbkunden.iteration%2}">
						  <td class="TD1">{$oKunde->cFirma}</td>
						  <td class="TD2">{$oKunde->cVorname} {$oKunde->cNachname}</td>
						  <td class="tcenter">{$oKunde->nAnzahl}</td>                                                    
						  <td class="tcenter">{$oKunde->Datum}</td>
						  <td class="tcenter">
								<a href="warenkorbpers.php?a={$oKunde->kKunde}{if $oBlaetterNaviKunde->nAktiv == 1}&s1={$oBlaetterNaviKunde->nAktuelleSeite}{/if}" class="button">{#warenkorbpersShow#}</a>
                                <a href="warenkorbpers.php?l={$oKunde->kKunde}{if $oBlaetterNaviKunde->nAktiv == 1}&s1={$oBlaetterNaviKunde->nAktuelleSeite}{/if}" class="button">{#warenkorbpersDel#}</a>                                
						  </td>
					 </tr>
				{/foreach}
				</tbody>
				</table>
	 {else}
		  <div class="box_info container">{#noDataAvailable#}</div>
	 {/if}
		  
	 </div>
	 
	 <div class="tabbertab{if isset($cTab) && $cTab == 'einstellungen'} tabbertabdefault{/if}">
	 
		  <h2>{#warenkorbpersSettings#}</h2>
		  
		  <form name="einstellen" method="post" action="warenkorbpers.php">
		  <input type="hidden" name="{$session_name}" value="{$session_id}">
		  <input type="hidden" name="einstellungen" value="1">
		  <input name="tab" type="hidden" value="einstellungen">
		  <div class="category">{#warenkorbpersSettings#}</div>
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
					 {else}
						  <input type="text" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}"  value="{$oConfig->gesetzterWert}" tabindex="1" /></p>
					 {/if}
					 {else}
						  {if $oConfig->cName}<h3 style="text-align:center;">({$oConfig->kEinstellungenConf}) {$oConfig->cName}</h3>{/if}
					 {/if}
				{/foreach}
		  </div>
		  
		  <p class="submit"><input name="speichern" type="submit" value="{#save#}" class="button orange" /></p>
		  </form>
		  
	 </div>
	 
</div>

{elseif $step == "anzeigen"}

<div class="category">
<p>{#warenkorbpersClient#} {$oWarenkorbPersPos_arr[0]->cVorname} {$oWarenkorbPersPos_arr[0]->cNachname}:</p>
</div>

{if $oBlaetterNavi->nAktiv == 1}
<div class="content">
<p>
{$oBlaetterNavi->nVon} - {$oBlaetterNavi->nBis} {#from#} {$oBlaetterNavi->nAnzahl}
{if $oBlaetterNavi->nAktuelleSeite == 1}
<< {#back#}
{else}
<a href="warenkorbpers.php?a={$oWarenkorbPersPos_arr[0]->kKunde}&s2={$oBlaetterNavi->nVoherige}"><< {#back#}</a>
{/if}

{if $oBlaetterNavi->nAnfang != 0}<a href="warenkorbpers.php?a={$oWarenkorbPersPos_arr[0]->kKunde}&s2={$oBlaetterNavi->nAnfang}">{$oBlaetterNavi->nAnfang}</a> ... {/if}
{foreach name=blaetternavi from=$oBlaetterNavi->nBlaetterAnzahl_arr item=Blatt}
{if $oBlaetterNavi->nAktuelleSeite == $Blatt}[{$Blatt}]
{else}
<a href="warenkorbpers.php?a={$oWarenkorbPersPos_arr[0]->kKunde}&s2={$Blatt}">{$Blatt}</a>
{/if}
{/foreach}

{if $oBlaetterNavi->nEnde != 0} ... <a href="warenkorbpers.php?a={$oWarenkorbPersPos_arr[0]->kKunde}&s2={$oBlaetterNavi->nEnde}">{$oBlaetterNavi->nEnde}</a>{/if}

{if $oBlaetterNavi->nAktuelleSeite == $oBlaetterNavi->nSeiten}
{#next#} >>
{else}
<a href="warenkorbpers.php?a={$oWarenkorbPersPos_arr[0]->kKunde}&s2={$oBlaetterNavi->nNaechste}">{#next#} >></a>
{/if}

</p>
{/if}

	<table>
	 <thead>
	<tr>
		<th class="tleft">{#warenkorbpersProduct#}</th>
		<th class="th-2">{#warenkorbpersCount#}</th>
		<th class="th-3">{#warenkorbpersDate#}</th>
	</tr>
	</thead>
	 <tbody>
	{foreach name=warenkorbpers from=$oWarenkorbPersPos_arr item=oWarenkorbPersPos}
	<tr class="tab_bg{$smarty.foreach.warenkorbpers.iteration%2}">
		<td class="tleft"><a href="{$URL_SHOP}/index.php?a={$oWarenkorbPersPos->kArtikel}" target="_blank">{$oWarenkorbPersPos->cArtikelName}</td>
		<td class="tcenter">{$oWarenkorbPersPos->fAnzahl} {$oKunde->cNachname}</td>
		<td class="tcenter">{$oWarenkorbPersPos->Datum}</td>
	</tr>
	 {/foreach}
	 </tbody>
	</table>
{/if}

{include file='tpl_inc/footer.tpl'}