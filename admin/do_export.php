<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'exportformat_inc.php';

@ini_set('max_execution_time', 0);

if (!isset($_GET['e']) || !((int)$_GET['e'] > 0) || !validateToken()) {
    die('0');
}

$queue = Shop::DB()->select('texportqueue', 'kExportqueue', (int)$_GET['e']);
if (!isset($queue->kExportformat) || !$queue->kExportformat || !$queue->nLimit_m) {
    die('1');
}
$ef = new Exportformat($queue->kExportformat);
if (!$ef->isOK()) {
    die('2');
}

$ef->startExport(
    $queue,
    isset($_GET['ajax']),
    isset($_GET['back']) && $_GET['back'] === 'admin',
    false,
    (isset($_GET['max']) ? (int)$_GET['max'] : null)
);
