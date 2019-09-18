{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: exporte.tpl, smarty template inc file
	
	login page for JTL-Shop 3 
	Admin
	
	Author: JTL-Software-GmbH
	http://www.jtl-software.de
	
	Copyright (c) 2007 JTL-Software
-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="exportformats"}
{if $step=='uebersicht'}
	{include file='tpl_inc/exportformate_uebersicht.tpl'}
{elseif $step=='neuer Export'}
	{include file='tpl_inc/exportformate_neuer_export.tpl'}
{/if}

{include file='tpl_inc/footer.tpl'}