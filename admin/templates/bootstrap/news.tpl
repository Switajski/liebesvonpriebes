{config_load file="$lang.conf" section='news'}
{include file='tpl_inc/header.tpl'}
{if $step === 'news_erstellen' || $step === 'news_editieren'}
    {include file='tpl_inc/news_erstellen.tpl'}
{elseif $step === 'news_kategorie_erstellen'}
    {include file='tpl_inc/news_kategorie_erstellen.tpl'}
{elseif $step === 'news_uebersicht'}
    {include file='tpl_inc/news_uebersicht.tpl'}
{elseif $step === 'news_vorschau'}
    {include file='tpl_inc/news_vorschau.tpl'}
{elseif $step === 'news_kommentar_editieren'}
    {include file='tpl_inc/news_kommentar_editieren.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}