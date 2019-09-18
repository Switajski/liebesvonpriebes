{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

{include file="tpl_inc/seite_header.tpl" cTitel=#benutzer# cBeschreibung=#benutzerDesc# cDokuURL=#benutzerURL#}
<div id="content">
	{if isset($hinweis) && $hinweis|count_characters > 0}			
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}			
		<p class="box_error">{$fehler}</p>
	{/if}
	
	<div class="tabber">
		<div class="tabbertab{if $action == 'account_view' || $action == ''} tabbertabdefault{/if}">
			<h2>{#benutzerTab#}</h2>
			<div class="category first">{#benutzerKategorie#}</div>
			<table class="list">
				<thead>
					<tr>
						<th class="tleft">#</th>
						<th class="tcenter">{#benutzerLogin#}</th>
						{*<th class="tcenter">{#benutzerName#}</th>*}
						<th class="tcenter">{#benutzerMail#}</th>
						<th class="tcenter">{#benutzerGruppe#}</th>
						<th class="tcenter">{#benutzerLoginVersuche#}</th>
						<th class="tcenter">{#benutzerLetzterLogin#}</th>
						<th class="tcenter">{#benutzerGueltigBis#}</th>
						<th width="260"></th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$oAdminList_arr item="oAdmin" name="admin"}
					<tr>
						<td class="tleft">{$oAdmin->kAdminlogin}</td>
						<td class="tcenter">{$oAdmin->cLogin}</td>
						{*<td class="tcenter">{$oAdmin->cName}</td>*}
						<td class="tcenter">{$oAdmin->cMail}</td>
						<td class="tcenter">{if $oAdmin->kAdminlogingruppe > 1}<a href="benutzerverwaltung.php?action=group_edit&id={$oAdmin->kAdminlogingruppe}">{$oAdmin->cGruppe}</a>{else}{$oAdmin->cGruppe}{/if}</td>
						<td class="tcenter">{$oAdmin->nLoginVersuch}</td>
						<td class="tcenter">{if $oAdmin->dLetzterLogin && $oAdmin->dLetzterLogin != '0000-00-00 00:00:00'}{$oAdmin->dLetzterLogin|date_format:"%d.%m.%Y %H:%M:%S"}{else}---{/if}</td>
						<td class="tcenter">{if !$oAdmin->bAktiv}gesperrt{else}{if $oAdmin->dGueltigBis && $oAdmin->dGueltigBis != '0000-00-00 00:00:00'}{$oAdmin->dGueltigBis|date_format:"%d.%m.%Y %H:%M:%S"}{else}---{/if}{/if}</td>
						<td class="tcenter">
							<a class="button edit notext" href="benutzerverwaltung.php?action=account_edit&id={$oAdmin->kAdminlogin}" title="{#bearbeitenLabel#}"></a>
							{if $oAdmin->bAktiv}
								<a class="button unlock notext" href="benutzerverwaltung.php?action=account_lock&id={$oAdmin->kAdminlogin}" title="{#sperrenLabel#}"></a>
							{else}
								<a class="button lock notext" href="benutzerverwaltung.php?action=account_unlock&id={$oAdmin->kAdminlogin}" title="{#entsperrenLabel#}"></a>
							{/if}
							<a class="button delete notext" href="benutzerverwaltung.php?action=account_delete&id={$oAdmin->kAdminlogin}" onclick="return confirm('Sind Sie sicher das der Benutzer entfernt werden soll?');" title="{#loeschenLabel#}"></a>
						</td>
					</tr>
					{/foreach}
				</tbody>
			</table>
			
			<div class="save_wrapper">
				<form action="benutzerverwaltung.php" method="get">
					<input type="hidden" name="action" value="account_edit" />
					<input type="submit" class="button orange" value="{#benutzerNeu#}" />
				</form>
			</div>
			
		</div>
		
		<div class="tabbertab{if $action == 'group_view'} tabbertabdefault{/if}">
			<h2>{#gruppenTab#}</h2>
			<div class="category first">{#gruppenKategorie#}</div>
			<table class="list">
				<thead>
					<tr>
						<th class="tleft">#</th>
						<th class="tleft">{#gruppenName#}</th>
						<th class="tleft">{#gruppenDesc#}</th>
						<th class="tcenter">{#gruppenBenutzer#}</th>
						<th width="160"></th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$oAdminGroup_arr item="oGroup"}
					<tr>
						<td class="tleft">{$oGroup->kAdminlogingruppe}</td>
						<td class="tleft">{$oGroup->cGruppe}</td>
						<td class="tleft">{$oGroup->cBeschreibung}</td>
						<td class="tcenter">{$oGroup->nCount}</td>
						<td class="tcenter">
							{if $oGroup->kAdminlogingruppe != 1}
								<a class="button edit notext" href="benutzerverwaltung.php?action=group_edit&id={$oGroup->kAdminlogingruppe}" title="{#bearbeitenLabel#}"></a>
								<a class="button delete notext" href="benutzerverwaltung.php?action=group_delete&id={$oGroup->kAdminlogingruppe}" onclick="return confirm('Sind Sie sicher das die Gruppe entfernt werden soll?');" title="{#loeschenLabel#}"></a>
							{/if}
						</td>
					</tr>
					{/foreach}
				</tbody>
			</table>
			
			<div class="save_wrapper">
				<form action="benutzerverwaltung.php" method="get">
					<input type="hidden" name="action" value="group_edit" />
					<input type="submit" class="button orange" value="{#gruppeNeu#}" />
				</form>
			</div>
			
		</div>
	</div>
</div>

