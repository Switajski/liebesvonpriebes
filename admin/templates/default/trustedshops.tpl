{*
-------------------------------------------------------------------------------
    File: trustedshops.tpl, smarty template inc file

    Vote system admin template page for JTL-Shop 3
    Admin

    Author: Daniel Böhmer daniel.boehmer@jtl-software.de
    http://www.jtl-software.de

-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="trustedshops"}

{if $step == "uebersicht"}
    {include file='tpl_inc/trustedshops_uebersicht.tpl'}
{elseif $step == "info"}
    {include file='tpl_inc/trustedshops_info.tpl'}
{elseif $step == "info_kundenbewertung"}
    {include file='tpl_inc/trustedshops_info_kundenbewertung.tpl'}
{/if}           
        

{include file='tpl_inc/footer.tpl'}