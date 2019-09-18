{config_load file="$lang.conf" section='clickandbuy'}
{include file='tpl_inc/header.tpl'}
{if $step === 'cab_uebersicht'}
    {include file='tpl_inc/clickandbuy_uebersicht.tpl'}
{elseif $step === 'cab_anmeldung'}
    {include file='tpl_inc/clickandbuy_anmeldung.tpl'}    
{/if}
{include file='tpl_inc/footer.tpl'}