{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: umfrage.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
{config_load file="$lang.conf" section="umfrage"}
{include file='tpl_inc/header.tpl'}

<script type="text/javascript" src="{$PFAD_CKEDITOR}ckeditor.js"></script>

{if $step == "umfrage_erstellen" || $step == "umfrage_editieren"}
	{include file='tpl_inc/umfrage_erstellen.tpl'}
{elseif $step == "umfrage_uebersicht"}
	{include file='tpl_inc/umfrage_uebersicht.tpl'}
{elseif $step == "umfrage_statistik"}
	{include file='tpl_inc/umfrage_statistik.tpl'}
{elseif $step == "umfrage_frage_erstellen"}
	{include file='tpl_inc/umfrage_frage_erstellen.tpl'}	
{elseif $step == "umfrage_vorschau"}
	{include file='tpl_inc/umfrage_vorschau.tpl'}
{elseif $step == "umfrage_statistik_sonstige_texte"}
	{include file='tpl_inc/umfrage_statistik_sonstige_texte.tpl'}
{/if}


{include file='tpl_inc/footer.tpl'}