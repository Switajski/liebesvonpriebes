{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='warenlager'}
{include file='tpl_inc/seite_header.tpl' cTitel=#warenlager# cBeschreibung=#warenlagerDesc# cDokuURL=#warenlagerURL#}
{if $cStep === 'uebersicht'}
    {include file='tpl_inc/warenlager_uebersicht.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}