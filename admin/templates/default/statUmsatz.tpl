{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: statUmsatz.tpl, smarty template inc file
	
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
			<h2 class="title"><span>{#salesStatisticTitle#}</span></h2>
			<div class="content">
            <p>{#salesStatisticDesc#}</p>
            </div>
		</div>
        <div class="container">
			<form name="umsaetze_anzeigen" method="post" action="">
        <div id="payment">
          <p><label for="month">{#monthYear#}</label>
              <select name="month" id="month" class="combo" style="width:150px">
													<option value=""   {if $month<1}selected="selected"{/if}>{#allMonth#}</option>
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
                 <fieldset>
                 <legend>{#filters#}</legend>
               <p><label for="filter1">{#filter1#}</label>
          <input type="hidden" name="bNew" value="1" />
          <input class="checkfield" type="checkbox" id="filter1" name="filter[]" value="{#filter1#}" {if $FilterOffen=="1" || !$bNew}checked="checked"{/if} /></p> 
          <p><label for="filter2">{#filter2#}</label>
          <input type="hidden" name="bNew" value="1" />
          <input class="checkfield" type="checkbox" id="filter2" name="filter[]" value="{#filter2#}" {if $FilterInBearbeitung=="1" || !$bNew}checked="checked"{/if} /></p>
          <p><label for="filter3">{#filter3#}</label>
          <input type="hidden" name="bNew" value="1" />
          <input class="checkfield" type="checkbox" id="filter3" name="filter[]" value="{#filter3#}" {if $FilterBezahlt=="1" || !$bNew}checked="checked"{/if} /></p>
          <p><label for="filter4">{#filter4#}</label>
          <input type="hidden" name="bNew" value="1" />
          <input class="checkfield" type="checkbox" id="filter4" name="filter[]" value="{#filter3#}" {if $FilterBezahlt=="1" || !$bNew}checked="checked"{/if} /></p>
            </fieldset>     
          </div>      
    <p class="submit">
      <input type="submit" name="show" value="{#submit#}" />
      <input type="submit" name="export" value="{#export#}" /></p>
          </form>
			</div>
            
            {if $Umsatz}<br>
            <img src="includes/diagramm.php"><br>
			<div id="example-1" class="post">
            <div id="kupon"> 
                    <table>
                    <thead>
                    <tr>
                    {if $month}
                    <th class="th-1">{#day#}</th>
                    {else}
                    <th class="th-1">{#month#}</th>
                    {/if}
                    <th class="th-2">{#numbersOfOrder#}</th>
                    <th class="th-3">{#numbersOfProducts#}</th>
                    <th class="th-4">{#taxes#}</th>
                    <th class="th-5">{#shipping#}</th>
                    <th class="th-6">{#sales#}</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach name=umsaetze from=$Umsatz key=k item=umsatz}
                   {if $month}
                   <tr class="tab_bg{$smarty.foreach.umsaetze.iteration%2}">
                    <td class="TD1">{$k|string_format:"%02u"}.{$umsatz->MONAT|string_format:"%02u"}.{$umsatz->JAHR}</td>
                    {else}
                    <tr class="tab_bg{$smarty.foreach.umsaetze.iteration%2}" onclick="document.getElementsByName('month')[0].selectedIndex={$k}; document.umsaetze_anzeigen.submit();"
										 onmouseover="this.style.cursor='pointer'">
                    <td class="TD1">{$k|string_format:"%02u"}/{$umsatz->JAHR}</td>
					{/if}
                    <td class="TD2">{$umsatz->ANZBESTELL}</td>
                    <td class="TD3">{$umsatz->ANZPROD|string_format:"%d"}</td>
                    <td class="TD4">{$umsatz->STEUERN}</td>
                    <td class="TD5">{$umsatz->VERSAND}</td>
                    <td class="TD6">{$umsatz->UMSATZ}</td>
                    </tr>
                    {/foreach}
                    <tr style="empty-cells:hide;">
                    <td class="TD1" style="border-top:2px solid #3b3b3b;"><strong>{#complete#}</strong></td>
                    <td class="TD2" style="border-top:2px solid #3b3b3b;"><strong>{$nSummeBestellungen}</strong></td>
                    <td class="TD3" style="border-top:2px solid #3b3b3b;"><strong>{$nSummeProdukte|string_format:"%d"}</strong></td>
                    <td class="TD4" style="border-top:2px solid #3b3b3b;"><strong>{$nSummeSteuern}</strong></td>
                    <td class="TD5" style="border-top:2px solid #3b3b3b;"><strong>{$nSummeVersand}</strong></td>
                    <td class="TD6" style="border-top:2px solid #3b3b3b;"><strong>{$nSummeUmsatz}</strong></td>
                    </tr>
                    </tbody>
                    </table>
                    
          </div> 
			</div>
            {/if}
            

{include file='tpl_inc/footer.tpl'}