{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: kampagne_detail.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2010 JTL-Software
-------------------------------------------------------------------------------
*}

<script type="text/javascript">
function changeSelect(currentSelect)
{ldelim}
	switch(currentSelect.options[currentSelect.selectedIndex].value)
	{ldelim}
		case "1":
			document.getElementById("SelectFromDay").style.display = "none";
			document.getElementById("SelectToDay").style.display = "none";
			break;
		case "2":
			document.getElementById("SelectFromDay").style.display = "none";
			document.getElementById("SelectToDay").style.display = "none";
			break;
		case "3":
			document.getElementById("SelectFromDay").style.display = "inline";
			document.getElementById("SelectToDay").style.display = "inline";
			break;
		case "4":
			document.getElementById("SelectFromDay").style.display = "inline";
			document.getElementById("SelectToDay").style.display = "inline";
			break;
	{rdelim}
{rdelim}

function selectSubmit(currentSelect)
{ldelim}
	$kKampagne = currentSelect.options[currentSelect.selectedIndex].value;
	
	if($kKampagne > 0)
		window.location.href = "kampagne.php?detail=1&kKampagne=" + $kKampagne;
{rdelim}
</script>

{include file="tpl_inc/seite_header.tpl" cTitel=#kampagneDetailStats#}
<div id="content">
	
	{if isset($hinweis) && $hinweis|count_characters > 0}			
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}			
		<p class="box_error">{$fehler}</p>
	{/if}
			
	<div id="payment">
		<div id="tabellenLivesuche">
		
			<form method="POST" action="kampagne.php">
			<input type="hidden" name="{$session_name}" value="{$session_id}" />
			<input type="hidden" name="detail" value="1" />
			<input type="hidden" name="zeitraum" value="1" />
			<input type="hidden" name="kKampagne" value="{$oKampagne->kKampagne}" />
			<table>
				<tr>
					<th class="th-1">{#kampagneDetailStats#} zu Kampagne <strong>{$oKampagne->cName}</strong></th> 
				</tr>
				
				<tr>
					<td>
						<div class="clearall">
							<div style="float: left; width: 25%;">
								<strong>{#kampagneDetailView#}:</strong><br />
								<select name="nAnsicht" class="combo" onChange="javascript:changeSelect(this);" style="width: 15em;">
									<option value="1"{if $smarty.session.Kampagne->nDetailAnsicht == 1} selected{/if}>{#kampagneStatYear#}</option>
									<option value="2"{if $smarty.session.Kampagne->nDetailAnsicht == 2} selected{/if}>{#kampagneStatMonth#}</option>
									<option value="3"{if $smarty.session.Kampagne->nDetailAnsicht == 3} selected{/if}>{#kampagneStatWeek#}</option>
									<option value="4"{if $smarty.session.Kampagne->nDetailAnsicht == 4} selected{/if}>{#kampagneStatDay#}</option>
								</select>
							</div>
							<div style="float: left; width: 25%;">
								<strong>{#kampagneDateFrom#}: </strong><br />
								<select name="cFromDay" class="combo" style="width: 4em;" id="SelectFromDay">
							{section name=fromDay loop=32 start=1 step=1}
									<option value="{$smarty.section.fromDay.index}"{if $smarty.session.Kampagne->cFromDate_arr.nTag == $smarty.section.fromDay.index} selected{/if}>{$smarty.section.fromDay.index}</option>
							{/section}
								</select>
								<select name="cFromMonth" class="combo" style="width: 8em;">
									<option value="1"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 1} selected{/if}>Januar</option>
									<option value="2"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 2} selected{/if}>Februar</option>
									<option value="3"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 3} selected{/if}>M&auml;rz</option>
									<option value="4"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 4} selected{/if}>April</option>
									<option value="5"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 5} selected{/if}>Mai</option>
									<option value="6"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 6} selected{/if}>Juni</option>
									<option value="7"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 7} selected{/if}>Juli</option>
									<option value="8"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 8} selected{/if}>August</option>
									<option value="9"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 9} selected{/if}>September</option>
									<option value="10"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 10} selected{/if}>Oktober</option>
									<option value="11"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 11} selected{/if}>November</option>
									<option value="12"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 12} selected{/if}>Dezember</option>
								</select>
							{assign var=cJahr value=$smarty.now|date_format:"%Y"}
								<select name="cFromYear" class="combo" style="width: 6em;">							
							{section name=fromYear loop=$cJahr+1 start=2005 step=1}
									<option value="{$smarty.section.fromYear.index}"{if $smarty.session.Kampagne->cFromDate_arr.nJahr == $smarty.section.fromYear.index} selected{/if}>{$smarty.section.fromYear.index}</option>
							{/section}
								</select>
							</div>	
								
							<div style="float: left; width: 25%;">
								<strong>{#kampagneDateTill#}: </strong><br />
								<select name="cToDay" class="combo" style="width: 4em;" id="SelectToDay">
							{section name=toDay loop=32 start=1 step=1}
									<option value="{$smarty.section.toDay.index}"{if $smarty.session.Kampagne->cToDate_arr.nTag == $smarty.section.toDay.index} selected{/if}>{$smarty.section.toDay.index}</option>
							{/section}
								</select>
								<select name="cToMonth" class="combo" style="width: 8em;">
									<option value="1"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 1} selected{/if}>Januar</option>
									<option value="2"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 2} selected{/if}>Februar</option>
									<option value="3"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 3} selected{/if}>M&auml;rz</option>
									<option value="4"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 4} selected{/if}>April</option>
									<option value="5"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 5} selected{/if}>Mai</option>
									<option value="6"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 6} selected{/if}>Juni</option>
									<option value="7"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 7} selected{/if}>Juli</option>
									<option value="8"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 8} selected{/if}>August</option>
									<option value="9"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 9} selected{/if}>September</option>
									<option value="10"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 10} selected{/if}>Oktober</option>
									<option value="11"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 11} selected{/if}>November</option>
									<option value="12"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 12} selected{/if}>Dezember</option>
								</select>
							{assign var=cJahr value=$smarty.now|date_format:"%Y"}
								<select name="cToYear" class="combo" style="width: 6em;">							
							{section name=toYear loop=$cJahr+1 start=2005 step=1}
									<option value="{$smarty.section.toYear.index}"{if $smarty.session.Kampagne->cToDate_arr.nJahr == $smarty.section.toYear.index} selected{/if}>{$smarty.section.toYear.index}</option>
							{/section}
								</select>
							</div>
							<div style="float: left; width: 25%;">
								<strong>{#kampagneSingle#}: </strong><br />
								<select name="kKampagne" class="combo" style="width: 15em;" onChange="javascript:selectSubmit(this);">
							{if isset($oKampagne_arr) && $oKampagne_arr|@count > 0}
								{foreach name=kampagnen from=$oKampagne_arr item=oKampagneTMP}
									<option value="{$oKampagneTMP->kKampagne}"{if $oKampagneTMP->kKampagne == $oKampagne->kKampagne} selected{/if}>{$oKampagneTMP->cName}</option>
								{/foreach}
							{/if}
								</select>
							</div>
						
						</div>
						<div class="tcenter container">
							<input name="submitZeitraum" type="submit" value="{#kampagneDetailStatsBTN#}" class="button blue" />
						</div>
					</td>
				</tr>
			</table>
			</form>
			
		</div>
		
		<div class="container">
			<a href="kampagne.php?{$session_name}={$session_id}&tab=globalestats" class="button">{#kampagneBackBTN#}</a>
		</div>
		
		<div class="tabber">                        
			
			<div class="tabbertab{if isset($cTab) && $cTab == 'detailansicht'} tabbertabdefault{/if}">
				<h2>{#kampagneDetailStats#}</h2>
				
			{if isset($oKampagneStat_arr) && $oKampagneStat_arr|@count > 0 && isset($oKampagneDef_arr) && $oKampagneDef_arr|@count > 0}
				<div id="tabellenLivesuche">
					<table>
						<tr>
							<th class="th-1"></th>
						{foreach name="kampagnendefs" from=$oKampagneDef_arr item=oKampagneDef}
							<th class="th-2">{$oKampagneDef->cName}</th>
						{/foreach}
						</tr>							

					{foreach name="kampagnenstats" from=$oKampagneStat_arr key=kKey item=oKampagneStatDef_arr}
					{if $kKey != "Gesamt"}
						<tr class="tab_bg{$smarty.foreach.kampagnenstats.iteration%2}">
						{if isset($oKampagneStat_arr[$kKey].cDatum)}						
							<td class="TD1">{$oKampagneStat_arr[$kKey].cDatum}</td>
						{/if}
						{foreach name="kampagnendefs" from=$oKampagneStatDef_arr key=kKampagneDef item=oKampagneStatDef_arr}
							{if $kKampagneDef != "cDatum"}
							<td class="TD1" style="text-align: center;">						
								<a href="kampagne.php?kKampagne={$oKampagne->kKampagne}&defdetail=1&kKampagneDef={$kKampagneDef}&cStamp={$kKey}">{$oKampagneStat_arr[$kKey][$kKampagneDef]}</a>
							</td>
							{/if}
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
						{foreach name="kampagnendefs" from=$oKampagneStatDef_arr key=kKampagneDef item=oKampagneStatDef_arr}
							<td class="TD1" style="text-align: center;">									
								{$oKampagneStat_arr.Gesamt[$kKampagneDef]}
							</td>
						{/foreach}
						</tr>
					</table>
				</div>
			{else}
				<div class="box_info">{#noDataAvailable#}</div>
			{/if}
			</div>
			
			<div class="tabbertab{if isset($cTab) && $cTab == 'detailgraphen'} tabbertabdefault{/if}">
				<br />
				<h2>{#kampagneDetailGraph#}</h2>
				
		    {if $Charts|@count > 0}
		        {foreach name=charts from=$Charts key=key item=Chart}
		             <br />
		             <h2 style="display: block">{$TypeNames[$key]}:</h2>
		             <br />
			        {if isset($headline)}
				        {assign var=hl value=$headline}
			        {else}
				        {assign var=hl value=null}
			        {/if}
			        {if isset($headline)}
				        {assign var=ylabel value=$ylabel}
			        {else}
				        {assign var=ylabel value=null}
			        {/if}
                     {include file='tpl_inc/linechart_inc.tpl' linechart=$Chart headline=$hl id=$key width='98%' height='400px' ylabel=$ylabel href=false legend=false ymin='0'}
                     <br />
		        {/foreach}
		    {else}
                <div class="box_info">{#noDataAvailable#}</div>
		    {/if}
			</div>
		</div>
		
	</div>
	
	<br />
	
	<div class="container">
		<a href="kampagne.php?{$session_name}={$session_id}&tab=globalestats" class="button">{#kampagneBackBTN#}</a>
	</div>
</div>

{if $smarty.session.Kampagne->nDetailAnsicht == 1 || $smarty.session.Kampagne->nDetailAnsicht == 2}
<script type="text/javascript">
	document.getElementById("SelectFromDay").style.display = "none";
	document.getElementById("SelectToDay").style.display = "none";
</script>
{/if}