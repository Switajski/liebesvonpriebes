{*
-------------------------------------------------------------------------------
JTL-Shop 3
File: versandarten_neue_Versandart.tpl, smarty template inc file

page for JTL-Shop 3 
Admin

Author: JTL-Software-GmbH
http://www.jtl-software.de

Copyright (c) 2007 JTL-Software
-------------------------------------------------------------------------------
*}
<script type="text/javascript">
{assign var=addOne value=1}

{if isset($VersandartStaffeln) && $VersandartStaffeln|@count > 0}
var i = Number({$VersandartStaffeln|@count}) + 1;
{else}
var i = 2;
{/if}
function addInputRow()
{ldelim}
    $('#price_range tbody').append('<tr><td>{#upTo#}</td><td><input type="text" name="bis[]"  id="bis' + i + '" class="kilogram"></td><td>{if isset($einheit)}{$einheit}{/if} / {#priceGross#}:</td><td class="tcenter"><input type="text" name="preis[]"  id="preis' + i + '" class="price_large">{* onKeyUp="setzePreisAjax(false, \'ajaxpreisstaffel' + i + '\', this)" /> <span id="ajaxpreisstaffel' + i + '"></span>*}</td></tr>');
    i += 1;
{rdelim}

function delInputRow()
{ldelim}
    i -= 1;    
    $('#price_range tbody tr:last').remove();
{rdelim}
</script>

{assign var=cTitel value=#createShippingMethod#}
{assign var=cBeschreibung value=#createShippingMethodDesc#}

{if isset($Versandart->kVersandart) && $Versandart->kVersandart > 0}
    {assign var=cTitel value=#modifyedShippingType#}
    {assign var=cBeschreibung value=""}
{/if}

{include file="tpl_inc/seite_header.tpl" cTitel=$cTitel cBeschreibung=$cBeschreibung}
<div id="content">       

{if isset($hinweis) && $hinweis|count_characters > 0}			
	 <div class="box_success">{$hinweis}</div>
{/if}
{if isset($fehler) && $fehler|count_characters > 0}			
	 <div class="box_error">{$fehler}</div>
{/if}

<div class="container">
<form name="versandart_neu" method="post" action="versandarten.php">
<input type="hidden" name="{$session_name}" value="{$session_id}" />
<input type="hidden" name="neueVersandart" value="1" />
<input type="hidden" name="kVersandberechnung" value="{$versandberechnung->kVersandberechnung}" />
<input type="hidden" name="kVersandart" value="{if isset($Versandart->kVersandart)}{$Versandart->kVersandart}{/if}" />
<input type="hidden" name="cModulId" value="{$versandberechnung->cModulId}" />
<div class="settings">

<div class="category">Allgemein</div>
<p><label for="cName">{#shippingMethodName#}</label>
<input type="text" id="cName" name="cName" value="{if isset($Versandart->cName)}{$Versandart->cName}{/if}"  /></p>
{foreach name=sprachen from=$sprachen item=sprache}
{assign var="cISO" value=$sprache->cISO}
{if isset($oVersandartSpracheAssoc_arr[$cISO])}
	<p>
		<label for="cName_{$cISO}">{#showedName#} ({$sprache->cNameDeutsch})</label>
		<input type="text" id="cName_{$cISO}" name="cName_{$cISO}" value="{if isset($oVersandartSpracheAssoc_arr[$cISO]->cName)}{$oVersandartSpracheAssoc_arr[$cISO]->cName}{/if}" />
	</p>
{/if}
{/foreach}
<p><label for="cBild">{#pictureURL#} <img src="{$currentTemplateDir}gfx/help.png" alt="{#pictureDesc#}" title="{#pictureDesc#}" style="vertical-align:middle; cursor:help;" /></label>
<input type="text" id="cBild" name="cBild" value="{if isset($Versandart->cBild)}{$Versandart->cBild}{/if}"  /></p>
{foreach name=sprachen from=$sprachen item=sprache}
{assign var="cISO" value=$sprache->cISO}
{if isset($oVersandartSpracheAssoc_arr[$cISO])}
	<p><label for="cLieferdauer_{$cISO}">{#shippingTime#} ({$sprache->cNameDeutsch})</label>
	<input type="text" id="cLieferdauer_{$cISO}" name="cLieferdauer_{$cISO}" value="{if isset($oVersandartSpracheAssoc_arr[$cISO]->cLieferdauer)}{$oVersandartSpracheAssoc_arr[$cISO]->cLieferdauer}{/if}"  /></p>
{/if}
{/foreach}

<p>
	<label for="nSort">{#minLiefertage#}</label>
	<input type="text" id="nMinLiefertage" name="nMinLiefertage" value="{if isset($Versandart->nMinLiefertage)}{$Versandart->nMinLiefertage}{/if}" />
</p>

<p>
	<label for="nSort">{#maxLiefertage#}</label>
	<input type="text" id="nMaxLiefertage" name="nMaxLiefertage" value="{if isset($Versandart->nMaxLiefertage)}{$Versandart->nMaxLiefertage}{/if}" />
</p>

<p>
	<label for="cAnzeigen">{#showShippingMethod#}</label>
	<select name="cAnzeigen" id="cAnzeigen" class="combo">
		<option value="immer"{if isset($Versandart->cAnzeigen) && $Versandart->cAnzeigen=="immer"}selected{/if}>{#always#}</option>
		<option value="guenstigste" {if isset($Versandart->cAnzeigen) && $Versandart->cAnzeigen=="guenstigste"}selected{/if}>{#lowest#}</option>
	</select>
</p>
<p>
	<label for="cNurAbhaengigeVersandart">{#onlyForOwnShippingPrices#} <img src="{$currentTemplateDir}gfx/help.png" alt="{#ownShippingPricesDesc#}" title="{#ownShippingPricesDesc#}" style="vertical-align:middle; cursor:help;" /></label>
	<select name="cNurAbhaengigeVersandart" id="cNurAbhaengigeVersandart" class="combo">
		<option value="N" {if isset($Versandart->cNurAbhaengigeVersandart) && $Versandart->cNurAbhaengigeVersandart == "N"}selected{/if}>{#no#}</option>
		<option value="Y" {if isset($Versandart->cNurAbhaengigeVersandart) && $Versandart->cNurAbhaengigeVersandart == "Y"}selected{/if}>{#yes#}</option>
	</select>
</p>

<p>
	<label for="eSteuer">{#taxshippingcosts#} <img src="{$currentTemplateDir}gfx/help.png" alt="{#taxshippingcostsDesc#}" title="{#taxshippingcostsDesc#}" style="vertical-align:middle; cursor:help;" /></label>
	<select name="eSteuer" id="bSteuer" class="combo">
		<option value="brutto" {if isset($Versandart->eSteuer) && $Versandart->eSteuer == "brutto"}selected{/if}>{#gross#}</option>
		<option value="netto" {if isset($Versandart->eSteuer) && $Versandart->eSteuer == "netto"}selected{/if}>{#net#}</option>
	</select>
</p>

<p>
	<label for="nSort">{#sortnr#}</label>
	<input type="text" id="nSort" name="nSort" value="{if isset($Versandart->nSort)}{$Versandart->nSort}{/if}" />
</p>

<p>
	<label for="kKundengruppe">{#customerclass#} <img src="{$currentTemplateDir}gfx/help.png" alt="{#customerclassDesc#}" title="{#customerclassDesc#}" style="vertical-align:middle; cursor:help;" /></label>
	<select name="kKundengruppe[]" id="kKundengruppe" multiple="multiple" style="height:150px" class="combo">
	<option value="-1" {if $gesetzteKundengruppen.alle}selected{/if}>{#all#}</option>
	{foreach name=kundengruppen from=$kundengruppen item=oKundengruppe}
		{assign var="klasse" value=$oKundengruppe->kKundengruppe}
		<option value="{$oKundengruppe->kKundengruppe}" {if isset($gesetzteKundengruppen.$klasse) && $gesetzteKundengruppen.$klasse}selected{/if}>{$oKundengruppe->cName}</option>
	{/foreach}
	</select>
</p>

<p>
	<label for="kVersandklasse">{#shippingclass#} <img src="{$currentTemplateDir}gfx/help.png" alt="{#shippingclassDesc#}" title="{#shippingclassDesc#}" style="vertical-align:middle; cursor:help;" /></label>
	<select name="kVersandklasse[]" id="kVersandklasse" multiple="multiple" style="height:100px" class="combo">
		<option value="-1" {if isset($gesetzteVersandklassen.alle) && $gesetzteVersandklassen.alle}selected{/if}>{#all#}</option>
		{if !$versandklassenExceeded}
		{foreach name=versandklassen from=$versandklassen item=versandklasse}
		{assign var="klasse" value=$versandklasse->kVersandklasse}
		<option value="{$versandklasse->kVersandklasse}" {if $gesetzteVersandklassen.$klasse}selected{/if}>{$versandklasse->cName}</option>
		{/foreach}
		{/if}
	</select>
	<br />{if isset($versandklassenExceeded) && $versandklassenExceeded  == 1}<strong><font color="red">{#versandklassenExceeded#}</font></strong>{/if}
</p>
{foreach name=sprachen from=$sprachen item=sprache}
{assign var="cISO" value=$sprache->cISO}
{if isset($oVersandartSpracheAssoc_arr[$cISO])}
	<p><label for="cHinweistext_{$cISO}">{#shippingNote#} ({$sprache->cNameDeutsch})</label>
	<textarea id="cHinweistext_{$cISO}" class="combo" style="width: 400px; height: 80px;" name="cHinweistext_{$cISO}">{if isset($oVersandartSpracheAssoc_arr[$cISO]->cHinweistext)}{$oVersandartSpracheAssoc_arr[$cISO]->cHinweistext}{/if}</textarea></p>
{/if}
{/foreach}


<div class="category">{#acceptedPaymentMethods#}</div>
<!--<div style="padding-left: 490px;">{#gross#} ({#grossValue#})<font style="padding-left: 15px;">{#net#}</font></div><br>-->
<table class="list">
    <thead>
        <th class="check"></th>
        <th class="tleft">Zahlungsart</th>
        <th></th>
        <th>{#amount#}</th>
        <th></th>
    </thead>
    <tbody>
    {foreach name=zahlungsarten from=$zahlungsarten item=zahlungsart}
        {assign var="kZahlungsart" value=$zahlungsart->kZahlungsart}
        <tr>
            <td class="check"><input type="checkbox" id="kZahlungsart{$smarty.foreach.zahlungsarten.index}" name="kZahlungsart[]"  class="boxen" value="{$kZahlungsart}" {if isset($VersandartZahlungsarten[$kZahlungsart]->checked)}{$VersandartZahlungsarten[$kZahlungsart]->checked}{/if} /></td>
            <td>
                <label for="kZahlungsart{$smarty.foreach.zahlungsarten.index}">
                    {$zahlungsart->cName}{if isset($zahlungsart->cAnbieter) && $zahlungsart->cAnbieter|count_characters > 0} ({$zahlungsart->cAnbieter}){/if}
                </label>
            </td>
            <td>{#discount#}</td>
            <td class="tcenter"><input type="text" id="Netto_{$kZahlungsart}" name="fAufpreis_{$kZahlungsart}" value="{if isset($VersandartZahlungsarten[$kZahlungsart]->fAufpreis)}{$VersandartZahlungsarten[$kZahlungsart]->fAufpreis}{/if}" class="price_large"{* onKeyUp="setzePreisAjax(false, 'ZahlungsartAufpreis_{$zahlungsart->kZahlungsart}', this)"*} /></td>
            <td>
                <select name="cAufpreisTyp_{$kZahlungsart}" id="cAufpreisTyp_{$kZahlungsart}">
                    <option value="festpreis"{if isset($VersandartZahlungsarten[$kZahlungsart]->cAufpreisTyp) && $VersandartZahlungsarten[$kZahlungsart]->cAufpreisTyp == "festpreis"} selected{/if}>Betrag</option>
                    <option value="prozent"{if isset($VersandartZahlungsarten[$kZahlungsart]->cAufpreisTyp) && $VersandartZahlungsarten[$kZahlungsart]->cAufpreisTyp == "prozent"} selected{/if}>%</option>
                </select>
                <span id="ZahlungsartAufpreis_{$zahlungsart->kZahlungsart}" class="ZahlungsartAufpreis"></span>
            </td>
        </tr>
    {/foreach}
    </tbody>
</table>

<div class="category">{#freeShipping#}</div>
<table class="list">
    <thead>
        <tr>
            <th class="check"></th>
            <th></th>
            <th>{#amount#}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="check"><input type="checkbox" id="versandkostenfreiAktiv" name="versandkostenfreiAktiv" class="boxen" value="1" {if isset($Versandart->fVersandkostenfreiAbX) && $Versandart->fVersandkostenfreiAbX > 0}checked{/if} /></td>
            <td><label for="versandkostenfreiAktiv">{#activate#}</label></td>
            <td class="tcenter"><input type="text" id="fVersandkostenfreiAbX" name="fVersandkostenfreiAbX" class="price_large" value="{if isset($Versandart->fVersandkostenfreiAbX)}{$Versandart->fVersandkostenfreiAbX}{/if}">{* onKeyUp="setzePreisAjax(false, 'ajaxversandkostenfrei', this)" /> <span id="ajaxversandkostenfrei"></span>*}</td>
        </tr>
    </tbody>
</table>

<div class="category">{#maxCosts#}</div>
<table class="list">
    <thead>
        <th class="check"></th>
        <th></th>
        <th>{#amount#}</th>
    </thead>
    <tbody>
        <tr>
            <td class="check"><input type="checkbox" id="versanddeckelungAktiv" name="versanddeckelungAktiv"  class="boxen" value="1" {if isset($Versandart->fDeckelung) && $Versandart->fDeckelung > 0}checked{/if} /></td>
            <td><label for="versanddeckelungAktiv">{#activate#}</label></td>
            <td class="tcenter"><input type="text" id="fDeckelung" name="fDeckelung" value="{if isset($Versandart->fDeckelung)}{$Versandart->fDeckelung}{/if}" class="price_large">{* onKeyUp="setzePreisAjax(false, 'ajaxdeckelung', this)" /> <span id="ajaxdeckelung"></span>*}</td>
        </tr>
    </tbody>
</table>


{if $versandberechnung->cModulId=="vm_versandberechnung_gewicht_jtl" || $versandberechnung->cModulId=="vm_versandberechnung_warenwert_jtl" || $versandberechnung->cModulId=="vm_versandberechnung_artikelanzahl_jtl"}
    <div class="category">{#priceScale#}</div>
    <table id="price_range">
        <thead>
            <th colspan="3"></th>
            <th>{#amount#}</th>
        </thead>
        <tbody>
            
        {if isset($VersandartStaffeln) && $VersandartStaffeln|@count > 0}
            {foreach name="preisstaffel" from=$VersandartStaffeln item=oPreisstaffel} 
            {if $oPreisstaffel->fBis != 999999999}
                <tr>
                    <td>{#upTo#}</td>
                    <td><input type="text" id="bis{$smarty.foreach.preisstaffel.index}" name="bis[]" value="{if isset($VersandartStaffeln[$smarty.foreach.preisstaffel.index]->fBis)}{$VersandartStaffeln[$smarty.foreach.preisstaffel.index]->fBis}{/if}" class="kilogram" /></td>
                    <td>{$einheit} / {#amount#}:</td>
                    <td class="tcenter"><input type="text" id="preis{$smarty.foreach.preisstaffel.index}" name="preis[]" value="{if isset($VersandartStaffeln[$smarty.foreach.preisstaffel.index]->fPreis)}{$VersandartStaffeln[$smarty.foreach.preisstaffel.index]->fPreis}{/if}" class="price_large">{* onKeyUp="setzePreisAjax(false, 'ajaxpreisstaffel{$smarty.foreach.preisstaffel.index}', this)" /> <span id="ajaxpreisstaffel{$smarty.foreach.preisstaffel.index}"></span>*}</td>
                </tr>
            {/if}
            {/foreach}
        {else}
        <tr>
            <td>{#upTo#}</td>
            <td><input type="text" id="bis1" name="bis[]" value="" class="kilogram" /></td>
            <td>{$einheit} / {#amount#}:</td>
            <td class="tcenter"><input type="text" id="preis1" name="preis[]" value="" class="price_large">{* onKeyUp="setzePreisAjax(false, 'ajaxpreis1', this)" /> <span id="ajaxpreis1"></span>*}</td>
        </tr>
        {/if}
        
        </tbody>
        <tfoot>
            <td colspan="6" class="tcenter">
                <input name="addRow" type="button" value="{#addPriceScale#}" onclick="javascript:addInputRow();" class="button add" />
                <input name="delRow" type="button" value="{#delPriceScale#}" onclick="javascript:delInputRow();" class="button remove" />
            </td>
        </tfoot>
    </table>
{elseif $versandberechnung->cModulId=="vm_versandkosten_pauschale_jtl"}
    <div class="category">{#shippingPrice#}</div>
    <table class="list">
        <thead>
            <th class="check"></th>
            <th></th>
            <th>{#amount#}</th>
        </thead>
        <tbody>
            <tr>
                <td class="check"></td>
                <td></td>
                <td class="tcenter"><input type="text" id="fPreisNetto" name="fPreis" value="{if isset($Versandart->fPreis)}{$Versandart->fPreis}{/if}" class="price_large">{* onKeyUp="setzePreisAjax(false, 'ajaxfPreisNetto', this)" /> <span id="ajaxfPreisNetto"></span>*}</td>
            </tr>
        </tbody>
    </table>
{/if}

{*
{if $versandberechnung->cModulId=="vm_versandberechnung_gewicht_jtl" || $versandberechnung->cModulId=="vm_versandberechnung_warenwert_jtl" || $versandberechnung->cModulId=="vm_versandberechnung_artikelanzahl_jtl"}
<div style="padding-left: 565px;">{#amount#}<font style="padding-left: 35px;">{#net#}</font></div><br>
{section name=staffel loop=10}
<p><label for="bis{$smarty.section.staffel.index}" class="left">{#upTo#}</label>
<input type="text" id="bis{$smarty.section.staffel.index}" name="bis{$smarty.section.staffel.index}" value="{$VersandartStaffeln[$smarty.section.staffel.index]->fBis}"  tabindex="11" style="width:50px" /> {$einheit} / {#priceGross#}: <input type="text" id="preisBrutto{$smarty.section.staffel.index}" name="preisBrutto{$smarty.section.staffel.index}" value="{$VersandartStaffeln[$smarty.section.staffel.index]->fPreis}"  tabindex="11" style="width:60px" onkeyup="javascript:setzeNetto(this, 'preis{$smarty.section.staffel.index}', {$fSteuersatz});" /> <input type="text" id="preis{$smarty.section.staffel.index}" name="preis{$smarty.section.staffel.index}" value="{$VersandartStaffeln[$smarty.section.staffel.index]->fPreis}"  tabindex="11" style="width:60px" onkeyup="javascript:setzeBrutto(this, 'preisBrutto{$smarty.section.staffel.index}', {$fSteuersatz});" /> {$waehrung}</p>
{/section}
{elseif $versandberechnung->cModulId=="vm_versandkosten_pauschale_jtl"}
<div style="padding-left: 375px;">{#amount#}<font style="padding-left: 35px;">{#net#}</font></div><br>
<p><label for="fPreis">{#shippingPrice#}</label>
<input type="text" id="fPreisBrutto" name="fPreisBrutto" value="{$Versandart->fPreis}"  tabindex="12" style="width:60px" onkeyup="javascript:setzeNetto(this, 'fPreisNetto', {$fSteuersatz});" /> <input type="text" id="fPreisNetto" name="fPreis" value="{$Versandart->fPreis}"  tabindex="12" style="width:60px" onkeyup="javascript:setzeBrutto(this, 'fPreisBrutto', {$fSteuersatz});" /> {$waehrung}</p>
{/if}
</div>
*}
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

var Nordasien = new Array("MN","RU");
var Ostasien = new Array("CN","TW","JP","KP","KR");
var Suedasien = new Array("BD","BT","IN","MV","NP","PK","LK");
var Suedostasien = new Array("BN","ID","KH","LA","MY","MM","PH","SG","TH","TL","VN");
var Vorderasien = new Array("EG","AM","AZ","BH","GE","IQ","IR","IL","YE","JO","QA","KW","LB","OM","PS","SA","SY","TR","AE","CY");
var Zentralasien = new Array("AF","KZ","KG","TJ","TM","ZU");
var Asien = new Array("MN","RU","CN","TW","JP","KP","KR","BD","BT","IN","MV","NP","PK","LK","BN","ID","KH","LA","MY","MM","PH","SG","TH","TL","VN","EG","AM","AZ","BH","GE","IQ","IR","IL","YE","JO","QA","KW","LB","OM","PS","SA","SY","TR","AE","AF","KG","TJ","TM");
var Europa = new Array("AL","AD","BE","BA","BG","DK","DE","EE","FI","FR","GR","IE","IT","KZ","HR","LV","LI","LT","LU","MT","MK","MD","MC","ME","NL","NO","AT","PL","PT","RO","RU","SM","SE","CH","RS","SK","SI","ES","CZ","TR","UA","HU","GB","VA","BY","FO","GI","SJ","CY","IS","YU");
var Europa_EU = new Array("BE","BG","DK","DE","EE","FI","FR","GR","HR","IE","IT","LV","LT","LU","MT","NL","AT","PL","PT","RO","SE","SK","SI","ES","CZ","HU","GB","CY");
var Europa_nichtEU = new Array("AL","AD","BA","CH","IL","KZ","LI","MK","MD","MC","ME","NO","RU","SM","CH","RS","TR","UA","VA","BY","FO","GI","SJ","IS","YU");
var Afrika = new Array("EG","DZ","AO","GQ","ET","BJ","BW","BF","BI","DJ","CI","ER","GA","GM","GH","GN","GW","CM","CV","KE","KM","CD","CG","LS","LR","LY","MG","MW","ML","MA","MR","MU","MZ","NA","NE","NG","RW","ZM","ST","SN","SC","SL","ZW","SO","ZA","SD","SZ","TZ","TG","TD","TN","UG","CF");
var Nordamerika = new Array("CA","MX","US","BM","GL","PM");
var Mittelamerika = new Array("BZ","CR","SV","GT","HN","NI","PA");
var Suedamerika = new Array("AR","BO","BR","CL","CO","EC","FK","GF","GY","PY","PE","SR","UY","VE");
var Karibik = new Array("AG","BS","BB","CU","DM","DO","GD","HAT","JM","KN","LC","VC","TT","AI","AW","KY","GP","MQ","MS","AN","PR","TC","VG","VI");
var Ozeanien = new Array("AU","FJ","KI","MH","FM","NR","NZ","PW","PG","SB","WS","TO","TV","VU","AS","GU","UM","MP","PF","NC","WF","PN","NF","CK","NU","TK");
var Welt = new Array("MN","RU","CN","TW","JP","KP","KR","BD","BT","IN","MV","NP","PK","LK","BN","ID","KH","LA","MY","MM","PH","SG","TH","TL","VN","EG","AM","AZ","BH","GE","IQ","IR","IL","YE","JO","QA","KW","LB","OM","PS","SA","SY","TR","AE","CY","AF","KZ","KG","TJ","TM","ZU","AL","AD","BE","BA","BG","DK","DE","EE","FI","FR","GR","IE","IL","IT","KZ","HR","LV","LI","LT","LU","MT","MK","MD","MC","ME","NL","NO","AT","PL","PT","RO","RU","SM","SE","CH","RS","SK","SI","ES","CZ","TR","UA","HU","GB","VA","BY","FO","GI","SJ","SJ","CY","EG","DZ","AO","GQ","ET","BJ","BW","BF","BI","DJ","CI","ER","GA","GM","GH","GN","GW","CM","CV","KE","KM","CD","CG","LS","LR","LY","MG","MW","ML","MA","MR","MU","MZ","NA","NE","NG","RW","ZM","ST","SN","SC","SL","ZW","SO","ZA","SD","SZ","TZ","TG","TD","TN","UG","CF","CA","MX","US","BM","GL","PM","BZ","CR","SV","GT","HN","NI","PA","AR","BO","BR","CL","CO","EC","FK","GF","GY","PY","PE","SR","UY","VE","AG","BS","BB","CU","DM","DO","GD","HAT","JM","KN","LC","VC","TT","AI","AW","KY","GP","MQ","MS","AN","PR","TC","VG","VI","AU","FJ","KI","MH","FM","NR","NZ","PW","PG","SB","WS","TO","TV","VU","AS","GU","UM","MP","PF","NC","WF","PN","NF","CK","NU","TK");

function toggle(region) 
{
    if (document.versandart_neu.elements['land[]'])
    {
        switch (region)
        {
            case "Europa_EU":
                for (var i=0; i < document.versandart_neu.elements['land[]'].length; i++)
                {
                    if (Europa_EU.contains(document.versandart_neu.elements['land[]'][i].value))
                        document.versandart_neu.elements['land[]'][i].checked=true;
                }
            break;        
            case "Europa_nichtEU":
                for (var i=0; i < document.versandart_neu.elements['land[]'].length; i++)
                {
                    if (Europa_nichtEU.contains(document.versandart_neu.elements['land[]'][i].value))
                        document.versandart_neu.elements['land[]'][i].checked=true;
                }
            break;        
            case "Europa":
                for (var i=0; i < document.versandart_neu.elements['land[]'].length; i++)
                {
                    if (Europa_EU.contains(document.versandart_neu.elements['land[]'][i].value))
                        document.versandart_neu.elements['land[]'][i].checked=true;
                }
            break;        
            case "Nordamerika":
                for (var i=0; i < document.versandart_neu.elements['land[]'].length; i++)
                {
                    if (Nordamerika.contains(document.versandart_neu.elements['land[]'][i].value))
                        document.versandart_neu.elements['land[]'][i].checked=true;
                }
            break;            
            case "Asien":
                for (var i=0; i < document.versandart_neu.elements['land[]'].length; i++)
                {
                    if (Asien.contains(document.versandart_neu.elements['land[]'][i].value))
                        document.versandart_neu.elements['land[]'][i].checked=true;
                }
            break;        
            case "allesAus":
                for (var i=0; i < document.versandart_neu.elements['land[]'].length; i++)
                    document.versandart_neu.elements['land[]'][i].checked=false;
            break;        
            case "allesAn":
                for (var i=0; i < document.versandart_neu.elements['land[]'].length; i++)
                    document.versandart_neu.elements['land[]'][i].checked=true;
            break;
        }
    }
}
//-->
</script>
{/literal}

<div class="category">{#shipToCountries#}</div>
<div class="container">
    <a href="javascript:toggle('Europa_EU')" class="button">{#checkEU#}</a>
    <a href="javascript:toggle('Europa_nichtEU')" class="button">{#checkNonEU#}</a>
    <a href="javascript:toggle('Europa')" class="button">{#checkEurope#}</a>
    <a href="javascript:toggle('Nordamerika')" class="button">{#checkNA#}</a>
    <a href="javascript:toggle('Asien')" class="button">{#checkAsia#}</a>
    <a href="javascript:toggle('allesAus')" class="button remove">{#checkAllOff#}</a>
    <a href="javascript:toggle('allesAn')" class="button add">{#checkAllOn#}</a>
</div>

<table>
    <tbody>
        <tr>
        {foreach name=versandlaender from=$versandlaender item=versandland}
            {if $smarty.foreach.versandlaender.index%3==0}
            <td style="height:0;"></td>
            </tr><tr>
            {/if}
            <td>
                <input type="checkbox" name="land[]" id="country_{$versandland->cISO}" value="{$versandland->cISO}" {if isset($gewaehlteLaender) && is_array($gewaehlteLaender) && in_array($versandland->cISO,$gewaehlteLaender)} checked="checked"{/if} />
                <label for="country_{$versandland->cISO}">{$versandland->cName}</label>
            </td>
        {/foreach}
        </tr>
    </tbody>
</table>

<div class="save_wrapper">
    <input type="submit" value="{if !isset($Versandart->kVersandart) || !$Versandart->kVersandart}{#createShippingType#}{else}{#modifyedShippingType#}{/if}" class="button orange" />
</div>
</form>
</div>

{*
<script type="text/javascript">
{foreach name=zahlungsarten from=$zahlungsarten item=zahlungsart}
	if(document.getElementById('Netto_{$zahlungsart->kZahlungsart}').value > 0 && document.getElementById('cAufpreisTyp_{$zahlungsart->kZahlungsart}').options[document.getElementById('cAufpreisTyp_{$zahlungsart->kZahlungsart}').selectedIndex].value == "festpreis")
		xajax_getCurrencyConversionAjax(0, document.getElementById('Netto_{$zahlungsart->kZahlungsart}').value, 'ZahlungsartAufpreis_{$zahlungsart->kZahlungsart}');
{/foreach}
	
xajax_getCurrencyConversionAjax(0, document.getElementById('fVersandkostenfreiAbX').value, 'ajaxversandkostenfrei');
xajax_getCurrencyConversionAjax(0, document.getElementById('fDeckelung').value, 'ajaxdeckelung');

{if $versandberechnung->cModulId=="vm_versandberechnung_gewicht_jtl" || $versandberechnung->cModulId=="vm_versandberechnung_warenwert_jtl" || $versandberechnung->cModulId=="vm_versandberechnung_artikelanzahl_jtl"}
	{if $VersandartStaffeln|@count > 0}    
        {foreach name="preisstaffel" from=$VersandartStaffeln item=oPreisstaffel}
			xajax_getCurrencyConversionAjax(0, document.getElementById('preis{$smarty.foreach.preisstaffel.index}').value, 'ajaxpreisstaffel{$smarty.foreach.preisstaffel.index}');
		{/foreach}
	{else}
		xajax_getCurrencyConversionAjax(0, document.getElementById('preis1').value, 'ajaxpreis1');
	{/if}
{elseif $versandberechnung->cModulId=="vm_versandkosten_pauschale_jtl"}
		xajax_getCurrencyConversionAjax(0, document.getElementById('fPreisNetto').value, 'ajaxfPreisNetto');
{/if}
</script>
*}