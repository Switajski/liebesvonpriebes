{*
-------------------------------------------------------------------------------
    JTL-Shop 3
    File: kampagne.tpl, smarty template inc file
    
    page for JTL-Shop 3 
    Admin
    
    Author: daniel.boehmer@jtl-software.de, JTL-Software
    http://www.jtl-software.de
    
    Copyright (c) 2010 JTL-Software
-------------------------------------------------------------------------------
*} 
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="kampagne"}

<script type="text/javascript" src="{$PFAD_CKEDITOR}ckeditor.js"></script>
<script type="text/javascript" src="templates/default/js/checkAllMSG.js"></script>

{if $step == "kampagne_uebersicht"}
	{include file='tpl_inc/kampagne_uebersicht.tpl'}
{elseif $step == "kampagne_detail"}
	{include file='tpl_inc/kampagne_detail.tpl'}
{elseif $step == "kampagne_defdetail"}
	{include file='tpl_inc/kampagne_defdetail.tpl'}
{elseif $step == "kampagne_erstellen" || $step == "kampagne_editieren"}
	{include file='tpl_inc/kampagne_erstellen.tpl'}
{/if}


{include file='tpl_inc/footer.tpl'}