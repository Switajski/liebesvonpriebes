<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_INCLUDES . 'tools.Global.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.TrustedShops.php';

/**
 * @param JobQueue $oJobQueue
 */
function bearbeiteTrustedShopsKundenbewertung($oJobQueue)
{
    $oJobQueue->nInArbeit = 1;
    $cValidSprachISO_arr  = ['de', 'en', 'fr', 'pl', 'es'];
    foreach ($cValidSprachISO_arr as $cValidSprachISO) {
        unset($oTrustedShops);
        $oTrustedShops                = new TrustedShops(-1, $cValidSprachISO);
        $oTrustedShopsKundenbewertung = $oTrustedShops->holeKundenbewertungsstatus($cValidSprachISO);
        if ($oTrustedShopsKundenbewertung->nStatus == 1 && strlen($oTrustedShopsKundenbewertung->cTSID) > 0) {
            $returnValue = $oTrustedShops->aenderKundenbewertungsstatus(
                $oTrustedShopsKundenbewertung->cTSID,
                1,
                $cValidSprachISO
            );
            if ($returnValue !== 1) {
                $oTrustedShops->aenderKundenbewertungsstatusDB(
                    0,
                    $oTrustedShopsKundenbewertung->cISOSprache
                );
            }
        }
    }

    $oJobQueue->dZuletztGelaufen = date('Y-m-d H:i');
    $oJobQueue->nInArbeit        = 0;
    $oJobQueue->updateJobInDB();
    $oJobQueue->deleteJobInDB();
    unset($oJobQueue);
}
