<?php

require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_CLASSES . 'class.JTLSEARCH_Verwaltung_Base.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_MODULES . '/export/includes/defines_inc.php';
require_once JTLSEARCH_PFAD_CLASSES . 'class.Communication.php';
require_once JTLSEARCH_PFAD_CLASSES . 'class.SecurityIntern.php';

/**
 * Description of class
 *
 * @author andre
 */
class JTLSEARCH_Verwaltung_export extends JTLSEARCH_Verwaltung_Base
{
    /**
     * @param IDebugger $oDebugger
     */
    public function __construct(IDebugger $oDebugger)
    {
        $this->oDebugger = $oDebugger;
    }

    /**
     * @param bool $bForce
     * @return $this
     */
    public function generateContent($bForce = false)
    {
        if ($this->getIssetContent() === false || $bForce === true) {
            $this->setIssetContent(true)
                 ->setSort(2)
                 ->setContentTemplate('templates/verwaltung_export.tpl')
                 ->setCssFile('templates/css/export.css')
                 ->setName('Export')
                 ->setContentVar('oResultImportStatus', $this->getImportStatus())
                 ->setContentVar('oResultImportHistory', $this->getImportHistory());
        }

        return $this;
    }

    /**
     * @param $nExportMethod
     */
    public function newQueue($nExportMethod)
    {
        require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.JTLSearchExportQueue.php';
        $this->oDebugger->doDebug(__FILE__ . ': Erstellen einer neuen JTLSearchJobqueue ($nExportMethod: ' . $nExportMethod . ').');
        if (isset($nExportMethod) && is_int($nExportMethod) && $nExportMethod > 0) {
            if (is_writable(JTLSEARCH_PFAD_EXPORTFILE_DIR)) {
                if (is_dir(JTLSEARCH_PFAD_EXPORTFILE_DIR . '/tmpSearchExport' . $nExportMethod)) {
                    if (is_writable(JTLSEARCH_PFAD_EXPORTFILE_DIR . '/tmpSearchExport' . $nExportMethod)) {
                        $this->rrmdir(JTLSEARCH_PFAD_EXPORTFILE_DIR . '/tmpSearchExport' . $nExportMethod);
                    } else {
                        $this->oDebugger->doDebug(__FILE__ . ': ' . JTLSEARCH_PFAD_EXPORTFILE_DIR . '/tmpSearchExport' . $nExportMethod . ' hat keine Schreibrechte.', JTLSEARCH_DEBUGGING_LEVEL_ERROR);
                        die(JTLSEARCH_PFAD_EXPORTFILE_DIR . '/tmpSearchExport' . $nExportMethod . ' hat keine Schreibrechte.');
                    }
                }
                mkdir(JTLSEARCH_PFAD_EXPORTFILE_DIR . '/tmpSearchExport' . $nExportMethod);
            } else {
                $this->oDebugger->doDebug(__FILE__ . ': ' . JTLSEARCH_PFAD_EXPORTFILE_DIR . ' hat keine Schreibrechte.', JTLSEARCH_DEBUGGING_LEVEL_ERROR);
                die(JTLSEARCH_PFAD_EXPORTFILE_DIR . ' hat keine Schreibrechte.');
            }

            try {
                if (JTLSearchExportQueue::generateNew($nExportMethod)) {
                    $this->oDebugger->doDebug(__FILE__ . ': Erstellen einer neuen JTLSearchJobqueue erfolgreich beendet.', JTLSEARCH_DEBUGGING_LEVEL_NOTICE);
                }
            } catch (Exception $oEx) {
                $this->oDebugger->doDebug(__FILE__ . ': Fehler beim Erstellen einer neuen JTLSearchJobqueue ($oEx: ' . $oEx->getMessage() . ').', JTLSEARCH_DEBUGGING_LEVEL_ERROR);
                die();
            }
        } else {
            $this->oDebugger->doDebug(__FILE__ . ': $nExportMethod muss eine Zahl größer als 0 sein ($nExportMethod = ' . $nExportMethod . ')', JTLSEARCH_DEBUGGING_LEVEL_ERROR);
        }

        //$nExportMethod 2 bedeutet, dass es über ein Ajax-Request aufgerufen wurde und 1 als Antwort erwartet
        if ($nExportMethod == 2) {
            echo 1;
        }
    }

    /**
     * @param $nExportMethod
     * @return mixed
     */
    public function doExport($nExportMethod)
    {
        //PCLZIP
        if (!class_exists('PclZip')) {
            require_once PFAD_ROOT . PFAD_PCLZIP . 'pclzip.lib.php';
            $this->oDebugger->doDebug(__FILE__ . ': PclZip-Klasse wurde eingebunden.', JTLSEARCH_DEBUGGING_LEVEL_NOTICE);
        }
        if (intval($nExportMethod) === 3) {
            $oPclZip = new PclZip(JTLSEARCH_PFAD_DELTA_EXPORTFILE_ZIP);
            $this->oDebugger->doDebug(__FILE__ . ': Exportmethode: ' . $nExportMethod . ' Exportfile: ' . JTLSEARCH_PFAD_DELTA_EXPORTFILE_ZIP . '.', JTLSEARCH_DEBUGGING_LEVEL_NOTICE);
        } else {
            $oPclZip = new PclZip(JTLSEARCH_PFAD_EXPORTFILE_ZIP);
            $this->oDebugger->doDebug(__FILE__ . ': Exportmethode: ' . $nExportMethod . ' Exportfile: ' . JTLSEARCH_PFAD_EXPORTFILE_ZIP . '.', JTLSEARCH_DEBUGGING_LEVEL_NOTICE);
        }

        require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.JTLShopExport.php';
        if (isset($nExportMethod) && is_integer($nExportMethod)) {
            $this->oDebugger->doDebug(__FILE__ . ': Erstellen eines Export-Objektes mit nExportMethod = ' . $nExportMethod . '.', JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
            $oExport = new JTLShopExport($oPclZip, $this->oDebugger, $nExportMethod);
        } else {
            $this->oDebugger->doDebug(__FILE__ . ': Erstellen eines Export-Objektes.', JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
            $oExport = new JTLShopExport($oPclZip, $this->oDebugger);
        }
        $this->oDebugger->doDebug(__FILE__ . ': Exportpfad setzen ' . JTLSEARCH_PFAD_EXPORTFILE_DIR . '.', JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
        $oExport->setExportPath(JTLSEARCH_PFAD_EXPORTFILE_DIR);
        $oResult = $oExport->exportAll();
        $this->oDebugger->doDebug(__FILE__ . ': Export-Durchgang beenden ($oResult = ' . print_r($oResult, true) . ').', JTLSEARCH_DEBUGGING_LEVEL_NOTICE);

        return $oResult;
    }

    /**
     * @return bool|mixed
     */
    private function getImportHistory()
    {
        return $this->getImportData('getimporthistory');
    }

    /**
     * @return bool|mixed
     */
    private function getImportError()
    {
        return $this->getImportData('getimporterror');
    }

    /**
     * @return bool|mixed
     */
    private function getImportStatus()
    {
        return $this->getImportData('getimportstatus');
    }

    /**
     * @param $cAction
     * @return bool|mixed
     */
    private function getImportData($cAction)
    {
        $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . "; {$cAction} vom Search Server holen.");
        $oServerSettings = self::getServerSettings();
        ;
        // Security Objekt erstellen und Parameter zum senden der Daten setzen
        $oSecurity = new SecurityIntern();

        $xData_arr['a']   = $cAction;
        $xData_arr['pid'] = $oServerSettings->cProjectId;

        $oSecurity->setParam_arr(array($xData_arr['a'], $xData_arr['pid']));
        $xData_arr['p'] = $oSecurity->createKey();

        $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; $xData_arr = ' . print_r($xData_arr, true));

        try {
            $cReturn = Communication::postData(urldecode($oServerSettings->cServerUrl) . 'servermanager/index.php', $xData_arr);
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; Antwort von Suchserver: ' . $cReturn);

            return json_decode($cReturn);
        } catch (Exception $oEx) {
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; Fehler beider Kommunikation mit dem Server.');
        }

        return false;
    }

    /**
     * @param $dir
     */
    private function rrmdir($dir)
    {
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                $this->rrmdir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dir);
    }
}
