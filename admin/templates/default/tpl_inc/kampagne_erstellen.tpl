{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: kampagne_erstellen.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2010 JTL-Software
-------------------------------------------------------------------------------
*}

<script type="text/javascript">
function changeWertSelect(currentSelect)
{ldelim}	
	if(currentSelect.selectedIndex == "0")
		document.getElementById("cWertInput").style.display = "block";
	else if(currentSelect.selectedIndex == "1")
		document.getElementById("cWertInput").style.display = "none";
{rdelim}
</script>

{if isset($oKampagne->kKampagne) && $oKampagne->kKampagne > 0}
{include file="tpl_inc/seite_header.tpl" cTitel=#kampagneEdit#}
{else}
{include file="tpl_inc/seite_header.tpl" cTitel=#kampagneCreate#}
{/if}

<div id="content">
	
	{if isset($hinweis) && $hinweis|count_characters > 0}			
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}			
		<p class="box_error">{$fehler}</p>
	{/if}
			
	<div class="container">
		<form method="post" action="kampagne.php">
		<input type="hidden" name="{$session_name}" value="{$session_id}" />
			<input type="hidden" name="tab" value="uebersicht" />
		<input type="hidden" name="erstellen_speichern" value="1" />
	{if isset($oKampagne->kKampagne) && $oKampagne->kKampagne > 0}
		<input type="hidden" name="kKampagne" value="{$oKampagne->kKampagne}" />
	{/if}
		
		<table class="kundenfeld" id="formtable">				
			<tr>
				<td>{#kampagneName#}</td>
				<td><input name="cName" type="text"  value="{$oKampagne->cName}"{if isset($oKampagne->kKampagne) && $oKampagne->kKampagne < 1000} disabled{/if} /></td>
			</tr>
			
			<tr>
				<td>{#kampagneParam#}</td>
				<td><input name="cParameter" type="text"  value="{$oKampagne->cParameter}" /></td>
			</tr>
			
			<tr>
				<td>{#kampagneValue#}</td>
				<td>
					<select name="nDynamisch" class="combo" id="cWertSelect" onChange="javascript:changeWertSelect(this);"{if isset($oKampagne->kKampagne) && $oKampagne->kKampagne < 1000} disabled{/if}>							
						<option value="0"{if $oKampagne->nDynamisch == 0} selected{/if}>Fester Wert</option>
						<option value="1"{if $oKampagne->nDynamisch == 1} selected{/if}>Dynamisch</option>
					</select>
					<div id="cWertInput" style="display: {if $oKampagne->nDynamisch == 0}block{else}none{/if};">
						<br />
						<strong>{#kampagneValueStatic#}: </strong>
						<input name="cWert" type="text"  value="{$oKampagne->cWert}"{if isset($oKampagne->kKampagne) && $oKampagne->kKampagne < 1000} disabled{/if} />
					</div>
				</td>					
			</tr>
			
			<tr>
				<td>{#kampagnenActive#}</td>
				<td>
					<select name="nAktiv" class="combo">
						<option value="0"{if $oKampagne->nAktiv == 0} selected{/if}>Nein</option>
						<option value="1"{if $oKampagne->nAktiv == 1} selected{/if}>Ja</option>							
					</select>
				</td>
			</tr>
			
		</table>
		<p class="submit"><input name="submitSave" type="submit" value="{#save#}" class="button orange"/></p>
			</form>
		<a href="kampagne.php?{$session_name}={$session_id}&tab=uebersicht" class="container button">{#kampagneBackBTN#}</a>
	</div>
</div>