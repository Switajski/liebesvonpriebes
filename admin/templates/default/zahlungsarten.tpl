{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: zahlungsarten.tpl, smarty template inc file
	
	login page for JTL-Shop 3 
	Admin
	
	Author: JTL-Software-GmbH
	http://www.jtl-software.de
	
	Copyright (c) 2007 JTL-Software
-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="zahlungsarten"}

<script type="text/javascript" src="templates/default/js/versandart_bruttonetto.js"></script>

{if $step=='uebersicht'}
	{include file='tpl_inc/zahlungsarten_uebersicht.tpl'}
{elseif $step=='einstellen'}
	{include file='tpl_inc/zahlungsarten_einstellen.tpl'}
{elseif $step=='log'}
	{include file='tpl_inc/zahlungsarten_log.tpl'}
{/if}

{include file='tpl_inc/footer.tpl'}