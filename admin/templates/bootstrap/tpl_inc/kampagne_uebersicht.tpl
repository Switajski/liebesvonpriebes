<script type="text/javascript">
    function changeZeitSelect(currentSelect) {ldelim}
        if (currentSelect.options[currentSelect.selectedIndex].value > 0)
            window.location.href = "kampagne.php?tab=globalestats&nAnsicht=" + currentSelect.options[currentSelect.selectedIndex].value;
    {rdelim}
</script>

{include file='tpl_inc/seite_header.tpl' cTitel=#kampagne# cBeschreibung=#kampagneDesc# cDokuURL=#kampagneURL#}
<div id="content" class="container-fluid">
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($cTab) || $cTab === 'uebersicht'} active{/if}">
            <a data-toggle="tab" role="tab" href="#uebersicht">{#kampagneOverview#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'globalestats'} active{/if}">
            <a data-toggle="tab" role="tab" href="#globalestats">{#kampagneGlobalStats#}</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="uebersicht" class="tab-pane fade {if !isset($cTab) || $cTab === 'uebersicht'} active in{/if}">
            <div class="panel panel-default">
                <div class="panel-heading"><h3 class="panel-title">{#kampagneIntern#}</h3></div>
                {if $oKampagne_arr|count > 0}
                    <div class="table-responsive">
                        <table class="table">
                            <tr>
                                <th class="tleft">{#kampagneName#}</th>
                                <th class="tleft">{#kampagneParam#}</th>
                                <th class="tleft">{#kampagneValue#}</th>
                                <th class="th-4">{#kampagnenActive#}</th>
                                <th class="th-5">{#kampagnenDate#}</th>
                                <th class="th-6"></th>
                            </tr>

                            {foreach name="kampagnen" from=$oKampagne_arr item=oKampagne}
                                {if isset($oKampagne->kKampagne) && $oKampagne->kKampagne < 1000}
                                    <tr class="tab_bg{$smarty.foreach.kampagnen.iteration%2}">
                                        <td class="TD2">
                                            <strong><a href="kampagne.php?kKampagne={$oKampagne->kKampagne}&detail=1&token={$smarty.session.jtl_token}">{$oKampagne->cName}</a></strong>
                                        </td>
                                        <td class="TD3">{$oKampagne->cParameter}</td>
                                        <td class="TD3">
                                            {if isset($oKampagne->nDynamisch) && $oKampagne->nDynamisch == 1}
                                                {#kampagneDynamic#}
                                            {else}
                                                {#kampagneStatic#}
                                                <br />
                                                <strong>{#kampagneValueStatic#}:</strong>
                                                {$oKampagne->cWert}
                                            {/if}
                                        </td>
                                        <td class="tcenter">{if isset($oKampagne->nAktiv) && $oKampagne->nAktiv == 1}{#yes#}{else}{#no#}{/if}</td>
                                        <td class="tcenter">{$oKampagne->dErstellt_DE}</td>
                                        <td class="tcenter">
                                            <a href="kampagne.php?kKampagne={$oKampagne->kKampagne}&editieren=1&token={$smarty.session.jtl_token}" title="{#modify#}" class="btn btn-default"><i class="fa fa-edit"></i></a>
                                        </td>
                                    </tr>
                                {/if}
                            {/foreach}
                        </table>
                    </div>
                {else}
                    <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
                {/if}
            </div>
            <div class="panel panel-default">
                <div class="panel-heading"><h3 class="panel-title">{#kampagneExtern#}</h3></div>
                <form name="kampagnen" method="post" action="kampagne.php">
                    {if isset($nGroessterKey) && $nGroessterKey >= 1000}
                        {$jtl_token}
                        <input type="hidden" name="tab" value="uebersicht" />
                        <input type="hidden" name="delete" value="1" />
                        <div class="table-responsive">
                            <table class="table">
                                <tr>
                                    <th class="check"></th>
                                    <th class="tleft">{#kampagneName#}</th>
                                    <th class="tleft">{#kampagneParam#}</th>
                                    <th class="tleft">{#kampagneValue#}</th>
                                    <th class="th-4">{#kampagnenActive#}</th>
                                    <th class="th-5">{#kampagnenDate#}</th>
                                    <th class="th-6"></th>
                                </tr>

                                {foreach name="kampagnen" from=$oKampagne_arr item=oKampagne}
                                    {if $oKampagne->kKampagne >= 1000}
                                        <tr class="tab_bg{$smarty.foreach.kampagnen.iteration%2}">
                                            <td class="check">
                                                <input name="kKampagne[]" type="checkbox" value="{$oKampagne->kKampagne}">
                                            </td>
                                            <td class="TD2">
                                                <strong><a href="kampagne.php?kKampagne={$oKampagne->kKampagne}&detail=1&token={$smarty.session.jtl_token}">{$oKampagne->cName}</a></strong>
                                            </td>
                                            <td class="TD3">{$oKampagne->cParameter}</td>
                                            <td class="TD3">
                                                {if isset($oKampagne->nDynamisch) && $oKampagne->nDynamisch == 1}
                                                    {#kampagneDynamic#}
                                                {else}
                                                    {#kampagneStatic#}
                                                    <br />
                                                    <strong>{#kampagneValueStatic#}:</strong>
                                                    {$oKampagne->cWert}
                                                {/if}
                                            </td>
                                            <td class="tcenter">{if isset($oKampagne->nAktiv) && $oKampagne->nAktiv == 1}{#yes#}{else}{#no#}{/if}</td>
                                            <td class="tcenter">{$oKampagne->dErstellt_DE}</td>
                                            <td class="tcenter">
                                                <a href="kampagne.php?kKampagne={$oKampagne->kKampagne}&editieren=1&token={$smarty.session.jtl_token}"
                                                   class="btn btn-default" title="{#modify#}">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    {/if}
                                {/foreach}
                                <tr>
                                    <td class="check">
                                        <input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);" />
                                    </td>
                                    <td colspan="6"><label for="ALLMSGS">{#globalSelectAll#}</label></td>
                                </tr>
                            </table>
                        </div>
                    {else}
                        <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
                    {/if}
                    <div class="panel-footer">
                        <div class="btn-group">
                            {if isset($nGroessterKey) && $nGroessterKey >= 1000}
                                <button name="submitDelete" type="submit" value="{#delete#}" class="btn btn-danger"><i class="fa fa-trash"></i> {#deleteSelected#}</button>
                            {/if}
                            <a href="kampagne.php?neu=1&token={$smarty.session.jtl_token}" class="btn btn-primary">{#kampagneNewBTN#}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div id="globalestats" class="tab-pane fade {if isset($cTab) && $cTab === 'globalestats'} active in{/if}">
            <div class="block">
                <div class="input-group p25" style="min-width: 435px;">
                        <span class="input-group-addon">
                            <label for="nAnsicht">{#kampagneView#}:</label>
                        </span>
                    <select id="nAnsicht" name="nAnsicht" class="form-control combo" onchange="changeZeitSelect(this);">
                        <option value="-1"></option>
                        <option value="1"{if $smarty.session.Kampagne->nAnsicht == 1} selected{/if}>{#kampagneStatMonth#}</option>
                        <option value="2"{if $smarty.session.Kampagne->nAnsicht == 2} selected{/if}>{#kampagneStatWeek#}</option>
                        <option value="3"{if $smarty.session.Kampagne->nAnsicht == 3} selected{/if}>{#kampagneStatDay#}</option>
                    </select>
                    <span class="input-group-addon">
                            <strong>{#kampagnePeriod#}:</strong> {$cZeitraum}
                        </span>
                </div>
            </div>
            <div class="panel panel-default">
                {if isset($oKampagne_arr) && $oKampagne_arr|@count > 0 && isset($oKampagneDef_arr) && $oKampagneDef_arr|@count > 0}
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="th-1"></th>
                                    {foreach name="kampagnendefs" from=$oKampagneDef_arr item=oKampagneDef}
                                        <th class="th-2">
                                            <a href="kampagne.php?tab=globalestats&nSort={$oKampagneDef->kKampagneDef}&token={$smarty.session.jtl_token}">{$oKampagneDef->cName}</a>
                                            {if $oKampagneDef->cName === 'Angeschaute Newsletter'}
                                                <label type="button" class="label label-default label-sm"
                                                       data-toggle="tooltip"
                                                       data-placement="left"
                                                       title="{#kampagnenNLInfo#}">
                                                    ?
                                                </label>
                                            {/if}
                                        </th>
                                    {/foreach}
                                </tr>
                            </thead>

                            {foreach name="kampagnenstats" from=$oKampagneStat_arr key=kKampagne item=oKampagneStatDef_arr}
                                {if $kKampagne != "Gesamt"}
                                    <tr class="tab_bg{$smarty.foreach.kampagnenstats.iteration%2}">
                                        <td class="TD1">
                                            <a href="kampagne.php?detail=1&kKampagne={$oKampagne_arr[$kKampagne]->kKampagne}&cZeitParam={$cZeitraumParam}&token={$smarty.session.jtl_token}">{$oKampagne_arr[$kKampagne]->cName}</a>
                                        </td>
                                        {foreach name="kampagnendefs" from=$oKampagneStatDef_arr key=kKampagneDef item=oKampagneStatDef}
                                            <td class="TD1">
                                                <a href="kampagne.php?kKampagne={$kKampagne}&defdetail=1&kKampagneDef={$kKampagneDef}&cZeitParam={$cZeitraumParam}&token={$smarty.session.jtl_token}">{$oKampagneStat_arr[$kKampagne][$kKampagneDef]}</a>
                                            </td>
                                        {/foreach}
                                    </tr>
                                {/if}
                            {/foreach}
                            <tfoot>
                                <tr>
                                    <td class="TD1">{#kampagneOverall#}</td>
                                    {foreach name="kampagnendefs" from=$oKampagneDef_arr key=kKampagneDef item=oKampagneDef}
                                        <td class="TD1">
                                            {$oKampagneStat_arr.Gesamt[$kKampagneDef]}
                                        </td>
                                    {/foreach}
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="panel-footer">
                        <div class="btn-group">
                            <a href="kampagne.php?tab=globalestats&nStamp=-1&token={$smarty.session.jtl_token}" class="btn btn-default"><i class="fa fa-angle-double-left"></i> Fr&uuml;her</a>
                            {if isset($nGreaterNow) && !$nGreaterNow}
                                <a href="kampagne.php?tab=globalestats&nStamp=1&token={$smarty.session.jtl_token}" class="btn btn-default"><i class="fa fa-angle-double-right"></i> Sp&auml;ter</a>
                            {/if}
                        </div>
                    </div>
                {else}
                    <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
                {/if}
            </div>
        </div>
    </div>
</div>
