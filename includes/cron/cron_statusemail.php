<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_INCLUDES . 'tools.Global.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'statusemail_inc.php';

/**
 * @param JobQueue $oJobQueue
 */
function bearbeiteStatusemail($oJobQueue)
{
    $bAusgefuehrt = false;
    $oStatusemail = $oJobQueue->holeJobArt();

    if ($oStatusemail === null) {
        return;
    }

    $oStatusemail->nIntervall_arr = StringHandler::parseSSK($oStatusemail->cIntervall);
    $oStatusemail->nInhalt_arr    = StringHandler::parseSSK($oStatusemail->cInhalt);

    // Laufe alle Intervalle durch
    foreach ($oStatusemail->nIntervall_arr as $nIntervall) {
        $nIntervall         = (int)$nIntervall;
        $cInterval          = '';
        $cIntervalAdj       = '';
        $dLetzterVersand    = '';
        $dLetzterVersandCol = '';

        switch ($nIntervall) {
            case 1:
                $cInterval          = 'day';
                $cIntervalAdj       = 'Tägliche';
                $dLetzterVersand    = $oStatusemail->dLetzterTagesVersand;
                $dLetzterVersandCol = 'dLetzterTagesVersand';
                break;
            case 7:
                $cInterval          = 'week';
                $cIntervalAdj       = 'Wöchentliche';
                $dLetzterVersand    = $oStatusemail->dLetzterWochenVersand;
                $dLetzterVersandCol = 'dLetzterWochenVersand';
                break;
            case 30:
                $cInterval          = 'month';
                $cIntervalAdj       = 'Monatliche';
                $dLetzterVersand    = $oStatusemail->dLetzterMonatsVersand;
                $dLetzterVersandCol = 'dLetzterMonatsVersand';
                break;
            default:
                continue;
        }

        if (isIntervalExceeded($dLetzterVersand, $cInterval)) {
            $dVon        = $dLetzterVersand;
            $dBis        = date_create()->format('Y-m-d H:i:s');
            $oMailObjekt = baueStatusEmail($oStatusemail, $dVon, $dBis);

            if ($oMailObjekt) {
                $oMailObjekt->cIntervall = utf8_decode($cIntervalAdj . ' Status-Email');
                sendeMail(MAILTEMPLATE_STATUSEMAIL, $oMailObjekt, $oMailObjekt->mail);
                Shop::DB()->query("
                    UPDATE tstatusemail
                        SET " . $dLetzterVersandCol . " = now()
                        WHERE nAktiv = " . (int)$oJobQueue->kKey,
                    4);
                $bAusgefuehrt = true;

                if (isset($oMailObjekt->mail->oAttachment_arr)) {
                    unlink($oMailObjekt->mail->oAttachment_arr[0]->cFilePath);
                }
            }
        }
    }

    if ($bAusgefuehrt === true) {
        $oJobQueue->deleteJobInDB();
    }

    unset($oJobQueue);
}
