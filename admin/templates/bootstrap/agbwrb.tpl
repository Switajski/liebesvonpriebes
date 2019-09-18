{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='agbwrb'}
{if $step === 'agbwrb_uebersicht'}
    {include file='tpl_inc/agbwrb_uebersicht.tpl'}
{elseif $step === 'agbwrb_editieren'}
    {include file='tpl_inc/agbwrb_editieren.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}