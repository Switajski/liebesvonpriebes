<form name="sprachestatus" method="post" action="rma.php">
	<input name="tab" type="hidden" value="status">
	<p class="txtCenter">
		<label for="{#changeLanguage#}">{#changeLanguage#}:</strong></label>
		<input type="hidden" name="sprachwechsel" value="1">
		<select name="kSprache" class="selectBox" onchange="javascript:document.sprachestatus.submit();">
        {foreach name=sprachen from=$Sprachen item=sprache}
			<option value="{$sprache->kSprache}" {if $sprache->kSprache==$smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
		{/foreach}
		</select>
	</p>
</form>

<div class="settings">
	
	<form method="POST" action="rma.php">
    	<input name="kSprache" type="hidden" value="{$smarty.session.kSprache}">
		<input name="tab" type="hidden" value="status">
		<input name="a" type="hidden" value="addStatus">
	{if isset($oRMAStatus)}
		<input name="kRMAStatus" type="hidden" value="{$oRMAStatus->getRMAStatus()}">
	{/if}
	
		<p>
        	<label for="cStatus">{#rmaStatus#}: {getHelpDesc cDesc="Wie soll der Status lauten?"}</label>
        	<input name="cStatus" type="text"{if isset($cPlausi_arr.cStatus)} class="fieldfillout"{/if} value="{if $cPost_arr.cStatus}{$cPost_arr.cStatus}{elseif isset($oRMAStatus)}{$oRMAStatus->getStatus()}{/if}">
        	{if isset($cPlausi_arr.cStatus)}<font class="fillout">{#FillOut#}</font>{/if}
        </p>
        
        <p>
        	<label for="eFunktion">{#rmaFunction#}: {getHelpDesc cDesc="Welche Funktion soll der Status erhalten? (RMA Einleitung / RMA Abgeschlossen / Keine)"}</label>
        	<select name="eFunktion"{if isset($cPlausi_arr.eFunktion)} class="fieldfillout"{/if}>
                <option value="keine"{if (isset($cPost_arr.eFunktion) && $cPost_arr.eFunktion == 'keine') || (isset($oRMAStatus) && $oRMAStatus->getFunktion() == 'keine')} selected{/if}>Keine</option>
            	<option value="start"{if (isset($cPost_arr.eFunktion) && $cPost_arr.eFunktion == 'start') || (isset($oRMAStatus) && $oRMAStatus->getFunktion() == 'start')} selected{/if}>RMA Einleitung</option>
            	<option value="ende"{if (isset($cPost_arr.eFunktion) && $cPost_arr.eFunktion == 'ende') || (isset($oRMAStatus) && $oRMAStatus->getFunktion() == 'ende')} selected{/if}>RMA Abgeschlossen</option>
			</select>
        	{if isset($cPlausi_arr.eFunktion)}<font class="fillout">{#FillOut#}</font>{/if}
        </p>
	
		<p>
        	<label for="nAktiv">{#rmaActive#}: {getHelpDesc cDesc="Soll der Grund im Frontend aktiv und somit angezeigt werden?"}</label>
        	<select name="nAktiv">
                <option value="1"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv == 1) || (isset($oRMAStatus) && $oRMAStatus->getAktiv() == 1)} selected{/if}>Ja</option>
            	<option value="0"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv == 0) || (isset($oRMAStatus) && $oRMAStatus->getAktiv() == 0)} selected{/if}>Nein</option>
			</select>
		</p>
	
		<p class="submit"><input name="saveStatusBTN" type="submit" value="{if isset($oRMAStatus) && $oRMAStatus->getRMAStatus() > 0}{#rmaUpdate#}{else}{#save#}{/if}" class="button orange" /></p>
	
	</form>
	
</div>

{if isset($oRMAStatus_arr) && $oRMAStatus_arr|@count > 0}
<br />
<br />
<form method="POST" action="rma.php">
	<input name="tab" type="hidden" value="status">
	<input name="a" type="hidden" value="delStatus">

	<table class="list">
		<thead>
			<tr>
				<th style="width: 10px;">&nbsp;</th>
				<th class="tleft">{#rmaStatus#}</th>
				<th class="tleft">{#rmaFunction#}</th>
				<th class="tleft">{#rmaActive#}</th>
				<th class="tleft">&nbsp;</th>	
			</tr>
		</thead>
		<tbody>
		{foreach name=rmagrund from=$oRMAStatus_arr item=oRMAStatus}
			<tr>
				<td><input name="kRMAStatus_arr[]" type="checkbox" value="{$oRMAStatus->getRMAStatus()}" /></td>
				<td class="tleft">{$oRMAStatus->getStatus()}</td>
				<td class="tleft">{$oRMAStatus->getFunktion()}</td>
				<td class="tleft">{if $oRMAStatus->getAktiv() == 1}Ja{else}Nein{/if}</td>
				<td><a href="rma.php?a=editStatus&tab=status&kRMAStatus={$oRMAStatus->getRMAStatus()}" class="button">{#rmaEdit#}</a></td>
			</tr>
		{/foreach}
		</tbody>
		
		<tfoot>
			<tr>
				<td style="width: 10px;"><input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);"></td>
                <td colspan="5" class="tleft">{#globalSelectAll#}</td>
			</tr>
		</tfoot>
		
	</table>
	
	<p class="submit"><input name="delStatusBTN" type="submit" value="{#rmaDelete#}" class="button orange" /></p>
</form>
{/if}