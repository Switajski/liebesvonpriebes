{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: sitemapexport.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: NIclas Potthast niclas@jtl-software.de
	http://www.jtl-software.de
	Copyright (c) 2008 JTL-Software
    

-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
<script type="text/javascript" src="templates/default/js/checkAllMSG.js"></script>
<script type="text/javascript" src="templates/default/js/expander.js"></script>
{config_load file="$lang.conf" section="sitemapExport"}

{include file="tpl_inc/seite_header.tpl" cTitel=#sitemapExport# cBeschreibung=#sitemapExportDesc# cDokuURL=#sitemapExportURL#}
<div id="content">
{if isset($hinweis) && $hinweis|count_characters > 0}
	<p class="box_success">{$hinweis}</p>
{/if}
{if isset($fehler) && $fehler|count_characters > 0}
	<p class="box_error">{$fehler}</p>
{/if}

<div class="container">

<div class="tabber">
	 
	 <div class="tabbertab{if isset($cTab) && $cTab == 'export'} tabbertabdefault{/if}">
	 
		  <h2>{#sitemapExport#}</h2>
		  
		  {if isset($errorNoWrite) && $errorNoWrite|count_characters > 0}			
				<p class="box_error">{$errorNoWrite}</p>
		  {/if}
		 
		  <p><input style="width:550px;" type="text" readonly="readonly" value="{$URL}"  /></p>
		  <div class="box_info container">
		  <p>{#searchEngines#}</p>
		  <p>{#download#} <a href="{$URL}">{#xml#}</a></p>
		  </div>
		  
		  <form action="sitemap.php" method="post">
				<input type="hidden" name="update" value="1" />
				<input type="hidden" name="tab" value="export" />     
				<p class="submit"><input type="submit" value="{#sitemapExportSubmit#}" class="button orange" /></p>
		  </form>
		  
	 </div>

<div class="tabbertab{if isset($cTab) && $cTab == 'downloads'} tabbertabdefault{/if}">
		  <h2>{#sitemapDownload#}</h2>
		 
	 {if isset($oSitemapDownload_arr) && $oSitemapDownload_arr|@count > 0}
		  <form name="sitemapdownload" method="POST" action="sitemapexport.php">
		  <input type="hidden" name="{$session_name}" value="{$session_id}">
		  <input type="hidden" name="download_edit" value="1">
		  <input type="hidden" name="tab" value="downloads">
		  {if $oBlaetterNaviDownload->nAktiv == 1}
		  <div class="container">
					 <p>
					 {$oBlaetterNaviDownload->nVon} - {$oBlaetterNaviDownload->nBis} {#ratingFrom#} {$oBlaetterNaviDownload->nAnzahl}
					 {if $oBlaetterNaviDownload->nAktuelleSeite == 1}
						  << {#ratingPrevious#}
					 {else}
						  <a href="bewertung.php?s1={$oBlaetterNaviDownload->nVoherige}&tab=downloads"><< {#ratingPrevious#}</a>
					 {/if}
					 
					 {if $oBlaetterNaviDownload->nAnfang != 0}<a href="bewertung.php?s1={$oBlaetterNaviDownload->nAnfang}&tab=downloads">{$oBlaetterNaviDownload->nAnfang}</a> ... {/if}
					 {foreach name=blaetternavi from=$oBlaetterNaviDownload->nBlaetterAnzahl_arr item=Blatt}
						  {if $oBlaetterNaviDownload->nAktuelleSeite == $Blatt}[{$Blatt}]
						  {else}
								<a href="bewertung.php?s1={$Blatt}&tab=downloads">{$Blatt}</a>
						  {/if}
					 {/foreach}
					 
					 {if $oBlaetterNaviDownload->nEnde != 0} ... <a href="bewertung.php?s1={$oBlaetterNaviDownload->nEnde}&tab=downloads">{$oBlaetterNaviDownload->nEnde}</a>{/if}
					 
					 {if $oBlaetterNaviDownload->nAktuelleSeite == $oBlaetterNaviDownload->nSeiten}
						  {#ratingNext#} >>
					 {else}
						  <a href="bewertung.php?s1={$oBlaetterNaviDownload->nNaechste}&tab=downloads">{#ratingNext#} >></a>
					 {/if}
					 
					 </p>
		  </div>
		  {/if}
		  <div id="payment">
				<div id="tabellenBewertung">
				<table>
					 <tr>
						  <th class="th-1">&nbsp;</th>
						  <th class="th-2">{#sitemapName#}</th>
						  <th class="th-3">{#sitemapBot#}</th>
						  <th class="th-5">{#sitemapIP#}</th>
						  <th class="th-4">{#sitemapUserAgent#}</th>
						  <th class="th-6">{#sitemapDate#}</th>
						  
					 </tr>
				{foreach name=sitemapdownloads from=$oSitemapDownload_arr item=oSitemapDownload}
					 <tr class="tab_bg{$smarty.foreach.sitemapdownloads.iteration%2}">                    
						  <td class="TD1"><input name="kSitemapTracker[]" type="checkbox" value="{$oSitemapDownload->kSitemapTracker}"></td>
						  <td class="TD2">{$oSitemapDownload->cSitemap}</td>
						  <td class="TD3">{$oSitemapDownload->cBot}</td>
						  <td class="TD5">{$oSitemapDownload->cIP}</td>
						  <td class="TD4">{$oSitemapDownload->cUserAgent}</td>
						  <td class="TD6">{$oSitemapDownload->dErstellt_DE}</td>
					 </tr>
				{/foreach}
					 <tr>
						  <td class="TD1"><input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);"></td>
						  <td colspan="6" class="TD7">{#sitemapSelectAll#}</td>
					 </tr>
				</table>
				<p style="text-align:center;"><input name="loeschen" type="submit" value="{#sitemapDelete#}" /></p>
				</div>
		  </div>
		  </form>
		  
	 {else}
		  <div class="box_info">{#noDataAvailable#}</div>
	 {/if}
		  
	 </div>

<div class="tabbertab{if isset($cTab) && $cTab == 'report'} tabbertabdefault{/if}">
	 
		  <h2>{#sitemapReport#}</h2>
		 
	 {if isset($oSitemapReport_arr) && $oSitemapReport_arr|@count > 0}
		  <form name="sitemapreport" method="POST" action="sitemapexport.php">
		  <input type="hidden" name="{$session_name}" value="{$session_id}">
		  <input type="hidden" name="report_edit" value="1">
		  <input type="hidden" name="tab" value="report">
		  {if isset($oBlaetterNaviReport->nAktiv) && $oBlaetterNaviReport->nAktiv == 1}
		  <div class="container">
					 <p>
					 {$oBlaetterNaviReport->nVon} - {$oBlaetterNaviReport->nBis} {#ratingFrom#} {$oBlaetterNaviReport->nAnzahl}
					 {if $oBlaetterNaviReport->nAktuelleSeite == 1}
						  << {#ratingPrevious#}
					 {else}
						  <a href="bewertung.php?s2={$oBlaetterNaviReport->nVoherige}&tab=report"><< {#ratingPrevious#}</a>
					 {/if}
					 
					 {if $oBlaetterNaviReport->nAnfang != 0}<a href="bewertung.php?s2={$oBlaetterNaviReport->nAnfang}&tab=report">{$oBlaetterNaviReport->nAnfang}</a> ... {/if}
					 {foreach name=blaetternavi from=$oBlaetterNaviReport->nBlaetterAnzahl_arr item=Blatt}
						  {if $oBlaetterNaviReport->nAktuelleSeite == $Blatt}[{$Blatt}]
						  {else}
								<a href="bewertung.php?s2={$Blatt}&tab=report">{$Blatt}</a>
						  {/if}
					 {/foreach}
					 
					 {if $oBlaetterNaviReport->nEnde != 0} ... <a href="bewertung.php?s2={$oBlaetterNaviReport->nEnde}&tab=report">{$oBlaetterNaviReport->nEnde}</a>{/if}
					 
					 {if $oBlaetterNaviReport->nAktuelleSeite == $oBlaetterNaviReport->nSeiten}
						  {#ratingNext#} >>
					 {else}
						  <a href="bewertung.php?s2={$oBlaetterNaviReport->nNaechste}&tab=report">{#ratingNext#} >></a>
					 {/if}
					 
					 </p>
		  </div>
		  {/if}
		  <div id="payment">
				<div id="tabellenBewertung">
				<table>
					 <tr>
						  <th class="check"></th>
						  <th class="th-1"></th>
						  <th class="tleft">{#sitemapProcessTime#}</th>
						  <th class="th-3">{#sitemapTotalURL#}</th>
						  <th class="th-5">{#sitemapDate#}</th>                                
					 </tr>
				{foreach name=sitemapreports from=$oSitemapReport_arr item=oSitemapReport}
					 <tr class="tab_bg{$smarty.foreach.sitemapreports.iteration%2}">                    
						  <td class="check"><input name="kSitemapReport[]" type="checkbox" value="{$oSitemapReport->kSitemapReport}"></td>								
				{if isset($oSitemapReport->oSitemapReportFile_arr) && $oSitemapReport->oSitemapReportFile_arr|@count > 0}
					<td><a href="#" onclick="$('#info_{$oSitemapReport->kSitemapReport}').toggle();return false;"><img src="{$currentTemplateDir}gfx/layout/more.png" alt="+" /></a></td>
				{else}
					<td class="TD1">&nbsp;</td>
				{/if}								
						  <td class="tcenter">{$oSitemapReport->fVerarbeitungszeit} sek.</td>
						  <td class="tcenter">{$oSitemapReport->nTotalURL}</td>
						  <td class="tcenter">{$oSitemapReport->dErstellt_DE}</td>
					 </tr>
			
		{if isset($oSitemapReport->oSitemapReportFile_arr) && $oSitemapReport->oSitemapReportFile_arr|@count > 0}
			<tr id="info_{$oSitemapReport->kSitemapReport}" style="display: none;">
				<td>&nbsp;</td>
				<td colspan="4">

					<table border="0" cellspacing="1" cellpadding="0" width="100%">
						<tr>
							<th class="tleft">{#sitemapProcessTime#}</th>
							<th class="th-2">{#sitemapCountURL#}</th>
							<th class="th-3">{#sitemapDate#}</th>											
						</tr>
							
					{foreach name=sitemapreportfiles from=$oSitemapReport->oSitemapReportFile_arr item=oSitemapReportFile}
						<tr class="tab_bg{$smarty.foreach.sitemapreports.iteration%2}">
							<td class="TD1">{$oSitemapReportFile->cDatei}</td>
							<td class="tcenter">{$oSitemapReportFile->nAnzahlURL}</td>
							<td class="tcenter">{$oSitemapReportFile->fGroesse} KB</td>
						</tr>
					{/foreach}
					</table>
					
				</td>
			</tr>
		{/if}
				{/foreach}
					 <tr>
						  <td class="check"><input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);"></td>
						  <td colspan="4" class="TD5">{#sitemapSelectAll#}</td>
					 </tr>
				</table>
				<p style="text-align:center;"><input name="loeschen" type="submit" value="{#sitemapDelete#}" class="button orange" /></p>
				</div>
		  </div>
		  </form>
		  
	 {else}
		  <div class="box_info">{#noDataAvailable#}</div>
	 {/if}
		  
	 </div>
	 
	 <div class="tabbertab{if isset($cTab) && $cTab == 'einstellungen'} tabbertabdefault{/if}">
	 
		  <h2>{#sitemapSettings#}</h2>
 
		  <form name="einstellen" method="post" action="sitemapexport.php">
		  <input type="hidden" name="{$session_name}" value="{$session_id}" />
		  <input type="hidden" name="einstellungen" value="1" />
		  <input type="hidden" name="tab" value="einstellungen" />
		  <div class="settings">
				{foreach name=conf from=$oConfig_arr item=oConfig}
					 {if $oConfig->cConf == "Y"}
						  <p><label for="{$oConfig->cWertName}">({$oConfig->kEinstellungenConf}) {$oConfig->cName} {if $oConfig->cBeschreibung}<img src="{$currentTemplateDir}gfx/help.png" alt="{$oConfig->cBeschreibung}" title="{$oConfig->cBeschreibung}" style="vertical-align:middle; cursor:help;" />{/if}</label>
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
						  {if $oConfig->cName}<div class="category">({$oConfig->kEinstellungenConf}) {$oConfig->cName}</div>{/if}
					 {/if}
				{/foreach}
		  </div>
		  <p class="submit"><input type="submit" value="{#sitemapSave#}" class="button orange" /></p>
		  </form>
		  
	 </div>
	 
</div>

</div>

<div class="container">
<div class="settings">

</div>
{include file='tpl_inc/footer.tpl'}