{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: news.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
{config_load file="$lang.conf" section="news"}
{include file='tpl_inc/header.tpl'}

<script type="text/javascript" src="{$PFAD_CKEDITOR}ckeditor.js"></script>

{if $step == "news_erstellen" || $step == "news_editieren"}
	{include file='tpl_inc/news_erstellen.tpl'}
{elseif $step == "news_kategorie_erstellen"}
	{include file='tpl_inc/news_kategorie_erstellen.tpl'}
{elseif $step == "news_uebersicht"}
	{include file='tpl_inc/news_uebersicht.tpl'}
{elseif $step == "news_vorschau"}
	{include file='tpl_inc/news_vorschau.tpl'}
{elseif $step == "news_kommentar_editieren"}
	{include file='tpl_inc/news_kommentar_editieren.tpl'}
{/if}


{include file='tpl_inc/footer.tpl'}