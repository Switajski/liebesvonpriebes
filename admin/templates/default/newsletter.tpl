{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: newsletter.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
{if $step != 'vorlage_vorschau'}
	{include file='tpl_inc/header.tpl'}	
{/if}

{config_load file="$lang.conf" section="newsletter"}

<script type="text/javascript" src="{$PFAD_CKEDITOR}ckeditor.js"></script>
<script type="text/javascript" src="templates/default/js/checkAllMSG.js"></script>

{if $step=='uebersicht'}
	{include file='tpl_inc/newsletter_uebersicht.tpl'}
{elseif $step=='vorlage_erstellen'}
	{include file='tpl_inc/newsletter_vorlage_erstellen.tpl'}
{elseif $step=='vorlage_std_erstellen'}
	{include file='tpl_inc/newsletter_vorlage_std_erstellen.tpl'}	
{elseif $step=='history_anzeigen'}
	{include file='tpl_inc/newsletter_anzeigen.tpl'}
{elseif $step=='vorlage_vorschau_iframe'}
	{include file='tpl_inc/newsletter_vorlagenvorschau_vorbereitung.tpl'}
{elseif $step=='vorlage_vorschau'}
	{include file='tpl_inc/newsletter_vorlagenvorschau.tpl'}
{/if}

{if $step != 'vorlage_vorschau'}
	
	{include file='tpl_inc/footer.tpl'}
{/if}