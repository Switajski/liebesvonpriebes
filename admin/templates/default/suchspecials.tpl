{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: suchspecials.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
{config_load file="$lang.conf" section="suchspecials"}
{include file='tpl_inc/header.tpl'}

{include file="tpl_inc/seite_header.tpl" cTitel=#suchspecials# cBeschreibung=#suchspecialsDesc# cDokuURL=#suchspecialURL#}
<div id="content">	
	{if isset($hinweis) && $hinweis|count_characters > 0}			
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}			
		<p class="box_error">{$fehler}</p>
	{/if}

	<div class="block clearall tcenter">
		<form name="sprache" method="post" action="suchspecials.php">
			<label for="{#changeLanguage#}">{#changeLanguage#}:</label>
			<input type="hidden" name="sprachwechsel" value="1">
			<select id="{#changeLanguage#}" name="kSprache" class="selectBox" onchange="javascript:document.sprache.submit();">
				{foreach name=sprachen from=$Sprachen item=sprache}
					<option value="{$sprache->kSprache}" {if $sprache->kSprache==$smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
				{/foreach}
			</select>
		</form>
	</div>

<div class="tabber">
	<div class="tabbertab{if isset($cTab) && $cTab == 'suchspecials'} tabbertabdefault{/if}">
		<h2>{#suchspecials#}</h2> 
		<form name="suchspecials" method="post" action="suchspecials.php">
			<div id="settings">
				<input type="hidden" name="{$session_name}" value="{$session_id}" />
				<input type="hidden" name="suchspecials" value="1" />
				
				<div class="item">
					<div class="name">{#bestseller#}</div>
					<div class="for"><input name="bestseller" type="text"  value="{$oSuchSpecials_arr[1]}"></div>
				</div>
				<div class="item">
					<div class="name">{#specialOffers#}</div>
					<div class="for"><input name="sonderangebote" type="text"  value="{$oSuchSpecials_arr[2]}"></div>
				</div>
				<div class="item">
					<div class="name">{#newInAssortment#}</div>
					<div class="for"><input name="neu_im_sortiment" type="text"  value="{$oSuchSpecials_arr[3]}"></div>
				</div>
				<div class="item">
					<div class="name">{#topOffers#}</div>
					<div class="for"><input name="top_angebote" type="text"  value="{$oSuchSpecials_arr[4]}"></div>
				</div>
				<div class="item">
					<div class="name">{#shortTermAvailable#}</div>
					<div class="for"><input name="in_kuerze_verfuegbar" type="text"  value="{$oSuchSpecials_arr[5]}"></div>
				</div>
				<div class="item">
					<div class="name">{#topreviews#}</div>
					<div class="for"><input name="top_bewertet" type="text"  value="{$oSuchSpecials_arr[6]}"></div>
				</div>
			</div>
			<div class="save_wrapper">
				<input type="submit" value="{#suchspecialsSave#}" class="button orange" />
			</div>
		</form>
	</div>
	
	<div class="tabbertab{if isset($cTab) && $cTab == 'einstellungen'} tabbertabdefault{/if}">
		
		<br />
		<h2>{#suchsepcialsSettings#}</h2>
		
		<form name="einstellen" method="post" action="suchspecials.php">
			<input type="hidden" name="{$session_name}" value="{$session_id}" />
			<input type="hidden" name="einstellungen" value="1" />
			<input type="hidden" name="tab" value="einstellungen" />
			<div class="settings">
				{foreach name=conf from=$oConfig_arr item=oConfig}
					{if $oConfig->cConf == "Y"}
						<p><label for="{$oConfig->cWertName}">({$oConfig->kEinstellungenConf}) {$oConfig->cName} {if $oConfig->cBeschreibung}<img src="{$currentTemplateDir}gfx/help.png" alt="{$oConfig->cBeschreibung}" title="{$oConfig->cBeschreibung}" style="vertical-align:middle; cursor:help;" /></label>
					{/if}
					{if $oConfig->cInputTyp=="selectbox"}
						<select name="{$oConfig->cWertName}" id="{$oConfig->cWertName}"> 
						{foreach name=selectfor from=$oConfig->ConfWerte item=wert}
							<option value="{$wert->cWert}" {if $oConfig->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
						{/foreach}
						</select>
					{elseif $oConfig->cInputTyp=="listbox"}
						<select name="{$oConfig->cWertName}[]" id="{$oConfig->cWertName}" multiple="multiple" style="width: 250px; height: 150px;"> 
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
			</div>
			
			<div class="save_wrapper">
				<input type="submit" value="{#suchspecialsSave#}" class="button orange" />
			</div>
		</form> 
		
	</div>
</div>
</div>

{include file='tpl_inc/footer.tpl'}