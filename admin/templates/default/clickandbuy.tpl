{*
-------------------------------------------------------------------------------
    JTL-Shop 3
    File: clickandbuy.tpl, smarty template inc file
    
    page for JTL-Shop 3 
    Admin
    
    Author: daniel.boehmer@jtl-software.de, JTL-Software
    http://www.jtl-software.de
    
    Copyright (c) 2010 JTL-Software
-------------------------------------------------------------------------------
*}
{config_load file="$lang.conf" section="clickandbuy"}
{include file='tpl_inc/header.tpl'}

<link media="all" rel="stylesheet" type="text/css" href="{$currentTemplateDir}css/clickandbuy.css" />
<script type="text/javascript" src="templates/default/js/checkAllMSG.js"></script>

{if $step == "cab_uebersicht"}
    {*<div style="text-align: center; width: 99%; height: 900px;"><iframe height="900" width="99%" src="https://eu.clickandbuy.com/cgi-bin/register.pl?_show=merchantnew&lang=de&Nation=DE&00N200000014o7g=JTL-Shop" scrolling="yes"></iframe></div>*}
    {include file='tpl_inc/clickandbuy_uebersicht.tpl'}
{elseif $step == "cab_anmeldung"}
    {include file='tpl_inc/clickandbuy_anmeldung.tpl'}    
{/if}


{include file='tpl_inc/footer.tpl'}