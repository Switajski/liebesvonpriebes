<script data-ignore="true" type="text/javascript">
    var jtl_debug = {ldelim}{rdelim};
    jtl_debug.jtl_lang_var_search_results = '{$oPlugin_jtl_debug->oPluginSprachvariableAssoc_arr.search_results}';
    jtl_debug.enableSmartyDebugParam      = '{$oPlugin_jtl_debug->oPluginEinstellungAssoc_arr.jtl_debug_query_string}';
    jtl_debug.getDebugSessionParam        = 'jtl-debug-session';
</script>

{if $oPlugin_jtl_debug->oPluginEinstellungAssoc_arr.jtl_debug_show_text_links === 'Y'}
    <a id="jtl-debug-show" class="btn btn-primary" href="#">{$oPlugin_jtl_debug->oPluginSprachvariableAssoc_arr.textlink_show}</a>
{/if}
<div id="jtl-debug-content">
    <div class="jtl-debug-search">
        {if $oPlugin_jtl_debug->oPluginEinstellungAssoc_arr.jtl_debug_show_text_links === 'Y'}
            <a id="jtl-debug-hide" class="btn btn-default"  href="#">{$oPlugin_jtl_debug->oPluginSprachvariableAssoc_arr.textlink_hide}</a>
        {/if}
        <input type="text" id="jtl-debug-searchbox" placeholder="{$oPlugin_jtl_debug->oPluginSprachvariableAssoc_arr.enter_search_term}" />
        <span id="jtl-debug-search-results"></span>
        <span id="jtl-debug-info-area">Fetching debug objects...</span>
    </div>
</div>