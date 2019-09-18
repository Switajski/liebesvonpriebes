{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='versandarten'}
{if $step === 'uebersicht'}
    {include file='tpl_inc/versandarten_uebersicht.tpl'}
{elseif $step === 'neue Versandart'}
    {include file='tpl_inc/versandarten_neue_Versandart.tpl'}
{elseif $step === 'Zuschlagsliste'}
    {include file='tpl_inc/versandarten_zuschlagsliste.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}