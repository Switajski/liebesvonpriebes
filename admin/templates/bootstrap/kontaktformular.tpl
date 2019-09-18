{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='kontaktformular'}
{if $step === 'uebersicht'}
    {include file='tpl_inc/kontaktformular_uebersicht.tpl'}
{elseif $step === 'betreff'}
    {include file='tpl_inc/kontaktformular_betreff.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}