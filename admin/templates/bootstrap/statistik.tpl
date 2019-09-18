{config_load file="$lang.conf" section="statistics"}
{include file='tpl_inc/header.tpl'}

{include file='tpl_inc/statistik_jsoninc.tpl'}
{include file='tpl_inc/statistik_header.tpl'}

{if isset($linechart)}
    <br />
    {include file='tpl_inc/linechart_inc.tpl' linechart=$linechart headline=$headline id='linechart' width='100%' height='400px' ylabel=$ylabel href=false legend=false ymin='0'}
{elseif isset($piechart)}
    <br />
    {include file='tpl_inc/piechart_inc.tpl' piechart=$piechart headline=$headline id='piechart' width='100%' height='400px'}
{/if}

{include file='tpl_inc/pagination.tpl' oPagination=$oPagination}

{if isset($oStat_arr) && $oStat_arr|@count > 0}
    <table class="list table">
        <thead>
        <tr>
            {foreach name=member from=$cMember_arr[0] key=i item=cMember}
                <th>{$cMember[1]}</th>
            {/foreach}
        </tr>
        </thead>
        <tbody>
        {foreach name=stats key=i from=$oStat_arr item=oStat}
            {if $i >= $nPosAb && $i < $nPosBis}
                <tr>
                    {foreach name=member from=$cMember_arr[$i] key=j item=cMember}
                        {assign var=cMemberVar value=$cMember[0]}
                        <td class="tcenter">
                            {if $cMemberVar == "nCount" && $nTyp == $STATS_ADMIN_TYPE_UMSATZ}
                                {$oStat->$cMemberVar|number_format:2:',':'.'} &euro;
                            {elseif $cMemberVar == "nCount"}
                                {$oStat->$cMemberVar|number_format:0:',':'.'}
                            {else}
                                {$oStat->$cMemberVar}
                            {/if}
                        </td>
                    {/foreach}
                </tr>
            {/if}
        {/foreach}
        </tbody>
    </table>
{/if}

{include file='tpl_inc/footer.tpl'}