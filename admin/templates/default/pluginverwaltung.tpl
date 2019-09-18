{*
-------------------------------------------------------------------------------
    JTL-Shop 3
    File: pluginverwaltung.tpl, smarty template inc file
    
    page for JTL-Shop 3 
    Admin
    
    Author: daniel.boehmer@jtl-software.de, JTL-Software
    http://www.jtl-software.de
    
    Copyright (c) 2010 JTL-Software
-------------------------------------------------------------------------------
*}
{config_load file="$lang.conf" section="pluginverwaltung"}
{include file='tpl_inc/header.tpl'}

<script type="text/javascript" src="templates/default/js/checkAllMSG.js"></script>

{if $step == "pluginverwaltung_uebersicht"}
    {include file='tpl_inc/pluginverwaltung_uebersicht.tpl'}
{elseif $step == "pluginverwaltung_sprachvariablen"}
    {include file='tpl_inc/pluginverwaltung_sprachvariablen.tpl'}
{elseif $step == "pluginverwaltung_lizenzkey"}
    {include file='tpl_inc/pluginverwaltung_lizenzkey.tpl'}
{/if}


{include file='tpl_inc/footer.tpl'}