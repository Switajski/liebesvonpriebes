{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}
{config_load file="$lang.conf" section="bestellungen"}
{include file='tpl_inc/header.tpl'}
{if $step === 'bestellungen_uebersicht'}
    {include file='tpl_inc/bestellungen_uebersicht.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}