{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: versandarten_uebersicht.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: JTL-Software-GmbH
	http://www.jtl-software.de
	
	Copyright (c) 2007 JTL-Software
    

-------------------------------------------------------------------------------
*}
 
 <script type="text/javascript">
{literal}
function confirmDelete(cName) {
	 return confirm('Sind Sie sicher das Sie die Versandart "' + cName + '" löschen möchten?');
}
{/literal}
</script>

{include file="tpl_inc/seite_header.tpl" cTitel=#shippingmethods# cBeschreibung=#isleListsHint# cDokuURL=#shippingmethodsURL#}
<div id="content">
	 {foreach name=versandarten from=$versandarten item=versandart}
		  <table class="list container">
				<thead>
					 <tr>				
						  <th colspan="2" class="tleft">{#activeShippingmethods#}</th>				
					 </tr>
				</thead>
				<tbody>
					 <tr>
						  <td style="width:160px">{#shippingTypeName#}</td>
						  <td>
								{$versandart->cName} ({foreach name=versandartsprache from=$versandart->oVersandartSprachen_arr item=oVersandartSprachen}{$oVersandartSprachen->cName}{if !$smarty.foreach.versandartsprache.last}, {/if}{/foreach})
						  </td>
					 </tr>
					 <tr>
						  <td>{#countries#}</td>
						  <td>
								{foreach name=laender from=$versandart->land_arr item=land}
									 <a href="versandarten.php?{$SID}&amp;zuschlag=1&amp;kVersandart={$versandart->kVersandart}&amp;cISO={$land}" class="country {if isset($versandart->zuschlag_arr[$land])}addition{/if}">{$land}</a>
								{/foreach}
						  </td>
					 </tr>
					 <tr>
						  <td>{#shippingclasses#}</td>
						  <td>
								{foreach name=versandklassen from=$versandart->versandklassen item=versandklasse}
									 {$versandklasse}
								{/foreach}
						  </td>
					 </tr>
					 <tr>
						  <td>{#customerclass#}</td>
						  <td>
								{foreach name=versandklassen from=$versandart->cKundengruppenName_arr item=cKundengruppenName}
									 {$cKundengruppenName}
								{/foreach}
						  </td>
					 </tr>
                     <tr>
						  <td>{#taxshippingcosts#}</td>
						  <td>{if $versandart->eSteuer == "netto"}{#net#}{else}{#gross#}{/if}</td>
					 </tr>
					 <tr>
					 	   <td>{#shippingtime#}</td>
					 	   <td>{$versandart->nMinLiefertage} - {$versandart->nMaxLiefertage} Tage</td>
					 </tr>
					 <tr>
						  <td>{#paymentMethods#}</td>
						  <td>
								{foreach name=zahlungsarten from=$versandart->versandartzahlungsarten item=zahlungsart}
									 {$zahlungsart->zahlungsart->cName}{if isset($zahlungsart->zahlungsart->cAnbieter) && $zahlungsart->zahlungsart->cAnbieter|count_characters > 0} ({$zahlungsart->zahlungsart->cAnbieter}){/if} {if $zahlungsart->fAufpreis!=0}{if $zahlungsart->cAufpreisTyp != "%"}{getCurrencyConversionSmarty fPreisBrutto=$zahlungsart->fAufpreis bSteuer=false}{else}{$zahlungsart->fAufpreis}%{/if}{/if}<br />
								{/foreach}
						  </td>
					 </tr>
					 <tr>
						  <td>
								{if $versandart->versandberechnung->cModulId=="vm_versandberechnung_gewicht_jtl" || $versandart->versandberechnung->cModulId=="vm_versandberechnung_warenwert_jtl" || $versandart->versandberechnung->cModulId=="vm_versandberechnung_artikelanzahl_jtl"}
									 {#priceScale#}
								{elseif $versandart->versandberechnung->cModulId=="vm_versandkosten_pauschale_jtl"}
									 {#shippingPrice#}
								{/if}
						  </td>
						  <td>
								{if $versandart->versandberechnung->cModulId=="vm_versandberechnung_gewicht_jtl" || $versandart->versandberechnung->cModulId=="vm_versandberechnung_warenwert_jtl" || $versandart->versandberechnung->cModulId=="vm_versandberechnung_artikelanzahl_jtl"}
									 {foreach name=preisstaffel from=$versandart->versandartstaffeln item=versandartstaffel}
                                        {if $versandartstaffel->fBis != 999999999}
										  {#upTo#} {$versandartstaffel->fBis} {$versandart->einheit} {getCurrencyConversionSmarty fPreisBrutto=$versandartstaffel->fPreis bSteuer=false}<br />
                                        {/if}
									 {/foreach}
								{elseif $versandart->versandberechnung->cModulId=="vm_versandkosten_pauschale_jtl"}
									 {getCurrencyConversionSmarty fPreisBrutto=$versandart->fPreis bSteuer=false}
								{/if}
						  </td>
					 </tr>
					 {if $versandart->fVersandkostenfreiAbX>0}
					 <tr>
						  <td>{#freeFrom#}</td>
						  <td>{getCurrencyConversionSmarty fPreisBrutto=$versandart->fVersandkostenfreiAbX bSteuer=false}</td>
					 </tr>
					 {/if}
					 {if $versandart->fDeckelung>0}
					 <tr>
						  <td>{#maxCostsUpTo#}</td>
						  <td>{getCurrencyConversionSmarty fPreisBrutto=$versandart->fDeckelung bSteuer=false}</td>
					 </tr>
					 {/if}
					 <tfoot class="light">
						  <td></td>
						  <td>
								<a href="versandarten.php?{$SID}&amp;del={$versandart->kVersandart}" class="button remove" onclick="return confirmDelete('{$versandart->cName}');">{#deleteShippingType#}</a>
								<a href="versandarten.php?{$SID}&amp;edit={$versandart->kVersandart}" class="button edit">{#modifyShippingType#}</a>
								<a href="versandarten.php?{$SID}&amp;clone={$versandart->kVersandart}" class="button clone">{#cloneShippingType#}</a>
						  </td>
					 </tfoot>
				</tbody>
		  </table>
	 {/foreach}

	 <div id="settings">
		  <form name="versandart_neu" method="post" action="versandarten.php">
				<input type="hidden" name="{$session_name}" value="{$session_id}" />
				<input type="hidden" name="neu" value="1" />
				<div class="category">{#createShippingMethod#}</div>
				{foreach name=versandberechnungen from=$versandberechnungen item=versandberechnung}
					 <div class="item">
						  <div class="for">
								<input type="radio" id="l{$smarty.foreach.versandberechnungen.index}" name="kVersandberechnung" value="{$versandberechnung->kVersandberechnung}" {if $smarty.foreach.versandberechnungen.index == 0}checked="checked"{/if} />
								<label for="l{$smarty.foreach.versandberechnungen.index}">{$versandberechnung->cName}</label>
						  </div>
					 </div>
				{/foreach} 
				<div class="save_wrapper">
					 <input type="submit" value="{#createShippingMethod#}" class="button orange" />
				</div>
		  </form>
	 </div>
</div>