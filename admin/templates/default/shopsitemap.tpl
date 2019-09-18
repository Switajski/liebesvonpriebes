{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: shopsitemap.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="shopsitemap"}

{include file="tpl_inc/seite_header.tpl" cTitel=#shopsitemap# cBeschreibung=#shopsitemapDesc# cDokuURL=#shopsitemapURL#}
<div id="content">
	
	{if isset($hinweis) && $hinweis|count_characters > 0}
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}
		<p class="box_error">{$fehler}</p>
	{/if}
	
	<div class="container">
		<form name="einstellen" method="post" action="shopsitemap.php" id="einstellen">
		<input type="hidden" name="{$session_name}" value="{$session_id}">
		<input type="hidden" name="speichern" value="1">
		<div id="settings">		
			{foreach name=conf from=$oConfig_arr item=cnf}
				{if $cnf->cConf=="Y"}
					<div class="item{if isset($cnf->kEinstellungenConf) && isset($cSuche) && $cnf->kEinstellungenConf == $cSuche} highlight{/if}">
						<div class="name">
							<label for="{$cnf->cWertName}">
								{$cnf->cName} <span class="sid">{$cnf->kEinstellungenConf} &raquo;</span>
							</label>
						</div>
						<div class="for">
							{if $cnf->cInputTyp=="selectbox"}
								<select name="{$cnf->cWertName}" id="{$cnf->cWertName}">
									{foreach name=selectfor from=$cnf->ConfWerte item=wert}
										<option value="{$wert->cWert}" {if $cnf->gesetzterWert==$wert->cWert}selected{/if}>{$wert->cName}</option>
									{/foreach}
								</select>
							{elseif $cnf->cInputTyp=="pass"}
								<input type="password" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{$cnf->gesetzterWert}" tabindex="1" /> 
							{else}
								<input type="text" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{$cnf->gesetzterWert}" tabindex="1" /> 
							{/if}
							
							{if $cnf->cBeschreibung}
								<div class="help" ref="{$cnf->kEinstellungenConf}" title="{$cnf->cBeschreibung}"></div>
							{/if}
						</div>
					</div> 
				{else}
					<div class="category">
						{$cnf->cName}
						<div class="right">
							<p class="sid">{$cnf->kEinstellungenConf}</p>
							{if isset($cnf->cSektionsPfad) && $cnf->cSektionsPfad|count_characters > 0}
								<p class="path"><strong>{#settingspath#}:</strong> {$cnf->cSektionsPfad}</p>
							{/if}
						</div>
					</div>
				{/if}
			{/foreach}
		</div>
		
		<p class="submit"><input name="speichern" type="submit" value="{#shopsitemapSave#}" class="button orange" /></p>
		</form>
	</div>		
</div>


{include file='tpl_inc/footer.tpl'}