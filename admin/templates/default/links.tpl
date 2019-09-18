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
{config_load file="$lang.conf" section="links"}

<script type="text/javascript" src="{$PFAD_CKEDITOR}ckeditor.js"></script>
<script type="text/javascript" src="{$PFAD_CKEDITOR}/adapters/jquery.js"></script>

{if $step=='uebersicht'}
	{include file='tpl_inc/links_uebersicht.tpl'}
{elseif $step=='neue Linkgruppe'}
	{include file='tpl_inc/links_neue_linkgruppe.tpl'}
{elseif $step=='neuer Link'}
	{include file='tpl_inc/links_neuer_link.tpl'}
{elseif $step=='linkgruppe_loeschen_confirm'}
	{include file='tpl_inc/links_loesch_confirm.tpl'}
{/if}

{include file='tpl_inc/footer.tpl'}