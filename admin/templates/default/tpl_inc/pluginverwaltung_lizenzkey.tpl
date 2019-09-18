{*
-------------------------------------------------------------------------------
    JTL-Shop 3
    File: pluginverwaltung_lizenzkey.tpl, smarty template inc file
    
    page for JTL-Shop 3 
    Admin
    
    Author: daniel.boehmer@jtl-software.de, JTL-Software
    http://www.jtl-software.de
    
    Copyright (c) 2010 JTL-Software
-------------------------------------------------------------------------------
*}
 
{assign var=cPlugin value=#plugin#}
{include file="tpl_inc/seite_header.tpl" cTitel=#pluginverwaltungLicenceKeyInput# cBeschreibung=#pluginverwaltungDesc#}
<div id="content">

	{if isset($hinweis) && $hinweis|count_characters > 0}			
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}			
		<p class="box_error">{$fehler}</p>
	{/if}

	<div class="container">
		<form name="pluginverwaltung" method="post" action="pluginverwaltung.php">
			<input type="hidden" name="{$session_name}" value="{$session_id}" />			
			<input type="hidden" name="pluginverwaltung_uebersicht" value="1" />
			<input type="hidden" name="lizenzkeyadd" value="1" />
			<input type="hidden" name="kPlugin" value="{$kPlugin}" />

			<div class="category">{#pluginverwaltungLicenceKeyInput#}</div>

			<div id="settings">
			
			
				<div class="item">
					<div class="name">
						{#pluginverwaltungLicenceKey#}
					</div>
					<div class="for">
						<input name="cKey" type="text" value="" />
					</div>
				</div>
			</div>
			
			<div class="save_wrapper">
				<input name="speichern" type="submit" value="{#pluginBtnSave#}" class="button orange" />
			</div>
		</form>
	</div>
</div>