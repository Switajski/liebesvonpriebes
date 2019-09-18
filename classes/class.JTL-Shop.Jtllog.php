<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Jtllog
 */
class Jtllog
{
    /**
     * @access protected
     * @var int
     */
    protected $kLog;

    /**
     * @access protected
     * @var int
     */
    protected $nLevel;

    /**
     * @access protected
     * @var string
     */
    protected $cLog;

    /**
     * @access protected
     * @var string
     */
    protected $cKey;

    /**
     * @access protected
     * @var int
     */
    protected $kKey;

    /**
     * @access protected
     * @var string
     */
    protected $dErstellt;

    /**
     * Constructor
     *
     * @param int $kLog primarykey
     * @access public
     */
    public function __construct($kLog = 0)
    {
        if ((int)$kLog > 0) {
            $this->loadFromDB($kLog);
        }
    }

    /**
     * Loads database member into class member
     *
     * @param int $kLog
     * @return $this
     * @access private
     */
    private function loadFromDB($kLog)
    {
        $oObj = Shop::DB()->select('tjtllog', 'kLog', (int)$kLog);
        if (isset($oObj->kLog) && $oObj->kLog > 0) {
            foreach (get_object_vars($oObj) as $k => $v) {
                $this->$k = $v;
            }
        }

        return $this;
    }

    /**
     * Store the class in the database
     *
     * @param bool $bPrim - Controls the return of the method
     * @return bool|int
     * @access public
     */
    public function save($bPrim = true)
    {
        $oObj        = new stdClass();
        $cMember_arr = array_keys(get_object_vars($this));
        if (is_array($cMember_arr) && count($cMember_arr) > 0) {
            foreach ($cMember_arr as $cMember) {
                $oObj->$cMember = $this->$cMember;
            }
        }

        unset($oObj->kLog);
        $this->setErstellt(date('Y-m-d H:i:s'));

        $kPrim = Shop::DB()->insert('tjtllog', $oObj);
        if ($kPrim > 0) {
            return $bPrim ? $kPrim : true;
        }

        return false;
    }

    /**
     * Update the class in the database
     *
     * @return int
     * @access public
     */
    public function update()
    {
        $_upd            = new stdClass();
        $_upd->nLevel    = (int)$this->nLevel;
        $_upd->cLog      = $this->cLog;
        $_upd->cKey      = $this->cKey;
        $_upd->kKey      = (int)$this->kKey;
        $_upd->dErstellt = $this->dErstellt;

        return Shop::DB()->update('tjtllog', 'kLog', (int)$this->kLog, $_upd);
    }

    /**
     * @param string $cLog
     * @param int    $nLevel
     * @param bool   $bForce
     * @param string $cKey
     * @param string $kKey
     * @param bool   $bPrim
     * @return bool|int
     */
    public function write($cLog, $nLevel = JTLLOG_LEVEL_ERROR, $bForce = false, $cKey = '', $kKey = '', $bPrim = true)
    {
        return self::writeLog($cLog, $nLevel, $bForce, $cKey, $kKey, $bPrim);
    }

    /**
     * @param int $nLevel
     * @return int
     */
    public static function doLog($nLevel = JTLLOG_LEVEL_ERROR)
    {
        $nSystemlogFlag = 0;
        if (isset($GLOBALS['nSystemlogFlag']) && (int)$GLOBALS['nSystemlogFlag'] > 0) {
            $nSystemlogFlag = $GLOBALS['nSystemlogFlag'];
        }
        if ($nSystemlogFlag === 0) {
            $nSystemlogFlag = getSytemlogFlag();
        }

        return self::isBitFlagSet($nSystemlogFlag, $nLevel) > 0;
    }

    /**
     * Write a Log into the database
     *
     * @access public
     * @param string $cLog
     * @param int    $nLevel
     * @param bool   $bForce
     * @param string $cKey
     * @param string $kKey
     * @param bool   $bPrim
     * @return bool|int
     */
    public static function writeLog(
        $cLog,
        $nLevel = JTLLOG_LEVEL_ERROR,
        $bForce = false,
        $cKey = '',
        $kKey = '',
        $bPrim = true
    ) {
        if ($bForce || self::doLog($nLevel)) {
            if (strlen($cLog) > 0) {
                $oLog = new self();
                $oLog->setcLog($cLog)
                     ->setLevel($nLevel)
                     ->setcKey($cKey)
                     ->setkKey($kKey)
                     ->setErstellt('now()');

                return $oLog->save($bPrim);
            }
        }

        return false;
    }

    /**
     * Get Logs from the database
     *
     * @access public
     * @param string $cFilter
     * @param int    $nLevel
     * @param int    $nLimitN
     * @param int    $nLimitM
     * @return array
     */
    public static function getLog($cFilter = '', $nLevel = 0, $nLimitN = 0, $nLimitM = 1000)
    {
        $oJtllog_arr = [];
        $conditions  = [];
        $values      = ['limitfrom' => $nLimitN, 'limitto' => $nLimitM];
        if (strlen($cFilter) > 0) {
            $conditions[]   = "cLog LIKE :clog";
            $values['clog'] = '%' . $cFilter . '%';
        }
        if ((int)$nLevel > 0) {
            $conditions[]     = "nLevel = :nlevel";
            $values['nlevel'] = (int)$nLevel;
        }
        $cSQLWhere = count($conditions) > 0
            ? ' WHERE ' . implode(' AND ', $conditions)
            : '';
        $oLog_arr  = Shop::DB()->executeQueryPrepared("
            SELECT kLog
                FROM tjtllog
                " . $cSQLWhere . "
                ORDER BY dErstellt DESC, kLog DESC
                LIMIT :limitfrom, :limitto", $values, 2
        );
        if (is_array($oLog_arr) && count($oLog_arr) > 0) {
            foreach ($oLog_arr as $oLog) {
                if (isset($oLog->kLog) && (int)$oLog->kLog > 0) {
                    $oJtllog_arr[] = new self($oLog->kLog);
                }
            }
        }

        return $oJtllog_arr;
    }

    /**
     * Get Logcount from the database
     *
     * @access public
     * @param string $cFilter
     * @param int    $nLevel
     * @return int
     */
    public static function getLogCount($cFilter, $nLevel = 0)
    {
        $cSQLWhere = '';
        if ((int)$nLevel > 0) {
            $cSQLWhere = " WHERE nLevel = " . (int)$nLevel;
        }

        if (strlen($cFilter) > 0 && strlen($cSQLWhere) === 0) {
            $cSQLWhere .= " WHERE cLog LIKE '%" . $cFilter . "%'";
        } elseif (strlen($cFilter) > 0 && strlen($cSQLWhere) > 0) {
            $cSQLWhere .= " AND cLog LIKE '%" . $cFilter . "%'";
        }

        $oLog = Shop::DB()->query("SELECT count(*) AS nAnzahl FROM tjtllog" . $cSQLWhere, 1);

        if (isset($oLog->nAnzahl) && $oLog->nAnzahl > 0) {
            return (int)$oLog->nAnzahl;
        }

        return 0;
    }

    /**
     * Write a Log into the database
     *
     * @return void
     * @access public
     */
    public static function truncateLog()
    {
        Shop::DB()->query("DELETE FROM tjtllog WHERE DATE_ADD(dErstellt, INTERVAL 30 DAY) < now()", 3);
        $oObj = Shop::DB()->query("SELECT count(*) AS nCount FROM tjtllog", 1);

        if (isset($oObj->nCount) && (int)$oObj->nCount > JTLLOG_MAX_LOGSIZE) {
            $nLimit = (int)$oObj->nCount - JTLLOG_MAX_LOGSIZE;
            Shop::DB()->query("DELETE FROM tjtllog ORDER BY dErstellt LIMIT {$nLimit}", 4);
        }
    }

    /**
     * Write a Log into the database
     *
     * @return int
     * @access public
     */
    public static function deleteAll()
    {
        return Shop::DB()->query("TRUNCATE TABLE tjtllog", 3);
    }

    /**
     * Delete the class in the database
     *
     * @return int
     * @access public
     */
    public function delete()
    {
        return Shop::DB()->delete('tjtllog', 'kLog', $this->getkLog());
    }

    /**
     * Sets the kLog
     *
     * @access public
     * @param int $kLog
     * @return $this
     */
    public function setkLog($kLog)
    {
        $this->kLog = (int)$kLog;

        return $this;
    }

    /**
     * Sets the nLevel
     *
     * @access public
     * @param int $nLevel
     * @return $this
     */
    public function setLevel($nLevel)
    {
        $this->nLevel = (int)$nLevel;

        return $this;
    }

    /**
     * Sets the cLog
     *
     * @access public
     * @param string $cLog
     * @param bool   $bFilter
     * @return $this
     */
    public function setcLog($cLog, $bFilter = true)
    {
        $this->cLog = $bFilter ? StringHandler::filterXSS($cLog) : $cLog;

        return $this;
    }

    /**
     * Sets the cKey
     *
     * @param string $cKey
     * @return $this
     */
    public function setcKey($cKey)
    {
        $this->cKey = Shop::DB()->escape($cKey);

        return $this;
    }

    /**
     * Sets the kKey
     *
     * @access public
     * @param int $kKey
     * @return $this
     */
    public function setkKey($kKey)
    {
        $this->kKey = (int)$kKey;

        return $this;
    }

    /**
     * Sets the dErstellt
     *
     * @access public
     * @param string $dErstellt
     * @return $this
     */
    public function setErstellt($dErstellt)
    {
        $this->dErstellt = Shop::DB()->escape($dErstellt);

        return $this;
    }

    /**
     * Sets BitFlag
     *
     * @access public
     * @param array $nFlag_arr
     * @return int
     */
    public static function setBitFlag($nFlag_arr)
    {
        $nVal = 0;

        if (is_array($nFlag_arr) && count($nFlag_arr) > 0) {
            foreach ($nFlag_arr as $nFlag) {
                $nVal |= $nFlag;
            }
        }

        return $nVal;
    }

    /**
     * Gets the kLog
     *
     * @access public
     * @return int
     */
    public function getkLog()
    {
        return (int)$this->kLog;
    }

    /**
     * Gets the nLevel
     *
     * @access public
     * @return int
     */
    public function getLevel()
    {
        return (int)$this->nLevel;
    }

    /**
     * Gets the cLog
     *
     * @access public
     * @return string
     */
    public function getcLog()
    {
        return $this->cLog;
    }

    /**
     * Gets the cKey
     *
     * @access public
     * @return string
     */
    public function getcKey()
    {
        return $this->cKey;
    }

    /**
     * Gets the kKey
     *
     * @access public
     * @return int
     */
    public function getkKey()
    {
        return $this->kKey;
    }

    /**
     * Gets the dErstellt
     *
     * @access public
     * @return string
     */
    public function getErstellt()
    {
        return $this->dErstellt;
    }

    /**
     * Gets the BitFlag
     *
     * @access public
     * @param $nVal
     * @param $nFlag
     * @return int
     */
    public static function isBitFlagSet($nVal, $nFlag)
    {
        return ($nVal & $nFlag);
    }

    /**
     * @param string $string
     * @param int    $level
     * @return bool
     */
    public static function cronLog($string, $level = 1)
    {
        if (defined('VERBOSE_CRONJOBS') && (int)VERBOSE_CRONJOBS >= $level && PHP_SAPI === 'cli') {
            $now = new DateTime();
            echo $now->format('Y-m-d H:i:s') . ' ' . $string . PHP_EOL;

            return true;
        }

        return false;
    }
}
