{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: login.tpl, smarty template inc file
	
	login page for JTL-Shop 3 
	Admin
	
	Author: JTL-Software-GmbH
	http://www.jtl-software.de
	
	Copyright (c) 2007 JTL-Software
-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="einstellungen"}
{if $step=='uebersicht'}
	{include file='tpl_inc/einstellungen_uebersicht.tpl'}
{elseif $step=='einstellungen bearbeiten'}
	{include file='tpl_inc/einstellungen_bearbeiten.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}