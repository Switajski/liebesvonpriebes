{config_load file="$lang.conf" section='objectcache'}
{include file='tpl_inc/header.tpl'}
{if $step === 'uebersicht'}
    {include file='tpl_inc/objectcache_uebersicht.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}