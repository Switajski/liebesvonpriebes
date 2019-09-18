{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="wunschliste"}
{include file='tpl_inc/seite_header.tpl' cTitel=#wishlistName# cBeschreibung=#wishlistDesc# cDokuURL=#wishlistURL#}
<div id="content" class="container-fluid">
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($cTab) || $cTab === 'wunschlistepos'} active{/if}">
            <a data-toggle="tab" role="tab" href="#wunschlistepos">{#wishlistTop100#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'wunschlisteartikel'} active{/if}">
            <a data-toggle="tab" role="tab" href="#wunschlisteartikel">{#wishlistPosTop100#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'wunschlistefreunde'} active{/if}">
            <a data-toggle="tab" role="tab" href="#wunschlistefreunde">{#wishlistSend#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'einstellungen'} active{/if}">
            <a data-toggle="tab" role="tab" href="#einstellungen">{#wishlistSettings#}</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="wunschlistepos" class="tab-pane fade {if !isset($cTab) || $cTab === 'wunschlistepos'} active in{/if}">
            {if isset($CWunschliste_arr) && $CWunschliste_arr|@count > 0}
                {include file='tpl_inc/pagination.tpl' oPagination=$oPagiPos cAnchor='wunschlistepos'}
                <div class="panel panel-default table-responsive">
                    <table class="table">
                        <tr>
                            <th class="tleft">{#wishlistName#}</th>
                            <th class="tleft">{#wishlistAccount#}</th>
                            <th class="th-3">{#wishlistPosCount#}</th>
                            <th class="th-4">{#wishlistDate#}</th>
                        </tr>
                        {foreach name=wunschliste from=$CWunschliste_arr item=CWunschliste}
                            <tr class="tab_bg{$smarty.foreach.wunschliste.iteration%2}">
                                <td class="TD1">
                                    {if $CWunschliste->nOeffentlich == 1}
                                        <a href="{$shopURL}/index.php?wlid={$CWunschliste->cURLID}" rel="external">{$CWunschliste->cName}</a>
                                    {else}
                                        <span>{$CWunschliste->cName}</span>
                                    {/if}
                                </td>
                                <td class="TD2">{$CWunschliste->cVorname} {$CWunschliste->cNachname}</td>
                                <td class="tcenter">{$CWunschliste->Anzahl}</td>
                                <td class="tcenter">{$CWunschliste->Datum}</td>
                            </tr>
                        {/foreach}
                    </table>
                </div>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <div id="wunschlisteartikel" class="tab-pane fade {if isset($cTab) && $cTab === 'wunschlisteartikel'} active in{/if}">
            {if isset($CWunschlistePos_arr) && $CWunschlistePos_arr|@count > 0}
                {include file='tpl_inc/pagination.tpl' oPagination=$oPagiArtikel cAnchor='wunschlisteartikel'}
                <div class="panel panel-default table-responsive">
                    <table class="table">
                        <tr>
                            <th class="tleft">{#wishlistPosName#}</th>
                            <th class="th-2">{#wishlistPosCount#}</th>
                            <th class="th-3">{#wishlistLastAdded#}</th>
                        </tr>
                        {foreach name=wunschlistepos from=$CWunschlistePos_arr item=CWunschlistePos}
                            <tr class="tab_bg{$smarty.foreach.wunschlistepos.iteration%2}">
                                <td class="TD1">
                                    <a href="{$shopURL}/index.php?a={$CWunschlistePos->kArtikel}&" rel="external">{$CWunschlistePos->cArtikelName}</a>
                                </td>
                                <td class="tcenter">{$CWunschlistePos->Anzahl}</td>
                                <td class="tcenter">{$CWunschlistePos->Datum}</td>
                            </tr>
                        {/foreach}
                    </table>
                </div>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <div id="wunschlistefreunde" class="tab-pane fade {if isset($cTab) && $cTab === 'wunschlistefreunde'} active in{/if}">
            {if $CWunschlisteVersand_arr && $CWunschlisteVersand_arr|@count > 0}
                {include file='tpl_inc/pagination.tpl' oPagination=$oPagiFreunde cAnchor='wunschlistefreunde'}
                <div class="panel panel-default table-responsive">
                    <table class="table">
                        <tr>
                            <th class="tleft">{#wishlistName#}</th>
                            <th class="tleft">{#wishlistAccount#}</th>
                            <th class="th-3">{#wishlistReceiverCount#}</th>
                            <th class="th-4">{#wishlistPosCount#}</th>
                            <th class="th-5">{#wishlistDate#}</th>
                        </tr>
                        {foreach name=wunschlisteversand from=$CWunschlisteVersand_arr item=CWunschlisteVersand}
                            <tr class="tab_bg{$smarty.foreach.wunschlisteversand.iteration%2}">
                                <td class="TD1">
                                    <a href="{$shopURL}/index.php?wlid={$CWunschlisteVersand->cURLID}" rel="external">{$CWunschlisteVersand->cName}</a>
                                </td>
                                <td class="TD2">{$CWunschlisteVersand->cVorname} {$CWunschlisteVersand->cNachname}</td>
                                <td class="tcenter">{$CWunschlisteVersand->nAnzahlEmpfaenger}</td>
                                <td class="tcenter">{$CWunschlisteVersand->nAnzahlArtikel}</td>
                                <td class="tcenter">{$CWunschlisteVersand->Datum}</td>
                            </tr>
                        {/foreach}
                    </table>
                </div>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <div id="einstellungen" class="tab-pane fade {if isset($cTab) && $cTab === 'einstellungen'} active in{/if}">
            {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' action='wunschliste.php' buttonCaption=#save# title='Einstellungen' tab='einstellungen'}
        </div>
    </div>
</div>

{include file='tpl_inc/footer.tpl'}