<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

if (!isset($oJobQueue)) {
    global $oJobQueue;
}

if (isset($oJobQueue) && $oJobQueue->cJobArt === 'JTLSearchExport') {
    require_once PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/includes/defines_inc.php';
    require_once JTLSEARCH_PFAD_INCLUDES . 'global_inc.php';
    require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_INCLUDES . 'defines_inc.php';
    require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_MODULES . '/export/class.JTLSEARCH_Verwaltung_export.php';

    $oExport = new JTLSEARCH_Verwaltung_export($oDebugger);

    $oDebugger->doDebug(__FILE__ . ': Cron Starten ($oJobQueue->nLimitN = ' . $oJobQueue->nLimitN . ').');

    $oJobQueue->nInArbeit = 1;
    $oJobQueue->updateJobInDB();

    if ($oJobQueue->cTabelle == 'JTLSearchDeltaExportCron') {
        $nExportMethod = 3;
        $oDebugger->doDebug(__FILE__ . ' Export-Methode: ' . $nExportMethod . ' (Delta-Export)', JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
    } else {
        $nExportMethod = 1;
        $oDebugger->doDebug(__FILE__ . ' Export-Methode: ' . $nExportMethod . ' (Aufgabenplaner)', JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
    }
    if ($oJobQueue->nLimitN == 0) {
        $oDebugger->doDebug(__FILE__ . ': Neue JTLSearchExportQueue erstellen.', JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
        $oExport->newQueue($nExportMethod);
    } else {
        $oDebugger->doDebug(__FILE__ . ': Bestehende JTLSearchExportQueue weiter bearbeitet (ab ' . $oJobQueue->nLimitN . ').', JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
    }

    $oResult = $oExport->doExport($nExportMethod);

    if (isset($oResult) && is_object($oResult)) {
        //nReturnCode 1 = Export nicht fertig, braucht weitere Durchläufe
        //nReturnCode 2 = Export fertig, braucht keine weiteren Durchläufe
        if ($oResult->nReturnCode == 2) {
            $oJobQueue->deleteJobInDB();
            $oDebugger->doDebug(__FILE__ . ': Export fertig, Job aus tjobqueue gelöscht.', JTLSEARCH_DEBUGGING_LEVEL_NOTICE);
        } else {
            $oJobQueue->nLimitN   = $oResult->nExported;
            $oJobQueue->nInArbeit = 0;
            $oJobQueue->updateJobInDB();
            $oDebugger->doDebug(__FILE__ . ': Export nicht fertig, Job in tjobqueue geupdated ($oJobQueue->nLimitN = ' . $oJobQueue->nLimitN . ').');
        }
    } else {
        $oDebugger->doDebug(__FILE__ . ': FEHLER beim abarbeiten des Crons. $oResult gibt es nicht.', JTLSEARCH_DEBUGGING_LEVEL_ERROR);
        $oJobQueue->nInArbeit = 0;
        $oJobQueue->updateJobInDB();
    }
    $oDebugger->doDebug(__FILE__ . ': Ende Cron.', JTLSEARCH_DEBUGGING_LEVEL_NOTICE);
    //@todo: check.
//	$GLOBALS['DB'] = new NiceDB(DB_HOST, DB_USER, DB_PASS, DB_NAME);
}
