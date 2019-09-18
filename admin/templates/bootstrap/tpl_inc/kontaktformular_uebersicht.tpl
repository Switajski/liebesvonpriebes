{include file='tpl_inc/seite_header.tpl' cTitel=#configureContactform# cBeschreibung=#contanctformDesc# cDokuURL=#cURL#}
<div id="content" class="container-fluid">
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($cTab) || $cTab === 'config'} active{/if}">
            <a data-toggle="tab" role="tab" href="#config">{#config#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'subjects'} active{/if}">
            <a data-toggle="tab" role="tab" href="#subjects">{#subjects#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'content'} active{/if}">
            <a data-toggle="tab" role="tab" href="#contents">{#contents#}</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="config" class="tab-pane fade {if !isset($cTab) || $cTab === 'config'} active in{/if}">
            <form name="einstellen" method="post" action="kontaktformular.php">
                {$jtl_token}
                <input type="hidden" name="einstellungen" value="1" />
                <div class="settings panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{#settings#}</h3>
                    </div>
                    <div class="panel-body">
                        {foreach name=conf from=$Conf item=cnf}
                            {if $cnf->cConf === 'Y'}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="{$cnf->cWertName}">{$cnf->cName}</label>
                                    </span>
                                    {if $cnf->cInputTyp === 'selectbox'}
                                        <span class="input-group-wrap">
                                            <select name="{$cnf->cWertName}" id="{$cnf->cWertName}" class="form-control combo">
                                                {foreach name=selectfor from=$cnf->ConfWerte item=wert}
                                                    <option value="{$wert->cWert}" {if $cnf->gesetzterWert==$wert->cWert}selected{/if}>{$wert->cName}</option>
                                                {/foreach}
                                            </select>
                                        </span>
                                    {else}
                                        <input class="form-control" type="text" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{$cnf->gesetzterWert}" tabindex="1" />
                                    {/if}
                                    {if isset($cnf->cBeschreibung)}
                                        <span class="input-group-addon">{getHelpDesc cDesc=$cnf->cBeschreibung}</span>
                                    {/if}
                                </div>
                            {/if}
                        {/foreach}
                    </div>
                    <div class="panel-footer">
                        <button type="submit" value="{#save#}" class="btn btn-primary"><i class="fa fa-save"></i> {#save#}</button>
                    </div>
                </div>
            </form>
        </div>
        <div id="subjects" class="tab-pane fade {if isset($cTab) && $cTab === 'subjects'} active in{/if}">
            <div class="alert alert-info">{#contanctformSubjectDesc#}</div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#subjects#}</h3>
                </div>
                <div class="table-responsive">
                    <table class="list table">
                        <thead>
                        <tr>
                            <th class="tleft">{#subject#}</th>
                            <th class="tleft">{#mail#}</th>
                            <th>{#custgrp#}</th>
                            <th>Aktionen</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach name=betreffs from=$Betreffs item=Betreff}
                            <tr>
                                <td class="TD1">
                                    <a href="kontaktformular.php?kKontaktBetreff={$Betreff->kKontaktBetreff}&token={$smarty.session.jtl_token}">{$Betreff->cName}</a>
                                </td>
                                <td class="TD2">{$Betreff->cMail}</td>
                                <td class="tcenter">{$Betreff->Kundengruppen}</td>
                                <td class="tcenter">
                                    <span class="btn-group">
                                        <a href="kontaktformular.php?kKontaktBetreff={$Betreff->kKontaktBetreff}&token={$smarty.session.jtl_token}"
                                           class="btn btn-default" title="{#modify#}"><i class="fa fa-edit"></i>
                                        </a>
                                        <a href="kontaktformular.php?del={$Betreff->kKontaktBetreff}&token={$smarty.session.jtl_token}" class="btn btn-danger" title="{#delete#}"><i class="fa fa-trash"></i></a>
                                    </span>
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
                <div class="panel-footer">
                    <a class="btn btn-primary" href="kontaktformular.php?neu=1&token={$smarty.session.jtl_token}"><i class="fa fa-share"></i> {#newSubject#}</a>
                </div>
            </div>
        </div>
        <div id="contents" class="tab-pane fade {if isset($cTab) && $cTab === 'content'} active in{/if}">
            <form name="einstellen" method="post" action="kontaktformular.php">
                {$jtl_token}
                <input type="hidden" name="content" value="1" />
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{#contents#}</h3>
                    </div>
                    <div class="panel-body">
                        <div class="settings">
                            {foreach name=sprachen from=$sprachen item=sprache}
                                {assign var="cISOcat" value=$sprache->cISO|cat:"_titel"}
                                {assign var="cISO" value=$sprache->cISO}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="cTitle_{$cISO}">{#title#} ({$sprache->cNameDeutsch})</label>
                                    </span>
                                    <span class="input-group-wrap">
                                        <input class="form-control" type="text" name="cTitle_{$cISO}" id="cTitle_{$cISO}" value="{if !empty($Content[$cISOcat])}{$Content[$cISOcat]}{/if}" tabindex="1" />
                                    </span>
                                </div>
                            {/foreach}
                            {foreach name=sprachen from=$sprachen item=sprache}
                                {assign var="cISOcat" value=$sprache->cISO|cat:"_oben"}
                                {assign var="cISO" value=$sprache->cISO}
                                <div class="category">{#topContent#} ({$sprache->cNameDeutsch})</div>
                                <textarea class="ckeditor form-control" name="cContentTop_{$cISO}" id="cContentTop_{$cISO}">{if !empty($Content[$cISOcat])}{$Content[$cISOcat]}{/if}</textarea>
                            {/foreach}
                            {foreach name=sprachen from=$sprachen item=sprache}
                                {assign var="cISOcat" value=$sprache->cISO|cat:"_unten"}
                                {assign var="cISO" value=$sprache->cISO}
                                <div class="category">{#bottomContent#} ({$sprache->cNameDeutsch})</div>
                                <textarea class="ckeditor form-control" name="cContentBottom_{$cISO}" id="cContentBottom_{$cISO}">{if !empty($Content[$cISOcat])}{$Content[$cISOcat]}{/if}</textarea>
                            {/foreach}
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {#save#}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>