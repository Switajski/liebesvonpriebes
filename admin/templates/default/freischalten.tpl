{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}
 
{config_load file="$lang.conf" section="freischalten"}
{include file='tpl_inc/header.tpl'}

<script type="text/javascript" src="templates/default/js/checkAllMSG.js"></script>

{include file="tpl_inc/seite_header.tpl" cTitel=#freischalten# cBeschreibung=#freischaltenDesc# cDokuURL=#freischaltenURL#}
<div id="content">
	{if isset($hinweis) && $hinweis|count_characters > 0}			
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}			
		<p class="box_error">{$fehler}</p>
	{/if}
	
	<div class="block container clearall">
		<div class="left">
			<form name="sprache" method="post" action="freischalten.php">
				<label for="{#changeLanguage#}">{#changeLanguage#}</label>
				<input type="hidden" name="sprachwechsel" value="1" />
				<select id="{#changeLanguage#}" name="kSprache" onchange="javascript:document.sprache.submit();">
				{foreach name=sprachen from=$Sprachen item=sprache}
				<option value="{$sprache->kSprache}" {if $sprache->kSprache==$smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
				{/foreach}
				</select>
			</form>
		</div>
		<div class="right">
			<form name="suche" method="POST" action="freischalten.php">
				<input type="hidden" name="{$session_name}" value="{$session_id}" />
				<input type="hidden" name="Suche" value="1" />
				<label for="search_key">{#freischaltenSearchItem#}</label>
				<input name="cSuche" type="text" value="{if isset($cSuche)}{$cSuche}{/if}" id="search_key" />
				<label for="search_type">{#freischaltenSearchType#}</label>
				<select name="cSuchTyp" id="search_type">
					<option value="Bewertung"{if isset($cSuchTyp) && $cSuchTyp == "Bewertung"} selected{/if}>{#freischaltenReviews#}</option>
					<option value="Livesuche"{if isset($cSuchTyp) && $cSuchTyp == "Livesuche"} selected{/if}>{#freischaltenLivesearch#}</option>
					<option value="Tag"{if isset($cSuchTyp) && $cSuchTyp == "Tag"} selected{/if}>{#freischaltenTags#}</option>
					<option value="Newskommentar"{if isset($cSuchTyp) && $cSuchTyp == "Newskommentar"} selected{/if}>{#freischaltenNewsComments#}</option>
					<option value="Newsletterempfaenger"{if isset($cSuchTyp) && $cSuchTyp == "Newsletterempfaenger"} selected{/if}>{#freischaltenNewsletterReceiver#}</option>
				</select>
				<button name="submitSuche" type="submit" class="button blue">{#freischaltenSearchBTN#}</button>
			</form>
		</div>
	</div>

	{* Bewertungen *}
	{if $oBewertung_arr|@count > 0 && $oBewertung_arr}
		<div class="container">
			<div class="category">
				<a href="bewertung.php">{#freischaltenReviews#}</a>		
				{if $oBlaetterNaviBewertungen->nAktiv == 1}
					<p>
					{$oBlaetterNaviBewertungen->nVon} - {$oBlaetterNaviBewertungen->nBis} {#from#} {$oBlaetterNaviBewertungen->nAnzahl}
					{if $oBlaetterNaviBewertungen->nAktuelleSeite == 1}
						<< {#back#}
					{else}
						<a href="freischalten.php?s1={$oBlaetterNaviBewertungen->nVoherige}"><< {#back#}</a>
					{/if}
					{if $oBlaetterNaviBewertungen->nAnfang != 0}<a href="freischalten.php?s1={$oBlaetterNaviBewertungen->nAnfang}">{$oBlaetterNaviBewertungen->nAnfang}</a> ... {/if}
					{foreach name=blaetternavi from=$oBlaetterNaviBewertungen->nBlaetterAnzahl_arr item=Blatt}
						{if $oBlaetterNaviBewertungen->nAktuelleSeite == $Blatt}[{$Blatt}]
						{else}
							<a href="freischalten.php?s1={$Blatt}">{$Blatt}</a>
						{/if}
					{/foreach}
					{if $oBlaetterNaviBewertungen->nEnde != 0} ... <a href="freischalten.php?s1={$oBlaetterNaviBewertungen->nEnde}">{$oBlaetterNaviBewertungen->nEnde}</a>{/if}
					{if $oBlaetterNaviBewertungen->nAktuelleSeite == $oBlaetterNaviBewertungen->nSeiten}
						{#next#} >>
					{else}
						<a href="freischalten.php?s1={$oBlaetterNaviBewertungen->nNaechste}">{#next#} >></a>
					{/if}
					</p>
				{/if}
			</div>
			
			<form method="POST" action="freischalten.php">
				<input type="hidden" name="{$session_name}" value="{$session_id}" />
				<input type="hidden" name="freischalten" value="1" />
				<input type="hidden" name="bewertungen" value="1" />
				<table class="list">
					<thead>
						<tr>
							<th class="check"></th>
							<th class="tleft">{#freischaltenReviewsProduct#}</th>
							<th class="tleft">{#freischaltenReviewsCustomer#}</th>
							<th>{#freischaltenReviewsStars#}</th>
							<th>{#freischaltenReviewsDate#}</th>
							<th>Aktionen</th>
						</tr>
					</thead>
					<tbody>
						{foreach name=bewertungen from=$oBewertung_arr item=oBewertung}
						<tr>
							<td class="check">
								<input name="kBewertung[]" type="checkbox" value="{$oBewertung->kBewertung}" />
								<input type="hidden" name="kArtikel[]" value="{$oBewertung->kArtikel}" />
							</td>
							<td><a href="../../index.php?a={$oBewertung->kArtikel}" target="_blank">{$oBewertung->ArtikelName}</td>
							<td>{$oBewertung->cName}.</td>
							<td class="tcenter">{$oBewertung->nSterne}</td>
							<td class="tcenter">{$oBewertung->Datum}</td>
							<td class="tcenter"><a href="bewertung.php?a=editieren&kBewertung={$oBewertung->kBewertung}&nFZ=1&{$session_name}={$session_id}">{#freischaltenEdit#}</a></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td colspan="6">
								<strong>{$oBewertung->cTitel}</strong>
								<p>{$oBewertung->cText}</p>
							</td>
						</tr>
						{/foreach}
					</tbody>
					<tfoot>
						<tr>
							<td class="check"><input name="ALLMSGS" id="ALLMSGS1" type="checkbox" onclick="AllMessages(this.form);" /></td>
							<td colspan="5"><label for="ALLMSGS1">{#freischaltenSelectAll#}</label></td>
						</tr>
					</tfoot>
				</table>
				<div class="save_wrapper">
					<button name="freischaltensubmit" type="submit" class="button orange">{#freischaltenActivate#}</button>
					<button name="freischaltenleoschen" type="submit" class="button orange">{#freischaltenDelete#}</button>
				</div>
			</form>
		</div>
	{/if}
	
	
	{* Suchanfragen *}
	{if $oSuchanfrage_arr|@count > 0 && $oSuchanfrage_arr}
		<div class="container">
			<div class="category">
				<a href="livesuche.php">{#freischaltenLivesearch#}</a>
				{if $oBlaetterNaviSuchanfrage->nAktiv == 1}
				<div class="content">
					<p>
					{$oBlaetterNaviSuchanfrage->nVon} - {$oBlaetterNaviSuchanfrage->nBis} {#from#} {$oBlaetterNaviSuchanfrage->nAnzahl}
					{if $oBlaetterNaviSuchanfrage->nAktuelleSeite == 1}
						<< {#back#}
					{else}
						<a href="freischalten.php?s2={$oBlaetterNaviSuchanfrage->nVoherige}"><< {#back#}</a>
					{/if}
					
					{if $oBlaetterNaviSuchanfrage->nAnfang != 0}<a href="freischalten.php?s2={$oBlaetterNaviSuchanfrage->nAnfang}">{$oBlaetterNaviSuchanfrage->nAnfang}</a> ... {/if}
					{foreach name=blaetternavi from=$oBlaetterNaviSuchanfrage->nBlaetterAnzahl_arr item=Blatt}
						{if $oBlaetterNaviSuchanfrage->nAktuelleSeite == $Blatt}[{$Blatt}]
						{else}
							<a href="freischalten.php?s2={$Blatt}">{$Blatt}</a>
						{/if}
					{/foreach}
					
					{if $oBlaetterNaviSuchanfrage->nEnde != 0} ... <a href="freischalten.php?s2={$oBlaetterNaviSuchanfrage->nEnde}">{$oBlaetterNaviSuchanfrage->nEnde}</a>{/if}
					
					{if $oBlaetterNaviSuchanfrage->nAktuelleSeite == $oBlaetterNaviSuchanfrage->nSeiten}
						{#next#} >>
					{else}
						<a href="freischalten.php?s2={$oBlaetterNaviSuchanfrage->nNaechste}">{#next#} >></a>
					{/if}
					
					</p>
				</div>
				{/if}
			</div>
			
			<form method="POST" action="freischalten.php">
				<input type="hidden" name="{$session_name}" value="{$session_id}" />
				<input type="hidden" name="freischalten" value="1" />
				<input type="hidden" name="suchanfragen" value="1" />
					
				{if isset($cSuche) && isset($cSuchTyp) && $cSuche && $cSuchTyp}
					{assign var=cSuchStr value="Suche=1&cSuche=`$cSuche`&cSuchTyp=`$cSuchTyp`&"}
				{else}
					{assign var=cSuchStr value=""}
				{/if}
					
				<table class="list">
					<thead>
						<tr>
							<th class="check">&nbsp;</th>            			
							<th class="tleft">(<a href="freischalten.php?{$cSuchStr}nSort=1{if $nSort != 11}1{/if}{$session_name}={$session_id}{if $oBlaetterNaviSuchanfragen->nAktuelleSeite > 0}&s1={$oBlaetterNaviSuchanfragen->nAktuelleSeite}{/if}" style="text-decoration: underline;">{if !isset($nSort) || $nSort != 11}Z...A{else}A...Z{/if}</a>) {#freischaltenLivesearchSearch#}</th>
							<th>(<a href="freischalten.php?{$cSuchStr}nSort=2{if $nSort != 22}2{/if}{$session_name}={$session_id}{if $oBlaetterNaviSuchanfragen->nAktuelleSeite > 0}&s1={$oBlaetterNaviSuchanfragen->nAktuelleSeite}{/if}" style="text-decoration: underline;">{if !isset($nSort) || $nSort != 22}1...9{else}9...1{/if}</a>) {#freischaltenLivesearchCount#}</th>
							<th>(<a href="freischalten.php?{$cSuchStr}nSort=3{if $nSort != 33}3{/if}{$session_name}={$session_id}{if $oBlaetterNaviSuchanfragen->nAktuelleSeite > 0}&s1={$oBlaetterNaviSuchanfragen->nAktuelleSeite}{/if}" style="text-decoration: underline;">{if !isset($nSort) || $nSort != 33}0...1{else}1...0{/if}</a>) {#freischaltenLivesearchHits#}</th>
							<th>{#freischaltenLiveseachDate#}</th>
						</tr>
					</thead>
					<tbody>
						{foreach name=suchanfragen from=$oSuchanfrage_arr item=oSuchanfrage}
						<tr class="tab_bg{$smarty.foreach.suchanfragen.iteration%2}">
							<td class="check"><input name="kSuchanfrage[]" type="checkbox" value="{$oSuchanfrage->kSuchanfrage}" /></td>
							<td class="tleft">{$oSuchanfrage->cSuche}</td>
							<td class="tcenter">{$oSuchanfrage->nAnzahlGesuche}</td>
							<td class="tcenter">{$oSuchanfrage->nAnzahlTreffer}</td>
							<td class="tcenter">{$oSuchanfrage->dZuletztGesucht_de}</td>
						</tr>
						{/foreach}
					</tbody>
					<tfoot>
					<tr>
						<td class="check"><input name="ALLMSGS" id="ALLMSGS2" type="checkbox" onclick="AllMessages(this.form);" /></td>
						<td colspan="5"><label for="ALLMSGS2">{#freischaltenSelectAll#}</label></td>
					</tr>
					</tfoot>
				</table>
				<div class="save_wrapper">
					<input name="freischaltensubmit" type="submit" value="{#freischaltenActivate#}" class="button orange" />
					<input name="freischaltenleoschen" type="submit" value="{#freischaltenDelete#}" class="button orange" />
					<input name="nMapping" type="radio" value="1" /> {#freischaltenMappingOn#}
					<input name="cMapping" type="text" value="" />
					<input name="submitMapping" type="submit" value="{#freischaltenMappingOnBTN#}" class="button orange" />
				</div>
				<p class="container box_info">{#freischaltenMappingDesc#}</p>
			</form>
		</div>
	{/if}
	
	
	{* Tags *}
	<div class="container">
		{if $oTag_arr|@count > 0 && $oTag_arr}			
			<div class="category">
				<a href="tagging.php">{#freischaltenTags#}</a>
				{if $oBlaetterNaviTag->nAktiv == 1}
					<p>
						{$oBlaetterNaviTag->nVon} - {$oBlaetterNaviTag->nBis} {#from#} {$oBlaetterNaviTag->nAnzahl}
						{if $oBlaetterNaviTag->nAktuelleSeite == 1}
							<< {#back#}
						{else}
							<a href="freischalten.php?s3={$oBlaetterNaviTag->nVoherige}"><< {#back#}</a>
						{/if}
						
						{if $oBlaetterNaviTag->nAnfang != 0}<a href="freischalten.php?s3={$oBlaetterNaviTag->nAnfang}">{$oBlaetterNaviTag->nAnfang}</a> ... {/if}
						{foreach name=blaetternavi from=$oBlaetterNaviTag->nBlaetterAnzahl_arr item=Blatt}
							{if $oBlaetterNaviTag->nAktuelleSeite == $Blatt}[{$Blatt}]
							{else}
								<a href="freischalten.php?s3={$Blatt}">{$Blatt}</a>
							{/if}
						{/foreach}
						
						{if $oBlaetterNaviTag->nEnde != 0} ... <a href="freischalten.php?s3={$oBlaetterNaviTag->nEnde}">{$oBlaetterNaviTag->nEnde}</a>{/if}
						
						{if $oBlaetterNaviTag->nAktuelleSeite == $oBlaetterNaviTag->nSeiten}
							{#next#} >>
						{else}
							<a href="freischalten.php?s3={$oBlaetterNaviTag->nNaechste}">{#next#} >></a>
						{/if}
					</p>
				{/if}
			</div>

			<form method="POST" action="freischalten.php">
				<input type="hidden" name="{$session_name}" value="{$session_id}" />
				<input type="hidden" name="freischalten" value="1" />
				<input type="hidden" name="tags" value="1" />
				<table class="list">
					<thead>
						<tr>
							<th class="check">&nbsp;</th>
							<th class="tleft">{#freischaltenTagsName#}</th>
							<th>{#freischaltenTagsProductName#}</th>
							<th>{#freischaltenTagsCount#}</th>
						</tr>
					</thead>
					<tbody>
						{foreach name=tags from=$oTag_arr item=oTag}
							<tr>
								<td class="check"><input name="kTag[]" type="checkbox" value="{$oTag->kTag}" /></td>
								<td>{$oTag->cName}</td>
								<td class="tcenter"><a href="{if isset($oTag->cArtikelSeo) && $oTag->cArtikelSeo|count_characters > 0}{$URL_SHOP}/{$oTag->cArtikelSeo}{else}{$URL_SHOP}/index.php?a={$oTag->kArtikel}{/if}" target="_blank">{$oTag->cArtikelName}</a></td>
								<td class="tcenter">{$oTag->Anzahl}</td>
							</tr>
						{/foreach}
					</tbody>
					<tfoot>
						<tr>
							<td class="check"><input name="ALLMSGS" id="ALLMSGS3" type="checkbox" onclick="AllMessages(this.form);" /></td>
							<td colspan="5"><label for="ALLMSGS3">{#freischaltenSelectAll#}</label></td>
						</tr>
					</tfoot>
				</table>
				<div class="save_wrapper">
					<input name="freischaltensubmit" type="submit" value="{#freischaltenActivate#}" class="button orange" />
					<input name="freischaltenleoschen" type="submit" value="{#freischaltenDelete#}" class="button orange" />
				</div>
			</form>
		</div>
	{/if}
		
	{* News-Kommentare *}
	{if $oNewsKommentar_arr|@count > 0 && $oNewsKommentar_arr}
		<div class="container">
			<div class="category">
				<a href="newsletter.php">{#freischaltenNewsComments#}</a>
				{if $oBlaetterNaviNewsKommentar->nAktiv == 1}
					<div class="content">
						<p>
						{$oBlaetterNaviNewsKommentar->nVon} - {$oBlaetterNaviNewsKommentar->nBis} {#from#} {$oBlaetterNaviNewsKommentar->nAnzahl}
						{if $oBlaetterNaviNewsKommentar->nAktuelleSeite == 1}
							<< {#back#}
						{else}
							<a href="freischalten.php?s4={$oBlaetterNaviNewsKommentar->nVoherige}"><< {#back#}</a>
						{/if}
						
						{if $oBlaetterNaviNewsKommentar->nAnfang != 0}<a href="freischalten.php?s4={$oBlaetterNaviNewsKommentar->nAnfang}">{$oBlaetterNaviNewsKommentar->nAnfang}</a> ... {/if}
						{foreach name=blaetternavi from=$oBlaetterNaviNewsKommentar->nBlaetterAnzahl_arr item=Blatt}
							{if $oBlaetterNaviNewsKommentar->nAktuelleSeite == $Blatt}[{$Blatt}]
							{else}
								<a href="freischalten.php?s4={$Blatt}">{$Blatt}</a>
							{/if}
						{/foreach}
						
						{if $oBlaetterNaviNewsKommentar->nEnde != 0} ... <a href="freischalten.php?s4={$oBlaetterNaviNewsKommentar->nEnde}">{$oBlaetterNaviNewsKommentar->nEnde}</a>{/if}
						
						{if $oBlaetterNaviNewsKommentar->nAktuelleSeite == $oBlaetterNaviNewsKommentar->nSeiten}
							{#next#} >>
						{else}
							<a href="freischalten.php?s4={$oBlaetterNaviNewsKommentar->nNaechste}">{#next#} >></a>
						{/if}
						
						</p>
					</div>
				{/if}
			</div>
			
			<form method="POST" action="freischalten.php">
				<input type="hidden" name="{$session_name}" value="{$session_id}" />
				<input type="hidden" name="freischalten" value="1" />
				<input type="hidden" name="newskommentare" value="1" />
				<table class="list">
					<thead>
						<tr>
							<th class="check">&nbsp;</th>
							<th class="tleft">{#freischaltenNewsCommentsVisitor#}</th>
							<th class="tleft">{#freischaltenNewsCommentsHeadline#}</th>
							<th>{#freischaltenNewsCommentsDate#}</th>
							<th>Aktionen</th>
						</tr>
					</thead>
					<tbody>
					{foreach name=newskommentare from=$oNewsKommentar_arr item=oNewsKommentar}
						<tr>
							<td class="check"><input type="checkbox" name="kNewsKommentar[]" value="{$oNewsKommentar->kNewsKommentar}" /></td>
							<td>
							{if $oNewsKommentar->cVorname|count_characters > 0}
								{$oNewsKommentar->cVorname} {$oNewsKommentar->cNachname}
							{else}
								{$oNewsKommentar->cName}
							{/if}
							</td>
							<td>{$oNewsKommentar->cBetreff|truncate:50:"..."}</td>
							<td class="tcenter">{$oNewsKommentar->dErstellt_de}</td>
							<td class="tcenter"><a href="news.php?news=1&kNews={$oNewsKommentar->kNews}&kNewsKommentar={$oNewsKommentar->kNewsKommentar}&nkedit=1&nFZ=1&{$session_name}={$session_id}">{#freischaltenEdit#}</a></td>
						</tr>
						<tr>
							<td class="check">&nbsp;</td>
							<td char="TD8" colspan="4"><b>{$oNewsKommentar->cBetreff}</b><br>{$oNewsKommentar->cKommentar}</td>
						</tr>
					{/foreach}
					</tbody>
					<tfoot>
						<tr>
							<td class="check"><input name="ALLMSGS" id="ALLMSGS4" type="checkbox" onclick="AllMessages(this.form);" /></td>
							<td colspan="5"><label for="ALLMSGS4">{#freischaltenSelectAll#}</label></td>
						</tr>
					</tfoot>
				</table>
				<div class="save_wrapper">
					<input name="freischaltensubmit" type="submit" value="{#freischaltenActivate#}" class="button orange" />
					<input name="freischaltenleoschen" type="submit" value="{#freischaltenDelete#}" class="button orange" />
				</div>
			</form>
		</div>
	{/if}
	
	{* Newsletter-Empf�nger *}
	{if $oNewsletterEmpfaenger_arr|@count > 0 && $oNewsletterEmpfaenger_arr}
		<div class="container">
			<div class="category">
				<a href="newsletter.php">{#freischaltenNewsletterReceiver#}</a>
				{if $oBlaetterNaviNewsletterEmpfaenger->nAktiv == 1}
					<div class="content">
						<p>
						{$oBlaetterNaviNewsletterEmpfaenger->nVon} - {$oBlaetterNaviNewsletterEmpfaenger->nBis} {#from#} {$oBlaetterNaviNewsletterEmpfaenger->nAnzahl}
						{if $oBlaetterNaviNewsletterEmpfaenger->nAktuelleSeite == 1}
							<< {#back#}
						{else}
							<a href="freischalten.php?s5={$oBlaetterNaviNewsletterEmpfaenger->nVoherige}"><< {#back#}</a>
						{/if}
						
						{if $oBlaetterNaviNewsletterEmpfaenger->nAnfang != 0}<a href="freischalten.php?s5={$oBlaetterNaviNewsletterEmpfaenger->nAnfang}">{$oBlaetterNaviNewsletterEmpfaenger->nAnfang}</a> ... {/if}
						{foreach name=blaetternavi from=$oBlaetterNaviNewsletterEmpfaenger->nBlaetterAnzahl_arr item=Blatt}
							{if $oBlaetterNaviNewsletterEmpfaenger->nAktuelleSeite == $Blatt}[{$Blatt}]
							{else}
								<a href="freischalten.php?s5={$Blatt}">{$Blatt}</a>
							{/if}
						{/foreach}
						
						{if $oBlaetterNaviNewsletterEmpfaenger->nEnde != 0} ... <a href="freischalten.php?s5={$oBlaetterNaviNewsletterEmpfaenger->nEnde}">{$oBlaetterNaviNewsletterEmpfaenger->nEnde}</a>{/if}
						
						{if $oBlaetterNaviNewsletterEmpfaenger->nAktuelleSeite == $oBlaetterNaviNewsletterEmpfaenger->nSeiten}
							{#next#} >>
						{else}
							<a href="freischalten.php?s5={$oBlaetterNaviNewsletterEmpfaenger->nNaechste}">{#next#} >></a>
						{/if}
						
						</p>
					</div>
				{/if}
			</div>
			
			<form method="POST" action="freischalten.php">
				<input type="hidden" name="{$session_name}" value="{$session_id}" />
				<input type="hidden" name="freischalten" value="1" />
				<input type="hidden" name="newsletterempfaenger" value="1" />
				<table class="list">
					<thead>
						<tr>
							<th class="check">&nbsp;</th>
							<th class="tleft">{#freischaltenNewsletterReceiverEmail#}</th>
							<th class="tleft">{#freischaltenNewsletterReceiverFirstName#}</th>
							<th class="tleft">{#freischaltenNewsletterReceiverLastName#}</th>
							<th>(<a href="freischalten.php?{$cSuchStr}nSort=4{if $nSort != 44}4{/if}{$session_name}={$session_id}{if $oBlaetterNaviNewsletterEmpfaenger->nAktuelleSeite > 0}&s1={$oBlaetterNaviNewsletterEmpfaenger->nAktuelleSeite}{/if}">{if $nSort != 44}Alt...Neu{elseif $nSort == 44}Neu...Alt{/if}</a>) {#freischaltenNewsletterReceiverDate#}</th>
						</tr>
					</thead>
					<tbody>
					{foreach name=newsletterempfaenger from=$oNewsletterEmpfaenger_arr item=oNewsletterEmpfaenger}
						<tr>
							<td class="check"><input type="checkbox" name="kNewsletterEmpfaenger[]" value="{$oNewsletterEmpfaenger->kNewsletterEmpfaenger}" /></td>
							<td>{$oNewsletterEmpfaenger->cEmail}</td>
							<td>{$oNewsletterEmpfaenger->cVorname}</td>
							<td>{$oNewsletterEmpfaenger->cNachname}</td>
							<td class="tcenter">{$oNewsletterEmpfaenger->dEingetragen_de}</td>
						</tr>
					{/foreach}
					</tbody>
					<tfoot>
						<tr>
							<td class="check"><input name="ALLMSGS" id="ALLMSGS5" type="checkbox" onclick="AllMessages(this.form);" /></td>
							<td colspan="5"><label for="ALLMSGS5">{#freischaltenSelectAll#}</label></td>
						</tr>
					</tfoot>
				</table>
				<div class="save_wrapper">
					<input name="freischaltensubmit" type="submit" value="{#freischaltenActivate#}" class="button orange" />
					<input name="freischaltenleoschen" type="submit" value="{#freischaltenDelete#}" class="button orange" />
				</div>
			</form>
		</div>
	{/if}
</div>
{include file='tpl_inc/footer.tpl'}