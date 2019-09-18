{*
-------------------------------------------------------------------------------
    JTL-Shop 3
    File: statistik_header.tpl, smarty template inc file
    
    page for JTL-Shop 3 
    Admin
    
    Author: daniel.boehemr@jtl-software.de, JTL-Software
    http://www.jtl-software.de
    
    Copyright (c) 2010 JTL-Software
-------------------------------------------------------------------------------
*}

<script type="text/javascript">
function changeStatType(elem)
{ldelim}
	window.location.href = "statistik.php?s=" + elem.options[elem.selectedIndex].value;
{rdelim}
</script>

{include file="tpl_inc/seite_header.tpl" cTitel=#statisticTitle# cBeschreibung=#statisticDesc# cDokuURL=#statisticURL#}
<div id="content">
	<div class="block">
		<label for="statType">Statistiktyp:</label>
		<select name="statType" id="statType" onChange="javascript:changeStatType(this);">
			<option value="{$STATS_ADMIN_TYPE_BESUCHER}"{if $nTyp == $STATS_ADMIN_TYPE_BESUCHER} selected{/if}>Besucher</option>
			<option value="{$STATS_ADMIN_TYPE_KUNDENHERKUNFT}"{if $nTyp == $STATS_ADMIN_TYPE_KUNDENHERKUNFT} selected{/if}>Kundenherkunft</option>
			<option value="{$STATS_ADMIN_TYPE_SUCHMASCHINE}"{if $nTyp == $STATS_ADMIN_TYPE_SUCHMASCHINE} selected{/if}>Suchmaschinen</option>
			<option value="{$STATS_ADMIN_TYPE_UMSATZ}"{if $nTyp == $STATS_ADMIN_TYPE_UMSATZ} selected{/if}>Umsatz</option>
		</select>
	</div>
			
	{if isset($cHinweis) && $cHinweis|count_characters > 0}			
		<p class="box_success">{$cHinweis}</p>
	{/if}
	{if isset($cFehler) && $cFehler|count_characters > 0}			
		<p class="box_error">{$cFehler}</p>
	{/if}
	
	
	<div class="container">
		<form method="POST" action="statistik.php">
			
			<ul class="hlist">
				<li class="p50">
					<strong>Von:</strong>
					<select name="cTagVon">
						<option value="0">TAG</option>
					{section name=tagvon start=1 loop=32 step=1}
						<option value="{$smarty.section.tagvon.index}"{if $cPostVar_arr.cTagVon == $smarty.section.tagvon.index} selected{/if}>{$smarty.section.tagvon.index}</option>
					{/section}
					</select>
					
					<select name="cMonatVon">
						<option value="0">MONAT</option>
					{section name=monatvon start=1 loop=13 step=1}
						<option value="{$smarty.section.monatvon.index}"{if $cPostVar_arr.cMonatVon == $smarty.section.monatvon.index} selected{/if}>{$smarty.section.monatvon.index}</option>
					{/section}
					</select>
					
					<select name="cJahrVon">
						<option value="0">JAHR</option>
					{section name=jahrvon start=2009 loop=2021 step=1}
						<option value="{$smarty.section.jahrvon.index}"{if $cPostVar_arr.cJahrVon == $smarty.section.jahrvon.index} selected{/if}>{$smarty.section.jahrvon.index}</option>
					{/section}
					</select>
					
					<strong>- Bis:</strong>
					<select name="cTagBis">
						<option value="0">TAG</option>
					{section name=tagbis start=1 loop=32 step=1}
						<option value="{$smarty.section.tagbis.index}"{if $cPostVar_arr.cTagBis == $smarty.section.tagbis.index} selected{/if}>{$smarty.section.tagbis.index}</option>
					{/section}
					</select>
					
					<select name="cMonatBis">
						<option value="0">MONAT</option>
					{section name=monatbis start=1 loop=13 step=1}
						<option value="{$smarty.section.monatbis.index}"{if $cPostVar_arr.cMonatBis == $smarty.section.monatbis.index} selected{/if}>{$smarty.section.monatbis.index}</option>
					{/section}
					</select>
					
					<select name="cJahrBis">
						<option value="0">JAHR</option>
					{section name=jahrbis start=2009 loop=2021 step=1}
						<option value="{$smarty.section.jahrbis.index}"{if $cPostVar_arr.cJahrBis == $smarty.section.jahrbis.index} selected{/if}>{$smarty.section.jahrbis.index}</option>
					{/section}
					</select>
					
					<input name="btnDatum" type="submit" value="Go" class="button blue" />
				</li>
				<li class="p50 tright">
					<button name="btnZeit" type="submit" value="1" {if $btnZeit == 1}class="blue"{/if}>Heute</button>
					<button name="btnZeit" type="submit" value="2" {if $btnZeit == 2}class="blue"{/if}>diese Woche</button>
					<button name="btnZeit" type="submit" value="3" {if $btnZeit == 3}class="blue"{/if}>letzte Woche</button>
					<button name="btnZeit" type="submit" value="4" {if $btnZeit == 4}class="blue"{/if}>diesen Monat</button>
					<button name="btnZeit" type="submit" value="5" {if $btnZeit == 5}class="blue"{/if}>letzten Monat</button>
					<button name="btnZeit" type="submit" value="6" {if $btnZeit == 6}class="blue"{/if}>dieses Jahr</button>
					<button name="btnZeit" type="submit" value="7" {if $btnZeit == 7}class="blue"{/if}>letztes Jahr</button>
				</li>
			</ul>
		</form>