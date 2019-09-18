<?php

/**
 * copyright (c) 2006-2011 JTL-Software-GmbH, all rights reserved
 *
 * this file may not be redistributed in whole or significant part
 * and is subject to the JTL-Software-GmbH license.
 *
 * license: http://jtl-software.de/jtlshop3license.html
 */
require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.Export.php');

/**
 * Description of JTLShopExport
 *
 * @author Andre Vermeulen
 */
class JTLShopExport extends Export
{
    protected function nextRun()
    {
        $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; Vorbereitung eines neuen Durchgangs.');
        $this->oJTLSearchExportQueue->save();

        $oReturnObj = new stdClass();
        $oReturnObj->nReturnCode = 1;
        $oReturnObj->nCountAll = $this->oJTLSearchExportQueue->getSumCount();
        $oReturnObj->nExported = $this->oJTLSearchExportQueue->getLimitN();

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

    protected function lastRun()
    {
        $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Nachbereitung des Exports (Letzter Durchgang).');
        
        //Delta-Export
        if ($this->oJTLSearchExportQueue->getExportMethod() == 3) {
            $oCountExport_arr = $this->oDB->getAsObject("SELECT kId, eDocumentType FROM tjtlsearchdeltaexport WHERE bDelete = 1 GROUP BY eDocumentType", 2);
            if (is_array($oCountExport_arr) && count($oCountExport_arr) > 0) {
                foreach ($oCountExport_arr as $oCountExport) {
                    $cKeyName = 'k'.strtoupper($oCountExport->eDocumentType[0]).substr($oCountExport->eDocumentType, 1);
                    $oDelItem = new stdClass();
                    $oDelItem->{$cKeyName} = $oCountExport->kId;
                    $oDelItem->cObjectType = strtoupper($oCountExport->eDocumentType[0]).substr($oCountExport->eDocumentType, 1);
                    $oDelItem->bDelete = 1;
                    file_put_contents($this->getFileName(false, $this->cExportPath), json_encode($oDelItem)."\n", FILE_APPEND);
                    unset($oDelItem);
                }
            }
        }
        
        $oReturnObj = new stdClass();
        $oReturnObj->nReturnCode = 2;
        $oReturnObj->nCountAll = $this->oJTLSearchExportQueue->getSumCount();
        $oReturnObj->nExported = $this->oJTLSearchExportQueue->getLimitN();

        $this->oJTLSearchExportQueue->setFinished(true);
        $this->oJTLSearchExportQueue->setLastRun(date('Y-m-d H:i:s'));
        $this->oJTLSearchExportQueue->save();
        if ($oReturnObj->nExported > 0) {
            $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Daten werden gezippt.');
            $this->zipFile();
            $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Daten wurden gezippt.');

            $oReturnObj->nServerResponse = $this->sendFileToImportQueue();

            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; $oReturnObj = ' . print_r($oReturnObj, true));
        } else {
            $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Keine Daten exportiert ('.$oReturnObj->nExported.').');
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
                $nDeletedRows = $this->oDB->execSQL('TRUNCATE TABLE `tjtlsearchdeltaexport` ');
                if ($nDeletedRows != $oReturnObj->nCountAll) {
                    $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; '.$nDeletedRows.' Zeilen aus tjtlsearchdeltaexport gelöscht, '.$oReturnObj->nCountAll. 'Zeilen exportiert.');
                }
                return $oReturnObj;
                break;

            default:
                return $oReturnObj;
                break;
        }
    }

    protected function sendFileToImportQueue()
    {
        if ($this->oJTLSearchExportQueue->getExportMethod() == 3) {
            $cExportFile = JTLSEARCH_URL_DELTA_EXPORTFILE_ZIP;
            $xData_arr['delta'] = 1;
        } else {
            $cExportFile = JTLSEARCH_URL_EXPORTFILE_ZIP;
        }
        
        $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; File in die Importqueue des Suchservers schicken.');
        require_once(JTLSEARCH_PFAD_CLASSES . 'class.Communication.php');
        require_once(JTLSEARCH_PFAD_CLASSES . 'class.Security.php');

        $oServerSettings = $this->oDB->getServerSettings();
        
        // Security Objekt erstellen und Parameter zum senden der Daten setzen
        $oSecurity = new Security($oServerSettings->cProjectId, $oServerSettings->cAuthHash);
        $oSecurity->setParam_arr(array('getexport', urlencode($cExportFile)));

        $xData_arr['a'] = 'getexport';
        $xData_arr['pid'] = $oServerSettings->cProjectId;
        $xData_arr['url'] = urlencode($cExportFile);
        $xData_arr['p'] = $oSecurity->createKey();

        $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; $xData_arr = '.  print_r($xData_arr, true));
        
        //Antwort-/Fehler-Codes:
        // 1 = Alles O.K.
        // 2 = Authentifikation fehlgeschlagen
        // 3 = Benutzer wurde nicht gefunden
        // 4 = Auftrag konnte nicht in die Queue gespeichert werden
        // 5 = Requester IP stimmt nicht mit der Domain aus der Datenbank ueberein
        try {
            $cReturn = Communication::postData(urldecode($oServerSettings->cServerUrl) . 'importdaemon/index.php', $xData_arr);
            $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Antwort von Suchserver: '.$cReturn);
            return json_decode($cReturn);
        } catch (Exception $oEx) {
            $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Fehler beim Senden der Datei in die Importqueue.');
        }
    }

    public function setExportPath($cPath)
    {
        if (is_string($cPath) && strlen($cPath) > 0) {
            $this->cExportPath = $cPath;
            $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Exportpfad auf "'.$cPath.'" festlegen.');
        }
    }
}
