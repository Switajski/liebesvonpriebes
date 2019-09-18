<?php

/**
 * Jtlsearchexportqueue Class
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
    private $oDB;
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
     * @var integer
     */
    protected $kExportqueue;
    /**
     * @access protected
     * @var integer
     */
    protected $nLimitN;
    /**
     * @access protected
     * @var integer
     */
    protected $nLimitM;
    /**
     * @access protected
     * @var integer
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
     * Constructor
     *
     * @param int kExportqueue primarykey
     * @access public
     */
    public function __construct(JTLSearchDB $oDB, IDebugger $oDebugger, $nExportMethod)
    {
        $this->oDB = $oDB;
        $this->oDebugger = $oDebugger;
        if (is_numeric($nExportMethod) && $nExportMethod > 0) {
            $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; ExportQueue mit $nExportMethod = '.$nExportMethod.' im Konstruktor laden');
            $this->loadFromDB($nExportMethod);
        } else {
            $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; $nExportMethod muss eine Zahl sein und größer gleich 1 ($nExportMethod = '.$nExportMethod.')');
            die('Es ist ein Fehler passiet. Für weitere Infos Debugging aktivieren');
        }
    }

    /**
     * Loads database member into class member
     * @param int kExportqueue primarykey
     * @access private
     */
    private function loadFromDB($nExportMethod)
    {
        $oObj = $this->oDB->getAsObject("SELECT *
                                              FROM tjtlsearchexportqueue
                                              WHERE nExportMethod = " . intval($nExportMethod) . " AND bFinished = 0 AND bLocked = 0", 1);

        if (isset($oObj->kExportqueue) && $oObj->kExportqueue > 0) {
            $cMember_arr = array_keys(get_object_vars($oObj));
            foreach ($cMember_arr as $cMember) {
                $this->$cMember = $oObj->$cMember;
            }

            $this->bLocked = 1;
            $this->update();
            $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; JTLSearchExportQueue mit der ID '.$this->kExportqueue.' wurde geladen (nLimitn = '.$this->nLimitN.', nLimitM = '.$this->nLimitM.', nExportMethod = '.$this->nExportMethod.')');
        } else {
            $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Keine JTLSearchExportQueue fuer $nExportMethod = '.$nExportMethod.' vorhanden.');
        }
    }

    public function isExportFinished()
    {
        if ($this->getSumCount() > $this->nLimitN) {
            return false;
        } else {
            return true;
        }
    }

    public function setCount($cKey, $nValue)
    {
        if (is_string($cKey) && isset($this->nCount_arr[$cKey]) && is_int($nValue)) {
            $this->nCount_arr[$cKey] = $nValue;
        } else {
            if (!isset($this->nCount_arr[$cKey])) {
                $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Es gibt kein $nCount_arr['.$cKey.'].');
            } else {
                $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Parameter falsch ($cKey = '.$cKey.', $nValue = '.$nValue.').');
            }
        }
    }

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

    private function loadExportObjects()
    {
        if ($this->nExportMethod == 3) {
            $oExport_arr = $this->oDB->getAsObject('SELECT kId, eDocumentType FROM tjtlsearchdeltaexport WHERE bDelete = 0 LIMIT 0, '.JTLSEARCH_LIMIT_N_METHOD_3, 2);
            
            if (is_array($oExport_arr) && count($oExport_arr) > 0) {
                foreach ($oExport_arr as $oExport) {
                    $this->xExportObject_arr[] = array($oExport->eDocumentType, $oExport->kId);
                }
            }
        } else {
            $nTmpN = $this->nLimitN;
            $nLeftover = $this->nLimitM;
            $nPrevious = 0;
            /*echo '<hr />RunNr: '.$this->nLimitN/$this->nLimitM.'<br />';
            echo 'n: '.$this->nLimitN.'<br />';
            echo 'm: '.$this->nLimitM.'<br />';
            echo '<br /><br />';*/

            foreach ($this->nCount_arr as $cKey => $nCount) {
                if ($this->nLimitN > ($nPrevious + $nCount)) { // von diesem Typ wurden schon alle Items exportiert
                    $nExported = $nCount;
                    $nExportRun = 0;
                    $nPrevious += $nCount;
                } else { //von diesem Typ sind noch Items zum Exportieren übrig

                    if ($nCount < $nLeftover) { //alle verbleibenden Items von diesem Typ in die Queue packen
                        $nExported = $nTmpN-$nPrevious;
                        $nExportRun = $nCount-$nExported;

                        $nLeftover -= $nExportRun;
                    } else { //ein Teil der Items in die Queue packen
                        $nExported = $nTmpN-$nPrevious;

                        //Anzahl verbleibender Items für diesen Typ und Lauf berechnen
                        if ($nCount-$nExported < $nLeftover) { // Anzahl verbleibender Items ist kleiner als nLeftover
                            $nExportRun = $nCount - $nExported;
                        } else { //Sonst
                            $nExportRun = $nLeftover;
                        }
                        $nLeftover -= $nExportRun;
                    }
                    $nPrevious += $nExportRun+$nExported;
                    $nTmpN += $nExportRun;
                }
                if ($nExportRun > 0) {
                    $cClass = $cKey.'Data';
                    // $oRes_arr = $cClass::getItemKeys($this->oDB, $nExported, $nExportRun);
                    $oRes_arr = call_user_func(array($cClass, 'getItemKeys'), $this->oDB, $nExported, $nExportRun);
                    foreach ($oRes_arr as $oRes) {
                        $this->xExportObject_arr[] = array($cKey, $oRes->kItem);
                    }
                } else {
                    $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; 0 ExportItems für diesen Run');
                }
                /*echo 'cKey: '.$cKey.'<br />';
                echo 'nExportRun: '.$nExportRun.'<br />';
                echo 'nExported: '.$nExported.'<br />';
                echo 'nCount: '.$nCount.'<br />';
                echo 'nPrevious: '.$nPrevious.'<br />';
                echo '<br />';*/
            }
        }
    }

    public function getNextExportObject()
    {
        if ($this->xExportObject_arr === null) {
            $this->loadExportObjects();
            $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; In diesem Run zu exportierende Items laden.');
        }
        
        if (count($this->xExportObject_arr) > 0) {
            foreach ($this->xExportObject_arr as $nKey => $xExportObject) {
                $xResult = $xExportObject;
                unset($this->xExportObject_arr[$nKey]);
                $this->nLimitN++;
                break;
            }
        } else {
            $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Keine zu exportierenden Items vorhanden.');
            return null;
        }
        $this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Next Item: '.  print_r($xResult, true));
        return $xResult;
    }

    public static function generateNew($oDB, $nExportMethod)
    {
        $oQueue = $oDB->getAsObject("SELECT COUNT(*) AS nCount FROM tjtlsearchexportqueue WHERE nExportMethod = " . intval($nExportMethod) . " AND bFinished = 0", 1);
        if (isset($oQueue) && $oQueue->nCount > 0) {
            //$this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Konnte keine neue Queue erstellen');
            return false;
        } else {
            $oObj = new stdClass();
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
            $oObj->bFinished = 0;
            $oObj->bLocked = 0;
            $oObj->dStartTime = date('Y-m-d H:i:s');
            $oObj->dLastRun = 0;
            $oObj->nExportMethod = intval($nExportMethod);
            if ($oDB->insertRow($oObj, "tjtlsearchexportqueue") > 0) {
                return true;
            } else {
                //$this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; Fehler beim anlegen einer neuen jtlsearchexportqueue');
                //$this->oDebugger->doDebug(__FILE__.':'.__CLASS__.'->'.__METHOD__.'; '.  print_r($oObj, true));
                die('Es ist ein Fehler passiet. Für weitere Infos Debugging aktivieren');
            }
        }
    }

    /**
     * Store the class in the database
     * @return boolean|integer
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
                    if ($cMember !== "oDB") {
                        $oObj->$cMember = $this->$cMember;
                    }
                }
            }
            unset($oObj->kExportqueue);
            $this->kExportqueue = $this->oDB->insertRow($oObj, "tjtlsearchexportqueue");
        }
    }

    /**
     * Update the class in the database
     * @return integer
     * @access public
     */
    private function update()
    {
        $res = $this->oDB->execSQL("UPDATE tjtlsearchexportqueue
                                               SET kExportqueue = " . $this->kExportqueue . ",
                                                   nLimitN = " . $this->nLimitN . ",
                                                   nLimitM = " . $this->nLimitM . ",
                                                   nExportMethod = " . $this->nExportMethod . ",
                                                   bFinished = " . $this->bFinished . ",
                                                   dStartTime = '" . $this->dStartTime . "',
                                                   dLastRun = '" . $this->dLastRun . "'
                                               WHERE kExportqueue = " . $this->kExportqueue);
        
        return $this->oDB->DB()->affected_rows;
    }

    /**
     * Delete the class in the database
     * @return integer
     * @access public
     */
    public function delete()
    {
        $res = $this->oDB->execSQL("DELETE FROM tjtlsearchexportqueue
                                               WHERE kExportqueue = " . $this->kExportqueue, 3);

        return $this->oDB->DB()->affected_rows;
    }

    /**
     * Sets the kExportqueue
     * @access public
     * @var integer
     */
    public function setExportqueue($kExportqueue)
    {
        $this->kExportqueue = intval($kExportqueue);

        return $this;
    }

    /**
     * Sets the nLimitN
     * @access public
     * @var integer
     */
    public function setLimitN($nLimitN)
    {
        $this->nLimitN = intval($nLimitN);

        return $this;
    }

    /**
     * Sets the nLimitM
     * @access public
     * @var integer
     */
    public function setLimitM($nLimitM)
    {
        $this->nLimitM = intval($nLimitM);

        return $this;
    }

    /**
     * Sets the nExportMethod
     * @access public
     * @var integer
     */
    public function setExportMethod($nExportMethod)
    {
        $this->nExportMethod = intval($nExportMethod);

        return $this;
    }

    /**
     * Sets the bFinished
     * @access public
     * @var
     */
    public function setFinished($bFinished)
    {
        $this->bFinished = true;
    }

    /**
     * Sets the dStartTime
     * @access public
     * @var string
     */
    public function setStartTime($dStartTime)
    {
        $this->dStartTime = $this->oDB->escape($dStartTime);
    }

    /**
     * Sets the dLastRun
     * @access public
     * @var string
     */
    public function setLastRun($dLastRun)
    {
        $this->dLastRun = $this->oDB->escape($dLastRun);
    }

    /**
     * Gets the kExportqueue
     * @access public
     * @return integer
     */
    public function getExportqueue()
    {
        return $this->kExportqueue;
    }

    /**
     * Gets the nLimitN
     * @access public
     * @return integer
     */
    public function getLimitN()
    {
        return $this->nLimitN;
    }

    /**
     * Gets the nLimitM
     * @access public
     * @return integer
     */
    public function getLimitM()
    {
        return $this->nLimitM;
    }

    /**
     * Gets the nExportMethod
     * @access public
     * @return integer
     */
    public function getExportMethod()
    {
        return $this->nExportMethod;
    }

    /**
     * Gets the bFinished
     * @access public
     * @return
     */
    public function getFinished()
    {
        return $this->bFinished;
    }

    /**
     * Gets the dStartTime
     * @access public
     * @return string
     */
    public function getStartTime()
    {
        return $this->dStartTime;
    }

    /**
     * Gets the dLastRun
     * @access public
     * @return string
     */
    public function getLastRun()
    {
        return $this->dLastRun;
    }

    public function getFileName($bAllArr = false, $cFullPath = null)
    {
        if ($cFullPath !== null && is_string($cFullPath) && strlen($cFullPath) > 0) {
            $cPath = $cFullPath.'tmpSearchExport'.$this->nExportMethod.'/';
        } else {
            $cPath = '';
        }
        $nFile = intval($this->nLimitN/JTLSEARCH_FILE_LIMIT);
        if ($bAllArr === true) {
            $cRes_arr = array();
            for ($i = 0; $i <= $nFile; $i++) {
                $cRes_arr[] = $cPath.JTLSEARCH_FILE_NAME.$i.JTLSEARCH_FILE_NAME_SUFFIX;
            }
            return $cRes_arr;
        } else {
            return $cPath.JTLSEARCH_FILE_NAME.$nFile.JTLSEARCH_FILE_NAME_SUFFIX;
        }
    }
}
