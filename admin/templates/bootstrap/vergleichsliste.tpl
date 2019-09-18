{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="vergleichsliste"}

{include file='tpl_inc/seite_header.tpl' cTitel=#configureComparelist# cBeschreibung=#configureComparelistDesc# cDokuURL=#configureComparelistURL#}
<div id="content" class="container-fluid">
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($cTab) || $cTab === 'letztenvergleiche'} active{/if}">
            <a data-toggle="tab" role="tab" href="#letztenvergleiche">{#last20Compares#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'topartikel'} active{/if}">
            <a data-toggle="tab" role="tab" href="#topartikel">{#topCompareProducts#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'einstellungen'} active{/if}">
            <a data-toggle="tab" role="tab" href="#einstellungen">{#compareSettings#}</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="letztenvergleiche" class="tab-pane fade {if !isset($cTab) || $cTab === 'letztenvergleiche'} active in{/if}">
            {if $Letzten20Vergleiche && $Letzten20Vergleiche|@count > 0}
                {include file='tpl_inc/pagination.tpl' oPagination=$oPagination cAnchor='letztenvergleiche'}
                <div class="settings panel panel-default table-responsive">
                    <table  class="table">
                        <tr>
                            <th class="th-1">{#compareID#}</th>
                            <th class="tleft">{#compareProducts#}</th>
                            <th class="th-3">{#compareDate#}</th>
                        </tr>
                        {foreach name=letzten20 from=$Letzten20Vergleiche item=oVergleichsliste20}
                            <tr class="tab_bg{$smarty.foreach.letzten20.iteration%2}">
                                <td class="tcenter">{$oVergleichsliste20->kVergleichsliste}</td>
                                <td class="">
                                    {foreach name=letzten20pos from=$oVergleichsliste20->oLetzten20VergleichslistePos_arr item=oVergleichslistePos20}
                                        <a href="{$shopURL}/index.php?a={$oVergleichslistePos20->kArtikel}" target="_blank">{$oVergleichslistePos20->cArtikelName}</a>{if !$smarty.foreach.letzten20pos.last}{/if}
                                        <br />
                                    {/foreach}
                                </td>
                                <td class="tcenter">{$oVergleichsliste20->Datum}</td>
                            </tr>
                        {/foreach}
                    </table>
                </div>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <div id="topartikel" class="tab-pane fade {if isset($cTab) && $cTab === 'topartikel'} active in{/if}">
            <form id="postzeitfilter" name="postzeitfilter" method="post" action="vergleichsliste.php">
                {$jtl_token}
                <input type="hidden" name="zeitfilter" value="1" />
                <input type="hidden" name="tab" value="topartikel" />
                <div class="block">
                    <div class="input-group p25 left" style="margin-right: 20px;">
                        <span class="input-group-addon">
                            <label for="nZeitFilter">{#compareTimeFilter#}:</label>
                        </span>
                        <span class="input-group-wrap">
                            <select class="form-control" id="nZeitFilter" name="nZeitFilter" onchange="document.postzeitfilter.submit();">
                                <option value="1"{if isset($smarty.session.Vergleichsliste->nZeitFilter) && $smarty.session.Vergleichsliste->nZeitFilter == 1} selected{/if}>
                                    letzte 24 Stunden
                                </option>
                                <option value="7"{if isset($smarty.session.Vergleichsliste->nZeitFilter) && $smarty.session.Vergleichsliste->nZeitFilter == 7} selected{/if}>
                                    letzte 7 Tage
                                </option>
                                <option value="30"{if isset($smarty.session.Vergleichsliste->nZeitFilter) && $smarty.session.Vergleichsliste->nZeitFilter == 30} selected{/if}>
                                    letzte 30 Tage
                                </option>
                                <option value="365"{if isset($smarty.session.Vergleichsliste->nZeitFilter) && $smarty.session.Vergleichsliste->nZeitFilter == 365} selected{/if}>
                                    letztes Jahr
                                </option>
                            </select>
                        </span>
                    </div>

                    <div class="input-group p25 left">
                        <span class="input-group-addon">
                            <label for="nAnzahl">{#compareTopCount#}:</label>
                        </span>
                        <span class="input-group-wrap">
                            <select class="form-control" id="nAnzahl" name="nAnzahl" onchange="document.postzeitfilter.submit();">
                                <option value="10"{if isset($smarty.session.Vergleichsliste->nAnzahl) && $smarty.session.Vergleichsliste->nAnzahl == 10} selected{/if}>10
                                </option>
                                <option value="20"{if isset($smarty.session.Vergleichsliste->nAnzahl) && $smarty.session.Vergleichsliste->nAnzahl == 20} selected{/if}>20
                                </option>
                                <option value="50"{if isset($smarty.session.Vergleichsliste->nAnzahl) && $smarty.session.Vergleichsliste->nAnzahl == 50} selected{/if}>50
                                </option>
                                <option value="100"{if isset($smarty.session.Vergleichsliste->nAnzahl) && $smarty.session.Vergleichsliste->nAnzahl == 100} selected{/if}>
                                    100
                                </option>
                                <option value="-1"{if isset($smarty.session.Vergleichsliste->nAnzahl) && $smarty.session.Vergleichsliste->nAnzahl == -1} selected{/if}>
                                    Alle
                                </option>
                            </select>
                        </span>
                    </div>
                </div>
            </form>

            {if isset($TopVergleiche) && $TopVergleiche|@count > 0}
                <div class="settings panel panel-default table-responsive">
                    <table class="bottom table">
                        <tr>
                            <th class="tleft">{#compareProduct#}</th>
                            <th class="th-2">{#compareCount#}</th>
                        </tr>
                        {foreach name=top from=$TopVergleiche item=oVergleichslistePosTop}
                            <tr class="tab_bg{$smarty.foreach.top.iteration%2}">
                                <td class="TD1">
                                    <a href="{$shopURL}/index.php?a={$oVergleichslistePosTop->kArtikel}" target="_blank">{$oVergleichslistePosTop->cArtikelName}</a>
                                </td>
                                <td class="tcenter">{$oVergleichslistePosTop->nAnzahl}</td>
                            </tr>
                        {/foreach}
                    </table>
                </div>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <div id="einstellungen" class="tab-pane fade {if isset($cTab) && $cTab === 'einstellungen'} active in{/if}">
            {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' action='vergleichsliste.php' buttonCaption=#save# title='Einstellungen' tab='einstellungen'}
        </div>
    </div>
</div>

{include file='tpl_inc/footer.tpl'}