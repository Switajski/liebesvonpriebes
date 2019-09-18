<script type="text/javascript">
    {assign var=addOne value=1}
    var i = {if isset($VersandartStaffeln) && $VersandartStaffeln|@count > 0}Number({$VersandartStaffeln|@count}) + 1{else}2{/if};
    function addInputRow() {ldelim}
        $('#price_range tbody').append('<tr><td><div class="input-group"><span class="input-group-addon"><label>{#upTo#}</label></span><input type="text" name="bis[]"  id="bis' + i + '" class="form-control kilogram"><span class="input-group-addon"><label>{if isset($einheit)}{$einheit}{/if}</label></span></div></td><td class="tcenter"><div class="input-group"><span class="input-group-addon"><label>{#amount#}</label></span><input type="text" name="preis[]"  id="preis' + i + '" class="form-control price_large"></div></td></tr>');
        i += 1;
    {rdelim}

    function confirmAllCombi() {ldelim}
        return confirm('{#shippingConfirm#}');
    {rdelim}

    {literal}
    function delInputRow() {
        i -= 1;
        $('#price_range tbody tr:last').remove();
    }

    function addShippingCombination() {
        var newCombi = '<li class=\'input-group\'>'+$('#ulVK #liVKneu').html()+'</li>';
        newCombi = newCombi.replace(/selectX/gi,'select');
        if ($("select[name='Versandklassen']").length >= 1) {
            newCombi = newCombi.replace(/<option value="-1">/gi, '<option value="-1" disabled="disabled">');
        }

        $('#ulVK').append(newCombi);
    }

    function updateVK() {
        var val = '';
        $("select[name='Versandklassen']").each( function(index) {
            if ($(this).val()!= null) {
                val += ((val.length > 0)?' ':'') + $(this).val().toString().replace(/,/gi,'-');
            }
        });
        $("input[name='kVersandklasse']").val(val);
    }

    function checkCombination() {
        var remove = false;
        $("select[name='Versandklassen']").each(function (index) {
            if (index == 0) {
                if ($.inArray("-1", $(this).val()) != -1) {
                    if (!confirmAllCombi()) {
                        var valSelected = $(this).val();
                        valSelected.shift();
                        $(this).val(valSelected);
                        $('.select2').select2();
                        return false;
                    }
                    if ($("select[name='Versandklassen']").length >= 1) {
                        $(this).val("-1");
                        $('#addNewShippingClassCombi').prop('disabled', true);
                        remove = true;
                    }
                    $(this).val("-1");
                    $('#addNewShippingClassCombi').prop('disabled', true);
                    $('.select2').select2();
                } else {
                    $('#addNewShippingClassCombi').prop('disabled', false);
                }
            } else {
                if (remove) {
                    $(this).parent().parent().detach();
                }
            }
        });
    }
    {/literal}
</script>

{assign var=cTitel value=#createShippingMethod#}
{assign var=cBeschreibung value=#createShippingMethodDesc#}

{if isset($Versandart->kVersandart) && $Versandart->kVersandart > 0}
    {assign var=cTitel value=#modifyedShippingType#}
    {assign var=cBeschreibung value=""}
{/if}

{include file='tpl_inc/seite_header.tpl' cTitel=$cTitel cBeschreibung=$cBeschreibung}
<div id="content" class="container-fluid">
    <form name="versandart_neu" method="post" action="versandarten.php">
        {$jtl_token}
        <input type="hidden" name="neueVersandart" value="1" />
        <input type="hidden" name="kVersandberechnung" value="{$versandberechnung->kVersandberechnung}" />
        <input type="hidden" name="kVersandart" value="{if isset($Versandart->kVersandart)}{$Versandart->kVersandart}{/if}" />
        <input type="hidden" name="cModulId" value="{$versandberechnung->cModulId}" />
        <div class="settings">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Allgemein</h3>
                </div>
                <div class="panel-body">
                    <ul class="jtl-list-group">
                        <li class="input-group">
                            <span class="input-group-addon">
                                <label for="cName">{#shippingMethodName#}</label>
                            </span>
                            <input class="form-control" type="text" id="cName" name="cName" value="{if isset($Versandart->cName)}{$Versandart->cName}{/if}" />
                        </li>
                        {foreach name=sprachen from=$sprachen item=sprache}
                            {assign var="cISO" value=$sprache->cISO}
                            {if isset($oVersandartSpracheAssoc_arr[$cISO])}
                                <li class="input-group">
                                    <span class="input-group-addon">
                                        <label for="cName_{$cISO}">{#showedName#} ({$sprache->cNameDeutsch})</label>
                                    </span>
                                    <input class="form-control" type="text" id="cName_{$cISO}" name="cName_{$cISO}" value="{if isset($oVersandartSpracheAssoc_arr[$cISO]->cName)}{$oVersandartSpracheAssoc_arr[$cISO]->cName}{/if}" />
                                </li>
                            {/if}
                        {/foreach}
                        <li class="input-group">
                            <span class="input-group-addon">
                                <label for="cBild">{#pictureURL#}</label>
                            </span>
                            <input class="form-control" type="text" id="cBild" name="cBild" value="{if isset($Versandart->cBild)}{$Versandart->cBild}{/if}" />
                            <span class="input-group-addon">{getHelpDesc cDesc=#pictureDesc#}</span>
                        </li>
                        {foreach name=sprachen from=$sprachen item=sprache}
                            {assign var="cISO" value=$sprache->cISO}
                            {if isset($oVersandartSpracheAssoc_arr[$cISO])}
                                <li class="input-group">
                                    <span class="input-group-addon">
                                        <label for="cLieferdauer_{$cISO}">{#shippingTime#} ({$sprache->cNameDeutsch})</label>
                                    </span>
                                    <input class="form-control" type="text" id="cLieferdauer_{$cISO}" name="cLieferdauer_{$cISO}" value="{if isset($oVersandartSpracheAssoc_arr[$cISO]->cLieferdauer)}{$oVersandartSpracheAssoc_arr[$cISO]->cLieferdauer}{/if}" />
                                </li>
                            {/if}
                        {/foreach}

                        <li class="input-group">
                            <span class="input-group-addon">
                                <label for="nMinLiefertage">{#minLiefertage#}</label>
                            </span>
                            <input class="form-control" type="text" id="nMinLiefertage" name="nMinLiefertage" value="{if isset($Versandart->nMinLiefertage)}{$Versandart->nMinLiefertage}{/if}" />
                        </li>

                        <li class="input-group">
                            <span class="input-group-addon">
                                <label for="nMaxLiefertage">{#maxLiefertage#}</label>
                            </span>
                            <input class="form-control" type="text" id="nMaxLiefertage" name="nMaxLiefertage" value="{if isset($Versandart->nMaxLiefertage)}{$Versandart->nMaxLiefertage}{/if}" />
                        </li>

                        <li class="input-group">
                            <span class="input-group-addon">
                                <label for="cAnzeigen">{#showShippingMethod#}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select name="cAnzeigen" id="cAnzeigen" class="form-control combo">
                                    <option value="immer" {if isset($Versandart->cAnzeigen) && $Versandart->cAnzeigen === 'immer'}selected{/if}>{#always#}</option>
                                    <option value="guenstigste" {if isset($Versandart->cAnzeigen) && $Versandart->cAnzeigen === 'guenstigste'}selected{/if}>{#lowest#}</option>
                                </select>
                            </span>
                        </li>

                        <li class="input-group">
                            <span class="input-group-addon">
                                <label for="cIgnoreShippingProposal">{#excludeShippingProposal#}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select name="cIgnoreShippingProposal" id="cIgnoreShippingProposal" class="form-control combo">
                                    <option value="N" {if isset($Versandart->cIgnoreShippingProposal) && $Versandart->cIgnoreShippingProposal === 'N'}selected{/if}>{#no#}</option>
                                    <option value="Y" {if isset($Versandart->cIgnoreShippingProposal) && $Versandart->cIgnoreShippingProposal === 'Y'}selected{/if}>{#yes#}</option>
                                </select>
                            </span>
                            <span class="input-group-addon">{getHelpDesc cDesc=#excludeShippingProposalDesc#}</span>
                        </li>

                        <li class="input-group">
                            <span class="input-group-addon">
                                <label for="cNurAbhaengigeVersandart">{#onlyForOwnShippingPrices#}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select name="cNurAbhaengigeVersandart" id="cNurAbhaengigeVersandart" class="combo form-control">
                                    <option value="N" {if isset($Versandart->cNurAbhaengigeVersandart) && $Versandart->cNurAbhaengigeVersandart === 'N'}selected{/if}>{#no#}</option>
                                    <option value="Y" {if isset($Versandart->cNurAbhaengigeVersandart) && $Versandart->cNurAbhaengigeVersandart === 'Y'}selected{/if}>{#yes#}</option>
                                </select>
                            </span>
                            <span class="input-group-addon">{getHelpDesc cDesc=#ownShippingPricesDesc#}</span>
                        </li>

                        <li class="input-group">
                            <span class="input-group-addon">
                                <label for="eSteuer">{#taxshippingcosts#}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select name="eSteuer" id="eSteuer" class="combo form-control">
                                    <option value="brutto" {if isset($Versandart->eSteuer) && $Versandart->eSteuer === 'brutto'}selected{/if}>{#gross#}</option>
                                    <option value="netto" {if isset($Versandart->eSteuer) && $Versandart->eSteuer === 'netto'}selected{/if}>{#net#}</option>
                                </select>
                            </span>
                            <span class="input-group-addon">{getHelpDesc cDesc=#taxshippingcostsDesc#}</span>
                        </li>

                        <li class="input-group">
                            <span class="input-group-addon">
                                <label for="cSendConfirmationMail">Versandbest&auml;tigung senden?</label>
                            </span>
                            <span class="input-group-wrap">
                                <select name="cSendConfirmationMail" id="cSendConfirmationMail" class="combo form-control">
                                    <option value="Y" {if isset($Versandart->cSendConfirmationMail) && $Versandart->cSendConfirmationMail === 'Y'}selected{/if}>{#yes#}</option>
                                    <option value="N" {if isset($Versandart->cSendConfirmationMail) && $Versandart->cSendConfirmationMail === 'N'}selected{/if}>{#no#}</option>
                                </select>
                            </span>
                            {*<span class="input-group-addon">{getHelpDesc cDesc=''}</span>*}
                        </li>

                        <li class="input-group">
                            <span class="input-group-addon">
                                <label for="nSort">{#sortnr#}</label>
                            </span>
                            <input class="form-control" type="text" id="nSort" name="nSort" value="{if isset($Versandart->nSort)}{$Versandart->nSort}{/if}" />
                        </li>

                        <li class="input-group">
                            <span class="input-group-addon">
                                <label for="kKundengruppe">{#customerclass#}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select name="kKundengruppe[]" id="kKundengruppe" multiple="multiple" class="combo form-control">
                                    <option value="-1" {if $gesetzteKundengruppen.alle}selected{/if}>{#all#}</option>
                                    {foreach name=kundengruppen from=$kundengruppen item=oKundengruppe}
                                        {assign var="klasse" value=$oKundengruppe->kKundengruppe}
                                        <option value="{$oKundengruppe->kKundengruppe}" {if isset($gesetzteKundengruppen.$klasse) && $gesetzteKundengruppen.$klasse}selected{/if}>{$oKundengruppe->cName}</option>
                                    {/foreach}
                                </select>
                            </span>
                            <span class="input-group-addon">{getHelpDesc cDesc=#customerclassDesc#}</span>
                        </li>

                        {foreach name=sprachen from=$sprachen item=sprache}
                            {assign var="cISO" value=$sprache->cISO}
                            {if isset($oVersandartSpracheAssoc_arr[$cISO])}
                                <li class="input-group">
                                    <span class="input-group-addon">
                                        <label for="cHinweistextShop_{$cISO}">{#shippingNoteShop#} ({$sprache->cNameDeutsch})</label>
                                    </span>
                                    <textarea id="cHinweistextShop_{$cISO}" class="form-control combo" name="cHinweistextShop_{$cISO}">{if isset($oVersandartSpracheAssoc_arr[$cISO]->cHinweistextShop)}{$oVersandartSpracheAssoc_arr[$cISO]->cHinweistextShop}{/if}</textarea>
                                </li>
                            {/if}
                        {/foreach}

                        {foreach name=sprachen from=$sprachen item=sprache}
                            {assign var="cISO" value=$sprache->cISO}
                            {if isset($oVersandartSpracheAssoc_arr[$cISO])}
                                <li class="input-group">
                                    <span class="input-group-addon">
                                        <label for="cHinweistext_{$cISO}">{#shippingNoteEmail#} ({$sprache->cNameDeutsch})</label>
                                    </span>
                                    <textarea id="cHinweistext_{$cISO}" class="form-control combo" name="cHinweistext_{$cISO}">{if isset($oVersandartSpracheAssoc_arr[$cISO]->cHinweistext)}{$oVersandartSpracheAssoc_arr[$cISO]->cHinweistext}{/if}</textarea>
                                </li>
                            {/if}
                        {/foreach}
                    </ul>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#validOnShippingClasses#}</h3>
                </div>
                <div class="panel-body">
                    <input name="kVersandklasse" type="hidden" value="{if !empty($Versandart->cVersandklassen)}{$Versandart->cVersandklassen}{else}-1{/if}">
                    <ul id="ulVK" class="jtl-list-group">
                        <li id='liVKneu' class="input-group" style="display:none;">
                            <span class="input-group-wrap">
                                <selectX class="selectX2 form-control" name="Versandklassen"
                                         onchange="checkCombination();updateVK();"
                                         multiple>
                                    <option value="-1">{#allCombinations#}</option>
                                    {foreach from=$versandKlassen item=vk}
                                        <option value="{$vk->kVersandklasse}">{$vk->cName}</option>
                                    {/foreach}
                                </selectX>
                            </span>
                            <span class="input-group-addon">{getHelpDesc cDesc=#shippingclassDesc#}</span>
                            <div class="input-group-btn">
                                <button class="btn btn-danger" type="button"
                                        onclick="$(this).parent().parent().detach(); updateVK();"><span
                                            class="glyphicon glyphicon-remove"></span></button>
                            </div>
                        </li>
                        {if !empty($Versandart->cVersandklassen)}
                            {$aVK = ' '|explode:$Versandart->cVersandklassen}
                            {foreach name="vKombi" from=$aVK item=VK}
                                <li class="input-group">
                                    <span class="input-group-wrap">
                                        <select class="select2 form-control" name="Versandklassen"
                                                onchange="checkCombination();updateVK();" multiple="multiple">
                                            <option value="-1"{if $smarty.foreach.vKombi.iteration >1} disabled="disabled"{/if}{if $VK === '-1'} selected{/if}>{#allCombinations#}</option>
                                            {if $VK === '-1'}
                                                {foreach from=$versandKlassen item=vk}
                                                    <option value="{$vk->kVersandklasse}">{$vk->cName}</option>
                                                {/foreach}
                                            {else}
                                                {$vkID = '-'|explode:$VK}
                                                {foreach from=$versandKlassen item=vk}
                                                    <option value="{$vk->kVersandklasse}"
                                                            {if $vk->kVersandklasse|in_array:$vkID}selected{/if}>{$vk->cName}</option>
                                                {/foreach}
                                            {/if}
                                        </select>
                                    </span>
                                    <span class="input-group-addon">{getHelpDesc cDesc=#shippingclassDesc#}</span>
                                    {if  $smarty.foreach.vKombi.iteration != 1}
                                    <div class="input-group-btn">
                                        <button class="btn btn-danger" type="button"
                                                onclick="$(this).parent().parent().detach(); updateVK();">
                                            <span class="glyphicon glyphicon-remove"></span></button>
                                    </div>
                                    {/if}
                                </li>
                            {/foreach}
                        {else}
                            <li class="input-group">
                                        <span class="input-group-wrap">
                                            <select class="select2 form-control" name="Versandklassen"
                                                    onchange="checkCombination();updateVK();" multiple="multiple">
                                                <option value="-1"{if $smarty.foreach.vKombi.iteration >1} disabled="disabled"{/if} selected>{#allCombinations#}</option>
                                                {foreach from=$versandKlassen item=vk}
                                                    <option value="{$vk->kVersandklasse}">{$vk->cName}</option>
                                                {/foreach}
                                            </select>
                                        </span>
                                <span class="input-group-addon">{getHelpDesc cDesc=#shippingclassDesc#}</span>
                            </li>
                        {/if}
                    </ul>
                </div>
                <div class="panel-footer">
                    <button id="addNewShippingClassCombi" class="btn btn-success" type="button"
                            onclick="addShippingCombination();$('.select2').select2();">
                        <span class="glyphicon glyphicon-plus"></span> {#addShippingClass#}
                    </button>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#acceptedPaymentMethods#}</h3>
                </div>
                <div class="panel-body">
                    <ul class="jtl-list-group">

                        <li class="input-group2 table-responsive">
                            <table class="list table">
                                <thead>
                                <tr>
                                    <th class="check"></th>
                                    <th class="tleft">Zahlungsart</th>
                                    <th></th>
                                    <th>{#amount#}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach name=zahlungsarten from=$zahlungsarten item=zahlungsart}
                                    {assign var="kZahlungsart" value=$zahlungsart->kZahlungsart}
                                    <tr>
                                        <td class="check">
                                            <input type="checkbox" id="kZahlungsart{$smarty.foreach.zahlungsarten.index}" name="kZahlungsart[]" class="boxen" value="{$kZahlungsart}" {if isset($VersandartZahlungsarten[$kZahlungsart]->checked)}{$VersandartZahlungsarten[$kZahlungsart]->checked}{/if} />
                                        </td>
                                        <td>
                                            <label for="kZahlungsart{$smarty.foreach.zahlungsarten.index}">
                                                {$zahlungsart->cName}{if isset($zahlungsart->cAnbieter) && $zahlungsart->cAnbieter|strlen > 0} ({$zahlungsart->cAnbieter}){/if}
                                            </label>
                                        </td>
                                        <td>{#discount#}</td>
                                        <td class="tcenter">
                                            <input type="text" id="Netto_{$kZahlungsart}" name="fAufpreis_{$kZahlungsart}" value="{if isset($VersandartZahlungsarten[$kZahlungsart]->fAufpreis)}{$VersandartZahlungsarten[$kZahlungsart]->fAufpreis}{/if}" class="form-control price_large"{* onKeyUp="setzePreisAjax(false, 'ZahlungsartAufpreis_{$zahlungsart->kZahlungsart}', this)"*} />
                                        </td>
                                        <td>
                                            <select name="cAufpreisTyp_{$kZahlungsart}" id="cAufpreisTyp_{$kZahlungsart}" class="form-control">
                                                <option value="festpreis"{if isset($VersandartZahlungsarten[$kZahlungsart]->cAufpreisTyp) && $VersandartZahlungsarten[$kZahlungsart]->cAufpreisTyp === 'festpreis'} selected{/if}>
                                                    Betrag
                                                </option>
                                                <option value="prozent"{if isset($VersandartZahlungsarten[$kZahlungsart]->cAufpreisTyp) && $VersandartZahlungsarten[$kZahlungsart]->cAufpreisTyp === 'prozent'} selected{/if}>
                                                    %
                                                </option>
                                            </select>
                                            <span id="ZahlungsartAufpreis_{$zahlungsart->kZahlungsart}" class="ZahlungsartAufpreis"></span>
                                        </td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#freeShipping#}</h3>
                </div>
                <div class="panel-body">
                    <ul class="jtl-list-group">
                        <li class="input-group">
                            <span class="input-group-addon"><label for="versandkostenfreiAktiv">{#activate#}</label></span>
                            <span class="input-group-wrap">
                                <select id="versandkostenfreiAktiv" name="versandkostenfreiAktiv" class="combo form-control">
                                    <option value="0">{#no#}</option>
                                    <option value="1" {if isset($Versandart->fVersandkostenfreiAbX) && $Versandart->fVersandkostenfreiAbX > 0}selected{/if}>{#yes#}</option>
                                </select>
                            </span>
                        </li>
                        <li class="input-group">
                            <span class="input-group-addon"><label>{#amount#}</label></span>
                            <input type="text" id="fVersandkostenfreiAbX" name="fVersandkostenfreiAbX" class="form-control price_large" value="{if isset($Versandart->fVersandkostenfreiAbX)}{$Versandart->fVersandkostenfreiAbX}{/if}">{* onKeyUp="setzePreisAjax(false, 'ajaxversandkostenfrei', this)" /> <span id="ajaxversandkostenfrei"></span>*}
                        </li>
                    </ul>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#maxCosts#}</h3>
                </div>
                <div class="panel-body">
                    <ul class="jtl-list-group">
                        <li class="input-group2 table-responsive">
                            <table class="list table">
                                <thead>
                                <tr>
                                    <th class="check"></th>
                                    <th></th>
                                    <th>{#amount#}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td class="check">
                                        <input type="checkbox" id="versanddeckelungAktiv" name="versanddeckelungAktiv" class="boxen" value="1" {if isset($Versandart->fDeckelung) && $Versandart->fDeckelung > 0}checked{/if} />
                                    </td>
                                    <td><label for="versanddeckelungAktiv">{#activate#}</label></td>
                                    <td class="tcenter">
                                        <input type="text" id="fDeckelung" name="fDeckelung" value="{if isset($Versandart->fDeckelung)}{$Versandart->fDeckelung}{/if}" class="form-control price_large">{* onKeyUp="setzePreisAjax(false, 'ajaxdeckelung', this)" /> <span id="ajaxdeckelung"></span>*}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </li>
                    </ul>
                </div>
            </div>
            {if $versandberechnung->cModulId === 'vm_versandberechnung_gewicht_jtl' || $versandberechnung->cModulId === 'vm_versandberechnung_warenwert_jtl' || $versandberechnung->cModulId === 'vm_versandberechnung_artikelanzahl_jtl'}
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#priceScale#}</h3>
                </div>
                <div class="panel-body">
                    <ul class="jtl-list-group">
                        <li class="input-group2 table-responsive">
                            <table id="price_range" class="table">
                                <thead>
                                <tr>
                                    <th class="p50"></th>
                                    <th>{#amount#}</th>
                                </tr>
                                </thead>
                                <tbody>

                                {if isset($VersandartStaffeln) && $VersandartStaffeln|@count > 0}
                                    {foreach name="preisstaffel" from=$VersandartStaffeln item=oPreisstaffel}
                                        {if $oPreisstaffel->fBis != 999999999}
                                            <tr>
                                                <td>
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><label>{#upTo#}</label></span>
                                                        <input type="text" id="bis{$smarty.foreach.preisstaffel.index}" name="bis[]" value="{if isset($VersandartStaffeln[$smarty.foreach.preisstaffel.index]->fBis)}{$VersandartStaffeln[$smarty.foreach.preisstaffel.index]->fBis}{/if}" class="form-control kilogram" />
                                                        <span class="input-group-addon"><label>{$einheit}</label></span>
                                                   </div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><label>{#amount#}:</label></span>
                                                        <input type="text" id="preis{$smarty.foreach.preisstaffel.index}" name="preis[]" value="{if isset($VersandartStaffeln[$smarty.foreach.preisstaffel.index]->fPreis)}{$VersandartStaffeln[$smarty.foreach.preisstaffel.index]->fPreis}{/if}" class="form-control price_large">{* onKeyUp="setzePreisAjax(false, 'ajaxpreisstaffel{$smarty.foreach.preisstaffel.index}', this)" /> <span id="ajaxpreisstaffel{$smarty.foreach.preisstaffel.index}"></span>*}
                                                    </div>
                                                </td>
                                            </tr>
                                        {/if}
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td>
                                            <div class="input-group">
                                                <span class="input-group-addon"><label>{#upTo#}</label></span>
                                                <input type="text" id="bis1" name="bis[]" value="" class="form-control kilogram" />
                                                <span class="input-group-addon"><label>{$einheit}</label></span>
                                            </div>
                                        </td>
                                        <td class="tcenter">
                                            <div class="input-group">
                                                <span class="input-group-addon"><label>{#amount#}:</label></span>
                                                <input type="text" id="preis1" name="preis[]" value="" class="form-control price_large">{* onKeyUp="setzePreisAjax(false, 'ajaxpreis1', this)" /> <span id="ajaxpreis1"></span>*}
                                            </div>
                                        </td>
                                    </tr>
                                {/if}

                                </tbody>
                            </table>
                            <div class="btn-group">
                                <button name="addRow" type="button" value="{#addPriceScale#}" onclick="addInputRow();" class="btn btn-primary"><i class="fa fa-share"></i> {#addPriceScale#}</button>
                                <button name="delRow" type="button" value="{#delPriceScale#}" onclick="delInputRow();" class="btn btn-danger"><i class="fa fa-trash"></i> {#delPriceScale#}</button>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            {elseif $versandberechnung->cModulId === 'vm_versandkosten_pauschale_jtl'}
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#shippingPrice#}</h3>
                </div>
                <div class="panel-body">
                    <ul class="jtl-list-group">
                        <li class="input-group2 table-responsive">
                            <table class="list table">
                                <thead>
                                <tr>
                                    <th class="check"></th>
                                    <th></th>
                                    <th>{#amount#}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td class="check"></td>
                                    <td></td>
                                    <td class="tcenter">
                                        <input type="text" id="fPreisNetto" name="fPreis" value="{if isset($Versandart->fPreis)}{$Versandart->fPreis}{/if}" class="form-control price_large">{* onKeyUp="setzePreisAjax(false, 'ajaxfPreisNetto', this)" /> <span id="ajaxfPreisNetto"></span>*}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </li>
                    </ul>
                </div>
            </div>
            {/if}
            {literal}
                <script type="text/javascript">
                    <!--
                    Array.prototype.contains = function (elem) {
                        var i;
                        for (i = 0; i < this.length; i++) {
                            if (this[i] == elem) {
                                return true;
                            }
                        }

                        return false;
                    };

                    var Nordasien = ['MN', 'RU'],
                        Ostasien = ['CN', 'TW', 'JP', 'KP', 'KR'],
                        Suedasien = ['BD', 'BT', 'IN', 'MV', 'NP', 'PK', 'LK'],
                        Suedostasien = ['BN', 'ID', 'KH', 'LA', 'MY', 'MM', 'PH', 'SG', 'TH', 'TL', 'VN'],
                        Vorderasien = ['EG', 'AM', 'AZ', 'BH', 'GE', 'IQ', 'IR', 'IL', 'YE', 'JO', 'QA', 'KW', 'LB', 'OM', 'PS', 'SA', 'SY', 'TR', 'AE', 'CY'],
                        Zentralasien = ['AF', 'KZ', 'KG', 'TJ', 'TM', 'ZU'],
                        Asien = ['MN', 'RU', 'CN', 'TW', 'JP', 'KP', 'KR', 'BD', 'BT', 'IN', 'MV', 'NP', 'PK', 'LK', 'BN', 'ID', 'KH', 'LA', 'MY', 'MM', 'PH', 'SG', 'TH', 'TL', 'VN', 'EG', 'AM', 'AZ', 'BH', 'GE', 'IQ', 'IR', 'IL', 'YE', 'JO', 'QA', 'KW', 'LB', 'OM', 'PS', 'SA', 'SY', 'TR', 'AE', 'AF', 'KG', 'TJ', 'TM'],
                        Europa = ['AL', 'AD', 'BE', 'BA', 'BG', 'DK', 'DE', 'EE', 'FI', 'FR', 'GR', 'IE', 'IT', 'KZ', 'HR', 'LV', 'LI', 'LT', 'LU', 'MT', 'MK', 'MD', 'MC', 'ME', 'NL', 'NO', 'AT', 'PL', 'PT', 'RO', 'RU', 'SM', 'SE', 'CH', 'RS', 'SK', 'SI', 'ES', 'CZ', 'TR', 'UA', 'HU', 'GB', 'VA', 'BY', 'FO', 'GI', 'SJ', 'CY', 'IS', 'YU'],
                        Europa_EU = ['BE', 'BG', 'DK', 'DE', 'EE', 'FI', 'FR', 'GR', 'HR', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'AT', 'PL', 'PT', 'RO', 'SE', 'SK', 'SI', 'ES', 'CZ', 'HU', 'GB', 'CY'],
                        Europa_nichtEU = ['AL', 'AD', 'BA', 'CH', 'IL', 'KZ', 'LI', 'MK', 'MD', 'MC', 'ME', 'NO', 'RU', 'SM', 'CH', 'RS', 'TR', 'UA', 'VA', 'BY', 'FO', 'GI', 'SJ', 'IS', 'YU'],
                        Afrika = ['EG', 'DZ', 'AO', 'GQ', 'ET', 'BJ', 'BW', 'BF', 'BI', 'DJ', 'CI', 'ER', 'GA', 'GM', 'GH', 'GN', 'GW', 'CM', 'CV', 'KE', 'KM', 'CD', 'CG', 'LS', 'LR', 'LY', 'MG', 'MW', 'ML', 'MA', 'MR', 'MU', 'MZ', 'NA', 'NE', 'NG', 'RW', 'ZM', 'ST', 'SN', 'SC', 'SL', 'ZW', 'SO', 'ZA', 'SD', 'SZ', 'TZ', 'TG', 'TD', 'TN', 'UG', 'CF'],
                        Nordamerika = ['CA', 'MX', 'US', 'BM', 'GL', 'PM'],
                        Mittelamerika = ['BZ', 'CR', 'SV', 'GT', 'HN', 'NI', 'PA'],
                        Suedamerika = ['AR', 'BO', 'BR', 'CL', 'CO', 'EC', 'FK', 'GF', 'GY', 'PY', 'PE', 'SR', 'UY', 'VE'],
                        Karibik = ['AG', 'BS', 'BB', 'CU', 'DM', 'DO', 'GD', 'HAT', 'JM', 'KN', 'LC', 'VC', 'TT', 'AI', 'AW', 'KY', 'GP', 'MQ', 'MS', 'AN', 'PR', 'TC', 'VG', 'VI'],
                        Ozeanien = ['AU', 'FJ', 'KI', 'MH', 'FM', 'NR', 'NZ', 'PW', 'PG', 'SB', 'WS', 'TO', 'TV', 'VU', 'AS', 'GU', 'UM', 'MP', 'PF', 'NC', 'WF', 'PN', 'NF', 'CK', 'NU', 'TK'],
                        Welt = ['MN', 'RU', 'CN', 'TW', 'JP', 'KP', 'KR', 'BD', 'BT', 'IN', 'MV', 'NP', 'PK', 'LK', 'BN', 'ID', 'KH', 'LA', 'MY', 'MM', 'PH', 'SG', 'TH', 'TL', 'VN', 'EG', 'AM', 'AZ', 'BH', 'GE', 'IQ', 'IR', 'IL', 'YE', 'JO', 'QA', 'KW', 'LB', 'OM', 'PS', 'SA', 'SY', 'TR', 'AE', 'CY', 'AF', 'KZ', 'KG', 'TJ', 'TM', 'ZU', 'AL', 'AD', 'BE', 'BA', 'BG', 'DK', 'DE', 'EE', 'FI', 'FR', 'GR', 'IE', 'IL', 'IT', 'KZ', 'HR', 'LV', 'LI', 'LT', 'LU', 'MT', 'MK', 'MD', 'MC', 'ME', 'NL', 'NO', 'AT', 'PL', 'PT', 'RO', 'RU', 'SM', 'SE', 'CH', 'RS', 'SK', 'SI', 'ES', 'CZ', 'TR', 'UA', 'HU', 'GB', 'VA', 'BY', 'FO', 'GI', 'SJ', 'SJ', 'CY', 'EG', 'DZ', 'AO', 'GQ', 'ET', 'BJ', 'BW', 'BF', 'BI', 'DJ', 'CI', 'ER', 'GA', 'GM', 'GH', 'GN', 'GW', 'CM', 'CV', 'KE', 'KM', 'CD', 'CG', 'LS', 'LR', 'LY', 'MG', 'MW', 'ML', 'MA', 'MR', 'MU', 'MZ', 'NA', 'NE', 'NG', 'RW', 'ZM', 'ST', 'SN', 'SC', 'SL', 'ZW', 'SO', 'ZA', 'SD', 'SZ', 'TZ', 'TG', 'TD', 'TN', 'UG', 'CF', 'CA', 'MX', 'US', 'BM', 'GL', 'PM', 'BZ', 'CR', 'SV', 'GT', 'HN', 'NI', 'PA', 'AR', 'BO', 'BR', 'CL', 'CO', 'EC', 'FK', 'GF', 'GY', 'PY', 'PE', 'SR', 'UY', 'VE', 'AG', 'BS', 'BB', 'CU', 'DM', 'DO', 'GD', 'HAT', 'JM', 'KN', 'LC', 'VC', 'TT', 'AI', 'AW', 'KY', 'GP', 'MQ', 'MS', 'AN', 'PR', 'TC', 'VG', 'VI', 'AU', 'FJ', 'KI', 'MH', 'FM', 'NR', 'NZ', 'PW', 'PG', 'SB', 'WS', 'TO', 'TV', 'VU', 'AS', 'GU', 'UM', 'MP', 'PF', 'NC', 'WF', 'PN', 'NF', 'CK', 'NU', 'TK'];

                    function toggle(region) {
                        var i;
                        if (document.versandart_neu.elements['land[]']) {
                            switch (region) {
                                case 'Europa_EU':
                                    for (i = 0; i < document.versandart_neu.elements['land[]'].length; i++) {
                                        if (Europa_EU.contains(document.versandart_neu.elements['land[]'][i].value))
                                            document.versandart_neu.elements['land[]'][i].checked = true;
                                    }
                                    break;
                                case 'Europa_nichtEU':
                                    for (i = 0; i < document.versandart_neu.elements['land[]'].length; i++) {
                                        if (Europa_nichtEU.contains(document.versandart_neu.elements['land[]'][i].value))
                                            document.versandart_neu.elements['land[]'][i].checked = true;
                                    }
                                    break;
                                case 'Europa':
                                    for (i = 0; i < document.versandart_neu.elements['land[]'].length; i++) {
                                        if (Europa_EU.contains(document.versandart_neu.elements['land[]'][i].value))
                                            document.versandart_neu.elements['land[]'][i].checked = true;
                                    }
                                    break;
                                case 'Nordamerika':
                                    for (i = 0; i < document.versandart_neu.elements['land[]'].length; i++) {
                                        if (Nordamerika.contains(document.versandart_neu.elements['land[]'][i].value))
                                            document.versandart_neu.elements['land[]'][i].checked = true;
                                    }
                                    break;
                                case 'Asien':
                                    for (i = 0; i < document.versandart_neu.elements['land[]'].length; i++) {
                                        if (Asien.contains(document.versandart_neu.elements['land[]'][i].value))
                                            document.versandart_neu.elements['land[]'][i].checked = true;
                                    }
                                    break;
                                case 'allesAus':
                                    for (i = 0; i < document.versandart_neu.elements['land[]'].length; i++)
                                        document.versandart_neu.elements['land[]'][i].checked = false;
                                    break;
                                case 'allesAn':
                                    for (i = 0; i < document.versandart_neu.elements['land[]'].length; i++)
                                        document.versandart_neu.elements['land[]'][i].checked = true;
                                    break;
                            }
                        }
                    }
                    //-->
                </script>
            {/literal}
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#shipToCountries#}</h3>
                </div>
                <div class="panel-body">
                    <div class="btn-group">
                        <a onclick="toggle('Europa_EU');" class="btn btn-default">{#checkEU#}</a>
                        <a onclick="toggle('Europa_nichtEU');" class="btn btn-default">{#checkNonEU#}</a>
                        <a onclick="toggle('Europa');" class="btn btn-default">{#checkEurope#}</a>
                        <a onclick="toggle('Nordamerika');" class="btn btn-default">{#checkNA#}</a>
                        <a onclick="toggle('Asien');" class="btn btn-default">{#checkAsia#}</a>
                        <a onclick="toggle('allesAus');" class="btn btn-danger">{#checkAllOff#}</a>
                        <a onclick="toggle('allesAn');" class="btn btn-primary">{#checkAllOn#}</a>
                    </div>
                    <table class="table" style="margin-top: 10px;">
                        <tbody>
                        <tr>
                            {foreach name=versandlaender from=$versandlaender item=versandland}
                            {if $smarty.foreach.versandlaender.index%3==0}
                            <td style="height:0;border:0 none;" colspan="4"></td>
                        </tr>
                        <tr>
                            {/if}
                            <td>
                                <input type="checkbox" name="land[]" id="country_{$versandland->cISO}" value="{$versandland->cISO}" {if isset($gewaehlteLaender) && is_array($gewaehlteLaender) && in_array($versandland->cISO,$gewaehlteLaender)} checked="checked"{/if} />
                                <label for="country_{$versandland->cISO}">{$versandland->cName}</label>
                            </td>
                            {/foreach}
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="save_wrapper">
                <button type="submit" value="{if !isset($Versandart->kVersandart) || !$Versandart->kVersandart}{#createShippingType#}{else}{#modifyedShippingType#}{/if}"
                        class="btn btn-primary">
                    {if !isset($Versandart->kVersandart) || !$Versandart->kVersandart}
                        <i class="fa fa-share"></i> {#createShippingType#}
                    {else}
                        <i class="fa fa-edit"></i> {#modifyedShippingType#}
                    {/if}
                </button>
            </div>
        </div>
    </form>
</div><!-- #content -->