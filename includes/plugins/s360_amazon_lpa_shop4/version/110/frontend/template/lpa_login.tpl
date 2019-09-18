{if $lpa_login_display_mode === 'default'}
    {$oPlugin->oPluginSprachvariableAssoc_arr.lpa_login_success}
{elseif $lpa_login_display_mode === 'merge'}
    {include file="$lpa_template_path_merge_form"}
{elseif $lpa_login_display_mode === 'create'}
    {include file="$lpa_template_path_create_form"}
{elseif $lpa_login_display_mode === 'error'}
    <div class="alert alert-danger">
        {$oPlugin->oPluginSprachvariableAssoc_arr.lpa_generic_error}
    <div>
{/if}