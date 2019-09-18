<?php

require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_CLASSES . 'class.JTLSEARCH_Verwaltung_Base.php');
require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_MODULES . '/export/includes/defines_inc.php');

/**
 * Description of class
 *
 * @author andre
 */
class JTLSEARCH_Verwaltung_export extends JTLSEARCH_Verwaltung_Base
{
    public function __construct(JTLSearchDB $oDB, IDebugger $oDebugger)
    {
        $this->oDB = $oDB;
        $this->oDebugger = $oDebugger;

        $this->setSort(2);
        $this->setContentTemplate('templates/verwaltung_export.tpl');
        $this->setCssFile('templates/css/export.css');
        $this->setName('Export');
        $this->setContentVar('oExportStats_arr', $this->getExportStats());
    }

    public function newQueue($nExportMethod)
    {
        require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.JTLSearchExportQueue.php');
        $this->oDebugger->doDebug(__FILE__ . ': Erstellen einer neuen JTLSearchJobqueue ($nExportMethod: ' . $nExportMethod . ').');
        if (isset($nExportMethod) && is_int($nExportMethod) && $nExportMethod > 0) {
            if (is_writable(JTLSEARCH_PFAD_EXPORTFILE_DIR)) {
                if (is_dir(JTLSEARCH_PFAD_EXPORTFILE_DIR . '/tmpSearchExport' . $nExportMethod)) {
                    if (is_writable(JTLSEARCH_PFAD_EXPORTFILE_DIR . '/tmpSearchExport' . $nExportMethod)) {
                        $this->rrmdir(JTLSEARCH_PFAD_EXPORTFILE_DIR . '/tmpSearchExport' . $nExportMethod);
                    } else {
                        $this->oDebugger->doDebug(__FILE__.': '.JTLSEARCH_PFAD_EXPORTFILE_DIR . '/tmpSearchExport' . $nExportMethod. ' hat keine Schreibrechte.', JTLSEARCH_DEBUGGING_LEVEL_ERROR);
                        die(JTLSEARCH_PFAD_EXPORTFILE_DIR . '/tmpSearchExport' . $nExportMethod. ' hat keine Schreibrechte.');
                    }
                }
                mkdir(JTLSEARCH_PFAD_EXPORTFILE_DIR . '/tmpSearchExport' . $nExportMethod);
            } else {
                $this->oDebugger->doDebug(__FILE__.': '.JTLSEARCH_PFAD_EXPORTFILE_DIR. ' hat keine Schreibrechte.', JTLSEARCH_DEBUGGING_LEVEL_ERROR);
                die(JTLSEARCH_PFAD_EXPORTFILE_DIR. ' hat keine Schreibrechte.');
            }
            
            try {
                if (JTLSearchExportQueue::generateNew($this->oDB, $nExportMethod)) {
                    $this->oDebugger->doDebug(__FILE__ . ': Erstellen einer neuen JTLSearchJobqueue erfolgreich beendet.', JTLSEARCH_DEBUGGING_LEVEL_NOTICE);
                }
            } catch (Exception $oEx) {
                $this->oDebugger->doDebug(__FILE__ . ': Fehler beim erstellen einer neuen JTLSearchJobqueue ($oEx: '.$oEx->getMessage().').', JTLSEARCH_DEBUGGING_LEVEL_ERROR);
                die();
            }
        } else {
            $this->oDebugger->doDebug(__FILE__.': $nExportMethod muss eine Zahl größer als 0 sein ($nExportMethod = '.$nExportMethod.')', JTLSEARCH_DEBUGGING_LEVEL_ERROR);
        }

        //$nExportMethod 2 bedeutet das es über ein Ajax-Request aufgerufen wurde und 1 als Antwort erwartet
        if ($nExportMethod == 2) {
            echo 1;
        }
    }
    
    public function doExport($nExportMethod)
    {
        //PCLZIP
        if (!class_exists('PclZip')) {
            require_once(JTLSEARCH_PFAD_LIBS."pclzip-2-6/pclzip.lib.php");
            $this->oDebugger->doDebug(__FILE__ . ': PclZip-Klasse wurde eingebunden.', JTLSEARCH_DEBUGGING_LEVEL_NOTICE);
        }
        if (intval($nExportMethod) == 3) {
            $oPclZip = new PclZip(JTLSEARCH_PFAD_DELTA_EXPORTFILE_ZIP);
            $this->oDebugger->doDebug(__FILE__ . ': Exportmethode: '.$nExportMethod.' Exportfile: '.JTLSEARCH_PFAD_DELTA_EXPORTFILE_ZIP.'.', JTLSEARCH_DEBUGGING_LEVEL_NOTICE);
        } else {
            $oPclZip = new PclZip(JTLSEARCH_PFAD_EXPORTFILE_ZIP);
            $this->oDebugger->doDebug(__FILE__ . ': Exportmethode: '.$nExportMethod.' Exportfile: '.JTLSEARCH_PFAD_EXPORTFILE_ZIP.'.', JTLSEARCH_DEBUGGING_LEVEL_NOTICE);
        }

        require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES.'class.JTLShopExport.php');
        if (isset($nExportMethod) && is_integer($nExportMethod)) {
            $this->oDebugger->doDebug(__FILE__.': Erstellen eines Export-Objektes mit nExportMethod = '.$nExportMethod.'.', JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
            $oExport = new JTLShopExport($this->oDB, $oPclZip, $this->oDebugger, $nExportMethod);
        } else {
            $this->oDebugger->doDebug(__FILE__.': Erstellen eines Export-Objektes.', JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
            $oExport = new JTLShopExport($this->oDB, $oPclZip, $this->oDebugger);
        }
        $this->oDebugger->doDebug(__FILE__.': Exportpfad setzen '.JTLSEARCH_PFAD_EXPORTFILE_DIR.'.', JTLSEARCH_DEBUGGING_LEVEL_DEBUG);
        $oExport->setExportPath(JTLSEARCH_PFAD_EXPORTFILE_DIR);
        $oResult = $oExport->exportAll();
        $this->oDebugger->doDebug(__FILE__.': Export-Durchgang beenden ($oResult = '.  print_r($oResult, true).').', JTLSEARCH_DEBUGGING_LEVEL_NOTICE);
        return $oResult;
    }
    
    private function getExportStats()
    {
        $oRes['lastFinished']   = $this->oDB->getAsObject("SELECT DATE_FORMAT(dLastRun, '%d.%m.%Y') AS cDatum, DATE_FORMAT(dLastRun, '%H:%i:%s') AS cZeit FROM tjtlsearchexportqueue WHERE bFinished = 1 AND nExportMethod = 1 ORDER BY dLastRun DESC LIMIT 0, 1", 1);
        $oRes['current']        = $this->oDB->getAsObject("SELECT DATE_FORMAT(dLastRun, '%d.%m.%Y') AS cDatum, DATE_FORMAT(dLastRun, '%H:%i:%s') AS cZeit FROM tjtlsearchexportqueue WHERE bFinished = 0 AND nExportMethod = 1 ORDER BY dLastRun DESC LIMIT 0, 1", 1);
        $oTmpRes                = $this->oDB->getAsObject("SELECT UNIX_TIMESTAMP(dLetzterStart) AS dLetzterStart, nAlleXStd, DATE_FORMAT(dStart, '%d.%m.%Y %H:%i') AS dStart FROM tcron WHERE cJobArt = 'JTLSearchExport'", 1);
        
        if (isset($oTmpRes->dLetzterStart) && isset($oTmpRes->nAlleXStd)) {
            $oNextStart = new stdClass();
            if ($oTmpRes->dLetzterStart == 0) {
                $cStart_arr = explode(' ', $oTmpRes->dStart);
                $oNextStart->cDatum     = $cStart_arr[0];
                $oNextStart->cZeit      = $cStart_arr[1];
                $oNextStart->dStart = $oTmpRes->dStart;
                $oRes['nextStart'] = $oNextStart;
            } else {
                $dNextStart = $oTmpRes->dLetzterStart + ($oTmpRes->nAlleXStd*3600);
                $oNextStart->cDatum = date('d.m.Y', $dNextStart);
                $oNextStart->cZeit = date('H:i:s', $dNextStart);
                $oNextStart->dStart = $oTmpRes->dStart;
                $oRes['nextStart'] = $oNextStart;
            }
        }
        return $oRes;
    }
    
    private function rrmdir($dir)
    {
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                rrmdir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dir);
    }
}
