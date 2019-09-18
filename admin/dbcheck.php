<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('DBCHECK_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dbcheck_inc.php';
/** @global JTLSmarty $smarty */
$cHinweis          = '';
$cFehler           = '';
$cDBStruct_arr     = getDBStruct();
$cDBFileStruct_arr = getDBFileStruct();
$cDBError_arr      = [];
$maintenanceResult = null;

if (!empty($_POST['action']) && !empty($_POST['check'])) {
    $maintenanceResult = doDBMaintenance($_POST['action'], $_POST['check']);
}

if (!is_array($cDBFileStruct_arr)) {
    $cFehler = 'Fehler beim Lesen der Struktur-Datei.';
}

if (strlen($cFehler) === 0) {
    $cDBError_arr = compareDBStruct($cDBFileStruct_arr, $cDBStruct_arr);
}

$smarty->assign('cFehler', $cFehler)
       ->assign('cDBFileStruct_arr', $cDBFileStruct_arr)
       ->assign('cDBError_arr', $cDBError_arr)
       ->assign('JTL_VERSION', JTL_VERSION)
       ->assign('maintenanceResult', $maintenanceResult)
       ->display('dbcheck.tpl');
