<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
defined('JTLCRON') || define('JTLCRON', true);

if (!isset($bCronManuell) || !$bCronManuell) {
    require_once __DIR__ . '/globalinclude.php';
}
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.JobQueue.php';

$oJobQueue_arr = Shop::DB()->query("SELECT * FROM tjobqueue WHERE nInArbeit = 0 AND dStartZeit < now()", 2);
if (is_array($oJobQueue_arr) && count($oJobQueue_arr) > 0) {
    foreach ($oJobQueue_arr as $i => $oJobQueueTMP) {
        if ($i >= JOBQUEUE_LIMIT_JOBS) {
            break;
        }
        $oJobQueue = new JobQueue(
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
        Jtllog::cronLog('Got job ' . $oJobQueue->kJobQueue . ' (kCron = ' .
            $oJobQueue->kCron . ', type = ' . $oJobQueue->cJobArt . ')');
        if (Jtllog::doLog(JTLLOG_LEVEL_NOTICE)) {
            Jtllog::writeLog(
                print_r($oJobQueue, true),
                JTLLOG_LEVEL_NOTICE,
                false,
                'kJobQueue',
                $oJobQueueTMP->kJobQueue
            );
        }

        switch ($oJobQueue->cJobArt) {
            case 'newsletter':
                require_once PFAD_ROOT . PFAD_INCLUDES . PFAD_CRON . 'cron_newsletterversand.php';
                bearbeiteNewsletterversand($oJobQueue);
                break;

            case 'exportformat':
                require_once PFAD_ROOT . PFAD_INCLUDES . PFAD_CRON . 'cron_exportformate.php';
                bearbeiteExportformate($oJobQueue);
                break;

            case 'statusemail':
                require_once PFAD_ROOT . PFAD_INCLUDES . PFAD_CRON . 'cron_statusemail.php';
                bearbeiteStatusemail($oJobQueue);
                break;

            case 'tskundenbewertung':
                require_once PFAD_ROOT . PFAD_INCLUDES . PFAD_CRON . 'cron_trustedshopskundenbewertung.php';
                bearbeiteTrustedShopsKundenbewertung($oJobQueue);
                break;

            case 'clearcache':
                require_once PFAD_ROOT . PFAD_INCLUDES . PFAD_CRON . 'cron_clearcache.php';
                bearbeiteClearCache($oJobQueue);
                break;

            default:
                break;
        }
        executeHook(HOOK_JOBQUEUE_INC_BEHIND_SWITCH, ['oJobQueue' => &$oJobQueue]);
    }
} else {
    Jtllog::cronLog('No jobs found', 3);
}
