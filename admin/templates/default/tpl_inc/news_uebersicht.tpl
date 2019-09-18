{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: news_uebersicht.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
{include file="tpl_inc/seite_header.tpl" cTitel=#news# cBeschreibung=#newsDesc# cDokuURL=#newsURL#}
<div id="content">
	 {if isset($hinweis) && $hinweis|count_characters > 0}
		  <p class="box_success">{$hinweis}</p>
	 {/if}
	 {if isset($fehler) && $fehler|count_characters > 0}
		  <p class="box_error">{$fehler}</p>
	 {/if}
	
	<form name="sprache" method="post" action="news.php">
	<p class="txtCenter">
	<label for="{#changeLanguage#}">{#changeLanguage#}:</strong></label>
	<input type="hidden" name="sprachwechsel" value="1" />
	<select id="{#changeLanguage#}" name="kSprache" class="selectBox" onchange="javascript:document.sprache.submit();">
	{foreach name=sprachen from=$Sprachen item=sprache}
	<option value="{$sprache->kSprache}" {if $sprache->kSprache==$smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
	{/foreach}
	</select>
	</p>
	</form>

	  
	<div class="tabber">
		 
		 <div class="tabbertab{if isset($cTab) && $cTab == 'inaktiv'} tabbertabdefault{/if}">
		 
			  <h2>{#newsCommentActivate#}</h2>
			  
			 {if $oNewsKommentar_arr && $oNewsKommentar_arr|@count > 0}
			  <form method="POST" action="news.php">
					<input type="hidden" name="{$session_name}" value="{$session_id}" />
				  <input type="hidden" name="news" value="1" />
				  <input type="hidden" name="newskommentar_freischalten" value="1" />
				  <input type="hidden" name="nd" value="1" />
				  <input type="hidden" name="tab" value="inaktiv" />
				  <input type="hidden" name="s1" value="{$oBlaetterNaviKommentar->nAktuelleSeite}" />
			  {if $oBlaetterNaviKommentar->nAktiv == 1}
			  <div class="content">
						 <p>
						 {$oBlaetterNaviKommentar->nVon} - {$oBlaetterNaviKommentar->nBis} {#ratingFrom#} {$oBlaetterNaviKommentar->nAnzahl}
						 {if $oBlaetterNaviKommentar->nAktuelleSeite == 1}
							  << {#ratingPrevious#}
						 {else}
							  <a href="news.php?s1={$oBlaetterNaviKommentar->nVoherige}&tab=inaktiv"><< {#ratingPrevious#}</a>
						 {/if}
						 
						 {if $oBlaetterNaviKommentar->nAnfang != 0}<a href="news.php?s1={$oBlaetterNaviKommentar->nAnfang}&tab=inaktiv">{$oBlaetterNaviKommentar->nAnfang}</a> ... {/if}
						 {foreach name=blaetternavi from=$oBlaetterNaviKommentar->nBlaetterAnzahl_arr item=Blatt}
							  {if $oBlaetterNaviKommentar->nAktuelleSeite == $Blatt}[{$Blatt}]
							  {else}
									<a href="news.php?s1={$Blatt}&tab=inaktiv">{$Blatt}</a>
							  {/if}
						 {/foreach}
						 
						 {if $oBlaetterNaviKommentar->nEnde != 0} ... <a href="news.php?s1={$oBlaetterNaviKommentar->nEnde}&tab=inaktiv">{$oBlaetterNaviKommentar->nEnde}</a>{/if}
						 
						 {if isset($oBlaetterNaviKommentar->nAktuelleSeite) && $oBlaetterNaviKommentar->nAktuelleSeite == $oBlaetterNaviKommentar->nSeiten}
							  {#ratingNext#} >>
						 {else}
							  <a href="news.php?s1={$oBlaetterNaviKommentar->nNaechste}&tab=inaktiv">{#ratingNext#} >></a>
						 {/if}
						 
						 </p>
			  </div>
			  {/if}

				<table class="list">
					<thead>
					 <tr>
						  <th class="check">&nbsp;</th>
						  <th class="tleft">{#newsUser#}</th>
						  <th class="tleft">{#newsHeadline#}</th>
						  <th class="tleft">{#newsText#}</th>
						  <th class="th-5">{#newsDate#}</th>
						  <th class="th-6"></th>
					 </tr>
					 </thead>
					<tbody>
				{foreach name=newskommentare from=$oNewsKommentar_arr item=oNewsKommentar}
					 <tr class="tab_bg{$smarty.foreach.newskommentare.iteration%2}">
						  <td class="check"><input type="checkbox" name="kNewsKommentar[]" value="{$oNewsKommentar->kNewsKommentar}" /></td>
						  <td class="TD2">
						  {if $oNewsKommentar->cVorname|count_characters > 0}
								{$oNewsKommentar->cVorname} {$oNewsKommentar->cNachname}
						  {else}
								{$oNewsKommentar->cName}
						  {/if}
						  </td>
						  <td class="TD3">{$oNewsKommentar->cBetreff|truncate:50:"..."}</td>
						  <td class="TD4">{$oNewsKommentar->cKommentar|truncate:150:"..."}</td>
						  <td class="tcenter">{$oNewsKommentar->dErstellt_de}</td>
						  <td class="tcenter">
								<a href="news.php?news=1{if isset($oBlaetterNaviKommentar->nAktuelleSeite) && $oBlaetterNaviKommentar->nAktuelleSeite}&s1={$oBlaetterNaviKommentar->nAktuelleSeite}{/if}&kNews={$oNewsKommentar->kNews}&kNewsKommentar={$oNewsKommentar->kNewsKommentar}&nkedit=1&{$session_name}={$session_id}&tab=inaktiv" class="button edit">{#newsEdit#}</a>
						  </td>
					 </tr>
				{/foreach}
				</tbody>
				</table>

			   <div class="save_wrapper">
				<input name="freischalten" type="submit" value="{#newsActivate#}" class="button orange" />
				<input name="kommentareloeschenSubmit" type="submit" value="{#delete#}" class="button orange" />
				</div>
			  </form>
			  
		 {else}
			  <div class="box_info container">{#noDataAvailable#}</div>
		 {/if}
			  
		 </div>
		 
		 <div class="tabbertab{if isset($cTab) && $cTab == 'aktiv'} tabbertabdefault{/if}">
		 
			  <h2>{#newsOverview#}</h2>
			  
			  <div class="container top">
					<form name="erstellen" method="POST" action="news.php">
						<input type="hidden" name="{$session_name}" value="{$session_id}" />
						<input type="hidden" name="news" value="1" />
						<input type="hidden" name="erstellen" value="1" />
						<input type="hidden" name="tab" value="aktiv" />
						<input type="hidden" name="s2" value="{$oBlaetterNaviNews->nAktuelleSeite}" />
						<input name="news_erstellen" type="submit" value="{#newAdd#}" class="button add" />
				  </form>
			  </div>
			  
		 {if $oNews_arr|@count > 0 && $oNews_arr}                
			  <form name="news" method="post" action="news.php">
			  <input type="hidden" name="{$session_name}" value="{$session_id}" />
			  <input type="hidden" name="news" value="1" />
			  <input type="hidden" name="news_loeschen" value="1" />
			  <input type="hidden" name="tab" value="aktiv" />
			  <input type="hidden" name="s2" value="{$oBlaetterNaviNews->nAktuelleSeite}" />
			  {if $oBlaetterNaviNews->nAktiv == 1}
			  <div class="container">
						 <p>
						 {$oBlaetterNaviNews->nVon} - {$oBlaetterNaviNews->nBis} {#ratingFrom#} {$oBlaetterNaviNews->nAnzahl}
						 {if $oBlaetterNaviNews->nAktuelleSeite == 1}
							  << {#ratingPrevious#}
						 {else}
							  <a href="news.php?s2={$oBlaetterNaviNews->nVoherige}&tab=aktiv"><< {#ratingPrevious#}</a>
						 {/if}
						 
						 {if $oBlaetterNaviNews->nAnfang != 0}<a href="news.php?s2={$oBlaetterNaviNews->nAnfang}&tab=aktiv">{$oBlaetterNaviNews->nAnfang}</a> ... {/if}
						 {foreach name=blaetternavi from=$oBlaetterNaviNews->nBlaetterAnzahl_arr item=Blatt}
							  {if $oBlaetterNaviNews->nAktuelleSeite == $Blatt}[{$Blatt}]
							  {else}
									<a href="news.php?s2={$Blatt}&tab=aktiv">{$Blatt}</a>
							  {/if}
						 {/foreach}
						 
						 {if $oBlaetterNaviNews->nEnde != 0} ... <a href="news.php?s2={$oBlaetterNaviNews->nEnde}&tab=aktiv">{$oBlaetterNaviNews->nEnde}</a>{/if}
						 
						 {if isset($oBlaetterNaviNews->nAktuelleSeite) && $oBlaetterNaviNews->nAktuelleSeite == $oBlaetterNaviNews->nSeiten}
							  {#ratingNext#} >>
						 {else}
							  <a href="news.php?s2={$oBlaetterNaviNews->nNaechste}&tab=aktiv">{#ratingNext#} >></a>
						 {/if}
						 
						 </p>
			  </div>
			  {/if}
				<table class="list">
					<thead>
					 <tr>
						  <th class="check"></th>
						  <th class="tleft">{#newsHeadline#}</th>
						  <th class="tleft">{#newsCustomerGrp#}</th>
						  <th class="tleft">{#newsValidation#}</th>
						  <th>{#newsActive#}</th>
						  <th>{#newsComments#}</th>
						  <th>{#newsDate#}</th>
						  <th></th>
					 </tr>
					 </thead>
					<tbody>
				{foreach name=news from=$oNews_arr item=oNews}
					 <tr class="tab_bg{$smarty.foreach.news.iteration%2}">
						  <td class="check"><input type="checkbox" name="kNews[]" value="{$oNews->kNews}" /></td>
						  <td class="TD2">{$oNews->cBetreff}</td>
						  <td class="TD3">
						  {foreach name=kundengruppen from=$oNews->cKundengruppe_arr item=cKundengruppe}    
								{$cKundengruppe}{if !$smarty.foreach.kundengruppen.last},{/if}
						  {/foreach}
						  </td>
						  <td class="TD4">{$oNews->dGueltigVon_de}</td>
						  <td class="tcenter">{$oNews->nAktiv}</td>
						  <td class="tcenter">{$oNews->nNewsKommentarAnzahl}</td>
						  <td class="tcenter">{$oNews->Datum}</td>
						  <td class="tcenter">
								<a href="news.php?news=1{if $oBlaetterNaviNews->nAktuelleSeite}&s2={$oBlaetterNaviNews->nAktuelleSeite}{/if}&news_editieren=1&kNews={$oNews->kNews}&{$session_name}={$session_id}&tab=aktiv" class="button edit">{#newsEdit#}</a>
								<a href="news.php?news=1{if $oBlaetterNaviNews->nAktuelleSeite}&s2={$oBlaetterNaviNews->nAktuelleSeite}{/if}&nd=1&kNews={$oNews->kNews}&tab=aktiv&{$SID}" class="button">{#newsPreview#}</a>
						  </td>
					 </tr>
				{/foreach}
				</tbody>
				</table>                   
			  <div class="save_wrapper"><input name="loeschen" type="submit" value="{#delete#}" class="button orange" /></div>
			  </form>
			  
		 {else}
			  <div class="box_info container">{#noDataAvailable#}</div>
		 {/if}
			  
		 </div>
		 
		 <div class="tabbertab{if isset($cTab) && $cTab == 'kategorien'} tabbertabdefault{/if}">
		 
			  <h2>{#newsCatOverview#}</h2>
			  
			  <div class="container top">
					<form name="erstellen" method="POST" action="news.php">
						<input type="hidden" name="{$session_name}" value="{$session_id}" />
						<input type="hidden" name="news" value="1" />
						<input type="hidden" name="erstellen" value="1" />
						<input type="hidden" name="tab" value="kategorien" />
						<input type="hidden" name="s3" value="{$oBlaetterNaviKats->nAktuelleSeite}" />
						<input name="news_kategorie_erstellen" type="submit" value="{#newsCatAdd#}" class="button add" />
				  </form>
			  </div>
			  
		 {if $oNewsKategorie_arr|@count > 0 && $oNewsKategorie_arr}
			  <form name="news" method="post" action="news.php">
			  <input type="hidden" name="{$session_name}" value="{$session_id}" />
			  <input type="hidden" name="news" value="1" />
			  <input type="hidden" name="news_kategorie_loeschen" value="1" />
			  <input type="hidden" name="tab" value="kategorien" />
			  <input type="hidden" name="s3" value="{$oBlaetterNaviKats->nAktuelleSeite}" />
			  {if $oBlaetterNaviKats->nAktiv == 1}
			  <div class="container">
						 <p>
						 {$oBlaetterNaviKats->nVon} - {$oBlaetterNaviKats->nBis} {#ratingFrom#} {$oBlaetterNaviKats->nAnzahl}
						 {if $oBlaetterNaviKats->nAktuelleSeite == 1}
							  << {#ratingPrevious#}
						 {else}
							  <a href="news.php?s3={$oBlaetterNaviKats->nVoherige}&tab=kategorien"><< {#ratingPrevious#}</a>
						 {/if}
						 
						 {if $oBlaetterNaviKats->nAnfang != 0}<a href="news.php?s3={$oBlaetterNaviKats->nAnfang}&tab=kategorien">{$oBlaetterNaviKats->nAnfang}</a> ... {/if}
						 {foreach name=blaetternavi from=$oBlaetterNaviKats->nBlaetterAnzahl_arr item=Blatt}
							  {if $oBlaetterNaviKats->nAktuelleSeite == $Blatt}[{$Blatt}]
							  {else}
									<a href="news.php?s3={$Blatt}&tab=kategorien">{$Blatt}</a>
							  {/if}
						 {/foreach}
						 
						 {if $oBlaetterNaviKats->nEnde != 0} ... <a href="news.php?s3={$oBlaetterNaviKats->nEnde}&tab=kategorien">{$oBlaetterNaviKats->nEnde}</a>{/if}
						 
						 {if $oBlaetterNaviKats->nAktuelleSeite == $oBlaetterNaviKats->nSeiten}
							  {#ratingNext#} >>
						 {else}
							  <a href="news.php?s3={$oBlaetterNaviKats->nNaechste}&tab=kategorien">{#ratingNext#} >></a>
						 {/if}
						 
						 </p>
			  </div>
			  {/if}
					<table class="list">
						<thead>
						 <tr>
							  <th class="check"></th>
							  <th class="tleft">{#newsCatName#}</th>
							  <th class="">{#newsCatSortShort#}</th>
							  <th class="th-4">{#newsActive#}</th>
							  <th class="th-5">{#newsCatLastUpdate#}</th>
							  <th class="th-5">&nbsp;</th>
						 </tr>
						 </thead>
						<tbody>
					{foreach name=newskategorie from=$oNewsKategorie_arr item=oNewsKategorie}
						 <tr class="tab_bg{$smarty.foreach.newskategorie.iteration%2}">
							  <td class="check"><input type="checkbox" name="kNewsKategorie[]" value="{$oNewsKategorie->kNewsKategorie}" /></td>
							  <td class="TD2">{$oNewsKategorie->cName}</td>
							  <td class="tcenter">{$oNewsKategorie->nSort}</td>
							  <td class="tcenter">{$oNewsKategorie->nAktiv}</td>
							  <td class="tcenter">{$oNewsKategorie->dLetzteAktualisierung_de}</td>
							  <td class="tcenter">
								<a href="news.php?news=1{if isset($oNewsKategorie->nAktuelleSeite) && $oNewsKategorie->nAktuelleSeite}&s3={$oNewsKategorie->nAktuelleSeite}{/if}&newskategorie_editieren=1&kNewsKategorie={$oNewsKategorie->kNewsKategorie}&{$session_name}={$session_id}&tab=kategorien" class="button edit">{#newsEdit#}</a>
							  </td>
						 </tr>
					{/foreach}
					</tbody>
					</table>                  
			  <div class="save_wrapper"><input name="loeschen" type="submit" value="{#delete#}" class="button orange" /></div>
			  </form>
		 
		 {else}
			  <div class="box_info container">{#noDataAvailable#}</div>
		 {/if}
			  
		 </div>
		 
		 <div class="tabbertab{if isset($cTab) && $cTab == 'einstellungen'} tabbertabdefault{/if}">
		 
			  <br />
			  <h2>{#newsSettings#}</h2>
			  
			  <form name="einstellen" method="post" action="news.php">
			  <input type="hidden" name="{$session_name}" value="{$session_id}" />
			  <input type="hidden" name="einstellungen" value="1" />
			  <input type="hidden" name="tab" value="einstellungen" />
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
							  <select name="{$oConfig->cWertName}[]" id="{$oConfig->cWertName}" multiple="multiple" class="combo"> 
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
					
					{foreach name=monatspraefix from=$oNewsMonatsPraefix_arr item=oNewsMonatsPraefix}
						 <p><label for="praefix_{$oNewsMonatsPraefix->cISOSprache}">{#newsPraefix#} ({$oNewsMonatsPraefix->cNameDeutsch}) </label>
						 <input type="text" name="praefix_{$oNewsMonatsPraefix->cISOSprache}"  value="{$oNewsMonatsPraefix->cPraefix}" tabindex="1" /></p>
					{/foreach}
			  </div>
			  
			  <p class="submit"><input type="submit" value="{#newsSave#}" class="button orange" /></p>
			  </form>
			  
		 </div>
		 
	</div>
</div>