<script type="text/javascript">
    var i = {if isset($oUmfrageFrage->oUmfrageFrageAntwort_arr) && $oUmfrageFrage->oUmfrageFrageAntwort_arr|@count > 0}Number({$oUmfrageFrage->oUmfrageFrageAntwort_arr|@count}) + 1{else}1{/if},
    im = {if isset($oUmfrageFrage->oUmfrageMatrixOption_arr) && $oUmfrageFrage->oUmfrageMatrixOption_arr|@count > 0}Number({$oUmfrageFrage->oUmfrageMatrixOption_arr|@count}) + 1{else}1{/if};

function addInputRow() {ldelim}
    var row, cell_1, input1, input2, label1, label2, paragraph;
    row = document.getElementById('formtable').insertRow(i);
    row.id = '' + i;
    cell_1 = row.insertCell(0);

    paragraph = document.createElement('p');
    paragraph.className = 'form-inline';

    input1 = document.createElement('input');
    input1.type = 'text';
    input1.name = 'cNameAntwort[]';
    input1.className = 'form-control';
    input1.id = 'cNameAntwort_' + i;

    input2 = document.createElement('input');
    input2.type = 'text';
    input2.name = 'nSortAntwort[]';
    input2.className = 'form-control';
    input2.id = 'nSortAntwort_' + i;
    input2.style.width = '40px';

    label1 = document.createElement('label');
    label1.setAttribute('for', 'cNameAntwort_' + i);
    label1.innerHTML = 'Antwort ' + i;
    label1.style.paddingRight = '5px';

    label2 = document.createElement('label');
    label2.setAttribute('for', 'nSortAntwort_' + i);
    label2.innerHTML = '  {#umfrageQSort#}: ';
    label2.style.paddingLeft = '5px';
    label2.style.paddingRight = '5px';

    paragraph.appendChild(label1);
    paragraph.appendChild(input1);

    paragraph.appendChild(label2);
    paragraph.appendChild(input2);
    cell_1.appendChild(paragraph);

    i += 1;
{rdelim}

function addInputRowOption() {ldelim}
    var row, cell_1, input1, input2, myTex1, myText2, paragraph;
    row = document.getElementById('formtableOption').insertRow(im);
    row.id = im;

    cell_1 = row.insertCell(0);

    paragraph = document.createElement('p');
    paragraph.className = 'form-inline';

    input1 = document.createElement('input');
    input1.type = 'text';
    input1.name = 'cNameOption[]';
    input1.className = 'form-control';
    input1.id = 'cNameOption_' + im;

    input2 = document.createElement('input');
    input2.type = 'text';
    input2.name = 'nSortOption[]';
    input2.className = 'form-control';
    input2.id = 'nSortOption_' + im;
    input2.style.width = '40px';

    myText1 = document.createTextNode('Option ' + im + ':');
    myText2 = document.createTextNode('  {#umfrageQSort#}:');

    paragraph.appendChild(myText1);
    paragraph.appendChild(input1);

    paragraph.appendChild(myText2);
    paragraph.appendChild(input2);

    cell_1.appendChild(paragraph);

    im += 1;
{rdelim}

function resetteFormtable() {ldelim}
    var table, row, cell_1;
    document.getElementById('formtableOptionDIV').innerHTML = "";
    table = document.createElement('table');
    table.id = "formtableOption";
    im = 1;
    row = table.insertRow(0);
    cell_1 = row.insertCell(0);
    cell_1.className = "left";
    cell_1.id = "buttonsOption";
    document.getElementById('formtableOptionDIV').appendChild(table);

    document.getElementById('formtableDIV').innerHTML = "";
    table = document.createElement('table');
    table.id = "formtable";
    i = 1;
    row = table.insertRow(0);

    cell_1 = row.insertCell(0);
    cell_1.className = "left";
    cell_1.id = "buttons";
    document.getElementById('formtableDIV').appendChild(table);
{rdelim}

function checkSelect(selectBox) {ldelim}
    var row, cell_1, input1, input2, myText1, myText2, button, label1, label2, paragraph;
    switch(Number(selectBox.selectedIndex))
    {ldelim}
        case 0:
            resetteFormtable();
            break;
        case 1:
            resetteFormtable();
            row = document.getElementById('formtable').insertRow(i);
            row.id = i;

            cell_1 = row.insertCell(0);

            paragraph = document.createElement('p');
            paragraph.className = 'form-inline';

            input1 = document.createElement('input');
            input1.type = 'text';
            input1.name = 'cNameAntwort[]';
            input1.className = 'form-control field';
            input1.id = 'cNameAntwort_' + i;

            input2 = document.createElement('input');
            input2.type = 'text';
            input2.name = 'nSortAntwort[]';
            input2.className = 'form-control field';
            input2.id = 'nSortAntwort_' + i;
            input2.style.width = '40px';

            label1 = document.createElement('label');
            label1.setAttribute('for', 'cNameAntwort_' + i);
            label1.innerHTML = 'Antwort ' + i;
            label1.style.paddingRight = '5px';

            label2 = document.createElement('label');
            label2.setAttribute('for', 'nSortAntwort_' + i);
            label2.innerHTML = '  {#umfrageQSort#}: ';
            label2.style.paddingLeft = '5px';
            label2.style.paddingRight = '5px';

            paragraph.appendChild(label1);
            paragraph.appendChild(input1);

            paragraph.appendChild(label2);
            paragraph.appendChild(input2);

            cell_1.appendChild(paragraph);

            button = document.createElement('button');
            button.type = 'button';
            button.name = 'button';
            button.setAttribute('class', 'btn btn-primary');
            button.innerHTML = 'Antwort hinzuf&uuml;gen';
            button.onclick = function() {ldelim} addInputRow(); {rdelim};

            document.getElementById('buttons').appendChild(button);

            i += 1;
            break;
        case 2:
            resetteFormtable();
            row = document.getElementById('formtable').insertRow(i);
            row.id = i;
            cell_1 = row.insertCell(0);

            paragraph = document.createElement('p');
            paragraph.className = 'form-inline';

            input1 = document.createElement('input');
            input1.type = 'text';
            input1.name = 'cNameAntwort[]';
            input1.className = 'form-control field';
            input1.id = 'cNameAntwort_' + i;

            input2 = document.createElement('input');
            input2.type = 'text';
            input2.name = 'nSortAntwort[]';
            input2.className = 'form-control field';
            input2.id = 'nSortAntwort_' + i;
            input2.style.width = '40px';

            label1 = document.createElement('label');
            label1.setAttribute('for', 'cNameAntwort_' + i);
            label1.innerHTML = 'Antwort ' + i;
            label1.style.paddingRight = '5px';

            label2 = document.createElement('label');
            label2.setAttribute('for', 'nSortAntwort_' + i);
            label2.innerHTML = '  {#umfrageQSort#}: ';
            label2.style.paddingLeft = '5px';
            label2.style.paddingRight = '5px';

            paragraph.appendChild(label1);
            paragraph.appendChild(input1);

            paragraph.appendChild(label2);
            paragraph.appendChild(input2);

            cell_1.appendChild(paragraph);

            button = document.createElement('button');
            button.type = 'button';
            button.name = 'button';
            button.setAttribute('class', 'btn btn-primary');
            button.innerHTML = 'Antwort hinzuf&uuml;gen';
            button.onclick = function() {ldelim} addInputRow(); {rdelim};

            document.getElementById('buttons').appendChild(button);

            i += 1;
            break;
        case 3:
            resetteFormtable();
            row = document.getElementById('formtable').insertRow(i);
            row.id = i;

            cell_1 = row.insertCell(0);

            paragraph = document.createElement('p');
            paragraph.className = 'form-inline';

            input1 = document.createElement('input');
            input1.type = 'text';
            input1.name = 'cNameAntwort[]';
            input1.className = 'form-control field';
            input1.id = 'cNameAntwort_' + i;

            input2 = document.createElement('input');
            input2.type = 'text';
            input2.name = 'nSortAntwort[]';
            input2.className = 'form-control field';
            input2.id = 'nSortAntwort_' + i;
            input2.style.width = '40px';

            label1 = document.createElement('label');
            label1.setAttribute('for', 'cNameAntwort_' + i);
            label1.innerHTML = 'Antwort ' + i;
            label1.style.paddingRight = '5px';

            label2 = document.createElement('label');
            label2.setAttribute('for', 'nSortAntwort_' + i);
            label2.innerHTML = '  {#umfrageQSort#}: ';
            label2.style.paddingLeft = '5px';
            label2.style.paddingRight = '5px';

            paragraph.appendChild(label1);
            paragraph.appendChild(input1);

            paragraph.appendChild(label2);
            paragraph.appendChild(input2);

            cell_1.appendChild(paragraph);

            button = document.createElement('button');
            button.type = 'button';
            button.name = 'button';
            button.setAttribute('class', 'btn btn-primary');
            button.innerHTML = 'Antwort hinzuf&uuml;gen';
            button.onclick = function() {ldelim} addInputRow(); {rdelim};

            document.getElementById('buttons').appendChild(button);

            i += 1;
            break;
        case 4:
            resetteFormtable();
            row = document.getElementById('formtable').insertRow(i);
            row.id = i;
            cell_1 = row.insertCell(0);

            paragraph = document.createElement('p');
            paragraph.className = 'form-inline';

            input1 = document.createElement('input');
            input1.type = 'text';
            input1.name = 'cNameAntwort[]';
            input1.className = 'form-control field';
            input1.id = 'cNameAntwort_' + i;

            input2 = document.createElement('input');
            input2.type = 'text';
            input2.name = 'nSortAntwort[]';
            input2.className = 'form-control field';
            input2.id = 'nSortAntwort_' + i;
            input2.style.width = '40px';

            label1 = document.createElement('label');
            label1.setAttribute('for', 'cNameAntwort_' + i);
            label1.innerHTML = 'Antwort ' + i;
            label1.style.paddingRight = '5px';

            label2 = document.createElement('label');
            label2.setAttribute('for', 'nSortAntwort_' + i);
            label2.innerHTML = '  {#umfrageQSort#}: ';
            label2.style.paddingLeft = '5px';
            label2.style.paddingRight = '5px';

            paragraph.appendChild(label1);
            paragraph.appendChild(input1);

            paragraph.appendChild(label2);
            paragraph.appendChild(input2);

            cell_1.appendChild(paragraph);

            button = document.createElement('button');
            button.type = 'button';
            button.name = 'button';
            button.setAttribute('class', 'btn btn-primary');
            button.innerHTML = 'Antwort hinzuf&uuml;gen';
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
            row = document.getElementById('formtableOption').insertRow(im);
            row.id = im;
            cell_1 = row.insertCell(0);

            paragraph = document.createElement('p');
            paragraph.className = 'form-inline';

            input1 = document.createElement('input');
            input1.type = 'text';
            input1.name = 'cNameOption[]';
            input1.className = 'form-control field';
            input1.id = 'cNameOption_' + im;

            input2 = document.createElement('input');
            input2.type = 'text';
            input2.name = 'nSortOption[]';
            input2.className = 'form-control field';
            input2.id = 'nSortOption_' + im;
            input2.style.width = '40px';

            myText1 = document.createTextNode('Option ' + im + ':');
            myText2 = document.createTextNode('  {#umfrageQSort#}:');

            label1 = document.createElement('label');
            label1.setAttribute('for', 'cNameOption_' + im);
            label1.innerHTML = 'Option ' + im;
            label1.style.paddingRight = '5px';

            label2 = document.createElement('label');
            label2.setAttribute('for', 'nSortOption_' + im);
            label2.innerHTML = '  {#umfrageQSort#}: ';
            label2.style.paddingLeft = '5px';
            label2.style.paddingRight = '5px';

            paragraph.appendChild(label1);
            paragraph.appendChild(input1);

            paragraph.appendChild(label2);
            paragraph.appendChild(input2);

            cell_1.appendChild(paragraph);

            button = document.createElement('button');
            button.type = 'button';
            button.name = 'button';
            button.setAttribute('class', 'btn btn-primary');
            button.innerHTML = 'Option hinzuf&uuml;gen';
            button.onclick = function() {ldelim} addInputRowOption(); {rdelim};

            document.getElementById('buttonsOption').appendChild(button);

            im += 1;

            row = document.getElementById('formtable').insertRow(i);
            row.id = i;

            cell_1 = row.insertCell(0);

            paragraph = document.createElement('p');
            paragraph.className = 'form-inline';

            input1 = document.createElement('input');
            input1.type = 'text';
            input1.name = 'cNameAntwort[]';
            input1.className = 'form-control field';
            input1.id = 'cNameAntwort_' + i;

            input2 = document.createElement('input');
            input2.type = 'text';
            input2.name = 'nSortAntwort[]';
            input2.className = 'form-control field';
            input2.id = 'nSortAntwort_' + i;
            input2.style.width = '40px';

            label1 = document.createElement('label');
            label1.setAttribute('for', 'cNameAntwort_' + i);
            label1.innerHTML = 'Antwort ' + i;
            label1.style.paddingRight = '5px';

            label2 = document.createElement('label');
            label2.setAttribute('for', 'nSortAntwort_' + i);
            label2.innerHTML = '  {#umfrageQSort#}: ';
            label2.style.paddingLeft = '5px';
            label2.style.paddingRight = '5px';

            paragraph.appendChild(label1);
            paragraph.appendChild(input1);

            paragraph.appendChild(label2);
            paragraph.appendChild(input2);

            cell_1.appendChild(paragraph);

            button = document.createElement('button');
            button.type = 'button';
            button.name = 'button';
            button.setAttribute('class', 'btn btn-primary');
            button.innerHTML = 'Antwort hinzuf&uuml;gen';
            button.onclick = function() {ldelim} addInputRow(); {rdelim};

            document.getElementById('buttons').appendChild(button);

            i += 1;
            break;
        case 8:
            resetteFormtable();
            row = document.getElementById('formtableOption').insertRow(i);
            row.id = im;
            cell_1 = row.insertCell(0);

            paragraph = document.createElement('p');
            paragraph.className = 'form-inline';

            input1 = document.createElement('input');
            input1.type = 'text';
            input1.name = 'cNameOption[]';
            input1.className = 'form-control';
            input1.id = 'cNameOption_' + im;

            input2 = document.createElement('input');
            input2.type = 'text';
            input2.name = 'nSortOption[]';
            input2.className = 'form-control';
            input2.id = 'nSortOption_' + im;
            input2.style.width = '40px';

            label1 = document.createElement('label');
            label1.setAttribute('for', 'cNameOption_' + i);
            label1.innerHTML = 'Option ' + im;
            label1.style.paddingRight = '5px';

            label2 = document.createElement('label');
            label2.setAttribute('for', 'nSortOption_' + im);
            label2.innerHTML = '  {#umfrageQSort#}: ';
            label2.style.paddingLeft = '5px';
            label2.style.paddingRight = '5px';

            paragraph.appendChild(label1);
            paragraph.appendChild(input1);

            paragraph.appendChild(label2);
            paragraph.appendChild(input2);

            cell_1.appendChild(paragraph);

            button = document.createElement('button');
            button.type = 'button';
            button.name = 'button';
            button.setAttribute('class', 'btn btn-primary');
            button.innerHTML = 'Option hinzuf&uuml;gen';
            button.onclick = function() {ldelim} addInputRowOption(); {rdelim};

            document.getElementById('buttonsOption').appendChild(button);

            im += 1;

            row = document.getElementById('formtable').insertRow(i);
            row.id = '' + i;
            cell_1 = row.insertCell(0);

            paragraph = document.createElement('p');
            paragraph.className = 'form-inline';

            input1 = document.createElement('input');
            input1.type = 'text';
            input1.name = 'cNameAntwort[]';
            input1.className = 'form-control';
            input1.id = 'cNameAntwort_' + i;

            input2 = document.createElement('input');
            input2.type = 'text';
            input2.name = 'nSortAntwort[]';
            input2.className = 'form-control';
            input2.id = 'nSortAntwort_' + i;
            input2.style.width = '40px';

            label1 = document.createElement('label');
            label1.setAttribute('for', 'cNameAntwort_' + i);
            label1.innerHTML = 'Antwort ' + i;
            label1.style.paddingRight = '5px';

            label2 = document.createElement('label');
            label2.setAttribute('for', 'nSortAntwort_' + i);
            label2.innerHTML = '  {#umfrageQSort#}: ';
            label2.style.paddingLeft = '5px';
            label2.style.paddingRight = '5px';

            paragraph.appendChild(label1);
            paragraph.appendChild(input1);

            paragraph.appendChild(label2);
            paragraph.appendChild(input2);

            cell_1.appendChild(paragraph);

            button = document.createElement('button');
            button.type = 'button';
            button.name = 'button';
            button.setAttribute('class', 'btn btn-primary');
            button.innerHTML = 'Antwort hinzuf&uuml;gen';
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
    <div id="content" class="container-fluid">
        {*<div id="welcome" class="post">*}
            {*<h2 class="title"><span>{#umfrageEnterQ#}</span></h2>*}
        {*</div>*}
        {if isset($oUmfrageFrage_arr) && $oUmfrageFrage_arr|@count > 0}
        <div id="payment">
            <div id="tabellenLivesuche">
            <table class="table">
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

        <div class="content">
            <form name="umfrage" id="umfrage" method="post" action="umfrage.php">
                {$jtl_token}
                <input type="hidden" name="umfrage" value="1" />
                <input type="hidden" name="umfrage_frage_speichern" value="1" />
                <input type="hidden" name="kUmfrage" value="{$kUmfrageTMP}" />
                {if isset($oUmfrageFrage->kUmfrageFrage) && $oUmfrageFrage->kUmfrageFrage > 0}
                <input type="hidden" name="umfrage_frage_edit_speichern" value="1" />
                <input type="hidden" name="kUmfrageFrage" value="{$oUmfrageFrage->kUmfrageFrage}" />
                {/if}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{#umfrageEnterQ#}</h3>
                    </div>
                    <table class="kundenfeld table">
                        <tr>
                            <td><label for="cName">{#umfrageQ#}:</label></td>
                            <td><input class="form-control" id="cName" name="cName" type="text"  value="{if isset($oUmfrageFrage->cName)}{$oUmfrageFrage->cName}{/if}" /></td>
                        </tr>

                        <tr>
                            <td><label for="cTypSelect">{#umfrageType#}:</label></td>
                            <td>
                                <span class="input-group-wrap">
                                    <select name="cTyp" id="cTypSelect" class="form-control combo" onchange="checkSelect(this);">
                                        <option {if isset($oUmfrageFrage->kUmfrageFrage) && $oUmfrageFrage->kUmfrageFrage > 0}{else}selected{/if}></option>
                                        <option value="multiple_single"{if isset($oUmfrageFrage->cTyp) && $oUmfrageFrage->cTyp === 'multiple_single'}selected{/if}>Multiple Choice (Eine Antwort)</option>
                                        <option value="multiple_multi"{if isset($oUmfrageFrage->cTyp) && $oUmfrageFrage->cTyp === 'multiple_multi'}selected{/if}>Multiple Choice (Viele Antworten)</option>
                                        <option value="select_single"{if isset($oUmfrageFrage->cTyp) && $oUmfrageFrage->cTyp === 'select_single'}selected{/if}>Selectbox (Eine Antwort)</option>
                                        <option value="select_multi"{if isset($oUmfrageFrage->cTyp) && $oUmfrageFrage->cTyp === 'select_multi'}selected{/if}>SelectBox (Viele Antworten)</option>
                                        <option value="text_klein"{if isset($oUmfrageFrage->cTyp) && $oUmfrageFrage->cTyp === 'text_klein'}selected{/if}>Textfeld (klein)</option>
                                        <option value="text_gross"{if isset($oUmfrageFrage->cTyp) && $oUmfrageFrage->cTyp === 'text_gross'}selected{/if}>Textfeld (gro&szlig;)</option>
                                        <option value="matrix_single"{if isset($oUmfrageFrage->cTyp) && $oUmfrageFrage->cTyp === 'matrix_single'}selected{/if}>Matrix (Eine Antwort pro Zeile)</option>
                                        <option value="matrix_multi"{if isset($oUmfrageFrage->cTyp) && $oUmfrageFrage->cTyp === 'matrix_multi'}selected{/if}>Matrix (Viele Antworten pro Zeile)</option>
                                        <option value="text_statisch"{if isset($oUmfrageFrage->cTyp) && $oUmfrageFrage->cTyp === 'text_statisch'}selected{/if}>Statischer Trenntext</option>
                                        <option value="text_statisch_seitenwechsel"{if isset($oUmfrageFrage->cTyp) && $oUmfrageFrage->cTyp === 'text_statisch'}selected{/if}>Statischer Trenntext + Seitenwechsel</option>
                                    </select>
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <td><label for="nSort">{#umfrageSort#}:</label></td>
                            <td><input class="form-control" id="nSort" name="nSort" type="text"  value="{if isset($oUmfrageFrage->nSort)}{$oUmfrageFrage->nSort}{/if}" /></td>
                        </tr>

                        <tr>
                            <td><label for="nFreifeld">{#umfrageQFreeField#}:</label></td>
                            <td>
                                <select id="nFreifeld" name="nFreifeld" class="form-control combo">
                                    <option value="1"{if isset($oUmfrageFrage->nFreifeld) && $oUmfrageFrage->nFreifeld == 1}selected{/if}>Ja</option>
                                    <option value="0"{if isset($oUmfrageFrage->nFreifeld) && $oUmfrageFrage->nFreifeld == 0}selected{/if}>Nein</option>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td><label for="nNotwendig">{#umfrageQEssential#}:</label></td>
                            <td>
                                <select id="nNotwendig" name="nNotwendig" class="form-control combo">
                                    <option value="1"{if isset($oUmfrageFrage->nNotwendig) && $oUmfrageFrage->nNotwendig == 1}selected{/if}>Ja</option>
                                    <option value="0"{if isset($oUmfrageFrage->nNotwendig) && $oUmfrageFrage->nNotwendig == 0}selected{/if}>Nein</option>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td><label for="cBeschreibung">{#umfrageText#}:</label></td>
                            <td><textarea id="cBeschreibung" class="ckeditor" name="cBeschreibung" rows="15" cols="60">{if isset($oUmfrageFrage->cBeschreibung)}{$oUmfrageFrage->cBeschreibung}{/if}</textarea></td>
                        </tr>
                    </table>

                    <div id="formtableOptionDIV">
                        <table id="formtableOption" class="kundenfeld">
                            <tr>
                                <td id="buttonsOption">
                                    {if isset($oUmfrageFrage->oUmfrageMatrixOption_arr) && $oUmfrageFrage->oUmfrageMatrixOption_arr|@count > 0}
                                        <button name="button" type="button" value="Option hinzuf&uuml;gen" onclick="addInputRowOption();" class="btn btn-primary"><i class="fa fa-share"></i> Option hinzuf&uuml;gen</button>
                                    {/if}
                                </td>
                            </tr>
                            {if isset($oUmfrageFrage->oUmfrageMatrixOption_arr) && $oUmfrageFrage->oUmfrageMatrixOption_arr|@count > 0}
                            {foreach name=umfragematrixoption from=$oUmfrageFrage->oUmfrageMatrixOption_arr item=oUmfrageMatrixOption}
                            <input name="kUmfrageMatrixOption[]" type="hidden" value="{$oUmfrageMatrixOption->kUmfrageMatrixOption}" />
                            <tr>
                                <td>Option {$smarty.foreach.umfragematrixoption.iteration}:<input name="cNameOption[]"  type="text" value="{$oUmfrageMatrixOption->cName}" /> {#umfrageQSort#}: <input name="nSortOption[]"  type="text" value="{$oUmfrageMatrixOption->nSort}" style="width: 40px;"></td>
                            </tr>
                            {/foreach}
                            {/if}
                        </table>
                    </div>

                    <div id="formtableDIV">
                        <table id="formtable" class="kundenfeld table">
                            <tr>
                                <td id="buttons">
                                    {if isset($oUmfrageFrage->oUmfrageFrageAntwort_arr) && $oUmfrageFrage->oUmfrageFrageAntwort_arr|@count > 0}
                                        <button class="btn btn-succcess" name="button" value="Antwort hinzuf&uuml;gen" type="button" onclick="addInputRow();"><i class="fa fa-share"></i> Antwort hinzuf&uuml;gen</button>
                                    {/if}
                                </td>
                            </tr>
                            {if isset($oUmfrageFrage->oUmfrageFrageAntwort_arr) && $oUmfrageFrage->oUmfrageFrageAntwort_arr|@count > 0}
                                {foreach name=umfragefrageantwort from=$oUmfrageFrage->oUmfrageFrageAntwort_arr item=oUmfrageFrageAntwort}
                                <tr>
                                    <td>
                                        <p class="form-inline">
                                            <input name="kUmfrageFrageAntwort[]" type="hidden" value="{$oUmfrageFrageAntwort->kUmfrageFrageAntwort}" />
                                            <label for="cNameAntwort-{$smarty.foreach.umfragefrageantwort.index}">Antwort {$smarty.foreach.umfragefrageantwort.iteration}</label>
                                            <input class="form-control" id="cNameAntwort-{$smarty.foreach.umfragefrageantwort.index}" name="cNameAntwort[]"  type="text" value="{$oUmfrageFrageAntwort->cName}" />
                                            <label for="nSortAntwort-{$smarty.foreach.umfragefrageantwort.index}">{#umfrageQSort#}:</label>
                                            <input id="nSortAntwort-{$smarty.foreach.umfragefrageantwort.index}" name="nSortAntwort[]"  type="text" class="form-control" value="{$oUmfrageFrageAntwort->nSort}" style="width: 40px;" />
                                        </p>
                                    </td>
                                </tr>
                                {/foreach}
                            {/if}
                        </table>
                    </div>
                    <div class="panel-footer">
                        {if isset($oUmfrageFrage->kUmfrageFrage) && $oUmfrageFrage->kUmfrageFrage > 0}
                            <button class="btn btn-primary" name="speichern" type="submit" value="{#umfrageSave#}"><i class="fa fa-save"></i> {#umfrageSave#}</button>
                        {else}
                            <div class="btn-group">
                                <button class="btn btn-success" name="nocheinefrage" type="submit" value="{#umfrageAnotherQ#}"><i class="fa fa-share"></i> {#umfrageAnotherQ#}</button>
                                <button class="btn btn-primary" name="speichern" type="submit" value="{#umfrageSaveQ#}"><i class="fa fa-save"></i> {#umfrageSaveQ#}</button>
                            </div>
                        {/if}
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{if isset($oUmfrageFrage->kUmfrageFrage) && $oUmfrageFrage->kUmfrageFrage > 0}
<script type="text/javascript">
    var input_hidden_cTyp = document.createElement('input');
    document.getElementById("cTypSelect").disabled = true;
    input_hidden_cTyp.type = 'hidden';
    input_hidden_cTyp.name = 'cTyp';
    input_hidden_cTyp.value = '{$oUmfrageFrage->cTyp}';
    document.getElementById('umfrage').appendChild(input_hidden_cTyp);
</script>
{/if}