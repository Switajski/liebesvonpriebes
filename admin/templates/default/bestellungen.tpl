{*
-------------------------------------------------------------------------------
    JTL-Shop 3
    File: bestellungen.tpl, smarty template inc file
    
    page for JTL-Shop 3 
    Admin
    
    Author: daniel.boehmer@jtl-software.de, JTL-Software
    http://www.jtl-software.de
    
    Copyright (c) 2010 JTL-Software
-------------------------------------------------------------------------------
*}
{config_load file="$lang.conf" section="bestellungen"}
{include file='tpl_inc/header.tpl'}

<script type="text/javascript" src="templates/default/js/checkAllMSG.js"></script>

{if $step == "bestellungen_uebersicht"}
    {include file='tpl_inc/bestellungen_uebersicht.tpl'}
{/if}


{include file='tpl_inc/footer.tpl'}