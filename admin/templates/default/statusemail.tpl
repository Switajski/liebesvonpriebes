{*
-------------------------------------------------------------------------------
    File: statusemail.tpl, smarty template inc file

    Vote system admin template page for JTL-Shop 3
    Admin

    Author: Daniel Böhmer daniel.boehmer@jtl-software.de
    http://www.jtl-software.de
-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="statusemail"}

{if $step == "statusemail_uebersicht"}
    {include file='tpl_inc/statusemail_uebersicht.tpl'}
{/if}           
        

{include file='tpl_inc/footer.tpl'}