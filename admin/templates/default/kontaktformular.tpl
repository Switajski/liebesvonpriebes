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
{config_load file="$lang.conf" section="kontaktformular"}
<script type="text/javascript" src="{$PFAD_CKEDITOR}ckeditor.js"></script>

{if $step=='uebersicht'}
	{include file='tpl_inc/kontaktformular_uebersicht.tpl'}
{elseif $step=='betreff'}
	{include file='tpl_inc/kontaktformular_betreff.tpl'}
{elseif $step=='content'}
	{include file='tpl_inc/kontaktformular_content.tpl'}
{/if}

{include file='tpl_inc/footer.tpl'}