{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: vergleichsliste.tpl, smarty template inc file
	
	preisverlauf page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2007 JTL-Software
-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="vergleichsliste"}

{include file="tpl_inc/seite_header.tpl" cTitel=#configureComparelist# cBeschreibung=#configureComparelistDesc# cDokuURL=#configureComparelistURL#}
<div id="content">
	
	{if isset($hinweis) && $hinweis|count_characters > 0}
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}
		<p class="box_error">{$fehler}</p>
	{/if}
	
	<div class="container">
	  
			<div class="tabber">
				
				 <div class="tabbertab{if isset($cTab) && $cTab == 'letztenvergleiche'} tabbertabdefault{/if}">
				 
					  
					  <h2>{#last20Compares#}</h2>
					  
				 {if $Letzten20Vergleiche && $Letzten20Vergleiche|@count > 0}    
					  {if $oBlaetterNavi->nAktiv == 1}
					  <div class="container">
							<p>
							{$oBlaetterNavi->nVon} - {$oBlaetterNavi->nBis} {#compareFrom#} {$oBlaetterNavi->nAnzahl}
							{if $oBlaetterNavi->nAktuelleSeite == 1}
								 << {#comparePrevious#}
							{else}
								 <a href="vergleichsliste.php?s1={$oBlaetterNavi->nVoherige}&tab=letztenvergleiche"><< {#comparePrevious#}</a>
							{/if}
							
							{if $oBlaetterNavi->nAnfang != 0}<a href="vergleichsliste.php?s1={$oBlaetterNavi->nAnfang}&tab=letztenvergleiche">{$oBlaetterNavi->nAnfang}</a> ... {/if}
							{foreach name=blaetternavi from=$oBlaetterNavi->nBlaetterAnzahl_arr item=Blatt}
								 {if $oBlaetterNavi->nAktuelleSeite == $Blatt}[{$Blatt}]
								 {else}
									  <a href="vergleichsliste.php?s1={$Blatt}&tab=letztenvergleiche">{$Blatt}</a>
								 {/if}
							{/foreach}
							
							{if $oBlaetterNavi->nEnde != 0} ... <a href="vergleichsliste.php?s1={$oBlaetterNavi->nEnde}&tab=letztenvergleiche">{$oBlaetterNavi->nEnde}</a>{/if}
							
							{if $oBlaetterNavi->nAktuelleSeite == $oBlaetterNavi->nSeiten}
								 {#compareNext#} >>
							{else}
								 <a href="vergleichsliste.php?s1={$oBlaetterNavi->nNaechste}&tab=letztenvergleiche">{#compareNext#} >></a>
							{/if}
							
							</p>
					  </div>
					  {/if}
					  
					  <div id="payment">
							<div id="tabellenLivesuche">
							<table>
								 <tr>
									  <th class="th-1">{#compareID#}</th>
									  <th class="tleft">{#compareProducts#}</th>
									  <th class="th-3">{#compareDate#}</th>
								 </tr>
							{foreach name=letzten20 from=$Letzten20Vergleiche item=oVergleichsliste20}
								 <tr class="tab_bg{$smarty.foreach.letzten20.iteration%2}">
									  <td class="tcenter">{$oVergleichsliste20->kVergleichsliste}</td>
									  <td class="">
									  {foreach name=letzten20pos from=$oVergleichsliste20->oLetzten20VergleichslistePos_arr item=oVergleichslistePos20}
											<a href="../../index.php?a={$oVergleichslistePos20->kArtikel}" target="_blank">{$oVergleichslistePos20->cArtikelName}</a>{if !$smarty.foreach.letzten20pos.last}{/if}<br>
									  {/foreach}
									  </td>
									  <td class="tcenter">{$oVergleichsliste20->Datum}</td>
								 </tr>
							{/foreach}
							</table>
							</div>
					  </div>
					  
				 {else}
					  <div class="box_info">{#noDataAvailable#}</div>
				 {/if}
					  
				 </div>
					  
				 <div class="tabbertab{if isset($cTab) && $cTab == 'topartikel'} tabbertabdefault{/if}">
				 
					  
					  <h2>{#topCompareProducts#}</h2>
					  
							<form id="postzeitfilter" name="postzeitfilter" method="POST" action="vergleichsliste.php">
							<input type="hidden" name="{$session_name}" value="{$session_id}" />
							<input type="hidden" name="zeitfilter" value="1" />
							<input type="hidden" name="tab" value="topartikel">
							<table cellspacing="3">
								 <tr>
									  <td style="width: 110px;"><b>{#compareTimeFilter#}:</b></td>
									  <td>
											<select name="nZeitFilter" onchange="javascript:document.postzeitfilter.submit();">
												 <option value="1"{if $smarty.session.Vergleichsliste->nZeitFilter == 1} selected{/if}>letzte 24 Stunden</option>
												 <option value="7"{if $smarty.session.Vergleichsliste->nZeitFilter == 7} selected{/if}>letzte 7 Tage</option>
												 <option value="30"{if $smarty.session.Vergleichsliste->nZeitFilter == 30} selected{/if}>letzte 30 Tage</option>
												 <option value="365"{if $smarty.session.Vergleichsliste->nZeitFilter == 365} selected{/if}>letztes Jahr</option>
											</select>
									  </td>
								 </tr>
								 
								 <tr>
									  <td style="width: 110px;"><b>{#compareTopCount#}:</b></td>
									  <td>
											<select name="nAnzahl" onchange="javascript:document.postzeitfilter.submit();">
												 <option value="10"{if $smarty.session.Vergleichsliste->nAnzahl == 10} selected{/if}>10</option>
												 <option value="20"{if $smarty.session.Vergleichsliste->nAnzahl == 20} selected{/if}>20</option>
												 <option value="50"{if $smarty.session.Vergleichsliste->nAnzahl == 50} selected{/if}>50</option>
												 <option value="100"{if $smarty.session.Vergleichsliste->nAnzahl == 100} selected{/if}>100</option>
												 <option value="-1"{if $smarty.session.Vergleichsliste->nAnzahl == -1} selected{/if}>Alle</option>
											</select>
									  </td>
								 </tr>
							</table>
							</form>
					  
					  {if $TopVergleiche && $TopVergleiche|@count > 0}
							<div id="payment">
								 <div id="tabellenLivesuche">
								 <table class="container bottom">
									  <tr>
											<th class="tleft">{#compareProduct#}</th>
											<th class="th-2">{#compareCount#}</th>
									  </tr>
								 {foreach name=top from=$TopVergleiche item=oVergleichslistePosTop}
									  <tr class="tab_bg{$smarty.foreach.top.iteration%2}">                                
											<td class="TD1"><a href="../../index.php?a={$oVergleichslistePosTop->kArtikel}" target="_blank">{$oVergleichslistePosTop->cArtikelName}</a></td>
											<td class="tcenter">{$oVergleichslistePosTop->nAnzahl}</td>
									  </tr>
								 {/foreach}
								 </table>
								 </div>
							</div>
					  {else}
							<div class="container box_info">{#noDataAvailable#}</div>
					  {/if}
					  
				 </div>
				 
				 <div class="tabbertab{if isset($cTab) && $cTab == 'einstellungen'} tabbertabdefault{/if}">
				 
					  
					  <h2>{#compareSettings#}</h2>
	  
					  <form name="einstellen" method="post" action="vergleichsliste.php">
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
								 {else}
									  <input type="text" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}"  value="{$oConfig->gesetzterWert}" tabindex="1" /></p>
								 {/if}
								 {else}
									  {if $oConfig->cName}<h3 style="text-align:center;">({$oConfig->kEinstellungenConf}) {$oConfig->cName}</h3>{/if}
								 {/if}
							{/foreach}
					  </div>
					  
					  <p class="submit"><input type="submit" value="{#compareSave#}" class="button orange" /></p>
					  </form>
	  
			 </div>
				 
			</div>
			
	</div>
	 
 </div>
{include file='tpl_inc/footer.tpl'}