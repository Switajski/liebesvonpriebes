{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: shoptemplate.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2007 JTL-Software
    

-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="wunschliste"}

{include file="tpl_inc/seite_header.tpl" cTitel=#wishlistName# cBeschreibung=#wishlistDesc# cDokuURL=#wishlistURL#}
<div id="content">


	{if isset($hinweis) && $hinweis|count_characters > 0}
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}
		<p class="box_error">{$fehler}</p>
	{/if}
	
<div class="container">

	 <div class="tabber">
		  
		  <div class="tabbertab{if isset($cTab) && $cTab == 'wunschlistepos'} tabbertabdefault{/if}">
				
				
				<h2>{#wishlistTop100#}</h2>
				
		  {if isset($CWunschliste_arr) && $CWunschliste_arr|@count > 0}
				
				{if $oBlaetterNaviPos->nAktiv == 1}
				<div class="container">
						  <p>
						  {$oBlaetterNaviPos->nVon} - {$oBlaetterNaviPos->nBis} {#wishlistFrom#} {$oBlaetterNaviPos->nAnzahl}
						  {if $oBlaetterNaviPos->nAktuelleSeite == 1}
								<< {#wishlistPrevious#}
						  {else}
								<a href="wunschliste.php?s1={$oBlaetterNaviPos->nVoherige}&tab=wunschlistepos"><< {#wishlistPrevious#}</a>
						  {/if}
						  
						  {if $oBlaetterNaviPos->nAnfang != 0}<a href="wunschliste.php?s1={$oBlaetterNaviPos->nAnfang}&tab=wunschlistepos">{$oBlaetterNaviPos->nAnfang}</a> ... {/if}
						  {foreach name=blaetternavi from=$oBlaetterNaviPos->nBlaetterAnzahl_arr item=Blatt}
								{if $oBlaetterNaviPos->nAktuelleSeite == $Blatt}[{$Blatt}]
								{else}
									 <a href="wunschliste.php?s1={$Blatt}&tab=wunschlistepos">{$Blatt}</a>
								{/if}
						  {/foreach}
						  
						  {if $oBlaetterNaviPos->nEnde != 0} ... <a href="wunschliste.php?s1={$oBlaetterNaviPos->nEnde}&tab=wunschlistepos">{$oBlaetterNaviPos->nEnde}</a>{/if}
						  
						  {if $oBlaetterNaviPos->nAktuelleSeite == $oBlaetterNaviPos->nSeiten}
								{#wishlistNext#} >>
						  {else}
								<a href="wunschliste.php?s1={$oBlaetterNaviPos->nNaechste}&tab=wunschlistepos">{#wishlistNext#} >></a>
						  {/if}
						  
						  </p>
				</div>
				{/if}
				<div id="payment">
					 <div id="tabellenLivesuche">
					 <table>
						  <tr>
								<th class="tleft">{#wishlistName#}</th>
								<th class="tleft">{#wishlistAccount#}</th>
								<th class="th-3">{#wishlistPosCount#}</th>
								<th class="th-4">{#wishlistDate#}</th>
						  </tr>
					 {foreach name=wunschliste from=$CWunschliste_arr item=CWunschliste}
						  <tr class="tab_bg{$smarty.foreach.wunschliste.iteration%2}">                                
								<td class="TD1">
									 {if $CWunschliste->nOeffentlich == 1}
										  <a href="../../index.php?wlid={$CWunschliste->cURLID}" rel="external">{$CWunschliste->cName}</a>
									 {else}
										  <font style="font-weight: normal;">{$CWunschliste->cName}</font>
									 {/if}
								</td>
								<td class="TD2">{$CWunschliste->cVorname} {$CWunschliste->cNachname}</td>
								<td class="tcenter">{$CWunschliste->Anzahl}</td>
								<td class="tcenter">{$CWunschliste->Datum}</td>
						  </tr>
					 {/foreach}
					 </table>
					 </div>
				</div>
				
		  {else}
				<div class="box_info">{#noDataAvailable#}</div>
		  {/if}
				
		  </div>
		  
		  <div class="tabbertab{if isset($cTab) && $cTab == 'wunschlisteartikel'} tabbertabdefault{/if}">
				
				
				<h2>{#wishlistPosTop100#}</h2>
				
		  {if isset($CWunschlistePos_arr) && $CWunschlistePos_arr|@count > 0}
				
				{if $oBlaetterNaviArtikel->nAktiv == 1}
				<div class="container">
						  <p>
						  {$oBlaetterNaviArtikel->nVon} - {$oBlaetterNaviArtikel->nBis} {#wishlistFrom#} {$oBlaetterNaviArtikel->nAnzahl}
						  {if $oBlaetterNaviArtikel->nAktuelleSeite == 1}
								<< {#wishlistPrevious#}
						  {else}
								<a href="wunschliste.php?s2={$oBlaetterNaviArtikel->nVoherige}&tab=wunschlisteartikel"><< {#wishlistPrevious#}</a>
						  {/if}
						  
						  {if $oBlaetterNaviArtikel->nAnfang != 0}<a href="wunschliste.php?s2={$oBlaetterNaviArtikel->nAnfang}&tab=wunschlisteartikel">{$oBlaetterNaviArtikel->nAnfang}</a> ... {/if}
						  {foreach name=blaetternavi from=$oBlaetterNaviArtikel->nBlaetterAnzahl_arr item=Blatt}
								{if $oBlaetterNaviArtikel->nAktuelleSeite == $Blatt}[{$Blatt}]
								{else}
									 <a href="wunschliste.php?s2={$Blatt}&tab=wunschlisteartikel">{$Blatt}</a>
								{/if}
						  {/foreach}
						  
						  {if $oBlaetterNaviArtikel->nEnde != 0} ... <a href="wunschliste.php?s2={$oBlaetterNaviArtikel->nEnde}&tab=wunschlisteartikel">{$oBlaetterNaviArtikel->nEnde}</a>{/if}
						  
						  {if $oBlaetterNaviArtikel->nAktuelleSeite == $oBlaetterNaviArtikel->nSeiten}
								{#wishlistNext#} >>
						  {else}
								<a href="wunschliste.php?s2={$oBlaetterNaviArtikel->nNaechste}&tab=wunschlisteartikel">{#wishlistNext#} >></a>
						  {/if}
						  
						  </p>
				</div>
				{/if}
				<div id="payment">
					 <div id="tabellenLivesuche">
					 <table>
						  <tr>
								<th class="tleft">{#wishlistPosName#}</th>
								<th class="th-2">{#wishlistPosCount#}</th>
								<th class="th-3">{#wishlistLastAdded#}</th>
						  </tr>
					 {foreach name=wunschlistepos from=$CWunschlistePos_arr item=CWunschlistePos}
						  <tr class="tab_bg{$smarty.foreach.wunschlistepos.iteration%2}">                                
								<td class="TD1"><a href="../../index.php?a={$CWunschlistePos->kArtikel}&" rel="external">{$CWunschlistePos->cArtikelName}</a></td>
								<td class="tcenter">{$CWunschlistePos->Anzahl}</td>
								<td class="tcenter">{$CWunschlistePos->Datum}</td>
						  </tr>
					 {/foreach}
					 </table>
					 </div>
				</div>
				
		  {else}
				<div class="box_info">{#noDataAvailable#}</div>
		  {/if}
				
		  </div>
		  
		  <div class="tabbertab{if isset($cTab) && $cTab == 'wunschlistefreunde'} tabbertabdefault{/if}">
				
				
				<h2>{#wishlistSend#}</h2>
		  
		  {if $CWunschlisteVersand_arr && $CWunschlisteVersand_arr|@count > 0}
				
				{if $oBlaetterNaviFreunde->nAktiv == 1}
				<div class="container">
						  <p>
						  {$oBlaetterNaviFreunde->nVon} - {$oBlaetterNaviFreunde->nBis} {#wishlistFrom#} {$oBlaetterNaviFreunde->nAnzahl}
						  {if $oBlaetterNaviFreunde->nAktuelleSeite == 1}
								<< {#wishlistPrevious#}
						  {else}
								<a href="wunschliste.php?s3={$oBlaetterNaviFreunde->nVoherige}&tab=wunschlistefreunde"><< {#wishlistPrevious#}</a>
						  {/if}
						  
						  {if $oBlaetterNaviFreunde->nAnfang != 0}<a href="wunschliste.php?s3={$oBlaetterNaviFreunde->nAnfang}&tab=wunschlistefreunde">{$oBlaetterNaviFreunde->nAnfang}</a> ... {/if}
						  {foreach name=blaetternavi from=$oBlaetterNaviFreunde->nBlaetterAnzahl_arr item=Blatt}
								{if $oBlaetterNaviFreunde->nAktuelleSeite == $Blatt}[{$Blatt}]
								{else}
									 <a href="wunschliste.php?s3={$Blatt}&tab=wunschlistefreunde">{$Blatt}</a>
								{/if}
						  {/foreach}
						  
						  {if $oBlaetterNaviFreunde->nEnde != 0} ... <a href="wunschliste.php?s3={$oBlaetterNaviFreunde->nEnde}&tab=wunschlistefreunde">{$oBlaetterNaviFreunde->nEnde}</a>{/if}
						  
						  {if $oBlaetterNaviFreunde->nAktuelleSeite == $oBlaetterNaviFreunde->nSeiten}
								{#wishlistNext#} >>
						  {else}
								<a href="wunschliste.php?s3={$oBlaetterNaviFreunde->nNaechste}&tab=wunschlistefreunde">{#wishlistNext#} >></a>
						  {/if}
						  
						  </p>
				</div>
				{/if}
				<div id="payment">
					 <div id="tabellenLivesuche">
					 <table>
						  <tr>
								<th class="tleft">{#wishlistName#}</th>
								<th class="tleft">{#wishlistAccount#}</th>
								<th class="th-3">{#wishlistReceiverCount#}</th>
								<th class="th-4">{#wishlistPosCount#}</th>
								<th class="th-5">{#wishlistDate#}</th>
						  </tr>
					 {foreach name=wunschlisteversand from=$CWunschlisteVersand_arr item=CWunschlisteVersand}
						  <tr class="tab_bg{$smarty.foreach.wunschlisteversand.iteration%2}">
								<td class="TD1"><a href="../../index.php?wlid={$CWunschlisteVersand->cURLID}" rel="external">{$CWunschlisteVersand->cName}</a></td>
								<td class="TD2">{$CWunschlisteVersand->cVorname} {$CWunschlisteVersand->cNachname}</td>
								<td class="tcenter">{$CWunschlisteVersand->nAnzahlEmpfaenger}</td>
								<td class="tcenter">{$CWunschlisteVersand->nAnzahlArtikel}</td>
								<td class="tcenter">{$CWunschlisteVersand->Datum}</td>
						  </tr>
					 {/foreach}
					 </table>
					 </div>
				</div>
				
		  {else}
				<div class="box_info">{#noDataAvailable#}</div>
		  {/if}
				
		  </div>
		  
		  <div class="tabbertab{if isset($cTab) && $cTab == 'einstellungen'} tabbertabdefault{/if}">
				
				
				<h2>{#wishlistSettings#}</h2>
				<form name="einstellen" method="post" action="wunschliste.php">
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
				
				<p class="submit"><input type="submit" value="{#wishlisteSave#}" class="button orange" /></p>
				</form>    
		  </div>
		  
	 </div>
	 
</div>
        

{include file='tpl_inc/footer.tpl'}