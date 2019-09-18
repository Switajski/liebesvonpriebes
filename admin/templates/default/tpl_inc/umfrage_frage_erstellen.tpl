{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: umfrage_frage_erstellen.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
<script type="text/javascript">
{if $oUmfrageFrage->oUmfrageFrageAntwort_arr|@count > 0}
	var i = Number({$oUmfrageFrage->oUmfrageFrageAntwort_arr|@count}) + 1;
{else}
	var i = 1;
{/if}

{if $oUmfrageFrage->oUmfrageMatrixOption_arr|@count > 0}
	var im = Number({$oUmfrageFrage->oUmfrageMatrixOption_arr|@count}) + 1;
{else}
	var im = 1;
{/if}

function addInputRow()
{ldelim}
	var row = document.getElementById('formtable').insertRow(i);
	row.id = i;
	
	var cell_1 = row.insertCell(0);
	cell_1.className = "left";
	
	var input1 = document.createElement('input');
	input1.type = 'text';
	input1.name = 'cNameAntwort[]';
	input1.className = 'field';
	input1.id = 'cNameAntwort_' + i;
	
	var input2 = document.createElement('input');
	input2.type = 'text';
	input2.name = 'nSortAntwort[]';
	input2.className = 'field';
	input2.id = 'nSortAntwort_' + i;
	input2.style.width = '20px';
	
	var myText1 = document.createTextNode('Antwort ' + i + ':');
	var myText2 = document.createTextNode('  {#umfrageQSort#}: ');
	
	cell_1.appendChild(myText1);
	cell_1.appendChild(input1);
	
	cell_1.appendChild(myText2);
	cell_1.appendChild(input2);
	
	i += 1;
{rdelim}

function addInputRowOption()
{ldelim}
	var row = document.getElementById('formtableOption').insertRow(im);
	row.id = im;
	
	var cell_1 = row.insertCell(0);
	cell_1.className = "left";
	
	var input1 = document.createElement('input');
	input1.type = 'text';
	input1.name = 'cNameOption[]';
	input1.className = 'field';
	input1.id = 'cNameOption_' + im;
	
	var input2 = document.createElement('input');
	input2.type = 'text';
	input2.name = 'nSortOption[]';
	input2.className = 'field';
	input2.id = 'nSortOption_' + im;
	input2.style.width = '20px';	
	
	var myText1 = document.createTextNode('Option ' + im + ':');
	var myText2 = document.createTextNode('  {#umfrageQSort#}:');
	
	cell_1.appendChild(myText1);
	cell_1.appendChild(input1);
	
	cell_1.appendChild(myText2);
	cell_1.appendChild(input2);
	
	im += 1;
{rdelim}

function resetteFormtable()
{ldelim}
	document.getElementById('formtableOptionDIV').innerHTML = "";
	var table = document.createElement('table');
	table.id = "formtableOption";
	im = 1;
	var row = table.insertRow(0);
	var cell_1 = row.insertCell(0);			
	cell_1.className = "left";
	cell_1.id = "buttonsOption";
	document.getElementById('formtableOptionDIV').appendChild(table);

	document.getElementById('formtableDIV').innerHTML = "";
	var table = document.createElement('table');
	table.id = "formtable";
	i = 1;	
	var row = table.insertRow(0);
	var cell_1 = row.insertCell(0);		
	cell_1.className = "left";
	cell_1.id = "buttons";
	document.getElementById('formtableDIV').appendChild(table);
{rdelim}

function checkSelect(selectBox)
{ldelim}
	switch(Number(selectBox.selectedIndex))
	{ldelim}
		case 0:
			resetteFormtable();
			break;
		case 1:
			resetteFormtable();
			var row = document.getElementById('formtable').insertRow(i);
			row.id = i;
			
			var cell_1 = row.insertCell(0);			
			cell_1.className = "left";
			
			var input1 = document.createElement('input');
			input1.type = 'text';
			input1.name = 'cNameAntwort[]';
			input1.className = 'field';
			input1.id = 'cNameAntwort_' + i;
			
			var input2 = document.createElement('input');
			input2.type = 'text';
			input2.name = 'nSortAntwort[]';
			input2.className = 'field';
			input2.id = 'nSortAntwort_' + i;			
			input2.style.width = '20px';
			
			var myText1 = document.createTextNode('Antwort ' + i + ':');
			var myText2 = document.createTextNode('  {#umfrageQSort#}: ');
			
			cell_1.appendChild(myText1);
			cell_1.appendChild(input1);
			
			cell_1.appendChild(myText2);
			cell_1.appendChild(input2);
			
			var button = document.createElement('input');
			button.type = 'button';
			button.name = 'button';
			button.value = 'Antwort hinzufügen';
			button.onclick = function() {ldelim} addInputRow(); {rdelim};
			
			document.getElementById('buttons').appendChild(button);
			
			i += 1;
			break;
		case 2:	
			resetteFormtable();
			var row = document.getElementById('formtable').insertRow(i);
			row.id = i;
			var cell_1 = row.insertCell(0);			
			cell_1.className = "left";
			
			var input1 = document.createElement('input');
			input1.type = 'text';
			input1.name = 'cNameAntwort[]';
			input1.className = 'field';
			input1.id = 'cNameAntwort_' + i;
			
			var input2 = document.createElement('input');
			input2.type = 'text';
			input2.name = 'nSortAntwort[]';
			input2.className = 'field';
			input2.id = 'nSortAntwort_' + i;			
			input2.style.width = '20px';
			
			var myText1 = document.createTextNode('Antwort ' + i + ':');
			var myText2 = document.createTextNode('  {#umfrageQSort#}: ');
			
			cell_1.appendChild(myText1);
			cell_1.appendChild(input1);
			
			cell_1.appendChild(myText2);
			cell_1.appendChild(input2);
			
			var button = document.createElement('input');
			button.type = 'button';
			button.name = 'button';
			button.value = 'Antwort hinzufügen';
			button.onclick = function() {ldelim} addInputRow(); {rdelim};
			
			document.getElementById('buttons').appendChild(button);
			
			i += 1;
			break;
		case 3:
			resetteFormtable();
			var row = document.getElementById('formtable').insertRow(i);
			row.id = i;
			var cell_1 = row.insertCell(0);			
			cell_1.className = "left";
			
			var input1 = document.createElement('input');
			input1.type = 'text';
			input1.name = 'cNameAntwort[]';
			input1.className = 'field';
			input1.id = 'cNameAntwort_' + i;
			
			var input2 = document.createElement('input');
			input2.type = 'text';
			input2.name = 'nSortAntwort[]';
			input2.className = 'field';
			input2.id = 'nSortAntwort_' + i;			
			input2.style.width = '20px';
			
			var myText1 = document.createTextNode('Antwort ' + i + ':');
			var myText2 = document.createTextNode('  {#umfrageQSort#}: ');
			
			cell_1.appendChild(myText1);
			cell_1.appendChild(input1);
			
			cell_1.appendChild(myText2);
			cell_1.appendChild(input2);
			
			var button = document.createElement('input');
			button.type = 'button';
			button.name = 'button';
			button.value = 'Antwort hinzufügen';
			button.onclick = function() {ldelim} addInputRow(); {rdelim};
			
			document.getElementById('buttons').appendChild(button);
			
			i += 1;
			break;
		case 4:
			resetteFormtable();
			var row = document.getElementById('formtable').insertRow(i);
			row.id = i;
			var cell_1 = row.insertCell(0);			
			cell_1.className = "left";
			
			var input1 = document.createElement('input');
			input1.type = 'text';
			input1.name = 'cNameAntwort[]';
			input1.className = 'field';
			input1.id = 'cNameAntwort_' + i;
			
			var input2 = document.createElement('input');
			input2.type = 'text';
			input2.name = 'nSortAntwort[]';
			input2.className = 'field';
			input2.id = 'nSortAntwort_' + i;			
			input2.style.width = '20px';
			
			var myText1 = document.createTextNode('Antwort ' + i + ':');
			var myText2 = document.createTextNode('  {#umfrageQSort#}: ');
			
			cell_1.appendChild(myText1);
			cell_1.appendChild(input1);
			
			cell_1.appendChild(myText2);
			cell_1.appendChild(input2);
			
			var button = document.createElement('input');
			button.type = 'button';
			button.name = 'button';
			button.value = 'Antwort hinzufügen';
			button.onclick = function() {ldelim} addInputRow(); {rdelim};
			
			document.getElementById('buttons').appendChild(button);
			
			i += 1;
			break;
		case 5:
			resetteFormtable();
			break;
		case 6:
			resetteFormtable();
			break;
		case 7:
			resetteFormtable();
			
			var row = document.getElementById('formtableOption').insertRow(im);
			row.id = im;
			var cell_1 = row.insertCell(0);			
			cell_1.className = "left";
			
			var input1 = document.createElement('input');
			input1.type = 'text';
			input1.name = 'cNameOption[]';
			input1.className = 'field';
			input1.id = 'cNameOption_' + im;
			
			var input2 = document.createElement('input');
			input2.type = 'text';
			input2.name = 'nSortOption[]';
			input2.className = 'field';
			input2.id = 'nSortOption_' + im;
			input2.style.width = '20px';
			
			var myText1 = document.createTextNode('Option ' + im + ':');
			var myText2 = document.createTextNode('  {#umfrageQSort#}:');
			
			cell_1.appendChild(myText1);
			cell_1.appendChild(input1);
			
			cell_1.appendChild(myText2);
			cell_1.appendChild(input2);
			
			var button = document.createElement('input');
			button.type = 'button';
			button.name = 'button';
			button.value = 'Option hinzufügen';
			button.onclick = function() {ldelim} addInputRowOption(); {rdelim};
			
			document.getElementById('buttonsOption').appendChild(button);
			
			im += 1;
			
			var row = document.getElementById('formtable').insertRow(i);
			row.id = i;
			
			var cell_1 = row.insertCell(0);			
			cell_1.className = "left";
			
			var input1 = document.createElement('input');
			input1.type = 'text';
			input1.name = 'cNameAntwort[]';
			input1.className = 'field';
			input1.id = 'cNameAntwort_' + i;
			
			var input2 = document.createElement('input');
			input2.type = 'text';
			input2.name = 'nSortAntwort[]';
			input2.className = 'field';
			input2.id = 'nSortAntwort_' + i;			
			input2.style.width = '20px';
			
			var myText1 = document.createTextNode('Antwort ' + i + ':');
			var myText2 = document.createTextNode('  {#umfrageQSort#}: ');
			
			cell_1.appendChild(myText1);
			cell_1.appendChild(input1);
			
			cell_1.appendChild(myText2);
			cell_1.appendChild(input2);
			
			var button = document.createElement('input');
			button.type = 'button';
			button.name = 'button';
			button.value = 'Antwort hinzufügen';
			button.onclick = function() {ldelim} addInputRow(); {rdelim};
			
			document.getElementById('buttons').appendChild(button);
			
			i += 1;
			break;
		case 8:
			resetteFormtable();
			
			var row = document.getElementById('formtableOption').insertRow(i);
			row.id = im;
			var cell_1 = row.insertCell(0);			
			cell_1.className = "left";
			
			var input1 = document.createElement('input');
			input1.type = 'text';
			input1.name = 'cNameOption[]';
			input1.className = 'field';
			input1.id = 'cNameOption_' + im;
			
			var input2 = document.createElement('input');
			input2.type = 'text';
			input2.name = 'nSortOption[]';
			input2.className = 'field';
			input2.id = 'nSortOption_' + im;
			input2.style.width = '20px';	
			
			var myText1 = document.createTextNode('Option ' + im + ':');
			var myText2 = document.createTextNode('  {#umfrageQSort#}:');
			
			cell_1.appendChild(myText1);
			cell_1.appendChild(input1);
			
			cell_1.appendChild(myText2);
			cell_1.appendChild(input2);
			
			var button = document.createElement('input');
			button.type = 'button';
			button.name = 'button';
			button.value = 'Option hinzufügen';
			button.onclick = function() {ldelim} addInputRowOption(); {rdelim};
			
			document.getElementById('buttonsOption').appendChild(button);
			
			im += 1;
			
			var row = document.getElementById('formtable').insertRow(i);
			row.id = i;
			var cell_1 = row.insertCell(0);			
			cell_1.className = "left";
			
			var input1 = document.createElement('input');
			input1.type = 'text';
			input1.name = 'cNameAntwort[]';
			input1.className = 'field';
			input1.id = 'cNameAntwort_' + i;
			
			var input2 = document.createElement('input');
			input2.type = 'text';
			input2.name = 'nSortAntwort[]';
			input2.className = 'field';
			input2.id = 'nSortAntwort_' + i;
			input2.style.width = '20px';
			
			var myText1 = document.createTextNode('Antwort ' + i + ':');
			var myText2 = document.createTextNode('  {#umfrageQSort#}: ');
			
			cell_1.appendChild(myText1);
			cell_1.appendChild(input1);
			
			cell_1.appendChild(myText2);
			cell_1.appendChild(input2);
			
			var button = document.createElement('input');
			button.type = 'button';
			button.name = 'button';
			button.value = 'Antwort hinzufügen';
			button.onclick = function() {ldelim} addInputRow(); {rdelim};
			
			document.getElementById('buttons').appendChild(button);
			
			i += 1;
			break;
		case 9:
			resetteFormtable();
			break;
		case 10:
			resetteFormtable();
			break;
	{rdelim}
{rdelim}
</script>

<div id="page">
	<div id="content">
		<div id="welcome" class="post">
			<h2 class="title"><span>{#umfrageEnterQ#}</span></h2>
		</div>
		
		{if $hinweis}
			<br />
			<div class="userNotice">
				{$hinweis}
			</div>
		{/if}
		{if $fehler}
			<br />
			<div class="userError">
				{$fehler}
			</div>
		{/if}
		
		<br />
		
		{if $oUmfrageFrage_arr|@count > 0 && $oUmfrageFrage_arr}
		<div id="payment">
        	<div id="tabellenLivesuche">
        	<table>
        		<tr>
        			<th class="th-1">{#umfrageQ#}</th>
        			<th class="th-2">{#umfrageQType#}</th>
        			<th class="th-3">{#umfrageSort#}</th>
        		</tr>
        	{foreach name=umfragefrage from=$oUmfrageFrage_arr item=oUmfrageFrageTMP}
        		<tr class="tab_bg{$smarty.foreach.umfragefrage.iteration%2}">
        			<td class="TD1">{$oUmfrageFrageTMP->cName}</td>
        			<td class="TD2">{$oUmfrageFrageTMP->cTyp}</td>
        			<td class="TD3">{$oUmfrageFrageTMP->nSort}</td>
        		</tr>
			{/foreach}
        	</table>
        	</div>
		</div>		
		<br />
		{/if}
		
		<div class="container">
			<form name="umfrage" id="umfrage" method="post" action="umfrage.php">
			<input type="hidden" name="{$session_name}" value="{$session_id}" />
			<input type="hidden" name="umfrage" value="1" />
			<input type="hidden" name="umfrage_frage_speichern" value="1" />			
			<input type="hidden" name="kUmfrage" value="{$kUmfrageTMP}" />
			{if $oUmfrageFrage->kUmfrageFrage > 0}
			<input type="hidden" name="umfrage_frage_edit_speichern" value="1" />
			<input type="hidden" name="kUmfrageFrage" value="{$oUmfrageFrage->kUmfrageFrage}" />
			{/if}
			
			<table class="kundenfeld">				
				<tr>
					<td class="left"><b>{#umfrageQ#}:</b></td>
					<td><input name="cName" type="text"  value="{$oUmfrageFrage->cName}" /></td>
				</tr>
				
				<tr>
					<td class="left"><b>{#umfrageType#}:</b></td>
					<td>
						<select name="cTyp" id="cTypSelect" class="combo" onchange="checkSelect(this);">
							<option {if $oUmfrageFrage->kUmfrageFrage > 0}{else}selected{/if}></option>
							<option value="multiple_single"{if $oUmfrageFrage->cTyp == "multiple_single"}selected{/if}>Multiple Choice (Eine Antwort)</option>
							<option value="multiple_multi"{if $oUmfrageFrage->cTyp == "multiple_multi"}selected{/if}>Multiple Choice (Viele Antworten)</option>
							<option value="select_single"{if $oUmfrageFrage->cTyp == "select_single"}selected{/if}>Selectbox (Eine Antwort)</option>
							<option value="select_multi"{if $oUmfrageFrage->cTyp == "select_multi"}selected{/if}>SelectBox (Viele Antworten)</option>
							<option value="text_klein"{if $oUmfrageFrage->cTyp == "text_klein"}selected{/if}>Textfeld (klein)</option>
							<option value="text_gross"{if $oUmfrageFrage->cTyp == "text_gross"}selected{/if}>Textfeld (gro&szlig;)</option>
							<option value="matrix_single"{if $oUmfrageFrage->cTyp == "matrix_single"}selected{/if}>Matrix (Eine Antwort pro Zeile)</option>
							<option value="matrix_multi"{if $oUmfrageFrage->cTyp == "matrix_multi"}selected{/if}>Matrix (Viele Antworten pro Zeile)</option>
							<option value="text_statisch"{if $oUmfrageFrage->cTyp == "text_statisch"}selected{/if}>Statischer Trenntext</option>
							<option value="text_statisch_seitenwechsel"{if $oUmfrageFrage->cTyp == "text_statisch"}selected{/if}>Statischer Trenntext + Seitenwechsel</option>
						</select>
					</td>
				</tr>
				
				<tr>
					<td class="left"><b>{#umfrageSort#}:</b></td>
					<td><input name="nSort" type="text"  value="{$oUmfrageFrage->nSort}" /></td>
				</tr>
				
				<tr>
					<td class="left"><b>{#umfrageQFreeField#}:</b></td>
					<td>
						<select name="nFreifeld" class="combo">
							<option value="1"{if $oUmfrageFrage->nFreifeld == 1}selected{/if}>Ja</option>
							<option value="0"{if $oUmfrageFrage->nFreifeld == 0}selected{/if}>Nein</option>
						</select>
					</td>
				</tr>
				
				<tr>
					<td class="left"><b>{#umfrageQEssential#}:</b></td>
					<td>
						<select name="nNotwendig" class="combo">
							<option value="1"{if $oUmfrageFrage->nNotwendig == 1}selected{/if}>Ja</option>
							<option value="0"{if $oUmfrageFrage->nNotwendig == 0}selected{/if}>Nein</option>
						</select>
					</td>
				</tr>
				
				<tr>
					<td class="left"><b>{#umfrageText#}:</b></td>
					<td><textarea class="ckeditor" name="cBeschreibung" rows="15" cols="60">{$oUmfrageFrage->cBeschreibung}</textarea></td>
				</tr>			
			</table>
			
			<div id="formtableOptionDIV">
				<table id="formtableOption" class="kundenfeld">
					<tr>
						<td class="left" id="buttonsOption">{if $oUmfrageFrage->oUmfrageMatrixOption_arr|@count > 0}<input name="button" type="button" value="Option hinzufügen" onclick="addInputRowOption();" />{/if}</td>
					</tr>
					{if $oUmfrageFrage->oUmfrageMatrixOption_arr|@count > 0}
					{foreach name=umfragematrixoption from=$oUmfrageFrage->oUmfrageMatrixOption_arr item=oUmfrageMatrixOption}
					<input name="kUmfrageMatrixOption[]" type="hidden" value="{$oUmfrageMatrixOption->kUmfrageMatrixOption}" />
					<tr>
						<td>Option {$smarty.foreach.umfragematrixoption.iteration}:<input name="cNameOption[]"  type="text" value="{$oUmfrageMatrixOption->cName}" /> {#umfrageQSort#}: <input name="nSortOption[]"  type="text" value="{$oUmfrageMatrixOption->nSort}" style="width: 20px;"></td>
					</tr>
					{/foreach}
					{/if}		
				</table>
			</div>
			
			<div id="formtableDIV">
				<table id="formtable" class="kundenfeld">
					<tr>
						<td class="left" id="buttons">{if $oUmfrageFrage->oUmfrageFrageAntwort_arr|@count > 0}<input name="button" value="Antwort hinzufügen" type="button" onclick="addInputRow();" />{/if}</td>
					</tr>
					{if $oUmfrageFrage->oUmfrageFrageAntwort_arr|@count > 0}
					{foreach name=umfragefrageantwort from=$oUmfrageFrage->oUmfrageFrageAntwort_arr item=oUmfrageFrageAntwort}
					<input name="kUmfrageFrageAntwort[]" type="hidden" value="{$oUmfrageFrageAntwort->kUmfrageFrageAntwort}" />
					<tr>
						<td>Antwort {$smarty.foreach.umfragefrageantwort.iteration}:<input name="cNameAntwort[]"  type="text" value="{$oUmfrageFrageAntwort->cName}" /> {#umfrageQSort#}: <input name="nSortAntwort[]"  type="text" value="{$oUmfrageFrageAntwort->nSort}" style="width: 20px;" /></td>
					</tr>
					{/foreach}
					{/if}
				</table>
			</div>
			
			<table class="kundenfeld">
				<tr>
					<td class="left">&nbsp;</td>
					<td>
						{if $oUmfrageFrage->kUmfrageFrage > 0}
							<input name="speichern" type="submit" value="{#umfrageSave#}" />
						{else}
							<input name="nocheinefrage" type="submit" value="{#umfrageAnotherQ#}" />
							<input name="speichern" type="submit" value="{#umfrageSaveQ#}" />
						{/if}
					</td>
				</tr>
			</table>
			</form>
		</div>
	</div>
</div>

{if $oUmfrageFrage->kUmfrageFrage > 0}
<script type="text/javascript">
	document.getElementById("cTypSelect").disabled = true;
	var input_hidden_cTyp = document.createElement('input');
	input_hidden_cTyp.type = 'hidden';
	input_hidden_cTyp.name = 'cTyp';
	input_hidden_cTyp.value = '{$oUmfrageFrage->cTyp}';
	document.getElementById('umfrage').appendChild(input_hidden_cTyp);
</script>
{/if}