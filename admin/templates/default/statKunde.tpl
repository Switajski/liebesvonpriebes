{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: statSuchmaschine.tpl, smarty template inc file
	
	sales statistics page for JTL-Shop 3 
	Admin
	
	Author: niclas@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2008 JTL-Software
    

-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="statistics"}
<div id="page">
        <div id="content">
        <div id="welcome" class="post">
			<h2 class="title"><span>{#visiterStatisticTitle#}</span></h2>
			<div class="content">
            <p>{#visiterStatisticDesc#}</p>
            </div>
		</div>
        <div class="container">
			<form name="auswahl" method="post" action="">
            <input type="hidden" id="not_new" value="{$smarty.post.not_new}" />
			<input type="hidden" name="page" value="0" />
        <div id="payment">
        <fieldset>
                 <legend>{#filters#}</legend>
          <p><label for="month">{#monthYear#}</label>
              <select name="month" id="month" class="combo" style="width:150px">
													<option value="" {if $month<1}selected="selected"{/if}>&nbsp;</option>
													<option value="01" {if $month==1}selected="selected"{/if}>{#january#}</option>
													<option value="02" {if $month==2}selected="selected"{/if}>{#february#}</option>
													<option value="03" {if $month==3}selected="selected"{/if}>{#march#}</option>
													<option value="04" {if $month==4}selected="selected"{/if}>{#april#}</option>
													<option value="05" {if $month==5}selected="selected"{/if}>{#may#}</option>
													<option value="06" {if $month==6}selected="selected"{/if}>{#june#}</option>
													<option value="07" {if $month==7}selected="selected"{/if}>{#july#}</option>
													<option value="08" {if $month==8}selected="selected"{/if}>{#august#}</option>
													<option value="09" {if $month==9}selected="selected"{/if}>{#september#}</option>
													<option value="10" {if $month==10}selected="selected"{/if}>{#october#}</option>
													<option value="11" {if $month==11}selected="selected"{/if}>{#november#}</option>
													<option value="12" {if $month==12}selected="selected"{/if}>{#december#}</option>
												</select>
                                                <select name="year" id="year" class="combo" style="width:100px">
													<option value="{$current_year}" {if $year==$current_year}selected="selected"{/if}>{$current_year}</option>
													<option value="{$current_year-1}" {if $year==$current_year-1}selected="selected"{/if}>{$current_year-1}</option>
													<option value="{$current_year-2}" {if $year==$current_year-2}selected="selected"{/if}>{$current_year-2}</option>
													<option value="{$current_year-3}" {if $year==$current_year-3}selected="selected"{/if}>{$current_year-3}</option>
													<option value="{$current_year-4}" {if $year==$current_year-4}selected="selected"{/if}>{$current_year-4}</option>
												</select></p>
                      <p><label for="group_by">{#groupto#}</label>                     
                     <select name="group_by" id="group_by" class="combo">
                     <option value="">&nbsp;</option>
					<option value="kBestellung" {if $group_by=="kBestellung"}selected="selected"{/if}>{#orderNo#}</option>
					<option value="kKunde" {if $group_by=="kKunde"}selected="selected"{/if}>{#visiter#}</option>
					<option value="cBrowser" {if $group_by=="cBrowser"}selected="selected"{/if}>{#browser#}</option>
					<option value="cReferer" {if $group_by=="cReferer"}selected="selected"{/if}>{#referrer#}</option>
					<option value="cSuchanfrage" {if $group_by=="cSuchanfrage"}selected="selected"{/if}>{#searchatse#}</option>
												</select></p>
                           <p><label for="order_by">{#sortto#}</label>
                           <select name="order_by" id="order_by" class="combo">
													<option value="dZeit" {if $order_by=="dZeit"}selected="selected"{/if}>{#date#}</option>
													<option value="tkunde.kKunde" {if $order_by=="tkunde.kKunde"}selected="selected"{/if}>{#accountNumber#}</option>
													<option value="dErstellt" {if $order_by=="dErstellt"}selected="selected"{/if}>{#accountBuild#}</option>
													<option value="kBestellung" {if $order_by=="kBestellung"}selected="selected"{/if}>{#orders#}</option>
													<option value="cBrowser" {if $order_by=="cBrowser"}selected="selected"{/if}>{#browser#}</option>
													<option value="cSuchanfrage" {if $order_by=="cSuchanfrage"}selected="selected"{/if}>{#searchKey#}</option>
												</select></p>
                         <p><label for="order">{#sortOptions#}</label>
                           <select name="order" id="order" class="combo">
													<option value="DESC" {if $order=="DESC"}selected="selected"{/if}>{#desc#}</option>
													<option value="ASC" {if $order=="ASC"}selected="selected"{/if}>{#asc#}</option>
												</select></p> 
                          <p><label for="limit">{#siteOptions#}</label>
              				<select name="limit" id="limit" class="combo">
													<option value="25" {if $limit=="25"}selected="selected"{/if}>25</option>
													<option value="50" {if $limit=="50"}selected="selected"{/if}>50</option>
													<option value="100" {if $limit=="100"}selected="selected"{/if}>100</option>
													<option value="500" {if $limit=="500"}selected="selected"{/if}>500</option>
													<option value="" {if !$limit}selected="selected"{/if}>&nbsp;</option>
							</select></p>                      
                                                
            </fieldset>     
          </div>      
    <p class="submit"><input onclick="document.getElementById('not_new').value='1';" type="submit" name="show" value="{#submit#}" /> <input type="submit" name="export" value="{#export#}" /></p>
          </form>
			</div>
            
           <div id="example-1" class="post">
           <div id="statisticTable">
           {if $group_by}
           
	{if $group_by=="kBestellung"}
									<div style="margin:10px 0">{#groupOrderDesc1#} <strong>{$count_all}</strong> {#groupOrderDesc2#}:
                                    <ul>
									<li style="margin:5px 0"><strong>{$counter_positiv}</strong>{#groupOrderDesc3#}<strong> {$prozent_positiv}%</strong></li>
									<li style="margin:5px 0"><strong>{$counter_negativ} </strong>{#groupOrderDesc4#}<strong> {$prozent_negativ}%</strong></li>
									</ul>
									{#groupOrderDesc5#} {$counter_positiv} {#groupOrderDesc16#} {$counter_gleiches_datum} {#groupOrderDesc7#} <strong>{$counter_gleiches_datum_prozent}%</strong>.</div>
	{/if}
    
	{if $group_by=="kKunde"}
									<div style="margin:10px 0">{#groupOrderDesc1#} <strong>{$count_all}</strong> {#groupOrderDesc10#}
                                    <ul>
									<li style="margin:5px 0"><strong>{$counter_positiv}</strong> {#groupOrderDesc8#} <strong>{$prozent_positiv}%</strong></li>
									<li style="margin:5px 0"><strong>{$counter_negativ}</strong> {#groupOrderDesc9#} <strong>{$prozent_negativ}%</strong></li>
									</ul></div>
	{/if}
    <p class="clearer" />
          
                    <table>
                    <thead style="overflow:auto">
                    <tr>
    {if $group_by=="kBestellung"}
                    <th class="th-1">{#browser#}</th>
                    <th class="th-2">{#searchKey#}</th>
                    <th class="th-3">{#orderNo#}</th>
     {else}
                    <th class="th-4">{$group_by}</th>
      {/if}
                    <th colspan="2" class="th-5">{#number#} ({$count_all})</th>
                    </tr>
                    </thead>
                    <tbody style="overflow:auto">
      {foreach name=query from=$arQuery item=row key=QueryKey}
                   <tr class="tab_bg{$smarty.foreach.query.iteration%2}">
       {if $QueryKey==0}
		{if $group_by=="kBestellung"}
                    <td class="TD1">-</td>
                    <td class="TD2">-</td>
                    <td class="TD3">-</td>
        {else}
                    <td class="TD4">-</td>
         {/if}
			{else}
				{if $group_by=="kBestellung"}
                    <td class="TD5">{$row->cBrowser}</td>
                    <td class="TD6">{$row->cSuchanfrage}</td>
                    <td class="TD7">{$row->kBestellung}</td>
           {/if}
			{if $group_by=="kKunde"}
                    <td title="{$row->cEinstiegsseiteKomplett}" class="TD5"><a href="..{$row->kKunde}" rel="external">{$row->kKunde}</a></td>
            {/if}
              {if $group_by=="cBrowser"}
                    <td class="TD6">{$row->cBrowser}</td>
             {/if}
               {if $group_by=="cReferer"}
                    <td class="TD7">{$row->cReferer}</td>
             {/if}
               {if $group_by=="cSuchanfrage"}
					<td class="TD8">{$row->cSuchanfrage}</td>
		{/if}
          {/if}
                   <td class="TD8"><strong>{$row->count}</strong></td>
					<td class="TD9">{$row->prozent}%</td>
                    </tr>
                    {/foreach}
                    </tbody>
                    </table>
                    {else}
                    <table>
                    <thead>
                    <tr>
                    <th class="th-1">{#date#}</th>
                    <th class="th-2">{#browser#}</th>
                    <th class="th-3">{#referrer#}</th>
                    <th class="th-4">{#searchKey#}</th>
                    <th class="th-5">{#accountNumber#}</th>
                    <th class="th-6">{#accountBuild#}</th>
                    <th class="th-7">{#orders#}</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach name=query from=$arQuery item=row}
                   <tr class="tab_bg{$smarty.foreach.query.iteration%2}">
                    <td class="TD1">{$row->dZeit}</td>
                    <td class="TD2">{$row->cBrowser}</td>
                    <td class="TD3"><a href="http://{$row->cReferer}" rel="external">{$row->cReferer}</a></td>
                    <td class="TD4">{$row->cSuchanfrage}</td>
                    <td class="TD5">{if $row->kKunde > 0}{$row->kKunde}{/if}</td>
                    <td class="TD6">{$row->dErstellt}</td>
                    <td class="TD7">{if $row->kBestellung > 0}Y{/if}</td>
                    </tr>
                    {/foreach}
                    </tbody>
                    </table>
                   {if $nav} <p style="margin:10px 0 0">{#nav#}:<span class="smallfont">({$maxPage})</span></p>{/if}
                    <div class="pagesBottom">
                    {$nav}
                    </div>
                    {/if} 
          </div> 
          <div class="clearer"> </div>
			</div>

{include file='tpl_inc/footer.tpl'}