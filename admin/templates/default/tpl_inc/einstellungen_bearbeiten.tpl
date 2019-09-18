{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: einstellungen_bearbeiten.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: JTL-Software-GmbH
	http://www.jtl-software.de
	
	Copyright (c) 2007 JTL-Software

-------------------------------------------------------------------------------
*}

{assign var="cTitel" value=#preferences#|cat:": "|cat:$Sektion->cName}
{if isset($cSearch) && $cSearch|count_characters  > 0}
	{assign var="cTitel" value=$cSearch}
{/if}

{include file="tpl_inc/seite_header.tpl" cTitel=$cTitel cBeschreibung=$cPrefDesc cDokuURL=$cPrefURL}
<div id="content">
	{if isset($cHinweis) && $cHinweis|count_characters > 0}
		<p class="box_success">{$cHinweis}</p>
	{/if}
	{if isset($cFehler) && $cFehler|count_characters > 0}
		<p class="box_error">{$cFehler}</p>
	{/if}
	
	<div id="settings">
		<form name="einstellen" method="post" action="einstellungen.php">
			<input type="hidden" name="{$session_name}" value="{$session_id}" />
			<input type="hidden" name="einstellungen_bearbeiten" value="1" />
			{if isset($cSuche) && $cSuche|count_characters > 0}
				<input type="hidden" name="cSuche" value="{$cSuche}" />
				<input type="hidden" name="einstellungen_suchen" value="1" />
			{/if}
			<input type="hidden" name="kSektion" value="{$kEinstellungenSektion}" />	
			{if isset($Conf) && $Conf|@count > 0}
				{foreach name=conf from=$Conf item=cnf}
					{if $cnf->cConf=="Y"}
						<div class="item{if isset($cSuche) && $cnf->kEinstellungenConf == $cSuche} highlight{/if}">
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
				<div class="save_wrapper">
					<input type="submit" value="{#savePreferences#}" class="button orange" />
				</div>
			{else}
				<p class="box_info">{#noSearchResult#}</p>
			{/if}
		</form>
	</div>
</div>