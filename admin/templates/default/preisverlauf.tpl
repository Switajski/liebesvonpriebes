{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: preisverlauf.tpl, smarty template inc file
	
	preisverlauf page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2007 JTL-Software
-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="preisverlauf"}

{include file="tpl_inc/seite_header.tpl" cTitel=#configurePriceFlow# cBeschreibung=#configurePriceFlowDesc# cDokuURL=#configurePriceFlowURL#}
<div id="content">
	
	{if isset($hinweis) && $hinweis|count_characters > 0}
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}
		<p class="box_error">{$fehler}</p>
	{/if}
	
	<div class="container">
		<form name="einstellen" method="post" action="preisverlauf.php">
		<input type="hidden" name="{$session_name}" value="{$session_id}">
		<input type="hidden" name="einstellungen" value="1">
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
					{if $oConfig->cName}<div class="category">({$oConfig->kEinstellungenConf}) {$oConfig->cName}</div>{/if}
				{/if}
			{/foreach}
		</div>
		
		<p class="submit"><input type="submit" value="{#save#}" class="button orange" /></p>
		</form>
	</div>	                   
 </div>
{include file='tpl_inc/footer.tpl'}