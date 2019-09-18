{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: tagging.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="tagging"}

<script type="text/javascript" src="templates/default/js/checkAllMSG.js"></script>

{if $step=='uebersicht'}
    {include file='tpl_inc/tagging_uebersicht.tpl'}
{elseif $step=='detail' || $step == 'detailloeschen'}
    {include file='tpl_inc/tagging_tagdetail.tpl'}
{/if}
        

{include file='tpl_inc/footer.tpl'}