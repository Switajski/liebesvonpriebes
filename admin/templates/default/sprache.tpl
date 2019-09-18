{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: branding.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: andreas.juetten@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
{config_load file="$lang.conf" section="lang"}
{include file='tpl_inc/header.tpl'}
<script type="text/javascript">
{literal}
$(document).ready(function() {
   $('.keyarea').each(function(idx, item) {
      var old_height = $(this).css('height');
      $(this).bind('focus', function() {
         $(this).css('height', '60px');
      });
   });   
});

function showSection(sectionID)
{
   $('.section').each(function(idx, item) {
      $(this).hide();
   });
   $('#section' + sectionID).show();
}
{/literal}
</script>
{include file="tpl_inc/seite_header.tpl" cTitel=#lang# cBeschreibung=#langDesc# cDokuURL=#langURL#}
<div id="content">		
	{if isset($hinweis) && $hinweis|count_characters > 0}			
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}			
		<p class="box_error">{$fehler}</p>
	{/if}
	
	<div class="container block tcenter">
		<form name="sprache" method="post" action="sprache.php">
			<input type="hidden" name="sprache" value="1" />		
			<label for="{#lang#}">Installierte Sprachen:</label>
			<select name="cISO" id="{#lang#}" onchange="javascript:document.sprache.submit();">
			<option value="">Bitte w&auml;hlen</option>
			{foreach from=$oInstallierteSprachen item=oSprache}
			<option value="{$oSprache->cISO}" {if $cISO == $oSprache->cISO}selected="selected"{/if}>{$oSprache->cNameDeutsch} {if $oSprache->cShopStandard=="Y"}(Standard){/if}</option>
			{/foreach}
			</select>
		</form>
	</div>
	
	{if $cISO|strlen > 0}
		<div class="tabber">
			<div class="tabbertab {if isset($cTab) && $cTab == 'sprachvariablen'}tabbertabdefault{/if}">
				<h2>Sprachvariablen</h2>
				<div class="block tcenter">
					<label for="section">Sektion:</label>
					<select name="kSprachsektion" onchange="showSection(options[selectedIndex].value);" id="section">
					{foreach from=$oWerte_arr item=oSektion}
						<option value="{$oSektion->kSprachsektion}" {if isset($kSprachsektion) && $kSprachsektion == $oSektion->kSprachsektion}selected="selected"{/if}>{$oSektion->cName}</option>
					{/foreach}
					</select>
				</div>
				
				{foreach from=$oWerte_arr item=oSektion}
				<div id="section{$oSektion->kSprachsektion}" class="container section">
					<form action="sprache.php" method="post">
						<table class="list">
							<thead>
							<tr>
								<th style="width:20%" class="tleft">Variable ({$oSektion->oWerte_arr|@count})</th>
								<th style="width:75%" class="tleft">Wert</th>
								<th style="width:15%">Aktion</th>
							</tr>
							<tbody>
								{foreach from=$oSektion->oWerte_arr item=oWert}
								<tr>
									<td>{$oWert->cName}</td>
									<td>
										<input type="hidden" name="cName[]" value="{$oWert->cName}" />
										<textarea style="width:99%;border:1px solid #ccc;padding:3px" rows="1" class="keyarea" id="{$oWert->kSprachsektion}{$oWert->cName}" name="cWert[]">{$oWert->cWert}</textarea>
									</td>
									<td valign="top" align="center">
										<a href="#" onclick="$('#{$oWert->kSprachsektion}{$oWert->cName}').val('{$oWert->cStandard|escape:"htmlall"}');return false;" class="button reset notext" title="wiederherstellen"></a>
										{if !$oWert->bSystem}
											<a href="sprache.php?cISO={$cISO}&action=delete&kSprachsektion={$oWert->kSprachsektion}&cName={$oWert->cName}" class="button remove notext" title="entfernen"></a>
										{/if}
									</td>
								</tr>
								{/foreach}
							</tbody>
						</table>
						<input type="hidden" name="action" value="updateSection" />
						<input type="hidden" name="cISO" value="{$cISO}" />
						<input type="hidden" name="kSprachsektion" value="{$oSektion->kSprachsektion}" />
						<div class="save_wrapper">
							<input type="submit" value="Speichern" class="button orange" />
						</div>
					</form>
				</div>
				{/foreach}
				
				<script type="text/javascript">
					showSection({if isset($kSprachsektion) && $kSprachsektion > 0}{$kSprachsektion}{else}{$oWerte_arr[0]->kSprachsektion}{/if});
				</script>
			</div>
			
			<div class="tabbertab {if isset($cTab) && $cTab == 'suche'}tabbertabdefault{/if}">
				<h2>Suche</h2>
				<form action="sprache.php" method="post" id="{if isset($oWert->cSektion)}{$oWert->cSektion}{/if}{if isset($oWert->cName)}{$oWert->cName}{/if}">
					<div class="container top block tcenter">
						Suchwort: <input type="text" name="cSuchwort" autocomplete="off" />
						<input type="hidden" name="action" value="search" />
						<input type="hidden" name="cISO" value="{$cISO}" />
						<input type="submit" value="Suchen" class="button blue" />
					</div>
				</form>
				
				{if isset($oSuchWerte_arr) && $oSuchWerte_arr|@count > 0}
					<form action="sprache.php" method="post">
						<table class="list">
							<thead>
								<tr>
									<th class="tleft" style="width:20%">Sektion</th>
									<th class="tleft" style="width:20%">Variable ({$oSuchWerte_arr|@count})</th>
									<th class="tleft" style="width:50%">Wert</th>
									<th class="th-3" style="width:10%">Aktion</th>
								</tr>
							</thead>
							
							<tbody>
							{foreach from=$oSuchWerte_arr item=oSuchWert}						
								<tr>
									<td valign="top" style="line-height:25px">{$oSuchWert->cSektionName}</td>
									<td valign="top" style="line-height:25px">{$oSuchWert->cName|regex_replace:"/($cSuchwort)/i":"<font color='#d70000'>\$1</font>"}</td>
									<td valign="top"><textarea style="width:99%;border:1px solid #ccc;padding:3px" rows="1" class="keyarea" id="suche_{$oSuchWert->kSprachsektion}{$oSuchWert->cName}" name="cWert[]">{$oSuchWert->cWert}</textarea></td>
									<td valign="top" align="center">
										<a href="#" onclick="$('#suche_{$oSuchWert->kSprachsektion}{$oSuchWert->cName}').val('{$oSuchWert->cStandard|escape:"htmlall"}');return false;" class="button reset notext" title="wiederherstellen"></a>
										{if !$oSuchWert->bSystem}
											<a href="sprache.php?cISO={$cISO}&action=delete&kSprachsektion={$oSuchWert->kSprachsektion}&cName={$oSuchWert->cName}" class="button remove notext" title="entfernen"></a>
										{/if}
										<input type="hidden" name="kSprachsektion[]" value="{$oSuchWert->kSprachsektion}" />
										<input type="hidden" name="cName[]" value="{$oSuchWert->cName}" />
									</td>
								</tr>
							{/foreach}
							</tbody>
						</table>
						<input type="hidden" name="update" value="1" />
						<input type="hidden" name="cSuchwort" value="{$cSuchwort}" />
						<input type="hidden" name="action" value="search" />
						<input type="hidden" name="cISO" value="{$cISO}" />
						<div class="save_wrapper">
							<input type="submit" value="Speichern" class="button orange" />
						</div>
					</form>
				{/if}
			</div>
			
			<div class="tabbertab {if isset($cTab) && $cTab == 'hinzufuegen'}tabbertabdefault{/if}">
				<h2>Hinzuf&uuml;gen</h2>
				<form action="sprache.php" method="post">
					<div id="settings">
						<div class="category first">Variable</div>
						<div class="item">
							<div class="name">Sektion</div>
							<div class="for">
								<select name="kSprachsektion" onchange="showSection(options[selectedIndex].value);">
								{foreach from=$oWerte_arr item=oSektion}
									<option value="{$oSektion->kSprachsektion}" {if $oSektion->cName == "custom"}selected="selected"{/if}>{$oSektion->cName}</option>
								{/foreach}
								</select>
							</div>
						</div>
						
						<div class="item">
							<div class="name">Variable</div>
							<div class="for">
								<input type="text" name="cName" />
							</div>
						</div>
						
						<div class="category">Sprachwert</div>
						
						{foreach from=$oInstallierteSprachen item=oSprache}
						<div class="item">
							<div class="name">{$oSprache->cNameDeutsch}</div>
							<div class="for">
								<input type="hidden" name="cSprachISO[]" value="{$oSprache->cISO}" />
								<input type="text" name="cWert[]" />
							</div>
						</div>
						{/foreach}
					</div>
					
					<div class="save_wrapper">
						<input type="hidden" name="action" value="add" />
						<input type="hidden" name="cISO" value="{$cISO}" />
						<input type="submit" value="Hinzuf&uuml;gen" class="button orange" />
					</div>
				</form>
			</div>
			
			{if $oLogWerte_arr|@count > 0}
			<div class="tabbertab {if isset($cTab) && $cTab == 'ngvariablen'}tabbertabdefault{/if}">
				<h2>Nicht gefundene Variablen</h2>
				
				<table class="list">
					<thead>
						<tr>
							<th class="tleft">Sektion</th>
							<th class="tleft">Variable</th>
						</tr>
					</thead>
					<tbody>
						{foreach from=$oLogWerte_arr item=oWert}
						<form action="sprache.php" method="post" id="{$oWert->cSektion}{$oWert->cName}">
							<input type="hidden" name="action" value="add" />
							<input type="hidden" name="cISO" value="{$cISO}" />
							<tr>
								<td>{$oWert->cSektion}</td>
								<td>{$oWert->cName}</td>
							</tr>
						</form>
						{/foreach}
					</tbody>
				</table>
			</div>
			{/if}
			<div class="tabbertab {if isset($cTab) && $cTab == 'export'}tabbertabdefault{/if}">
				<h2>Export</h2>
				<form action="sprache.php" method="post">                  
					Variablen: 
					<select name="nTyp">
						<option value="0">Alle Variablen</option>
						<option value="1">Nur Systemvariablen</option>
						<option value="2">Nur eigene Variablen</option>
					</select>
					
					<input type="hidden" name="action" value="export" />
					<input type="hidden" name="cISO" value="{$cISO}" />
					<input type="submit" value="Exportieren" class="button blue" />
				</form>
			</div>
			<div class="tabbertab {if isset($cTab) && $cTab == 'import'}tabbertabdefault{/if}">
				<h2>Import</h2>
				<form action="sprache.php" method="post" enctype="multipart/form-data">
					<div id="settings">
						<div class="category first">Sprache importieren</div>
						<div class="item">
							<div class="name">Sprache</div>
							<div class="for">
								<select name="cSprachISO" class="selectBox" id="cSprachISO">
									{foreach from=$oVerfuegbareSprachen item=oSprache}
									<option value="{$oSprache->cISO}" {if $oSprache->cISO == $cISO}selected="selected"{/if}>{$oSprache->cNameDeutsch}</option>
									{/foreach}
								</select>
							</div>
						</div>

						<div class="item">
							<div class="name">Typ</div>
							<div class="for">
								<select name="nTyp" id="nTyp" style="width:auto">
									<option value="0">Vorhandene l&ouml;schen, dann importieren</option>
									<option value="1">Vorhandene &uuml;berschreiben, neue importieren</option>
									<option value="2">Vorhandene beibehalten, neue importieren</option>
								</select>
							</div>
						</div>
						
						<div class="item">
							<div class="name">Datei</div>
							<div class="for">
								<input name="langfile" type="file" size="55" />
							</div>
						</div>
					</div>
					<div class="save_wrapper">
						<input type="hidden" name="action" value="import" />
						<input type="hidden" name="cISO" value="{$cISO}" />
						<input type="submit" value="Importieren" class="button orange" />
					</div>
				</form>
			</div>
		</div>
	{/if}
</div>
{include file='tpl_inc/footer.tpl'}