{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='checkbox'}

<script type='text/javascript'>
    {literal}
    function aenderAnzeigeLinks(bShow) {
        if (bShow) {
            document.getElementById('InterneLinks').style.display = 'block';
            document.getElementById('InterneLinks').disabled = false;
        } else {
            document.getElementById('InterneLinks').style.display = 'none';
            document.getElementById('InterneLinks').disabled = true;
        }
    }

    function checkFunctionDependency() {
        var elemOrt = document.getElementById('cAnzeigeOrt'),
            elemSF = document.getElementById('kCheckBoxFunktion');

        if (elemSF.options[elemSF.selectedIndex].value == 1) {
            elemOrt.options[2].disabled = true;
        } else if (elemSF.options[elemSF.selectedIndex].value != 1) {
            elemOrt.options[2].disabled = false;
        }
        if (elemOrt.options[elemOrt.selectedIndex].value == 3) {
            elemSF.options[2].disabled = true;
        } else if (elemOrt.options[elemOrt.selectedIndex].value != 3) {
            elemSF.options[2].disabled = false;
        }
    }
    {/literal}
</script>

{include file='tpl_inc/seite_header.tpl' cTitel=#checkbox# cBeschreibung=#checkboxDesc# cDokuURL=#checkboxURL#}
<div id="content" class="container-fluid">
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($cTab) || $cTab === 'uebersicht'} active{/if}">
            <a data-toggle="tab" role="tab" href="#uebersicht">{#checkboxOverview#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'erstellen'} active{/if}">
            <a data-toggle="tab" role="tab" href="#erstellen">{#checkboxCreate#}</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="uebersicht" class="tab-pane fade {if !isset($cTab) || $cTab === 'uebersicht'} active in{/if}">
            {if isset($oCheckBox_arr) && $oCheckBox_arr|@count > 0}
                {include file='tpl_inc/pagination.tpl' oPagination=$oPagination cAnchor='uebersicht'}
                <div id="tabellenLivesuche">
                    <form name="uebersichtForm" method="post" action="checkbox.php">
                        {$jtl_token}
                        <input type="hidden" name="uebersicht" value="1" />
                        <input type="hidden" name="tab" value="uebersicht" />
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Vorhandene Checkboxen</h3>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
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
                                        <th class="th-8" colspan="2">{#checkboxDate#}</th>
                                    </tr>
                                    {foreach name=checkboxen from=$oCheckBox_arr item=oCheckBoxUebersicht}
                                        <tr class="tab_bg{$smarty.foreach.checkboxen.iteration%2}">
                                            <td class="TD1">
                                                <input name="kCheckBox[]" type="checkbox" value="{$oCheckBoxUebersicht->kCheckBox}" />
                                            </td>
                                            <td class="TD1">{$oCheckBoxUebersicht->cName}</td>
                                            <td class="TD2">{if isset($oCheckBoxUebersicht->oLink->cName)}{$oCheckBoxUebersicht->oLink->cName}{/if}</td>
                                            <td class="TD3">
                                                {foreach name="anzeigeortAusgabe" from=$oCheckBoxUebersicht->kAnzeigeOrt_arr item=kAnzeigeOrt}
                                                    {$cAnzeigeOrt_arr[$kAnzeigeOrt]}{if !$smarty.foreach.anzeigeortAusgabe.last}, {/if}
                                                {/foreach}
                                            </td>
                                            <td class="TD4">{if isset($oCheckBoxUebersicht->oCheckBoxFunktion->cName)}{$oCheckBoxUebersicht->oCheckBoxFunktion->cName}{/if}</td>

                                            <td class="TD4">{if $oCheckBoxUebersicht->nPflicht}{#yes#}{else}{#no#}{/if}</td>
                                            <td class="TD5">{if $oCheckBoxUebersicht->nAktiv}{#yes#}{else}{#no#}{/if}</td>
                                            <td class="TD5">{if $oCheckBoxUebersicht->nLogging}{#yes#}{else}{#no#}{/if}</td>
                                            <td class="TD6">{$oCheckBoxUebersicht->nSort}</td>
                                            <td class="TD7">
                                                {foreach name="kundengruppe" from=$oCheckBoxUebersicht->cKundengruppeAssoc_arr item=cKundengruppeAssoc}
                                                    {$cKundengruppeAssoc}{if !$smarty.foreach.kundengruppe.last}, {/if}
                                                {/foreach}
                                            </td>
                                            <td class="TD8">{$oCheckBoxUebersicht->dErstellt_DE}</td>
                                            <td class="TD9">
                                                <a href="checkbox.php?edit={$oCheckBoxUebersicht->kCheckBox}&token={$smarty.session.jtl_token}"
                                                   class="btn btn-default" title="{#modify#}">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    {/foreach}
                                    <tr>
                                        <td class="TD1">
                                            <input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);">
                                        </td>
                                        <td colspan="11" class="TD7"><label for="ALLMSGS">{#globalSelectAll#}</label></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="panel-footer">
                                <div class="btn-group submit">
                                    <button name="erstellenShowButton" type="submit" class="btn btn-primary" value="neue Checkbox erstellen">neue Checkbox erstellen</button>
                                    <button name="checkboxAktivierenSubmit" type="submit" class="btn btn-default" value="{#checkboxActivate#}">{#checkboxActivate#}</button>
                                    <button name="checkboxDeaktivierenSubmit" class="btn btn-warning" type="submit" value="{#checkboxDeactivate#}">{#checkboxDeactivate#}</button>
                                    <button name="checkboxLoeschenSubmit" class="btn btn-danger" type="submit" value="{#checkboxDelete#}"><i class="fa fa-trash"></i> {#checkboxDelete#}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
                <form method="post" action="checkbox.php">
                    {$jtl_token}
                    <input name="tab" type="hidden" value="erstellen" />
                    <button name="erstellenShowButton" type="submit" class="btn btn-primary" value="neue Checkbox erstellen"><i class="fa fa-share"></i> neue Checkbox erstellen</button>
                </form>
            {/if}
        </div>
        <div id="erstellen" class="tab-pane fade {if isset($cTab) && $cTab === 'erstellen'} active in{/if}">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{if isset($oCheckBox->kCheckBox) && $oCheckBox->kCheckBox > 0}{#edit#}{else}{#checkboxCreate#}{/if}</h3>
                </div>
                <div class="panel-body">
                    <form method="post" action="checkbox.php" >
                        {$jtl_token}
                        <input name="erstellen" type="hidden" value="1" />
                        <input name="tab" type="hidden" value="erstellen" />
                        {if isset($oCheckBox->kCheckBox) && $oCheckBox->kCheckBox > 0}
                            <input name="kCheckBox" type="hidden" value="{$oCheckBox->kCheckBox}" />
                        {elseif isset($kCheckBox) && $kCheckBox > 0}
                            <input name="kCheckBox" type="hidden" value="{$kCheckBox}" />
                        {/if}

                        <div class="settings">
                            <div class="input-group{if isset($cPlausi_arr.cName)} error{/if}">
                                <span class="input-group-addon">
                                    <label for="cName">Name{if isset($cPlausi_arr.cName)} <span class="fillout">{#FillOut#}</span>{/if}</label>
                                </span>
                                <input id="cName" name="cName" type="text" placeholder="Name" class="form-control{if isset($cPlausi_arr.cName)} fieldfillout{/if}" value="{if isset($cPost_arr.cName)}{$cPost_arr.cName}{elseif isset($oCheckBox->cName)}{$oCheckBox->cName}{/if}">
                                <span class="input-group-addon">{getHelpDesc cDesc="Name der Checkbox"}</span>
                            </div>
                            {if isset($oSprache_arr) && $oSprache_arr|@count > 0}
                                {foreach name="textsprache" from=$oSprache_arr item=oSprache}
                                    {assign var=cISO value=$oSprache->cISO}
                                    {assign var=kSprache value=$oSprache->kSprache}
                                    {assign var=cISOText value="cText_$cISO"}
                                    <div class="input-group{if isset($cPlausi_arr.cText)} error{/if}">
                                        <span class="input-group-addon">
                                            <label for="cText_{$oSprache->cISO}">Text ({$oSprache->cNameDeutsch}){if isset($cPlausi_arr.cText)} <span class="fillout">{#FillOut#}</span>{/if}</label>
                                        </span>
                                        <textarea id="cText_{$oSprache->cISO}" placeholder="Text ({$oSprache->cNameDeutsch})" class="form-control {if isset($cPlausi_arr.cText)}fieldfillout{else}field{/if}" name="cText_{$oSprache->cISO}">{if isset($cPost_arr.$cISOText)}{$cPost_arr.$cISOText}{elseif isset($oCheckBox->oCheckBoxSprache_arr[$kSprache]->cText)}{$oCheckBox->oCheckBoxSprache_arr[$kSprache]->cText}{/if}</textarea>
                                        <span class="input-group-addon">{getHelpDesc cDesc="Welcher Text soll hinter der Checkbox stehen?"}</span>
                                    </div>
                                {/foreach}

                                {foreach name="beschreibungsprache" from=$oSprache_arr item=oSprache}
                                    {assign var=cISO value=$oSprache->cISO}
                                    {assign var=kSprache value=$oSprache->kSprache}
                                    {assign var=cISOBeschreibung value="cBeschreibung_$cISO"}
                                    <div class="input-group{if isset($cPlausi_arr.cBeschreibung)} error{/if}">
                                        <span class="input-group-addon">
                                            <label for="cBeschreibung_{$oSprache->cISO}">Beschreibung ({$oSprache->cNameDeutsch}){if isset($cPlausi_arr.cBeschreibung)} <span class="fillout">{#FillOut#}</span>{/if}</label>
                                        </span>
                                        <textarea id="cBeschreibung_{$oSprache->cISO}" class="form-control {if isset($cPlausi_arr.cBeschreibung)}fieldfillout{else}field{/if}" name="cBeschreibung_{$oSprache->cISO}">{if isset($cPost_arr.$cISOBeschreibung)}{$cPost_arr.$cISOBeschreibung}{elseif isset($oCheckBox->oCheckBoxSprache_arr[$kSprache]->cBeschreibung)}{$oCheckBox->oCheckBoxSprache_arr[$kSprache]->cBeschreibung}{/if}</textarea>
                                        <span class="input-group-addon">{getHelpDesc cDesc="Soll die Checkbox eine Beschreibung erhalten?"}</span>
                                    </div>
                                {/foreach}
                            {/if}

                            {if isset($oLink_arr) && $oLink_arr|@count > 0}
                                <div class="input-group{if isset($cPlausi_arr.kLink)} error{/if}">
                                    <span class="input-group-addon">
                                        <label for="nLink">Interner Link{if isset($cPlausi_arr.kLink)} <span class="fillout">{#FillOut#}</span>{/if}</label>
                                    </span>
                                    <div class="input-group-wrap">
                                        <div class="form-group">
                                            <div class="col-xs-3 group-radio">
                                                <label>
                                                <input id="nLink" name="nLink" type="radio" class="{if isset($cPlausi_arr.kLink)} fieldfillout{/if}" value="-1" onClick="aenderAnzeigeLinks(false);"{if (!isset($cPlausi_arr.kLink) && (!isset($oCheckBox->kLink) || !$oCheckBox->kLink)) || isset($cPlausi_arr.kLink) && $cPost_arr.nLink == -1} checked="checked"{/if} />
                                                Kein Link
                                                </label>
                                            </div>
                                            <div class="col-xs-3 group-radio">
                                                <label>
                                                    <input id="nLink2" name="nLink" type="radio" class="form-control2{if isset($cPlausi_arr.kLink)} fieldfillout{/if}" value="1" onClick="aenderAnzeigeLinks(true);"{if (isset($cPost_arr.nLink) && $cPost_arr.nLink == 1) || (isset($oCheckBox->kLink) && $oCheckBox->kLink > 0)} checked="checked"{/if} />
                                                    Interner Link
                                                </label>
                                            </div>
                                            <div id="InterneLinks" style="display: none;" class="input-group-wrap col-xs-6">
                                                <select name="kLink" class="form-control">
                                                    {foreach name="links" from=$oLink_arr item=oLink}
                                                        <option value="{$oLink->kLink}"{if (isset($cPost_arr.kLink) && $cPost_arr.kLink == $oLink->kLink) || (isset($oCheckBox->kLink) && $oCheckBox->kLink == $oLink->kLink)} selected{/if}>{$oLink->cName}</option>
                                                    {/foreach}
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="input-group-addon">{getHelpDesc cDesc="Interne Shop CMS Seite. Einstellbar unter Inhalt->CMS"}</span>
                                </div>
                            {/if}

                            <div class="input-group{if isset($cPlausi_arr.cAnzeigeOrt)} error{/if}">
                                <span class="input-group-addon">
                                    <label for="cAnzeigeOrt">Anzeigeort{if isset($cPlausi_arr.cAnzeigeOrt)} <span class="fillout">{#FillOut#}</span>{/if}</label>
                                </span>
                                <select id="cAnzeigeOrt" name="cAnzeigeOrt[]" class="form-control{if isset($cPlausi_arr.cAnzeigeOrt)} fieldfillout{/if}" multiple onClick="checkFunctionDependency();">
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
                                        <option value="{$key}"{if $bAOSelect} selected="selected"{/if}>{$cAnzeigeOrt}</option>
                                    {/foreach}
                                </select>
                                <span class="input-group-addon">{getHelpDesc cDesc="Stelle im Shopfrontend an der die Checkboxen angezeigt werden (Mehrfachauswahl mit STRG m&ouml;glich)."}</span>
                            </div>

                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="nPflicht">Pflichtangabe:</label>
                                </span>
                                <span class="input-group-wrap">
                                    <select id="nPflicht" name="nPflicht" class="form-control">
                                        <option value="Y"{if (isset($cPost_arr.nPflicht) && $cPost_arr.nPflicht === 'Y') || (isset($oCheckBox->nPflicht) && $oCheckBox->nPflicht == 1)} selected{/if}>
                                            Ja
                                        </option>
                                        <option value="N"{if (isset($cPost_arr.nPflicht) && $cPost_arr.nPflicht === 'N') || (isset($oCheckBox->nPflicht) && $oCheckBox->nPflicht == 0)} selected{/if}>
                                            Nein
                                        </option>
                                    </select>
                                </span>
                                <span class="input-group-addon">{getHelpDesc cDesc="Soll die Checkbox gepr&uuml;ft werden, ob diese aktiviert wurde?"}</span>
                            </div>

                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="nAktiv">Aktiv:</label>
                                </span>
                                <span class="input-group-wrap">
                                    <select id="nAktiv" name="nAktiv" class="form-control">
                                        <option value="Y"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv === 'Y') || (isset($oCheckBox->nAktiv) && $oCheckBox->nAktiv == 1)} selected{/if}>
                                            Ja
                                        </option>
                                        <option value="N"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv === 'N') || (isset($oCheckBox->nAktiv) && $oCheckBox->nAktiv == 0)} selected{/if}>
                                            Nein
                                        </option>
                                    </select>
                                </span>
                                <span class="input-group-addon">{getHelpDesc cDesc="Soll die Checkbox im Frontend aktiv und somit sichtbar sein?"}</span>
                            </div>

                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="nLogging">Checkbox Logging</label>
                                </span>
                                <span class="input-group-wrap">
                                    <select id="nLogging" name="nLogging" class="form-control">
                                        <option value="Y"{if (isset($cPost_arr.nLogging) && $cPost_arr.nLogging === 'Y') || (isset($oCheckBox->nLogging) && $oCheckBox->nLogging == 1)} selected{/if}>
                                            Ja
                                        </option>
                                        <option value="N"{if (isset($cPost_arr.nLogging) && $cPost_arr.nLogging === 'N') || (isset($oCheckBox->nLogging) && $oCheckBox->nLogging == 0)} selected{/if}>
                                            Nein
                                        </option>
                                    </select>
                                </span>
                                <span class="input-group-addon">{getHelpDesc cDesc="Soll die Eingabe der Checkbox protokolliert werden?"}</span>
                            </div>

                            <div class="input-group{if isset($cPlausi_arr.nSort)} error{/if}">
                                <span class="input-group-addon">
                                    <label for="nSort">Sortierung (h&ouml;her = weiter unten){if isset($cPlausi_arr.nSort)} <span class="fillout">{#FillOut#}</span>{/if}</label>
                                </span>
                                <input id="nSort" name="nSort" type="text" class="form-control{if isset($cPlausi_arr.nSort)} fieldfillout{/if}" value="{if isset($cPost_arr.nSort)}{$cPost_arr.nSort}{elseif isset($oCheckBox->nSort)}{$oCheckBox->nSort}{/if}" />
                                <span class="input-group-addon">{getHelpDesc cDesc="Anzeigereihenfolge von Checkboxen."}</span>
                            </div>

                            {if isset($oCheckBoxFunktion_arr) && $oCheckBoxFunktion_arr|@count > 0}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="kCheckBoxFunktion">Spezielle Shopfunktion:</label>
                                    </span>
                                    <span class="input-group-wrap">
                                        <select class="form-control" id="kCheckBoxFunktion" name="kCheckBoxFunktion" onclick="checkFunctionDependency();">
                                            <option value="0"></option>
                                            {foreach name="checkboxfunktion" from=$oCheckBoxFunktion_arr item=oCheckBoxFunktion}
                                                <option value="{$oCheckBoxFunktion->kCheckBoxFunktion}"{if (isset($cPost_arr.kCheckBoxFunktion) && $cPost_arr.kCheckBoxFunktion == $oCheckBoxFunktion->kCheckBoxFunktion) || (isset($oCheckBox->kCheckBoxFunktion) && $oCheckBox->kCheckBoxFunktion == $oCheckBoxFunktion->kCheckBoxFunktion)} selected{/if}>{$oCheckBoxFunktion->cName}</option>
                                            {/foreach}
                                        </select>
                                    </span>
                                    <span class="input-group-addon">{getHelpDesc cDesc="Soll die Checkbox eine Funktion ausf&uuml;hren, wenn sie aktiviert wurde?"}</span>
                                </div>
                            {/if}

                            {if isset($oKundengruppe_arr) && $oKundengruppe_arr|@count > 0}
                                <div class="input-group{if isset($cPlausi_arr.kKundengruppe)} error{/if}">
                                    <span class="input-group-addon">
                                        <label for="kKundengruppe">Kundengruppe{if isset($cPlausi_arr.kKundengruppe)} <span class="fillout">{#FillOut#}</span>{/if}</label>
                                    </span>
                                    <select id="kKundengruppe" name="kKundengruppe[]" class="form-control{if isset($cPlausi_arr.kKundengruppe)} fieldfillout{/if}" multiple>
                                        {foreach name="kundengruppen" from=$oKundengruppe_arr key=key item=oKundengruppe}
                                            {assign var=bKGSelect value=false}
                                            {if !isset($cPost_arr.kKundengruppe) && !isset($cPlausi_arr.kKundengruppe) && !isset($oCheckBox->kKundengruppe_arr) && $oKundengruppe->cStandard === 'Y'}
                                                {assign var=bKGSelect value=true}
                                            {elseif isset($oCheckBox->kKundengruppe_arr) && $oCheckBox->kKundengruppe_arr|@count > 0}
                                                {foreach name=boxenkundengruppe from=$oCheckBox->kKundengruppe_arr item=kKundengruppe}
                                                    {if $kKundengruppe == $oKundengruppe->kKundengruppe}
                                                        {assign var=bKGSelect value=true}
                                                    {/if}
                                                {/foreach}
                                            {elseif isset($cPost_arr.kKundengruppe) && $cPost_arr.kKundengruppe|@count > 0}
                                                {foreach name=boxenkundengruppe from=$cPost_arr.kKundengruppe item=kKundengruppe}
                                                    {if $kKundengruppe == $oKundengruppe->kKundengruppe}
                                                        {assign var=bKGSelect value=true}
                                                    {/if}
                                                {/foreach}
                                            {/if}
                                            <option value="{$oKundengruppe->kKundengruppe}"{if $bKGSelect} selected{/if}>{$oKundengruppe->cName}</option>
                                        {/foreach}
                                    </select>
                                    <span class="input-group-addon">{getHelpDesc cDesc="F&uuml;r welche Kundengruppen soll die Checkbox sichtbar sein (Mehrfachauswahl mit STRG m&ouml;glich)?"}</span>
                                </div>
                            {/if}
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button name="speichern" type="submit" value="{#save#}" class="btn btn-primary"><i class="fa fa-save"></i> {#save#}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{if (isset($cPost_arr.nLink) && $cPost_arr.nLink == 1) || (isset($oCheckBox->kLink) && $oCheckBox->kLink > 0)}
    <script type="text/javascript">
        aenderAnzeigeLinks(true);
    </script>
{/if}

{include file='tpl_inc/footer.tpl'}