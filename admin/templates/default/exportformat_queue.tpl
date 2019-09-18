{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: exportformat_queue.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="exportformat queue"}

<script type="text/javascript" src="/defaulttemplates/js/checkAllMSG.js"></script>

{if $step=='uebersicht'}
	{include file='tpl_inc/exportformat_queue_uebersicht.tpl'}
{elseif $step=='erstellen'}
	{include file='tpl_inc/exportformat_queue_erstellen.tpl'}
{elseif $step=='fertiggestellt'}
    {include file='tpl_inc/exportformat_queue_fertiggestellt.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}