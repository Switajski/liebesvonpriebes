{include file='tpl_inc/seite_header.tpl' cTitel=#auswahlassistent# cBeschreibung=#auswahlassistentDesc#
         cDokuURL=#auswahlassistentURL#}

<div id="content">
    {if !isset($noModule) || !$noModule}
        <div class="block">
            <form name="sprache" method="post" action="auswahlassistent.php">
                {$jtl_token}
                <input id="{#changeLanguage#}" type="hidden" name="sprachwechsel" value="1" />
                <div class="input-group p25 left">
                <span class="input-group-addon">
                    <label for="lang-changer">{#changeLanguage#}:</strong></label>
                </span>
                    <span class="input-group-wrap last">
                    <select id="lang-changer" name="kSprache" class="form-control selectBox" onchange="document.sprache.submit();">
                        {foreach name=sprachen from=$Sprachen item=sprache}
                            <option value="{$sprache->kSprache}" {if $sprache->kSprache == $smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
                        {/foreach}
                    </select>
                </span>
                </div>
            </form>
        </div>
        <ul class="nav nav-tabs" role="tablist">
            <li class="tab{if !isset($cTab) || $cTab === 'uebersicht'} active{/if}">
                <a data-toggle="tab" role="tab" href="#overview">{#aaOverview#}</a>
            </li>
            <li class="tab{if isset($cTab) && $cTab === 'einstellungen'} active{/if}">
                <a data-toggle="tab" role="tab" href="#config">{#aaConfig#}</a>
            </li>
        </ul>
        <div class="tab-content">
            <div id="overview" class="tab-pane fade{if !isset($cTab) || $cTab === 'uebersicht'} active in{/if}">
                <form name="uebersichtForm" method="post" action="auswahlassistent.php">
                    {$jtl_token}
                    <input type="hidden" name="tab" value="uebersicht" />
                    <div class="panel panel-default">
                        {if isset($oAuswahlAssistentGruppe_arr) && $oAuswahlAssistentGruppe_arr|@count > 0}
                            <div class="table-responsive">
                                <table class="list table">
                                    <thead>
                                        <tr>
                                            <th class="tcenter"></th>
                                            <th class="check">&nbsp;</th>
                                            <th class="tleft">{#aaName#}</th>
                                            <th class="tcenter">{#aaLocation#}</th>
                                            <th class="tright">&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {foreach name=auswahlgruppen from=$oAuswahlAssistentGruppe_arr item=oAuswahlAssistentGruppe}
                                            <tr{if !$oAuswahlAssistentGruppe->nAktiv} class="text-danger"{/if}>
                                                <td>{if !$oAuswahlAssistentGruppe->nAktiv}<i class="fa fa-times"></i>{/if}</td>
                                                <td class="check">
                                                    <input name="kAuswahlAssistentGruppe_arr[]" type="checkbox"
                                                           value="{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}"
                                                           id="group-{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}"/>
                                                </td>
                                                <td class="tleft">
                                                    <label for="group-{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}">
                                                        {$oAuswahlAssistentGruppe->cName}
                                                    </label>
                                                </td>
                                                <td class="tcenter">
                                                    {foreach name=anzeigeort from=$oAuswahlAssistentGruppe->oAuswahlAssistentOrt_arr item=oAuswahlAssistentOrt}
                                                        {$oAuswahlAssistentOrt->cOrt}{if !$smarty.foreach.anzeigeort.last}, {/if}
                                                    {/foreach}
                                                </td>
                                                <td class="tright" width="265">
                                                    {if isset($oAuswahlAssistentGruppe->oAuswahlAssistentFrage_arr) && $oAuswahlAssistentGruppe->oAuswahlAssistentFrage_arr|@count > 0}
                                                        <div class="btn-group">
                                                            <a class="btn btn-default button down"
                                                               id="btn_toggle_{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}"
                                                               title="Fragen anzeigen">
                                                                <i class="fa fa-question-circle-o"></i>
                                                            </a>
                                                    {else}
                                                        <div>
                                                    {/if}
                                                            <a href="auswahlassistent.php?a=editGrp&g={$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}&token={$smarty.session.jtl_token}"
                                                               class="btn btn-default edit" title="{#modify#}">
                                                                <i class="fa fa-edit"></i>
                                                            </a>
                                                        </div>
                                                </td>
                                            </tr>
                                            {if isset($oAuswahlAssistentGruppe->oAuswahlAssistentFrage_arr) && $oAuswahlAssistentGruppe->oAuswahlAssistentFrage_arr|@count > 0}
                                                <tr>
                                                    <td class="tleft" colspan="5"
                                                        id="row_toggle_{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}"
                                                        style="display: none;">
                                                        <div id="rowdiv_toggle_{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}"
                                                             style="display: none;">
                                                            <table class="list table">
                                                                <tr>
                                                                    <th class="tcenter"></th>
                                                                    <th class="tleft">{#aaQuestionName#}</th>
                                                                    <th class="tcenter">{#aaMerkmal#}</th>
                                                                    <th class="tcenter">{#aaSort#}</th>
                                                                    <th class="tright">&nbsp;</th>
                                                                </tr>
                                                                {foreach name=auswahlfragen from=$oAuswahlAssistentGruppe->oAuswahlAssistentFrage_arr item=oAuswahlAssistentFrage}
                                                                    <tr{if !$oAuswahlAssistentFrage->nAktiv} class="text-danger"{/if}>
                                                                        <td>{if !$oAuswahlAssistentFrage->nAktiv}<i class="fa fa-times"></i>{/if}</td>
                                                                        <td class="tleft">{$oAuswahlAssistentFrage->cFrage}</td>
                                                                        <td class="tcenter">{$oAuswahlAssistentFrage->cName}</td>
                                                                        <td class="tcenter">{$oAuswahlAssistentFrage->nSort}</td>
                                                                        <td class="tright" style="width:250px">
                                                                            <div class="btn-group">
                                                                                <a href="auswahlassistent.php?a=editQuest&q={$oAuswahlAssistentFrage->kAuswahlAssistentFrage}&token={$smarty.session.jtl_token}" class="btn btn-default edit">
                                                                                    <i class="fa fa-edit"></i>
                                                                                </a>
                                                                                <a href="auswahlassistent.php?a=delQuest&q={$oAuswahlAssistentFrage->kAuswahlAssistentFrage}&token={$smarty.session.jtl_token}" class="btn btn-danger remove">
                                                                                    <i class="fa fa-trash"></i>
                                                                                </a>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                {/foreach}
                                                            </table>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <script>
                                                    $("#btn_toggle_{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}").click(function () {
                                                        $("#row_toggle_{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}").slideToggle(100, 'linear');
                                                        $("#rowdiv_toggle_{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}").slideToggle(100, 'linear');
                                                    });
                                                </script>
                                            {/if}
                                        {/foreach}
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td></td>
                                            <td class="check">
                                                <input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);">
                                            </td>
                                            <td colspan="3" class="tleft"><label for="ALLMSGS">{#globalSelectAll#}</label></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        {else}
                            <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
                        {/if}
                        <div class="panel-footer">
                            <div class="btn-group">
                                {if isset($oAuswahlAssistentGruppe_arr) && $oAuswahlAssistentGruppe_arr|@count > 0}
                                    <button type="submit" name="a" value="delGrp" class="btn btn-danger">
                                        <i class="fa fa-trash"></i> {#aaDelete#}
                                    </button>
                                {/if}
                                <button type="submit" name="a" value="newGrp" class="btn btn-primary">
                                    <i class="fa fa-share"></i> {#aaGroup#}
                                </button>
                                <button type="submit" name="a" value="newQuest" class="btn btn-default">
                                    <i class="fa fa-share"></i> {#aaQuestion#}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <!-- #overview -->
            <div id="config" class="tab-pane fade{if isset($cTab) && $cTab === 'einstellungen'} active in{/if}">
                {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' a='saveSettings' action='auswahlassistent.php' buttonCaption=#save# tab='einstellungen'}
            </div>
            <!-- #config -->
        </div>
        <!-- .tab-content -->
    {else}
        <div class="alert alert-danger">{#noModuleAvailable#}</div>
    {/if}
</div><!-- #content -->

{include file='tpl_inc/footer.tpl'}