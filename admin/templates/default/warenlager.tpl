{*
-------------------------------------------------------------------------------
    JTL-Shop 3
    File: warenkorbpers.tpl, smarty template inc file
    
    page for JTL-Shop 3 
    Admin
    
    Author: daniel.boehmer@jtl-software.de, JTL-Software
    http://www.jtl-software.de
    
    Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="warenlager"}
{include file="tpl_inc/seite_header.tpl" cTitel=#warenlager# cBeschreibung=#warenlagerDesc# cDokuURL=#warenlagerURL#}

{if $cStep == "uebersicht" || $cStep == "uebersicht"}
    {include file='tpl_inc/warenlager_uebersicht.tpl'}
{/if}

{include file='tpl_inc/footer.tpl'}