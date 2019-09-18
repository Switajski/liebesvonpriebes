{config_load file="$lang.conf" section='exportformats'}
{include file='tpl_inc/header.tpl'}

{if $step === 'yategoexport_uebersicht'}
    {include file='tpl_inc/yategoexport_uebersicht.tpl'}
{/if}

{include file='tpl_inc/footer.tpl'}