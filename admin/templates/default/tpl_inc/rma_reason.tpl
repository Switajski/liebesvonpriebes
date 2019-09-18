<form name="sprachereason" method="post" action="rma.php">
	<input name="tab" type="hidden" value="reason">
	<p class="txtCenter">
		<label for="{#changeLanguage#}">{#changeLanguage#}:</strong></label>
		<input type="hidden" name="sprachwechsel" value="1">
		<select name="kSprache" class="selectBox" onchange="javascript:document.sprachereason.submit();">
        {foreach name=sprachen from=$Sprachen item=sprache}
			<option value="{$sprache->kSprache}" {if $sprache->kSprache==$smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
		{/foreach}
		</select>
	</p>
</form>

<div class="settings">
	
	<form method="POST" action="rma.php">
    	<input name="kSprache" type="hidden" value="{$smarty.session.kSprache}">
		<input name="tab" type="hidden" value="reason">
		<input name="a" type="hidden" value="addReason">
	{if isset($oRMAGrund)}
		<input name="kRMAGrund" type="hidden" value="{$oRMAGrund->getRMAGrund()}">
	{/if}
	
		<p>
        	<label for="cGrund">{#rmaReason#}: {getHelpDesc cDesc="Wie soll der Grund lauten?"}</label>
        	<input name="cGrund" type="text"{if isset($cPlausi_arr.cGrund)} class="fieldfillout"{/if} value="{if $cPost_arr.cGrund}{$cPost_arr.cGrund}{elseif isset($oRMAGrund)}{$oRMAGrund->getGrund()}{/if}">
        	{if isset($cPlausi_arr.cGrund)}<font class="fillout">{#FillOut#}</font>{/if}
        </p>
        
        <p>
        	<label for="cKommentar">{#rmaCommentheadline#}: {getHelpDesc cDesc="Wie soll die Kommentar&uuml;berschrift lauten?"}</label>
        	<input name="cKommentar" type="text"{if isset($cPlausi_arr.cKommentar)} class="fieldfillout"{/if} value="{if $cPost_arr.cKommentar}{$cPost_arr.cKommentar}{elseif isset($oRMAGrund)}{$oRMAGrund->getKommentar()}{/if}">
        	{if isset($cPlausi_arr.cKommentar)}<font class="fillout">{#FillOut#}</font>{/if}
        </p>
        
        <p>
        	<label for="nSort">{#rmaSort#}: {getHelpDesc cDesc="In welcher Reihenfolge sollen die Gr&uuml;nde angezeigt werden? (Umso h&ouml;her desto weiter unten, z.b. 3)"}</label>
        	<input name="nSort" type="text" value="{if isset($oRMAGrund)}{$oRMAGrund->getSort()}{/if}">
        </p>
	
		<p>
        	<label for="nAktiv">{#rmaActive#}: {getHelpDesc cDesc="Soll der Grund im Frontend aktiv und somit angezeigt werden?"}</label>
        	<select name="nAktiv">
                <option value="1"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv == 1) || (isset($oRMAGrund) && $oRMAGrund->getAktiv() == 1)} selected{/if}>Ja</option>
            	<option value="0"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv == 0) || (isset($oRMAGrund) && $oRMAGrund->getAktiv() == 0)} selected{/if}>Nein</option>
			</select>
		</p>
	
		<p class="submit"><input name="saveReasonBTN" type="submit" value="{if isset($oRMAGrund) && $oRMAGrund->getRMAGrund() > 0}{#rmaUpdate#}{else}{#save#}{/if}" class="button orange" /></p>
	
	</form>
	
</div>

{if isset($oRMAGrund_arr) && $oRMAGrund_arr|@count > 0}
<br />
<br />
<form method="POST" action="rma.php">
	<input name="tab" type="hidden" value="reason">
	<input name="a" type="hidden" value="delReason">

	<table class="list">
		<thead>
			<tr>
				<th style="width: 10px;">&nbsp;</th>
				<th class="tleft">{#rmaReason#}</th>
				<th class="tleft">{#rmaCommentheadline#}</th>
				<th class="tleft">{#rmaSort#}</th>
				<th class="tleft">{#rmaActive#}</th>
				<th class="tleft">&nbsp;</th>	
			</tr>
		</thead>
		<tbody>
		{foreach name=rmagrund from=$oRMAGrund_arr item=oRMAGrund}
			<tr>
				<td><input name="kRMAGrund_arr[]" type="checkbox" value="{$oRMAGrund->getRMAGrund()}" /></td>
				<td class="tleft">{$oRMAGrund->getGrund()}</td>
				<td class="tleft">{$oRMAGrund->getKommentar()}</td>
				<td class="tleft">{$oRMAGrund->getSort()}</td>
				<td class="tleft">{if $oRMAGrund->getAktiv() == 1}Ja{else}Nein{/if}</td>
				<td><a href="rma.php?a=editReason&tab=reason&kRMAGrund={$oRMAGrund->getRMAGrund()}" class="button">{#rmaEdit#}</a></td>
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
	
	<p class="submit"><input name="delReasonBTN" type="submit" value="{#rmaDelete#}" class="button orange" /></p>
</form>
{/if}