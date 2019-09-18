{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: news_vorschau.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}

{include file="tpl_inc/seite_header.tpl" cTitel=#news# cBeschreibung=#newsDesc#}
<div id="content">
	
	{if isset($hinweis) && $hinweis|count_characters > 0}			
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}			
		<p class="box_error">{$fehler}</p>
	{/if}
			
	<div class="container">
		<div class="category first clearall">
			<div class="left">{$oNews->cBetreff}</div>
			<div class="no_overflow tright">{$oNews->Datum}</div>
		</div>
		
		<div class="container">
			{$oNews->cText}
		</div>
		
		{if $oNewsKommentar_arr|@count > 0}
		<form method="post" action="news.php">
			<input type="hidden" name="{$session_name}" value="{$session_id}" />
			<input type="hidden" name="news" value="1" />
			<input type="hidden" name="kNews" value="{$oNews->kNews}" />
			<input type="hidden" name="kommentare_loeschen" value="1" />
			<input type="hidden" name="nd" value="1" />
			<div class="category">{#newsComments#}</div>
			{foreach name=kommentare from=$oNewsKommentar_arr item=oNewsKommentar}
			<table width="100%" cellpadding="5" cellspacing="5" class="kundenfeld">
			<tr>
			<td valign="top" align="left" style="width: 33%;">
			
			<table>
			<tr>
			<td style="width: 10px;"><input name="kNewsKommentar[]" type="checkbox" value="{$oNewsKommentar->kNewsKommentar}" /></td>
			<td><b>
			{if $oNewsKommentar->cVorname|count_characters > 0}	
			{$oNewsKommentar->cVorname} {$oNewsKommentar->cNachname|truncate:1:""}. <a href="news.php?news=1&kNews={$oNews->kNews}&kNewsKommentar={$oNewsKommentar->kNewsKommentar}&nkedit=1&{$session_name}={$session_id}" class="button edit">{#newsEdit#}</a>
			{else}
			{$oNewsKommentar->cName}
			{/if}	
			</b></td>
			</tr>
			<tr>
			<td>&nbsp;</td>
			<td>{$oNewsKommentar->dErstellt_de}</td>
			</tr>
			<tr>
			<td>&nbsp;</td>
			<td>{$oNewsKommentar->cKommentar}</td>
			</tr>
			</table>	
			
			</td>
			</tr>
			</table>
			{/foreach}
			<div class="save_wrapper">
				<input name="kommentar_loeschen" type="submit" value="{#delete#}" class="button orange" />
			</div>
		</form>
		{/if}
	</div>
	<p><button type="button" onclick="location.href='../../../../news.php'">zur&uuml;ck</button></p>
</div>