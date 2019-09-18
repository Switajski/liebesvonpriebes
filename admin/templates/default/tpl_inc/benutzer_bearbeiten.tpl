{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

<script type="text/javascript">
{literal}
$(document).ready(function() {
	$('#tmp_check').bind('click', function() {
		if ($(this).is(':checked'))
			$('#tmp_date').show();
		else
			$('#tmp_date').hide();
	});
	
	$('#dGueltigBis').datetimepicker({
		showSecond: true,
		timeFormat: 'hh:mm:ss',
		dateFormat: 'dd.mm.yy'
	});
	
});

{/literal}
</script>

{assign var="cTitel" value=#benutzerNeu#}
{if isset($oAccount) && $oAccount->kAdminlogin > 0}
	{assign var="cTitel" value=#benutzerBearbeiten#}
{/if}

{include file="tpl_inc/seite_header.tpl" cTitel=$cTitel cBeschreibung=#benutzerDesc#}
<div id="content">
	{if isset($hinweis) && $hinweis|count_characters > 0}			
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}			
		<p class="box_error">{$fehler}</p>
	{/if}
	
	<form action="benutzerverwaltung.php" method="post">
		<div id="settings">
			<div class="category">Allgemein</div>
			
			<div class="item{if isset($cError_arr.cName)} error{/if}">
				<div class="name">Vor- und Nachname</div>
				<div class="for"><input type="text" name="cName" value="{if isset($oAccount->cName)}{$oAccount->cName}{/if}" /> {if isset($cError_arr.cName)}Bitte ausf&uuml;llen{/if}</div>
			</div>
			
			<div class="item{if isset($cError_arr.cMail)} error{/if}">
				<div class="name">E-Mail Adresse</div>
				<div class="for"><input type="text" name="cMail" value="{if isset($oAccount->cMail)}{$oAccount->cMail}{/if}" /> {if isset($cError_arr.cMail)}Bitte ausf&uuml;llen{/if}</div>
			</div>
			
			<div class="category">Anmeldedaten</div>
			<div class="item{if isset($cError_arr.cLogin)} error{/if}">
				<div class="name">Benutzername</div>
				<div class="for"><input type="text" name="cLogin" value="{if isset($oAccount->cLogin)}{$oAccount->cLogin}{/if}" /> {if isset($cError_arr.cLogin) && $cError_arr.cLogin == 1}Bitte ausf&uuml;llen{elseif isset($cError_arr.cLogin) && $cError_arr.cLogin == 2}Benutzername <b>'{$oAccount->cLogin}'</b> bereits vergeben{/if}</div>
			</div>
			
			<div class="item{if isset($cError_arr.cPass)} error{/if}">
				<div class="name">Passwort</div>
				<div class="for">
					<input type="text" name="cPass" id="cPass" autocomplete="off" />
					<a href="#" onclick="xajax_getRandomPassword();return false;" class="button generate">Passwort generieren</a>
					{if isset($cError_arr.cPass)}Bitte ausf&uuml;llen{/if}
				</div>
			</div>
			
			{if isset($oAccount->kAdminlogingruppe) && $oAccount->kAdminlogingruppe > 1}
				<div class="item">
					<div class="name">Zeitlich begrenzter Zugriff</div>
					<div class="for">
						<input type="checkbox" id="tmp_check" name="dGueltigBisAktiv" value="1" {if ($oAccount->dGueltigBis && $oAccount->dGueltigBis != '0000-00-00 00:00:00') || $cError_arr.dGueltigBis}checked="checked"{/if} />
					</div>
				</div>
				
				<div class="item{if $cError_arr.dGueltigBis} error{else} {if !$oAccount->dGueltigBis || $oAccount->dGueltigBis == '0000-00-00 00:00:00'}hidden{/if}{/if}" id="tmp_date">
					<div class="name">... bis einschlie&szlig;lich</div>
					<div class="for">
						<input type="text" name="dGueltigBis" value="{if $oAccount->dGueltigBis}{$oAccount->dGueltigBis|date_format:"%d.%m.%Y %H:%M:%S"}{/if}" id="dGueltigBis" />
						{if $cError_arr.dGueltigBis}Bitte ausf&uuml;llen{/if}
					</div>
				</div>
			{/if}
			
			{if !isset($oAccount->kAdminlogingruppe) || !($oAccount->kAdminlogingruppe == 1 && $nAdminCount <= 1)}
				<div class="category">Berechtigungen</div>
				<div class="item">
					<div class="name">Benutzergruppe</div>
					<div class="for">
						<select name="kAdminlogingruppe">
							{foreach from=$oAdminGroup_arr item="oGroup"}
								<option value="{$oGroup->kAdminlogingruppe}" {if isset($oAccount->kAdminlogingruppe) && $oAccount->kAdminlogingruppe == $oGroup->kAdminlogingruppe}selected="selected"{/if}>{$oGroup->cGruppe} ({$oGroup->nCount})</option>
							{/foreach}
						</select>
					</div>
				</div>
			{else}
				<input type="hidden" name="kAdminlogingruppe" value="1" />
			{/if}
			
		</div>
		<div class="save_wrapper">
			<input type="hidden" name="action" value="account_edit" />
			{if isset($oAccount) && $oAccount->kAdminlogin > 0}
				<input type="hidden" name="kAdminlogin" value="{$oAccount->kAdminlogin}" />
			{/if}
			<input type="hidden" name="save" value="1" />
			<input type="submit" value="{$cTitel}" class="button orange" />
		</div>
	</form>
</div>

