{config_load file="$lang.conf" section='pluginverwaltung'}
{include file='tpl_inc/header.tpl'}
{if $step === 'pluginverwaltung_uebersicht'}
    {include file='tpl_inc/pluginverwaltung_uebersicht.tpl'}
{elseif $step === 'pluginverwaltung_sprachvariablen'}
    {include file='tpl_inc/pluginverwaltung_sprachvariablen.tpl'}
{elseif $step === 'pluginverwaltung_lizenzkey'}
    {include file='tpl_inc/pluginverwaltung_lizenzkey.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}