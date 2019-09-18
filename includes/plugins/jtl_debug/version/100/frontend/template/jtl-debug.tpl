{if $oPlugin_jtl_debug->oPluginEinstellungAssoc_arr.jtl_debug_show_text_links === 'Y'}
	<a id="jtl-debug-show" href="#">{$oPlugin_jtl_debug->oPluginSprachvariableAssoc_arr.textlink_show}</a>
{/if}
<div id="jtl-debug-content">
	<div class="jtl-debug-search">
		{if $oPlugin_jtl_debug->oPluginEinstellungAssoc_arr.jtl_debug_show_text_links === 'Y'}
			<a id="jtl-debug-hide" href="#">{$oPlugin_jtl_debug->oPluginSprachvariableAssoc_arr.textlink_hide}</a>
		{/if}
		<input type="text" id="jtl-debug-searchbox" placeholder="{$oPlugin_jtl_debug->oPluginSprachvariableAssoc_arr.enter_search_term}" />
		<span id="jtl-debug-search-results"></span>
		<span id="jtl-debug-info-area">Fetching Debug Objects...</span>
	</div>
</div>