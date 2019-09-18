<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

$cPluginPath = "includes/plugins/{$oPlugin->cVerzeichnis}/version/{$oPlugin->nVersion}/frontend/template/";
if (!isset($oPlugin->oPluginEinstellungAssoc_arr['jtlsearch_suggest_align'])) {
    $oPlugin->oPluginEinstellungAssoc_arr['jtlsearch_suggest_align'] = 'left';
}
$cJS = <<<HTML
<script type="text/javascript">
	$(function() {
		$('.ac_input').jtl_search({
			'align' : '{$oPlugin->oPluginEinstellungAssoc_arr['jtlsearch_suggest_align']}',
			'url' : '{$oPlugin->cFrontendPfadURL}'
		});
	});
</script>
HTML;

pq('body')->append($cJS);
