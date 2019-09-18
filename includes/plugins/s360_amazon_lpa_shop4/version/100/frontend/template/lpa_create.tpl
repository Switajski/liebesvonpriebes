{if isset($lpa_create_result) && $lpa_create_result == true}
    {* This should only be shown when new customer accounts need manual activation *}
    <div>{lang key="activateAccountDesc" section="global"}</div>
{else}
    <div class="alert alert-danger">{$oPlugin->oPluginSprachvariableAssoc_arr.lpa_check_input}</div>
    {include file="$lpa_template_path_create_form"}
{/if}