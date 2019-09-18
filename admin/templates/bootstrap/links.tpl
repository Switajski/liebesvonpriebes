{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='links'}
{if $step === 'uebersicht'}
    {include file='tpl_inc/links_uebersicht.tpl'}
{elseif $step === 'neue Linkgruppe'}
    {include file='tpl_inc/links_neue_linkgruppe.tpl'}
{elseif $step === 'neuer Link'}
    {include file='tpl_inc/links_neuer_link.tpl'}
{elseif $step === 'linkgruppe_loeschen_confirm'}
    {include file='tpl_inc/links_loesch_confirm.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}