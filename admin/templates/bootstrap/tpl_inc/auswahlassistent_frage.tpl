{if (isset($oFrage->kAuswahlAssistentFrage) && $oFrage->kAuswahlAssistentFrage > 0) || (isset($kAuswahlAssistentFrage) && $kAuswahlAssistentFrage > 0)}
    {assign var="cTitel" value=#auswahlassistent#|cat:' - '|cat:#aaQuestionEdit#}
{else}
    {assign var="cTitel" value=#auswahlassistent#|cat:' - '|cat:#aaQuestion#}
{/if}

{include file='tpl_inc/seite_header.tpl' cTitel=$cTitel cBeschreibung=#auswahlassistentDesc#
cDokuURL=#auswahlassistentURL#}

<div id="content">
    {if !isset($noModule) || !$noModule}
        <form class="navbar-form settings" method="post" action="auswahlassistent.php">
            {$jtl_token}
            <input name="speichern" type="hidden" value="1">
            <input name="kSprache" type="hidden" value="{$smarty.session.kSprache}">
            <input name="tab" type="hidden" value="frage">
            <input name="a" type="hidden" value="addQuest">
            {if (isset($oFrage->kAuswahlAssistentFrage) && $oFrage->kAuswahlAssistentFrage > 0) || (isset($kAuswahlAssistentFrage) && $kAuswahlAssistentFrage > 0)}
                <input class="form-control" name="kAuswahlAssistentFrage" type="hidden"
                       value="{if isset($kAuswahlAssistentFrage) && $kAuswahlAssistentFrage > 0}{$kAuswahlAssistentFrage}{else}{$oFrage->kAuswahlAssistentFrage}{/if}">
            {/if}
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="cFrage">
                                {#aaQuestionName#}
                                {if isset($cPlausi_arr.cName)}
                                    <span class="fillout">{#FillOut#}</span>
                                {/if}
                            </label>
                        </span>
                        <input id="cFrage" class="form-control{if isset($cPlausi_arr.cFrage)} fieldfillout{/if}"
                               name="cFrage" type="text"
                               value="{if isset($cPost_arr.cFrage)}{$cPost_arr.cFrage}{elseif isset($oFrage->cFrage)}{$oFrage->cFrage}{/if}">
                        <span class="input-group-addon">{getHelpDesc cDesc="Wie soll die Frage lauten?"}</span>
                    </div>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="kAuswahlAssistentGruppe">
                                Gruppe
                                {if isset($cPlausi_arr.kAuswahlAssistentGruppe)}
                                    <span class="fillout">{#FillOut#}</span>
                                {/if}
                            </label>
                        </span>
                        <span class="input-group-wrap">
                            <select id="kAuswahlAssistentGruppe" name="kAuswahlAssistentGruppe" class="form-control{if isset($cPlausi_arr.kAuswahlAssistentGruppe)} fieldfillout{/if}">
                                <option value="-1">{#aaChoose#}</option>
                                {foreach name=gruppen from=$oAuswahlAssistentGruppe_arr item=oAuswahlAssistentGruppe}
                                    <option value="{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}"
                                            {if isset($oAuswahlAssistentGruppe->kAuswahlAssistentGruppe) && ((isset($cPost_arr.kAuswahlAssistentGruppe) && $oAuswahlAssistentGruppe->kAuswahlAssistentGruppe == $cPost_arr.kAuswahlAssistentGruppe) || (isset($oFrage->kAuswahlAssistentGruppe) && $oAuswahlAssistentGruppe->kAuswahlAssistentGruppe == $oFrage->kAuswahlAssistentGruppe))} selected{/if}>{$oAuswahlAssistentGruppe->cName}</option>
                                {/foreach}
                            </select>
                        </span>
                        <span class="input-group-addon">{getHelpDesc cDesc="In welche Gruppe soll die Frage hinzugef&uuml;gt werden?"}</span>
                    </div>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="kMM">Merkmal {if isset($cPlausi_arr.kMerkmal) && $cPlausi_arr.kMerkmal == 1} <span class="fillout">{#FillOut#}</span>{/if}
                                {if isset($cPlausi_arr.kMerkmal) && $cPlausi_arr.kMerkmal == 2 }<span class="fillout">{#aaMerkmalTaken#}</span>{/if}
                            </label>
                        </span>
                        <span class="input-group-wrap">
                            <select id="kMM" name="kMerkmal" class="form-control{if isset($cPlausi_arr.kMerkmal)} fieldfillout{/if}">
                                <option value="-1">{#aaChoose#}</option>
                                {foreach name=merkmale from=$oMerkmal_arr item=oMerkmal}
                                    <option value="{$oMerkmal->kMerkmal}"{if (isset($cPost_arr.kMerkmal) && $oMerkmal->kMerkmal == $cPost_arr.kMerkmal) || (isset($oFrage->kMerkmal) && $oMerkmal->kMerkmal == $oFrage->kMerkmal)} selected{/if}>{$oMerkmal->cName}</option>
                                {/foreach}
                            </select>
                        </span>
                        <span class="input-group-addon">{getHelpDesc cDesc="Welches Merkmal soll die Frage erhalten?"}</span>
                    </div>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="nSort">
                                Sortierung
                                {if isset($cPlausi_arr.nSort)}
                                    <span class="fillout">{#FillOut#}</span>
                                {/if}
                            </label>
                        </span>
                        <input id="nSort" class="form-control{if isset($cPlausi_arr.nSort)} fieldfillout{/if}"
                               name="nSort" type="text"
                               value="{if isset($cPost_arr.nSort)}{$cPost_arr.nSort}{elseif isset($oFrage->nSort)}{$oFrage->nSort}{else}1{/if}">
                        <span class="input-group-addon">
                            {getHelpDesc cDesc="An welcher Position soll die Frage stehen? (Umso h&ouml;her desto weiter unten, z.b. 3)"}
                        </span>
                    </div>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="nAktiv">Aktiv</label>
                        </span>
                        <span class="input-group-wrap">
                            <select id="nAktiv" class="form-control" name="nAktiv">
                                <option value="1"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv == 1) || (isset($oFrage->nAktiv) && $oFrage->nAktiv == 1)} selected{/if}>
                                    Ja
                                </option>
                                <option value="0"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv == 0) || (isset($oFrage->nAktiv) && $oFrage->nAktiv == 0)} selected{/if}>
                                    Nein
                                </option>
                            </select>
                        </span>
                        <span class="input-group-addon">
                            {getHelpDesc cDesc="Soll die Frage aktiviert sein? (Aktivierte Fragen werden angezeigt)"}
                        </span>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="btn-group">
                        <button name="speichernSubmit" type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {#save#}</button>
                        <a href="auswahlassistent.php" class="btn btn-danger">{#goBack#}</a>
                    </div>
                </div>
            </div>
        </form>
    {else}
        <div class="alert alert-danger">{#noModuleAvailable#}</div>
    {/if}
</div>

{include file='tpl_inc/footer.tpl'}