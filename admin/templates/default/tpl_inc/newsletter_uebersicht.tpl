{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: newsletter_uebersicht.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}

{include file="tpl_inc/seite_header.tpl" cTitel=#newsletteroverview# cBeschreibung=#newsletterdesc# cDokuURL=#newsletterURL#}
<div id="content">
	
	{if isset($hinweis) && $hinweis|count_characters > 0}
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}
		<p class="box_error">{$fehler}</p>
	{/if}
	
	<div class="container">
	  <form name="sprache" method="post" action="newsletter.php">
	  <input type="hidden" name="{$session_name}" value="{$session_id}">
	  
	  <p class="txtCenter">
	  <label for="{#changeLanguage#}">{#changeLanguage#}:</strong></label>
		<input type="hidden" name="sprachwechsel" value="1">
		 <select id="{#changeLanguage#}" name="kSprache" class="selectBox" onchange="javascript:document.sprache.submit();">
		 {foreach name=sprachen from=$Sprachen item=sprache}
			<option value="{$sprache->kSprache}" {if $sprache->kSprache==$smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
		 {/foreach}
		 </select>
			 </p>
		</form>
		<br>
		
	  <div class="tabber">
				 
				 <div class="tabbertab{if isset($cTab) && $cTab == 'inaktiveabonnenten'} tabbertabdefault{/if}">
				 
					  <h2>{#newsletterSubscripterNotActive#}</h2>
					  
					  <!-- Übersicht Newsletterabonnenten -->
					  {if isset($oNewsletterEmpfaenger_arr) && $oNewsletterEmpfaenger_arr|@count > 0}
							<form name="suche" method="POST" action="newsletter.php">
							<input type="hidden" name="{$session_name}" value="{$session_id}" />
							<input type="hidden" name="inaktiveabonnenten" value="1" />
							<input type="hidden" name="tab" value="inaktiveabonnenten" />
							{if isset($cSucheInaktiv) && $cSucheInaktiv|count_characters > 0}
								 <input type="hidden" name="cSucheInaktiv" value="{$cSucheInaktiv}" />
							{/if}
							<input type="hidden" name="s1" value="{$oBlaetterNaviInaktiveAbonnenten->nAktuelleSeite}" />
									  
							<div id="payment">
								 <div id="tabellenLivesuche">
									  <table>
											<tr>
												 <th class="th-1">{#newsletterSubscriber#} {#newsletterSearch#}</th>                                        
											</tr>
											
											<tr>
												 <td>
													  <strong>{#newslettersubscriberSearch#}:</strong> <input name="cSucheInaktiv" type="text" value="{if isset($cSucheInaktiv) && $cSucheInaktiv|count_characters > 0}{$cSucheInaktiv}{/if}" />
													  <input name="submitInaktiveAbonnentenSuche" type="submit" class="button blue" value="{#newsletterSearchBTN#}" />
												 </td>
											</tr>
									  </table>
								 </div>
							</div>
							</form>
						  
							{if $oBlaetterNaviInaktiveAbonnenten->nAktiv == 1}
							<div class="content">
								 <p>
								 {$oBlaetterNaviInaktiveAbonnenten->nVon} - {$oBlaetterNaviInaktiveAbonnenten->nBis} {#from#} {$oBlaetterNaviInaktiveAbonnenten->nAnzahl}
								 {if $oBlaetterNaviInaktiveAbonnenten->nAktuelleSeite == 1}
									  << {#back#}
								 {else}
									  <a href="newsletter.php?s1={$oBlaetterNaviInaktiveAbonnenten->nVoherige}&tab=inaktiveabonnenten{if isset($cSucheInaktiv) && $cSucheInaktiv|count_characters > 0}&cSucheInaktiv={$cSucheInaktiv}{/if}&{$session_name}={$session_id}"><< {#back#}</a>
								 {/if}
								 
								 {if $oBlaetterNaviInaktiveAbonnenten->nAnfang != 0}<a href="newsletter.php?s1={$oBlaetterNaviInaktiveAbonnenten->nAnfang}&tab=inaktiveabonnenten{if isset($cSucheInaktiv) && $cSucheInaktiv|count_characters > 0}&cSucheInaktiv={$cSucheInaktiv}{/if}&{$session_name}={$session_id}">{$oBlaetterNaviInaktiveAbonnenten->nAnfang}</a> ... {/if}
								 {foreach name=blaetternavi from=$oBlaetterNaviInaktiveAbonnenten->nBlaetterAnzahl_arr item=Blatt}
									  {if $oBlaetterNaviInaktiveAbonnenten->nAktuelleSeite == $Blatt}[{$Blatt}]
									  {else}
											<a href="newsletter.php?s1={$Blatt}&tab=inaktiveabonnenten{if isset($cSucheInaktiv) && $cSucheInaktiv|count_characters > 0}&cSucheInaktiv={$cSucheInaktiv}{/if}&{$session_name}={$session_id}">{$Blatt}</a>
									  {/if}
								 {/foreach}
								 
								 {if $oBlaetterNaviInaktiveAbonnenten->nEnde != 0} ... <a href="newsletter.php?s1={$oBlaetterNaviInaktiveAbonnenten->nEnde}&tab=inaktiveabonnenten{if isset($cSucheInaktiv) && $cSucheInaktiv|count_characters > 0}&cSucheInaktiv={$cSucheInaktiv}{/if}&{$session_name}={$session_id}">{$oBlaetterNaviInaktiveAbonnenten->nEnde}</a>{/if}
								 
								 {if $oBlaetterNaviInaktiveAbonnenten->nAktuelleSeite == $oBlaetterNaviInaktiveAbonnenten->nSeiten}
									  {#next#} >>
								 {else}
									  <a href="newsletter.php?s1={$oBlaetterNaviInaktiveAbonnenten->nNaechste}&tab=inaktiveabonnenten{if isset($cSucheInaktiv) && $cSucheInaktiv|count_characters > 0}&cSucheInaktiv={$cSucheInaktiv}{/if}&{$session_name}={$session_id}">{#next#} >></a>
								 {/if}
								 
								 </p>
							</div>
							{/if}
					  
							<div id="payment">
								 <div id="tabellenLivesuche">
									  <form name="inaktiveabonnentenForm" method="POST" action="newsletter.php">
									  <input type="hidden" name="{$session_name}" value="{$session_id}" />
									  <input type="hidden" name="inaktiveabonnenten" value="1" />
									  <input type="hidden" name="tab" value="inaktiveabonnenten" />
									  {if isset($cSucheInaktiv) && $cSucheInaktiv|count_characters > 0}
											<input type="hidden" name="cSucheInaktiv" value="{$cSucheInaktiv}" />
									  {/if}
									  <input type="hidden" name="s1" value="{$oBlaetterNaviInaktiveAbonnenten->nAktuelleSeite}" />
									  
									  <table>
											<tr>
												 <th class="th-1">&nbsp;</th>
												 <th class="tleft">{#newslettersubscriberfirstname#}</th>
												 <th class="tleft">{#newslettersubscriberlastname#}</th>
												 <th class="tleft">{#newslettersubscriberCustomerGrp#}</th>
												 <th class="tleft">{#newslettersubscriberemail#}</th>
												 <th class="tcenter">{#newslettersubscriberdate#}</th>
											</tr>
									  {foreach name=newsletterletztenempfaenger from=$oNewsletterEmpfaenger_arr item=oNewsletterEmpfaenger}
											<tr class="tab_bg{$smarty.foreach.newsletterletztenempfaenger.iteration%2}">
												 <td class="tleft"><input name="kNewsletterEmpfaenger[]" type="checkbox" value="{$oNewsletterEmpfaenger->kNewsletterEmpfaenger}"></td>
												 <td class="tleft">{if $oNewsletterEmpfaenger->cVorname != ""}{$oNewsletterEmpfaenger->cVorname}{else}{$oNewsletterEmpfaenger->newsVorname}{/if}</td>
												 <td class="tleft">{if $oNewsletterEmpfaenger->cNachname != ""}{$oNewsletterEmpfaenger->cNachname}{else}{$oNewsletterEmpfaenger->newsNachname}{/if}</td>
												 <td class="tleft">{if isset($oNewsletterEmpfaenger->cName) && $oNewsletterEmpfaenger->cName|count_characters > 0}{$oNewsletterEmpfaenger->cName}{else}{#NotAvailable#}{/if}</td>
												 <td class="tleft">{$oNewsletterEmpfaenger->cEmail}{if $oNewsletterEmpfaenger->nAktiv == 0} *{/if}</td>
												 <td class="tcenter">{$oNewsletterEmpfaenger->Datum}</td>
											</tr>
									  {/foreach}
											<tr>
												 <td class="TD1"><input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);"></td>
												 <td colspan="6" class="TD7">{#globalSelectAll#}</td>
											</tr>
									  </table>
									  <p class="submit"><input name="abonnentfreischaltenSubmit" type="submit" value="{#newsletterUnlock#}" class="button orange"> <input class="button orange" name="abonnentloeschenSubmit" type="submit" value="{#newsletterdelete#}"></p>
									  </form>
								 </div>
							</div>
					  {else}
							<br/>{#noDataAvailable#}<br/><br/>
					  {/if}
					  
				 </div>           
            
            <div class="tabbertab{if isset($cTab) && $cTab == 'alleabonnenten'} tabbertabdefault{/if}">
				 
					  <h2>{#newsletterAllSubscriber#}</h2>
				  
				 {if isset($oAbonnenten_arr) && $oAbonnenten_arr|@count > 0}   
					  <form name="suche" method="POST" action="newsletter.php">
					  <input type="hidden" name="{$session_name}" value="{$session_id}" />
					  <input type="hidden" name="Suche" value="1" />
					  <input type="hidden" name="tab" value="alleabonnenten" />
					  <input type="hidden" name="s5" value="{$oBlaetterNaviAlleAbonnenten->nAktuelleSeite}" />
					  {if isset($cSucheAktiv) && $cSucheAktiv|count_characters > 0}
							<input type="hidden" name="cSucheAktiv" value="{$cSucheAktiv}" />
					  {/if}
								 
							<div id="payment">
								 <div id="tabellenLivesuche">
									  <table>
											<tr>
												 <th class="th-1">{#newsletterSubscriber#} {#newsletterSearch#}</th>                                        
											</tr>
											
											<tr>
												 <td>
													  <strong>{#newslettersubscriberSearch#}:</strong> <input name="cSucheAktiv" type="text" value="{if isset($cSucheAktiv) && $cSucheAktiv|count_characters > 0}{$cSucheAktiv}{/if}" />
													  <input name="submitSuche" type="submit" value="{#newsletterSearchBTN#}" class="button blue" />
												 </td>
											</tr>
									  </table>
								 </div>
							</div>
					  </form>
				 
					  {if $oBlaetterNaviAlleAbonnenten->nAktiv == 1}
					  <div class="content">
							<p>
							{$oBlaetterNaviAlleAbonnenten->nVon} - {$oBlaetterNaviAlleAbonnenten->nBis} {#from#} {$oBlaetterNaviAlleAbonnenten->nAnzahl}
							{if $oBlaetterNaviAlleAbonnenten->nAktuelleSeite == 1}
								 << {#back#}
							{else}
								 <a href="newsletter.php?s5={$oBlaetterNaviAlleAbonnenten->nVoherige}&tab=alleabonnenten{if isset($cSucheAktiv) && $cSucheAktiv|count_characters > 0}&cSucheAktiv={$cSucheAktiv}{/if}&{$session_name}={$session_id}"><< {#back#}</a>
							{/if}
							
							{if $oBlaetterNaviAlleAbonnenten->nAnfang != 0}<a href="newsletter.php?s5={$oBlaetterNaviAlleAbonnenten->nAnfang}&tab=alleabonnenten{if isset($cSucheAktiv) && $cSucheAktiv|count_characters > 0}&cSucheAktiv={$cSucheAktiv}{/if}&{$session_name}={$session_id}">{$oBlaetterNaviAlleAbonnenten->nAnfang}</a> ... {/if}
							{foreach name=blaetternavi from=$oBlaetterNaviAlleAbonnenten->nBlaetterAnzahl_arr item=Blatt}
								 {if $oBlaetterNaviAlleAbonnenten->nAktuelleSeite == $Blatt}[{$Blatt}]
								 {else}
									  <a href="newsletter.php?s5={$Blatt}&tab=alleabonnenten{if isset($cSucheAktiv) && $cSucheAktiv|count_characters > 0}&cSucheAktiv={$cSucheAktiv}{/if}&{$session_name}={$session_id}">{$Blatt}</a>
								 {/if}
							{/foreach}
							
							{if $oBlaetterNaviAlleAbonnenten->nEnde != 0} ... <a href="newsletter.php?s5={$oBlaetterNaviAlleAbonnenten->nEnde}&tab=alleabonnenten{if isset($cSucheAktiv) && $cSucheAktiv|count_characters > 0}&cSucheAktiv={$cSucheAktiv}{/if}&{$session_name}={$session_id}">{$oBlaetterNaviAlleAbonnenten->nEnde}</a>{/if}
							
							{if $oBlaetterNaviAlleAbonnenten->nAktuelleSeite == $oBlaetterNaviAlleAbonnenten->nSeiten}
								 {#next#} >>
							{else}
								 <a href="newsletter.php?s5={$oBlaetterNaviAlleAbonnenten->nNaechste}&tab=alleabonnenten{if isset($cSucheAktiv) && $cSucheAktiv|count_characters > 0}&cSucheAktiv={$cSucheAktiv}{/if}&{$session_name}={$session_id}">{#next#} >></a>
							{/if}
							
							</p>
					  </div>
					  {/if}
					  
					  <!-- Übersicht Newsletterhistory -->
					  <form method="POST" action="newsletter.php">
						  <input name="newsletterabonnent_loeschen" type="hidden" value="1">
						  <input type="hidden" name="{$session_name}" value="{$session_id}">
                          <input type="hidden" name="tab" value="alleabonnenten">
						  
					  <div id="payment">
							<div id="tabellenLivesuche">
							<table>
								 <tr>
									  <th class="th-1">&nbsp;</th>
									  <th class="tleft">{#newslettersubscribername#}</th>
									  <th class="tleft">{#newslettersubscriberCustomerGrp#}</th>
									  <th class="tleft">{#newslettersubscriberemail#}</th>
									  <th class="tcenter">{#newslettersubscriberdate#}</th>
									  <th class="tcenter">{#newslettersubscriberLastNewsletter#}</th>
								 </tr>
							{foreach name=newsletterabonnenten from=$oAbonnenten_arr item=oAbonnenten}
								 <tr class="tab_bg{$smarty.foreach.newsletterabonnenten.iteration%2}">
									  <td class="tleft"><input name="kNewsletterEmpfaenger[]" type="checkbox" value="{$oAbonnenten->kNewsletterEmpfaenger}"></td>
									  <td class="tleft">{$oAbonnenten->cVorname} {$oAbonnenten->cNachname}</td>
									  <td class="tleft">{$oAbonnenten->cName}</td>
									  <td class="tleft">{$oAbonnenten->cEmail}</td>
									  <td class="tcenter">{$oAbonnenten->dEingetragen_de}</td>
									  <td class="tcenter">{$oAbonnenten->dLetzterNewsletter_de}</td>
								 </tr>
							{/foreach}
								 <tr>
									  <td class="TD1"><input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);"></td>
									  <td colspan="6" class="TD7">{#globalSelectAll#}</td>
								 </tr>
							</table>
							</div>
					  </div>
					  <p class="submit"><input name="loeschen" type="submit" value="{#newsletterdelete#}" class="button orange"></p>
					  </form>
				 {else}
					  <br/>{#noDataAvailable#}<br/><br/>
                      {if isset($cSucheAktiv) && $cSucheAktiv|count_characters > 0}
                            <form method="POST" action="newsletter.php">
                                <input name="tab" type="hidden" value="alleabonnenten" />
                                <input name="submitAbo" type="submit" value="{#newsletterNewSearch#}" class="button orange" />
                            </form>
                      {/if}
				 {/if}                  
					  
				 </div>
             
            <div class="tabbertab{if isset($cTab) && $cTab == 'neuerabonnenten'} tabbertabdefault{/if}">
               <h2>{#newsletterNewSubscriber#}</h2>

               <form method="post" action="newsletter.php">
               <input type="hidden" name="newsletterabonnent_neu" value="1">
               <input name="tab" type="hidden" value="neuerabonnenten">
               
               <div class="category first">
                  {#newsletterNewSubscriber#}
               </div>
               
               <div class="settings">
                  <p>
                     <label for="cAnrede">{#newslettersubscriberanrede#}</label>
                     <select name="cAnrede" id="cAnrede">
                        <option value="m">Herr</option>
                        <option value="w">Frau</option>
                     </select>
                  </p>
                  <p>
                     <label for="cVorname">{#newslettersubscriberfirstname#}</label>
                     <input type="text" name="cVorname" id="cVorname" value="{$oNewsletter->cVorname}" />
                  </p>
                  <p>
                     <label for="cNachname">{#newslettersubscriberlastname#}</label>
                     <input type="text" name="cNachname" id="cNachname" value="{$oNewsletter->cNachname}" />
                  </p>
                  <p>
                     <label for="cEmail">{#newslettersubscriberemail#}</label>
                     <input type="text" name="cEmail" id="cEmail" value="{$oNewsletter->cEmail}" />
                  </p>
                  <p>
                     <label for="kSprache">{#newslettersubscriberlang#}</label>
                     <select name="kSprache" id="kSprache">
                        {foreach from=$Sprachen item=oSprache}
                           <option value="{$oSprache->kSprache}">{$oSprache->cNameDeutsch}</option>
                        {/foreach}
                     </select>
                  </p>
               </div>

               <p class="submit">
                  <input name="speichern" type="submit" value="{#save#}" class="button orange" />
               </p>
               </form>
            </div>
				 <div class="tabbertab{if isset($cTab) && $cTab == 'newsletterqueue'} tabbertabdefault{/if}">
				 
                  <h2>{#newsletterqueue#}</h2>
					  
					  <!-- Übersicht NewsletterQueue -->
					  {if isset($oNewsletterQueue_arr) && $oNewsletterQueue_arr|@count > 0}
						  <form method="POST" action="newsletter.php">
						  <input type="hidden" name="{$session_name}" value="{$session_id}">
						  <input name="newsletterqueue" type="hidden" value="1">
						  <input name="tab" type="hidden" value="newsletterqueue">
						  <input name="s2" type="hidden" value="{$oBlaetterNaviNLWarteschlage->nAktuelleSeite}">

						  {if $oBlaetterNaviNLWarteschlage->nAktiv == 1}
							<div class="content">
								 <p>
								 {$oBlaetterNaviNLWarteschlage->nVon} - {$oBlaetterNaviNLWarteschlage->nBis} {#from#} {$oBlaetterNaviNLWarteschlage->nAnzahl}
								 {if $oBlaetterNaviNLWarteschlage->nAktuelleSeite == 1}
									  << {#back#}
								 {else}
									  <a href="newsletter.php?s2={$oBlaetterNaviNLWarteschlage->nVoherige}&tab=newsletterqueue&{$session_name}={$session_id}"><< {#back#}</a>
								 {/if}
								 
								 {if $oBlaetterNaviNLWarteschlage->nAnfang != 0}<a href="newsletter.php?s2={$oBlaetterNaviNLWarteschlage->nAnfang}&tab=newsletterqueue&{$session_name}={$session_id}">{$oBlaetterNaviNLWarteschlage->nAnfang}</a> ... {/if}
								 {foreach name=blaetternavi from=$oBlaetterNaviNLWarteschlage->nBlaetterAnzahl_arr item=Blatt}
									  {if $oBlaetterNaviNLWarteschlage->nAktuelleSeite == $Blatt}[{$Blatt}]
									  {else}
											<a href="newsletter.php?s2={$Blatt}&tab=newsletterqueue&{$session_name}={$session_id}">{$Blatt}</a>
									  {/if}
								 {/foreach}
								 
								 {if $oBlaetterNaviNLWarteschlage->nEnde != 0} ... <a href="newsletter.php?s2={$oBlaetterNaviNLWarteschlage->nEnde}&tab=newsletterqueue&{$session_name}={$session_id}">{$oBlaetterNaviNLWarteschlage->nEnde}</a>{/if}
								 
								 {if $oBlaetterNaviNLWarteschlage->nAktuelleSeite == $oBlaetterNaviNLWarteschlage->nSeiten}
									  {#next#} >>
								 {else}
									  <a href="newsletter.php?s2={$oBlaetterNaviNLWarteschlage->nNaechste}&tab=newsletterqueue&{$session_name}={$session_id}">{#next#} >></a>
								 {/if}
								 
								 </p>
							</div>
							{/if}
							
							<div id="payment">
								 <div id="tabellenLivesuche">
								 <table>
									  <tr>
											<th class="th-1" style="width: 4%;">&nbsp;</th>
											<th class="th-2" style="width: 40%;">{#newsletterqueuesubject#}</th>
											<th class="th-3" style="width: 30%;">{#newsletterqueuedate#}</th>
											<th class="th-4" style="width: 26%;">{#newsletterqueueimprovement#}</th>
											<th class="th-5" style="width: 26%;">{#newsletterqueuecount#}</th>
											<th class="th-6" style="width: 26%;">{#newsletterqueuecustomergrp#}</th>
									  </tr>
								 {foreach name=newsletterqueue from=$oNewsletterQueue_arr item=oNewsletterQueue}
						{if isset($oNewsletterQueue->nAnzahlEmpfaenger) && $oNewsletterQueue->nAnzahlEmpfaenger > 0}
									  <tr class="tab_bg{$smarty.foreach.newsletterqueue.iteration%2}">
											<td class="TD1"><input name="kNewsletterQueue[]" type="checkbox" value="{$oNewsletterQueue->kNewsletterQueue}"></td>
											<td class="TD2">{$oNewsletterQueue->cBetreff}</td>
											<td class="TD3">{$oNewsletterQueue->Datum}</td>
											<td class="TD4">{$oNewsletterQueue->nLimitN}</td>
											<td class="TD5">{$oNewsletterQueue->nAnzahlEmpfaenger}</td>
											<td class="TD6">                        
												 {foreach name=kundengruppen from=$oNewsletterQueue->cKundengruppe_arr item=cKundengruppe}
													  {if $cKundengruppe == "0"}Newsletterempf&auml;nger ohne Kundenkonto{if !$smarty.foreach.kundengruppen.last}, {/if}{/if}
													  {foreach name=kundengruppe from=$oKundengruppe_arr item=oKundengruppe}
															{if $cKundengruppe == $oKundengruppe->kKundengruppe}{$oKundengruppe->cName}{if !$smarty.foreach.kundengruppen.last}, {/if}{/if}
													  {/foreach}
												 {/foreach}
											</td>
									  </tr>
						{/if}
								 {/foreach}
									  <tr>
											<td class="TD1"><input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);"></td>
											<td colspan="6" class="TD7">{#globalSelectAll#}</td>
									  </tr>
								 </table>
								 </div>
								 
								 <p class="submit"><input name="loeschen" type="submit" value="{#newsletterdelete#}" class="button orange"></p>
							</div>
							</form>
					  {else}
							<br/>{#noDataAvailable#}<br/><br/>
					  {/if}
					  
				 </div>
				 
				 <div class="tabbertab{if isset($cTab) && $cTab == 'newslettervorlagen'} tabbertabdefault{/if}">
					  <h2>{#newsletterdraft#}</h2>
					  
					  <!-- Übersicht Newslettervorlagen -->
					  <form method="POST" action="newsletter.php">
					  <input type="hidden" name="{$session_name}" value="{$session_id}">
					  <input name="newslettervorlagen" type="hidden" value="1">
					  <input name="tab" type="hidden" value="newslettervorlagen">
					  <input name="s3" type="hidden" value="{$oBlaetterNaviNLVorlagen->nAktuelleSeite}">
					  
				 {if isset($oNewsletterVorlage_arr) && $oNewsletterVorlage_arr|@count > 0}  
					  {if $oBlaetterNaviNLVorlagen->nAktiv == 1}
					  <div class="content">
							<p>
							{$oBlaetterNaviNLVorlagen->nVon} - {$oBlaetterNaviNLVorlagen->nBis} {#from#} {$oBlaetterNaviNLVorlagen->nAnzahl}
							{if $oBlaetterNaviNLVorlagen->nAktuelleSeite == 1}
								 << {#back#}
							{else}
								 <a href="newsletter.php?s3={$oBlaetterNaviNLVorlagen->nVoherige}&tab=newslettervorlagen&{$session_name}={$session_id}"><< {#back#}</a>
							{/if}
							
							{if $oBlaetterNaviNLVorlagen->nAnfang != 0}<a href="newsletter.php?s3={$oBlaetterNaviNLVorlagen->nAnfang}&tab=newslettervorlagen&{$session_name}={$session_id}">{$oBlaetterNaviNLVorlagen->nAnfang}</a> ... {/if}
							{foreach name=blaetternavi from=$oBlaetterNaviNLVorlagen->nBlaetterAnzahl_arr item=Blatt}
								 {if $oBlaetterNaviNLVorlagen->nAktuelleSeite == $Blatt}[{$Blatt}]
								 {else}
									  <a href="newsletter.php?s3={$Blatt}&tab=newslettervorlagen&{$session_name}={$session_id}">{$Blatt}</a>
								 {/if}
							{/foreach}
							
							{if $oBlaetterNaviNLVorlagen->nEnde != 0} ... <a href="newsletter.php?s3={$oBlaetterNaviNLVorlagen->nEnde}&tab=newslettervorlagen&{$session_name}={$session_id}">{$oBlaetterNaviNLVorlagen->nEnde}</a>{/if}
							
							{if $oBlaetterNaviNLVorlagen->nAktuelleSeite == $oBlaetterNaviNLVorlagen->nSeiten}
								 {#next#} >>
							{else}
								 <a href="newsletter.php?s3={$oBlaetterNaviNLVorlagen->nNaechste}&tab=newslettervorlagen&{$session_name}={$session_id}">{#next#} >></a>
							{/if}
							
							</p>
					  </div>
					  {/if}
				 {/if}
					
				 {if isset($oNewsletterVorlage_arr) && $oNewsletterVorlage_arr|@count > 0}      
					  <div id="payment">
							<div id="tabellenLivesuche">                    
							<table>
								 <tr>
									  <th class="th-1">&nbsp;</th>
									  <th class="th-2">{#newsletterdraftname#}</th>
									  <th class="th-3">{#newsletterdraftsubject#}</th>
							<th class="th-4">{#newsletterdraftStdShort#}</th>								
									  <th class="th-5" style="width: 15em;">{#newsletterdraftoptions#}</th>
								 </tr>
							{foreach name=newslettervorlage from=$oNewsletterVorlage_arr item=oNewsletterVorlage}
								 <tr class="tab_bg{$smarty.foreach.newslettervorlage.iteration%2}">
									  <td class="TD1"><input name="kNewsletterVorlage[]" type="checkbox" value="{$oNewsletterVorlage->kNewsletterVorlage}"></td>
									  <td class="TD2">{$oNewsletterVorlage->cName}</td>
									  <td class="TD3">{$oNewsletterVorlage->cBetreff}</td>
							<td class="TD4">
							{if $oNewsletterVorlage->kNewslettervorlageStd > 0}
								{#yes#}
							{else}
								{#no#}
							{/if}
							</td>
									  <td class="TD5">
								[<a href="newsletter.php?&vorschau={$oNewsletterVorlage->kNewsletterVorlage}&iframe=1&tab=newslettervorlagen&{$session_name}={$session_id}">{#newsletterPreview#}</a>]<br />
							{if $oNewsletterVorlage->kNewslettervorlageStd > 0}																		
											[<a href="newsletter.php?newslettervorlagenstd=1&editieren={$oNewsletterVorlage->kNewsletterVorlage}&tab=newslettervorlagen&{$session_name}={$session_id}">{#newsletteredit#}</a>]<br />
							{else}
								[<a href="newsletter.php?newslettervorlagen=1&editieren={$oNewsletterVorlage->kNewsletterVorlage}&tab=newslettervorlagen&{$session_name}={$session_id}">{#newsletteredit#}</a>]<br />
							{/if}
											[<a href="newsletter.php?newslettervorlagen=1&vorbereiten={$oNewsletterVorlage->kNewsletterVorlage}&tab=newslettervorlagen&{$session_name}={$session_id}">{#newsletterprepare#}</a>]
									  </td>
								 </tr>
							{/foreach}
								 <tr>
									  <td class="TD1"><input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);"></td>
									  <td colspan="6" class="TD7">{#globalSelectAll#}</td>
								 </tr>
							</table>                    
							</div>
					  </div>
				 {else}
					  <br/>{#noDataAvailable#}<br/><br/>
				 {/if}
					  <p class="submit"><input name="vorlage_erstellen" class="button orange" type="submit" value="{#newsletterdraftcreate#}">{if isset($oNewsletterVorlage_arr) && $oNewsletterVorlage_arr|@count > 0} <input class="button orange" name="loeschen" type="submit" value="{#newsletterdelete#}">{/if}</p>
					  </form>
					  
				 </div>
			
			<div class="tabbertab{if isset($cTab) && $cTab == 'newslettervorlagenstd'} tabbertabdefault{/if}">
					  <h2>{#newsletterdraftStd#}</h2>
				
				{if isset($oNewslettervorlageStd_arr) && $oNewslettervorlageStd_arr|@count > 0}					
				<form method="POST" action="newsletter.php">
				<input type="hidden" name="{$session_name}" value="{$session_id}">
					  <input name="newslettervorlagenstd" type="hidden" value="1">
				<input name="vorlage_std_erstellen" type="hidden" value="1">
					  <input name="tab" type="hidden" value="newslettervorlagenstd">
					  <input name="s6" type="hidden" value="{$oBlaetterNaviNLVorlagen->nAktuelleSeite}">
				<div id="payment">
							<div id="tabellenLivesuche">                    
							<table>
								 <tr>
									  <th class="th-1">{#newsletterdraftname#}</th>
									  <th class="th-2">{#newsletterdraftStdPicture#}</th>
								 </tr>
							{foreach name=newslettervorlagestsd from=$oNewslettervorlageStd_arr item=oNewslettervorlageStd}
								 <tr class="tab_bg{$smarty.foreach.newslettervorlagestsd.iteration%2}">
									  <td class="TD1"><input name="kNewsletterVorlageStd" type="radio" value="{$oNewslettervorlageStd->kNewslettervorlageStd}"> {$oNewslettervorlageStd->cName}</td>
							<td class="TD2" valign="top">{$oNewslettervorlageStd->cBild}</td>
								 </tr>
							{/foreach}
							</table>                    						
							</div>
					  </div>
				<p style="text-align: center;"><input name="submitVorlageStd" type="submit" value="{#newsletterdraftStdUse#}" class="button orange" /></p>
				</form>
				
				{else}
					<br/>{#noDataAvailable#}<br/><br/>
				{/if}
				
			</div>
				 
				 <div class="tabbertab{if isset($cTab) && $cTab == 'newsletterhistory'} tabbertabdefault{/if}">
					  <h2>{#newsletterhistory#}</h2>
					  
					  <!-- Übersicht Newsletterhistory -->
					  {if isset($oNewsletterHistory_arr) && $oNewsletterHistory_arr|@count > 0}
							<form method="POST" action="newsletter.php">
							<input type="hidden" name="{$session_name}" value="{$session_id}">
							<input name="newsletterhistory" type="hidden" value="1">
							<input name="tab" type="hidden" value="newsletterhistory">
							<input name="s4" type="hidden" value="{$oBlaetterNaviNLHistory->nAktuelleSeite}">
							
							{if $oBlaetterNaviNLHistory->nAktiv == 1}
							<div class="content">
								 <p>
								 {$oBlaetterNaviNLHistory->nVon} - {$oBlaetterNaviNLHistory->nBis} {#from#} {$oBlaetterNaviNLHistory->nAnzahl}
								 {if $oBlaetterNaviNLHistory->nAktuelleSeite == 1}
									  << {#back#}
								 {else}
									  <a href="newsletter.php?s4={$oBlaetterNaviNLHistory->nVoherige}&tab=newsletterhistory&{$session_name}={$session_id}"><< {#back#}</a>
								 {/if}
								 
								 {if $oBlaetterNaviNLHistory->nAnfang != 0}<a href="newsletter.php?s4={$oBlaetterNaviNLHistory->nAnfang}&tab=newsletterhistory&{$session_name}={$session_id}">{$oBlaetterNaviNLHistory->nAnfang}</a> ... {/if}
								 {foreach name=blaetternavi from=$oBlaetterNaviNLHistory->nBlaetterAnzahl_arr item=Blatt}
									  {if $oBlaetterNaviNLHistory->nAktuelleSeite == $Blatt}[{$Blatt}]
									  {else}
											<a href="newsletter.php?s4={$Blatt}&tab=newsletterhistory&{$session_name}={$session_id}">{$Blatt}</a>
									  {/if}
								 {/foreach}
								 
								 {if $oBlaetterNaviNLHistory->nEnde != 0} ... <a href="newsletter.php?s4={$oBlaetterNaviNLHistory->nEnde}&tab=newsletterhistory&{$session_name}={$session_id}">{$oBlaetterNaviNLHistory->nEnde}</a>{/if}
								 
								 {if $oBlaetterNaviNLHistory->nAktuelleSeite == $oBlaetterNaviNLHistory->nSeiten}
									  {#next#} >>
								 {else}
									  <a href="newsletter.php?s4={$oBlaetterNaviNLHistory->nNaechste}&tab=newsletterhistory&{$session_name}={$session_id}">{#next#} >></a>
								 {/if}
								 
								 </p>
							</div>
							{/if}
							
							<div id="payment">
								 <div id="tabellenLivesuche">
								 <table>
									  <tr>
											<th class="th-1">&nbsp;</th>
											<th class="tleft">{#newsletterhistorysubject#}</th>
											<th class="tleft">{#newsletterhistorycount#}</th>
											<th class="tleft">{#newsletterqueuecustomergrp#}</th>
											<th class="tcenter">{#newsletterhistorydate#}</th>
									  </tr>
								 {foreach name=newsletterhistory from=$oNewsletterHistory_arr item=oNewsletterHistory}
									  <tr class="tab_bg{$smarty.foreach.newsletterhistory.iteration%2}">
											<td class="tleft"><input name="kNewsletterHistory[]" type="checkbox" value="{$oNewsletterHistory->kNewsletterHistory}"></td>
											<td class="tleft"><a href="newsletter.php?newsletterhistory=1&anzeigen={$oNewsletterHistory->kNewsletterHistory}&tab=newsletterhistory&{$session_name}={$session_id}">{$oNewsletterHistory->cBetreff}</a></td>
											<td class="tleft">{$oNewsletterHistory->nAnzahl}</td>
											<td class="tleft">{$oNewsletterHistory->cKundengruppe}</td>
											<td class="tcenter">{$oNewsletterHistory->Datum}</td>
									  </tr>
								 {/foreach}
									  <tr>
											<td class="TD1"><input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);"></td>
											<td colspan="6" class="TD7">{#globalSelectAll#}</td>
									  </tr>
								 </table>
								 </div>
							</div>
							<p class="submit"><input name="loeschen" type="submit" class="button orange" value="{#newsletterdelete#}"></p>
							</form>
					  {else}
							<br/>{#noDataAvailable#}<br/><br/>
					  {/if}
					  
				 </div>

				 <div class="tabbertab{if isset($cTab) && $cTab == 'einstellungen'} tabbertabdefault{/if}">
					  <h2>{#newsletterconfig#}</h2>
					  
					  <form name="einstellen" method="post" action="newsletter.php">
					  <input type="hidden" name="{$session_name}" value="{$session_id}">
					  <input type="hidden" name="einstellungen" value="1">
					  <input name="tab" type="hidden" value="einstellungen">
					  <div class="settings">
							{foreach name=conf from=$oConfig_arr item=oConfig}
								 {if $oConfig->cConf == "Y"}
									  <p><label for="{$oConfig->cWertName}">{$oConfig->cName} {if $oConfig->cBeschreibung}<img src="{$currentTemplateDir}gfx/help.png" alt="{$oConfig->cBeschreibung}" title="{$oConfig->cBeschreibung}" style="vertical-align:middle; cursor:help;" /></label>
								 {/if}
								 {if $oConfig->cInputTyp=="selectbox"}
									  <select name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" class="combo"> 
									  {foreach name=selectfor from=$oConfig->ConfWerte item=wert}
											<option value="{$wert->cWert}" {if $oConfig->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
									  {/foreach}
									  </select> 
								 {else}
                                      {if $oConfig->cWertName == 'newsletter_smtp_pass'}
                                        <input type="password" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}"  value="{$oConfig->gesetzterWert}" tabindex="1" /></p>
                                      {else}
									    <input type="text" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}"  value="{$oConfig->gesetzterWert}" tabindex="1" /></p>
                                      {/if}
								 {/if}
								 {else}
									  {if $oConfig->cName}<h3 style="text-align:center;">{$oConfig->cName}</h3>{/if}
								 {/if}
							{/foreach}
					  </div>
					  
					  <p class="submit"><input name="speichern" type="submit" value="{#save#}" class="button orange" /></p>
					  </form>
					  
				 </div>
				 
	  </div>
	
 </div>
