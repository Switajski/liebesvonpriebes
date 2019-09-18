{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: kundenfeld.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
{config_load file="$lang.conf" section="globalemetaangaben"}
{include file='tpl_inc/header.tpl'}
{include file="tpl_inc/seite_header.tpl" cTitel=#globalemetaangaben# cBeschreibung=#globalemetaangabenDesc# cDokuURL=#globalemetaangabenUrl#}
<div id="content">	
	{if isset($hinweis) && $hinweis|count_characters > 0}			
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}			
		<p class="box_error">{$fehler}</p>
	{/if}

	<div class="container block tcenter">
		<form name="sprache" method="post" action="globalemetaangaben.php">
			<label for="{#changeLanguage#}">{#changeLanguage#}</label>
			<input type="hidden" name="sprachwechsel" value="1" />
			<select id="{#changeLanguage#}" name="kSprache" class="selectBox" onchange="javascript:document.sprache.submit();">
				{foreach name=sprachen from=$Sprachen item=sprache}
					<option value="{$sprache->kSprache}" {if $sprache->kSprache==$smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
				{/foreach}
			</select>
		</form>
	</div>
	
	<div class="container">
		<form method="post" action="globalemetaangaben.php">
			<input type="hidden" name="{$session_name}" value="{$session_id}" />
			<input type="hidden" name="einstellungen" value="1" />
			<div class="settings">
				<p><label for="Title">{#globalemetaangabenTitle#}</label>
				<input type="text" name="Title" value="{if isset($oMetaangaben_arr.Title)}{$oMetaangaben_arr.Title}{/if}" tabindex="1" /></p>
				
				<p><label for="Meta Description">{#globalemetaangabenMetaDesc#}</label>
				<input type="text" name="Meta_Description" value="{if isset($oMetaangaben_arr.Meta_Description)}{$oMetaangaben_arr.Meta_Description}{/if}" tabindex="1" /></p>
				
				<p><label for="Meta Keywords">{#globalemetaangabenKeywords#}</label>
				<input type="text" name="Meta_Keywords" value="{if isset($oMetaangaben_arr.Meta_Keywords)}{$oMetaangaben_arr.Meta_Keywords}{/if}" tabindex="1" /></p>
				
				<p><label for="Meta Description Praefix">{#globalemetaangabenMetaDescPraefix#}</label>
				<input type="text" name="Meta_Description_Praefix" value="{if isset($oMetaangaben_arr.Meta_Description_Praefix)}{$oMetaangaben_arr.Meta_Description_Praefix}{/if}" tabindex="1" /></p>
				
				{foreach name=conf from=$oConfig_arr item=oConfig}
				{if $oConfig->cConf == "Y"}
				<p><label for="{$oConfig->cWertName}">{$oConfig->cName} {if $oConfig->cBeschreibung}<img src="{$currentTemplateDir}gfx/help.png" alt="{$oConfig->cBeschreibung}" title="{$oConfig->cBeschreibung}" style="vertical-align:middle; cursor:help;" /></label>
				{if $oConfig->cInputTyp=="selectbox"}
				<select name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" class="combo"> 
				{foreach name=selectfor from=$oConfig->ConfWerte item=wert}
				<option value="{$wert->cWert}" {if $oConfig->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
				{/foreach}
				</select> 
				{else}
				<input type="text" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" value="{$oConfig->gesetzterWert}" tabindex="1" /></p>
				{/if}
				{else}
				{if $oConfig->cName}<h3 style="text-align:center;">{$oConfig->cName}</h3>{/if}
				{/if}
				{/if}
				{/foreach}
			</div>
			
			<div class="save_wrapper">
				<input name="speichern" type="submit" value="{#globalemetaangabenSave#}" class="button orange" />
			</div>
		</form>
	</div>
</div>

{include file='tpl_inc/footer.tpl'}