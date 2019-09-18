{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: bewertung_uebersicht.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehemr@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}

{include file="tpl_inc/seite_header.tpl" cTitel=#votesystem# cBeschreibung=#votesystemDesc# cDokuURL=#votesystemURL#}
<div id="content">	
	 {if isset($hinweis) && $hinweis|count_characters > 0}			
		  <p class="box_success">{$hinweis}</p>
	 {/if}
	 {if isset($fehler) && $fehler|count_characters > 0}			
		  <p class="box_error">{$fehler}</p>
	 {/if}
	
	<div class="container">
		<form name="sprache" method="post" action="bewertung.php">
			<p class="txtCenter">
			<label for="{#changeLanguage#}">{#changeLanguage#}:</strong></label>
			<input type="hidden" name="sprachwechsel" value="1">
			<select id="{#changeLanguage#}" name="kSprache" class="selectBox" onchange="javascript:document.sprache.submit();">
			{foreach name=sprachen from=$Sprachen item=sprache}
				<option value="{$sprache->kSprache}" {if $sprache->kSprache==$smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
			{/foreach}
			</select>
				 </p>
			</form>
			
			<div class="tabber">
				 
				 <div class="tabbertab{if isset($cTab) && $cTab == 'freischalten'} tabbertabdefault{/if}">
					  <h2>{#ratingsInaktive#}</h2>
				 {if $oBewertung_arr && $oBewertung_arr|@count > 0}
					  <form method="POST" action="bewertung.php">
					  <input type="hidden" name="{$session_name}" value="{$session_id}">
					  <input type="hidden" name="bewertung_nicht_aktiv" value="1">
					  <input type="hidden" name="tab" value="freischalten">
					  {if $oBlaetterNaviInaktiv->nAktiv == 1}
					  <div class="content">
								 <p>
								 {$oBlaetterNaviInaktiv->nVon} - {$oBlaetterNaviInaktiv->nBis} {#ratingFrom#} {$oBlaetterNaviInaktiv->nAnzahl}
								 {if $oBlaetterNaviInaktiv->nAktuelleSeite == 1}
									  << {#ratingPrevious#}
								 {else}
									  <a href="bewertung.php?s1={$oBlaetterNaviInaktiv->nVoherige}&tab=freischalten"><< {#ratingPrevious#}</a>
								 {/if}
								 
								 {if $oBlaetterNaviInaktiv->nAnfang != 0}<a href="bewertung.php?s1={$oBlaetterNaviInaktiv->nAnfang}&tab=freischalten">{$oBlaetterNaviInaktiv->nAnfang}</a> ... {/if}
								 {foreach name=blaetternavi from=$oBlaetterNaviInaktiv->nBlaetterAnzahl_arr item=Blatt}
									  {if $oBlaetterNaviInaktiv->nAktuelleSeite == $Blatt}[{$Blatt}]
									  {else}
											<a href="bewertung.php?s1={$Blatt}&tab=freischalten">{$Blatt}</a>
									  {/if}
								 {/foreach}
								 
								 {if $oBlaetterNaviInaktiv->nEnde != 0} ... <a href="bewertung.php?s1={$oBlaetterNaviInaktiv->nEnde}&tab=freischalten">{$oBlaetterNaviInaktiv->nEnde}</a>{/if}
								 
								 {if $oBlaetterNaviInaktiv->nAktuelleSeite == $oBlaetterNaviInaktiv->nSeiten}
									  {#ratingNext#} >>
								 {else}
									  <a href="bewertung.php?s1={$oBlaetterNaviInaktiv->nNaechste}&tab=freischalten">{#ratingNext#} >></a>
								 {/if}
								 
								 </p>
					  </div>
					  {/if}
							<table>
								<thead>
								 <tr>
									  <th class="check">&nbsp;</th>
									  <th class="tleft">{#productName#}</th>
									  <th class="tleft">{#customerName#}</th>
									  <th class="tleft">{#ratingText#}</th>
									  <th class="th-5">{#ratingStars#}</th>
									  <th class="th-6">{#ratingDate#}</th>
									  <th class="th-7">&nbsp;</th>
								 </tr>
								 </thead>
								<tbody>
					  {if $oBewertung_arr && $oBewertung_arr|@count > 0}
							{foreach name=bewertung from=$oBewertung_arr item=oBewertung key=kKey}
								 <tr class="tab_bg{$smarty.foreach.bewertung.iteration%2}">
									  <input type="hidden" name="kArtikel[{$kKey}]" value="{$oBewertung->kArtikel}">
									  <td class="check"><input name="kBewertung[{$kKey}]" type="checkbox" value="{$oBewertung->kBewertung}"></td>
									  <td class="TD2"><a href="../index.php?a={$oBewertung->kArtikel}" target="_blank">{$oBewertung->ArtikelName}</td>
									  <td class="TD3">{$oBewertung->cName}.</td>
									  <td class="TD4"><b>{$oBewertung->cTitel}</b><br>{$oBewertung->cText}</td>
									  <td class="tcenter">{$oBewertung->nSterne}</td>
									  <td class="tcenter">{$oBewertung->Datum}</td>
									  <td class="tcenter">
										<a href="bewertung.php?a=editieren&kBewertung={$oBewertung->kBewertung}&{$session_name}={$session_id}&tab=freischalten" class="button edit">{#ratingEdit#}</a>
									  </td>
								 </tr>
								 </tbody>
							{/foreach}
								<tfoot>
								 <tr>
									  <td class="check"><input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);"></td>
									  <td colspan="6" class="TD7">{#ratingSelectAll#}</td>
								 </tr>
								 </tfoot>
					  {/if}
							</table>
							<div class="save_wrapper">
								<input name="aktivieren" type="submit" value="{#ratingActive#}" class="button orange" />
								<input name="loeschen" type="submit" value="{#ratingDelete#}" class="button orange" />
							</div>
					  </form>
					 
				 {else}
					  <div class="box_info container">{#noDataAvailable#}</div>
				 {/if} 
					  
				 </div>
				 
				 <div class="tabbertab{if isset($cTab) && $cTab == 'letzten50'} tabbertabdefault{/if}">
					  
					  <h2>{#ratingLast50#}</h2>
				 {if $oBewertungLetzten50_arr && $oBewertungLetzten50_arr|@count > 0}
					  <form name="letzten50" method="POST" action="bewertung.php">
					  <input type="hidden" name="{$session_name}" value="{$session_id}">
					  <input type="hidden" name="bewertung_aktiv" value="1">
					  <input type="hidden" name="tab" value="letzten50">
					  {if $oBlaetterNaviAktiv->nAktiv == 1}
					  <div class="content">
								 <p>
								 {$oBlaetterNaviAktiv->nVon} - {$oBlaetterNaviAktiv->nBis} {#ratingFrom#} {$oBlaetterNaviAktiv->nAnzahl}
								 {if $oBlaetterNaviAktiv->nAktuelleSeite == 1}
									  << {#ratingPrevious#}
								 {else}
									  <a href="bewertung.php?s2={$oBlaetterNaviAktiv->nVoherige}&tab=letzten50"><< {#ratingPrevious#}</a>
								 {/if}
								 
								 {if $oBlaetterNaviAktiv->nAnfang != 0}<a href="bewertung.php?s2={$oBlaetterNaviAktiv->nAnfang}&tab=letzten50">{$oBlaetterNaviAktiv->nAnfang}</a> ... {/if}
								 {foreach name=blaetternavi from=$oBlaetterNaviAktiv->nBlaetterAnzahl_arr item=Blatt}
									  {if $oBlaetterNaviAktiv->nAktuelleSeite == $Blatt}[{$Blatt}]
									  {else}
											<a href="bewertung.php?s2={$Blatt}&tab=letzten50">{$Blatt}</a>
									  {/if}
								 {/foreach}
								 
								 {if $oBlaetterNaviAktiv->nEnde != 0} ... <a href="bewertung.php?s2={$oBlaetterNaviAktiv->nEnde}&tab=letzten50">{$oBlaetterNaviAktiv->nEnde}</a>{/if}
								 
								 {if $oBlaetterNaviAktiv->nAktuelleSeite == $oBlaetterNaviAktiv->nSeiten}
									  {#ratingNext#} >>
								 {else}
									  <a href="bewertung.php?s2={$oBlaetterNaviAktiv->nNaechste}&tab=letzten50">{#ratingNext#} >></a>
								 {/if}
								 
								 </p>
					  </div>
					  {/if}
							<table>
								<thead>
								 <tr>
									  <th class="check">&nbsp;</th>
									  <th class="tleft">{#productName#}</th>
									  <th class="tleft">{#customerName#}</th>
									  <th class="tleft">{#ratingText#}</th>
									  <th class="th-5">{#ratingStars#}</th>
									  <th class="th-6">{#ratingDate#}</th>
									  <th class="th-7">&nbsp;</th>
								 </tr>
								 </thead>
								<tbody>
							{foreach name=bewertungletzten50 from=$oBewertungLetzten50_arr item=oBewertungLetzten50}
								 <tr class="tab_bg{$smarty.foreach.bewertungletzten50.iteration%2}">                    
									  <td class="check"><input name="kBewertung[]" type="checkbox" value="{$oBewertungLetzten50->kBewertung}"><input type="hidden" name="kArtikel[]" value="{$oBewertungLetzten50->kArtikel}"></td>
									  <td class="TD2"><a href="../index.php?a={$oBewertungLetzten50->kArtikel}" target="_blank">{$oBewertungLetzten50->ArtikelName}</td>
									  <td class="TD3">{$oBewertungLetzten50->cName}.</td>
									  <td class="TD4"><b>{$oBewertungLetzten50->cTitel}</b><br>{$oBewertungLetzten50->cText}</td>
									  <td class="tcenter">{$oBewertungLetzten50->nSterne}</td>
									  <td class="tcenter">{$oBewertungLetzten50->Datum}</td>
									  <td class="tcenter7">
										<a href="bewertung.php?a=editieren&kBewertung={$oBewertungLetzten50->kBewertung}&{$session_name}={$session_id}&tab=letzten50" class="button edit">{#ratingEdit#}</a>
									  </td>
								 </tr>
							{/foreach}
							</tbody>
								<tfoot>
								 <tr>
									  <td class="check"><input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);"></td>
									  <td colspan="6" class="TD7">{#ratingSelectAll#}</td>
								 </tr>
								</tfoot>
							</table>
							<div class="save_wrapper"><input name="loeschen" type="submit" value="{#ratingDelete#}" class="button orange" /></div>
					  </form>
					  
				 {else}
					  <div class="box_info container">{#noDataAvailable#}</div>
				 {/if}
				 
				 </div>
				 
				 <div class="tabbertab{if isset($cTab) && $cTab == 'artikelbewertung'} tabbertabdefault{/if}">
					  
					  <br />
					  <h2>{#ratingForProduct#}</h2>                                
					  <form name="artikelbewertung" method="POST" action="bewertung.php">
					  <input type="hidden" name="{$session_name}" value="{$session_id}">
					  <input type="hidden" name="bewertung_aktiv" value="1">
					  <input type="hidden" name="tab" value="artikelbewertung">
							<div class="block container top">
								<p><b>{#ratingcArtNr#}:</b> <input name="cArtNr" type="text" > <input name="submitSearch" type="submit" value="{#ratingSearch#}" class="button blue"></p>
							</div>
							
							{if isset($cArtNr) && $cArtNr|count_characters > 0}
								<div class="box_info container">{#ratingSearchedFor#}: {$cArtNr}</div>
							{/if}
							
	      			 {if $oBewertungAktiv_arr && $oBewertungAktiv_arr|@count > 0}
							<table>
								<thead>
								 <tr>
									  <th class="th-1">&nbsp;</th>
									  <th class="tleft">{#productName#}</th>
									  <th class="tleft">{#customerName#}</th>
									  <th class="tleft">{#ratingText#}</th>
									  <th class="th-5">{#ratingStars#}</th>
									  <th class="th-6">{#ratingDate#}</th>
									  <th class="th-7">&nbsp;</th>
								 </tr>
								 </thead>
								 <tbody>
							{foreach name=bewertungaktiv from=$oBewertungAktiv_arr item=oBewertungAktiv}
								 <tr class="tab_bg{$smarty.foreach.bewertungaktiv.iteration%2}">                    
									  <td class="TD1"><input name="kBewertung[]" type="checkbox" value="{$oBewertungAktiv->kBewertung}"><input type="hidden" name="kArtikel[]" value="{$oBewertungAktiv->kArtikel}"></td>
									  <td class="TD2"><a href="../index.php?a={$oBewertungAktiv->kArtikel}" target="_blank">{$oBewertungAktiv->ArtikelName}</td>
									  <td class="TD3">{$oBewertungAktiv->cName}.</td>
									  <td class="TD4"><b>{$oBewertungAktiv->cTitel}</b><br>{$oBewertungAktiv->cText}</td>
									  <td class="tcenter">{$oBewertungAktiv->nSterne}</td>
									  <td class="tcenter">{$oBewertungAktiv->Datum}</td>
									  <td class="tcenter"><a href="bewertung.php?a=editieren&kBewertung={$oBewertungAktiv->kBewertung}&{$session_name}={$session_id}&tab=artikelbewertung" class="button edit">{#ratingEdit#}</a></td>
								 </tr>
							{/foreach}
							</tbody>
								 <tfoot>
								 <tr>
									  <td class="TD1"><input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);"></td>
									  <td colspan="6" class="TD7">{#ratingSelectAll#}</td>
								 </tr>
								 </tfoot>
							</table>
							<div class="save_wrapper"><input name="loeschen" type="submit" value="{#ratingDelete#}" class="button orange" /></div>
				 {else}
					  <div class="box_info container">{#noDataAvailable#}</div>
				 {/if} 
											  
					  </form>
				 </div>
				 
				 <div class="tabbertab{if isset($cTab) && $cTab == 'einstellungen'} tabbertabdefault{/if}">
					  
					  <br />
					  <h2>{#ratingSettings#}</h2>
					  <form name="einstellen" method="post" action="bewertung.php">
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
									  <input type="text" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}"  value="{$oConfig->gesetzterWert}" tabindex="1"{if $oConfig->cWertName|strpos:"_guthaben"} onKeyUp="javascript:setzePreisAjax(false, 'EinstellungAjax_{$oConfig->cWertName}', this);"{/if} />{if $oConfig->cWertName|strpos:"_guthaben"} <span id="EinstellungAjax_{$oConfig->cWertName}"></span>{/if}</p>
								 {/if}
								 {else}
									  {if $oConfig->cName}<h3 style="text-align:center;">({$oConfig->kEinstellungenConf}) {$oConfig->cName}</h3>{/if}
								 {/if}
							{/foreach}
					  </div>
					  
					  <p class="submit"><input type="submit" value="{#ragingSave#}" class="button orange" /></p>
					  </form>    
				 </div>
			
			</div>
		 
	</div>
			
</div>

<script type="text/javascript">
{foreach name=conf from=$oConfig_arr item=oConfig}
	{if $oConfig->cWertName|strpos:"_guthaben"}
		xajax_getCurrencyConversionAjax(0, document.getElementById('{$oConfig->cWertName}').value, 'EinstellungAjax_{$oConfig->cWertName}');
	{/if}
{/foreach}
</script>