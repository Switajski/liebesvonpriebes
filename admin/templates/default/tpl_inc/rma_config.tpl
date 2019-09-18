<form name="einstellen" method="post" action="rma.php">
	<input type="hidden" name="{$session_name}" value="{$session_id}">
	<input type="hidden" name="a" value="saveSettings">
	<input name="tab" type="hidden" value="config">
	<div class="settings">
{foreach name=conf from=$oConfig_arr item=oConfig}
	{if $oConfig->cConf == "Y"}
		<p><label for="{$oConfig->cWertName}">{$oConfig->cName} {if $oConfig->cBeschreibung}<img src="{$currentTemplateDir}gfx/help.png" alt="{$oConfig->cBeschreibung}" title="{$oConfig->cBeschreibung}" style="vertical-align:middle; cursor:help;" /></label>{/if}
		{if $oConfig->cInputTyp=="selectbox"}
			<select name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" class="combo"> 
			{foreach name=selectfor from=$oConfig->ConfWerte item=wert}
				<option value="{$wert->cWert}" {if $oConfig->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
			{/foreach}
			</select>
		{else}	
			<input type="text" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}"  value="{$oConfig->gesetzterWert}" tabindex="1" />
		{/if}
		</p>
	{else}
		{if $oConfig->cName}<p style="text-align: center;"><strong>{$oConfig->cName}</strong></p>{/if}
	{/if}
{/foreach}
	</div>

	<p class="submit"><input name="speicherSettings" class="button orange" type="submit" value="{#save#}" /></p>
</form>