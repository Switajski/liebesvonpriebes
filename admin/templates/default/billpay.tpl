{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="billpay"}

{include file="tpl_inc/seite_header.tpl" cTitel=#billpay# cBeschreibung=#billpayDesc# cDokuURL=#billpayURL#}
<div id="content">
{if $cFehler|count_characters > 0}
	 <div class="box_error">{$cFehler}</div>
{else}
	 <div class="container">
		  <div class="tabber">
				<div class="tabbertab{if isset($cTab) && $cTab == 'uebersicht'} tabbertabdefault{/if}">
					 <h2>{#billpayOverview#}</h2>
					 <div id="settings">
						  {if isset($cFehlerBillpay) && $cFehlerBillpay|count_characters > 0}
								<br />
								<p class="box_error">{$cFehlerBillpay}</p>
						  {else}
								<div class="category first">Kauf auf Rechnung</div>
								<div class="item">
									<div class="name">Status</div><div class="for">{if $oRechnung->bAktiv}<span class="success">Aktiv</span>{else}<span class="error">Inaktiv</span>{/if}</div>
								</div>
								{if $oRechnung->bAktiv}
									 <div class="item">
										 <div class="name">Mindestbestellwert</div><div class="for">{$oRechnung->cValMin} &euro;</div>
									 </div>
									 <div class="item">
										 <div class="name">Maximaler Bestellwert</div><div class="for">{$oRechnung->cValMax} &euro;</div>
									 </div>
								{/if}
                        
								<div class="category first">Kauf auf Rechnung B2B</div>
								<div class="item">
									<div class="name">Status</div><div class="for">{if $oRechnungB2B->bAktiv}<span class="success">Aktiv</span>{else}<span class="error">Inaktiv</span>{/if}</div>
								</div>
								{if $oRechnungB2B->bAktiv}
									 <div class="item">
										 <div class="name">Mindestbestellwert</div><div class="for">{$oRechnungB2B->cValMin} &euro;</div>
									 </div>
									 <div class="item">
										 <div class="name">Maximaler Bestellwert</div><div class="for">{$oRechnungB2B->cValMax} &euro;</div>
									 </div>
								{/if}
								
								<div class="category">Lastschriftverfahren</div>
								<div class="item">
									<div class="name">Status</div><div class="for">{if $oLastschrift->bAktiv}<span class="success">Aktiv</span>{else}<span class="error">Inaktiv</span>{/if}</div>
								</div>
								{if $oLastschrift->bAktiv}
									 <div class="item">
										 <div class="name">Mindestbestellwert</div><div class="for">{$oLastschrift->cValMin} &euro;</div>
									 </div>
									 <div class="item">
										 <div class="name">Maximaler Bestellwert</div><div class="for">{$oLastschrift->cValMax} &euro;</div>
									 </div>
								{/if}
								
								<div class="category">Ratenzahlung</div>
								<div class="item">
									<div class="name">Status</div><div class="for">{if $oRatenzahlung->bAktiv}<span class="success">Aktiv</span>{else}<span class="error">Inaktiv</span>{/if}</div>
								</div>
								{if $oRatenzahlung->bAktiv}
									 <div class="item">
										 <div class="name">Mindestbestellwert</div><div class="for">{$oRatenzahlung->cValMin} &euro;</div>
									 </div>
									 <div class="item">
										 <div class="name">Maximaler Bestellwert</div><div class="for">{$oRatenzahlung->cValMax} &euro;</div>
									 </div>
								{/if}
						  {/if}
					 </div>
				</div>
				<div class="tabbertab{if isset($cTab) && $cTab == 'log'} tabbertabdefault{/if}">
					 <h2>{#billpayLog#}</h2>
					 
					 {if $oLog_arr|@count == 0}
						  <br />
						  <p class="box_info">{#noDataAvailable#}</p>
					 {else}
						  {if $oBlaetterNavi->nAktiv == 1}
								<div class="block clearall">
									 <div class="left">
												<div class="pages tright">
													 <span class="pageinfo">{#page#}: <strong>{$oBlaetterNavi->nVon}</strong> - {$oBlaetterNavi->nBis} {#from#} {$oBlaetterNavi->nAnzahl}</span>
													 <a class="back" href="billpay.php?s1={$oBlaetterNavi->nVoherige}&tab=log">&laquo;</a>
													 {if $oBlaetterNavi->nAnfang != 0}<a href="billpay.php?s1={$oBlaetterNavi->nAnfang}&tab=log">{$oBlaetterNavi->nAnfang}</a> ... {/if}
														  {foreach name=blaetternavi from=$oBlaetterNavi->nBlaetterAnzahl_arr item=Blatt}
																<a class="page {if $oBlaetterNavi->nAktuelleSeite == $Blatt}active{/if}" href="billpay.php?s1={$Blatt}&tab=log">{$Blatt}</a>
														  {/foreach}
													 
													 {if $oBlaetterNavi->nEnde != 0}
														  ... <a class="page" href="billpay.php?s1={$oBlaetterNavi->nEnde}&tab=log">{$oBlaetterNavi->nEnde}</a>
													 {/if}
													 <a class="next" href="billpay.php?s1={$oBlaetterNavi->nNaechste}&tab=log">&raquo;</a>
												</div>
									 </div>
									 <div class="right">
										  <a href="billpay.php?tab=log&del=1" class="button remove">Log l&ouml;schen</a>
									 </div>
								</div>
						  {else}
								<div class="container">
									 <a href="billpay.php?tab=log&del=1" class="button remove">Log l&ouml;schen</a>
								</div>
						  {/if}
						  
						  <div class="container">
								<table class="list">
									 <thead>
										  <th class="tleft">Meldung</th>
										  <th>Typ</th>
										  <th>Datum</th>
										  <th></th>
									 </thead>
									 <tbody>
										  {foreach from=$oLog_arr item="oLog"}
												<tr>
													 <td>{$oLog->cLog|nl2br}</td>
													 <td class="tcenter">
														  {if $oLog->nLevel == 1}
																Fehler
														  {elseif $oLog->nLevel == 2}
																Hinweis
														  {elseif $oLog->nLevel == 3}
																Debug
														  {else}
																Unbekannt
														  {/if}
													 </td>
													 <td class="tcenter">{$oLog->dDatum|date_format:"%d.%m.%Y - %H:%M:%S"}</td>
													 <td style="width:80px">
														  {if $oLog->cLogData|count_characters > 0}
																<a href="#" onclick="$('#data{$oLog->kZahlunglog}').toggle();return false;" class="button">anzeigen</a>
														  {/if}
													 </td>
												</tr>
												{if $oLog->cLogData|count_characters > 0}
													 {assign var="oKunde" value=$oLog->cLogData|unserialize}
													 <tr class="hidden" id="data{$oLog->kZahlunglog}">
														  <td colspan="4">
																{if $oKunde->kKunde > 0}
																	 <p><strong>Kdn:</strong> {$oKunde->kKunde}</p>
																{/if}
																<p><strong>Name:</strong> {$oKunde->cVorname} {$oKunde->cNachname}</p>
																<p><strong>Stra&szlig;e:</strong> {$oKunde->cStrasse} {$oKunde->cHausnummer}</p>
																<p><strong>Ort:</strong> {$oKunde->cPLZ} {$oKunde->cOrt}</p>
																<p><strong>E-Mail:</strong> {$oKunde->cMail}</p>
														  </td>
													 </tr>
												{/if}
										  {/foreach}
									 </tbody>
								</table>
						  </div>
					 {/if}
				</div>
		  </div>
	 </div>
{/if}
</div>

{include file='tpl_inc/footer.tpl'}