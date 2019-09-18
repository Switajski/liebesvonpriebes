{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

{assign var="cTitel" value=#gruppeNeu#}
{if isset($oAdminGroup) && $oAdminGroup->kAdminlogingruppe > 0}
	{assign var="cTitel" value=#gruppeBearbeiten#}
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
			<div class="category">Gruppe</div>
			
			<div class="item{if $cError_arr.cGruppe} error{/if}">
				<div class="name">Name</div>
				<div class="for"><input type="text" name="cGruppe" value="{$oAdminGroup->cGruppe}" /> {if $cError_arr.cGruppe}Bitte ausf&uuml;llen{/if}</div>
			</div>
			
			<div class="item{if $cError_arr.cBeschreibung} error{/if}">
				<div class="name">Beschreibung</div>
				<div class="for"><input type="text" name="cBeschreibung" value="{$oAdminGroup->cBeschreibung}" /> {if $cError_arr.cBeschreibung}Bitte ausf&uuml;llen{/if}</div>
			</div>
		</div>
		
		<div id="settings">
			<div class="category">Berechtigungen</div>
		</div>
		
		{foreach from=$oAdminDefPermission_arr item=oGroup name="perm"}
			<div id="settings" class="perm_group">
				<div class="perm_wrapper {if $smarty.foreach.perm.iteration % 3 == 2}center{/if}">
					<div class="category">{$oGroup->cName}</div>
					<div class="perm_list">
						{foreach from=$oGroup->oPermission_arr item=oPerm}
							<label for="{$oPerm->cRecht}" class="perm"> <input type="checkbox" name="perm[]" value="{$oPerm->cRecht}" id="{$oPerm->cRecht}" {if is_array($cAdminGroupPermission_arr)}{if $oPerm->cRecht|in_array:$cAdminGroupPermission_arr}checked="checked"{/if}{/if} />{if $oPerm->cBeschreibung|count_characters > 0}{$oPerm->cBeschreibung}{if $bDebug} - {$oPerm->cRecht}{/if}{else}{$oPerm->cRecht}{/if}</label>
						{/foreach}
					</div>
				</div>
			</div>
			
			{if $smarty.foreach.perm.iteration % 3 == 0}
				<div class="clear"></div>
			{/if}
		{/foreach}
		
		<div class="save_wrapper clear">
			{if isset($oAdminGroup) && $oAdminGroup->kAdminlogingruppe > 0}
				<input type="hidden" name="kAdminlogingruppe" value="{$oAdminGroup->kAdminlogingruppe}" />
			{/if}
			<input type="hidden" name="action" value="group_edit" />
			<input type="hidden" name="save" value="1" />
			<input type="submit" value="{$cTitel}" class="button orange" />
		</div>
		
	</form>
</div>

