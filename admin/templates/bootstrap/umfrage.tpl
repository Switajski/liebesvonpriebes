{config_load file="$lang.conf" section='umfrage'}
{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=#umfrage# cBeschreibung=#umfrageDesc# cDokuURL=#umfrageURL#}
{if $step === 'umfrage_erstellen' || $step === 'umfrage_editieren'}
    {include file='tpl_inc/umfrage_erstellen.tpl'}
{elseif $step === 'umfrage_uebersicht'}
    {include file='tpl_inc/umfrage_uebersicht.tpl'}
{elseif $step === 'umfrage_statistik'}
    {include file='tpl_inc/umfrage_statistik.tpl'}
{elseif $step === 'umfrage_frage_erstellen'}
    {include file='tpl_inc/umfrage_frage_erstellen.tpl'}
{elseif $step === 'umfrage_vorschau'}
    {include file='tpl_inc/umfrage_vorschau.tpl'}
{elseif $step === 'umfrage_statistik_sonstige_texte'}
    {include file='tpl_inc/umfrage_statistik_sonstige_texte.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}