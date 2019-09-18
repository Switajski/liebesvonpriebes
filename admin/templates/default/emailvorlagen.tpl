{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: emailvorlagen.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: JTL-Software-GmbH
	http://www.jtl-software.de
	
	Copyright (c) 2007 JTL-Software
-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="emailvorlagen"}

<script type="text/javascript" src="{$PFAD_CKEDITOR}ckeditor.js"></script>

{if $step=='uebersicht'}
	{include file='tpl_inc/emailvorlagen_uebersicht.tpl'}
{elseif $step=='bearbeiten'}
	{include file='tpl_inc/emailvorlagen_bearbeiten.tpl'}
{elseif $step=='zuruecksetzen'}
    {include file='tpl_inc/emailvorlagen_reset_confirm.tpl'}
{/if}

{include file='tpl_inc/footer.tpl'}