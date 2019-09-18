<script type="text/javascript">
$(document).ready(function() {ldelim}
   xajax_getRemoteDataAjax('{$JTLURL_GET_SHOPMARKETPLACE}?v={$nVersionDB}', 'oMarketplace_arr', 'widgets/marketplace_data.tpl', 'marketplace_data_wrapper');
   {if $cPluginCheck|count_characters > 0} 
   xajax_getRemoteDataAjax('{$JTLURL_GET_SHOPMARKETPLACE}', 'oMarketplaceUpdates_arr', 'widgets/marketplace_update_data.tpl', 'marketplace_update_data_wrapper', 'check={$cPluginCheck}', 'marketplace_showUpdateCount');
   {/if}
{rdelim});
    
    function marketplace_showUpdateCount(oMarketplaceUpdates_arr) {ldelim}
        if(typeof(oMarketplaceUpdates_arr) == 'object') {ldelim}
            $('#marketplace_update_data_count').html(oMarketplaceUpdates_arr.length);
        {rdelim}
    {rdelim}
</script>

<div class="widget-custom-data widget-patch tabber" id="widget-tabber">
    <div class="tabbertab">
    <h2>&Uuml;bersicht</h2>
        <div id="marketplace_data_wrapper">
        <p class="ajax_preloader">Wird geladen...</p>
        </div>
    </div>

    {if $cPluginCheck|count_characters > 0} 
    <div class="tabbertab">
        <h2 title="Plugins mit neuen Versionen">Plugins mit neuen Versionen (<span id="marketplace_update_data_count">0</span>)</h2>
        <div id="marketplace_update_data_wrapper">
        <p class="ajax_preloader">Wird geladen...</p>
        </div>
    </div>
    {/if}
</div>