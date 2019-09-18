<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.JTLSearchExportQueue.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.CategoryData.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.ManufacturerData.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.ProductData.php';

/**
 * Class Export
 *
 * @author Andre Vermeulen
 */
abstract class Export
{
    /**
     * @var PclZip
     */
    protected $oArchive;

    /**
     * @var IDebugger
     */
    protected $oDebugger;

    /**
     * @var
     */
    protected $oDataObject_arr;

    /**
     * @var JTLSearchExportQueue
     */
    protected $oJTLSearchExportQueue;

    /**
     * @var array
     */
    protected $nCount_arr = [];

    /**
     * @var string
     */
    protected $cExportPath = '';

    /**
     * @param PclZip    $oArchive
     * @param IDebugger $oDebugger
     * @param int       $nExportMethod
     */
    public function __construct(PclZip $oArchive, IDebugger $oDebugger, $nExportMethod = 1)
    {
        try {
            $this->oArchive  = $oArchive;
            $this->oDebugger = $oDebugger;

            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ .
                '; JTLSearchExportQueue- ($nExportMethod = ' . $nExportMethod . ') und ItemData-Objekte Erstellen.');
            $this->oJTLSearchExportQueue           = new JTLSearchExportQueue($oDebugger, $nExportMethod);
            $this->oDataObject_arr['category']     = new CategoryData($oDebugger);
            $this->oDataObject_arr['manufacturer'] = new ManufacturerData($oDebugger);
            $this->oDataObject_arr['product']      = new ProductData($oDebugger);
            $this->loadCountExportDataIntoQueue();
        } catch (Exception $oEx) {
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; ' . $oEx);
            die('');
        }
    }

    /**
     * @param $cPath
     * @return mixed
     */
    abstract public function setExportPath($cPath);

    /**
     * @return $this
     */
    private function loadCountExportDataIntoQueue()
    {
        if ($this->oJTLSearchExportQueue->getExportMethod() == 3) {
            $oCountExport_arr = Shop::DB()->query("
                SELECT count(*) AS nCount, eDocumentType 
                    FROM tjtlsearchdeltaexport 
                    WHERE bDelete = 0 
                    GROUP BY eDocumentType", 2
            );
            foreach ($oCountExport_arr as $oCountExport) {
                $this->oJTLSearchExportQueue->setCount($oCountExport->eDocumentType, $oCountExport->nCount);
                $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; Delta-Export ' .
                    $oCountExport->eDocumentType . ' = ' . $oCountExport->nCount);
            }
        } else {
            foreach ($this->oDataObject_arr as $cKey => $oDataObject) {
                $nCount = $oDataObject->getCount();
                $this->oJTLSearchExportQueue->setCount($cKey, $nCount);
                $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; ' .
                    $cKey . '->getCount() = ' . $nCount);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function exportAll()
    {
        while (($xExport_arr = $this->oJTLSearchExportQueue->getNextExportObject()) !== null) {
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; ' .
                $xExport_arr[0] . '->loadFromDB(' . $xExport_arr[1] . ')');

            $this->oDataObject_arr[$xExport_arr[0]]->loadFromDB($xExport_arr[1]);
            $oExportItem = $this->oDataObject_arr[$xExport_arr[0]]->getFilledObject();
            if (is_object($oExportItem)) {
                $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ .
                    '; Erstelltes Item vom Type ' . $xExport_arr[0] . ': ' . $oExportItem . '.');
                file_put_contents($this->getFileName(false, $this->cExportPath), $oExportItem, FILE_APPEND);
            } else {
                if ($this->oJTLSearchExportQueue->getExportMethod() == 3) {
                    $oObj                = new stdClass();
                    $oObj->kId           = $xExport_arr[1];
                    $oObj->eDocumentType = $xExport_arr[0];
                    $oObj->bDelete       = 1;
                    $oObj->dLastModified = 'now()';

                    $upd                = new stdClass();
                    $upd->bDelete       = 1;
                    $upd->dLastModified = 'now()';

                    $keys = Shop::DB()->update(
                        'tjtlsearchdeltaexport',
                        ['kId', 'eDocumentType'],
                        [$oObj->kId, $oObj->eDocumentType],
                        $upd
                    );
                    if (!$keys) {
                        Shop::DB()->insert('tjtlsearchdeltaexport', $oObj);
                    }
                    $this->oDebugger->doDebug(__FILE__ . ":" . __CLASS__ . "->" . __METHOD__ .
                        "; Dokument vom Type {$xExport_arr[0]} mit ID: {$xExport_arr[1]} zum Löschen vorgemerkt.");
                } else {
                    $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ .
                        '; Erstelltes Item vom Type ' . $xExport_arr[0] . ' fehlgeschlagen: ' . $oExportItem . '.');
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
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; Keine gültige Exportqueue.');
        }
    }

    /**
     * @return mixed
     */
    abstract protected function nextRun();

    /**
     * @return mixed
     */
    abstract protected function lastRun();

    /**
     * @return bool
     */
    protected function zipFile()
    {
        $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; FileNames: ' .
            print_r($this->getFileName(true, $this->cExportPath), true));
        if (!$this->oArchive->create($this->getFileName(true, $this->cExportPath), PCLZIP_OPT_REMOVE_ALL_PATH)) {
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ .
                '; Es ist ein Fehler beim Zippen der Datei aufgetreten!');

            return false;
        }
        foreach ($this->getFileName(true, $this->cExportPath) as $cFileName) {
            if (file_exists($cFileName)) {
                unlink($cFileName);
            }
        }
        if (is_dir(JTLSEARCH_PFAD_EXPORTFILE_DIR . 'tmpSearchExport' . $this->oJTLSearchExportQueue->getExportMethod())) {
            rmdir(JTLSEARCH_PFAD_EXPORTFILE_DIR . 'tmpSearchExport' . $this->oJTLSearchExportQueue->getExportMethod());
        }

        return true;
    }

    /**
     * @param bool $bAllArr
     * @param null $cPath
     * @return array|string
     */
    public function getFileName($bAllArr = false, $cPath = null)
    {
        return $this->oJTLSearchExportQueue->getFileName($bAllArr, $cPath);
    }
}
