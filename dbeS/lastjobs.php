<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Artikel.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Bestellung.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Jtllog.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';

if (auth()) {
    Shop::DB()->query("UPDATE tglobals SET dLetzteAenderung = now()", 4);
    $cError = '';
    // TMP Verzeichnis leeren
    if (!KEEP_SYNC_FILES) {
        delDirRecursively(PFAD_ROOT . PFAD_DBES_TMP);
    }

    LastJob::getInstance()->finishStdJobs();

    $oLastJob_arr = getJobs();
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('LastJob Job Array: ' .
            print_r($oLastJob_arr, true), JTLLOG_LEVEL_DEBUG, false, 'LastJob Job Array');
    }

    if (is_array($oLastJob_arr) && count($oLastJob_arr) > 0) {
        $conf = Shop::getSettings([CONF_GLOBAL, CONF_RSS, CONF_SITEMAP]);

        foreach ($oLastJob_arr as $oLastJob) {
            if (isset($oLastJob->nJob) && (int)$oLastJob->nJob > 0) {
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog('Lastjobs Job: ' .
                        print_r($oLastJob, true), JTLLOG_LEVEL_DEBUG, false, 'nJob', $oLastJob->nJob);
                }
                switch ((int)$oLastJob->nJob) {
                    // Bewertungserinnerung
                    case LASTJOBS_BEWERTUNGSERINNNERUNG:
                        require_once PFAD_ROOT . PFAD_ADMIN . 'includes/bewertungserinnerung.php';
                        baueBewertungsErinnerung();
                        updateJob(LASTJOBS_BEWERTUNGSERINNNERUNG);
                        break;

                    // Sitemap
                    case LASTJOBS_SITEMAP:
                        if (isset($conf['sitemap']['sitemap_wawiabgleich']) &&
                            $conf['sitemap']['sitemap_wawiabgleich'] === 'Y'
                        ) {
                            require_once PFAD_ROOT . PFAD_ADMIN . 'includes/sitemapexport.php';
                            generateSitemapXML();
                            updateJob(LASTJOBS_SITEMAP);
                        }
                        break;

                    // RSS
                    case LASTJOBS_RSS:
                        if (isset($conf['rss']['rss_wawiabgleich']) &&
                            $conf['rss']['rss_wawiabgleich'] === 'Y'
                        ) {
                            require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'rss_inc.php';
                            generiereRSSXML();
                            updateJob(LASTJOBS_RSS);
                        }
                        break;

                    // GarbageCollector
                    case LASTJOBS_GARBAGECOLLECTOR:
                        if (isset($conf['global']['garbagecollector_wawiabgleich']) &&
                            $conf['global']['garbagecollector_wawiabgleich'] === 'Y'
                        ) {
                            require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.GarbageCollector.php';
                            $oGarbageCollector = new GarbageCollector();
                            $oGarbageCollector->run();
                            updateJob(LASTJOBS_GARBAGECOLLECTOR);
                        }
                        break;
                }
            }
        }
    }
    die('0');
}
if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
    Jtllog::writeLog('BEENDE: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'lastjobs');
}
die('3');

/**
 * Hole alle Jobs
 *
 * @return array
 */
function getJobs()
{
    $GLOBALS['nIntervall'] = defined('LASTJOBS_INTERVALL') ? LASTJOBS_INTERVALL : 12;
    executeHook(HOOK_LASTJOBS_HOLEJOBS);

    return LastJob::getInstance()->getRepeatedJobs($GLOBALS['nIntervall']);
}

/**
 * Setzt das dErstellt Datum neu auf die aktuelle Zeit
 *
 * @param int $nJob
 * @return bool
 */
function updateJob($nJob)
{
    return LastJob::getInstance()->restartJob($nJob);
}
