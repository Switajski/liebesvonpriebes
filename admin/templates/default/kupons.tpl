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
{config_load file="$lang.conf" section="coupons"}

<script type="text/javascript" src="templates/default/js/checkAllMSG.js"></script>
<script type="text/javascript" src="templates/default/js/versandart_bruttonetto.js"></script>

{if $step=='uebersicht'}
	{include file='tpl_inc/kupons_uebersicht.tpl'}
{elseif $step=='neuer Kupon'}
	{include file='tpl_inc/kupons_neuer_kupon.tpl'}
{/if}

{include file='tpl_inc/footer.tpl'}