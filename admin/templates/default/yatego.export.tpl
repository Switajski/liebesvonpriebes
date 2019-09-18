{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: yatego.export.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
{config_load file="$lang.conf" section="exportformats"}
{include file='tpl_inc/header.tpl'}

{if $step == "yategoexport_uebersicht"}
	{include file='tpl_inc/yategoexport_uebersicht.tpl'}
{/if}


{include file='tpl_inc/footer.tpl'}