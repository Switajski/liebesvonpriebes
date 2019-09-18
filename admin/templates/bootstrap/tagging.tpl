{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='tagging'}
{if $step === 'uebersicht'}
    {include file='tpl_inc/tagging_uebersicht.tpl'}
{elseif $step === 'detail' || $step === 'detailloeschen'}
    {include file='tpl_inc/tagging_tagdetail.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}