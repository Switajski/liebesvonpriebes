<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Emailhistory
 */
class Emailhistory
{
    /**
     * @var int
     */
    public $kEmailhistory;

    /**
     * @var int
     */
    public $kEmailvorlage;

    /**
     * @var string
     */
    public $cSubject;

    /**
     * @var string
     */
    public $cFromName;

    /**
     * @var string
     */
    public $cFromEmail;

    /**
     * @var string
     */
    public $cToName;

    /**
     * @var string
     */
    public $cToEmail;

    /**
     * @var string - date
     */
    public $dSent;

    /**
     * @param null|int    $kEmailhistory
     * @param null|object $oObj
     */
    public function __construct($kEmailhistory = null, $oObj = null)
    {
        if ((int)$kEmailhistory > 0) {
            $this->loadFromDB($kEmailhistory);
        } elseif ($oObj !== null && is_object($oObj)) {
            $cMember_arr = array_keys(get_object_vars($oObj));
            if (is_array($cMember_arr) && count($cMember_arr) > 0) {
                foreach ($cMember_arr as $cMember) {
                    $cMethod = 'set' . substr($cMember, 1);
                    if (method_exists($this, $cMethod)) {
                        call_user_func([&$this, $cMethod], $oObj->$cMember);
                    }
                }
            }
        }
    }

    /**
     * @param int $kEmailhistory
     * @return $this
     */
    protected function loadFromDB($kEmailhistory)
    {
        $oObj = Shop::DB()->select('temailhistory', 'kEmailhistory', (int)$kEmailhistory);
        if (isset($oObj->kEmailhistory) && $oObj->kEmailhistory > 0) {
            $cMember_arr = array_keys(get_object_vars($oObj));
            foreach ($cMember_arr as $cMember) {
                $this->$cMember = $oObj->$cMember;
            }
        }

        return $this;
    }

    /**
     * @param bool $bPrim
     * @return bool
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
        if (isset($oObj->kEmailhistory) && (int)$oObj->kEmailhistory > 0) {
            return $this->update();
        }
        unset($oObj->kEmailhistory);
        $kPrim = Shop::DB()->insert('temailhistory', $oObj);
        if ($kPrim > 0) {
            return $bPrim ? $kPrim : true;
        }

        return false;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function update()
    {
        $cQuery      = 'UPDATE temailhistory SET ';
        $cSet_arr    = [];
        $cMember_arr = array_keys(get_object_vars($this));
        if (is_array($cMember_arr) && count($cMember_arr) > 0) {
            foreach ($cMember_arr as $cMember) {
                $cMethod = 'get' . substr($cMember, 1);
                if (method_exists($this, $cMethod)) {
                    $mValue = "'" . Shop::DB()->realEscape(call_user_func([&$this, $cMethod])) . "'";
                    if (call_user_func([&$this, $cMethod]) === null) {
                        $mValue = 'NULL';
                    }
                    $cSet_arr[] = "{$cMember} = {$mValue}";
                }
            }
            $cQuery .= implode(', ', $cSet_arr);
            $cQuery .= " WHERE kEmailhistory = {$this->getEmailhistory()}";

            return Shop::DB()->query($cQuery, 3);
        } else {
            throw new Exception('ERROR: Object has no members!');
        }
    }

    /**
     * @return mixed
     */
    public function delete()
    {
        return Shop::DB()->delete('temailhistory', 'kEmailhistory', $this->getEmailhistory());
    }

    /**
     * @param string $cSqlLimit
     * @return array
     */
    public function getAll($cSqlLimit = '')
    {
        if ($cSqlLimit === null) {
            $cSqlLimit = '';
        }
        $oObj_arr = Shop::DB()->query("SELECT * FROM temailhistory ORDER BY dSent DESC" . $cSqlLimit, 2);
        if (is_array($oObj_arr) && count($oObj_arr) > 0) {
            $oEmailhistory_arr = [];
            foreach ($oObj_arr as $oObj) {
                $oEmailhistory_arr[] = new self(null, $oObj);
            }

            return $oEmailhistory_arr;
        }

        return [];
    }

    /**
     * @return int
     */
    public function getCount()
    {
        $oObj = Shop::DB()->query("SELECT count(*) AS nCount FROM temailhistory", 1);

        return (int)$oObj->nCount;
    }

    /**
     * @param array $kEmailhistory_arr
     * @return bool
     */
    public function deletePack(array $kEmailhistory_arr)
    {
        if (count($kEmailhistory_arr) > 0) {
            $kEmailhistory_arr = array_map(function ($i) { return (int)$i; }, $kEmailhistory_arr);

            return Shop::DB()->query("DELETE FROM temailhistory WHERE kEmailhistory IN (" . implode(',', $kEmailhistory_arr) . ")", 3);
        }

        return false;
    }

    /**
     * truncate the email-history-table
     *
     * @param void
     * @return boolean  true=success, false='something went wrong'
     */
    public function deleteAll()
    {
        // log that event!
        Jtllog::writeLog(utf8_decode('eMail-History gelöscht'), JTLLOG_LEVEL_NOTICE, true, 'Emailhistory');
        return !(Shop::DB()->query('TRUNCATE TABLE temailhistory', 3));
    }

    /**
     * @return int
     */
    public function getEmailhistory()
    {
        return (int)$this->kEmailhistory;
    }

    /**
     * @param int $kEmailhistory
     * @return $this
     */
    public function setEmailhistory($kEmailhistory)
    {
        $this->kEmailhistory = (int)$kEmailhistory;

        return $this;
    }

    /**
     * @return int
     */
    public function getEmailvorlage()
    {
        return (int)$this->kEmailvorlage;
    }

    /**
     * @param int $kEmailvorlage
     * @return $this
     */
    public function setEmailvorlage($kEmailvorlage)
    {
        $this->kEmailvorlage = (int)$kEmailvorlage;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->cSubject;
    }

    /**
     * @param string $cSubject
     * @return $this
     */
    public function setSubject($cSubject)
    {
        $this->cSubject = $cSubject;

        return $this;
    }

    /**
     * @return string
     */
    public function getFromName()
    {
        return $this->cFromName;
    }

    /**
     * @param string $cFromName
     * @return $this
     */
    public function setFromName($cFromName)
    {
        $this->cFromName = $cFromName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFromEmail()
    {
        return $this->cFromEmail;
    }

    /**
     * @param string $cFromEmail
     * @return $this
     */
    public function setFromEmail($cFromEmail)
    {
        $this->cFromEmail = $cFromEmail;

        return $this;
    }

    /**
     * @return string
     */
    public function getToName()
    {
        return $this->cToName;
    }

    /**
     * @param string $cToName
     * @return $this
     */
    public function setToName($cToName)
    {
        $this->cToName = $cToName;

        return $this;
    }

    /**
     * @return string
     */
    public function getToEmail()
    {
        return $this->cToEmail;
    }

    /**
     * @param string $cToEmail
     * @return $this
     */
    public function setToEmail($cToEmail)
    {
        $this->cToEmail = $cToEmail;

        return $this;
    }

    /**
     * @return string
     */
    public function getSent()
    {
        return $this->dSent;
    }

    /**
     * @param string $dSent
     * @return $this
     */
    public function setSent($dSent)
    {
        $this->dSent = $dSent;

        return $this;
    }
}
