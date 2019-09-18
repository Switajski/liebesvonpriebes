{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: umfrage_vorschau.tpl, smarty template inc file
	
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
			<h2 class="title"><span>{$oUmfrage->cName}</span></h2>
			<div class="content">
				<p>{#umfrageDesc#}</p>
		    </div>
		</div>
				
		{if $hinweis}
			<br>
			<div class="userNotice">
				{$hinweis}
			</div>
		{/if}
		{if $fehler}
			<br>
			<div class="userError">
				{$fehler}
			</div>
		{/if}
		
		<br>
		
		<div class="container">
			<table width="100%" cellpadding="5" cellspacing="10" class="kundenfeld">
				<tr>
					<td valign="top" align="left" style="background: #F2F2F2;width: 33%;">
					
						<table>
							<tr>
								<td><h1 class="txtBlack">{$oUmfrage->cName}</h1></td>
							</tr>
							<tr>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td><b>{#umfrageValidation#}:</b> {$oUmfrage->dGueltigVon_de}-{if $oUmfrage->dGueltigBis|truncate:10:"" == "0000-00-00"}{#umfrageInfinite#}{else}{$oUmfrage->dGueltigBis_de}{/if}</td>
							</tr>
							<tr>
								<td><b>{#umfrageActive#}:</b> {$oUmfrage->nAktiv}</td>
							</tr>
							<tr>
								<td><b>{#umfrageCustomerGrp#}:</b> 
								{foreach name=kundengruppen from=$oUmfrage->cKundengruppe_arr item=cKundengruppe}	
			        				{$cKundengruppe}{if !$smarty.foreach.kundengruppen.last},{/if}
			        			{/foreach}
								</td>
							</tr>
							<tr style="font-family: Verdana; font-size: 1em%; font-style: normal;">
								<td><b>{#umfrageText#}:</b> {$oUmfrage->cBeschreibung}</td>
							</tr>
						</table>	
					
					</td>
				</tr>
			</table>
			
			<form method="POST" action="umfrage.php">
				<input type="hidden" name="{$session_name}" value="{$session_id}">
			    <input type="hidden" name="umfrage" value="1">
			    <input type="hidden" name="kUmfrage" value="{$oUmfrage->kUmfrage}">
			    <input type="hidden" name="umfrage_frage_hinzufuegen" value="1">
			    
				<input name="umfragefragehinzufuegen" type="submit" value="{#umfrageQAdd#}" />
			</form>
			
			<form method="POST" action="umfrage.php">
				<input type="hidden" name="{$session_name}" value="{$session_id}">
			    <input type="hidden" name="umfrage" value="1">
			    <input type="hidden" name="kUmfrage" value="{$oUmfrage->kUmfrage}">
			    <input type="hidden" name="umfrage_statistik" value="1">
			    
				<input name="umfragestatistik" type="submit" value="{#umfrageStatsView#}" />
			</form>
			
			{if $oUmfrage->oUmfrageFrage_arr|@count > 0 && $oUmfrage->oUmfrageFrage_arr}
			<form method="POST" action="umfrage.php">
			<input type="hidden" name="{$session_name}" value="{$session_id}">
		    <input type="hidden" name="umfrage" value="1">
		    <input type="hidden" name="kUmfrage" value="{$oUmfrage->kUmfrage}">
		    <input type="hidden" name="umfrage_frage_loeschen" value="1">
			<br>
			<p style="width: 65px; border-bottom: 1px solid #000000;"><b>{#umfrageQs#}:</b></p>
			{foreach name=umfragefrage from=$oUmfrage->oUmfrageFrage_arr item=oUmfrageFrage}
				<table width="100%" cellpadding="5" cellspacing="5" class="kundenfeld">
					<tr>
						<td valign="top" align="left" style="width: 33%;">
						
							<table>
								<tr class="tab_bg{$smarty.foreach.umfragefrage.iteration%2}">
									<td style="width: 20px;"><b>{$smarty.foreach.umfragefrage.iteration}.</b></td>
									<td style="width: 10px;"><input name="kUmfrageFrage[]" type="checkbox" value="{$oUmfrageFrage->kUmfrageFrage}"></td>
									<td colspan="2"><font color="#000000"><b>{$oUmfrageFrage->cName}</b> [<a href="umfrage.php?umfrage=1&kUmfrage={$oUmfrage->kUmfrage}&kUmfrageFrage={$oUmfrageFrage->kUmfrageFrage}&fe=1&{$SID}">{#umfrageEdit#}</a>]</font></td>
								</tr>
								<tr class="tab_bg{$smarty.foreach.umfragefrage.iteration%2}">
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td colspan="2">{$oUmfrageFrage->cTypMapped}</td>
								</tr>
								<tr class="tab_bg{$smarty.foreach.umfragefrage.iteration%2}">
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td colspan="2">{$oUmfrageFrage->cBeschreibung}</td>
								</tr>
								
								{if $oUmfrageFrage->cTyp != "text_statisch" && $oUmfrageFrage->cTyp != "text_statisch_seitenwechsel" && $oUmfrageFrage->cTyp != "text_klein" && $oUmfrageFrage->cTyp != "text_gross"}
								<tr class="tab_bg{$smarty.foreach.umfragefrage.iteration%2}">
									<td>&nbsp;</td>
									<td>&nbsp;</td>									
									<td valign="top" {if $oUmfrageFrage->oUmfrageMatrixOption_arr|@count > 0}{else}colspan="2"{/if}><p style="width: 80px; border-bottom: 1px solid #000000;"><b>{#umfrageQA#}:</b></p>
									
										<table>
										{foreach name=umfragefrageantwort from=$oUmfrageFrage->oUmfrageFrageAntwort_arr item=oUmfrageFrageAntwort}
											<tr>
												<td style="width: 10px;"><input name="kUmfrageFrageAntwort[]" type="checkbox" value="{$oUmfrageFrageAntwort->kUmfrageFrageAntwort}"></td>
												<td>{$oUmfrageFrageAntwort->cName}</td>
											</tr>
										{/foreach}
										</table>
																			
									</td>
									{if $oUmfrageFrage->oUmfrageMatrixOption_arr|@count > 0 && $oUmfrageFrage->oUmfrageMatrixOption_arr}
									<td valign="top"><p style="width: 80px; border-bottom: 1px solid #000000;"><b>{#umfrageQO#}:</b></p>
									
										<table>
										{foreach name=umfragemaxtrixoption from=$oUmfrageFrage->oUmfrageMatrixOption_arr item=oUmfrageMatrixOption}
											<tr>
												<td style="width: 10px;"><input name="kUmfrageMatrixOption[]" type="checkbox" value="{$oUmfrageMatrixOption->kUmfrageMatrixOption}"></td>
												<td>{$oUmfrageMatrixOption->cName}</td>
											</tr>
										{/foreach}
										</table>
																			
									</td>
									{/if}
								</tr>
								{/if}
							</table>	
						
						</td>
					</tr>
				</table>
			{/foreach}
				<p><input name="umfragefrageloeschen" type="submit" value="{#delete#}" /></p>
			</form>
			{/if}
		
			<br><a href="umfrage.php?{$session_name}={$session_id}">{#umfrageBack#}</a>
		</div>
	</div>
</div>