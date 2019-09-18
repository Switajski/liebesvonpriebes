{if !$Exportformat->kExportformat}
    {include file='tpl_inc/seite_header.tpl' cTitel=#newExportformat# cDokuURL=#yategoURL#}
{else}
    {include file='tpl_inc/seite_header.tpl' cTitel=#modifyExportformat# cDokuURL=#yategoURL#}
{/if}
<div id="content" class="container-fluid">
    {if !$bYategoSchreibbar}
        <div class="alert alert-danger">
            <p><i class="fa fa-warning"></i> Das Verzeichnis "{$PFAD_EXPORT_YATEGO}" ist nicht beschreibbar. Bitte pr&uuml;fen Sie Ihre Schreibrechte!</p>
        </div>
    {/if}

    {if $bWaehrungsCheck}
        <form name="wxportformat_erstellen" method="post" action="yatego.export.php">
            {$jtl_token}
            <input type="hidden" name="yatego" value="1" />
            <input type="hidden" name="kExportformat" value="{$Exportformat->kExportformat}" />
            <div class="settings">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="tab{if !isset($cTab) || $cTab === 'export'} active{/if}">
                        <a data-toggle="tab" role="tab" href="#export">Export</a>
                    </li>
                    <li class="tab{if isset($cTab) && $cTab === 'settings'} active{/if}">
                        <a data-toggle="tab" role="tab" href="#settings">Einstellungen</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div id="export" class="tab-pane fade {if !isset($cTab) || $cTab === 'export'} active in{/if}">
                        <div class="panel panel-default export">
                            <div class="panel-heading">
                                <h3 class="panel-title">Export</h3>
                            </div>
                            <div class="panel-body">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="cName">{#name#}</label>
                                    </span>
                                    <input class="form-control" type="text" name="cName" id="cName" value="{$Exportformat->cName}" tabindex="1" />
                                </div>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="kSprache">{#language#}</label>
                                    </span>
                                    <span class="input-group-wrap">
                                        <select name="kSprache" id="kSprache" class="form-control combo">
                                            {foreach name=sprache from=$oSprachen item=sprache}
                                                <option value="{$sprache->kSprache}" {if $Exportformat->kSprache==$sprache->kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
                                            {/foreach}
                                        </select>
                                    </span>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="kKampagne">{#campaigns#}</label>
                                    </span>
                                    <span class="input-group-wrap">
                                        <select name="kKampagne" id="kKampagne" class="form-control combo">
                                            <option value="0"></option>
                                            {foreach name=kampagnen from=$oKampagne_arr item=oKampagne}
                                                <option value="{$oKampagne->kKampagne}" {if $Exportformat->kKampagne == $oKampagne->kKampagne}selected{/if}>{$oKampagne->cName}</option>
                                            {/foreach}
                                        </select>
                                    </span>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="kKundengruppe">{#customerGroup#}</label>
                                    </span>
                                    <span class="input-group-wrap">
                                        <select name="kKundengruppe" id="kKundengruppe" class="form-control combo">
                                            {foreach name=kdgrp from=$kundengruppen item=kdgrp}
                                                <option value="{$kdgrp->kKundengruppe}" {if $Exportformat->kKundengruppe==$kdgrp->kKundengruppe}selected{/if}>{$kdgrp->cName}</option>
                                            {/foreach}
                                        </select>
                                    </span>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="cKodierung">{#encoding#}</label>
                                    </span>
                                    <span class="input-group-wrap">
                                        <select name="cKodierung" id="cKodierung" class="form-control combo">
                                            <option value="ASCII" {if $Exportformat->cKodierung === 'ASCII'}selected{/if}>ASCII</option>
                                            <option value="UTF-8" {if $Exportformat->cKodierung === 'UTF-8'}selected{/if}>UTF-8</option>
                                        </select>
                                    </span>
                                </div>
                            </div>
                            <div class="panel-footer">
                                <div class="btn-group">
                                    {if $bYategoSchreibbar}
                                        <button name="expotieresubmit" type="submit" value="{#createExportFile#}" class="btn btn-primary">{#createExportFile#}</button>
                                    {/if}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="settings" class="tab-pane fade {if isset($cTab) && $cTab === 'settings'} active in{/if}">
                        <div class="panel panel-default settings">
                            <div class="panel-heading">
                                <h3 class="panel-title">{#settings#}</h3>
                            </div>
                            <div class="panel-body">
                                {foreach name=conf from=$oConfig_arr item=cnf}
                                    {if $cnf->cConf === 'Y'}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <label for="{$cnf->cWertName}">{$cnf->cName}</label>
                                            </span>
                                            <span class="input-group-wrap">
                                                {if $cnf->cInputTyp === 'selectbox'}
                                                    <select name="{$cnf->cWertName}" id="{$cnf->cWertName}" class="form-control combo">
                                                        {foreach name=selectfor from=$cnf->ConfWerte item=wert}
                                                            <option value="{$wert->cWert}" {if $cnf->gesetzterWert==$wert->cWert}selected{/if}>{$wert->cName}</option>
                                                        {/foreach}
                                                    </select>
                                                {else}
                                                    <input class="form-control" type="text" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{$cnf->gesetzterWert}" tabindex="3" />
                                                {/if}
                                            </span>
                                            {if $cnf->cBeschreibung}
                                                <span class="input-group-addon">{getHelpDesc cDesc=$cnf->cBeschreibung cID=$cnf->kEinstellungenConf}</span>
                                            {/if}
                                        </div>
                                    {else}
                                        <h3 style="text-align:center;">{$cnf->cName}</h3>
                                    {/if}
                                {/foreach}
                            </div>
                            <div class="panel-footer">
                                <div class="btn-group">
                                    <button name="einstellungensubmit" type="submit" value="{#saveSettings#}" class="btn btn-primary"><i class="fa fa-save"></i>&nbsp;{#saveSettings#}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    {else}
        <div class="alert alert-danger">
            <p><i class="fa fa-warning"></i> Sie ben&ouml;tigen die W&auml;hrung <strong>EUR</strong> damit der Yategoexport funktioniert. Bitte pr&uuml;fen Sie in der JTL-Wawi Ihre W&auml;hrungen!</p>
        </div>
    {/if}
</div>