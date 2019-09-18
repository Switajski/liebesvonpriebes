{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

{config_load file="$lang.conf" section="dbupdater"}
{config_load file="$lang.conf" section="shopupdate"}
{include file='tpl_inc/header.tpl'}

{if $step == "dbupdater_uebersicht"}
    {include file='tpl_inc/dbupdater_uebersicht.tpl'}
{/if}


{include file='tpl_inc/footer.tpl'}