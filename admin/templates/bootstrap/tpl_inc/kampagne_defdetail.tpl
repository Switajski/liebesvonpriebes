{include file='tpl_inc/seite_header.tpl' cTitel=#kampagneDetailStatsDef#}
<div id="content" class="container-fluid">
    <div id="tabellenLivesuche" class="table-responsive">
        <table class="table">
            <tr>
                <th class="tleft"><strong>{$oKampagneDef->cName}</strong></th>
            </tr>
            <tr>
                <td class="TD1">
                    {#kampagnePeriod#}: {$cStampText}<br />
                    {#kampagneOverall#}: {$nGesamtAnzahlDefDetail}
                </td>
            </tr>
        </table>
    </div>

    <div class="panel panel-default" id="payment">
        {if isset($oKampagneStat_arr) && $oKampagneStat_arr|@count > 0 && isset($oKampagneDef->kKampagneDef) && $oKampagneDef->kKampagneDef > 0}
            {include file='tpl_inc/pagination.tpl' oPagination=$oPagiDefDetail
                     cParam_arr=['kKampagne'=>$oKampagne->kKampagne, 'defdetail'=>1,
                                 'kKampagneDef'=>$oKampagneDef->kKampagneDef, 'cZeitParam'=>$cZeitraumParam,
                                 'token'=>$smarty.session.jtl_token]}
            <div id="tabellenLivesuche" class="table-responsive">
                <table class="table">
                    <tr>
                        {foreach name="kampagnendefs" from=$cMember_arr key=cMember item=cMemberAnzeige}
                            <th class="th-2">{$cMemberAnzeige|truncate:50:"..."}</th>
                        {/foreach}
                    </tr>

                    {foreach name="kampagnenstats" from=$oKampagneStat_arr item=oKampagneStat}
                        <tr class="tab_bg{$smarty.foreach.kampagnenstats.iteration%2}">
                            {foreach name="kampagnendefs" from=$cMember_arr key=cMember item=cMemberAnzeige}
                                <td class="TD1" style="text-align: center;">{$oKampagneStat->$cMember|wordwrap:40:"<br />":true}</td>
                            {/foreach}
                        </tr>
                    {/foreach}
                </table>
            </div>
        {else}
            <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
        {/if}
        <div class="panel-footer">
            <a class="btn btn-default" href="kampagne.php?kKampagne={$oKampagne->kKampagne}&detail=1&token={$smarty.session.jtl_token}">
                <i class="fa fa-angle-double-left"></i> {#kampagneBackBTN#}
            </a>
        </div>
    </div>
</div>