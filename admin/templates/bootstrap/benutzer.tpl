{config_load file="$lang.conf" section="benutzer"}
{include file='tpl_inc/header.tpl'}

{if $action == '' || $action === 'account_view' || $action === 'group_view'}
    {include file='tpl_inc/benutzer_uebersicht.tpl'}
{elseif $action === 'account_add' || $action == 'account_edit'}
    {include file='tpl_inc/benutzer_bearbeiten.tpl'}
{elseif $action === 'group_add' || $action == 'group_edit'}
    {include file='tpl_inc/gruppe_bearbeiten.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}
