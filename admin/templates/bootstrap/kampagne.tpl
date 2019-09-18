{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='kampagne'}
{if $step === 'kampagne_uebersicht'}
    {include file='tpl_inc/kampagne_uebersicht.tpl'}
{elseif $step === 'kampagne_detail'}
    {include file='tpl_inc/kampagne_detail.tpl'}
{elseif $step === 'kampagne_defdetail'}
    {include file='tpl_inc/kampagne_defdetail.tpl'}
{elseif $step === 'kampagne_erstellen' || $step === 'kampagne_editieren'}
    {include file='tpl_inc/kampagne_erstellen.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}