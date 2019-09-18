<?php

/**
 * Jtlsearchexportqueue Class
 *
 * @access public
 * @author
 * @copyright
 */
class JTLSearchExportQueue
{
    /**
     * @access private
     * @var object
     */
    private $oDebugger;
    /**
     * @access private
     * @var array
     */
    private $nCount_arr = array('category' => 0, 'manufacturer' => 0, 'product' => 0);
    /**
     * @access private
     * @var array
     */
    private $xExportObject_arr = null;
    /**
     * @access private
     * @var array
     */
    private $cSaveMember_arr = array('kExportqueue', 'nLimitN', 'nLimitM', 'nExportMethod', 'bFinished', 'bLocked', 'dStartTime', 'dLastRun');
    /**
     * @access protected
     * @var int
     */
    protected $kExportqueue;
    /**
     * @access protected
     * @var int
     */
    protected $nLimitN;
    /**
     * @access protected
     * @var int
     */
    protected $nLimitM;
    /**
     * @access protected
     * @var int
     */
    protected $nExportMethod;
    /**
     * @access protected
     * @var
     */
    protected $bFinished;
    /**
     * @access protected
     * @var
     */
    protected $bLocked;
    /**
     * @access protected
     * @var string
     */
    protected $dStartTime;
    /**
     * @access protected
     * @var string
     */
    protected $dLastRun;

    /**
     * @param IDebugger   $oDebugger
     * @param             $nExportMethod
     */
    public function __construct(IDebugger $oDebugger, $nExportMethod)
    {
        $this->oDebugger = $oDebugger;
        if (is_numeric($nExportMethod) && $nExportMethod > 0) {
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; ExportQueue mit $nExportMethod = ' . $nExportMethod . ' im Konstruktor laden');
            $this->loadFromDB($nExportMethod);
        } else {
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; $nExportMethod muss eine Zahl sein und größer gleich 1 ($nExportMethod = ' . $nExportMethod . ')');
            die('JTL-Search Fehler beim Datenexport: nExportMethod hat einen invaliden Wert ($nExportMethod). Es wird ein ganzzahliger Wert >= 1 erwartet.');
        }
    }

    /**
     * Loads database member into class member
     *
     * @param int $nExportMethod
     * @return $this
     * @access private
     */
    private function loadFromDB($nExportMethod)
    {
        $oObj = Shop::DB()->query(
            "SELECT *
                                              FROM tjtlsearchexportqueue
                                              WHERE nExportMethod = " . intval($nExportMethod) . " AND bFinished = 0 AND bLocked = 0", 1
        );

        if (isset($oObj->kExportqueue) && $oObj->kExportqueue > 0) {
            $cMember_arr = array_keys(get_object_vars($oObj));
            foreach ($cMember_arr as $cMember) {
                $this->$cMember = $oObj->$cMember;
            }
            $this->bLocked = 1;
            $this->update();
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; JTLSearchExportQueue mit der ID ' . $this->kExportqueue . ' wurde geladen (nLimitn = ' . $this->nLimitN . ', nLimitM = ' . $this->nLimitM . ', nExportMethod = ' . $this->nExportMethod . ')');
        } else {
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; Keine JTLSearchExportQueue fuer $nExportMethod = ' . $nExportMethod . ' vorhanden.');
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isExportFinished()
    {
        if ($this->getSumCount() > $this->nLimitN) {
            return false;
        }

        return true;
    }

    /**
     * @param $cKey
     * @param $nValue
     * @return $this
     */
    public function setCount($cKey, $nValue)
    {
        if (is_string($cKey) && isset($this->nCount_arr[$cKey]) && is_int($nValue)) {
            $this->nCount_arr[$cKey] = $nValue;
        } else {
            if (!isset($this->nCount_arr[$cKey])) {
                $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; Es gibt kein $nCount_arr[' . $cKey . '].');
            } else {
                $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; Parameter falsch ($cKey = ' . $cKey . ', $nValue = ' . $nValue . ').');
            }
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getSumCount()
    {
        $nResult = 0;
        foreach ($this->nCount_arr as $nCount) {
            if (is_int($nCount)) {
                $nResult += $nCount;
            }
        }

        return $nResult;
    }

    /**
     * @return $this
     */
    private function loadExportObjects()
    {
        if ($this->nExportMethod == 3) {
            $oExport_arr = Shop::DB()->query('SELECT kId, eDocumentType FROM tjtlsearchdeltaexport WHERE bDelete = 0 LIMIT 0, ' . JTLSEARCH_LIMIT_N_METHOD_3, 2);
            if (is_array($oExport_arr) && count($oExport_arr) > 0) {
                foreach ($oExport_arr as $oExport) {
                    $this->xExportObject_arr[] = array($oExport->eDocumentType, $oExport->kId);
                }
            }
        } else {
            $nTmpN     = $this->nLimitN;
            $nLeftover = $this->nLimitM;
            $nPrevious = 0;
            /*echo '<hr />RunNr: '.$this->nLimitN/$this->nLimitM.'<br />';
            echo 'n: '.$this->nLimitN.'<br />';
            echo 'm: '.$this->nLimitM.'<br />';
            echo '<br /><br />';*/

            foreach ($this->nCount_arr as $cKey => $nCount) {
                if ($this->nLimitN > ($nPrevious + $nCount)) { // von diesem Typ wurden schon alle Items exportiert
                    $nExported  = $nCount;
                    $nExportRun = 0;
                    $nPrevious += $nCount;
                } else { //von diesem Typ sind noch Items zum Exportieren übrig
                    if ($nCount < $nLeftover) { //alle verbleibenden Items von diesem Typ in die Queue packen
                        $nExported  = $nTmpN - $nPrevious;
                        $nExportRun = $nCount - $nExported;

                        $nLeftover -= $nExportRun;
                    } else { //ein Teil der Items in die Queue packen
                        $nExported = $nTmpN - $nPrevious;
                        //Anzahl verbleibender Items für diesen Typ und Lauf berechnen
                        if ($nCount - $nExported < $nLeftover) { // Anzahl verbleibender Items ist kleiner als nLeftover
                            $nExportRun = $nCount - $nExported;
                        } else { //Sonst
                            $nExportRun = $nLeftover;
                        }
                        $nLeftover -= $nExportRun;
                    }
                    $nPrevious += $nExportRun + $nExported;
                    $nTmpN += $nExportRun;
                }
                if ($nExportRun > 0) {
                    $cClass   = $cKey . 'Data';
                    $oRes_arr = call_user_func(array($cClass, 'getItemKeys'), $nExported, $nExportRun);
                    foreach ($oRes_arr as $oRes) {
                        $this->xExportObject_arr[] = array($cKey, $oRes->kItem);
                    }
                } else {
                    $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; 0 ExportItems für diesen Run');
                }
            }
        }

        return $this;
    }

    /**
     * @return null
     */
    public function getNextExportObject()
    {
        $xResult = null;
        if ($this->xExportObject_arr === null) {
            $this->loadExportObjects();
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; In diesem Run zu exportierende Items laden.');
        }
        if (count($this->xExportObject_arr) > 0) {
            foreach ($this->xExportObject_arr as $nKey => $xExportObject) {
                $xResult = $xExportObject;
                unset($this->xExportObject_arr[$nKey]);
                $this->nLimitN++;
                break;
            }
        } else {
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; Keine zu exportierenden Items vorhanden.');

            return null;
        }
        $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; Next Item: ' . print_r($xResult, true));

        return $xResult;
    }

    /**
     * @param $nExportMethod
     * @return bool
     */
    public static function generateNew($nExportMethod)
    {
        $oQueue = Shop::DB()->query("SELECT COUNT(*) AS nCount FROM tjtlsearchexportqueue WHERE nExportMethod = " . intval($nExportMethod) . " AND bFinished = 0", 1);
        if (isset($oQueue) && $oQueue->nCount > 0) {
            //$this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Konnte keine neue Queue erstellen');
            return false;
        } else {
            $oObj          = new stdClass();
            $oObj->nLimitN = 0;
            switch (intval($nExportMethod)) {
                case 3:
                    $oObj->nLimitM = JTLSEARCH_LIMIT_N_METHOD_3;
                    break;

                case 2:
                    $oObj->nLimitM = JTLSEARCH_LIMIT_N_METHOD_2;
                    break;

                case 1:
                default:
                    $oObj->nLimitM = JTLSEARCH_LIMIT_N_METHOD_1;
                    break;

            }
            $oObj->bFinished     = 0;
            $oObj->bLocked       = 0;
            $oObj->dStartTime    = date('Y-m-d H:i:s');
            $oObj->dLastRun      = '0000-00-00 00:00:00';
            $oObj->nExportMethod = intval($nExportMethod);
            if (Shop::DB()->insert('tjtlsearchexportqueue', $oObj) > 0) {
                return true;
            }
//            $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Fehler beim anlegen einer neuen jtlsearchexportqueue');
//            $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; '.  print_r($oObj, true));
            die('Es ist ein Fehler passiert. Für weitere Infos Debugging aktivieren');
        }
    }

    /**
     * Store the class in the database
     *
     * @return $this
     * @access public
     */
    public function save()
    {
        if (isset($this->kExportqueue) && $this->kExportqueue > 0) {
            $this->bLocked = 0;
            $this->update();
        } else {
            $oObj = new stdClass();
            if (is_array($this->cSaveMember_arr) && count($this->cSaveMember_arr) > 0) {
                foreach ($this->cSaveMember_arr as $cMember) {
                    $oObj->$cMember = $this->$cMember;
                }
            }
            unset($oObj->kExportqueue);
            $this->kExportqueue = Shop::DB()->insert('tjtlsearchexportqueue', $oObj);
        }

        return $this;
    }

    /**
     * Update the class in the database
     *
     * @return int
     * @access public
     */
    private function update()
    {
        $res = Shop::DB()->query(
            "UPDATE tjtlsearchexportqueue
               SET kExportqueue = " . $this->kExportqueue . ",
                   nLimitN = " . $this->nLimitN . ",
                   nLimitM = " . $this->nLimitM . ",
                   nExportMethod = " . $this->nExportMethod . ",
                   bFinished = " . $this->bFinished . ",
                   dStartTime = '" . $this->dStartTime . "',
                   dLastRun = '" . $this->dLastRun . "'
               WHERE kExportqueue = " . $this->kExportqueue, 3
        );

        return $res;
    }

    /**
     * Delete the class in the database
     *
     * @return int
     * @access public
     */
    public function delete()
    {
        return Shop::DB()->query(
            "DELETE FROM tjtlsearchexportqueue
               WHERE kExportqueue = " . $this->kExportqueue, 3
        );
    }

    /**
     * @param $kExportqueue
     * @return $this
     */
    public function setExportqueue($kExportqueue)
    {
        $this->kExportqueue = intval($kExportqueue);

        return $this;
    }

    /**
     * @param $nLimitN
     * @return $this
     */
    public function setLimitN($nLimitN)
    {
        $this->nLimitN = intval($nLimitN);

        return $this;
    }

    /**
     * @param $nLimitM
     * @return $this
     */
    public function setLimitM($nLimitM)
    {
        $this->nLimitM = intval($nLimitM);

        return $this;
    }

    /**
     * @param $nExportMethod
     * @return $this
     */
    public function setExportMethod($nExportMethod)
    {
        $this->nExportMethod = intval($nExportMethod);

        return $this;
    }

    /**
     * @param $bFinished
     * @return $this
     */
    public function setFinished($bFinished)
    {
        $this->bFinished = $bFinished;

        return $this;
    }

    /**
     * @param $dStartTime
     * @return $this
     */
    public function setStartTime($dStartTime)
    {
        $this->dStartTime = $dStartTime;

        return $this;
    }

    /**
     * @param $dLastRun
     * @return $this
     */
    public function setLastRun($dLastRun)
    {
        $this->dLastRun = $dLastRun;

        return $this;
    }

    /**
     * @return int
     */
    public function getExportqueue()
    {
        return $this->kExportqueue;
    }

    /**
     * @return int
     */
    public function getLimitN()
    {
        return $this->nLimitN;
    }

    /**
     * @return int
     */
    public function getLimitM()
    {
        return $this->nLimitM;
    }

    /**
     * @return int
     */
    public function getExportMethod()
    {
        return $this->nExportMethod;
    }

    /**
     * @return mixed
     */
    public function getFinished()
    {
        return $this->bFinished;
    }

    /**
     * @return string
     */
    public function getStartTime()
    {
        return $this->dStartTime;
    }

    /**
     * @return string
     */
    public function getLastRun()
    {
        return $this->dLastRun;
    }

    /**
     * @param bool $bAllArr
     * @param null $cFullPath
     * @return array|string
     */
    public function getFileName($bAllArr = false, $cFullPath = null)
    {
        if ($cFullPath !== null && is_string($cFullPath) && strlen($cFullPath) > 0) {
            $cPath = $cFullPath . 'tmpSearchExport' . $this->nExportMethod . '/';
        } else {
            $cPath = '';
        }
        $nFile = intval($this->nLimitN / JTLSEARCH_FILE_LIMIT);
        if ($bAllArr === true) {
            $cRes_arr = array();
            for ($i = 0; $i <= $nFile; $i++) {
                $cRes_arr[] = $cPath . JTLSEARCH_FILE_NAME . $i . JTLSEARCH_FILE_NAME_SUFFIX;
            }

            return $cRes_arr;
        }

        return $cPath . JTLSEARCH_FILE_NAME . $nFile . JTLSEARCH_FILE_NAME_SUFFIX;
    }
}
