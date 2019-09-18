{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: versandarten.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: JTL-Software-GmbH
	http://www.jtl-software.de
	
	Copyright (c) 2007 JTL-Software
-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}

<script type="text/javascript" src="templates/default/js/versandart_bruttonetto.js"></script>

{config_load file="$lang.conf" section="versandarten"}
{if $step=='uebersicht'}
	{include file='tpl_inc/versandarten_uebersicht.tpl'}
{elseif $step=='neue Versandart'}
	{include file='tpl_inc/versandarten_neue_Versandart.tpl'}
{elseif $step=='Zuschlagsliste'}
	{include file='tpl_inc/versandarten_zuschlagsliste.tpl'}
{/if}

{include file='tpl_inc/footer.tpl'}