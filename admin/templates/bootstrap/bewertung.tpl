{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="bewertungen"}

{if $step === 'bewertung_uebersicht'}
    {include file='tpl_inc/bewertung_uebersicht.tpl'}
{elseif $step === 'bewertung_editieren'}
    {include file='tpl_inc/bewertung_editieren.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}