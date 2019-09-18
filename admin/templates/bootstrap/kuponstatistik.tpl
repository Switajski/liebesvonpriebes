{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='couponStatistics'}
{if $step === 'kuponstatistik_uebersicht'}
    {include file='tpl_inc/kuponstatistik_uebersicht.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}