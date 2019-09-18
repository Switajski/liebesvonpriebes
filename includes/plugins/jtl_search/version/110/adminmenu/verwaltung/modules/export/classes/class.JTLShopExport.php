<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.Export.php';

/**
 * Description of JTLShopExport
 *
 * @author Andre Vermeulen
 */
class JTLShopExport extends Export
{
    /**
     * @return stdClass
     */
    protected function nextRun()
    {
        $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; Vorbereitung eines neuen Durchgangs.');
        $this->oJTLSearchExportQueue->save();

        $oReturnObj              = new stdClass();
        $oReturnObj->nReturnCode = 1;
        $oReturnObj->nCountAll   = $this->oJTLSearchExportQueue->getSumCount();
        $oReturnObj->nExported   = $this->oJTLSearchExportQueue->getLimitN();

        $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; $oReturnObj = ' . print_r($oReturnObj, true));

        switch ($this->oJTLSearchExportQueue->getExportMethod()) {
            //Export über Cron
            case 1:
                return $oReturnObj;
                break;

            //Export über Ajax (manuell gestartet)
            case 2:
                echo json_encode($oReturnObj);
                die();
                break;

            default:
                return $oReturnObj;
                break;

        }
    }

    /**
     * @return stdClass
     */
    protected function lastRun()
    {
        $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; Nachbereitung des Exports (Letzter Durchgang).');
        $oCountExport_arr = null;
        //Delta-Export
        if ($this->oJTLSearchExportQueue->getExportMethod() == 3) {
            $oCountExport_arr = Shop::DB()->query("SELECT kId, eDocumentType FROM tjtlsearchdeltaexport WHERE bDelete = 1", 2);
            if (is_array($oCountExport_arr) && count($oCountExport_arr) > 0) {
                foreach ($oCountExport_arr as $oCountExport) {
                    $cKeyName              = 'k' . strtoupper($oCountExport->eDocumentType[0]) . substr($oCountExport->eDocumentType, 1);
                    $oDelItem              = new stdClass();
                    $oDelItem->{$cKeyName} = $oCountExport->kId;
                    $oDelItem->cObjectType = strtoupper($oCountExport->eDocumentType[0]) . substr($oCountExport->eDocumentType, 1);
                    $oDelItem->bDelete     = 1;
                    file_put_contents($this->getFileName(false, $this->cExportPath), json_encode($oDelItem) . "\n", FILE_APPEND);
                    unset($oDelItem);
                }
            }
        }

        $oReturnObj              = new stdClass();
        $oReturnObj->nReturnCode = 2;
        $oReturnObj->nCountAll   = $this->oJTLSearchExportQueue->getSumCount();
        $oReturnObj->nExported   = $this->oJTLSearchExportQueue->getLimitN();
        if (is_array($oCountExport_arr) && count($oCountExport_arr) > 0) {
            $oReturnObj->nExported += count($oCountExport_arr);
        }

        $this->oJTLSearchExportQueue->setFinished(true)
                                    ->setLastRun(date('Y-m-d H:i:s'))
                                    ->save();
        if ($oReturnObj->nExported > 0) {
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; Daten werden gezippt.');
            $this->zipFile();
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; Daten wurden gezippt.');

            $oReturnObj->nServerResponse = $this->sendFileToImportQueue();

            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; $oReturnObj = ' . print_r($oReturnObj, true));
        } else {
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; Keine Daten exportiert (' . $oReturnObj->nExported . ').');
            $oReturnObj->nServerResponse = 0;
        }
        switch ($this->oJTLSearchExportQueue->getExportMethod()) {
            //Export über Cron
            case 1:
                return $oReturnObj;
                break;
            //Export über Ajax (manuell gestartet)
            case 2:
                echo json_encode($oReturnObj);
                break;
            //
            case 3:
                $nDeletedRows = Shop::DB()->query('TRUNCATE TABLE tjtlsearchdeltaexport', 3);
                if ($nDeletedRows != $oReturnObj->nCountAll) {
                    $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; ' . $nDeletedRows . ' Zeilen aus tjtlsearchdeltaexport gelöscht, ' . $oReturnObj->nCountAll . 'Zeilen exportiert.');
                }

                return $oReturnObj;
                break;

            default:
                return $oReturnObj;
                break;
        }
    }

    /**
     * @return mixed
     */
    protected function sendFileToImportQueue()
    {
        if ($this->oJTLSearchExportQueue->getExportMethod() == 3) {
            $cExportFile        = JTLSEARCH_URL_DELTA_EXPORTFILE_ZIP;
            $xData_arr['delta'] = 1;
        } else {
            $cExportFile = JTLSEARCH_URL_EXPORTFILE_ZIP;
        }

        $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; File in die Importqueue des Suchservers schicken.');
        require_once JTLSEARCH_PFAD_CLASSES . 'class.Communication.php';
        require_once JTLSEARCH_PFAD_CLASSES . 'class.Security.php';
        require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_CLASSES . 'class.JTLSEARCH_Verwaltung_Base.php';

        $oServerSettings = JTLSEARCH_Verwaltung_Base::getServerSettings();

        // Security Objekt erstellen und Parameter zum senden der Daten setzen
        $oSecurity = new Security($oServerSettings->cProjectId, $oServerSettings->cAuthHash);
        $oSecurity->setParam_arr(array('getexport', urlencode($cExportFile)));

        $xData_arr['a']   = 'getexport';
        $xData_arr['pid'] = $oServerSettings->cProjectId;
        $xData_arr['url'] = urlencode($cExportFile);
        $xData_arr['p']   = $oSecurity->createKey();

        $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; $xData_arr = ' . print_r($xData_arr, true));

        //Antwort-/Fehler-Codes:
        // 1 = Alles O.K.
        // 2 = Authentifikation fehlgeschlagen
        // 3 = Benutzer wurde nicht gefunden
        // 4 = Auftrag konnte nicht in die Queue gespeichert werden
        // 5 = Requester IP stimmt nicht mit der Domain aus der Datenbank ueberein
        try {
            $cReturn = Communication::postData(urldecode($oServerSettings->cServerUrl) . 'importdaemon/index.php', $xData_arr);
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; Antwort von Suchserver: ' . $cReturn);

            return json_decode($cReturn);
        } catch (Exception $oEx) {
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; Fehler beim Senden der Datei in die Importqueue.');
        }

        return '';
    }

    /**
     * @param $cPath
     * @return $this
     */
    public function setExportPath($cPath)
    {
        if (is_string($cPath) && strlen($cPath) > 0) {
            $this->cExportPath = $cPath;
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; Exportpfad auf "' . $cPath . '" festlegen.');
        }

        return $this;
    }
}
