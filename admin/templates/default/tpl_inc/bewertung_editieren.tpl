{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: umfrage_erstellen.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}

{include file="tpl_inc/seite_header.tpl" cTitel=#bearbeiteBewertung#}
<div id="content">
	 {if isset($hinweis) && $hinweis|count_characters > 0}			
		  <p class="box_success">{$hinweis}</p>
	 {/if}
	 {if isset($fehler) && $fehler|count_characters > 0}			
		  <p class="box_error">{$fehler}</p>
	 {/if}
	
	<div class="container">
		<form name="umfrage" method="post" action="bewertung.php">
		<input type="hidden" name="{$session_name}" value="{$session_id}" />
		<input type="hidden" name="bewertung_editieren" value="1" />
			<input type="hidden" name="tab" value="{$cTab}" />
			{if $nFZ == 1}<input name="nFZ" type="hidden" value="1">{/if}
		<input type="hidden" name="kBewertung" value="{$oBewertung->kBewertung}" />
		
		<table class="kundenfeld" id="formtable">
			
			<tr>
				<td>{#customerName#}:</strong></td>
				<td><input name="cName" type="text"  value="{$oBewertung->cName}" /></td>
			</tr>
			
			<tr>
				<td>{#ratingTitle#}:</strong></td>
				<td><input name="cTitel" type="text"  value="{$oBewertung->cTitel}" /></td>
			</tr>
			
			<tr>
				<td>{#ratingStars#}:</strong></td>
				<td>
					<select name="nSterne" class="combo" style="width: 50px;">
						<option value="1"{if $oBewertung->nSterne == 1} selected{/if}>1</option>
						<option value="2"{if $oBewertung->nSterne == 2} selected{/if}>2</option>
						<option value="3"{if $oBewertung->nSterne == 3} selected{/if}>3</option>
						<option value="4"{if $oBewertung->nSterne == 4} selected{/if}>4</option>
						<option value="5"{if $oBewertung->nSterne == 5} selected{/if}>5</option>
					</select>
				</td>
			</tr>
			
			<tr>
				<td>{#ratingText#}:</strong></td>
				<td><textarea class="ckeditor" name="cText" rows="15" cols="60">{$oBewertung->cText}</textarea></td>
			</tr>
			
		</table>
		<div class="save_wrapper">
			<input name="bewertungsubmit" type="submit" value="{#ratingSave#}" class="button orange" />
		</div>
		</form>
	</div>
	
</div>