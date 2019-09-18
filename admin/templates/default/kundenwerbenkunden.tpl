{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: kundenwerbenkunden.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
{config_load file="$lang.conf" section="kundenwerbenkunden"}
{include file='tpl_inc/header.tpl'}

<script type="text/javascript" src="templates/default/js/versandart_bruttonetto.js"></script>

{include file="tpl_inc/seite_header.tpl" cTitel=#kundenwerbenkunden# cBeschreibung=#kundenwerbenkundenDesc# cDokuURL=#kundenwerbenkundenURL#}
<div id="content">

	{if isset($hinweis) && $hinweis|count_characters > 0}			
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}			
		<p class="box_error">{$fehler}</p>
	{/if}
				
	<div class="container">
	
			<div class="tabber">
				 
				 <div class="tabbertab{if isset($cTab) && $cTab == 'einladungen'} tabbertabdefault{/if}">
				 
					  <h2>{#kundenwerbenkundenNotReggt#}</h2>
					  
				 {if $oKwKNichtReg_arr|@count > 0 && $oKwKNichtReg_arr}
					  <form name="umfrage" method="post" action="kundenwerbenkunden.php">
					  <input type="hidden" name="{$session_name}" value="{$session_id}">
					  <input type="hidden" name="KwK" value="1">
					  <input type="hidden" name="nichtreggt_loeschen" value="1">                    
					  <input type="hidden" name="s1" value="{$oBlaetterNaviNichtReg->nAktuelleSeite}">
					  <input type="hidden" name="tab" value="einladungen">
								 
					  {if $oBlaetterNaviNichtReg->nAktiv == 1}
					  <div class="container">
								 <p>
								 {$oBlaetterNaviNichtReg->nVon} - {$oBlaetterNaviNichtReg->nBis} {#from#} {$oBlaetterNaviNichtReg->nAnzahl}
								 {if $oBlaetterNaviNichtReg->nAktuelleSeite == 1}
									  << {#back#}
								 {else}
									  <a href="kundenwerbenkunden.php?s1={$oBlaetterNaviNichtReg->nVoherige}&tab=einladungen"><< {#back#}</a>
								 {/if}
								 
								 {if $oBlaetterNaviNichtReg->nAnfang != 0}<a href="kundenwerbenkunden.php?s1={$oBlaetterNaviNichtReg->nAnfang}&tab=einladungen">{$oBlaetterNaviNichtReg->nAnfang}</a> ... {/if}
								 {foreach name=blaetternavi from=$oBlaetterNaviNichtReg->nBlaetterAnzahl_arr item=Blatt}
									  {if $oBlaetterNaviNichtReg->nAktuelleSeite == $Blatt}[{$Blatt}]
									  {else}
											<a href="kundenwerbenkunden.php?s1={$Blatt}&tab=einladungen">{$Blatt}</a>
									  {/if}
								 {/foreach}
								 
								 {if $oBlaetterNaviNichtReg->nEnde != 0} ... <a href="kundenwerbenkunden.php?s1={$oBlaetterNaviNichtReg->nEnde}&tab=einladungen">{$oBlaetterNaviNichtReg->nEnde}</a>{/if}
								 
								 {if $oBlaetterNaviNichtReg->nAktuelleSeite == $oBlaetterNaviNichtReg->nSeiten}
									  {#next#} >>
								 {else}
									  <a href="kundenwerbenkunden.php?s1={$oBlaetterNaviNichtReg->nNaechste}&tab=einladungen">{#next#} >></a>
								 {/if}
								 
								 </p>
					  </div>
					  {/if}
					  
					  <div id="payment">
							<div id="tabellenLivesuche">
							<table>
								 <tr>
									  <th class="check"></th>
									  <th class="tleft">{#kundenwerbenkundenName#}</th>
									  <th class="tleft">{#kundenwerbenkundenFromReg#}</th>
									  <th class="tleft">{#kundenwerbenkundenCredit#}</th>
									  <th class="th-5">{#kundenwerbenkundenDateInvite#}</th>
								 </tr>
							{foreach name=nichtregkunden from=$oKwKNichtReg_arr item=oKwKNichtReg}
								 <tr class="tab_bg{$smarty.foreach.nichtregkunden.iteration%2}">
									  <td class="check"><input type="checkbox" name="kKundenWerbenKunden[]" value="{$oKwKNichtReg->kKundenWerbenKunden}"></td>
									  <td class="tleft"><b>{$oKwKNichtReg->cVorname} {$oKwKNichtReg->cNachname}</b><br>{$oKwKNichtReg->cEmail}</td>
									  <td class="tleft"><b>{$oKwKNichtReg->cBestandVorname} {$oKwKNichtReg->cBestandNachname}</b><br>{$oKwKNichtReg->cMail}</td>
									  <td class="tleft">{getCurrencyConversionSmarty fPreisBrutto=$oKwKNichtReg->fGuthaben}</td>
									  <td class="tcenter">{$oKwKNichtReg->dErstellt_de}</td>
								 </tr>
							{/foreach}
							</table>
							</div>
					  </div>            
					  <p class="submit"><input name="loeschen" type="submit" value="{#kundenwerbenkundenDelete#}" class="button orange" /></p>
					  </form>
					  
				 {else}
					  <div class="box_info container">{#noDataAvailable#}</div>
				 {/if}
					  
				 </div>
				 
				 <div class="tabbertab{if isset($cTab) && $cTab == 'registrierung'} tabbertabdefault{/if}">
				 
					  <h2>{#kundenwerbenkundenReggt#}</h2>
				 
				 {if $oKwKReg_arr && $oKwKReg_arr|@count > 0}
					  {if $oBlaetterNaviReg->nAktiv == 1}
					  <div class="container">
								 <p>
								 {$oBlaetterNaviReg->nVon} - {$oBlaetterNaviReg->nBis} {#from#} {$oBlaetterNaviReg->nAnzahl}
								 {if $oBlaetterNaviReg->nAktuelleSeite == 1}
									  << {#back#}
								 {else}
									  <a href="kundenwerbenkunden.php?s2={$oBlaetterNaviReg->nVoherige}&tab=registrierung"><< {#back#}</a>
								 {/if}
								 
								 {if $oBlaetterNaviReg->nAnfang != 0}<a href="kundenwerbenkunden.php?s2={$oBlaetterNaviReg->nAnfang}&tab=registrierung">{$oBlaetterNaviReg->nAnfang}</a> ... {/if}
								 {foreach name=blaetternavi from=$oBlaetterNaviReg->nBlaetterAnzahl_arr item=Blatt}
									  {if $oBlaetterNaviReg->nAktuelleSeite == $Blatt}[{$Blatt}]
									  {else}
											<a href="kundenwerbenkunden.php?s2={$Blatt}&tab=registrierung">{$Blatt}</a>
									  {/if}
								 {/foreach}
								 
								 {if $oBlaetterNaviReg->nEnde != 0} ... <a href="kundenwerbenkunden.php?s2={$oBlaetterNaviReg->nEnde}&tab=registrierung">{$oBlaetterNaviReg->nEnde}</a>{/if}
								 
								 {if $oBlaetterNaviReg->nAktuelleSeite == $oBlaetterNaviReg->nSeiten}
									  {#next#} >>
								 {else}
									  <a href="kundenwerbenkunden.php?s2={$oBlaetterNaviNichtReg->nNaechste}&tab=registrierung">{#next#} >></a>
								 {/if}
								 
								 </p>
					  </div>
					  {/if}
					  
					  <div id="payment">
							<div id="tabellenLivesuche">
							<table>
								 <tr>
									  <th class="tleft">{#kundenwerbenkundenRegName#}</th>
									  <th class="tleft">{#kundenwerbenkundenFromReg#}</th>
									  <th class="tleft">{#kundenwerbenkundenCredit#}</th>
									  <th class="th-4">{#kundenwerbenkundenDateInvite#}</th>
									  <th class="th-5">{#kundenwerbenkundenDateErstellt#}</th>
								 </tr>
							{foreach name=regkunden from=$oKwKReg_arr item=oKwKReg}
								 <tr class="tab_bg{$smarty.foreach.regkunden.iteration%2}">
									  <td class="TD2"><b>{$oKwKReg->cVorname} {$oKwKReg->cNachname}</b><br>{$oKwKReg->cEmail}</td>
									  <td class="TD2"><b>{$oKwKReg->cBestandVorname} {$oKwKReg->cBestandNachname}</b><br>{$oKwKReg->cMail}</td>
									  <td class="TD3">{getCurrencyConversionSmarty fPreisBrutto=$oKwKReg->fGuthaben}</td>
									  <td class="tcenter">{$oKwKReg->dErstellt_de}</td>
									  <td class="tcenter">{$oKwKReg->dBestandErstellt_de}</td>
								 </tr>
							{/foreach}
							</table>
							</div>
					  </div>  
					  
				 {else}
					  <div class="box_info container">{#noDataAvailable#}</div>
				 {/if}
					  
				 </div>
				 
				 <div class="tabbertab{if isset($cTab) && $cTab == 'praemie'} tabbertabdefault{/if}">
				 
					  <h2>{#kundenwerbenkundenBonis#}</h2>
				 
				 {if $oKwKBestandBonus_arr|@count > 0 && $oKwKBestandBonus_arr}
					  {if $oBlaetterNaviPraemie->nAktiv == 1}
					  <div class="container">
								 <p>
								 {$oBlaetterNaviPraemie->nVon} - {$oBlaetterNaviPraemie->nBis} {#from#} {$oBlaetterNaviPraemie->nAnzahl}
								 {if $oBlaetterNaviPraemie->nAktuelleSeite == 1}
									  << {#back#}
								 {else}
									  <a href="kundenwerbenkunden.php?s3={$oBlaetterNaviPraemie->nVoherige}&tab=praemie"><< {#back#}</a>
								 {/if}
								 
								 {if $oBlaetterNaviPraemie->nAnfang != 0}<a href="kundenwerbenkunden.php?s3={$oBlaetterNaviPraemie->nAnfang}&tab=praemie">{$oBlaetterNaviPraemie->nAnfang}</a> ... {/if}
								 {foreach name=blaetternavi from=$oBlaetterNaviPraemie->nBlaetterAnzahl_arr item=Blatt}
									  {if $oBlaetterNaviPraemie->nAktuelleSeite == $Blatt}[{$Blatt}]
									  {else}
											<a href="kundenwerbenkunden.php?s3={$Blatt}&tab=praemie">{$Blatt}</a>
									  {/if}
								 {/foreach}
								 
								 {if $oBlaetterNaviPraemie->nEnde != 0} ... <a href="kundenwerbenkunden.php?s3={$oBlaetterNaviPraemie->nEnde}&tab=praemie">{$oBlaetterNaviPraemie->nEnde}</a>{/if}
								 
								 {if $oBlaetterNaviPraemie->nAktuelleSeite == $oBlaetterNaviPraemie->nSeiten}
									  {#next#} >>
								 {else}
									  <a href="kundenwerbenkunden.php?s3={$oBlaetterNaviNichtReg->nNaechste}&tab=praemie">{#next#} >></a>
								 {/if}
								 
								 </p>
					  </div>
					  {/if}
											
					  <div id="payment">
							<div id="tabellenLivesuche">
							<table>
								 <tr>
									  <th class="tleft">{#kundenwerbenkundenFromReg#}</th>
									  <th class="tleft">{#kundenwerbenkundenCredit#}</th>
									  <th class="">{#kundenwerbenkundenExtraPoints#}</th>
									  <th class="th-4">{#kundenwerbenkundenDateBoni#}</th>
								 </tr>
							{foreach name=letzte100bonis from=$oKwKBestandBonus_arr item=oKwKBestandBonus}
								 <tr class="tab_bg{$smarty.foreach.letzte100bonis.iteration%2}">
									  <td class="TD2"><b>{$oKwKBestandBonus->cBestandVorname} {$oKwKBestandBonus->cBestandNachname}</b><br>{$oKwKBestandBonus->cMail}</td>
									  <td class="TD2">{getCurrencyConversionSmarty fPreisBrutto=$oKwKBestandBonus->fGuthaben}</td>
									  <td class="tcenter">{$oKwKBestandBonus->nBonuspunkte}</td>
									  <td class="tcenter">{$oKwKBestandBonus->dErhalten_de}</td>
								 </tr>
							{/foreach}
							</table>
							</div>
					  </div>
				 {else}
					  <div class="box_info container">{#noDataAvailable#}</div>
				 {/if}
				 
				 </div>
				 
				 <div class="tabbertab{if isset($cTab) && $cTab == 'einstellungen'} tabbertabdefault{/if}">
				 
					  <h2>{#kundenwerbenkundenSettings#}</h2>
					  
					  <form name="einstellen" method="post" action="kundenwerbenkunden.php">
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
								 {elseif $oConfig->cInputTyp=="selectkdngrp"}
									  <select name="{$oConfig->cWertName}[]" id="{$oConfig->cWertName}" class="combo"> 
									  {foreach name=selectfor from=$oConfig->ConfWerte item=wert}
											<option value="{$wert->kKundengruppe}" {foreach name=werte from=$oConfig->gesetzterWert item=gesetzterWert}{if $gesetzterWert->cWert == $wert->kKundengruppe}selected{/if}{/foreach}>{$wert->cName}</option>
									  {/foreach}
									  </select>
								 {else}
									  <input type="text" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}"  value="{$oConfig->gesetzterWert}" tabindex="1"{if $oConfig->cWertName|strpos:"_bestandskundenguthaben" || $oConfig->cWertName|strpos:"_neukundenguthaben"} onKeyUp="javascript:setzePreisAjax(false, 'EinstellungAjax_{$oConfig->cWertName}', this);"{/if} />{if $oConfig->cWertName|strpos:"_bestandskundenguthaben" || $oConfig->cWertName|strpos:"_neukundenguthaben"} <span id="EinstellungAjax_{$oConfig->cWertName}"></span>{/if}</p>
								 {/if}
								 {else}
									  {if $oConfig->cName}<h3 style="text-align:center;">({$oConfig->kEinstellungenConf}) {$oConfig->cName}</h3>{/if}
								 {/if}
							{/foreach}
					  </div>
					  
					  <p class="submit"><input type="submit" value="{#kundenwerbenkundenSave#}" class="button orange" /></p>
					  </form>
					  
				 </div>
				 
			</div>
					
	</div>
</div>

<script type="text/javascript">
{foreach name=conf from=$oConfig_arr item=oConfig}
	{if $oConfig->cWertName|strpos:"_bestandskundenguthaben" || $oConfig->cWertName|strpos:"_neukundenguthaben"}
		xajax_getCurrencyConversionAjax(0, document.getElementById('{$oConfig->cWertName}').value, 'EinstellungAjax_{$oConfig->cWertName}');
	{/if}
{/foreach}
</script>

{include file='tpl_inc/footer.tpl'}