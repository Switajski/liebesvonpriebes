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
{config_load file="$lang.conf" section="kundenfeld"}
{include file='tpl_inc/header.tpl'}

<script type="text/javascript">
{assign var=WertCount value=0}
{if isset($oKundenfeld->oKundenfeldWert_arr) && $oKundenfeld->oKundenfeldWert_arr|@count > 0}
    {assign var=WertCount value=$oKundenfeld->oKundenfeldWert_arr|@count}
{elseif isset($xPostVar_arr.cWert) && $xPostVar_arr.cWert|@count > 0}
    {assign var=WertCount value=$xPostVar_arr.cWert|@count}
{/if}   
var i = 6 + {$WertCount};
var j = 1 + {$WertCount};

function selectCheck(selectBox)
{ldelim}
	if(selectBox.selectedIndex == 3)
	{ldelim}	
		var row = document.getElementById('formtable').insertRow(-1);
		row.id = document.getElementById('formtable').rows.length - 1;
		
		var cell_1 = row.insertCell(0);
		var cell_2 = row.insertCell(1);
		
		var input1 = document.createElement('input');
		input1.type = 'text';
		input1.name = 'cWert[]';
		input1.className = 'field';
		input1.id = 'cWert_' + row.id;
		
		var myText = document.createTextNode('Wert ' + j + ':');
		cell_1.appendChild(myText);
		cell_2.appendChild(input1);
		
		var button = document.createElement('input');
		button.type = 'button';
		button.name = 'button';
        button.className = 'button add';
		button.value = 'Wert hinzuf&uuml;gen';
		button.onclick = function() {ldelim} addInputRow(); {rdelim};
		
		var input2 = document.createElement('input');
		input2.type = 'button';
		input2.name = 'delete';
		input2.value = 'Entfernen';
		input2.className = 'button remove';

		$(input2).bind('click', function() {ldelim}
			delInputRow(row.id);
		{rdelim});
		
		cell_2.appendChild(button);
		cell_2.appendChild(input2);
		
		j += 1;
	{rdelim}
	else
	{ldelim}
		var nMax = (document.getElementById('formtable').rows.length - i);
		for(var z = 0; z < nMax; z++)
			delInputRow(6);
	{rdelim}
{rdelim}

function addInputRow()
{ldelim}
	var row = document.getElementById('formtable').insertRow(-1);
	row.id = document.getElementById('formtable').rows.length - 1;
	
	var cell_1 = row.insertCell(0);
	var cell_2 = row.insertCell(1);
	
	var input1 = document.createElement('input');
	input1.type = 'text';
	input1.name = 'cWert[]';
	input1.className = 'field';
	input1.id = 'cWert_' + row.id;
	
	var myText = document.createTextNode('Wert ' + j + ':');
	
	var input2 = document.createElement('input');
	input2.type = 'button';
	input2.name = 'delete';
	input2.value = 'Entfernen';
	input2.className = 'button remove';
	
	$(input2).bind('click', function() {ldelim}
		delInputRow(row.id);
	{rdelim});
	
	cell_1.appendChild(myText);
	cell_2.appendChild(input1);
	cell_2.appendChild(input2);
	
	j += 1;
{rdelim}

function delInputRow(deli)
{ldelim}
	//alert(document.getElementById('formtable').rows.selectedIndex);
	document.getElementById('formtable').deleteRow(deli);
	j -= 1;
{rdelim}
</script>

{include file="tpl_inc/seite_header.tpl" cTitel=#kundenfeld# cBeschreibung=#kundenfeldDesc# cDokuURL=#kundenfeldURL#}
<div id="content">
	{if isset($hinweis) && $hinweis|count_characters > 0}			
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}			
		<p class="box_error">{$fehler}</p>
	{/if}
   

	<form name="sprache" method="post" action="kundenfeld.php">
		<p class="txtCenter">
			<label for="{#changeLanguage#}">{#changeLanguage#}:</strong></label>
			<input id="{#changeLanguage#}" type="hidden" name="sprachwechsel" value="1">
			<select name="kSprache" class="selectBox" onchange="javascript:document.sprache.submit();">
			{foreach name=sprachen from=$Sprachen item=sprache}
				<option value="{$sprache->kSprache}" {if $sprache->kSprache==$smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
				{/foreach}
			</select>
		</p>
	</form>
   
	<div class="tabber">
   
		<div class="tabbertab{if isset($cTab) && $cTab == 'uebersicht'} tabbertabdefault{/if}">
		
			<br />
            <h2>{#kundenfeld#}</h2>
		
			<div class="container">
				<form name="kundenfeld" method="post" action="kundenfeld.php">
					<input type="hidden" name="{$session_name}" value="{$session_id}">
					<input type="hidden" name="kundenfelder" value="1">
					<input name="tab" type="hidden" value="uebersicht">
				{if isset($oKundenfeld->kKundenfeld) && $oKundenfeld->kKundenfeld > 0}
					<input type="hidden" name="kKundenfeld" value="{$oKundenfeld->kKundenfeld}">
				{elseif isset($kKundenfeld) && $kKundenfeld > 0}
					<input type="hidden" name="kKundenfeld" value="{$kKundenfeld}">
				{/if}

				<table class="list" id="formtable">
					<tr>
						<td>{#kundenfeldName#}</td>
						<td><input name="cName" type="text"{if isset($xPlausiVar_arr.cName)} class="fieldfillout"{/if} value="{if isset($xPostVar_arr.cName)}{$xPostVar_arr.cName}{elseif isset($oKundenfeld->cName)}{$oKundenfeld->cName}{/if}" /></td>
					</tr>
					<tr>
						<td>{#kundenfeldWawi#}</td>
						<td><input name="cWawi" type="text"{if isset($xPlausiVar_arr.cWawi)} class="fieldfillout"{/if} value="{if isset($xPostVar_arr.cWawi)}{$xPostVar_arr.cWawi}{elseif isset($oKundenfeld->cWawi)}{$oKundenfeld->cWawi}{/if}" /></td>
					</tr>
					<tr>
						<td>{#kundenfeldSort#}</td>
						<td><input name="nSort" type="text"{if isset($xPlausiVar_arr.nSort)} class="fieldfillout"{/if} value="{if isset($xPostVar_arr.nSort)}{$xPostVar_arr.nSort}{elseif isset($oKundenfeld->nSort)}{$oKundenfeld->nSort}{/if}" /> {#kundenfeldSortDesc#}</td>
					</tr>
					<tr>
						<td>{#kundenfeldPflicht#}</td>
						<td>
							<select name="nPflicht"{if isset($xPlausiVar_arr.nPflicht)} class="fieldfillout"{/if}>
								<option value="1"{if (isset($xPostVar_arr.nPflicht) && $xPostVar_arr.nPflicht == 1) || (isset($oKundenfeld->nPflicht) && $oKundenfeld->nPflicht == 1)} selected{/if}>Ja</option>
								<option value="0"{if (isset($xPostVar_arr.nPflicht) && $xPostVar_arr.nPflicht == 0) || (isset($oKundenfeld->nPflicht) && $oKundenfeld->nPflicht == 0)} selected{/if}>Nein</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>{#kundenfeldEditable#}</td>
						<td>
							<select name="nEdit"{if isset($xPlausiVar_arr.nEdit)} class="fieldfillout"{/if}>
								<option value="1"{if (isset($xPostVar_arr.nEdit) && $xPostVar_arr.nEdit == 1) || (isset($oKundenfeld->nEdit) && $oKundenfeld->nEdit == 1)} selected{/if}>Ja</option>
								<option value="0"{if (isset($xPostVar_arr.nEdit) && $xPostVar_arr.nEdit == 0) || (isset($oKundenfeld->nEdit) && $oKundenfeld->nEdit == 1)} selected{/if}>Nein</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>{#kundenfeldTyp#}</td>
						<td>
							<select name="cTyp" onchange="selectCheck(this);"{if isset($xPlausiVar_arr.cTyp)} class="fieldfillout"{/if}>
								<option value="text"{if (isset($xPostVar_arr.cTyp) && $xPostVar_arr.cTyp == "text") || (isset($oKundenfeld->cTyp) && $oKundenfeld->cTyp == "text")} selected{/if}>Text</option>
								<option value="zahl"{if (isset($xPostVar_arr.cTyp) && $xPostVar_arr.cTyp == "zahl") || (isset($oKundenfeld->cTyp) && $oKundenfeld->cTyp == "zahl")} selected{/if}>Zahl</option>
								<option value="datum"{if (isset($xPostVar_arr.cTyp) && $xPostVar_arr.cTyp == "datum") || (isset($oKundenfeld->cTyp) && $oKundenfeld->cTyp == "datum")} selected{/if}>Datum</option>
								<option value="auswahl"{if (isset($xPostVar_arr.cTyp) && $xPostVar_arr.cTyp == "auswahl") || (isset($oKundenfeld->cTyp) && $oKundenfeld->cTyp == "auswahl")} selected{/if}>Auswahl</option>
							</select>
							<br>
						</td>
					</tr>
				{if isset($oKundenfeld->oKundenfeldWert_arr) && $oKundenfeld->oKundenfeldWert_arr|@count > 0}
					{foreach name=kundenfeldwerte from=$oKundenfeld->oKundenfeldWert_arr key=key item=oKundenfeldWert}
					{assign var=i value=$key+1}
					{assign var=j value=$key+6}
					<tr>
						<td>Wert {$i}:</td>
						<td>
							<input name="cWert[]" id="Wert_{$i}" type="text" class="field" value="{$oKundenfeldWert->cWert}" />
							{if $i == 1}<input name="button" type="button" class="button add" value="Wert hinzuf&uuml;gen" onClick="javascript:addInputRow();" />{/if}
							<input name="delete" type="button" class="button remove" value="Entfernen" onClick="javascript:delInputRow({$j});" />
						</td>
					</tr>
					{/foreach}         
					{elseif isset($xPostVar_arr.cWert) && $xPostVar_arr.cWert|@count > 0}
					{foreach name=kundenfeldwerte from=$xPostVar_arr.cWert key=key item=cKundenfeldWert}
					{assign var=i value=$key+1}
					{assign var=j value=$key+6}
					<tr>
						<td>Wert {$i}:</td>
						<td>
							<input name="cWert[]" id="Wert_{$i}" type="text" class="field" value="{$cKundenfeldWert}" />
							{if $i == 1}<input name="button" type="button" class="button add" value="Wert hinzuf&uuml;gen" onClick="javascript:addInputRow();" />{/if}
							<input name="delete" type="button" class="button remove" value="Entfernen" onClick="javascript:delInputRow({$j});" />
						</td>
					</tr>
					{/foreach}         
				{/if}
				</table>

				<div class="save_wrapper">
					<input name="speichern" type="button" class="button orange" value="{#kundenfeldSave#}" onclick="javascript:document.kundenfeld.submit();">
				</div>

				</form>			
			</div>
			<div class="container">
				<div class="category">{#kundenfeldExistingDesc#}</div>
				<form method="POST" action="kundenfeld.php">
					<input name="kundenfelder" type="hidden" value="1">
					<input name="tab" type="hidden" value="uebersicht">
					<table>
						<thead>
							<tr>
								<th class="check"></th>
								<th class="tleft">{#kundenfeldNameShort#}</th>
								<th class="tleft">{#kundenfeldWawiShort#}</th>
								<th class="tleft">{#kundenfeldTyp#}</th>
								<th class="tleft">{#kundenfeldValue#}</th>
								<th class="th-6">{#kundenfeldEdit#}</th>
								<th class="th-7">{#kundenfeldSort#}</th>
								<th class="th-8"></th>
							</tr>
						</thead>
						<tbody>
						{foreach name=kundenfeld from=$oKundenfeld_arr item=oKundenfeld}
							<tr class="tab_bg{$smarty.foreach.kundenfeld.iteration%2}">
								<td class="check"><input name="kKundenfeld[]" type="checkbox" value="{$oKundenfeld->kKundenfeld}"></td>
								<td class="TD2">{$oKundenfeld->cName}{if $oKundenfeld->nPflicht == 1} *{/if}</td>
								<td class="TD3">{$oKundenfeld->cWawi}</td>
								<td class="TD4">{$oKundenfeld->cTyp}</td>
								<td class="TD5">
								{if isset($oKundenfeld->oKundenfeldWert_arr)}
								{foreach name=kundenfeldwert from=$oKundenfeld->oKundenfeldWert_arr item=oKundenfeldWert}
								{$oKundenfeldWert->cWert}{if !$smarty.foreach.kundenfeldwert.last}, {/if}
								{/foreach}
								{/if}
								</td>
								<td class="tcenter">{if $oKundenfeld->nEditierbar == 1}{#kundenfeldYes#}{else}{#kundenfeldNo#}{/if}</td>
								<td class="tcenter"><input name="nSort_{$oKundenfeld->kKundenfeld}" type="text" value="{$oKundenfeld->nSort}" size="5"></td>
								<td class="tcenter"><a href="kundenfeld.php?a=edit&kKundenfeld={$oKundenfeld->kKundenfeld}&tab=uebersicht"><span class="button blue">{#kundenfeldEdit#}</span></a></td>
							</tr>
						{/foreach}
						</tbody>
					</table>
					<div class="box_info container">{#kundenfeldPflichtDesc#}</div>
					<p class="submit">
						<input name="loeschen" type="submit" value="{#kundenfeldDel#}" class="button orange">
						<input name="aktualisieren" type="submit" value="{#kundenfeldUpdate#}" class="button orange">
					</p>
				</form>
			</div>
		</div>
			
	
		<div class="tabbertab{if isset($cTab) && $cTab == 'einstellungen'} tabbertabdefault{/if}">
			
			<br />
            <h2>{#kundenfeldSettings#}</h2>
		
			<div class="container">
				<form method="post" action="kundenfeld.php">
					<input type="hidden" name="{$session_name}" value="{$session_id}">
					<input type="hidden" name="einstellungen" value="1">
					<input name="tab" type="hidden" value="einstellungen">
					<div class="settings">
					{foreach name=conf from=$oConfig_arr item=oConfig}
						{if $oConfig->cConf == "Y"}
							<p><label for="{$oConfig->cWertName}">{$oConfig->cName} {if $oConfig->cBeschreibung}<img src="{$currentTemplateDir}gfx/help.png" alt="{$oConfig->cBeschreibung}" title="{$oConfig->cBeschreibung}" style="vertical-align:middle; cursor:help;" /></label>
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
							{if $oConfig->cName}<div class="category">{$oConfig->cName}</div>{/if}
						{/if}
					{/foreach}
					</div>

					<p class="submit"><input name="speichern" type="submit" value="{#kundenfeldSave#}" class="orange button" /></p>
				</form>
		   </div>
		</div>
	</div>
	
</div>

{include file='tpl_inc/footer.tpl'}