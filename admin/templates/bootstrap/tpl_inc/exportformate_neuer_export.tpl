{if !isset($Exportformat->kExportformat)}
    {include file='tpl_inc/seite_header.tpl' cTitel=#newExportformat#}
{else}
    {include file='tpl_inc/seite_header.tpl' cTitel=#modifyExportformat#}
{/if}
<div id="content">
    <form name="wxportformat_erstellen" method="post" action="exportformate.php">
        {$jtl_token}
        <input type="hidden" name="neu_export" value="1" />
        <input type="hidden" name="kExportformat" value="{if isset($Exportformat->kExportformat)}{$Exportformat->kExportformat}{/if}" />
        {if isset($Exportformat->bPluginContentFile) && $Exportformat->bPluginContentFile}
            <input type="hidden" name="bPluginContentFile" value="1" />
        {/if}
        {if !empty($Exportformat->kPlugin)}
            <input type="hidden" name="kPlugin" value="{$Exportformat->kPlugin}" />
        {/if}
        <div class="panel panel-default settings">
            <div class="panel-body">
                <ul class="jtl-list-group">
                    <li class="input-group{if isset($cPlausiValue_arr.cName)} error{/if}">
                        <span class="input-group-addon">
                            <label for="cName">{#name#}{if isset($cPlausiValue_arr.cName)} <span class="fillout">{#FillOut#}</span>{/if}</label>
                        </span>
                        <input class="form-control" type="text" name="cName" id="cName" value="{if isset($cPostVar_arr.cName)}{$cPostVar_arr.cName}{elseif isset($Exportformat->cName)}{$Exportformat->cName}{/if}" tabindex="1" />
                    </li>
                    <li class="input-group item">
                        <span class="input-group-addon"><label for="kSprache">{#language#}</label></span>
                        <span class="input-group-wrap">
                            <select class="form-control" name="kSprache" id="kSprache">
                                {foreach name=sprache from=$sprachen item=sprache}
                                    <option value="{$sprache->kSprache}" {if isset($Exportformat->kSprache) && $Exportformat->kSprache == $sprache->kSprache || (isset($cPlausiValue_arr.kSprache) && $cPlausiValue_arr.kSprache == $sprache->kSprache)}selected{/if}>{$sprache->cNameDeutsch}</option>
                                {/foreach}
                            </select>
                        </span>
                    </li>
                    <li class="input-group item">
                        <span class="input-group-addon"><label for="kWaehrung">{#currency#}</label></span>
                        <span class="input-group-wrap">
                            <select class="form-control" name="kWaehrung" id="kWaehrung">
                                {foreach name=waehrung from=$waehrungen item=waehrung}
                                    <option value="{$waehrung->kWaehrung}" {if isset($Exportformat->kSprache) && $Exportformat->kWaehrung == $waehrung->kWaehrung || (isset($cPlausiValue_arr.kWaehrung) && $cPlausiValue_arr.cName == $waehrung->kWaehrung)}selected{/if}>{$waehrung->cName}</option>
                                {/foreach}
                            </select>
                        </span>
                    </li>
                    <li class="input-group item">
                        <span class="input-group-addon"><label for="kKampagne">{#campaigns#}</label></span>
                        <span class="input-group-wrap">
                            <select class="form-control" name="kKampagne" id="kKampagne">
                                <option value="0"></option>
                                {foreach name=kampagnen from=$oKampagne_arr item=oKampagne}
                                    <option value="{$oKampagne->kKampagne}" {if isset($Exportformat->kSprache) && $Exportformat->kKampagne == $oKampagne->kKampagne || (isset($cPlausiValue_arr.kKampagne) && $cPlausiValue_arr.kKampagne == $oKampagne->kKampagne)}selected{/if}>{$oKampagne->cName}</option>
                                {/foreach}
                            </select>
                        </span>
                    </li>
                    <li class="input-group item">
                        <span class="input-group-addon"><label for="kKundengruppe">{#customerGroup#}</label></span>
                        <span class="input-group-wrap">
                            <select class="form-control" name="kKundengruppe" id="kKundengruppe">
                                {foreach name=kdgrp from=$kundengruppen item=kdgrp}
                                    <option value="{$kdgrp->kKundengruppe}" {if isset($Exportformat->kSprache) && $Exportformat->kKundengruppe == $kdgrp->kKundengruppe || (isset($cPlausiValue_arr.kKundengruppe) && $cPlausiValue_arr.kKundengruppe == $kdgrp->kKundengruppe)}selected{/if}>{$kdgrp->cName}</option>
                                {/foreach}
                            </select>
                        </span>
                    </li>
                    <li class="input-group item">
                        <span class="input-group-addon"><label for="cKodierung">{#encoding#}</label></span>
                        <span class="input-group-wrap">
                            <select class="form-control" name="cKodierung" id="cKodierung">
                                <option value="ASCII" {if (isset($Exportformat->cKodierung) && $Exportformat->cKodierung === 'ASCII') || (isset($cPlausiValue_arr.cKodierung) && $cPlausiValue_arr.cKodierung === 'ASCII')}selected{/if}>
                                    ASCII
                                </option>
                                <option value="UTF-8" {if (isset($Exportformat->cKodierung) && $Exportformat->cKodierung === 'UTF-8') || (isset($cPlausiValue_arr.cKodierung) && $cPlausiValue_arr.cKodierung === 'UTF-8')}selected{/if}>
                                    UTF-8 + BOM
                                </option>
                                <option value="UTF-8noBOM" {if (isset($Exportformat->cKodierung) && $Exportformat->cKodierung === 'UTF-8noBOM') || (isset($cPlausiValue_arr.cKodierung) && $cPlausiValue_arr.cKodierung === 'UTF-8noBOM')}selected{/if}>
                                    UTF-8
                                </option>
                            </select>
                        </span>
                    </li>
                    <li class="input-group item">
                        <span class="input-group-addon"><label for="nUseCache">Cache nutzen?</label></span>
                        <span class="input-group-wrap">
                            <select class="form-control" name="nUseCache" id="nUseCache">
                                <option value="1" {if (isset($Exportformat->nUseCache) && $Exportformat->nUseCache === '1')}selected{/if}>Ja</option>
                                <option value="0" {if (!isset($Exportformat->nUseCache) || $Exportformat->nUseCache === '0')}selected{/if}>Nein</option>
                            </select>
                        </span>
                    </li>

                    <li class="input-group item">
                        <span class="input-group-addon"><label for="nVarKombiOption">{#varikombiOption#}</label></span>
                        <span class="input-group-wrap">
                            <select class="form-control" name="nVarKombiOption" id="nVarKombiOption">
                                <option value="1" {if (isset($Exportformat->nVarKombiOption) && $Exportformat->nVarKombiOption == 1) || (isset($cPlausiValue_arr.nVarKombiOption) && $cPlausiValue_arr.nVarKombiOption == 1)}selected{/if}>{#varikombiOption1#}</option>
                                <option value="2" {if (isset($Exportformat->nVarKombiOption) && $Exportformat->nVarKombiOption == 2) || (isset($cPlausiValue_arr.nVarKombiOption) && $cPlausiValue_arr.nVarKombiOption == 2)}selected{/if}>{#varikombiOption2#}</option>
                                <option value="3" {if (isset($Exportformat->nVarKombiOption) && $Exportformat->nVarKombiOption == 3) || (isset($cPlausiValue_arr.nVarKombiOption) && $cPlausiValue_arr.nVarKombiOption == 3)}selected{/if}>{#varikombiOption3#}</option>
                            </select>
                        </span>
                    </li>

                    <li class="input-group item">
                        <span class="input-group-addon"><label for="nSplitgroesse">{#splitSize#}</label></span>
                        <input class="form-control" type="text" name="nSplitgroesse" id="nSplitgroesse" value="{if isset($cPostVar_arr.nSplitgroesse)}{$cPostVar_arr.nSplitgroesse}{elseif isset($Exportformat->nSplitgroesse)}{$Exportformat->nSplitgroesse}{/if}" tabindex="2" />
                    </li>

                    <li class="input-group item{if isset($cPlausiValue_arr.cDateiname)} error{/if}">
                        <span class="input-group-addon">
                            <label for="cDateiname">{#filename#}{if isset($cPlausiValue_arr.cDateiname)} <span class="fillout">{#FillOut#}</span>{/if}</label>
                        </span>
                        <input class="form-control{if isset($cPlausiValue_arr.cDateiname)} fieldfillout{/if}" type="text" name="cDateiname" id="cDateiname" value="{if isset($cPostVar_arr.cDateiname)}{$cPostVar_arr.cDateiname}{elseif isset($Exportformat->cDateiname)}{$Exportformat->cDateiname}{/if}" tabindex="2" />
                    </li>
                </ul>
                {if !isset($Exportformat->bPluginContentFile)|| !$Exportformat->bPluginContentFile}
                    <p><label for="cKopfzeile">{#header#}</label>
                        {getHelpDesc placement='right' cDesc=#onlyIfNeeded#}
                        <textarea name="cKopfzeile" id="cKopfzeile" class="codemirror smarty field">{if isset($cPostVar_arr.cKopfzeile)}{$cPostVar_arr.cKopfzeile|replace:"\t":"<tab>"}{elseif isset($Exportformat->cKopfzeile)}{$Exportformat->cKopfzeile}{/if}</textarea>
                    </p>
                    <p><label for="cContent">{#template#}</label>
                        {getHelpDesc placement='right' cDesc=#smartyRules#}
                        <textarea name="cContent" id="cContent" class="codemirror smarty field{if isset($oSmartyError)}fillout{/if}">{if isset($cPostVar_arr.cContent)}{$cPostVar_arr.cContent|replace:"\t":"<tab>"}{elseif isset($Exportformat->cContent)}{$Exportformat->cContent}{/if}</textarea>
                    </p>
                    <p><label for="cFusszeile">{#footer#}</label>
                        {getHelpDesc placement='right' cDesc=#onlyIfNeededFooter#}
                        <textarea name="cFusszeile" id="cFusszeile" class="codemirror smarty field">{if isset($cPostVar_arr.cFusszeile)}{$cPostVar_arr.cFusszeile|replace:"\t":"<tab>"}{elseif isset($Exportformat->cFusszeile)}{$Exportformat->cFusszeile}{/if}</textarea>
                    </p>
                {else}
                    <input name="cContent" type="hidden" value="{if isset($Exportformat->cContent)}{$Exportformat->cContent}{/if}" />
                {/if}
            </div>
        </div>
        <div class="panel panel-default settings">
            <div class="panel-heading">
                <h3 class="panel-title">{#settings#}</h3>
            </div>
            <div class="panel-body">
                <ul class="jtl-list-group">
                    {foreach name=conf from=$Conf item=cnf}
                        {if $cnf->cConf === 'Y'}
                            <li class="input-group">
                                <span class="input-group-addon"><label for="{$cnf->cWertName}">{$cnf->cName}</label></span>
                                {if $cnf->cInputTyp === 'selectbox'}
                                    <span class="input-group-wrap">
                                        <select class="form-control" name="{$cnf->cWertName}" id="{$cnf->cWertName}">
                                            {foreach name=selectfor from=$cnf->ConfWerte item=wert}
                                                <option value="{$wert->cWert}" {if isset($cnf->gesetzterWert) && $cnf->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                                            {/foreach}
                                        </select>
                                    </span>
                                {else}
                                    <input class="form-control" type="text" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{if isset($cnf->gesetzterWert)}{$cnf->gesetzterWert}{/if}" tabindex="3" />
                                {/if}
                                {if $cnf->cBeschreibung}
                                    <span class="input-group-addon">
                                        {getHelpDesc cDesc=$cnf->cBeschreibung}
                                    </span>
                                {/if}
                            </li>
                        {else}
                            <h3 style="text-align:center;">{$cnf->cName}</h3>
                        {/if}
                    {/foreach}
                </ul>
            </div>
        </div>
        <div class="save_wrapper">
            <button type="submit" class="btn btn-primary" value="{if !isset($Exportformat->kExportformat) || !$Exportformat->kExportformat}{#newExportformatSave#}{else}{#modifyExportformatSave#}{/if}">
                <i class="fa fa-save"></i> {if !isset($Exportformat->kExportformat) || !$Exportformat->kExportformat}{#newExportformatSave#}{else}{#modifyExportformatSave#}{/if}
            </button>
        </div>
    </form>

    {if isset($Exportformat->kExportformat)}
        {getRevisions type='export' key=$Exportformat->kExportformat show=['cContent','cKopfzeile','cFusszeile'] data=$Exportformat}
    {/if}
</div>