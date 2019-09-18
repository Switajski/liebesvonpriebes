{config_load file="$lang.conf" section='emailhistory'}
{include file='tpl_inc/header.tpl'}
{if $step === 'uebersicht'}
    {include file='tpl_inc/emailhistory_uebersicht.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}