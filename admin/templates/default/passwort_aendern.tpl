{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: passwort_aendern.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: JTL-Software-GmbH
	http://www.jtl-software.de
	
	Copyright (c) 2007 JTL-Software
    

-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="passwortaendern"}

{include file="tpl_inc/seite_header.tpl" cTitel=#resetPassword# cBeschreibung=#passwordResetDesc# cDokuURL=#resetPasswordURL#}
<div id="content">
	
	{if $hinweis|@count_characters > 0}
		<p class="box_info">{$hinweis}</p>
	{/if}
	
	<div id="settings">
		<form name="login" method="post" action="passwort_aendern.php">
			<input type="hidden" name="zuruecksetzen" value="1" />
			<div class="item">
				<div class="name">
					<label for="benutzer" class="left">{#username#}</label>
				</div>
				<div class="for">
					<input type="text" name="benutzer" id="benutzer"  tabindex="1" />
				</div>
			</div>
			<div class="item">
				<div class="name">
					<label for="password" class="left">{#oldPassword#}</label>
				</div>
				<div class="for">
					<input type="password" name="password" id="password"  tabindex="2" />
				</div>
			</div>
			<div class="item">
				<div class="name">
					<label for="neuespasswort" class="left">{#newPassword#}</label>
				</div>
				<div class="for">
					<input type="password" name="neuespasswort" id="neuespasswort"  tabindex="3" />
				</div>
			</div>
			<div class="item">
				<div class="name">
				<label for="neuespasswort2" class="left">{#retypePassword#}</label>
				</div>
				<div class="for">
					<input type="password" name="neuespasswort2" id="neuespasswort2"  tabindex="4" />
				</div>
			</div>
			<div class="save_wrapper">
				<input type="submit" value="{#resetPassword#}" class="button orange" />
			</div>
		</form>
	</div>
</div>
{include file='tpl_inc/footer.tpl'}