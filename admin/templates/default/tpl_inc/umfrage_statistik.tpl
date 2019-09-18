{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: umfrage_statistik.tpl, smarty template inc file
	
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
			<table width="100%" cellpadding="5" cellspacing="10" class="kundenfeld">
				<tr>
					<td valign="top" align="left" style="background: #F2F2F2;width: 33%;">
					
						<table>
							<tr>
								<td><h1 class="txtBlack">{$oUmfrageStats->cName}</h1></td>
							</tr>
							<tr>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td><strong>{#umfrageValidation#}:</strong> {$oUmfrageStats->dGueltigVon_de}-{if $oUmfrageStats->dGueltigBis|truncate:10:"" == "0000-00-00"}{#umfrageInfinite#}{else}{$oUmfrageStats->dGueltigBis_de}{/if}</td>
							</tr>
							<tr>
								<td><strong>{#umfrageActive#}:</strong> {$oUmfrageStats->nAktiv}</td>
							</tr>
							<tr>
								<td><strong>{#umfrageCustomerGrp#}:</strong> 
								{foreach name=kundengruppen from=$oUmfrageStats->cKundengruppe_arr item=cKundengruppe}	
			        				{$cKundengruppe}{if !$smarty.foreach.kundengruppen.last},{/if}
			        			{/foreach}
								</td>
							</tr>
							<tr>
								<td><strong>{#umfrageTryCount#}:</strong> {$oUmfrageStats->nAnzahlDurchfuehrung}</td>
							</tr>
							<tr style="font-family: Verdana; font-size: 1em; font-style: normal;">
								<td><strong>{#umfrageText#}:</strong> {$oUmfrageStats->cBeschreibung}</td>
							</tr>							
						</table>	
					
					</td>
				</tr>
			</table>
		
			{if $oUmfrageStats->oUmfrageFrage_arr|@count > 0 && $oUmfrageStats->oUmfrageFrage_arr}
			
			<p><strong><u>{#umfrageQ#}:</u></strong></p>
			
			{foreach name=umfragefrage from=$oUmfrageStats->oUmfrageFrage_arr item=oUmfrageFrage}
			
			{if $oUmfrageFrage->oUmfrageFrageAntwort_arr|@count > 0 && $oUmfrageFrage->oUmfrageFrageAntwort_arr}
			{if $oUmfrageFrage->cTyp == "matrix_single" || $oUmfrageFrage->cTyp == "matrix_multi"}
			<table>
				<tr>
					<td><strong>{$oUmfrageFrage->cName}</strong> - {$oUmfrageFrage->cTypMapped}</td>
				</tr>
				<tr>
					<td>
					
					<div id="payment">
			            <div id="tabellenLivesuche">
							<table>
								<tr>
									<th class="th-1" style="width: 5%;">{#umfrageQASing#}</th>
									{foreach name=umfragematrixoption from=$oUmfrageFrage->oUmfrageMatrixOption_arr item=oUmfrageMatrixOption}
									{assign var=maxbreite value=95}
									{assign var=anzahloption value=$oUmfrageFrage->oUmfrageMatrixOption_arr|@count}
									{assign var=breite value="`$maxbreite/$anzahloption`"}
										<th class="th-1" style="width: {$breite}%;">{$oUmfrageMatrixOption->cName}</th>
									{/foreach}									
								</tr>
								
								{foreach name=umfragefrageantwort from=$oUmfrageFrage->oUmfrageFrageAntwort_arr item=oUmfrageFrageAntwort}
								{assign var=kUmfrageFrageAntwort value=$oUmfrageFrageAntwort->kUmfrageFrageAntwort}							
									<tr class="tab_bg{$smarty.foreach.umfragefrageantwort.iteration%2}">
										<td class="TD1">{$oUmfrageFrageAntwort->cName}</td>
									{foreach name=umfragematrixoption from=$oUmfrageFrage->oUmfrageMatrixOption_arr item=oUmfrageMatrixOption}
									{assign var=kUmfrageMatrixOption value=$oUmfrageMatrixOption->kUmfrageMatrixOption}
										<td align="center">
										{if $oUmfrageFrage->oErgebnisMatrix_arr[$kUmfrageFrageAntwort][$kUmfrageMatrixOption]->nBold == 1}<strong>{/if}
										{$oUmfrageFrage->oErgebnisMatrix_arr[$kUmfrageFrageAntwort][$kUmfrageMatrixOption]->fProzent}% ({$oUmfrageFrage->oErgebnisMatrix_arr[$kUmfrageFrageAntwort][$kUmfrageMatrixOption]->nAnzahl})
										{if $oUmfrageFrage->oErgebnisMatrix_arr[$kUmfrageFrageAntwort][$kUmfrageMatrixOption]->nBold == 1}</strong>{/if}
										</td>
									{/foreach}
									</tr>
								{/foreach}
							</table>
						</div>
					</div>
					
					</td>
				</tr>
			</table>
			<br />
			
			{else}
			
		  <table>
				<tr>
					<td><strong>{$oUmfrageFrage->cName}</strong> - {$oUmfrageFrage->cTypMapped}</td>
				</tr>
				<tr>
					<td>					
										
						<div id="payment">
			            	<div id="tabellenLivesuche">
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
			            			<td class="TD2" style="width: 60%;"><div class="freqbar" style="width: {$oUmfrageFrageAntwort->fProzent}%; height: 10px;"></div></td>
			            			<td class="TD3" style="width: 10%;">
			            			{if $smarty.foreach.umfragefrageantwort.first}
			            				<strong>{$oUmfrageFrageAntwort->fProzent} %</strong>
			            			{elseif $oUmfrageFrageAntwort->nAnzahlAntwort == $oUmfrageFrage->oUmfrageFrageAntwort_arr[0]->nAnzahlAntwort}
			            				<strong>{$oUmfrageFrageAntwort->fProzent} %</strong>
			            			{else}
			            				{$oUmfrageFrageAntwort->fProzent} %
			            			{/if}
			            			</td>
			            			<td class="TD4" style="width: 10%;">{$oUmfrageFrageAntwort->nAnzahlAntwort}</td>
			            		</tr>
			            		{if $smarty.foreach.umfragefrageantwort.last}
			            		<tr>
			            			<td></td>
			            			<td colspan="2" align="right">{#umfrageQMax#}</td>
			            			<td align="center">{$oUmfrageFrage->nAnzahlAntworten}</td>
			            		</tr>
			            		{/if}
							{/foreach}
			            	</table>
			            	</div>
						</div>
									
					</td>
				</tr>
			</table>
			<br />
			{/if}
			{/if}
			
			{/foreach}
			
			{/if}
						
		</div>
	</div>
</div>