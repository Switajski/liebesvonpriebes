<?php
/**
 * copyright (c) 2006-2011 JTL-Software-GmbH, all rights reserved
 *
 * this file may not be redistributed in whole or significant part
 * and is subject to the JTL-Software-GmbH license.
 *
 * license: http://jtl-software.de/jtlshop3license.html
 */

require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES.'class.JTLSearchExportQueue.php');
require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES.'class.CategoryData.php');
require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES.'class.ManufacturerData.php');
require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES.'class.ProductData.php');

/**
 * Description of Export
 *
 * @author Andre Vermeulen
 */
abstract class Export
{
    protected $oDB;

    protected $oArchive;
    
    protected $oDebugger;

    protected $oDataObject_arr;

    protected $oJTLSearchExportQueue;

    protected $nCount_arr = array();
    
    protected $cExportPath = '';

    public function __construct(JTLSearchDB $oDB, PclZip $oArchive, IDebugger $oDebugger, $nExportMethod = 1)
    {
        try {
            $this->oDB = $oDB;
            $this->oArchive = $oArchive;
            $this->oDebugger =  $oDebugger;

            $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; JTLSearchExportQueue- ($nExportMethod = '.$nExportMethod.') und ItemData-Objekte Erstellen.');
            $this->oJTLSearchExportQueue =              new JTLSearchExportQueue($this->oDB, $oDebugger, $nExportMethod);
            $this->oDataObject_arr['category'] =        new CategoryData($this->oDB, $oDebugger);
            $this->oDataObject_arr['manufacturer'] =    new ManufacturerData($this->oDB, $oDebugger);
            $this->oDataObject_arr['product'] =         new ProductData($this->oDB, $oDebugger);
            $this->loadCountExportDataIntoQueue();
        } catch (Exception $oEx) {
            $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; '.$oEx);
            die('Es ist ein Fehler passiet. Für weitere Infos Debugging aktivieren');
        }
    }
    
    abstract public function setExportPath($cPath);

    private function loadCountExportDataIntoQueue()
    {
        if ($this->oJTLSearchExportQueue->getExportMethod() == 3) {
            $oCountExport_arr = $this->oDB->getAsObject("SELECT count(*) AS nCount, eDocumentType FROM tjtlsearchdeltaexport WHERE bDelete = 0 GROUP BY eDocumentType", 2);
            foreach ($oCountExport_arr as $oCountExport) {
                $this->oJTLSearchExportQueue->setCount($oCountExport->eDocumentType, $oCountExport->nCount);
                $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Delta-Export '.$cKey.' = '.$nCount);
            }
        } else {
            foreach ($this->oDataObject_arr as $cKey => $oDataObject) {
                $nCount = $oDataObject->getCount();
                $this->oJTLSearchExportQueue->setCount($cKey, $nCount);
                $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; '.$cKey.'->getCount() = '.$nCount);
            }
        }
    }

    public function exportAll()
    {
        while (($xExport_arr = $this->oJTLSearchExportQueue->getNextExportObject()) !== null) {
            $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; '.$xExport_arr[0].'->loadFromDB('.$xExport_arr[1].')');
            
            $this->oDataObject_arr[$xExport_arr[0]]->loadFromDB($xExport_arr[1]);
            $oExportItem = $this->oDataObject_arr[$xExport_arr[0]]->getFilledObject();
            if (is_object($oExportItem)) {
                $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Erstelltes Item vom Type '.$xExport_arr[0].': '.$oExportItem.'.');
                file_put_contents($this->getFileName(false, $this->cExportPath), $oExportItem, FILE_APPEND);
            } else {
                if ($this->oJTLSearchExportQueue->getExportMethod() == 3) {
                    $oObj = new stdClass();
                    $oObj->kId = $xExport_arr[1];
                    $oObj->eDocumentType = $xExport_arr[0];
                    $oObj->bDelete = 1;
                    $oObj->dLastModified = 'now()';

                    if ($this->oDB->execSQL('UPDATE tjtlsearchdeltaexport SET bDelete = 1, dLastModified = now() WHERE kId = '.$oObj->kId.' AND eDocumentType = "'.$oObj->eDocumentType.'";') == 0) {
                        $this->oDB->insertRow($oObj, 'tjtlsearchdeltaexport');
                    }
                    $this->oDebugger->doDebug(__FILE__.":".__CLASS__."->".__METHOD__."; Dokument vom Type {$xExport_arr[0]} mit ID: {$xExport_arr[1]} zum Löschen vorgemerkt.");
                } else {
                    $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Erstelltes Item vom Type '.$xExport_arr[0].' fehlgeschlagen: '.$oExportItem.'.');
                }
            }
        }
        if ($this->oJTLSearchExportQueue->getExportqueue()) {
            if (!$this->oJTLSearchExportQueue->isExportFinished()) {
                return $this->nextRun();
            } else {
                return $this->lastRun();
            }
        } else {
            $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Keine Gültige Exportqueue.');
        }
    }
    
    abstract protected function nextRun();
    
    abstract protected function lastRun();

    protected function zipFile()
    {
        $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; FileNames: '.  print_r($this->getFileName(true, $this->cExportPath), true));
        if (!$this->oArchive->create($this->getFileName(true, $this->cExportPath), PCLZIP_OPT_REMOVE_ALL_PATH)) {
            $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Es ist ein Fehler beim Zippen der Datei aufgetreten!');
        } else {
            foreach ($this->getFileName(true, $this->cExportPath) as $cFileName) {
                if (file_exists($cFileName)) {
                    unlink($cFileName);
                }
            }
            if (is_dir(JTLSEARCH_PFAD_EXPORTFILE_DIR.'/tmpSearchExport'.$this->oJTLSearchExportQueue->getExportMethod())) {
                rmdir(JTLSEARCH_PFAD_EXPORTFILE_DIR.'/tmpSearchExport'.$this->oJTLSearchExportQueue->getExportMethod());
            }
        }
    }

    public function getFileName($bAllArr = false, $cPath = null)
    {
        return $this->oJTLSearchExportQueue->getFileName($bAllArr, $cPath);
    }
}
