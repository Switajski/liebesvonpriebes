{config_load file="$lang.conf" section="zusatzverpackung"}
{include file='tpl_inc/header.tpl'}

{include file='tpl_inc/seite_header.tpl' cTitel=#zusatzverpackung# cBeschreibung=#zusatzverpackungDesc# cDokuURL=#zusatzverpackungURL#}
<div id="content" class="container-fluid">
    {if $step === 'anzeigen'}
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{if isset($kVerpackung) && $kVerpackung > 0}{#zusatzverpackungEdit#}:{else}{#zusatzverpackungAdd#}:{/if}</h3>
            </div>
            <div class="table-responsive">
                <table class="container list table">
                    <thead>
                    <tr>
                        <th class="th-1">{#zusatzverpackungName#}</th>
                        <th class="th-2">{#zusatzverpackungISOLang#}</th>
                        <th class="th-3">{#zusatzverpackungDescLang#}</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach name=verpackungensprache from=$oVerpackungSprache_arr item=oVerpackungSprache}
                        <tr class="tab_bg{$smarty.foreach.verpackungensprache.iteration%2}">
                            <td class="TD1">{$oVerpackungSprache->cName}</td>
                            <td class="TD2">{$oVerpackungSprache->cISOSprache}</td>
                            <td class="TD3">{$oVerpackungSprache->cBeschreibung}</td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    {else}
        <form name="zusatzverpackung" method="post" action="zusatzverpackung.php">
            {$jtl_token}
            <input type="hidden" name="eintragen" value="1" />
            <input type="hidden" name="kVerpackung" value="{if isset($kVerpackung)}{$kVerpackung}{/if}" />
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{if isset($kVerpackung) && $kVerpackung > 0}{#zusatzverpackungEdit#}{else}{#zusatzverpackungAdd#}{/if}</h3>
                </div>
                <div class="table-responsive">
                    <table class="kundenfeld table">
                        {foreach name=sprachen from=$oSprache_arr item=oSprache}
                            {assign var=cISO value=$oSprache->cISO}
                            <tr>
                                <td><label for="cName_{$oSprache->cISO}">{#zusatzverpackungName#} ({$oSprache->cNameDeutsch})</label></td>
                                <td>
                                    <input class="form-control" id="cName_{$oSprache->cISO}" name="cName_{$oSprache->cISO}" type="text" value="{if isset($oVerpackungEdit->oSprach_arr[$cISO]->cName)}{$oVerpackungEdit->oSprach_arr[$cISO]->cName}{/if}">
                                </td>
                            </tr>
                        {/foreach}
                        <tr>
                            <td><label for="fBrutto">{#zusatzverpackungPrice#} ({#zusatzverpackungGross#})</label></td>
                            <td>
                                <input class="form-control" name="fBrutto" id="fBrutto" type="text" value="{if isset($oVerpackungEdit->fBrutto)}{$oVerpackungEdit->fBrutto}{/if}" onKeyUp="setzePreisAjax(false, 'WertAjax', this)" />
                                <span id="WertAjax"></span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="fMindestbestellwert">{#zusatzverpackungMinValue#} ({#zusatzverpackungGross#})</label></td>
                            <td>
                                <input class="form-control" name="fMindestbestellwert" id="fMindestbestellwert" type="text" value="{if isset($oVerpackungEdit->fMindestbestellwert)}{$oVerpackungEdit->fMindestbestellwert}{/if}" onKeyUp="setzePreisAjax(false, 'MindestWertAjax', this)" />
                                <span id="MindestWertAjax"></span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="fKostenfrei">{#zusatzverpackungExemptFromCharge#} ({#zusatzverpackungGross#})</label></td>
                            <td>
                                <input class="form-control" name="fKostenfrei" id="fKostenfrei" type="text" value="{if isset($oVerpackungEdit->fKostenfrei)}{$oVerpackungEdit->fKostenfrei}{/if}" onKeyUp="setzePreisAjax(false, 'KostenfreiAjax', this)" />
                                <span id="KostenfreiAjax"></span>
                            </td>
                        </tr>
                        {foreach name=sprachen from=$oSprache_arr item=oSprache}
                            {assign var=cISO value=$oSprache->cISO}
                            <tr>
                                <td><label for="cBeschreibung_{$cISO}">{#zusatzverpackungDescLang#} ({$oSprache->cNameDeutsch})</label></td>
                                <td>
                                    <textarea id="cBeschreibung_{$cISO}" name="cBeschreibung_{$cISO}" rows="5" cols="35" class="form-control combo">{if isset($oVerpackungEdit->oSprach_arr[$cISO]->cBeschreibung)}{$oVerpackungEdit->oSprach_arr[$cISO]->cBeschreibung}{/if}</textarea>
                                </td>
                            </tr>
                        {/foreach}
                        <tr>
                            <td><label for="kSteuerklasse">{#zusatzverpackungTaxClass#}</label></td>
                            <td>
                                <select id="kSteuerklasse" name="kSteuerklasse" class="form-control combo">
                                    <option value="-1">{#zusatzverpackungAutoTax#}</option>
                                    {foreach name=steuerklassen from=$oSteuerklasse_arr item=oSteuerklasse}
                                        <option value="{$oSteuerklasse->kSteuerklasse}" {if isset($oVerpackungEdit) && (int)$oSteuerklasse->kSteuerklasse === (int)$oVerpackungEdit->kSteuerklasse} selected{/if}>{$oSteuerklasse->cName}</option>
                                    {/foreach}
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="kKundengruppe">{#zusatzverpackungCustomerGrp#}</label></td>
                            <td>
                                <select id="kKundengruppe" name="kKundengruppe[]" multiple="multiple" class="form-control combo">
                                    <option value="-1"{if isset($oVerpackungEdit) && $oVerpackungEdit->cKundengruppe == "-1"} selected{/if}>Alle</option>
                                    {foreach name=kundengruppen from=$oKundengruppe_arr item=oKundengruppe}
                                        {if (isset($oVerpackungEdit->cKundengruppe) && $oVerpackungEdit->cKundengruppe == "-1") || !isset($oVerpackungEdit) || !$oVerpackungEdit}
                                            <option value="{$oKundengruppe->kKundengruppe}">{$oKundengruppe->cName}</option>
                                        {else}
                                            <option value="{$oKundengruppe->kKundengruppe}"{foreach name=verpackungkndgrp from=$oVerpackungEdit->kKundengruppe_arr item=kKundengruppe}{if isset($oKundengruppe->kKundengruppe) && $oKundengruppe->kKundengruppe == $kKundengruppe} selected{/if}{/foreach}>{$oKundengruppe->cName}</option>
                                        {/if}
                                    {/foreach}
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="nAktiv">{#zusatzverpackungActive#}</label></td>
                            <td>
                                <select id="nAktiv" name="nAktiv" class="form-control combo">
                                    <option value="1">Ja</option>
                                    <option value="0">Nein</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="panel-footer">
                    <button class="btn btn-primary" name="speichern" type="button" onclick="document.zusatzverpackung.submit();">
                        {if isset($kVerpackung) && $kVerpackung > 0}<i class="fa fa-save"></i> {#zusatzverpackungSave#}{else}<i class="fa fa-share"></i> {#zusatzverpackungAdd#}{/if}
                    </button>
                </div>
            </div>
        </form>

        <div class="category">{#zusatzverpackungAdded#}</div>
        {if isset($oVerpackung_arr) && $oVerpackung_arr|@count > 0}
            <form method="post" action="zusatzverpackung.php">
                {$jtl_token}
                <input type="hidden" name="bearbeiten" value="1" />
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{if isset($kVerpackung) && $kVerpackung > 0}{#zusatzverpackungEdit#}:{else}{#zusatzverpackungAdd#}:{/if}</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="list table">
                            <thead>
                            <tr>
                                <th class="th-1"></th>
                                <th class="th-2">{#zusatzverpackungName#}</th>
                                <th class="th-3">{#zusatzverpackungPrice#}</th>
                                <th class="th-4">{#zusatzverpackungMinValue#}</th>
                                <th class="th-5">{#zusatzverpackungExemptFromCharge#}</th>
                                <th class="th-6">{#zusatzverpackungCustomerGrp#}</th>
                                <th class="th-7">{#zusatzverpackungActive#}</th>
                                <th class="th-8">&nbsp;</th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach name=verpackungen from=$oVerpackung_arr item=oVerpackung}
                                <tr class="tab_bg{$smarty.foreach.verpackungen.iteration%2}">
                                    <td class="TD1">
                                        <input type="checkbox" name="kVerpackung[]" value="{$oVerpackung->kVerpackung}">
                                    </td>
                                    <td class="TD2">
                                        <a href="zusatzverpackung.php?a={$oVerpackung->kVerpackung}&token={$smarty.session.jtl_token}">{$oVerpackung->cName}</a>
                                    </td>
                                    <td class="TD3">{getCurrencyConversionSmarty fPreisBrutto=$oVerpackung->fBrutto}</td>
                                    <td class="TD4">{getCurrencyConversionSmarty fPreisBrutto=$oVerpackung->fMindestbestellwert}</td>
                                    <td class="TD5">{getCurrencyConversionSmarty fPreisBrutto=$oVerpackung->fKostenfrei}</td>
                                    <td class="TD6">
                                        {foreach name=kundengruppe from=$oVerpackung->cKundengruppe_arr item=cKundengruppe}
                                            {$cKundengruppe}{if !$smarty.foreach.kundengruppe.last},{/if}
                                        {/foreach}
                                    </td>
                                    <td class="TD7">
                                        <input name="nAktiv[]" type="checkbox" value="{$oVerpackung->kVerpackung}"{if $oVerpackung->nAktiv == 1} checked{/if}>
                                    </td>
                                    <td class="TD8">
                                        <a href="zusatzverpackung.php?edit={$oVerpackung->kVerpackung}&token={$smarty.session.jtl_token}"
                                           class="btn btn-default" title="{#modify#}"><i class="fa fa-edit"></i></a>
                                    </td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                    <div class="panel-footer">
                        <div class="btn-group">
                            <button name="loeschen" type="submit" value="{#zusatzverpackungDelete#}" class="btn btn-danger"><i class="fa fa-trash"></i> {#zusatzverpackungDelete#}</button>
                            <button name="aktualisieren" type="submit" value="{#zusatzverpackungUpdate#}" class="btn btn-default"><i class="fa fa-refresh"></i> {#zusatzverpackungUpdate#}</button>
                        </div>
                    </div>
                </div>
            </form>
        {else}
            <div class="alert alert-info">{#zusatzverpackungAddedNone#}</div>
        {/if}
    {/if}

</div>
<script type="text/javascript">
    ioCall('getCurrencyConversion', [0, $('#fBrutto').val(), 'WertAjax']);
    ioCall('getCurrencyConversion', [0, $('#fMindestbestellwert').val(), 'MindestWertAjax']);
    ioCall('getCurrencyConversion', [0, $('#fKostenfrei').val(), 'KostenfreiAjax']);
</script>

{include file='tpl_inc/footer.tpl'}