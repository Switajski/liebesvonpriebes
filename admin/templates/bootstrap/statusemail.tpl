{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='statusemail'}
{if $step === 'statusemail_uebersicht'}
    {include file='tpl_inc/statusemail_uebersicht.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}