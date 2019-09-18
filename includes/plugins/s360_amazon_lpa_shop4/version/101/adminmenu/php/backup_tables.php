<?php
// benötigt, um alle JTL-Funktionen zur Verfügung zu haben
require_once(dirname(__FILE__) . '/../../frontend/lib/lpa_includes.php');
require_once(PFAD_ROOT . PFAD_INCLUDES . "tools.Global.php");
require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.Jtllog.php");

$oPlugin = Plugin::getPluginById('s360_amazon_lpa_shop4');
require_once($oPlugin->cFrontendPfad . 'lib/lpa_defines.php');
require_once($oPlugin->cFrontendPfad . 'lib/lpa_utils.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPAController.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPADatabase.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPAAdapter.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPAStatusHandler.php');


// die Antwort ist im JSON Format
header('Content-Type: application/json');


if (!$oPlugin || $oPlugin->kPlugin == 0) {
    Jtllog::writeLog('LPA: Fehler beim Datenbankmanagement: Plugin-Objekt konnte nicht geladen werden!', JTLLOG_LEVEL_ERROR);
    echo json_encode(array('status' => 'error'));
    return;
}

$db = new LPADatabase();
$backupPath = $oPlugin->cFrontendPfad . 'backup/';

$userMessages = array();

$result = array();

$mode = StringHandler::filterXSS($_POST['operation']);

if ($mode === 'export') {
    $db->exportTables($backupPath . 'lpa_export_' . time());
    $userMessages[] =  'Tabellen wurden exportiert.';
    $result['status'] = 'success';
} elseif ($mode === 'import') {
    $importPath = StringHandler::filterXSS($_POST['id']);
    $importPath = str_replace(array('/'), "", $importPath); // also filter forward slashes - the variable is expected to contain a directory name only
    $db->importTables($backupPath . $importPath);
    $userMessages[] =  'Tabellen wurden importiert.';
    $result['status'] = 'success';
} else {
    $userMessages[] =  'Unbekannte Operation: ' . $mode ;
    $result['status'] = 'fail';
}

$result['messages'] = $userMessages;

echo json_encode($result);
return;
