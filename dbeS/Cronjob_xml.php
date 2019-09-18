<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/NetSync_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'smartyinclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'exportformat_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'exportformat_queue_inc.php';

/**
 * Class SyncCronjob
 */
class SyncCronjob extends NetSyncHandler
{
    /**
     *
     */
    protected function init()
    {
    }

    /**
     * @param Exception $oException
     */
    public static function exception($oException)
    {
        var_dump($oException);
    }

    /**
     * @param int $eRequest
     */
    protected function request($eRequest)
    {
        switch ($eRequest) {
            case NetSyncRequest::CronjobStatus:
                $oExport_arr = holeExportformatCron();
                if (is_array($oExport_arr)) {
                    foreach ($oExport_arr as &$oExport) {
                        $oExport = new CronjobStatus(
                            $oExport->kCron,
                            $oExport->cName,
                            $oExport->dStart_de,
                            $oExport->nAlleXStd,
                            (int)$oExport->oJobQueue->nLimitN,
                            (int)$oExport->nAnzahlArtikel->nAnzahl,
                            $oExport->dLetzterStart_de,
                            $oExport->dNaechsterStart_de
                        );
                    }
                }

                self::throwResponse(NetSyncResponse::Ok, $oExport_arr);
                break;

            case NetSyncRequest::CronjobHistory:
                $oExport_arr = holeExportformatQueueBearbeitet(24 * 7);
                if (is_array($oExport_arr)) {
                    foreach ($oExport_arr as &$oExport) {
                        $oExport = new CronjobHistory(
                            $oExport->cName,
                            $oExport->cDateiname,
                            $oExport->nLimitN,
                            $oExport->dZuletztGelaufen_DE
                        );
                    }
                }

                self::throwResponse(NetSyncResponse::Ok, $oExport_arr);
                break;

            case NetSyncRequest::CronjobTrigger:
                $bCronManuell = true;
                require_once PFAD_ROOT . PFAD_INCLUDES . 'cron_inc.php';

                self::throwResponse(NetSyncResponse::Ok, true);
                break;
        }
    }
}

NetSyncHandler::create('SyncCronjob');
