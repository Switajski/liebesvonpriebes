{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: newsletter_vorlage_std_erstellen.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2010 JTL-Software
-------------------------------------------------------------------------------
*}

<link type="text/css" rel="stylesheet" href="{$URL_SHOP}/{$PFAD_ADMIN}/templates/default/js/js_calender/dhtmlgoodies_calendar/dhtmlgoodies_calendar.css" media="screen"></LINK>
<SCRIPT type="text/javascript" src="{$URL_SHOP}/{$PFAD_ADMIN}/templates/default/js/js_calender/dhtmlgoodies_calendar/dhtmlgoodies_calendar.js"></script>
<script type="text/javascript">
var fields = 0;

function neu()
{ldelim}
	if (fields != 10) 
	{ldelim}
		document.getElementById('ArtNr').innerHTML += "<input name='cArtNr[]' type='text' class='field'>";
		fields += 1;
	{rdelim} 
	else 
	{ldelim}
		document.getElementById('ArtNr').innerHTML += "";
		document.form.add.disabled=true;
	{rdelim}
{rdelim}

function checkNewsletterSend()
{ldelim}
	bCheck = confirm("{#newsletterSendAuthentication#}");
	
	if(bCheck) {ldelim}
		var input1 = document.createElement('input');
		input1.type = 'hidden';
		input1.name = "speichern_und_senden";
		input1.value = "1";
		document.getElementById("formnewslettervorlage").appendChild(input1);
		document.formnewslettervorlage.submit();
	{rdelim}
{rdelim}
</script>

{include file="tpl_inc/seite_header.tpl" cTitel=#newsletterdraft# cBeschreibung=#newsletterdraftdesc#}
<div id="content">   
   {if $hinweis}
      <br>
      <div class="userNotice">
         {$hinweis}
      </div>
   {/if}
   {if $fehler}
      <br>
      <div class="userError">
         {$fehler}
      </div>
   {/if}
   
   {if $cPlausiValue_arr|@count > 0}
      <div class="box_error">
         Bitte füllen Sie alle Pflichtfelder aus.
      </div>
   {/if}
   
   <div class="container">
      <form name="formnewslettervorlagestd" id="formnewslettervorlagestd" method="POST" action="newsletter.php" enctype="multipart/form-data">
      <input type="hidden" name="{$session_name}" value="{$session_id}">
      <input name="newslettervorlagenstd" type="hidden" value="1">
      <input name="vorlage_std_speichern" type="hidden" value="1">
         <input name="tab" type="hidden" value="newslettervorlagenstd">
      
      {if isset($oNewslettervorlageStd->kNewslettervorlageStd) && $oNewslettervorlageStd->kNewslettervorlageStd > 0}
         <input name="kNewslettervorlageStd" type="hidden" value="{$oNewslettervorlageStd->kNewslettervorlageStd}">
      {elseif isset($cPostVar_arr.kNewslettervorlageStd) && $cPostVar_arr.kNewslettervorlageStd > 0}
         <input name="kNewslettervorlageStd" type="hidden" value="{$cPostVar_arr.kNewslettervorlageStd}">
      {/if}
      {if isset($oNewslettervorlageStd->kNewsletterVorlage) && $oNewslettervorlageStd->kNewsletterVorlage > 0}
         <input name="kNewsletterVorlage" type="hidden" value="{$oNewslettervorlageStd->kNewsletterVorlage}">
      {elseif isset($cPostVar_arr.kNewslettervorlage) && $cPostVar_arr.kNewslettervorlage > 0}
         <input name="kNewsletterVorlage" type="hidden" value="{$cPostVar_arr.kNewslettervorlage}">
      {/if}
      
      <table class="newsletter">
         <tr>
            <td><b>{#newsletterdraftname#}</b>:</td>
            <td>
               <input name="cName" type="text" class="{if isset($cPlausiValue_arr.cName)}fieldfillout{else}field{/if}" value="{if $cPostVar_arr.cName}{$cPostVar_arr.cName}{elseif isset($oNewslettervorlageStd->cName)}{$oNewslettervorlageStd->cName}{/if}">
               {if isset($cPlausiValue_arr.cName)}<font class="fillout">{#newsletterdraftFillOut#}</font>{/if}
            </td>
         </tr>
         
         <tr>
            <td><b>{#newsletterdraftsubject#}</b>:</td>
            <td>
               <input name="cBetreff" type="text" class="{if isset($cPlausiValue_arr.cBetreff)}fieldfillout{else}field{/if}" value="{if isset($cPostVar_arr.cBetreff)}{$cPostVar_arr.cBetreff}{elseif isset($oNewslettervorlageStd->cBetreff)}{$oNewslettervorlageStd->cBetreff}{/if}">
               {if isset($cPlausiValue_arr.cBetreff)}<font class="fillout">{#newsletterdraftFillOut#}</font>{/if}
            </td>					
         </tr>
         
         <tr>
            <td><b>{#newslettercustomergrp#}</b>:</td>
            <td>
               <select name="kKundengruppe[]" multiple="multiple" class="{if isset($cPlausiValue_arr.kKundengruppe_arr)}fieldfillout{else}combo{/if}"> 
                  <option value="0"
                  {if isset($kKundengruppe_arr)}
                  {foreach name=kkundengruppen from=$kKundengruppe_arr item=kKundengruppe}
                     {if $kKundengruppe == "0"}selected{/if}
                  {/foreach}
                  {elseif isset($cPostVar_arr.kKundengruppe)}
                     {foreach name=kkundengruppen from=$cPostVar_arr.kKundengruppe item=kKundengruppe}
                        {if $kKundengruppe == "0"}selected{/if}
                     {/foreach}
                  {/if}
                  >Newsletterempf&auml;nger ohne Kundenkonto</option>
               {foreach name=kundengruppen from=$oKundengruppe_arr item=oKundengruppe}
                  <option value="{$oKundengruppe->kKundengruppe}" 
               {if isset($kKundengruppe_arr)}
                  {foreach name=kkundengruppen from=$kKundengruppe_arr item=kKundengruppe}
                     {if $oKundengruppe->kKundengruppe == $kKundengruppe}selected{/if}
                  {/foreach}
               {elseif isset($cPostVar_arr.kKundengruppe)}
                  {foreach name=kkundengruppen from=$cPostVar_arr.kKundengruppe item=kKundengruppe}
                     {if $oKundengruppe->kKundengruppe == $kKundengruppe}selected{/if}
                  {/foreach}
               {/if}
                     >{$oKundengruppe->cName}</option>
               {/foreach}
               </select>
               {if isset($cPlausiValue_arr.kKundengruppe_arr)}<font class="fillout">{#newsletterdraftFillOut#}</font>{/if}
            </td>
         </tr>
         
         <tr>
            <td><b>{#newsletterdraftcharacter#}</b>:</td>
            <td>
               <select name="cArt" class="combo">
                  <option {if $oNewslettervorlageStd->cArt == 'text/html'}selected{/if}>text/html</option>
                  <option {if $oNewslettervorlageStd->cArt == 'text'}selected{/if}>text</option>
               </select>
            </td>
         </tr>
         
         <tr>
            <td style="vertical-align: middle;"><b>{#newsletterdraftdate#}</b>:</td>
            <td>
               <select name="dTag" class="combo" style="width: 50px;">
                  {section name=dTag start=1 loop=32 step=1}
                  {if $smarty.section.dTag.index < 10}
                     <option value="0{$smarty.section.dTag.index}"{if $oNewslettervorlageStd->oZeit->cZeit_arr|@count > 0}{if $oNewslettervorlageStd->oZeit->cZeit_arr[0] == $smarty.section.dTag.index} selected{/if}{else}{if $smarty.now|date_format:"%d" == $smarty.section.dTag.index} selected{/if}{/if}>0{$smarty.section.dTag.index}</option>
                  {else}
                     <option value="{$smarty.section.dTag.index}"{if $oNewslettervorlageStd->oZeit->cZeit_arr|@count > 0}{if $oNewslettervorlageStd->oZeit->cZeit_arr[0] == $smarty.section.dTag.index} selected{/if}{else}{if $smarty.now|date_format:"%d" == $smarty.section.dTag.index} selected{/if}{/if}>{$smarty.section.dTag.index}</option>
                  {/if}
                  {/section}
               </select>
               
               .
               
               <select name="dMonat" class="combo" style="width: 50px;">
                  {section name=dMonat start=1 loop=13 step=1}
                  {if $smarty.section.dMonat.index < 10}
                     <option value="0{$smarty.section.dMonat.index}"{if $oNewslettervorlageStd->oZeit->cZeit_arr|@count > 0}{if $oNewslettervorlageStd->oZeit->cZeit_arr[1] == $smarty.section.dMonat.index} selected{/if}{else}{if $smarty.now|date_format:"%m" == $smarty.section.dMonat.index} selected{/if}{/if}>0{$smarty.section.dMonat.index}</option>
                  {else}
                     <option value="{$smarty.section.dMonat.index}"{if $oNewslettervorlageStd->oZeit->cZeit_arr|@count > 0}{if $oNewslettervorlageStd->oZeit->cZeit_arr[1] == $smarty.section.dMonat.index} selected{/if}{else}{if $smarty.now|date_format:"%m" == $smarty.section.dMonat.index} selected{/if}{/if}>{$smarty.section.dMonat.index}</option>
                  {/if}
                  {/section}
               </select>
               
               .
               
               <select name="dJahr" class="combo" style="width: 60px;">
                  {section name=dJahr start=2008 loop=2016 step=1}
                     <option value="{$smarty.section.dJahr.index}"{if $oNewslettervorlageStd->oZeit->cZeit_arr|@count > 0}{if $oNewslettervorlageStd->oZeit->cZeit_arr[2] == $smarty.section.dJahr.index} selected{/if}{else}{if $smarty.now|date_format:"%Y" == $smarty.section.dJahr.index} selected{/if}{/if}>{$smarty.section.dJahr.index}</option>
                  {/section}
               </select>
               
               -
               
               <select name="dStunde" class="combo" style="width: 60px;">
                  {section name=dStunde start=0 loop=24 step=1}
                     {if $smarty.section.dStunde.index < 10}
                     <option value="0{$smarty.section.dStunde.index}"{if $oNewslettervorlageStd->oZeit->cZeit_arr|@count > 0}{if $oNewslettervorlageStd->oZeit->cZeit_arr[3] == $smarty.section.dStunde.index} selected{/if}{else}{if $smarty.now|date_format:"%H" == $smarty.section.dStunde.index} selected{/if}{/if}>0{$smarty.section.dStunde.index}</option>
                     {else}
                        <option value="{$smarty.section.dStunde.index}"{if $oNewslettervorlageStd->oZeit->cZeit_arr|@count > 0}{if $oNewslettervorlageStd->oZeit->cZeit_arr[3] == $smarty.section.dStunde.index} selected{/if}{else}{if $smarty.now|date_format:"%H" == $smarty.section.dStunde.index} selected{/if}{/if}>{$smarty.section.dStunde.index}</option>
                     {/if}
                  {/section}
               </select>
               
               :
               
               <select name="dMinute" class="combo" style="width: 60px;">
                  {section name=dMinute start=0 loop=60 step=1}
                     {if $smarty.section.dMinute.index < 10}
                     <option value="0{$smarty.section.dMinute.index}"{if $oNewslettervorlageStd->oZeit->cZeit_arr|@count > 0}{if $oNewslettervorlageStd->oZeit->cZeit_arr[4] == $smarty.section.dMinute.index} selected{/if}{else}{if $smarty.now|date_format:"%M" == $smarty.section.dMinute.index} selected{/if}{/if}>0{$smarty.section.dMinute.index}</option>
                     {else}
                        <option value="{$smarty.section.dMinute.index}"{if $oNewslettervorlageStd->oZeit->cZeit_arr|@count > 0}{if $oNewslettervorlageStd->oZeit->cZeit_arr[4] == $smarty.section.dMinute.index} selected{/if}{else}{if $smarty.now|date_format:"%M" == $smarty.section.dMinute.index} selected{/if}{/if}>{$smarty.section.dMinute.index}</option>
                     {/if}
                  {/section}
               </select>
            
               {#newsletterdraftformat#}
               <!-- <input id="dStartZeit" name="dStartZeit" type="text"  value="{$oNewsletterVorlage->Datum}"><input type="button" value="{#newsletterdraftcal#}" onclick="displayCalendar(document.getElementById('dStartZeit'),'dd.mm.yyyy hh:ii',this,true)">-->
            </td>
         </tr>
         
         <tr>
            <td>{#newslettercampaign#}:</strong><br />
            <td>
               <select name="kKampagne">
                  <option value="0"></option>
               {foreach name="Kampagnen" from=$oKampagne_arr item=oKampagne}
                  <option value="{$oKampagne->kKampagne}"{if $oKampagne->kKampagne == $oNewslettervorlageStd->kKampagne || $cPostVar_arr.kKampagne == $oKampagne->kKampagne} selected{/if}>{$oKampagne->cName}</option>
               {/foreach}
               </select>
            </td>
         </tr>
         
         <tr>
            <td><b>{#newsletterartnr#}</b>:</td>
            <td>
               <input name="cArtikel" id="assign_article_list" type="text" value="{if isset($cPostVar_arr.cArtikel) && $cPostVar_arr.cArtikel|count_characters > 0}{$cPostVar_arr.cArtikel}{else}{$oNewslettervorlageStd->cArtikel}{/if}" />
               <a href="#" class="button edit" id="show_article_list">Artikel verwalten</a>
               <div id="ajax_list_picker" class="article">{include file="tpl_inc/popup_artikelsuche.tpl"}</div>
            </td>
         </tr> 
         <tr>
            <td class="left"><b>{#newslettermanufacturer#}</b>:</td>
            <td>
               <input id="assign_manufacturer_list" name="cHersteller" type="text" value="{if isset($cPostVar_arr.cHersteller) && $cPostVar_arr.cHersteller|count_characters > 0}{$cPostVar_arr.cHersteller}{else}{$oNewslettervorlageStd->cHersteller}{/if}" >
            	<a href="#" class="button edit" id="show_manufacturer_list">Hersteller verwalten</a>
                  <div id="ajax_list_picker" class="manufacturer">{include file="tpl_inc/popup_herstellersuche.tpl"}</div>
            </td>
         </tr>
         
         <tr>
            <td class="left"><b>{#newslettercategory#}</b>:</td>
            <td>
               <input id="assign_categories_list"  name="cKategorie" type="text" value="{if isset($cPostVar_arr.cKategorie) && $cPostVar_arr.cKategorie|count_characters > 0}{$cPostVar_arr.cKategorie}{else}{$oNewslettervorlageStd->cKategorie}{/if}" >
            	<a href="#" class="button edit" id="show_categories_list">Kategorien verwalten</a>
                <div id="ajax_list_picker" class="categories">{include file="tpl_inc/popup_kategoriesuche.tpl"}</div>
            </td>
         </tr>
         
         <!--
         <tr>
            <td>&nbsp;</td>
            <td><input name="button" type="button" value="{#newsletternewartnr#}" onclick="javascript:neu();"></td>
         </tr>
         -->
         
   {if isset($oNewslettervorlageStd->oNewslettervorlageStdVar_arr) && $oNewslettervorlageStd->oNewslettervorlageStdVar_arr|@count > 0}
         <tr>
            <td colspan="2" align="center">
               <strong>{#newsletterdraftStdVarSection#}</strong>
               <br />
               <br />
            </td>
         </tr>
   
      {foreach name=newslettervorlagestdvar from=$oNewslettervorlageStd->oNewslettervorlageStdVar_arr item=oNewslettervorlageStdVar}
         
         {if $oNewslettervorlageStdVar->cTyp == "BILD"}
            <tr>
               <td>{$oNewslettervorlageStdVar->cName}</td>					
               <td>
                  <input name="kNewslettervorlageStdVar_{$oNewslettervorlageStdVar->kNewslettervorlageStdVar}" type="file"  accept="image/*"><br />
                  <strong>{#newsletterPicLink#}:</strong> <input name="cLinkURL" type="text" value="{if isset($cPostVar_arr.cLinkURL) && $cPostVar_arr.cLinkURL|count_characters > 0}{$cPostVar_arr.cLinkURL}{else}{$oNewslettervorlageStdVar->cLinkURL}{/if}"  /><br />
                  <strong>{#newsletterAltTag#}:</strong> <input name="cAltTag" type="text" value="{if isset($cPostVar_arr.cAltTag) && $cPostVar_arr.cAltTag|count_characters > 0}{$cPostVar_arr.cAltTag}{else}{$oNewslettervorlageStdVar->cAltTag}{/if}"  /><br />
                  {if isset($oNewslettervorlageStdVar->cInhalt) && $oNewslettervorlageStdVar->cInhalt|count_characters > 0}<img src="{$oNewslettervorlageStdVar->cInhalt}?={$nRand}" />{/if}
               </td>
            </tr>
         {elseif $oNewslettervorlageStdVar->cTyp == "TEXT"}
            <tr>
               <td>{$oNewslettervorlageStdVar->cName}</td>					
               <td>
                  <textarea id="kNewslettervorlageStdVar_{$oNewslettervorlageStdVar->kNewslettervorlageStdVar}" class="codemirror html" name="kNewslettervorlageStdVar_{$oNewslettervorlageStdVar->kNewslettervorlageStdVar}" style="width: 500px; height: 400px;">{if isset($oNewslettervorlageStdVar->cInhalt) && $oNewslettervorlageStdVar->cInhalt|count_characters > 0}{$oNewslettervorlageStdVar->cInhalt}{/if}</textarea>
               </td>
            </tr>
         {/if}
         
      {/foreach}
   {/if}
      </table>
      <p style="text-align: center;"><input name="speichern" type="submit" value="{#newsletterdraftsave#}"></p>
      {if (isset($oNewslettervorlageStd->kNewsletterVorlage) && $oNewslettervorlageStd->kNewsletterVorlage > 0) || (isset($cPostVar_arr.kNewslettervorlage) && $cPostVar_arr.kNewslettervorlage > 0)}
         <p><a href="newsletter.php?tab=newslettervorlagen&{$session_name}={$session_id}">{#newsletterback#}</a></p>
      {else}
         <p><a href="newsletter.php?tab=newslettervorlagenstd&{$session_name}={$session_id}">{#newsletterback#}</a></p>
      {/if}
      </form>		
   </div>		
</div>
