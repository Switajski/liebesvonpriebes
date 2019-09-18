{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: checkbox.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}

{include file='tpl_inc/header.tpl'}

{config_load file="$lang.conf" section="checkbox"}

<script type="text/javascript">
{literal}
function aenderAnzeigeLinks(bShow)
{
    if(bShow)
    {
        document.getElementById("InterneLinks").style.display = "block";
        document.getElementById("InterneLinks").disabled = false;
    }
    else
    {
        document.getElementById("InterneLinks").style.display = "none";
        document.getElementById("InterneLinks").disabled = true;
    }
}
    
function checkFunctionDependency()
{    
    var elemOrt = document.getElementById("cAnzeigeOrt");
    var elemSF = document.getElementById("kCheckBoxFunktion");
        
    if(elemSF.options[elemSF.selectedIndex].value == 1)
        elemOrt.options[2].disabled = true;
    else if(elemSF.options[elemSF.selectedIndex].value != 1)
        elemOrt.options[2].disabled = false;
        
    if(elemOrt.options[elemOrt.selectedIndex].value == 3)
        elemSF.options[2].disabled = true;
    else if(elemOrt.options[elemOrt.selectedIndex].value != 3)
        elemSF.options[2].disabled = false;  
}
{/literal}
</script>

<script type="text/javascript" src="{$PFAD_CKEDITOR}ckeditor.js"></script>
<script type="text/javascript" src="{$PFAD_TINYMCE}jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="templates/default/js/checkAllMSG.js"></script>

{include file="tpl_inc/seite_header.tpl" cTitel=#checkbox# cBeschreibung=#checkboxDesc# cDokuURL=#checkboxURL#}
<div id="content">
	
{if isset($cHinweis) && $cHinweis|count_characters > 0}
    <p class="box_success">{$cHinweis}</p>
{/if}
{if isset($cFehler) && $cFehler|count_characters > 0}
    <p class="box_error">{$cFehler}</p>
{/if}

    <div class="container">
		
        <div class="tabber">
				 
             <div class="tabbertab{if isset($cTab) && $cTab == 'uebersicht'} tabbertabdefault{/if}">

                  <br />
                  <h2>{#checkboxOverview#}</h2>

                  <div>
                    <form method="POST" action="checkbox.php">
                        <input name="tab" type="hidden" value="erstellen">
                        <input name="erstellenShowButton" type="submit" class="button orange" value="neue Checkbox erstellen">
                    </form>
                </div>
                  
                  <br />
                  
                  <!-- �bersicht Checkbox -->
                  {if isset($oCheckBox_arr) && $oCheckBox_arr|@count > 0}
                  
                    
                  
                        {if $oBlaetterNavi->nAktiv == 1}
                            <div class="block clearall">
                                 <div class="left">
                                        <div class="pages tright">
                                             <span class="pageinfo">{#page#}: <strong>{$oBlaetterNavi->nVon}</strong> - {$oBlaetterNavi->nBis} {#from#} {$oBlaetterNavi->nAnzahl}</span>
                                             <a class="back" href="checkbox.php?s1={$oBlaetterNavi->nVoherige}&tab=uebersicht">&laquo;</a>
                                             {if $oBlaetterNavi->nAnfang != 0}<a href="checkbox.php?s1={$oBlaetterNavi->nAnfang}&tab=uebersicht">{$oBlaetterNavi->nAnfang}</a> ... {/if}
                                                  {foreach name=blaetternavi from=$oBlaetterNavi->nBlaetterAnzahl_arr item=Blatt}
                                                        <a class="page {if $oBlaetterNavi->nAktuelleSeite == $Blatt}active{/if}" href="checkbox.php?s1={$Blatt}&tab=uebersicht">{$Blatt}</a>
                                                  {/foreach}

                                             {if $oBlaetterNavi->nEnde != 0}
                                                  ... <a class="page" href="checkbox.php?s1={$oBlaetterNavi->nEnde}&tab=uebersicht">{$oBlaetterNavi->nEnde}</a>
                                             {/if}
                                             <a class="next" href="checkbox.php?s1={$oBlaetterNavi->nNaechste}&tab=uebersicht">&raquo;</a>
                                        </div>
                                 </div>
                            </div>
						 {/if}

                         <br />
                  
                        <div id="payment">                            
                             <div id="tabellenLivesuche">
                                  <form name="uebersichtForm" method="POST" action="checkbox.php">
                                  <input type="hidden" name="{$session_name}" value="{$session_id}" />
                                  <input type="hidden" name="uebersicht" value="1" />
                                  <input type="hidden" name="tab" value="uebersicht" />

                                  <table>
                                        <tr>
                                             <th class="th-1">&nbsp;</th>
                                             <th class="th-1">{#checkboxName#}</th>
                                             <th class="th-2">{#checkboxLink#}</th>
                                             <th class="th-3">{#checkboxLocation#}</th>
                                             <th class="th-4">{#checkboxFunction#}</th>
                                             <th class="th-4">{#checkboxRequired#}</th>                                             
                                             <th class="th-5">{#checkboxActive#}</th>
                                             <th class="th-5">{#checkboxLogging#}</th>
                                             <th class="th-6">{#checkboxSort#}</th>
                                             <th class="th-7">{#checkboxGroup#}</th>
                                             <th class="th-8">{#checkboxDate#}</th>
                                             <th class="th-9">&nbsp;</th>
                                        </tr>
                                  {foreach name=checkboxen from=$oCheckBox_arr item=oCheckBoxUebersicht}
                                        <tr class="tab_bg{$smarty.foreach.checkboxen.iteration%2}">
                                             <td class="TD1"><input name="kCheckBox[]" type="checkbox" value="{$oCheckBoxUebersicht->kCheckBox}"></td>
                                             <td class="TD1" align="center">{$oCheckBoxUebersicht->cName}</td>
                                             <td class="TD2" align="center">{if isset($oCheckBoxUebersicht->oLink->cName)}{$oCheckBoxUebersicht->oLink->cName}{/if}</td>
                                             <td class="TD3" align="center">
                                                {foreach name="anzeigeortAusgabe" from=$oCheckBoxUebersicht->kAnzeigeOrt_arr item=kAnzeigeOrt}
                                                    {$cAnzeigeOrt_arr[$kAnzeigeOrt]}{if !$smarty.foreach.anzeigeortAusgabe.last}, {/if}
                                                {/foreach}
                                             </td>
                                             <td class="TD4" align="center">{if isset($oCheckBoxUebersicht->oCheckBoxFunktion->cName)}{$oCheckBoxUebersicht->oCheckBoxFunktion->cName}{/if}</td>
                                             
                                             <td class="TD4" align="center">{if $oCheckBoxUebersicht->nPflicht}{#yes#}{else}{#no#}{/if}</td>
                                             <td class="TD5" align="center">{if $oCheckBoxUebersicht->nAktiv}{#yes#}{else}{#no#}{/if}</td>
                                             <td class="TD5" align="center">{if $oCheckBoxUebersicht->nLogging}{#yes#}{else}{#no#}{/if}</td>
                                             <td class="TD6" align="center">{$oCheckBoxUebersicht->nSort}</td>
                                             <td class="TD7" align="center">
                                                {foreach name="kundengruppe" from=$oCheckBoxUebersicht->cKundengruppeAssoc_arr item=cKundengruppeAssoc}
                                                    {$cKundengruppeAssoc}{if !$smarty.foreach.kundengruppe.last}, {/if}
                                                {/foreach}
                                             </td>
                                             <td class="TD8" align="center">{$oCheckBoxUebersicht->dErstellt_DE}</td>
                                             <td class="TD9"><a href="checkbox.php?edit={$oCheckBoxUebersicht->kCheckBox}" class="button edit">{#checkboxEdit#}</a></td>
                                        </tr>
                                  {/foreach}
                                        <tr>
                                             <td class="TD1"><input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);"></td>
                                             <td colspan="9" class="TD7">{#globalSelectAll#}</td>
                                        </tr>
                                  </table>
                                  <p class="submit"><input name="checkboxAktivierenSubmit" type="submit" class="button orange" value="{#checkboxActivate#}"> <input name="checkboxDeaktivierenSubmit" class="button orange" type="submit" value="{#checkboxDeactivate#}"> <input name="checkboxLoeschenSubmit" class="button orange" type="submit" value="{#checkboxDelete#}"></p>
                                  </form>
                             </div>
                        </div>
                  {else}
                        <br/>{#noDataAvailable#}<br/><br/>
                  {/if}

            </div>
            
            <div class="tabbertab{if isset($cTab) && $cTab == 'erstellen'} tabbertabdefault{/if}">
                
                <br />
                <h2>{#checkboxCreate#}</h2>

                <!-- Checkbox Erstellen -->
                <form method="POST" action="checkbox.php">
                    <input name="erstellen" type="hidden" value="1">
                    <input name="tab" type="hidden" value="erstellen">
                {if isset($oCheckBox->kCheckBox) && $oCheckBox->kCheckBox > 0}
                    <input name="kCheckBox" type="hidden" value="{$oCheckBox->kCheckBox}">
                {elseif isset($kCheckBox) && $kCheckBox > 0}
                    <input name="kCheckBox" type="hidden" value="{$kCheckBox}">
                {/if}    
                    
                    <div class="settings">
                        <p>
                            <label for="cName">Name: {getHelpDesc cDesc="Name der Checkbox"}</label>
                            <input name="cName" type="text"{if isset($cPlausi_arr.cName)} class="fieldfillout"{/if} value="{if isset($cPost_arr.cName)}{$cPost_arr.cName}{elseif isset($oCheckBox->cName)}{$oCheckBox->cName}{/if}">
                            {if isset($cPlausi_arr.cName)}<font class="fillout">{#FillOut#}</font>{/if}
                        </p>
                {if isset($oSprache_arr) && $oSprache_arr|@count > 0}                    
                    {foreach name="textsprache" from=$oSprache_arr item=oSprache}
                        {assign var=cISO value=$oSprache->cISO}
                        {assign var=kSprache value=$oSprache->kSprache}
                        {assign var=cISOText value="cText_$cISO"}
                        <p>
                            <label for="cText_{$oSprache->cISO}">Text ({$oSprache->cNameDeutsch}): {getHelpDesc cDesc="Welcher Text soll hinter der Checkbox stehen?"}</label> 
                            <textarea class="{if isset($cPlausi_arr.cText)}fieldfillout{else}field{/if}" name="cText_{$oSprache->cISO}">{if isset($cPost_arr.$cISOText)}{$cPost_arr.$cISOText}{elseif isset($oCheckBox->oCheckBoxSprache_arr[$kSprache]->cText)}{$oCheckBox->oCheckBoxSprache_arr[$kSprache]->cText}{/if}</textarea>
                            {if isset($cPlausi_arr.cText)}<font class="fillout">{#FillOut#}</font>{/if}
                        </p>
                    {/foreach}
                        
                    {foreach name="beschreibungsprache" from=$oSprache_arr item=oSprache}
                        {assign var=cISO value=$oSprache->cISO}
                        {assign var=kSprache value=$oSprache->kSprache}
                        {assign var=cISOBeschreibung value="cBeschreibung_$cISO"}
                        <p>
                            <label for="cBeschreibung_{$oSprache->cISO}">Beschreibung ({$oSprache->cNameDeutsch}): {getHelpDesc cDesc="Soll die Checkbox eine Beschreibung erhalten?"}</label> 
                            <textarea class="{if isset($cPlausi_arr.cBeschreibung)}fieldfillout{else}field{/if}" name="cBeschreibung_{$oSprache->cISO}">{if isset($cPost_arr.$cISOBeschreibung)}{$cPost_arr.$cISOBeschreibung}{elseif isset($oCheckBox->oCheckBoxSprache_arr[$kSprache]->cBeschreibung)}{$oCheckBox->oCheckBoxSprache_arr[$kSprache]->cBeschreibung}{/if}</textarea>
                            {if isset($cPlausi_arr.cBeschreibung)}<font class="fillout">{#FillOut#}</font>{/if}
                        </p>
                    {/foreach}    
                {/if}
                        
                {if isset($oLink_arr) && $oLink_arr|@count > 0}                    
                        <p>
                            <label for="kLink">Interner Link: {getHelpDesc cDesc="Interne Shop CMS Seite. Einstellbar unter Inhalt->CMS"}</label>
                            <input name="nLink" type="radio"{if isset($cPlausi_arr.kLink)} class="fieldfillout"{/if} value="-1" onClick="javascript:aenderAnzeigeLinks(false);"{if (!isset($cPlausi_arr.kLink) && !$oCheckBox->kLink) || $cPost_arr.nLink == -1} checked="checked"{/if} /> Kein Link&nbsp;&nbsp;
                            <input name="nLink" type="radio"{if isset($cPlausi_arr.kLink)} class="fieldfillout"{/if} value="1" onClick="javascript:aenderAnzeigeLinks(true);"{if $cPost_arr.nLink == 1 || $oCheckBox->kLink > 0} checked="checked"{/if} /> Interner Link
                            {if isset($cPlausi_arr.kLink)}<font class="fillout">{#FillOut#}</font>{/if}
                        </p>
                        <p id="InterneLinks" style="display: none;">
                            <label for="fake1">&nbsp;</label>
                            <select name="kLink">
                    {foreach name="links" from=$oLink_arr item=oLink}
                                <option value="{$oLink->kLink}"{if (isset($cPost_arr.kLink) && $cPost_arr.kLink == $oLink->kLink) || (isset($oCheckBox->kLink) && $oCheckBox->kLink == $oLink->kLink)} selected{/if}>{$oLink->cName}</option>
                    {/foreach}                        
                            </select>
                        </p>
                {/if}
                        
                        <p>
                            <label for="cAnzeigeOrt">Anzeigeort: {getHelpDesc cDesc="Stelle im Shopfrontend an der die Checkboxen angezeigt werden (Mehrfachauswahl mit STRG m&ouml;glich)."}</label>
                            <select id="cAnzeigeOrt" name="cAnzeigeOrt[]"{if isset($cPlausi_arr.cAnzeigeOrt)}class="fieldfillout"{/if} MULTIPLE onClick="javascript:checkFunctionDependency();">
                        {foreach name="anzeigeortarr" from=$cAnzeigeOrt_arr key=key item=cAnzeigeOrt}
                                {assign var=bAOSelect value=false}
                                {if !isset($cPost_arr.cAnzeigeOrt) && !isset($cPlausi_arr.cAnzeigeOrt) && !isset($oCheckBox->kAnzeigeOrt_arr) && $key == $CHECKBOX_ORT_REGISTRIERUNG}
                                    {assign var=bAOSelect value=true}
                                {elseif isset($oCheckBox->kAnzeigeOrt_arr) && $oCheckBox->kAnzeigeOrt_arr|@count > 0}
                                    {foreach name=boxenanzeigeort from=$oCheckBox->kAnzeigeOrt_arr item=kAnzeigeOrt}
                                        {if $key == $kAnzeigeOrt}
                                            {assign var=bAOSelect value=true}
                                        {/if}
                                    {/foreach}
                                {elseif isset($cPost_arr.cAnzeigeOrt) && $cPost_arr.cAnzeigeOrt|@count > 0}
                                    {foreach name=boxenanzeigeort from=$cPost_arr.cAnzeigeOrt item=cBoxAnzeigeOrt}
                                        {if $cBoxAnzeigeOrt == $key}
                                            {assign var=bAOSelect value=true}
                                        {/if}
                                    {/foreach}
                                {/if}
                                <option value="{$key}"{if $bAOSelect} selected{/if}>{$cAnzeigeOrt}</option>
                        {/foreach}
                            </select>
                            {if isset($cPlausi_arr.cAnzeigeOrt)}<font class="fillout">{#FillOut#}</font>{/if}
                        </p>
                        
                        <p>
                            <label for="nPflicht">Pflichtangabe: {getHelpDesc cDesc="Soll die Checkbox gepr&uuml;ft werden, ob diese aktiviert wurde?"}</label>
                            <select name="nPflicht">
                                <option value="Y"{if (isset($cPost_arr.nPflicht) && $cPost_arr.nPflicht == "Y") || (isset($oCheckBox->nPflicht) && $oCheckBox->nPflicht == 1)} selected{/if}>Ja</option>
                                <option value="N"{if (isset($cPost_arr.nPflicht) && $cPost_arr.nPflicht == "N") || (isset($oCheckBox->nPflicht) && $oCheckBox->nPflicht == 0)} selected{/if}>Nein</option>
                            </select>
                        </p>
                        
                        <p>
                            <label for="nAktiv">Aktiv: {getHelpDesc cDesc="Soll die Checkbox im Frontend aktiv und somit sichtbar sein?"}</label>
                            <select name="nAktiv">
                                <option value="Y"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv == "Y") || (isset($oCheckBox->nAktiv) && $oCheckBox->nAktiv == 1)} selected{/if}>Ja</option>
                                <option value="N"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv == "N") || (isset($oCheckBox->nAktiv) && $oCheckBox->nAktiv == 0)} selected{/if}>Nein</option>
                            </select>
                        </p>
                        
                        <p>
                            <label for="nLogging">Checkbox Logging: {getHelpDesc cDesc="Soll die Eingabe der Checkbox protokolliert werden?"}</label>
                            <select name="nLogging">
                                <option value="Y"{if (isset($cPost_arr.nLogging) && $cPost_arr.nLogging == "Y") || (isset($oCheckBox->nLogging) && $oCheckBox->nLogging == 1)} selected{/if}>Ja</option>
                                <option value="N"{if (isset($cPost_arr.nLogging) && $cPost_arr.nLogging == "N") || (isset($oCheckBox->nLogging) && $oCheckBox->nLogging == 0)} selected{/if}>Nein</option>
                            </select>
                        </p>
                        
                        <p>
                            <label for="nSort">Sortierung (h&ouml;her = weiter unten): {getHelpDesc cDesc="Anzeigereihenfolge von Checkboxen."}</label>
                            <input name="nSort" type="text"{if isset($cPlausi_arr.nSort)} class="fieldfillout"{/if} value="{if $cPost_arr.nSort}{$cPost_arr.nSort}{elseif isset($oCheckBox->nSort)}{$oCheckBox->nSort}{/if}" />
                            {if isset($cPlausi_arr.nSort)}<font class="fillout">{#FillOut#}</font>{/if}
                        </p>
                
                {if isset($oCheckBoxFunktion_arr) && $oCheckBoxFunktion_arr|@count > 0}
                        <p>
                            <label for="kCheckBoxFunktion">Spezielle Shopfunktion: {getHelpDesc cDesc="Soll die Checkbox eine Funktion ausf&uuml;hren, wenn sie aktiviert wurde?"}</label>
                            <select id="kCheckBoxFunktion" name="kCheckBoxFunktion" onClick="javascript:checkFunctionDependency();">
                                <option value="0"></option>
                            {foreach name="checkboxfunktion" from=$oCheckBoxFunktion_arr item=oCheckBoxFunktion}
                                <option value="{$oCheckBoxFunktion->kCheckBoxFunktion}"{if (isset($cPost_arr.kCheckBoxFunktion) && $cPost_arr.kCheckBoxFunktion == $oCheckBoxFunktion->kCheckBoxFunktion) || (isset($oCheckBox->kCheckBoxFunktion) && $oCheckBox->kCheckBoxFunktion == $oCheckBoxFunktion->kCheckBoxFunktion)} selected{/if}>{$oCheckBoxFunktion->cName}</option>
                            {/foreach}    
                            </select>
                        </p>
                {/if}
                
                {if isset($oKundengruppe_arr) && $oKundengruppe_arr|@count > 0}
                        <p>
                            <label for="kKundengruppe">Kundengruppe: {getHelpDesc cDesc="F&uuml;r welche Kundengruppen soll die Checkbox sichtbar sein (Mehrfachauswahl mit STRG m&ouml;glich)?"}</label>
                            <select name="kKundengruppe[]"{if isset($cPlausi_arr.kKundengruppe)}class="fieldfillout"{/if} MULTIPLE>
                    {foreach name="kundengruppen" from=$oKundengruppe_arr key=key item=oKundengruppe}
                                {assign var=bKGSelect value=false}
                                {if !isset($cPost_arr.kKundengruppe) && !isset($cPlausi_arr.kKundengruppe) && !isset($oCheckBox->kKundengruppe_arr) && $oKundengruppe->cStandard == "Y"}
                                    {assign var=bKGSelect value=true}
                                {elseif $oCheckBox->kKundengruppe_arr|@count > 0}
                                    {foreach name=boxenkundengruppe from=$oCheckBox->kKundengruppe_arr item=kKundengruppe}
                                        {if $kKundengruppe == $oKundengruppe->kKundengruppe}
                                            {assign var=bKGSelect value=true}
                                        {/if}
                                    {/foreach}
                                {elseif $cPost_arr.kKundengruppe|@count > 0}
                                    {foreach name=boxenkundengruppe from=$cPost_arr.kKundengruppe item=kKundengruppe}
                                        {if $kKundengruppe == $oKundengruppe->kKundengruppe}
                                            {assign var=bKGSelect value=true}
                                        {/if}
                                    {/foreach}
                                {/if}
                                <option value="{$oKundengruppe->kKundengruppe}"{if $bKGSelect} selected{/if}>{$oKundengruppe->cName}</option>
                    {/foreach}
                            </select>
                            {if isset($cPlausi_arr.kKundengruppe)}<font class="fillout">{#FillOut#}</font>{/if}
                        </p>
                {/if}
                        
                        <p class="submit"><input name="speichern" type="submit" value="{#save#}" class="button orange" /></p>
                    </div>
                </form>
                
            </div>
            
            {*
            <div class="tabbertab{if isset($cTab) && $cTab == 'einstellungen'} tabbertabdefault{/if}">
                
                <br />
                <h2>{#checkboxSettings#}</h2>

                <!-- �bersicht Checkbox -->
                {if isset($oNewsletterEmpfaenger_arr) && $oNewsletterEmpfaenger_arr|@count > 0}
                
                {else}
                    <br/>{#noDataAvailable#}<br/><br/>
                {/if}
                    
            </div>
            *}
                
        </div>
    </div>
</div>

{if (isset($cPost_arr.nLink) && $cPost_arr.nLink == 1) || (isset($oCheckBox->kLink) && $oCheckBox->kLink > 0)}
<script type="text/javascript">
aenderAnzeigeLinks(true);
</script>
{/if}

{include file='tpl_inc/footer.tpl'}