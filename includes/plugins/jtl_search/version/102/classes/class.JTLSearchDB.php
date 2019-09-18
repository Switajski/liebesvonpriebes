<?php

/**
 * copyright (c) 2006-2011 JTL-Software-GmbH, all rights reserved
 *
 * this file may not be redistributed in whole or significant part
 * and is subject to the JTL-Software-GmbH license.
 *
 * license: http://jtl-software.de/jtlshop3license.html
 */

/**
 * Description of JTLSearchDB
 * Geninjat von Daniel Böhmer
 *
 * @author JTL Software
 */
class JTLSearchDB
{
    private static $Connection = null;
    private static $cDBHost = "";
    private static $cDBUser = "";
    private static $cDBPass = "";
    private static $cDBName = "";
    private static $cDBCharset = "";
    private static $nInstanceCounter = 0;
    private static $nExecuteTime = 0;

    /**
     * Constructor
     *
     * @param string cDBHost, string cDBLogin, string cDBPass, string cDBName
     * @access public
     */
    public function __construct(JTLSearchDBInfo $oDBInfo)
    {
        self::$cDBHost = $oDBInfo->getHost();
        self::$cDBUser = $oDBInfo->getUser();
        self::$cDBPass = $oDBInfo->getPass();
        self::$cDBName = $oDBInfo->getName();
        self::$cDBCharset = $oDBInfo->getCharset();

        $this->Connect();
    }

    /**
     * Opens a connection to a MySQL database server
     * @access private
     */
    private function Connect()
    {
        if (self::$nInstanceCounter <= 0) {
            self::$Connection = @mysql_connect(self::$cDBHost, self::$cDBUser, self::$cDBPass);

            if (!self::$Connection) {
                throw new Exception("ERROR: No database connection! " . mysql_error());
            } elseif (!@mysql_select_db(self::$cDBName, self::$Connection)) {
                throw new Exception("ERROR: Cannot select database " . self::$cDBName . "! " . mysql_error(self::$Connection));
            }

            self::execSQL("SET NAMES '" . self::$cDBCharset . "'");
            self::$nInstanceCounter++;
        }
    }

    /**
     * Sends a unique MySQL query to the current database
     * @param string cSQL, boolean bEcho
     * @access public
     * @return MySQL resource
     */
    public function execSQL($cSQL, $bEcho = false)
    {
        if (strlen($cSQL) > 0) {
            $nTime = microtime(true);
            if ($bEcho) {
                echo $cSQL;
            }

            $xResult = @mysql_query($cSQL, self::$Connection);

            if (!$xResult) {
                throw new Exception("ERROR: MySQL query error! SQL: {$cSQL} " . mysql_error(self::$Connection));
            } else {
                self::$nExecuteTime += microtime(true) - $nTime + 1;

                return $xResult;
            }
        } else {
            throw new Exception("ERROR: Empty SQL query!");
        }
    }

    /**
     * Sends a unique MySQL query to the current database and fetches it as Object (nType = 1) OR as Array of Objects (nType = 2)
     * @param string cSQL, boolean bEcho, integer nType
     * @access public
     * @return MySQL resource
     */
    public function getAsObject($cSQL, $nType = 1, $bEcho = false)
    {
        if (strlen($cSQL) > 0) {
            $xResult = $this->execSQL($cSQL, $bEcho);

            if (mysql_num_rows($xResult) > 0) {
                if ($nType === 1) {
                    $oDBResult = mysql_fetch_object($xResult);
                } elseif ($nType === 2) {
                    $oDBResult = array();

                    while ($oObj = mysql_fetch_object($xResult)) {
                        $oDBResult[] = $oObj;
                    }
                }

                return $oDBResult;
            } else {
                return NULL;
            }
        } else {
            throw new Exception("ERROR: Empty SQL query!");
        }
    }

    /**
     * Insert a current object into the given MySQL database
     * @param object oObject, string cTable
     * @access public
     * @return MySQL resource
     */
    public function insertRow($oObject, $cTable, $bEcho = false)
    {
        $arr=get_object_vars($oObject);
        if (!is_array($arr)) {
            return 0;
        }

        $columns="(";
        $values="(";
        $keys=array_keys($arr);
        for ($i=0;$i<count($keys);$i++) {
            if ($i==count($keys)-1) {
                $columns.=$keys[$i].") values";
                if ($oObject->$keys[$i]==="_DBNULL_") {//nicht sauber, aber nützlich
                    $values.='null'.")";
                } elseif ($oObject->$keys[$i]==="now()") {//nicht sauber, aber nützlich
                    $values.=$oObject->$keys[$i].")";
                } else {
                    $values.="\"".$this->realEscape($oObject->$keys[$i])."\")";
                }
            } else {
                $columns.=$keys[$i].", ";
                if ($oObject->$keys[$i]==="_DBNULL_") {//nicht sauber, aber nützlich
                    $values.='null'.", ";
                } elseif ($oObject->$keys[$i]==="now()") {//nicht sauber, aber nützlich
                    $values.=$oObject->$keys[$i].", ";
                } else {
                    $values.="\"".$this->realEscape($oObject->$keys[$i])."\", ";
                }
            }
        }

        $stmt="insert into $cTable $columns $values";
        if ($bEcho) {
            echo($stmt);
        }

        $res=$this->execSQL($stmt);
        if (!$res) {
            return 0;
        } else {
            $erg1=$this->execSQL('select LAST_INSERT_ID()');
            if ($erg1) {
                if ($res1=mysql_fetch_row($erg1)) {
                    if ($res1[0]>0) {
                        return $res1[0];
                    }
                }
            }
            return 1; // then this is non auto_increment - but insert was successful
        }
    }

    /**
     * safe mysql escape für insertRow. Dort wird jeweils mysql_real_escape aufgerufen
     * @access public
     * @param string $cStr Ausdruck, der escaped für mysql werden soll
     * @return escaped expression
     */
    public function escape($cStr)
    {
        return $this->realEscape($cStr);
    }

    /**
     * real mysql escape mysql escape
     * @access public
     * @param string $cStr Ausdruck, der escaped für mysql werden soll
     * @return escaped expression
     */
    public function realEscape($cStr)
    {
        if (get_magic_quotes_gpc()) {
            return mysql_real_escape_string(stripslashes($cStr), self::$Connection);
        } else {
            return mysql_real_escape_string($cStr, self::$Connection);
        }
    }
    
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

    /**
     * Destructor
     *
     * @access public
     */
    public function __destruct()
    {
        self::$nInstanceCounter--;

        if (self::$nInstanceCounter <= 0) {
            @mysql_close(self::$Connection);
        }
    }
}
