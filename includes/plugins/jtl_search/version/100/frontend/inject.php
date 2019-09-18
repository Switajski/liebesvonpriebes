<?php

$cPluginPath = "includes/plugins/{$oPlugin->cVerzeichnis}/version/{$oPlugin->nVersion}/frontend/template/";

$cJS = <<<HTML
<script type="text/javascript" src="{$cPluginPath}js/suggest.js"></script>
<script type="text/javascript">
	$(function() {
		$('.ac_input').jtl_search({
			'align' : 'left',
			'url' : '{$oPlugin->cFrontendPfadURLSSL}'
		});
	});
</script>
HTML;

$cCSS = <<<HTML
<link type="text/css" href="{$cPluginPath}css/suggest.css" rel="stylesheet" />
HTML;

pq('head')->append($cJS)->append($cCSS);
