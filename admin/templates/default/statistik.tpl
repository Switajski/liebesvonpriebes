{*
-------------------------------------------------------------------------------
    JTL-Shop 3
    File: statistik.tpl, smarty template inc file
    
    page for JTL-Shop 3 
    Admin
    
    Author: daniel.boehmer@jtl-software.de, JTL-Software
    http://www.jtl-software.de
    
    Copyright (c) 2010 JTL-Software
-------------------------------------------------------------------------------
*}
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
		
	{*
	{if $oStatJSON}
		<div class="container">
			<div id="my_chart"></div>
		</div>
	{else}
		<p class="box_info container">{#statisticNoData#}</p>
	{/if}
	*}
	
	{if $oBlaetterNavi->nAktiv == 1}
		<div class="container pages block">
			<span class="pageinfo">{#page#} <strong>{$oBlaetterNavi->nAktuelleSeite}</strong> {#from#} {$oBlaetterNavi->nBlaetterAnzahl_arr|@count}
				{*<strong>{$oBlaetterNavi->nVon}</strong> - {$oBlaetterNavi->nBis} {#from#} {$oBlaetterNavi->nAnzahl}*}
			</span>
			<a class="back" href="statistik.php?s1={$oBlaetterNavi->nVoherige}">&laquo;</a>
			{if $oBlaetterNavi->nAnfang != 0}<a href="statistik.php?s1={$oBlaetterNavi->nAnfang}">{$oBlaetterNavi->nAnfang}</a> ... {/if}
			{foreach name=blaetternavi from=$oBlaetterNavi->nBlaetterAnzahl_arr item=Blatt}
			<a class="page {if $oBlaetterNavi->nAktuelleSeite == $Blatt}active{/if}" href="statistik.php?s1={$Blatt}">{$Blatt}</a>
			{/foreach}
			
			{if $oBlaetterNavi->nEnde != 0}
			... <a class="page" href="statistik.php?s1={$oBlaetterNavi->nEnde}">{$oBlaetterNavi->nEnde}</a>
			{/if}
			<a class="next" href="statistik.php?s1={$oBlaetterNavi->nNaechste}">&raquo;</a>
		</div>
	{/if}
	
	{if isset($oStat_arr) && $oStat_arr|@count > 0}
	<table class="list">
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
	
	</div>
</div>

{include file='tpl_inc/footer.tpl'}