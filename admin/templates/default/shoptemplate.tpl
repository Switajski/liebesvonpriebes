{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: shoptemplate.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: JTL-Software-GmbH
	http://www.jtl-software.de
	
	Copyright (c) 2007 JTL-Software
    

-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="shoptemplate"}

{assign var="cBeschreibung" value=#shoptemplatesDesc#}
{if isset($oEinstellungenXML) && $oEinstellungenXML}
	{assign var="cTitel" value="Einstellungen: `$oTemplate->cName`"}
{elseif isset($oLayout) && $oLayout}
	{assign var="cTitel" value="Layout: `$oTemplate->cName`"}
{else}
	{assign var="cTitel" value=#shoptemplates#}
{/if}
{include file="tpl_inc/seite_header.tpl" cTitel=$cTitel cBeschreibung=$cBeschreibung cDokuURL=#shoptemplateURL#}

<div id="content">
	{if $cHinweis}
		<p class="box_success">{$cHinweis}</p>
	{/if}
	
	{if $cFehler}
		<p class="box_error">{$cFehler}</p>
	{/if}
	
	{if isset($oEinstellungenXML) && $oEinstellungenXML}
		<form action="shoptemplate.php" method="post">
			<div id="settings">
			{if isset($oTemplate->eTyp) && $oTemplate->eTyp === 'admin'}
				<input type="hidden" name="eTyp" value="admin" />
			{else}
            <div class="category">Template</div>
            <div class="item">
               <div class="name">
                  <label for="eTyp">
                     Standard-Template f&uuml;r mobile Endger&auml;te?
                  </label>
               </div>
               <div class="for">
                  <select name="eTyp">
                     <option value="standard" {if $oTemplate->eTyp == 'standard'}selected="selected"{/if}>Nein (optimiert f&uuml;r Standard-Browser)</option>
                     <option value="mobil" {if $oTemplate->eTyp == 'mobil'}selected="selected"{/if}>Ja (optimiert f&uuml;r mobile Endger&auml;te)</option>
                  </select>
               </div>
            </div>
			{/if}
			{foreach from=$oEinstellungenXML item=oSection}
				<div class="category">{$oSection->cName}</div>
				{foreach from=$oSection->oSettings_arr item=oSetting}
					<div class="item">
						{if $oSetting->bEditable}
							<div class="name">
								<label for="{$oSection->cKey}.{$oSetting->cKey}">
									{$oSetting->cName}
								</label>
							</div>
							<div class="for">
								{if $oSetting->cType == 'select'}
								<select name="cWert[]" id="{$oSection->cKey}.{$oSetting->cKey}">
									{foreach from=$oSetting->oOptions_arr item=oOption}
									<option value="{$oOption->cValue}" {if $oOption->cValue == $oSetting->cValue}selected="selected"{/if}>{$oOption->cName}</option>
									{/foreach}
								</select>
								{elseif $oSetting->cType == 'float'}
									<input type="text" name="cWert[]" id="{$oSection->cKey}.{$oSetting->cKey}" value="{$oSetting->cValue|escape:"html"}" />
								{elseif $oSetting->cType == 'text'}
									<input type="text" name="cWert[]" id="{$oSection->cKey}.{$oSetting->cKey}" value="{$oSetting->cValue|escape:"html"}" />
								{/if}
							</div>
						{else}
							<input type="hidden" name="cWert[]" value="{$oSetting->cValue|escape:"html"}" />
						{/if}
						<input type="hidden" name="cSektion[]" value="{$oSection->cKey}" />
						<input type="hidden" name="cName[]" value="{$oSetting->cKey}" />
					</div>
				{/foreach}
			{/foreach}
			<div class="save_wrapper">
				{if isset($smarty.get.activate)}<input type="hidden" name="activate" value="1" />{/if}
				<input type="hidden" name="type" value="settings" />
				<input type="hidden" name="ordner" value="{$oTemplate->cOrdner}" />
				<input type="hidden" name="admin" value="{$admin}" />
				<button type="submit" class="button orange">{if isset($smarty.get.activate)}Template aktivieren{else}Einstellungen speichern{/if}</button>
			</div>
			</div>
		</form>
	{elseif isset($oLayout) && $oLayout}
		<form action="shoptemplate.php" method="post">
			<div id="settings">
				{foreach name="section" from=$oLayout item=oSection}
					<div class="category">{$oSection->cName}</div>
					{foreach name="layout" from=$oSection->oItem_arr item=oItem}
						<div class="item">
							<div class="name">
								<label for="item_s{$smarty.foreach.section.iteration}_a{$smarty.foreach.layout.iteration}">{$oItem->cName}</label>
							</div>
							
							<div class="for">
								<input type="hidden" name="selector[]" value="{$oItem->cSelector}" />
								<input type="hidden" name="attribute[]" value="{$oItem->cAttribute}" />
								
								{if $oItem->cAttribute == 'background-color' || $oItem->cAttribute == 'color'}
									<div id="colorSelector_s{$smarty.foreach.section.iteration}_a{$smarty.foreach.layout.iteration}" style="display:inline-block">
										<div style="background-color: {$oItem->cValue}" class="colorSelector"></div>
									</div>
									<input type="hidden" name="value[]" class="input_s{$smarty.foreach.section.iteration}_a{$smarty.foreach.layout.iteration}" value="{$oItem->cValue}" />
									<script type="text/javascript">
										$('#colorSelector_s{$smarty.foreach.section.iteration}_a{$smarty.foreach.layout.iteration}').ColorPicker({ldelim}
											color: '{$oItem->cValue}',
											onShow: function (colpkr) {ldelim}
												$(colpkr).fadeIn(500);
												return false;
											{rdelim},
											onHide: function (colpkr) {ldelim}
												$(colpkr).fadeOut(500);
												return false;
											{rdelim},
											onChange: function (hsb, hex, rgb) {ldelim}
												$('#colorSelector_s{$smarty.foreach.section.iteration}_a{$smarty.foreach.layout.iteration} div').css('backgroundColor', '#' + hex);
												$('.input_s{$smarty.foreach.section.iteration}_a{$smarty.foreach.layout.iteration}').val('#' + hex);
											{rdelim}
										{rdelim});
									</script>
								{else}
									<input type="text" name="value[]" value="{$oItem->cValue}" id="item_s{$smarty.foreach.section.iteration}_a{$smarty.foreach.layout.iteration}"  />
								{/if}
							</div>
						</div>
					{/foreach}
				{/foreach}
			</div>
			<div class="save_wrapper">
				<input type="hidden" name="type" value="layout" />
				<input type="hidden" name="ordner" value="{$oTemplate->cOrdner}" />
				<input type="submit" value="Layout speichern" class="button orange" />
				<button type="submit" name="reset" value="1" class="button orange">Wiederherstellen</button>
			</div>
		</form>
	{else}
		<div id="settings">
			<table class="list">
				<thead>
					<tr>
						<th style="width:120px;">Vorschau</th>
						<th>Name</th>
						<th>Status</th>
						<!--<th>Autor</th>-->
						<th>Version</th>
						<th>Ordner</th>
						<th>Optionen</th>
					</tr>
				</thead>
				<tbody>
					{foreach name="template" from=$oTemplate_arr item=oTemplate}
					<tr>
						<td>
							{if $oTemplate->cPreview|count_characters > 0}<p class="image_preview" title="<strong>Template:</strong> {$oTemplate->cName}" ref="{$URL_SHOP}/templates/{$oTemplate->cOrdner}/{$oTemplate->cPreview}">{/if}
								<img src="{if $oTemplate->cPreview|strlen > 0}{$URL_SHOP}/templates/{$oTemplate->cOrdner}/{$oTemplate->cPreview}{else}{$URL_SHOP}/templates/gfx/nopreview.png{/if}" alt="" width="120" />
							{if $oTemplate->cPreview|count_characters > 0}</p>{/if}
						</td>
						<td class="tcenter">
							<h2 class="nospacing">{$oTemplate->cName}</h2>
							<p>
								{if $oTemplate->cURL|count_characters > 0}<a href="{$oTemplate->cURL}">{/if}
									{$oTemplate->cAuthor}
								{if $oTemplate->cURL|count_characters > 0}</a>{/if}
							</p>
						</td>
						<td class="tcenter">
							{if $oTemplate->bAktiv}
								<span class="success">Aktiviert {if $oTemplate->eTyp == 'mobil'}(Mobile Endger&auml;te){/if}</span>
							{/if}
						</td>
						<!--<td class="tcenter">{$oTemplate->cAuthor}</td>-->
						<td class="tcenter">{$oTemplate->cVersion}</td>
						<td class="tcenter">{$oTemplate->cOrdner}</td>
						<td class="tcenter">
							{if !$oTemplate->bAktiv}                          
								{if $oTemplate->bEinstellungen}
									<a class="button" href="shoptemplate.php?settings={$oTemplate->cOrdner}&activate=1">Aktivieren</a>
								{else}
									<a class="button" href="shoptemplate.php?switch={$oTemplate->cOrdner}">Aktivieren</a>
								{/if}
							{else}
								{if $oTemplate->bEinstellungen}
									<a class="button" href="shoptemplate.php?settings={$oTemplate->cOrdner}">Einstellungen</a>
								{/if}
								{if $oTemplate->bLayout}
									<a class="button" href="shoptemplate.php?layout={$oTemplate->cOrdner}">Layout</a>
								{/if}
							{/if}
						</td>
					</tr>
					{/foreach}
				</tbody>
			</table>
			<div class="clearall">
				<h1>Admin-Templates</h1>
			</div>
			<table class="list">
				<thead>
				<tr>
					<th style="width:120px;">Vorschau</th>
					<th>Name</th>
					<th>Status</th>
					<!--<th>Autor</th>-->
					<th>Version</th>
					<th>Ordner</th>
					<th>Optionen</th>
				</tr>
				</thead>
				<tbody>
				{foreach name="template" from=$oAdminTemplate_arr item=oTemplate}
					<tr>
						<td>
							{if $oTemplate->cPreview|count_characters > 0}<p class="image_preview" title="<strong>Template:</strong> {$oTemplate->cName}" ref="{$URL_SHOP}/admin/templates/{$oTemplate->cOrdner}/{$oTemplate->cPreview}">{/if}
								<img src="{if $oTemplate->cPreview|strlen > 0}{$URL_SHOP}/{$PFAD_ADMIN}templates/{$oTemplate->cOrdner}/{$oTemplate->cPreview}{else}{$URL_SHOP}/templates/gfx/nopreview.png{/if}" alt="" width="120" />
								{if $oTemplate->cPreview|count_characters > 0}</p>{/if}
						</td>
						<td class="tcenter">
							<h2 class="nospacing">{$oTemplate->cName}</h2>
							<p>
								{if $oTemplate->cURL|count_characters > 0}<a href="{$oTemplate->cURL}">{/if}
									{$oTemplate->cAuthor}
									{if $oTemplate->cURL|count_characters > 0}</a>{/if}
							</p>
						</td>
						<td class="tcenter">
							{if $oTemplate->bAktiv}
								<span class="success">Aktiviert {if $oTemplate->eTyp == 'mobil'}(Mobile Endger&auml;te){/if}</span>
							{/if}
						</td>
						<!--<td class="tcenter">{$oTemplate->cAuthor}</td>-->
						<td class="tcenter">{$oTemplate->cVersion}</td>
						<td class="tcenter">{$oTemplate->cOrdner}</td>
						<td class="tcenter">
							{if !$oTemplate->bAktiv}
								{if $oTemplate->bEinstellungen}
									<a class="button" href="shoptemplate.php?settings={$oTemplate->cOrdner}&activate=1&admin=true">Aktivieren</a>
								{else}
									<a class="button" href="shoptemplate.php?switch={$oTemplate->cOrdner}&admin=true">Aktivieren</a>
								{/if}
							{else}
								{if $oTemplate->bEinstellungen}
									<a class="button" href="shoptemplate.php?settings={$oTemplate->cOrdner}&admin=true">Einstellungen</a>
								{/if}
								{if $oTemplate->bLayout}
									<a class="button" href="shoptemplate.php?layout={$oTemplate->cOrdner}&admin=true">Layout</a>
								{/if}
							{/if}
						</td>
					</tr>
				{/foreach}
				</tbody>
			</table>
		</div>
	{/if}
</div>
{include file='tpl_inc/footer.tpl'}