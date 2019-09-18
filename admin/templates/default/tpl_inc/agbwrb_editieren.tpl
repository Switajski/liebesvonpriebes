{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: agbwrb_editieren.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}

{include file="tpl_inc/seite_header.tpl" cTitel=#agbwrb#}
<div id="content">
	
	{if isset($hinweis) && $hinweis|count_characters > 0}			
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}			
		<p class="box_error">{$fehler}</p>
	{/if}
		
	<div class="container">
		<form name="umfrage" method="post" action="agbwrb.php">
		<input type="hidden" name="{$session_name}" value="{$session_id}" />
		<input type="hidden" name="agbwrb" value="1" />
		<input type="hidden" name="agbwrb_editieren_speichern" value="1" />			
		<input type="hidden" name="kKundengruppe" value="{if isset($kKundengruppe)}{$kKundengruppe}{/if}" />
		
		{if isset($oAGBWRB->kText) && $oAGBWRB->kText > 0}
			<input type="hidden" name="kText" value="{if isset($oAGBWRB->kText)}{$oAGBWRB->kText}{/if}" />
		{/if}
		
		<div class="box_info">
			{#trustedShopInfo#}
		</div>
			
		<table class="list" id="formtable">
			
			<tr>
				<td>{#agbwrbStandard#}:</strong></td>
				<td>
					<select name="nStandard" class="combo">
						<option value="1"{if isset($oAGBWRB->nStandard) && $oAGBWRB->nStandard == 1} selected{/if}>{#agbwrbYes#}</option>
						<option value="0"{if isset($oAGBWRB->nStandard) && $oAGBWRB->nStandard == 0} selected{/if}>{#agbwrbNo#}</option>
					</select>
				</td>
			</tr>
		
			<tr>
				<td>{#agb#} (Text):</strong></td>
				<td><textarea name="cAGBContentText" rows="15" cols="60">{if isset($oAGBWRB->cAGBContentText)}{$oAGBWRB->cAGBContentText}{/if}</textarea></td>
			</tr>
			
			<tr>
				<td>{#agb#} (HTML):</strong></td>
				<td><textarea name="cAGBContentHtml" class="ckeditor" rows="15" cols="60">{if isset($oAGBWRB->cAGBContentHtml)}{$oAGBWRB->cAGBContentHtml}{/if}</textarea></td>
			</tr>
			
			<tr>
				<td>{#wrb#} (Text):</strong></td>
				<td><textarea name="cWRBContentText" rows="15" cols="60">{if isset($oAGBWRB->cWRBContentText)}{$oAGBWRB->cWRBContentText}{/if}</textarea></td>
			</tr>
			
			<tr>
				<td>{#wrb#} (HTML):</strong></td>
				<td><textarea name="cWRBContentHtml" class="ckeditor" rows="15" cols="60">{if isset($oAGBWRB->cWRBContentHtml)}{$oAGBWRB->cWRBContentHtml}{/if}</textarea></td>
			</tr>
			
			<tr>
				<td class="left">&nbsp;</td>
				<td><input name="agbwrbsubmit" type="submit" value="{#agbwrbSave#}" class="button orange" /></td>
			</tr>	
		</table>
		
			</form>
		
	</div>
</div>