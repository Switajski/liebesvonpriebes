{*
-------------------------------------------------------------------------------
    JTL-Shop 3
    File: kuponstatistik.tpl, smarty template inc file

    page for JTL-Shop 3
    Admin

    Author: andre@jtl-software.de, JTL-Software
    http://www.jtl-software.de

    Copyright (c) 2010 JTL-Software
-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{*config_load file="$lang.conf" section="kampagne"*}

{if $step == "kuponstatistik_uebersicht"}
	{include file='tpl_inc/kuponstatistik_uebersicht.tpl'}
{/if}


{include file='tpl_inc/footer.tpl'}