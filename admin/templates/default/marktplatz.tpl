{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

{config_load file="$lang.conf" section="marktplatz"}
{include file='tpl_inc/header.tpl'}

{if $action == 'overview'}
    {include file='tpl_inc/marktplatz_uebersicht.tpl'}
{elseif $action == 'detail'}
    {include file='tpl_inc/marktplatz_details.tpl'}
{/if}

{include file='tpl_inc/footer.tpl'}