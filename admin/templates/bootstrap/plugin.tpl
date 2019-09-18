{config_load file="$lang.conf" section='plugin'}
{include file='tpl_inc/header.tpl'}
{if $step === 'plugin_uebersicht'}
    {include file='tpl_inc/plugin_uebersicht.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}