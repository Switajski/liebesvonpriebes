{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: kampagne_uebersicht.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2010 JTL-Software
-------------------------------------------------------------------------------
*}

<script type="text/javascript">
	function changeZeitSelect(currentSelect) {ldelim}
		if (currentSelect.options[currentSelect.selectedIndex].value > 0)
			window.location.href = "kampagne.php?tab=globalestats&nAnsicht=" + currentSelect.options[currentSelect.selectedIndex].value;
		{rdelim}
</script>

{include file="tpl_inc/seite_header.tpl" cTitel=#kampagne# cBeschreibung=#kampagneDesc#}
<div id="content">
	{if isset($hinweis) && $hinweis|count_characters > 0}
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}
		<p class="box_error">{$fehler}</p>
	{/if}
	<div class="container">
		<div class="tabber">
			<div class="tabbertab{if isset($cTab) && $cTab == 'uebersicht'} tabbertabdefault{/if}">
				<h2>{#kampagneOverview#}</h2>
				<a href="kampagne.php?neu=1" class="button add">{#kampagneNewBTN#}</a>
				{if isset($oKampagne_arr) && $oKampagne_arr|@count > 0}
					<div id="tabellenLivesuche">

						<div class="category">{#kampagneIntern#}</div>
						{if isset($oKampagne_arr[0]->kKampagne) && $oKampagne_arr[0]->kKampagne < 1000}
							<table>
								<tr>
									<th class="tleft">{#kampagneName#}</th>
									<th class="tleft">{#kampagneParam#}</th>
									<th class="tleft">{#kampagneValue#}</th>
									<th class="th-4">{#kampagnenActive#}</th>
									<th class="th-5">{#kampagnenDate#}</th>
									<th class="th-6"></th>
								</tr>

								{foreach name="kampagnen" from=$oKampagne_arr item=oKampagne}
									{if isset($oKampagne->kKampagne) && $oKampagne->kKampagne < 1000}
										<tr class="tab_bg{$smarty.foreach.kampagnen.iteration%2}">
											<td class="TD2">
												<strong><a href="kampagne.php?kKampagne={$oKampagne->kKampagne}&detail=1">{$oKampagne->cName}</a></strong>
											</td>
											<td class="TD3">{$oKampagne->cParameter}</td>
											<td class="TD3">
												{if isset($oKampagne->nDynamisch) && $oKampagne->nDynamisch == 1}
													{#kampagneDynamic#}
												{else}
													{#kampagneStatic#}
													<br />
													<strong>{#kampagneValueStatic#}:</strong>
													{$oKampagne->cWert}
												{/if}
											</td>
											<td class="tcenter">{if isset($oKampagne->nAktiv) && $oKampagne->nAktiv == 1}{#yes#}{else}{#no#}{/if}</td>
											<td class="tcenter">{$oKampagne->dErstellt_DE}</td>
											<td class="tcenter">
												<a href="kampagne.php?kKampagne={$oKampagne->kKampagne}&editieren=1" class="button edit">{#kampagneEditBTN#}</a>
											</td>
										</tr>
									{/if}
								{/foreach}
							</table>
						{/if}

						{if isset($nGroessterKey) && $nGroessterKey >= 1000}
							<div class="category">{#kampagneExtern#}</div>
							<form name="kampagnen" method="POST" action="kampagne.php">
								<input type="hidden" name="{$session_name}" value="{$session_id}" />
								<input type="hidden" name="tab" value="uebersicht" />
								<input type="hidden" name="delete" value="1" />
								<table>
									<tr>
										<th class="check"></th>
										<th class="tleft">{#kampagneName#}</th>
										<th class="tleft">{#kampagneParam#}</th>
										<th class="tleft">{#kampagneValue#}</th>
										<th class="th-4">{#kampagnenActive#}</th>
										<th class="th-5">{#kampagnenDate#}</th>
										<th class="th-6"></th>
									</tr>

									{foreach name="kampagnen" from=$oKampagne_arr item=oKampagne}
										{if $oKampagne->kKampagne >= 1000}
											<tr class="tab_bg{$smarty.foreach.kampagnen.iteration%2}">
												<td class="check">
													<input name="kKampagne[]" type="checkbox" value="{$oKampagne->kKampagne}">
												</td>
												<td class="TD2">
													<strong><a href="kampagne.php?kKampagne={$oKampagne->kKampagne}&detail=1">{$oKampagne->cName}</a></strong>
												</td>
												<td class="TD3">{$oKampagne->cParameter}</td>
												<td class="TD3">
													{if isset($oKampagne->nDynamisch) && $oKampagne->nDynamisch == 1}
														{#kampagneDynamic#}
													{else}
														{#kampagneStatic#}
														<br />
														<strong>{#kampagneValueStatic#}:</strong>
														{$oKampagne->cWert}
													{/if}
												</td>
												<td class="tcenter">{if isset($oKampagne->nAktiv) && $oKampagne->nAktiv == 1}{#yes#}{else}{#no#}{/if}</td>
												<td class="tcenter">{$oKampagne->dErstellt_DE}</td>
												<td class="tcenter">
													<a href="kampagne.php?kKampagne={$oKampagne->kKampagne}&editieren=1" class="button edit">{#kampagneEditBTN#}</a>
												</td>
											</tr>
										{/if}
									{/foreach}
									<tr>
										<td class="check">
											<input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);" />
										</td>
										<td colspan="6">{#globalSelectAll#}</td>
									</tr>
								</table>
								<p align="center">
									<input name="submitDelete" type="submit" value="{#delete#}" class="button orange">
								</p>
							</form>
						{/if}
					</div>
				{else}
					<div class="box_info">{#noDataAvailable#}</div>
				{/if}
			</div>

			<div class="tabbertab{if isset($cTab) && $cTab == 'globalestats'} tabbertabdefault{/if}">
				<h2>{#kampagneGlobalStats#}</h2>

				<div id="payment">

					<div class="block tcenter container">
						<strong>{#kampagneView#}: </strong>
						<select name="nAnsicht" class="combo" style="width: 10em;" onChange="javascript:changeZeitSelect(this);";>
						<option value="-1"></option>
						<option value="1"{if $smarty.session.Kampagne->nAnsicht == 1} selected{/if}>{#kampagneStatMonth#}</option>
						<option value="2"{if $smarty.session.Kampagne->nAnsicht == 2} selected{/if}>{#kampagneStatWeek#}</option>
						<option value="3"{if $smarty.session.Kampagne->nAnsicht == 3} selected{/if}>{#kampagneStatDay#}</option>
						</select>
						<strong>{#kampagnePeriod#}:</strong> {$cZeitraum}
					</div>

					{if isset($oKampagne_arr) && $oKampagne_arr|@count > 0 && isset($oKampagneDef_arr) && $oKampagneDef_arr|@count > 0}
						<div id="tabellenLivesuche">
							<table>
								<tr>
									<th class="th-1"></th>
									{foreach name="kampagnendefs" from=$oKampagneDef_arr item=oKampagneDef}
										<th class="th-2">
											<a href="kampagne.php?tab=globalestats&nSort={$oKampagneDef->kKampagneDef}">{$oKampagneDef->cName}</a>
										</th>
									{/foreach}
								</tr>

								{foreach name="kampagnenstats" from=$oKampagneStat_arr key=kKampagne item=oKampagneStatDef_arr}
									{if $kKampagne != "Gesamt"}
										<tr class="tab_bg{$smarty.foreach.kampagnenstats.iteration%2}">
											<td class="TD1">
												<a href="kampagne.php?detail=1&kKampagne={$oKampagne_arr[$kKampagne]->kKampagne}&cZeitParam={$cZeitraumParam}">{$oKampagne_arr[$kKampagne]->cName}</a>
											</td>
											{foreach name="kampagnendefs" from=$oKampagneStatDef_arr key=kKampagneDef item=oKampagneStatDef}
												<td class="TD1" style="text-align: center;">
													<a href="kampagne.php?kKampagne={$kKampagne}&defdetail=1&kKampagneDef={$kKampagneDef}&cZeitParam={$cZeitraumParam}">{$oKampagneStat_arr[$kKampagne][$kKampagneDef]}</a>
												</td>
											{/foreach}
										</tr>
									{/if}
								{/foreach}
								<tr>
									{assign var=colspan value=$oKampagneDef_arr|@count}
									{assign var=gesamtcolspan value=$colspan+1}
									<td colspan="{$gesamtcolspan}" style="height: 1em;"></td>
								</tr>
								<tr>
									<td class="TD1">{#kampagneOverall#}</td>
									{foreach name="kampagnendefs" from=$oKampagneDef_arr key=kKampagneDef item=oKampagneDef}
										<td class="TD1" style="text-align: center;">
											{$oKampagneStat_arr.Gesamt[$kKampagneDef]}
										</td>
									{/foreach}
								</tr>
							</table>
							<p class="tcenter container">
								<a href="kampagne.php?tab=globalestats&nStamp=-1" class="button blue">
									Fr&uuml;her
								</a>
								{if !$nGreaterNow}
									<a href="kampagne.php?tab=globalestats&nStamp=1" class="button blue">
										Sp&auml;ter
									</a>
								{/if}
							</p>
						</div>
					{else}
						<div class="box_info">{#noDataAvailable#}</div>
					{/if}
				</div>
			</div>

		</div>
	</div>
