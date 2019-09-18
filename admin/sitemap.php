<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'sitemapexport.php';

@ini_set('max_execution_time', 0);

$oAccount->permission('EXPORT_SITEMAP_VIEW', true, true);

generateSitemapXML();

if (isset($_REQUEST['update']) && (int)$_REQUEST['update'] === 1) {
    header('Location: sitemapexport.php?update=1');
} else {
    header('Cache-Control: no-cache, must-revalidate');
    header('Content-type: application/xml');
    header('Content-Disposition: attachment; filename="sitemap_index.xml"');
    readfile(PFAD_ROOT . 'sitemap.xml');
}
