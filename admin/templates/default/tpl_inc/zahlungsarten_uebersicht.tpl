{*
-------------------------------------------------------------------------------
JTL-Shop 3
File: zahlungsarten_uebersicht.tpl, smarty template inc file

page for JTL-Shop 3 
Admin

Author: JTL-Software-GmbH
http://www.jtl-software.de

Copyright (c) 2007 JTL-Software
-------------------------------------------------------------------------------
*}

{include file="tpl_inc/seite_header.tpl" cTitel=#paymentmethods# cBeschreibung=#installedPaymentmethods# cDokuURL=#paymentmethodsURL#}
<div id="content">
	 {if isset($cHinweis) && $cHinweis|count_characters > 0}			
		  <p class="box_success">{$cHinweis}</p>
	 {/if}
	 
	 <form method="POST" action="zahlungsarten.php" class="container top">
	 	 <input type="hidden" name="{$session_name}" value="{$session_id}" />
		  <input type="hidden" name="checkNutzbar" value="1" />
	 	 <input name="checkSubmit" type="submit" value="{#paymentmethodsCheckAll#}" class="button reset" />
	 </form>

	 <table class="list">
	 <thead>
	 <tr>
	 <th class="tleft">{#paymentmethodName#}</th>
	 <th class="th-2">{#provider#}</th>
	 <th class="th-3">{#useable#}</th>
	 <th class="th-4">{#log#}</th>
     <th class="th-4"></th>
	 </tr>
	 </thead>
	 <tbody>
	 {foreach name=zahlungsarten from=$zahlungsarten item=zahlungsart}
	 <tr class="tab_bg{$smarty.foreach.zahlungsarten.iteration%2}">
	 <td class="TD1">{$zahlungsart->cName}</td>
	 <td class="TD2" style="text-align: center;">{$zahlungsart->cAnbieter}</td>
	 <td class="TD3" style="text-align: center;">
	 {if $zahlungsart->cModulId == "za_dresdnercetelem_jtl" && isset($nFinanzierungAktiv) && $nFinanzierungAktiv == 1}
	 {if $zahlungsart->nNutzbar == 1}<span class="success">{#yes#}</span>{else}<span class="error">{#no#}</span>{/if}
	 {elseif $zahlungsart->cModulId != "za_dresdnercetelem_jtl"}
	 {if $zahlungsart->nNutzbar == 1}<span class="success">{#yes#}</span>{else}<span class="error">{#no#}</span>{/if}
	 {else}						
	 <span class="error">{#expansionNeeded#}</span>
	 {/if}
	 </td>
     <td align="center">
     {if isset($zahlungsart->oZahlungsLog->oLog_arr) && $zahlungsart->oZahlungsLog->oLog_arr|@count > 0}
         <a href="zahlungsarten.php?a=log&kZahlungsart={$zahlungsart->kZahlungsart}&{$SID}" class="button down">{#viewLog#}</a>
     {/if}
     </td>
	 <td class="tcenter">
         <a href="zahlungsarten.php?kZahlungsart={$zahlungsart->kZahlungsart}&{$SID}" class="button edit">{#configure#}</a>     
     </td>
	 </tr>
	 {/foreach}
	 </tbody>
	 </table>
</div>