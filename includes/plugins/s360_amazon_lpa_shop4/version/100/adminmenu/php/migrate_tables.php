<?php

// benötigt, um alle JTL-Funktionen zur Verfügung zu haben
require_once(dirname(__FILE__) . '/../../../../../../globalinclude.php');
require_once(PFAD_ROOT . PFAD_INCLUDES . "tools.Global.php");
require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.Jtllog.php");

$oPlugin = Plugin::getPluginById('s360_amazon_lpa_shop4');
$oPluginAlt = Plugin::getPluginById('s360_amazon_lpa');
require_once($oPlugin->cFrontendPfad . 'lib/lpa_defines.php');
require_once($oPlugin->cFrontendPfad . 'lib/lpa_utils.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPAController.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPADatabase.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPAAdapter.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPAStatusHandler.php');


// die Antwort ist im JSON Format
header('Content-Type: application/json');

try {
    if (!$oPlugin || $oPlugin->kPlugin == 0 || empty($oPluginAlt)) {
        Jtllog::writeLog('LPA: Fehler beim Migrieren: Plugin-Objekt(e) konnte nicht geladen werden!', JTLLOG_LEVEL_ERROR);
        echo json_encode(array('status' => 'error'));
        return;
    }

    $db = new LPADatabase();
    $oldPluginId = 's360_amazon_lpa';
    $oldTablePrefix = 'xplugin_' . $oldPluginId . '_';
    $newTablePrefix = 'xplugin_' . S360_LPA_PLUGIN_ID . '_';
    $tablesNames = array('taccountmapping', 'tauthorization', 'tcapture', 'torder', 'trefund');

    $userMessages = array();

    $result = array();

    $mode = StringHandler::filterXSS($_POST['operation']);

    if ($mode === 'migrate') {
        $error = false;
        
        /*
         * Migrate business data.
         */
        foreach ($tablesNames as $tableName) {
            $oldtable = $oldTablePrefix . $tableName;
            $newtable = $newTablePrefix . $tableName;
            $userMessages[] = "Suche nach Tabelle $oldtable";
            $results = Shop::DB()->query("SELECT * FROM $oldtable", 2);
            if ($results !== FALSE) {
                $userMessages[] = "Migriere Tabelle $oldtable nach $newtable";
                $count = 0;
                foreach ($results as $res) {
                    $count = $count + 1;
                    $db->insertOrUpdate($res, $newtable, TRUE);
                }
                $userMessages[] = "$count Zeilen migriert.";
            } else {
                $userMessages[] = "Tabelle $oldtable nicht gefunden!";
                $error = true;
            }
        }
        $userMessages[] = "Migration abgeschlossen.";
        if ($error) {
            $result['status'] = 'error';
        } else {
            $result['status'] = 'success';
        }
    }

    $result['messages'] = $userMessages;

    echo json_encode($result);
    return;
} catch (Exception $ex) {
    echo json_encode(array('status' => 'error', 'message' => $ex->getMessage()));
    return;
}