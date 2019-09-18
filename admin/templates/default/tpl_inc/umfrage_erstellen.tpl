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
<script type="text/javascript">
if(document.getElementById("kupon").selectedIndex == 0)
{ldelim}
	document.getElementById('fGuthaben').disabled = false;
	document.getElementById('nBonuspunkte').disabled = false;
{rdelim}
else
{ldelim}
	document.getElementById('fGuthaben').disabled = true;
	document.getElementById('nBonuspunkte').disabled = true;
{rdelim}

function selectCheck(selectBox)
{ldelim}
	if(selectBox.selectedIndex == 0)
	{ldelim}
		document.getElementById('fGuthaben').disabled = false;
		document.getElementById('nBonuspunkte').disabled = false;
		document.getElementById('fGuthaben').value = "";
		document.getElementById('nBonuspunkte').value = "";
	{rdelim}
	else
	{ldelim}
		document.getElementById('fGuthaben').disabled = true;
		document.getElementById('nBonuspunkte').disabled = true;
		document.getElementById('fGuthaben').value = "";
		document.getElementById('nBonuspunkte').value = "";
	{rdelim}
{rdelim}

function checkInput(inputField, cFeld)
{ldelim}	
	{ldelim}
		document.getElementById('kupon').disabled = true;
		document.getElementById('kupon').selectedIndex = 0;
		if(cFeld == "fGuthaben")
			document.getElementById('nBonuspunkte').disabled = true;
		else
		document.getElementById('fGuthaben').disabled = true;
		inputField.disabled = false;
	{rdelim}
{rdelim}

function clearInput(inputField)
{ldelim}
	if(inputField.value.length == 0)
	{ldelim}
		document.getElementById('kupon').disabled = false;
		document.getElementById('fGuthaben').disabled = false;
		document.getElementById('nBonuspunkte').disabled = false;
	{rdelim}
{rdelim}
</script>

<div id="page">
	<div id="content">
		<div id="welcome" class="post">
			<h2 class="title"><span>{#umfrageEnter#}</span></h2>
		</div>
		
		{if $hinweis}
			<br />
			<div class="userNotice">
				{$hinweis}
			</div>
		{/if}
		{if $fehler}
			<br />
			<div class="userError">
				{$fehler}
			</div>
		{/if}
		
		<br />
		
		<div class="container">
			<form name="umfrage" method="post" action="umfrage.php">
			<input type="hidden" name="{$session_name}" value="{$session_id}" />
			<input type="hidden" name="umfrage" value="1" />
			<input type="hidden" name="umfrage_speichern" value="1" />
            <input type="hidden" name="tab" value="umfrage" />
            <input type="hidden" name="s1" value="{$s1}" />
			{if $oUmfrage->kUmfrage > 0}
			<input type="hidden" name="umfrage_edit_speichern" value="1" />
			<input type="hidden" name="kUmfrage" value="{$oUmfrage->kUmfrage}" />
			{/if}
			
			<table class="kundenfeld" id="formtable">				
				<tr>
					<td>{#umfrageName#}:</strong></td>
					<td><input name="cName" type="text"  value="{$oUmfrage->cName}" /></td>
				</tr>
				
				<tr>
					<td>{#umfrageSeo#}:</strong></td>
					<td><input name="cSeo" type="text"  value="{$oUmfrage->cSeo}" /></td>
				</tr>
				
				<tr>
					<td>{#umfrageCustomerGrp#}:</strong></td>
					<td>
						<select name="kKundengruppe[]" multiple="multiple" class="combo">
							<option value="-1" {foreach name=kundengruppen from=$oUmfrage->kKundengruppe_arr item=kKundengruppe}{if $kKundengruppe == "-1"}selected{/if}{/foreach}>Alle</option>
						{foreach name=kundengruppen from=$oKundengruppe_arr item=oKundengruppe}
							<option value="{$oKundengruppe->kKundengruppe}" {foreach name=kkundengruppen from=$oUmfrage->kKundengruppe_arr item=kKundengruppe}{if $oKundengruppe->kKundengruppe == $kKundengruppe}selected{/if}{/foreach}>{$oKundengruppe->cName}</option>
						{/foreach}
						</select>
					</td>
				</tr>
				
				<tr>
					<td>{#umfrageValidation#}:</strong></td>
					<td>{#umfrageFrom#}: <input name="dGueltigVon" type="text"  value="{if $oUmfrage->dGueltigVon_de|count_characters > 0}{$oUmfrage->dGueltigVon_de}{else}{$smarty.now|date_format:'%d.%m.%Y %H:%M'}{/if}" style="width: 150px;" /> {#umfrageTo#}: <input name="dGueltigBis" type="text"  value="{$oUmfrage->dGueltigBis_de}" style="width: 150px;" /></td>
				</tr>
				
				<tr>
					<td>{#umfrageActive#}:</strong></td>
					<td>
						<select name="nAktiv" class="combo" style="width: 80px;">
							<option value="1"{if $oUmfrage->nAktiv == 1} selected{/if}>Ja</option>
							<option value="0"{if $oUmfrage->nAktiv == 0} selected{/if}>Nein</option>
						</select>
					</td>
				</tr>
				
				{if $oKupon_arr|@count > 0 && $oKupon_arr}
				<tr>
					<td>{#umfrageCoupon#}:</strong></td>
					<td valign="top">
						<select id="kupon" name="kKupon" class="combo" onchange="selectCheck(this);">
							<option value="0"{if $oUmfrage->kKupon == 0} selected{/if} index=0>{#umfrageNoCoupon#}</option>
						{foreach name=kupon from=$oKupon_arr item=oKupon}
							<option value="{$oKupon->kKupon}"{if $oKupon->kKupon == $oUmfrage->kKupon} selected{/if}>{$oKupon->cName}</option>
						{/foreach}
						</select>
					</td>
				</tr>
				{/if}
				
				<tr>
					<td>{#umfrageCredits#}:</strong></td>
					<td><input name="fGuthaben" id="fGuthaben" type="text"  value="{$oUmfrage->fGuthaben}" onclick="checkInput(this,'fGuthaben');" onblur="clearInput(this);" /></td>
				</tr>
				
                <!--
				<tr>
					<td>{#umfrageExtraPoints#}:</strong></td>
					<td><input name="nBonuspunkte" id="nBonuspunkte" type="text"  value="{$oUmfrage->nBonuspunkte}" onclick="checkInput(this,'nBonuspunkte')" onblur="clearInput(this);" /></td>
				</tr>
                -->
				
				<tr>
					<td>{#umfrageText#}:</strong></td>
					<td><textarea class="ckeditor" name="cBeschreibung" rows="15" cols="60">{$oUmfrage->cBeschreibung}</textarea></td>
				</tr>
			</table>
			
			<table class="kundenfeld">
				<tr>
					<td class="left">&nbsp;</td>
					<td id="buttons">
						<input name="speichern" type="button" value="{#umfrageSave#}" onclick="javascript:document.umfrage.submit();" />
					</td>
				</tr>
			</table>
            </form>
			<a href="umfrage.php?{$session_name}={$session_id}">{#umfrageBack#}</a>
		</div>
	</div>
</div>