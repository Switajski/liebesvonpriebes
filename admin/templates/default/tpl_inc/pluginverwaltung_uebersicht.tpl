{*
-------------------------------------------------------------------------------
    JTL-Shop 3
    File: pluginverwaltung_uebersicht.tpl, smarty template inc file
    
    page for JTL-Shop 3 
    Admin
    
    Author: daniel.boehmer@jtl-software.de, JTL-Software
    http://www.jtl-software.de
    
    Copyright (c) 2010 JTL-Software
-------------------------------------------------------------------------------
*}
<script type="text/javascript">
function ackCheck(kPlugin)
{ldelim}
    var bCheck = confirm("Wollen Sie wirklich das Plugin updaten?");
    if(bCheck)
        window.location.href = "pluginverwaltung.php?pluginverwaltung_uebersicht=1&updaten=1&kPlugin=" + kPlugin;
{rdelim}

{if isset($bReload) && $bReload}
	window.location.href = window.location.href + "?h={$hinweis64}";
{/if}

{* Event wird auch der Checkbox zugewiesen
$(document).ready(function() {ldelim}
	 $('table.list tbody tr').each(function(idx, item) {ldelim}
		  $(this).click(function() {ldelim}
				if ($(this).find('td.check input:checkbox').length > 0)
				{ldelim}
					 var bChecked = $(this).find('td.check input:checkbox').attr('checked');
					 $(this).find('td.check input:checkbox').attr('checked', !bChecked);
				{rdelim}
		  {rdelim});
	 {rdelim});
{rdelim});
*}
</script>

{include file="tpl_inc/seite_header.tpl" cTitel=#pluginverwaltung# cBeschreibung=#pluginverwaltungDesc#}
<div id="content">
	 {if isset($hinweis) && $hinweis|count_characters > 0}			
		  <p class="box_success">{$hinweis}</p>
	 {/if}
	 {if isset($fehler) && $fehler|count_characters > 0}			
		  <p class="box_error">{$fehler}</p>
	 {/if}
		 
	 <div id="settings">
		  {if $PluginInstalliert_arr|@count > 0 && $PluginInstalliert_arr}
				<form name="pluginverwaltung" method="post" action="pluginverwaltung.php">
					 <input type="hidden" name="{$session_name}" value="{$session_id}" />
					 <input type="hidden" name="pluginverwaltung_uebersicht" value="1" />
					 
					 <div class="category">{#pluginListInstalled#}</div>
					 <table class="list">
						  <thead>
								<tr>
									 <th></th>
									 <th class="tleft">{#pluginName#}</th>
									 <th>{#status#}</th>
									 {*<th>{#pluginAuthor#}</th>*}
									 <th>{#pluginVersion#}</th>
									 <th>{#pluginInstalled#}</th>
									 {*<th>{#pluginUpdated#}</th>*}
									 <th>{#pluginFolder#}</th>
									 <th>{#pluginEditLocales#}</th>
									 <th>{#pluginEditLinkgrps#}</th>						
									 <th>&nbsp;</th>
									 <th>&nbsp;</th>
								</tr>
						  </thead>
						  <tbody>
						  {foreach from=$PluginInstalliert_arr item=PluginInstalliert}
								<tr {if isset($PluginInstalliert->dUpdate) && $PluginInstalliert->dUpdate|count_characters > 0 && $PluginInstalliert->cUpdateFehler == 1}class="highlight"{/if}>
									 <td class="check">
										  <input type="checkbox" name="kPlugin[]" value="{$PluginInstalliert->kPlugin}" />
									 </td>
									 <td>
										  <strong>{$PluginInstalliert->cName}</strong>
										  {if (isset($PluginInstalliert->dUpdate) && $PluginInstalliert->dUpdate|count_characters > 0) || (isset($PluginInstalliert->cInfo) && $PluginInstalliert->cInfo|count_characters > 0)}
												<p>
												{if $PluginInstalliert->cUpdateFehler == 1}
													 {if $PluginInstalliert->cInfo|count_characters > 0}{$PluginInstalliert->cInfo}<br />{/if}{#pluginUpdateExists#}
												{else}
													 {if $PluginInstalliert->cInfo|count_characters > 0}{$PluginInstalliert->cInfo}<br />{/if}{#pluginUpdateExists#}. <br />{#pluginUpdateExistsError#}: <br />{$PluginInstalliert->cUpdateFehler}
												{/if}
												</p>
										  {/if}
									 </td>
									 <td class="tcenter">
										  <span class="{if $PluginInstalliert->nStatus == 2}success{elseif $PluginInstalliert->nStatus == 1 || $PluginInstalliert->nStatus == 3 || $PluginInstalliert->nStatus == 4 || $PluginInstalliert->nStatus == 5 || $PluginInstalliert->nStatus == 6}info{/if}">{$PluginInstalliert->cStatus}</span>
									 </td>
									 {*<td class="tcenter">{$PluginInstalliert->cAutor}</td>*}
									 <td class="tcenter">{$PluginInstalliert->dVersion}{if isset($PluginInstalliert->dUpdate) && $PluginInstalliert->dUpdate|count_characters > 0} <span class="error">{$PluginInstalliert->dUpdate}</span>{/if}</td>
									 <td class="tcenter">{$PluginInstalliert->dInstalliert_DE}</td>
									 {*<td class="tcenter">{$PluginInstalliert->dZuletztAktualisiert_DE}</td>*}
									 <td class="tcenter">{$PluginInstalliert->cVerzeichnis}</td>
									 <td class="tcenter">{if isset($PluginInstalliert->oPluginSprachvariableAssoc_arr) && $PluginInstalliert->oPluginSprachvariableAssoc_arr|@count > 0}<a href="pluginverwaltung.php?pluginverwaltung_uebersicht=1&sprachvariablen=1&kPlugin={$PluginInstalliert->kPlugin}" class="button edit">{#pluginEdit#}</a>{/if}</td>
									 <td class="tcenter">{if isset($PluginInstalliert->oPluginFrontendLink_arr) && $PluginInstalliert->oPluginFrontendLink_arr|@count > 0}<a href="links.php?kPlugin={$PluginInstalliert->kPlugin}" class="button edit">{#pluginEdit#}</a>{/if}</td>
									 <td class="tcenter">{if isset($PluginInstalliert->cLizenzKlasse) && $PluginInstalliert->cLizenzKlasse|count_characters > 0}{if $PluginInstalliert->cLizenz && $PluginInstalliert->cLizenz|count_characters > 0}<strong>{#pluginBtnLicence#}:</strong> {$PluginInstalliert->cLizenz} <button name="lizenzkey" type="submit" class="button orange" value="{$PluginInstalliert->kPlugin}">{#pluginBtnLicenceChange#}</button>{else}<button name="lizenzkey" type="submit" class="button orange" value="{$PluginInstalliert->kPlugin}">{#pluginBtnLicenceAdd#}</button>{/if}{/if}</td>
									 <td class="tcenter">{if isset($PluginInstalliert->dUpdate) && $PluginInstalliert->dUpdate|count_characters > 0 && $PluginInstalliert->cUpdateFehler == 1}<a href="javascript:ackCheck({$PluginInstalliert->kPlugin});" class="button orange">{#pluginBtnUpdate#}</a>{/if}</td>
								</tr>
						  {/foreach}
						  </tbody>
						  <tfoot>
								<tr>
									 <td class="check"><input name="ALLMSGS" id="ALLMSGS1" type="checkbox" onclick="AllMessages(this.form);" /></td>
									 <td colspan="10"><label for="ALLMSGS1">{#pluginSelectAll#}</label></td>
								</tr>
						  </tfoot>
					 </table>
					 <div class="save_wrapper">
						  <button name="aktivieren" type="submit" class="button orange">{#pluginBtnActivate#}</button>
						  <button name="deaktivieren" type="submit" class="button orange">{#pluginBtnDeActivate#}</button>
						  <button name="deinstallieren" type="submit" class="button orange">{#pluginBtnDeInstall#}</button>           
					 </div>
				</form>
		  {/if}
		  
		  {if $PluginVerfuebar_arr|@count > 0 && $PluginVerfuebar_arr}
				<form name="pluginverwaltung" method="post" action="pluginverwaltung.php">
					 <input type="hidden" name="{$session_name}" value="{$session_id}" />
					 <input type="hidden" name="pluginverwaltung_uebersicht" value="1" />
					 
					 <div class="category">{#pluginListNotInstalled#}</div>
					 <table class="list">
						  <thead>
								<tr>
									 <th></th>
									 <th class="tleft">{#pluginName#}</th>
									 {*<th>{#pluginAuthor#}</th>*}
									 <th>{#pluginVersion#}</th>
									 <th>{#pluginFolder#}</th>
								</tr>
						  </thead>
						  <tbody>
								{foreach name="verfuergbareplugins" from=$PluginVerfuebar_arr item=PluginVerfuebar}
									 <tr>
										  <td class="check"><input type="checkbox" name="cVerzeichnis[]" value="{$PluginVerfuebar->cVerzeichnis}" /></td>
										  <td>
												<strong>{$PluginVerfuebar->cName}</strong>
												<p>{$PluginVerfuebar->cDescription}</p>
										  </td>
										  {*<td class="tcenter">{$PluginVerfuebar->cAuthor}</td>*}
										  <td class="tcenter">{$PluginVerfuebar->cVersion}</td>
										  <td class="tcenter">{$PluginVerfuebar->cVerzeichnis}</td>
									 </tr>
								{/foreach}
						  </tbody>
						  <tfoot>
								<tr>
									 <td class="check"><input name="ALLMSGS" id="ALLMSGS2" type="checkbox" onclick="AllMessages(this.form);" /></td>
									 <td colspan="5"><label for="ALLMSGS2">{#pluginSelectAll#}</label></td>
								</tr>
						  </tfoot>
					 </table>                  
					 <div class="save_wrapper">
						  <button name="installieren" type="submit" class="button orange">{#pluginBtnInstall#}</button>
					 </div>
				</form>
		  {/if}
	  
		  {if isset($PluginFehlerhaft_arr) && $PluginFehlerhaft_arr|@count > 0}
				<div class="category">{#pluginListNotInstalledAndError#}:</div>
				<table class="list">
					 <thead>
						  <tr>
								<th class="tleft">{#pluginName#}</th>
								<th class="tleft">{#pluginErrorCode#}</th>
								{*<th>{#pluginAuthor#}</th>*}
								<th>{#pluginVersion#}</th>
								<th>{#pluginFolder#}</th>
						  </tr>
					 </thead>
					 <tbody>
						  {foreach from=$PluginFehlerhaft_arr item=PluginFehlerhaft}
								<tr>
									 <td>
										  <strong>{$PluginFehlerhaft->cName}</strong>
										  <p>{$PluginFehlerhaft->cDescription}</p>
									 </td>
									 <td>
										  <span class="error">{$PluginFehlerhaft->cFehlercode}</span>
										  {$PluginFehlerhaft->cFehlerBeschreibung}
									 </td>
									 {*<td class="tcenter">{$PluginFehlerhaft->cAuthor}</td>*}
									 <td class="tcenter">{$PluginFehlerhaft->cVersion}</td>
									 <td class="tcenter">{$PluginFehlerhaft->cVerzeichnis}</td>
								</tr>
						  {/foreach}
					 </tbody>
				</table>
		  {/if}
	 </div>
</div>