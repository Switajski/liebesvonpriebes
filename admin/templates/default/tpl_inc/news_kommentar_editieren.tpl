{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: news_kommentar_editieren.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}

{include file="tpl_inc/seite_header.tpl" cTitel=#newsCommentEdit#}
<div id="content">
	
	{if isset($hinweis) && $hinweis|count_characters > 0}			
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}			
		<p class="box_error">{$fehler}</p>
	{/if}
	
	<div class="container">
		<form name="umfrage" method="post" action="news.php">
		<input type="hidden" name="{$session_name}" value="{$session_id}">
		<input type="hidden" name="news" value="1" />
		<input type="hidden" name="nkedit" value="1" />
			<input type="hidden" name="tab" value="inaktiv" />
			{if $nFZ == 1}<input name="nFZ" type="hidden" value="1">{/if}
		<input type="hidden" name="kNews" value="{$oNewsKommentar->kNews}" />
		<input type="hidden" name="kNewsKommentar" value="{$oNewsKommentar->kNewsKommentar}" />
		
		<table class="list" id="formtable">
			
			<tr>
				<td>{#newsHeadline#}:</strong></td>
				<td><input name="cName" type="text"  value="{$oNewsKommentar->cName}" /></td>
			</tr>
			
			<tr>
				<td>{#newsText#}:</strong></td>
				<td><textarea class="ckeditor" name="cKommentar" rows="15" cols="60">{$oNewsKommentar->cKommentar}</textarea></td>
			</tr>
			
		</table>
		<div class="save_wrapper">
			<input name="newskommentarsavesubmit" type="submit" value="{#newsSave#}" class="button orange" />
		</div>
		
			</form>
	</div>
</div>