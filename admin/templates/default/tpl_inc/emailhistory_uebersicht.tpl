{*
-------------------------------------------------------------------------------
JTL-Shop 3
File: emailhistory_uebersicht.tpl, smarty template inc file

page for JTL-Shop 3
Admin

Author: daniel.boehmer@jtl-software.de, JTL-Software
http://www.jtl-software.de

Copyright (c) 2010 JTL-Software
-------------------------------------------------------------------------------
*}

{include file="tpl_inc/seite_header.tpl" cTitel=#emailhistory# cBeschreibung=#emailhistoryDesc# cDokuURL=#emailhistoryURL#}
<div id="content">
	 {if isset($cHinweis) && $cHinweis|count_characters > 0}
		  <p class="box_success">{$cHinweis}</p>
	 {/if}
	 {if isset($cFehler) && $cFehler|count_characters > 0}			
		  <p class="box_error">{$cFehler}</p>
	 {/if}

	 {if $oEmailhistory_arr|@count > 0 && $oEmailhistory_arr}
		{if $oBlaetterNaviUebersicht->nAktiv == 1}
		  <div class=" block clearall">
				<div class="left">					 
					  <div class="pages tright">
							<span class="pageinfo">{#page#}: <strong>{$oBlaetterNaviUebersicht->nVon}</strong> - {$oBlaetterNaviUebersicht->nBis} {#from#} {$oBlaetterNaviUebersicht->nAnzahl}</span>
							<a class="back" href="emailhistory.php?s1={$oBlaetterNaviUebersicht->nVoherige}">&laquo;</a>
							{if $oBlaetterNaviUebersicht->nAnfang != 0}<a href="emailhistory.php?s1={$oBlaetterNaviUebersicht->nAnfang}">{$oBlaetterNaviUebersicht->nAnfang}</a> ... {/if}
								 {foreach name=blaetternavi from=$oBlaetterNaviUebersicht->nBlaetterAnzahl_arr item=Blatt}
									  <a class="page {if $oBlaetterNaviUebersicht->nAktuelleSeite == $Blatt}active{/if}" href="emailhistory.php?s1={$Blatt}">{$Blatt}</a>
								 {/foreach}
							
							{if $oBlaetterNaviUebersicht->nEnde != 0}
								 ... <a class="page" href="emailhistory.php?s1={$oBlaetterNaviUebersicht->nEnde}">{$oBlaetterNaviUebersicht->nEnde}</a>
							{/if}
							<a class="next" href="emailhistory.php?s1={$oBlaetterNaviUebersicht->nNaechste}">&raquo;</a>
					  </div>
				</div>
		  </div>
		{/if}
	 
		  <div class="category">{#emailhistory#}</div>
		  
		  <form name="emailhistory" method="post" action="emailhistory.php">
				<input type="hidden" name="{$session_name}" value="{$session_id}" />
				<input name="a" type="hidden" value="delete" /> 
				
				<table class="list">
					 <thead>
						  <tr>
								<th></th>
								<th class="tleft">{#subject#}</th>
								<th class="tleft">{#fromname#}</th>
								<th class="tleft">{#fromemail#}</th>
								<th class="tleft">{#toname#}</th>
								<th class="tleft">{#toemail#}</th>
								<th class="tleft">{#date#}</th>
						  </tr>
					 </thead>
					 <tbody>
						  {foreach name=emailhistory from=$oEmailhistory_arr item=oEmailhistory}
								<tr class="tab_bg{$smarty.foreach.emailhistory.iteration%2}">
									 <td class="check"><input type="checkbox" name="kEmailhistory[]" value="{$oEmailhistory->getEmailhistory()}" /></td>
									 <td>{$oEmailhistory->getSubject()}</td>									 
									 <td>{$oEmailhistory->getFromName()}</td>
									 <td>{$oEmailhistory->getFromEmail()}</td>
									 <td>{$oEmailhistory->getToName()}</td>
									 <td>{$oEmailhistory->getToEmail()}</td>
									 <td>{SmartyConvertDate date=$oEmailhistory->getSent()}</td>
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
					 <button name="zuruecksetzenBTN" type="submit" class="button orange">{#delete#}</button>
				</div>
		  </form>
	{else}
		{#nodata#}
	{/if}
</div>