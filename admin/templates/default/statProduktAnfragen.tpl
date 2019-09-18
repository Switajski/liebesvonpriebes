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
			<h2 class="title"><span>{#productStatisticTitle#}</span></h2>
			<div class="content">
            <p>{#productStatisticDesc#}</p>
            </div>
		</div>
        <div class="container">
			<form name="produkte_anzeigen" method="post" action="">
        <div id="payment">
        <fieldset>
                 <legend>{#filters#}</legend>
          <p><label for="filter">{#sitesToBeLook#}</label>
              <select name="filter" id="filter" class="combo">
			<option value="produkt" {if $filter=="produkt"}selected="selected"{/if}>{#productQuestion#}</option>
			<option value="verfuegbar" {if $filter=="verfuegbar"}selected="selected"{/if}>{#allQuestions#}</option>
			<option value="verfuegbar_ja" {if $filter=="verfuegbar_ja"}selected="selected"{/if}>{#productSend#}</option>
			<option value="verfuegbar_nein" {if $filter=="verfuegbar_nein"}selected="selected"{/if}>{#productNotSend#}</option>
												</select></p>
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
                           <p><label for="order_by">{#sortto#}</label>
                           <select name="order_by" id="order_by" class="combo">
							<option value="">&nbsp;</option>
							<option value="dErstellt" {if $order_by=="dErstellt"}selected="selected"{/if}>{#date#}</option>
							<option value="ART.kArtikel" {if $order_by=="ART.kArtikel"}selected="selected"{/if}>{#restrictions#}</option>
							<option value="ART.kHersteller" {if $order_by=="ART.kHersteller"}selected="selected"{/if}>{#sortby4#}</option>
												</select></p>
                            <p><label for="group_by">{#groupto#}</label>
                           <select name="group_by" id="group_by" class="combo">
                           <option value="">&nbsp;</option
							><option value="ART.cName" {if $group_by=="ART.cName"}selected="selected"{/if}>{#restrictions#}</option>
							<option value="HER.cName" {if $group_by=="HER.cName"}selected="selected"{/if}>{#sortby4#}</option>
							<option value="SPR.cNameDeutsch" {if $group_by=="SPR.cNameDeutsch"}selected="selected"{/if}>{#language#}</option>
							</select></p>
                         <p><label for="order">{#sortOptions#}</label>
                           <select name="order" id="order" class="combo">
													<option value="DESC" {if $order=="DESC"}selected="selected"{/if}>{#desc#}</option>
													<option value="ASC" {if $order=="ASC"}selected="selected"{/if}>{#asc#}</option>
												</select></p> 
                          <p><label for="limit">{#siteOptions#}</label>
              				<select name="limit" id="limit" class="combo">
							<option value="10" {if $limit=="10"}selected="selected"{/if}>10</option>
							<option value="25" {if $limit=="25"}selected="selected"{/if}>25</option>
							<option value="50" {if $limit=="50"}selected="selected"{/if}>50</option>
							<option value="100" {if $limit=="100"}selected="selected"{/if}>100</option>
							<option value="1" {if $limit=="1"}selected="selected"{/if}>&nbsp;</option>
							</select></p>                      
                                                
            </fieldset>     
          </div>      
    <p class="submit"><input type="submit" name="show" value="{#submit#}" /><input type="submit" name="export" value="{#export#}" /></p>
          </form>
			</div>
            
           <div id="example-1" class="post">
           <div id="statisticTable">
           {if $group_by}
                    <table>
                    <thead>
                    <tr>
                    <th class="th-1">{#number#}</th>
         {if $group_by == "ART.cName"}
                    <th class="th-2">{#restrictions#}</th>
                    {/if}
		{if $group_by == "HER.cName"}
                    <th class="th-3">{#sortby4#}</th>
		{/if}
		{if $group_by == "SPR.cNameDeutsch"}
                    <th class="th-4">{#language#}</th>
                    {/if}
                    </tr>
                    </thead>
                    <tbody>
                   {foreach name=query from=$arQuery item=row}
                   <tr class="tab_bg{$smarty.foreach.query.iteration%2}">
                    <td class="TD1">{$row->N_ANZAHL}</td>
         {if $group_by == "ART.cName"}
                    <td class="TD2">{$row->cName}</td>
		{/if}
		{if $group_by == "HER.cName"} 
                    <td class="TD3">{if $row->HER_URL}<a href="{$row->HER_URL}" rel="external">{/if}{$row->HER_NAME}{if $row->HER_URL}</a>{/if}</td>
		{/if}
		{if $group_by == "SPR.cNameDeutsch"}
                    <td class="TD4">{$row->VER_SPRACHE}</td>
		{/if}
                    </tr>
         {/foreach}
                    </tbody>
                    </table>
         {else}
                    <table>
                    <thead>
                    <tr>
                    <th class="th-1">{#dateOfQuestion#}</th>
                    <th class="th-2">{#articleNo1#}</th>
                    <th class="th-3">{#productName#}</th>
                    <th class="th-4">{#sortby4#}</th>
                    <th class="th-5">{#language#}</th>
                    {if $filter=="verfuegbar"}
                    <th class="th-6">{#productShipping#}</th>
                    {/if}
                    </tr>
                    </thead>
                    <tbody>
             {foreach name=query from=$arQuery item=row}
                   <tr class="tab_bg{$smarty.foreach.query.iteration%2}">
                    <td class="TD1">{$row->dErstellt}</td>
                    <td class="TD2">{$row->cArtNr}</td>
                    <td class="TD3">{if $row->cSeo}<a href="../{$row->cSeo}" rel="external">{/if}{$row->cName}{if $row->cSeo}</a>{/if}</td>
                    <td class="TD4">{if $row->HER_URL}<a href="{$row->HER_URL}" rel="external">{/if}{$row->HER_NAME}{if $row->HER_URL}</a>{/if}</td>
                    <td class="TD5">{$row->VER_SPRACHE}</td>
                    {if $filter=="verfuegbar"}
                    <td class="TD6">{if $row->nStatus=="1"}Y{/if}</td>
                    {/if}
                    </tr>
              {/foreach}
                    </tbody>
                    </table>
                    {/if}
                    
          </div> 
			</div>

{include file='tpl_inc/footer.tpl'}