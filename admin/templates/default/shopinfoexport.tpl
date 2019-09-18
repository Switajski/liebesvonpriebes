{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: sitemapexport.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: NIclas Potthast niclas@jtl-software.de
	http://www.jtl-software.de
	Copyright (c) 2008 JTL-Software
    

-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="shopinfoExport"}

{include file="tpl_inc/seite_header.tpl" cTitel=#shopinfoExport# cBeschreibung=#shopinfoExportDesc# cDokuURL=#shopinfoExportURL#}
<div id="content">
	 
    {if isset($hinweis) && $hinweis|count_characters > 0}			
        <p class="box_success">{$hinweis}</p>
    {/if}
	 
    {if isset($errorNoWrite) && $errorNoWrite|count_characters > 0}			
        <p class="box_error">{$errorNoWrite}</p>
    {/if}
	 
<div class="box_info">
	 <p><input style="width:550px;" type="text" readonly="readonly" value="{$URL}"  /></p>
	 <p class="container">{#priceSearchEngines#}</p>
</div>

<p><a href="shopinfo.php" class="button blue">{#download#} {#xml#}</a></p>

<div class="container">
<form action="shopinfo.php" method="post">
<input type="hidden" name="post" value="1" />
<input type="hidden" name="update" value="1" /> 
<div id="shopinfoExport">
<table>
<thead>
 <tr>
 <th colspan="2" class="linkTemplateName">{#shopinfoExport#} {#options#}</th>
 </tr>
 </thead>
<tbody>
<tr class="tab-1_bg">
<td class="TD1"><strong>{#optionsIntervall#}</strong>
<p class="smallfont">{#optionsIntervallHelp#}</p></td>
<td class="TD2"><input  type="text" name="shopInfo_updateInterval" value="{$objShopInfo->shopInfo_updateInterval}" /></td>
</tr>
<tr class="tab-2_bg">
<td class="TD1"><strong>{#logoURL#}</strong>
<p class="smallfont">{#logoURLHelp#}</p></td>
<td class="TD2"><input  type="text" name="shopInfo_logoURL" value="{$objShopInfo->shopInfo_logoURL}" /></td>
</tr>
<tr class="tab-1_bg">
<td class="TD1"><strong>{#email#}</strong>
<p class="smallfont">{#emailHelp#}</p></td>
<td class="TD2"><input  type="text" name="shopInfo_publicMail" value="{$objShopInfo->shopInfo_publicMail}" /></td>
</tr>
<tr class="tab-2_bg">
<td class="TD1"><strong>{#privateEmail#}</strong>
<p class="smallfont">{#privateEmailHelp#}</p></td>
<td class="TD2"><input  type="text" name="shopInfo_privateMail" value="{$objShopInfo->shopInfo_privateMail}" /></td>
</tr>
<tr class="tab-1_bg">
<td class="TD1"><strong>{#telephone#}</strong>
<p class="smallfont">{#telephoneHelp#}</p></td>
<td class="TD2"><input  type="text" name="shopInfo_orderPhone" value="{$objShopInfo->shopInfo_orderPhone}" /></td>
</tr>
<tr class="tab-2_bg">
<td class="TD1"><strong>{#fax#}</strong>
<p class="smallfont">{#faxHelp#}</p></td>
<td class="TD2"><input  type="text" name="shopInfo_orderFax" value="{$objShopInfo->shopInfo_orderFax}" /></td>
</tr>
<tr class="tab-1_bg">
<td class="TD1"><strong>{#hotline#}</strong>
<p class="smallfont">{#hotlineHelp#}</p></td>
<td class="TD2"><input  type="text" name="shopInfo_hotlineNumber" value="{$objShopInfo->shopInfo_hotlineNumber}" /></td>
</tr>
<tr class="tab-2_bg">
<td class="TD1"><strong>{#minHotlinePrice#}</strong>
<p class="smallfont">{#minHotlinePriceHelp#}</p></td>
<td class="TD2"><input  type="text" name="shopInfo_costPerMinute" value="{$objShopInfo->shopInfo_costPerMinute}" /></td>
</tr>
<tr class="tab-1_bg">
<td class="TD1"><strong>{#telHotlinePrice#}</strong>
<p class="smallfont">{#telHotlinePriceHelp#}</p></td>
<td class="TD2"><input  type="text" name="shopInfo_costPerCall" value="{$objShopInfo->shopInfo_costPerCall}" /></td>
</tr>
<tr class="tab-2_bg">
<td class="TD1"><strong>{#installment#}</strong>
<p class="smallfont">{#installmentHelp#}</p></td>
<td class="TD2"><select name="shopInfo_installment" class="combo">
			 <option value="N" {if $objShopInfo->shopInfo_installment=="N"}selected="selected"{/if}>{#no#}</option>
			 <option value="Y" {if $objShopInfo->shopInfo_installment=="Y"}selected="selected"{/if}>{#yes#}</option>
		 </select></td>
</tr>
<tr class="tab-1_bg">
<td class="TD1"><strong>{#payitem#}</strong>
<p class="smallfont">{#payitemHelp#}</p></td>
<td class="TD2"><input  type="text" name="shopInfo_payItems" value="{$objShopInfo->shopInfo_payItems}" /></td>
</tr>
<tr class="tab-2_bg">
<td class="TD1"><strong>{#repairService#}</strong>
<p class="smallfont">{#repairServiceHelp#}</p></td>
<td class="TD2"><select name="shopInfo_repairservice" class="combo">
			 <option value="N" {if $objShopInfo->shopInfo_repairservice=="N"}selected="selected"{/if}>{#no#}</option>
			 <option value="Y" {if $objShopInfo->shopInfo_repairservice=="Y"}selected="selected"{/if}>{#yes#} {#without#}</option>
			 <option value="Y+" {if $objShopInfo->shopInfo_repairservice=="Y+"}selected="selected"{/if}>{#yes#} {#with#}</option>
		 </select></td>
</tr>
<tr class="tab-1_bg">
<td class="TD1"><strong>{#presentService#}</strong>
<p class="smallfont">{#presentServiceHelp#}</p></td>
<td class="TD2"><select name="shopInfo_giftservice" class="combo">
			 <option value="N" {if $objShopInfo->shopInfo_giftservice=="N"}selected="selected"{/if}>{#no#}</option>
			 <option value="Y" {if $objShopInfo->shopInfo_giftservice=="Y"}selected="selected"{/if}>{#yes#} {#without#}</option>
			 <option value="Y+" {if $objShopInfo->shopInfo_giftservice=="Y+"}selected="selected"{/if}>{#yes#} {#with#}</option>
		 </select></td>
</tr>
<tr class="tab-2_bg">
<td class="TD1"><strong>{#trackBack#}</strong>
<p class="smallfont">{#trackBackHelp#}</p></td>
<td class="TD2"><select name="shopInfo_orderTracking" class="combo">
			 <option value="N" {if $objShopInfo->shopInfo_orderTracking=="N"}selected="selected"{/if}>{#no#}</option>
			 <option value="Y" {if $objShopInfo->shopInfo_orderTracking=="Y"}selected="selected"{/if}>{#yes#}</option>
		 </select></td>
</tr>
<tr class="tab-1_bg">
<td class="TD1"><strong>{#deliverTrackBack#}</strong>
<p class="smallfont">{#deliverTrackBackHelp#}</p></td>
<td class="TD2"><select name="shopInfo_deliverTracking" class="combo">
			 <option value="N" {if $objShopInfo->shopInfo_deliverTracking=="N"}selected="selected"{/if}>{#no#}</option>
			 <option value="Y" {if $objShopInfo->shopInfo_deliverTracking=="Y"}selected="selected"{/if}>{#yes#}</option>
		 </select></td>
</tr>
<tr class="tab-2_bg">
<td class="TD1"><strong>{#installationAssistance#}</strong>
<p class="smallfont">{#installationAssistanceHelp#}</p></td>
<td class="TD2"><select name="shopInfo_installationAssistance" class="combo">
			 <option value="N" {if $objShopInfo->shopInfo_installationAssistance=="N"}selected="selected"{/if}>{#no#}</option>
			 <option value="Y" {if $objShopInfo->shopInfo_installationAssistance=="Y"}selected="selected"{/if}>{#yes#} {#without#}</option>
			 <option value="Y+" {if $objShopInfo->shopInfo_installationAssistance=="Y+"}selected="selected"{/if}>{#yes#} {#with#}</option>
		 </select></td>
</tr>
<tr class="tab-1_bg">
<td class="TD1"><strong>{#certificationItems#}</strong>
<p class="smallfont">{#certificationItemsHelp#}</p></td>
<td class="TD2"><input  type="text" name="shopInfo_certificationItems" value="{$objShopInfo->shopInfo_certificationItems}" /></td>
</tr>
<tr class="tab-2_bg">
<td class="TD1"><strong>{#trusteesItems#}</strong>
<p class="smallfont">{#trusteesItemsHelp#}</p></td>
<td class="TD2"><input  type="text" name="shopInfo_trusteesItems" value="{$objShopInfo->shopInfo_trusteesItems}" /></td>
</tr>
<tr class="tab-1_bg">
<td class="TD1"><strong>{#mapping#}</strong>
<p class="smallfont">{#mappingHelp#}</p></td>
<td class="TD2">
<table>
{foreach name=Kategorien from=$objKategorien item=Kategorie}
			 <tr>
					 <td class="TD1-a">
<span class="smallfont"><strong>{$Kategorie->katName}:</strong></span>
			 </td>
					 <td class="TD2-a" style="vertical-align:top;">
			 <select name="Mapping[]" class="combo">
			 {foreach name=Mappings from=$arMapping item=Mapping}
				 <option value="{$Kategorie->katID}_{$Mapping}" {if $Mapping==$Kategorie->mapName}selected="selected"{/if}>{$Mapping}</option>
			 {/foreach}
			 </select>
			 </td></tr>
		 {/foreach}
		 </table>
				</td>
</tr>
</tbody>
</table>
</div> 
 
  
<p class="submit">
<input type="submit" value="{#shopinfoExportSubmit#}" class="button orange" /></p>
  </form>
 </div>

{include file='tpl_inc/footer.tpl'}
