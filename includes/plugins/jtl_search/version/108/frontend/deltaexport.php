<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/includes/defines_inc.php';
require_once JTLSEARCH_PFAD_INCLUDES . 'global_inc.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_INCLUDES . 'defines_inc.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_INCLUDES . 'defines_inc.php';
require_once PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.JobQueue.php";

$fTimeStart = microtime(true);

$nDocumentCount = Shop::DB()->query('SELECT count(*) AS nCount FROM tjtlsearchdeltaexport', 1);
if ($nDocumentCount->nCount > 0) {
    // Alle Search-Einträge aus der Jobque schmeißen
    Shop::DB()->query('DELETE FROM tjobqueue WHERE cJobArt = "JTLSearchExport"', 3);
    Shop::DB()->query('UPDATE tjtlsearchexportqueue SET bFinished = 1 WHERE nExportMethod = 1', 3);

    if ($nDocumentCount->nCount <= JTLSEARCH_DELTAEXPORT_ITEMS_MAX) {
        Shop::DB()->query(
            "INSERT INTO `tjobqueue` (`kCron` ,`kKey` ,`cJobArt` ,`cTabelle` ,`cKey` ,`nLimitN` ,`nLimitM` ,`nInArbeit` ,`dStartZeit`)
        VALUES ('0', '3', 'JTLSearchExport', 'JTLSearchDeltaExportCron', 'kId', '0', " . JTLSEARCH_LIMIT_N_METHOD_3 . ", '0', NOW());", 10
        );
        $oDebugger->doDebug(__FILE__ . ': Eintrag wurde in tjobqueue geschrieben.', JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
    } else {
        // Neuen Eintrag in Jobqueue schreiben
        $oTmpJobQueue             = new JobQueue();
        $oTmpJobQueue->kKey       = $oPlugin->kPlugin;
        $oTmpJobQueue->cKey       = 'kExportqueue';
        $oTmpJobQueue->cTabelle   = 'tjtlsearchexportqueue';
        $oTmpJobQueue->cJobArt    = 'JTLSearchExport';
        $oTmpJobQueue->dStartZeit = date('Y-m-d H:i:s');
        $oTmpJobQueue->nLimitN    = 0;
        $oTmpJobQueue->nLimitM    = JTLSEARCH_LIMIT_N_METHOD_1;
        $oTmpJobQueue->nInArbeit  = 0;
        $oTmpJobQueue->speicherJobInDB();
        unset($oTmpJobQueue);
        Shop::DB()->query('TRUNCATE TABLE tjtlsearchdeltaexport', 10);
        $oDebugger->doDebug(__FILE__ . ': tjtlsearchdeltaexport wurde geleert (mehr als ' . JTLSEARCH_DELTAEXPORT_ITEMS_MAX . ' Dokumente).', JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
    }

    if (JTLSEARCH_DELTAEXPORT_TRIGGER_CRON) {
        $oJobQueueTMP = Shop::DB()->query('SELECT * FROM tjobqueue WHERE cJobArt = "JTLSearchExport" ORDER BY cTabelle ASC LIMIT 0, 1', 1);
        $oJobQueue    = new JobQueue(
            $oJobQueueTMP->kJobQueue,
            $oJobQueueTMP->kCron,
            $oJobQueueTMP->kKey,
            $oJobQueueTMP->nLimitN,
            $oJobQueueTMP->nLimitM,
            0,
            $oJobQueueTMP->cJobArt,
            $oJobQueueTMP->cTabelle,
            $oJobQueueTMP->cKey,
            $oJobQueueTMP->dStartZeit,
            $oJobQueueTMP->dZuletztGelaufen
        );
        require_once JTLSEARCH_PFAD_FRONTEND . 'cron.php';
    } else {
        $oDebugger->doDebug(__FILE__ . ': Export wurde nicht getriggert weil JTLSEARCH_DELTAEXPORT_TRIGGER_CRON != true.', JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
    }
} else {
    $oDebugger->doDebug(__FILE__ . ': Keine neuen Dokumente.', JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
}

$fTimeEnd  = microtime(true);
$fTimeUsed = $fTimeEnd - $fTimeStart;
$oDebugger->doDebug(__FILE__ . " Delta Startzeit: $fTimeStart \nEndzeit: $fTimeEnd \nGebrauchte Zeit: $fTimeUsed", JTLSEARCH_DEBUGGING_LEVEL_NOTICE);
