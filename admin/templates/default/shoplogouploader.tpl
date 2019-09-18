{*
-------------------------------------------------------------------------------
    JTL-Shop 3
    File: shoplogouploader.tpl, smarty template inc file
    
    page for JTL-Shop 3 
    Admin
    
    Author: daniel.boehmer@jtl-software.de, JTL-Software
    http://www.jtl-software.de
    
    Copyright (c) 2010 JTL-Software
-------------------------------------------------------------------------------
*}
{config_load file="$lang.conf" section="shoplogouploader"}
{include file='tpl_inc/header.tpl'}

{if $step == "shoplogouploader_uebersicht"}
    {include file='tpl_inc/shoplogouploader_uebersicht.tpl'}
{/if}

{include file='tpl_inc/footer.tpl'}