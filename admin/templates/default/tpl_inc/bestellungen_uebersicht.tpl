{*
-------------------------------------------------------------------------------
JTL-Shop 3
File: bestellungen_uebersicht.tpl, smarty template inc file

page for JTL-Shop 3
Admin

Author: daniel.boehmer@jtl-software.de, JTL-Software
http://www.jtl-software.de

Copyright (c) 2010 JTL-Software
-------------------------------------------------------------------------------
*}

{include file="tpl_inc/seite_header.tpl" cTitel=#order# cBeschreibung=#orderDesc# cDokuURL=#orderURL#}
<div id="content">
	 {if isset($cHinweis) && $cHinweis|count_characters > 0}
		  <p class="box_success">{$cHinweis}</p>
	 {/if}
	 {if isset($cFehler) && $cFehler|count_characters > 0}			
		  <p class="box_error">{$cFehler}</p>
	 {/if}

	 {if $oBestellung_arr|@count > 0 && $oBestellung_arr}
		  <div class=" block clearall">
				<div class="left">
					 {if $oBlaetterNaviUebersicht->nAktiv == 1}
						  <div class="pages tright">
								<span class="pageinfo">{#page#}: <strong>{$oBlaetterNaviUebersicht->nVon}</strong> - {$oBlaetterNaviUebersicht->nBis} {#from#} {$oBlaetterNaviUebersicht->nAnzahl}</span>
								<a class="back" href="bestellungen.php?s1={$oBlaetterNaviUebersicht->nVoherige}{if isset($cSuche) && $cSuche|count_characters > 0}&cSuche={$cSuche}{/if}">&laquo;</a>
								{if $oBlaetterNaviUebersicht->nAnfang != 0}<a href="bestellungen.php?s1={$oBlaetterNaviUebersicht->nAnfang}{if isset($cSuche) && $cSuche|count_characters > 0}&cSuche={$cSuche}{/if}">{$oBlaetterNaviUebersicht->nAnfang}</a> ... {/if}
									 {foreach name=blaetternavi from=$oBlaetterNaviUebersicht->nBlaetterAnzahl_arr item=Blatt}
										  <a class="page {if $oBlaetterNaviUebersicht->nAktuelleSeite == $Blatt}active{/if}" href="bestellungen.php?s1={$Blatt}{if isset($cSuche) && $cSuche|count_characters > 0}&cSuche={$cSuche}{/if}">{$Blatt}</a>
									 {/foreach}
								
								{if $oBlaetterNaviUebersicht->nEnde != 0}
									 ... <a class="page" href="bestellungen.php?s1={$oBlaetterNaviUebersicht->nEnde}{if isset($cSuche) && $cSuche|count_characters > 0}&cSuche={$cSuche}{/if}">{$oBlaetterNaviUebersicht->nEnde}</a>
								{/if}
								<a class="next" href="bestellungen.php?s1={$oBlaetterNaviUebersicht->nNaechste}{if isset($cSuche) && $cSuche|count_characters > 0}&cSuche={$cSuche}{/if}">&raquo;</a>
						  </div>
					 {/if}
				</div>
				<div class="right">
                <form name="bestellungen" method="post" action="bestellungen.php">
                     <input type="hidden" name="{$session_name}" value="{$session_id}" />
                     <input type="hidden" name="Suche" value="1" />
					 <label for="orderSearch">{#orderSearchItem#}:</label>
					 <input name="cSuche" type="text" value="{if isset($cSuche)}{$cSuche}{/if}" id="orderSearch" />
					 <button name="submitSuche" type="submit" class="button blue">{#orderSearchBTN#}</button>
                </form>
				</div>
		  </div>
	 
		  <div class="category">{#order#}</div>
		  
		  <form name="bestellungen" method="post" action="bestellungen.php">
				<input type="hidden" name="{$session_name}" value="{$session_id}" />
				<input type="hidden" name="zuruecksetzen" value="1" />
				{if isset($cSuche) && $cSuche|count_characters > 0}
					 <input type="hidden" name="cSuche" value="{$cSuche}" />
				{/if}
				
				<table class="list">
					 <thead>
						  <tr>
								<th></th>
								<th class="tleft">{#orderNumber#}</th>
								<th class="tleft">{#orderCostumer#}</th>
								<th class="tleft">{#orderShippingName#}</th>
								<th class="tleft">{#orderPaymentName#}</th>
								<th>{#orderWawiPickedUp#}</th>                        
								<th>{#orderSum#}</th>
								<th class="tright">{#orderDate#}</th>
						  </tr>
					 </thead>
					 <tbody>
						  {foreach name=bestellungen from=$oBestellung_arr item=oBestellung}
								<tr class="tab_bg{$smarty.foreach.bestellungen.iteration%2}">
									 <td class="check">{if $oBestellung->cAbgeholt == "Y" && $oBestellung->cZahlungsartName != 'Amazon Payment'}<input type="checkbox" name="kBestellung[]" value="{$oBestellung->kBestellung}" />{/if}</td>
									 <td>{$oBestellung->cBestellNr}</td>
									 <td>{if $oBestellung->oKunde->cVorname || $oBestellung->oKunde->cNachname || $oBestellung->oKunde->cFirma}{$oBestellung->oKunde->cVorname} {$oBestellung->oKunde->cNachname}{if isset($oBestellung->oKunde->cFirma) && $oBestellung->oKunde->cFirma|count_characters > 0} ({$oBestellung->oKunde->cFirma}){/if}{else}{#noAccount#}{/if}</td>
									 <td>{$oBestellung->cVersandartName}</td>
									 <td>{$oBestellung->cZahlungsartName}</td>
									 <td class="tcenter">{if $oBestellung->cAbgeholt == "Y"}{#yes#}{else}{#no#}{/if}</td>                        
									 <td class="tcenter">{$oBestellung->WarensummeLocalized[0]}</td>
									 <td class="tright">{$oBestellung->dErstelldatum_de}</td>                        
								</tr>
						  {/foreach}
					 </tbody>
					 <tfoot>
						  <tr>
							 <td class="check"><input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);" /></td>
								<td colspan="8"><label for="ALLMSGS">Alle ausw&auml;hlen</label></td>
						 </tr>
					 </tfoot>
				</table>
				<div class="save_wrapper">
					 <button name="zuruecksetzenBTN" type="submit" class="button orange">{#orderPickedUpResetBTN#}</button>
				</div>
		  </form>
	 {/if}
</div>