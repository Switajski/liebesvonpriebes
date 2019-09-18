{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: exportformat_queue_erstellen.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
{include file="tpl_inc/seite_header.tpl" cTitel=#exportformatFormat#}
<div id="content">
	
    {if isset($hinweis) && $hinweis|count_characters > 0}			
        <p class="box_success">{$hinweis}</p>
    {/if}
    {if isset($fehler) && $fehler|count_characters > 0}			
        <p class="box_error">{$fehler}</p>
    {/if}
	
	<div class="container">
		<form name="exportformat_queue" method="post" action="exportformat_queue.php">
		<input type="hidden" name="{$session_name}" value="{$session_id}">
		<input type="hidden" name="erstellen_eintragen" value="1">
	{if isset($oCron->kCron) && $oCron->kCron > 0}
		<input type="hidden" name="kCron" value="{$oCron->kCron}">
	{/if}
		
	{if $oExportformat_arr|@count > 0}
		<table class="kundenfeld" id="formtable">				
			<tr>
				<td>{#exportformatFormat#}</td>
				<td>
					<select name="kExportformat">
						<option value="-1"></option>
						
					{foreach name=exportformate from=$oExportformat_arr item=oExportformat}
						<option value="{$oExportformat->kExportformat}"{if (isset($oFehler->kExportformat) && $oFehler->kExportformat == $oExportformat->kExportformat) || (isset($oCron->kKey) && $oCron->kKey == $oExportformat->kExportformat)} selected{/if}>{$oExportformat->cName} ({$oExportformat->Sprache->cNameDeutsch} / {$oExportformat->Waehrung->cName} / {$oExportformat->Kundengruppe->cName})</option>
					{/foreach}
						
					</select>
				</td>
			</tr>
			
			<tr>
				<td>{#exportformatStart#}</td>
				<td><input name="dStart" type="text"  value="{if isset($oFehler->dStart) && $oFehler->dStart|count_characters > 0}{$oFehler->dStart}{elseif isset($oCron->dStart_de) && $oCron->dStart_de|count_characters > 0}{$oCron->dStart_de}{else}{$smarty.now|date_format:'%d.%m.%Y %H:%M'}{/if}" /></td>
			</tr>

			<tr>
				<td>{#exportformatEveryXHour#}</td>
				<td>
				    <select name="nAlleXStunden">
				        <option value="24"{if (isset($oFehler->nAlleXStunden) && $oFehler->nAlleXStunden|count_characters > 0 && $oFehler->nAlleXStunden == 24) || (isset($oCron->nAlleXStd) && $oCron->nAlleXStd|count_characters > 0 && $oCron->nAlleXStd == 24)} selected{/if}>24 Stunden</option>
				        <option value="48"{if (isset($oFehler->nAlleXStunden) && $oFehler->nAlleXStunden|count_characters > 0 && $oFehler->nAlleXStunden == 48) || (isset($oCron->nAlleXStd) && $oCron->nAlleXStd|count_characters > 0 && $oCron->nAlleXStd == 48)} selected{/if}>48 Stunden</option>
				        <option value="168"{if (isset($oFehler->nAlleXStunden) && $oFehler->nAlleXStunden|count_characters > 0 && $oFehler->nAlleXStunden == 168) || (isset($oCron->nAlleXStd) && $oCron->nAlleXStd|count_characters > 0 && $oCron->nAlleXStd == 168)} selected{/if}>1 Woche</option>
				    </select>
                </td>
			</tr>			
		</table>
		
		<div class="save_wrapper">
			<input name="speichern" type="submit" value="{#exportformatAdd#}" class="button orange" />
		</div>
	{else}
		<div class="box_info">{#exportformatNoFormat#}</div>
	{/if}	
		
			</form>
	</div>
</div>
