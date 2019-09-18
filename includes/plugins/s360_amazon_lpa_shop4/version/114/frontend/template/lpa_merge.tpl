{if isset($lpa_merge_result) && $lpa_merge_result == true}
    {if $lpa_merge_hinweis}
        <div class="alert alert-info">{$lpa_merge_hinweis}</div>
    {/if}
    {$oPlugin->oPluginSprachvariableAssoc_arr.lpa_accounts_linked}
{else}
    <div class="col-xs-12">
        <div class="alert alert-danger">{$oPlugin->oPluginSprachvariableAssoc_arr.lpa_check_input}</div>
    </div>
    {include file="$lpa_template_path_merge_form"}
{/if}