<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Description of JTLSearchDB
 * Geninjat von Daniel Böhmer
 *
 * @author JTL Software
 */
class JTLSearchDB
{
    /**
     * @var mysqli|null
     */
    private $_db = null;

    /**
     * @var bool
     */
    private $_isConnected = false;

    /**
     * @var int
     */
    private $_executeTime = 0;

    /**
     * @param JTLSearchDBInfo $dbInfo
     * @throws Exception
     */
    public function __construct(JTLSearchDBInfo $dbInfo)
    {
        if (strpos($dbInfo->getHost(), '/') === false) {
            $this->_db = @new mysqli($dbInfo->getHost(), $dbInfo->getUser(), $dbInfo->getPass(), $dbInfo->getName());
        } else {
            $this->_db = @new mysqli('', $dbInfo->getUser(), $dbInfo->getPass(), $dbInfo->getName(), null, $dbInfo->getHost());
        }

        if ($this->_db->connect_error) {
            throw new Exception($this->_db->connect_error);
        }

        $this->_db->query("SET NAMES '{$dbInfo->getCharset()}'");
        $this->_isConnected = true;
    }

    /**
     *
     */
    public function __destruct()
    {
        if ($this->_isConnected) {
            $this->close();
        }
    }

    /**
     * @return bool
     */
    public function close()
    {
        return $this->_db->close();
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        return $this->_isConnected;
    }

    /**
     * @return string
     */
    public function getServerInfo()
    {
        return $this->_db->server_info;
    }

    /**
     * @return mysqli|null
     */
    public function DB()
    {
        return $this->_db;
    }

    /**
     * Sends a unique MySQL query to the current database
     *
     * @param      $cSQL
     * @param bool $bEcho
     * @return bool|mysqli_result
     * @throws Exception
     */
    public function execSQL($cSQL, $bEcho = false)
    {
        if (strlen($cSQL) > 0) {
            $nTime = microtime(true);
            if ($bEcho) {
                echo $cSQL;
            }

            $xResult = $this->_db->query($cSQL);

            if (!$xResult) {
                throw new Exception("ERROR: MySQL query error! SQL: {$cSQL} " . $this->_db->error);
            } else {
                $this->_executeTime += microtime(true) - $nTime + 1;

                return $xResult;
            }
        } else {
            throw new Exception("ERROR: Empty SQL query!");
        }
    }

    /**
     * Sends a unique MySQL query to the current database and fetches it as Object (nType = 1) OR as Array of Objects (nType = 2)
     *
     * @param      $cSQL
     * @param int  $nType
     * @param bool $bEcho
     * @return array|null|object|stdClass
     * @throws Exception
     */
    public function getAsObject($cSQL, $nType = 1, $bEcho = false)
    {
        if (strlen($cSQL) > 0) {
            $xResult = $this->execSQL($cSQL, $bEcho);

            if ($xResult->num_rows > 0) {
                if ($nType === 1) {
                    $oDBResult = $xResult->fetch_object();
                } elseif ($nType === 2) {
                    $oDBResult = array();

                    while ($oObj = $xResult->fetch_object()) {
                        $oDBResult[] = $oObj;
                    }
                }

                return $oDBResult;
            } else {
                return null;
            }
        } else {
            throw new Exception("ERROR: Empty SQL query!");
        }
    }

    /**
     * Insert a current object into the given MySQL database
     *
     * @param      $oObject
     * @param      $cTable
     * @param bool $bEcho
     * @return int
     * @throws Exception
     */
    public function insertRow($oObject, $cTable, $bEcho = false)
    {
        $arr = get_object_vars($oObject);
        if (!is_array($arr)) {
            return 0;
        }

        $columns = "(";
        $values  = "(";
        $keys    = array_keys($arr);
        for ($i = 0; $i < count($keys); $i++) {
            if ($i == count($keys) - 1) {
                $columns .= $keys[$i] . ") values";
                if ($oObject->$keys[$i] === "_DBNULL_") {
                    //nicht sauber, aber nützlich

                    $values .= 'null' . ")";
                } elseif ($oObject->$keys[$i] === "now()") {
                    //nicht sauber, aber nützlich

                    $values .= $oObject->$keys[$i] . ")";
                } else {
                    $values .= "\"" . $this->realEscape($oObject->$keys[$i]) . "\")";
                }
            } else {
                $columns .= $keys[$i] . ", ";
                if ($oObject->$keys[$i] === "_DBNULL_") {
                    //nicht sauber, aber nützlich

                    $values .= 'null' . ", ";
                } elseif ($oObject->$keys[$i] === "now()") {
                    //nicht sauber, aber nützlich

                    $values .= $oObject->$keys[$i] . ", ";
                } else {
                    $values .= "\"" . $this->realEscape($oObject->$keys[$i]) . "\", ";
                }
            }
        }

        $stmt = "INSERT INTO $cTable $columns $values";
        if ($bEcho) {
            echo($stmt);
        }

        $res = $this->execSQL($stmt);
        if (!$res) {
            return 0;
        } else {
            $erg1 = $this->execSQL('select LAST_INSERT_ID()');
            if ($erg1) {
                if ($res1 = $erg1->fetch_row()) {
                    if ($res1[0] > 0) {
                        return $res1[0];
                    }
                }
            }

            return 1; // then this is non auto_increment - but insert was successful
        }
    }

    /**
     * safe mysql escape für insertRow. Dort wird jeweils mysql_real_escape aufgerufen
     *
     * @access public
     * @param string $cStr Ausdruck, der escaped für mysql werden soll
     * @return string - escaped expression
     */
    public function escape($cStr)
    {
        return $this->realEscape($cStr);
    }

    /**
     * real mysql escape mysql escape
     *
     * @access public
     * @param string $cStr Ausdruck, der escaped für mysql werden soll
     * @return string - escaped expression
     */
    public function realEscape($cStr)
    {
        return $this->_db->real_escape_string($cStr);
    }

    /**
     * @return bool|stdClass
     * @throws Exception
     */
    public function getServerSettings()
    {
        $oServerSettings_arr = $this->getAsObject('SELECT cKey, cValue FROM tjtlsearchserverdata', 2);

        if (count($oServerSettings_arr) > 0) {
            $oResult = new stdClass();
            foreach ($oServerSettings_arr as $oServerSetting) {
                $oResult->{$oServerSetting->cKey} = $oServerSetting->cValue;
            }

            return $oResult;
        } else {
            return false;
        }
    }
}
