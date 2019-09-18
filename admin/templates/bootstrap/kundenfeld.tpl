{config_load file="$lang.conf" section="kundenfeld"}
{include file='tpl_inc/header.tpl'}

<script type="text/javascript">
    function countKundenfeldwert() {ldelim}
        return $('#formtable tr.kundenfeld_wert').length;
    {rdelim}

    function startKundenfeldwertEdit() {ldelim}
        $('#cTyp').after($('<div class="kundenfeld_wert"></div>').append(
                $('<button name="button" type="button" class="btn btn-primary add" value="Wert hinzuf&uuml;gen"></button>')
                .click(function() {ldelim}
                    addKundenfeldWert();
                {rdelim})
                .append('<i class="fa fa-plus-square-o"></i>&nbsp;Wert hinzuf&uuml;gen'))
        );
        addKundenfeldWert();
    {rdelim}

    function addKundenfeldWert() {ldelim}
        $('#formtable tbody').append($('<tr class="kundenfeld_wert"></tr>').append(
                '<td class="kundenfeld_wert_label">Wert ' + (countKundenfeldwert() + 1) + ':</td>',
                $('<td class="row"></td>').append(
                    $('<div class="col-lg-3 jtl-list-group"></div>').append(
                        '<input name="cWert[]" type="text" class="field form-control" value="" />'),
                    $('<div class="btn-group"></div>').append(
                        $('<button name="delete" type="button" class="btn btn-danger" value="Entfernen"></button>')
                            .click(function() {ldelim}
                                delKundenfeldWert(this);
                            {rdelim})
                            .append('<i class="fa fa-trash"></i>&nbsp;Entfernen')
                        )
                    )
                )
        );
    {rdelim}

    function delKundenfeldWert(pThis) {ldelim}
        if (countKundenfeldwert() > 1) {ldelim}
            $(pThis).closest('tr.kundenfeld_wert').remove();
            $('#formtable tr.kundenfeld_wert td.kundenfeld_wert_label').each(function(pIndex) {ldelim}
                $(this).html('Wert ' + (pIndex + 1) + ':');
            {rdelim});
        {rdelim} else {ldelim}
            alert('Das Feld muss mindestens einen Wert haben!');
        {rdelim}
    {rdelim}

    function stopKundenfeldwertEdit() {ldelim}
        $('#formtable .kundenfeld_wert').remove();
    {rdelim}

    function selectCheck(selectBox) {ldelim}
        if (selectBox.selectedIndex == 3) {ldelim}
            startKundenfeldwertEdit();
        {rdelim} else {ldelim}
            stopKundenfeldwertEdit();
        {rdelim}
    {rdelim}
</script>

{include file='tpl_inc/seite_header.tpl' cTitel=#kundenfeld# cBeschreibung=#kundenfeldDesc# cDokuURL=#kundenfeldURL#}
<div id="content" class="container-fluid">
    <div class="block">
        <form name="sprache" method="post" action="kundenfeld.php">
            {$jtl_token}
            <input id="{#changeLanguage#}" type="hidden" name="sprachwechsel" value="1" />
            <div class="p25 left input-group">
                <span class="input-group-addon">
                    <label for="kSprache">{#changeLanguage#}:</strong></label>
                </span>
                <span class="input-group-wrap last">
                    <select id="kSprache" name="kSprache" class="form-control selectBox" onchange="document.sprache.submit();">
                        {foreach name=sprachen from=$Sprachen item=sprache}
                            <option value="{$sprache->kSprache}" {if $sprache->kSprache == $smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
                        {/foreach}
                    </select>
                </span>
            </div>
        </form>
    </div>

    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($cTab) || $cTab === 'uebersicht'} active{/if}">
            <a data-toggle="tab" role="tab" href="#overview">{#kundenfeld#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'einstellungen'} active{/if}">
            <a data-toggle="tab" role="tab" href="#config">{#kundenfeldSettings#}</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="overview" class="tab-pane fade{if !isset($cTab) || $cTab === 'uebersicht'} active in{/if}">
            <form name="kundenfeld" method="post" action="kundenfeld.php">
                {$jtl_token}
                <input type="hidden" name="kundenfelder" value="1">
                <input name="tab" type="hidden" value="uebersicht">
                {if isset($oKundenfeld->kKundenfeld) && $oKundenfeld->kKundenfeld > 0}
                    <input type="hidden" name="kKundenfeld" value="{$oKundenfeld->kKundenfeld}">
                {elseif isset($kKundenfeld) && $kKundenfeld > 0}
                    <input type="hidden" name="kKundenfeld" value="{$kKundenfeld}">
                {/if}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{if isset($oKundenfeld->kKundenfeld) && $oKundenfeld->kKundenfeld > 0}{#kundenfeldEdit#}{else}{#kundenfeldCreate#}{/if}</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table list table-bordered" id="formtable">
                            <tr>
                                <td><label for="cName">{#kundenfeldName#}</label></td>
                                <td>
                                    <input id="cName" name="cName" type="text" class="{if isset($xPlausiVar_arr.cName)}fieldfillout{/if} form-control" value="{if isset($xPostVar_arr.cName)}{$xPostVar_arr.cName}{elseif isset($oKundenfeld->cName)}{$oKundenfeld->cName}{/if}" />
                                </td>
                            </tr>
                            <tr>
                                <td><label for="cWawi">{#kundenfeldWawi#}</label></td>
                                <td>
                                    <input id="cWawi" name="cWawi" type="text" class="{if isset($xPlausiVar_arr.cWawi)}fieldfillout{/if} form-control" value="{if isset($xPostVar_arr.cWawi)}{$xPostVar_arr.cWawi}{elseif isset($oKundenfeld->cWawi)}{$oKundenfeld->cWawi}{/if}" />
                                </td>
                            </tr>
                            <tr>
                                <td><label for="nSort">{#kundenfeldSort#}</label></td>
                                <td>
                                    <input id="nSort" name="nSort" type="text" class="{if isset($xPlausiVar_arr.nSort)}fieldfillout{/if} form-control" value="{if isset($xPostVar_arr.nSort)}{$xPostVar_arr.nSort}{elseif isset($oKundenfeld->nSort)}{$oKundenfeld->nSort}{/if}" placeholder="{#kundenfeldSortDesc#}"/>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="nPflicht">{#kundenfeldPflicht#}</label></td>
                                <td>
                                    <select id="nPflicht" name="nPflicht" class="{if isset($xPlausiVar_arr.nPflicht)} fieldfillout {/if}form-control">
                                        <option value="1"{if (isset($xPostVar_arr.nPflicht) && $xPostVar_arr.nPflicht == 1) || (isset($oKundenfeld->nPflicht) && $oKundenfeld->nPflicht == 1)} selected{/if}>
                                            Ja
                                        </option>
                                        <option value="0"{if (isset($xPostVar_arr.nPflicht) && $xPostVar_arr.nPflicht == 0) || (isset($oKundenfeld->nPflicht) && $oKundenfeld->nPflicht == 0)} selected{/if}>
                                            Nein
                                        </option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="nEdit">{#kundenfeldEditable#}</label></td>
                                <td>
                                    <select id="nEdit" name="nEdit" class="{if isset($xPlausiVar_arr.nEdit)} fieldfillout{/if} form-control">
                                        <option value="1"{if (isset($xPostVar_arr.nEdit) && $xPostVar_arr.nEdit == 1) || (isset($oKundenfeld->nEdit) && $oKundenfeld->nEdit == 1)} selected{/if}>
                                            Ja
                                        </option>
                                        <option value="0"{if (isset($xPostVar_arr.nEdit) && $xPostVar_arr.nEdit == 0) || (isset($oKundenfeld->nEdit) && $oKundenfeld->nEdit == 1)} selected{/if}>
                                            Nein
                                        </option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="cTyp">{#kundenfeldTyp#}</label></td>
                                <td>
                                    <select id="cTyp" name="cTyp" onchange="selectCheck(this);" class="{if isset($xPlausiVar_arr.cTyp)} fieldfillout{/if} form-control">
                                        <option value="text"{if (isset($xPostVar_arr.cTyp) && $xPostVar_arr.cTyp === 'text') || (isset($oKundenfeld->cTyp) && $oKundenfeld->cTyp === 'text')} selected{/if}>
                                            Text
                                        </option>
                                        <option value="zahl"{if (isset($xPostVar_arr.cTyp) && $xPostVar_arr.cTyp === 'zahl') || (isset($oKundenfeld->cTyp) && $oKundenfeld->cTyp === 'zahl')} selected{/if}>
                                            Zahl
                                        </option>
                                        <option value="datum"{if (isset($xPostVar_arr.cTyp) && $xPostVar_arr.cTyp === 'datum') || (isset($oKundenfeld->cTyp) && $oKundenfeld->cTyp === 'datum')} selected{/if}>
                                            Datum
                                        </option>
                                        <option value="auswahl"{if (isset($xPostVar_arr.cTyp) && $xPostVar_arr.cTyp === 'auswahl') || (isset($oKundenfeld->cTyp) && $oKundenfeld->cTyp === 'auswahl')} selected{/if}>
                                            Auswahl
                                        </option>
                                    </select>
                                    {if (isset($xPostVar_arr.cTyp) && $xPostVar_arr.cTyp === 'auswahl') || (isset($oKundenfeld->cTyp) && $oKundenfeld->cTyp === 'auswahl')}
                                        <div class="kundenfeld_wert">
                                            <button name="button" type="button" class="btn btn-primary add" value="Wert hinzuf&uuml;gen" onclick="addKundenfeldWert()"><i class="fa fa-plus-square-o"></i> Wert hinzuf&uuml;gen</button>
                                        </div>
                                    {/if}
                                </td>
                            </tr>
                            {if isset($oKundenfeld->oKundenfeldWert_arr) && $oKundenfeld->oKundenfeldWert_arr|@count > 0}
                                {foreach name=kundenfeldwerte from=$oKundenfeld->oKundenfeldWert_arr key=key item=oKundenfeldWert}
                                    {assign var=i value=$key+1}
                                    {assign var=j value=$key+6}
                                    <tr class="kundenfeld_wert">
                                        <td class="kundenfeld_wert_label">Wert {$i}:</td>
                                        <td class="row">
                                            <div class="col-lg-3 jtl-list-group">
                                                <input name="cWert[]" type="text" class="field form-control" value="{$oKundenfeldWert->cWert}" />
                                            </div>
                                            <div class="btn-group">
                                                <button name="delete" type="button" class="btn btn-danger" value="Entfernen" onclick="delKundenfeldWert(this)"><i class="fa fa-trash"></i> Entfernen</button>
                                            </div>
                                        </td>
                                    </tr>
                                {/foreach}
                            {elseif isset($xPostVar_arr.cWert) && $xPostVar_arr.cWert|@count > 0}
                                {foreach name=kundenfeldwerte from=$xPostVar_arr.cWert key=key item=cKundenfeldWert}
                                    {assign var=i value=$key+1}
                                    {assign var=j value=$key+6}
                                    <tr class="kundenfeld_wert">
                                        <td class="kundenfeld_wert_label">Wert {$i}:</td>
                                        <td class="row">
                                            <div class="col-lg-3 jtl-list-group">
                                                <input name="cWert[]" type="text" class="field form-control" value="{$cKundenfeldWert}" />
                                            </div>
                                            <div class="btn-group">
                                                <button name="delete" type="button" class="btn btn-danger" value="Entfernen" onclick="delKundenfeldWert(this)"><i class="fa fa-trash"></i> Entfernen</button>
                                            </div>
                                        </td>
                                    </tr>
                                {/foreach}
                            {/if}
                        </table>
                    </div>
                    <div class="panel-footer">
                        <button name="speichern" type="button" class="btn btn-primary" value="{#kundenfeldSave#}" onclick="document.kundenfeld.submit();"><i class="fa fa-save"></i> {#kundenfeldSave#}</button>
                    </div>
                </div>

            </form>


            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#kundenfeldExistingDesc#}</h3>
                </div>
                {if isset($oKundenfeld_arr) && $oKundenfeld_arr|@count > 0}
                    <form method="post" action="kundenfeld.php">
                        {$jtl_token}
                        <input name="kundenfelder" type="hidden" value="1">
                        <input name="tab" type="hidden" value="uebersicht">
                        <div class="table-responsive">
                            <table class="table">
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
                                        <td class="check">
                                            <input name="kKundenfeld[]" type="checkbox" value="{$oKundenfeld->kKundenfeld}" id="check-{$oKundenfeld->kKundenfeld}" />
                                        </td>
                                        <td class="TD2"><label for="check-{$oKundenfeld->kKundenfeld}">{$oKundenfeld->cName}{if $oKundenfeld->nPflicht == 1} *{/if}</label></td>
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
                                        <td class="tcenter">
                                            <input class="form-control" name="nSort_{$oKundenfeld->kKundenfeld}" type="text" value="{$oKundenfeld->nSort}" size="5" />
                                        </td>
                                        <td class="tcenter">
                                            <a href="kundenfeld.php?a=edit&kKundenfeld={$oKundenfeld->kKundenfeld}&tab=uebersicht&token={$smarty.session.jtl_token}"
                                               class="btn btn-default btn-sm" title="{#modify#}">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        </div>
                        <div class="panel-body">
                            <div class="alert alert-info">{#kundenfeldPflichtDesc#}</div>
                        </div>
                        <div class="panel-footer">
                            <div class="btn-group">
                                <button name="aktualisieren" type="submit" value="{#kundenfeldUpdate#}" class="btn btn-primary"><i class="fa fa-refresh"></i> {#kundenfeldUpdate#}</button>
                                <button name="loeschen" type="submit" value="{#kundenfeldDel#}" class="btn btn-danger">
                                    <i class="fa fa-trash"></i> {#deleteSelected#}
                                </button>
                            </div>
                        </div>
                    </form>
                {else}
                    <div class="panel-body">
                        <div class="alert alert-info"><i class="fa fa-info-circle"></i> {#noDataAvailable#}</div>
                    </div>
                {/if}
            </div>
        </div>
        <div id="config" class="tab-pane fade{if isset($cTab) && $cTab === 'einstellungen'} active in{/if}">
            {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' a='saveSettings' action='kundenfeld.php' buttonCaption=#save# title='Einstellungen' tab='einstellungen'}
        </div>
    </div>
</div>

{include file='tpl_inc/footer.tpl'}