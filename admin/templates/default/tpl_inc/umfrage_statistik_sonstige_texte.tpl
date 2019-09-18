{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: umfrage_statistik_sonstige_texte.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
<div id="page">
	<div id="content">
		<div id="welcome" class="post">
			<h2 class="title"><span>{#umfrage#}</span></h2>
			<div class="content">
				<p>{#umfrageDesc#}</p>
		    </div>
		</div>
		       	
		<div class="container">
					
			{if $oUmfrageFrage->oUmfrageFrageAntwort_arr|@count > 0 && $oUmfrageFrage->oUmfrageFrageAntwort_arr}
			
			<form method="POST" action="umfrage.php">
				<input type="hidden" name="umfrage" value="1" />
				<input type="hidden" name="{$session_name}" value="{$session_id}" />
				<input name="umfrage_statistik" type="hidden" value="1" />
				<input name="kUmfrage" type="hidden" value="{$oUmfrageFrage->kUmfrage}" />
			
			<p style="width: 55px; border-bottom: 1px solid #000000;"><b>{#umfrageQ#}:</b></p>
										
			<div id="payment">
            	<div id="tabellenLivesuche">
            	<b>{$oUmfrageFrage->cName}</b><br /><br />
            	<table>
            		<tr>
            			<th class="th-1" style="width: 20%;">{#umfrageQASing#}</th>
            			<th class="th-2" style="width: 60%;"></th>
            			<th class="th-3" style="width: 10%;">{#umfrageQResPercent#}</th>
            			<th class="th-4" style="width: 10%;">{#umfrageQResCount#}</th>
            		</tr>
            	{foreach name=umfragefrageantwort from=$oUmfrageFrage->oUmfrageFrageAntwort_arr item=oUmfrageFrageAntwort}
            		<tr class="tab_bg{$smarty.foreach.umfragefrageantwort.iteration%2}">
            			<td class="TD1" style="width: 20%;">{$oUmfrageFrageAntwort->cName}</td>
            			<td class="TD2" style="width: 60%;"><div class="freqbar" style="width: {$oUmfrageFrageAntwort->nProzent}%; height: 10px;"></div></td>
            			<td class="TD3" style="width: 10%;">
            			{if $smarty.foreach.umfragefrageantwort.first}
            				<b>{$oUmfrageFrageAntwort->nProzent} %</b>
            			{elseif $oUmfrageFrageAntwort->nAnzahlAntwort == $oUmfrageFrage->oUmfrageFrageAntwort_arr[0]->nAnzahlAntwort}
            				<b>{$oUmfrageFrageAntwort->nProzent} %</b>
            			{else}
            				{$oUmfrageFrageAntwort->nProzent} %
            			{/if}
            			</td>
            			<td class="TD4" style="width: 10%;">{$oUmfrageFrageAntwort->nAnzahlAntwort}</td>
            		</tr>
            		{if $smarty.foreach.umfragefrageantwort.last}
            		<tr>
            			<td></td>
            			<td colspan="2" align="right">{#umfrageQMax#}</td>
            			<td align="center">{$oUmfrageFrage->nMaxAntworten}</td>
            		</tr>
            		{/if}
				{/foreach}
            	</table>
            	</div>
			</div>	
			
			<p><input name="zurueck" type="submit" value="{#goBack#}" /></p>	
			</form>
			{/if}
						
		</div>
	</div>
</div>