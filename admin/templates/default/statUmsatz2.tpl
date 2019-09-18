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
	<div class="post">
	<form name="umsaetze_anzeigen" method="post" action="">
	<div id="payment">
		<p><label for="month">{#monthYear#}</label>
		<select name="nMonat" id="month" class="combo" style="width:150px">
			<option value="0"   {if $nMonat<1}selected="selected"{/if}>{#allMonth#}</option>
			<option value="1" {if $nMonat==1}selected="selected"{/if}>{#january#}</option>
			<option value="2" {if $nMonat==2}selected="selected"{/if}>{#february#}</option>
			<option value="3" {if $nMonat==3}selected="selected"{/if}>{#march#}</option>
			<option value="4" {if $nMonat==4}selected="selected"{/if}>{#april#}</option>
			<option value="5" {if $nMonat==5}selected="selected"{/if}>{#may#}</option>
			<option value="6" {if $nMonat==6}selected="selected"{/if}>{#june#}</option>
			<option value="7" {if $nMonat==7}selected="selected"{/if}>{#july#}</option>
			<option value="8" {if $nMonat==8}selected="selected"{/if}>{#august#}</option>
			<option value="9" {if $nMonat==9}selected="selected"{/if}>{#september#}</option>
			<option value="10" {if $nMonat==10}selected="selected"{/if}>{#october#}</option>
			<option value="11" {if $nMonat==11}selected="selected"{/if}>{#november#}</option>
			<option value="12" {if $nMonat==12}selected="selected"{/if}>{#december#}</option>
		</select>

		<select name="nJahr" class="combo" style="width:100px">
         <!--<option value="0" {if $nJahr==$nAkuellesJahr}selected="selected"{/if}>{#allYears#}</option>-->
			<option value="{$nAkuellesJahr}" {if $nJahr==$nAkuellesJahr}selected="selected"{/if}>{$nAkuellesJahr}</option>
			<option value="{$nAkuellesJahr-1}" {if $nJahr==$nAkuellesJahr-1}selected="selected"{/if}>{$nAkuellesJahr-1}</option>
			<option value="{$nAkuellesJahr-2}" {if $nJahr==$nAkuellesJahr-2}selected="selected"{/if}>{$nAkuellesJahr-2}</option>
			<option value="{$nAkuellesJahr-3}" {if $nJahr==$nAkuellesJahr-3}selected="selected"{/if}>{$nAkuellesJahr-3}</option>
			<option value="{$nAkuellesJahr-4}" {if $nJahr==$nAkuellesJahr-4}selected="selected"{/if}>{$nAkuellesJahr-4}</option>
			<option value="{$nAkuellesJahr-5}" {if $nJahr==$nAkuellesJahr-5}selected="selected"{/if}>{$nAkuellesJahr-5}</option>
		</select>
		</p>
		<fieldset>
		<legend>{#filters#}</legend>
			<p><label for="filter1">{#filter1#}</label>
			<input class="checkfield" type="checkbox" id="filter1" name="bFilter_arr[bFilterOffen]" value="1" {if $bFilterOffen}checked="checked"{/if} /></p> 
			<p><label for="filter2">{#filter2#}</label>
			<input class="checkfield" type="checkbox" id="filter2" name="bFilter_arr[bFilterInBearbeitung]" value="1" {if $bFilterInBearbeitung}checked="checked"{/if} /></p>
			<p><label for="filter3">{#filter3#}</label>
			<input class="checkfield" type="checkbox" id="filter3" name="bFilter_arr[bFilterBezahlt]" value="1" {if $bFilterBezahlt}checked="checked"{/if} /></p>
			<p><label for="filter4">{#filter4#}</label>
			<input class="checkfield" type="checkbox" id="filter4" name="bFilter_arr[bFilterVersendet]" value="1" {if $bFilterVersendet}checked="checked"{/if} /></p>
		</fieldset>     
	</div>      
	<p class="submit">
	<input type="submit" name="show" value="{#submit#}" />
	<input type="submit" name="export" value="{#export#}" />
	</p>
	</form>
	</div>
            
   {if $oStatistik}
   <div id="example-1" class="post">
   <div id="kupon"> 
   <table>
      <thead>
      <tr>
      {*if $nMonat*}
      <th class="th-1">{#day#}</th>
      {*else*}
      <!--<th class="th-1">{#month#}</th>-->
      {*/if*}
      <th class="th-2">{#numbersOfOrder#}</th>
      <th class="th-3">{#numbersOfProducts#}</th>
      <th class="th-4">{#taxes#}</th>
      <th class="th-5">{#shipping#}</th>
      <th class="th-6">{#sales#}</th>
      </tr>
      </thead>
      <tbody>
      {foreach name=statistik from=$oStatistik->oUmsatz_arr key=k item=oUmsatz}
      {*if $nMonat*}
      <tr class="tab_bg{$smarty.foreach.statistik.iteration%2}">
      <td class="TD1">{$oUmsatz->nTag|string_format:"%02u"}.{$oUmsatz->nMonat|string_format:"%02u"}.{$oUmsatz->nJahr}</td>
      {*else*}
      <!--
      <tr class="tab_bg{$smarty.foreach.statistik.iteration%2}" onclick="document.getElementsByName('month')[0].selectedIndex={$k}; document.umsaetze_anzeigen.submit();"
       onmouseover="this.style.cursor='pointer'">
      <td class="TD1">{$k|string_format:"%02u"}/{$oStatistik->nJahr}</td>
      -->
      {*/if*}
      <td class="TD2">{$oUmsatz->nBestellungen}</td>
      <td class="TD3">{$oUmsatz->nAnzahl}</td>
      <td class="TD4">{$oUmsatz->nSteuern}</td>
      <td class="TD5">---</td>
      <td class="TD6">{$oUmsatz->nSumme}</td>
      </tr>
      {/foreach}
      <tr style="empty-cells:hide;">
      <td class="TD1" style="border-top:2px solid #3b3b3b;"><strong>{#complete#}</strong></td>
      <td class="TD2" style="border-top:2px solid #3b3b3b;"><strong>{$oStatistik->nBestellungen}</strong></td>
      <td class="TD3" style="border-top:2px solid #3b3b3b;"><strong>{$oStatistik->nAnzahlGesamt}</strong></td>
      <td class="TD4" style="border-top:2px solid #3b3b3b;"><strong>{$oStatistik->nSteuernGesamt}</strong></td>
      <td class="TD5" style="border-top:2px solid #3b3b3b;"><strong>---</strong></td>
      <td class="TD6" style="border-top:2px solid #3b3b3b;"><strong>{$oStatistik->nSumme}</strong></td>
      </tr>
      </tbody>
   </table>
   </div>
   </div>
   {/if}
            

{include file='tpl_inc/footer.tpl'}