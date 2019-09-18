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
			<h2 class="title"><span>{#searchStatisticTitle#}</span></h2>
			<div class="content">
            <p>{#searchStatisticDesc#}</p>
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
													<option value=""   {if $month<1}selected="selected"{/if}>&nbsp;</option>
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
					<option value="cReferer" {if $group_by=="cReferer"}selected="selected"{/if}>{#se#}</option>
					<option value="cSuchanfrage" {if $group_by=="cSuchanfrage"}selected="selected"{/if}>{#sq#}</option>
					<option value="cEinstiegsseite" {if $group_by=="cEinstiegsseite"}selected="selected"{/if}>{#hp#}</option>
					<option value="bestell_count" {if $group_by=="bestell_count"}selected="selected"{/if}>{#orderNoReferrer#}</option>
					<option value="bestell_count_2" {if $group_by=="bestell_count_2"}selected="selected"{/if}>{#orderNoSe#}</option>
					<option value="bestell_count_3" {if $group_by=="bestell_count_3"}selected="selected"{/if}>{#orderNoSqSe#}</option>
												</select></p>
                           <p><label for="order_by">{#sortto#}</label>
                           <select name="order_by" id="order_by" class="combo">
													<option value="">&nbsp;</option>
													<option value="cReferer" {if $order_by=="cReferer"}selected="selected"{/if}>{#referrer#}</option>
													<option value="cSuchanfrage" {if $order_by=="cSuchanfrage"}selected="selected"{/if}>{#sq#}</option>
													<option value="cEinstiegsseite" {if $order_by=="cEinstiegsseite"}selected="selected"{/if}>{#hp#}</option>
													<option value="kKunde" {if $order_by=="kKunde"}selected="selected"{/if}>{#accountNumber#}</option>
													<option value="kBestellung" {if $order_by=="kBestellung"}selected="selected"{/if}>{#orders#}</option>
													<option value="dZeit" {if $order_by=="dZeit"}selected="selected"{/if}>{#date#}</option>
												</select></p>
                         <p><label for="order">{#sortOptions#}</label>
                           <select name="order" id="order" class="combo">
													<option value="DESC" {if $order=="DESC"}selected="selected"{/if}>{#desc#}</option>
													<option value="ASC" {if $order=="ASC"}selected="selected"{/if}>{#asc#}</option>
												</select></p> 
                          <p><label for="limit">{#siteOptions#}</label>
              				<select name="limit" id="limit" class="combo">
													<option value="50" {if $limit=="50"}selected="selected"{/if}>50</option>
													<option value="100" {if $limit=="100"}selected="selected"{/if}>100</option>
													<option value="500" {if $limit=="500"}selected="selected"{/if}>500</option>
													<option value="" {if !$limit}selected="selected"{/if}>&nbsp;</option>
							</select></p>                      
                                                
            </fieldset>     
          </div>      
    <p class="submit"><input type="submit" name="show" value="{#submit#}" /><input type="submit" name="export" value="{#export#}" /></p>
          </form>
			</div>
            
           <div id="example-1" class="post">
           <div id="statisticTable">
           {if $group_by == 'bestell_count' || $group_by == 'bestell_count_2' || $group_by == 'bestell_count_3'}
                    <table>
                    <thead style="overflow:auto">
                    <tr>
                    <th class="th-1">{#numbersOfOrder#}</th>
                    <th class="th-2">{if $group_by == 'bestell_count'}{#referrer#}{else}{#ses#}{/if}</th>
                    {if $group_by == 'bestell_count_3'}
                    <th class="th-3">{#sqs#}</th>
                    {/if}
                    </tr>
                    </thead>
                    <tbody style="overflow:auto">
                   {foreach name=query from=$arQuery item=row}
                   <tr class="tab_bg{$smarty.foreach.query.iteration%2}">
                    <td class="TD1">{$row->bestell_count}</td>
                    <td class="TD2"><a href="http://{$row->cReferer}" rel="external">{$row->cReferer}</a></td>
                    {if $group_by == 'bestell_count_3'} 
                    <td class="TD3">{$row->cSuchanfrage}</td>
                    {/if}
                    </tr>
                    {/foreach}
                    </tbody>
                    </table>
                    {elseif $group_by}
                    <table>
                    <thead>
                    <tr>
                    <th class="th-1">{$group_by}</th>
                    <th class="th-2">{#numbersOfOrder#} ({$count_all_bestellungen})</th>
                    <th class="th-3">{#conversionRate#}</th>
                    <th class="th-4">{#number#} ({$count_all})</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach name=query from=$arQuery item=row}
                   <tr class="tab_bg{$smarty.foreach.query.iteration%2}">
                   {if $group_by=="cReferer"}
                    <td class="TD1"><a href="http://{$row->cReferer}" rel="external">{$row->cReferer}</a></td>
                    {/if}
					{if $group_by=="cEinstiegsseite"}
                    <td title="{$row->cEinstiegsseiteKomplett}" class="TD2"><a href="..{$row->cEinstiegsseite}" rel="external">{$row->cEinstiegsseite}</a></td>
                    {/if}
					{if $group_by=="cSuchanfrage"}
                    <td class="TD3">{$row->cSuchanfrage}</td>
                    {/if}
                    <td class="TD4"><strong>{$row->bestell_count}</strong>{if $row->bestellungen_prozent} ({$row->bestellungen_prozent}%){/if}</td>
                    <td class="TD5">{$row->conversion_rate}</td>
                    <td class="TD6"><strong>{$row->count}</strong>{if $row->prozent} ({$row->prozent}%){/if}</td>
                    </tr>
                    {/foreach}
                    </tbody>
                    </table>
                    {else}
                    <table>
                    <thead>
                    <tr>
                    <th class="th-1">{#date#}</th>
                    <th class="th-2">{#referrer#}</th>
                    <th class="th-3">{#sq#}</th>
                    <th class="th-4">{#hp#}</th>
                    <th class="th-5">{#accountNumber#}</th>
                    <th class="th-6">{#orders#}?</th>
                    {if !$year && !$month}
                    <th class="th-7">{#date#}</th>
                    {/if}
                    </tr>
                    </thead>
                    <tbody>
                    {foreach name=query from=$arQuery item=row}
                   <tr class="tab_bg{$smarty.foreach.query.iteration%2}">
                    <td class="TD1">{$row->dZeit}</td>
                    <td class="TD2"><a href="http://{$row->cReferer}" rel="external">{$row->cReferer}</a></td>
                    <td class="TD3">{$row->cSuchanfrage}</td>
                    <td title="{$row->cEinstiegsseiteKomplett}" class="TD4"><a href="..{$row->cEinstiegsseiteKomplett}" rel="external">{$row->cEinstiegsseite}</a></td>
                    <td class="TD5">{if $row->kKunde > 0}{$row->kKunde}{/if}</td>
                    <td class="TD6">{if $row->kBestellung > 0}Y{/if}</td>
                    {if !$year && !$month}
                    <td class="TD7">{$row->dZeit}</td>
                    {/if}
                    </tr>
                    {/foreach}
                    </tbody>
                    </table>
                    {if $nav} <p style="margin:10px 0 0">{#nav#}:</p>{/if}
                    <div class="pagesBottom">
                    {$nav}
                    </div>
                    {/if}
          </div> 
			</div>

{include file='tpl_inc/footer.tpl'}