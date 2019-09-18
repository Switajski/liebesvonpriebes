<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */
ob_start();

require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'io_inc.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.IO.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.IOResponse.php';
$AktuelleSeite = 'IO';
/** @global JTLSmarty $smarty */
$io = IO::getInstance();
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
$smarty->setCaching(false)
       ->assign('BILD_KEIN_KATEGORIEBILD_VORHANDEN', BILD_KEIN_KATEGORIEBILD_VORHANDEN)
       ->assign('BILD_KEIN_ARTIKELBILD_VORHANDEN', BILD_KEIN_ARTIKELBILD_VORHANDEN)
       ->assign('BILD_KEIN_HERSTELLERBILD_VORHANDEN', BILD_KEIN_HERSTELLERBILD_VORHANDEN)
       ->assign('BILD_KEIN_MERKMALBILD_VORHANDEN', BILD_KEIN_MERKMALBILD_VORHANDEN)
       ->assign('BILD_KEIN_MERKMALWERTBILD_VORHANDEN', BILD_KEIN_MERKMALWERTBILD_VORHANDEN)
       ->assign('nSeitenTyp', PAGE_IO);
Shop::setPageType(PAGE_IO);

if (!isset($_REQUEST['io'])) {
    header(makeHTTPHeader(400));
    exit;
}

$request = $_REQUEST['io'];

executeHook(HOOK_IO_HANDLE_REQUEST, [
    'io'      => &$io,
    'request' => &$request
]);

try {
    $data = $io->handleRequest($request);
} catch (Exception $e) {
    $data = $e->getMessage();
    header(makeHTTPHeader(500));
}

ob_end_clean();

header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Content-type: application/json');

echo is_null($data) ? '{}' : json_encode($data);
