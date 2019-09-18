{if (isset($oGruppe->kAuswahlAssistentGruppe) && $oGruppe->kAuswahlAssistentGruppe > 0) || (isset($kAuswahlAssistentGruppe) && $kAuswahlAssistentGruppe > 0)}
    {assign var="cTitel" value=#auswahlassistent#|cat:' - '|cat:#aaGroupEdit#}
{else}
    {assign var="cTitel" value=#auswahlassistent#|cat:' - '|cat:#aaGroup#}
{/if}

{include file='tpl_inc/seite_header.tpl' cTitel=$cTitel cBeschreibung=#auswahlassistentDesc# cDokuURL=#auswahlassistentURL#}

<div id="content">
    {if !isset($noModule) || !$noModule}
        <form class="settings" method="post" action="auswahlassistent.php">
            {$jtl_token}
            <input name="kSprache" type="hidden" value="{$smarty.session.kSprache}">
            <input name="tab" type="hidden" value="gruppe">
            <input name="a" type="hidden" value="addGrp">
            {if (isset($oGruppe->kAuswahlAssistentGruppe) && $oGruppe->kAuswahlAssistentGruppe > 0) || (isset($kAuswahlAssistentGruppe) && $kAuswahlAssistentGruppe > 0)}
                <input class="form-control" name="kAuswahlAssistentGruppe" type="hidden"
                       value="{if isset($kAuswahlAssistentGruppe) && $kAuswahlAssistentGruppe > 0}{$kAuswahlAssistentGruppe}{else}{$oGruppe->kAuswahlAssistentGruppe}{/if}">
            {/if}
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="input-group">
                    <span class="input-group-addon">
                        <label for="cName">{#aaName#}{if isset($cPlausi_arr.cName)} <span class="fillout">{#FillOut#}</span>{/if}</label>
                    </span>
                        <input name="cName" id="cName" type="text"
                               class="form-control{if isset($cPlausi_arr.cName)} fieldfillout{/if}"
                               value="{if isset($cPost_arr.cName)}{$cPost_arr.cName}{elseif isset($oGruppe->cName)}{$oGruppe->cName}{/if}">
                        <span class="input-group-addon">{getHelpDesc cDesc="Welchen Namen soll die Gruppe erhalten?"}</span>
                    </div>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="cBeschreibung">{#aaDesc#}</label>
                        </span>
                        <textarea id="cBeschreibung" name="cBeschreibung"
                                  class="form-control description">{if isset($cPost_arr.cBeschreibung)}{$cPost_arr.cBeschreibung}{elseif isset($oGruppe->cBeschreibung)}{$oGruppe->cBeschreibung}{/if}</textarea>
                        <span class="input-group-addon">{getHelpDesc cDesc="Wie soll die Beschreibung lauten?"}</span>
                    </div>

                    {include file='tpl_inc/searchpicker_modal.tpl'
                        searchPickerName='categoryPicker'
                        modalTitle='Kategorien ausw&auml;hlen'
                        searchInputLabel='Suche Kategorien'
                    }
                    <script>
                        $(function () {
                            categoryPicker = new SearchPicker({
                                searchPickerName:  'categoryPicker',
                                getDataIoFuncName: 'getCategories',
                                keyName:           'kKategorie',
                                renderItemCb:      renderCategoryItem,
                                onApply:           onApplySelectedCategories,
                                selectedKeysInit:  $('#assign_categories_list').val().split(';').filter(function (i) { return i !== ''; })
                            });
                        });
                        function renderCategoryItem(item)
                        {
                            return '<p class="list-group-item-text">' + item.cName + '</p>';
                        }
                        function onApplySelectedCategories(selected)
                        {
                            $('#assign_categories_list').val(selected.join(';'));
                        }
                    </script>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="assign_categories_list">{#aaKat#}{if isset($cPlausi_arr.cOrt)} <span class="fillout">{#FillOut#}</span>{/if}
                                {if isset($cPlausi_arr.cKategorie) && $cPlausi_arr.cKategorie != 3} <span class="fillout">{#aaKatSyntax#}</span>{/if}
                                {if isset($cPlausi_arr.cKategorie) && $cPlausi_arr.cKategorie == 3} <span class="fillout">{#aaKatTaken#}</span>{/if}
                            </label>
                        </span>
                        <span class="input-group-wrap">
                            <input name="cKategorie" id="assign_categories_list" type="text"
                                   class="form-control{if isset($cPlausi_arr.cOrt)} fieldfillout{/if}"
                                   value="{if isset($cPost_arr.cKategorie)}{$cPost_arr.cKategorie}{elseif isset($oGruppe->cKategorie)}{$oGruppe->cKategorie}{/if}">
                        </span>
                        <span class="input-group-addon">
                            <button type="button" class="btn btn-info btn-xs" data-toggle="modal"
                                    data-target="#categoryPicker-modal"
                                    title="In welcher Kategorie soll die Gruppe angezeigt werden?">
                                <i class="fa fa-edit"></i>
                            </button>
                        </span>
                    </div>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="kLink_arr">{#aaSpecialSite#}{if isset($cPlausi_arr.cOrt)} <span class="fillout">{#FillOut#}</span>{/if}
                                {if isset($cPlausi_arr.kLink_arr)} <span class="fillout">{#aaLinkTaken#}</span>{/if}
                            </label>
                        </span>
                        <span class="input-group-wrap">
                            {if $oLink_arr|count > 0}
                                <select id="kLink_arr" name="kLink_arr[]"  class="form-control{if isset($cPlausi_arr.cOrt)} fieldfillout{/if}" multiple>
                                    {foreach name="links" from=$oLink_arr item=oLink}
                                        {assign var=bAOSelect value=false}
                                        {if isset($oGruppe->oAuswahlAssistentOrt_arr) && $oGruppe->oAuswahlAssistentOrt_arr|@count > 0}
                                            {foreach name=gruppelinks from=$oGruppe->oAuswahlAssistentOrt_arr item=oAuswahlAssistentOrt}
                                                {if $oLink->kLink == $oAuswahlAssistentOrt->kKey && $oAuswahlAssistentOrt->cKey == $AUSWAHLASSISTENT_ORT_LINK}
                                                    {assign var=bAOSelect value=true}
                                                {/if}
                                            {/foreach}
                                        {elseif isset($cPost_arr.kLink_arr) && $cPost_arr.kLink_arr|@count > 0}
                                            {foreach name=gruppelinks from=$cPost_arr.kLink_arr item=kLink}
                                                {if $kLink == $oLink->kLink}
                                                    {assign var=bAOSelect value=true}
                                                {/if}
                                            {/foreach}
                                        {/if}
                                        <option value="{$oLink->kLink}"{if $bAOSelect} selected{/if}>{$oLink->cName}</option>
                                    {/foreach}
                                </select>
                            {else}
                                <input type="text" disabled value="Keine Spezialseite &quot;Auswahlassistent&quot; vorhanden." class="form-control" />
                            {/if}
                        </span>
                        <span class="input-group-addon">
                            {getHelpDesc cDesc="Auf welcher Spezialseite soll die Gruppe angezeigt werden? (Mehrfachauswahl und Abwahl mit STRG m&ouml;glich)"}
                        </span>
                    </div>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="nStartseite">{#aaStartSite#}{if isset($cPlausi_arr.cOrt)} <span class="fillout">{#FillOut#}</span>{/if}
                                {if isset($cPlausi_arr.nStartseite)} <span class="fillout">{#aaStartseiteTaken#}</span>{/if}
                            </label>
                        </span>
                        <span class="input-group-wrap">
                            <select id="nStartseite" name="nStartseite"  class="form-control{if isset($cPlausi_arr.cOrt)} fieldfillout{/if}">
                                <option value="0"{if (isset($cPost_arr.nStartseite) && $cPost_arr.nStartseite == 0) || (isset($oGruppe->nStartseite) && $oGruppe->nStartseite == 0)} selected{/if}>
                                    Nein
                                </option>
                                <option value="1"{if (isset($cPost_arr.nStartseite) && $cPost_arr.nStartseite == 1) || (isset($oGruppe->nStartseite) && $oGruppe->nStartseite == 1)} selected{/if}>
                                    Ja
                                </option>
                            </select>
                        </span>
                        <span class="input-group-addon">{getHelpDesc cDesc="Soll die Gruppe auf der Startseite angezeigt werden? (Es darf immer nur eine Gruppe auf der Startseite aktiv sein)"}</span>
                    </div>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="nAktiv">{#aaActive#}</label>
                        </span>
                        <span class="input-group-wrap">
                            <select id="nAktiv" class="form-control" name="nAktiv">
                                <option value="1"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv == 1) || (isset($oGruppe->nAktiv) && $oGruppe->nAktiv == 1)} selected{/if}>
                                    Ja
                                </option>
                                <option value="0"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv == 0) || (isset($oGruppe->nAktiv) && $oGruppe->nAktiv == 0)} selected{/if}>
                                    Nein
                                </option>
                            </select>
                        </span>
                        <span class="input-group-addon">
                            {getHelpDesc cDesc="Soll die Checkbox im Frontend aktiv und somit sichtbar sein?"}
                        </span>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="btn-group">
                        <button name="speicherGruppe" type="submit" value="save" class="btn btn-primary"><i class="fa fa-save"></i> {#save#}</button>
                        <a href="auswahlassistent.php" class="btn btn-danger">{#goBack#}</a>
                    </div>
                </div>
            </div>
            <div id="ajax_list_picker" class="ajax_list_picker categories">{include file="tpl_inc/popup_kategoriesuche.tpl"}</div>
        </form>
    {else}
        <div class="alert alert-danger">{#noModuleAvailable#}</div>
    {/if}
</div>

{include file='tpl_inc/footer.tpl'}