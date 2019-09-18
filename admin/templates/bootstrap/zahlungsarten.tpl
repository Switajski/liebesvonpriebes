{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='zahlungsarten'}
{if $step === 'uebersicht'}
    {include file='tpl_inc/zahlungsarten_uebersicht.tpl'}
{elseif $step === 'einstellen'}
    {include file='tpl_inc/zahlungsarten_einstellen.tpl'}
{elseif $step === 'log'}
    {include file='tpl_inc/zahlungsarten_log.tpl'}
{elseif $step === 'payments'}
    {include file='tpl_inc/zahlungsarten_payments.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}