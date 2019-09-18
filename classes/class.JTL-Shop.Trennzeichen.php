<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Trennzeichen
 *
 * @access public
 * @author Daniel Böhmer JTL-Software GmbH
 */
class Trennzeichen
{
    /**
     * @access protected
     * @var int
     */
    public $kTrennzeichen;

    /**
     * @access protected
     * @var int
     */
    protected $kSprache;

    /**
     * @access protected
     * @var int
     */
    protected $nEinheit;

    /**
     * @access protected
     * @var int
     */
    protected $nDezimalstellen;

    /**
     * @access protected
     * @var string
     */
    protected $cDezimalZeichen;

    /**
     * @access protected
     * @var string
     */
    protected $cTausenderZeichen;

    /**
     * @var array
     */
    private static $unitObject = [];

    /**
     * Constructor
     *
     * @param int $kTrennzeichen primarykey
     * @access public
     */
    public function __construct($kTrennzeichen = 0)
    {
        if ((int)$kTrennzeichen > 0) {
            $this->loadFromDB($kTrennzeichen);
        }
    }

    /**
     * Loads database member into class member
     *
     * @param int $kTrennzeichen primarykey
     * @return $this
     * @access private
     */
    private function loadFromDB($kTrennzeichen = 0)
    {
        $kTrennzeichen = (int)$kTrennzeichen;
        $cacheID       = 'units_lfdb_' . $kTrennzeichen;
        if (($oObj = Shop::Cache()->get($cacheID)) === false) {
            $oObj = Shop::DB()->select('ttrennzeichen', 'kTrennzeichen', $kTrennzeichen);
            Shop::Cache()->set($cacheID, $oObj, [CACHING_GROUP_CORE]);
        }
        if (isset($oObj->kTrennzeichen) && $oObj->kTrennzeichen > 0) {
            $cMember_arr = array_keys(get_object_vars($oObj));
            foreach ($cMember_arr as $cMember) {
                $this->$cMember = $oObj->$cMember;
            }
        }

        return $this;
    }

    /**
     * getUnit() can be called very often within one page request
     * so try to use static class variable and object cache to avoid
     * unnecessary sql request
     *
     * @param int $nEinheit
     * @param int $kSprache
     * @return mixed
     */
    private static function getUnitObject($nEinheit, $kSprache)
    {
        if (isset(self::$unitObject[$kSprache][$nEinheit])) {
            return self::$unitObject[$kSprache][$nEinheit];
        }
        $cacheID = 'units_' . (int)$nEinheit . '_' . (int)$kSprache;
        if (($oObj = Shop::Cache()->get($cacheID)) === false) {
            $oObj = Shop::DB()->select('ttrennzeichen', 'nEinheit', $nEinheit, 'kSprache', (int)$kSprache);
            Shop::Cache()->set($cacheID, $oObj, [CACHING_GROUP_CORE]);
        }
        if (!isset(self::$unitObject[$kSprache])) {
            self::$unitObject[$kSprache] = [];
        }
        self::$unitObject[$kSprache][$nEinheit] = $oObj;

        return $oObj;
    }

    /**
     * Loads database member into class member
     *
     * @param int $nEinheit
     * @param int $kSprache
     * @param int $fAmount
     * @return int|string|Trennzeichen
     */
    public static function getUnit($nEinheit, $kSprache, $fAmount = -1)
    {
        $nEinheit = (int)$nEinheit;
        $kSprache = (int)$kSprache;
        if (!$kSprache) {
            $oSprache = gibStandardsprache(true);
            $kSprache = (int)$oSprache->kSprache;
        }

        if ($nEinheit > 0 && $kSprache > 0) {
            $oObj = self::getUnitObject($nEinheit, $kSprache);
            if (isset($oObj->kTrennzeichen) && $oObj->kTrennzeichen > 0) {
                return ($fAmount >= 0) ?
                    number_format($fAmount, $oObj->nDezimalstellen, $oObj->cDezimalZeichen, $oObj->cTausenderZeichen) :
                    new self($oObj->kTrennzeichen);
            } else {
                self::insertMissingRow($nEinheit, $kSprache);
            }
        }

        return $fAmount;
    }

    /**
     * Insert missing trennzeichen
     *
     * @param int $nEinheit
     * @param int $kSprache
     * @return mixed|bool
     */
    public static function insertMissingRow($nEinheit, $kSprache)
    {
        // Standardwert [kSprache][nEinheit]
        $xRowAssoc_arr       = [];
        $xRowAssoc_arr[1][1] = ['nDezimalstellen' => 2, 'cDezimalZeichen' => ',', 'cTausenderZeichen' => '.'];
        $xRowAssoc_arr[1][3] = ['nDezimalstellen' => 2, 'cDezimalZeichen' => ',', 'cTausenderZeichen' => '.'];
        $xRowAssoc_arr[2][1] = ['nDezimalstellen' => 2, 'cDezimalZeichen' => ',', 'cTausenderZeichen' => '.'];
        $xRowAssoc_arr[2][3] = ['nDezimalstellen' => 2, 'cDezimalZeichen' => ',', 'cTausenderZeichen' => '.'];

        $nEinheit = (int)$nEinheit;
        $kSprache = (int)$kSprache;

        if ($nEinheit > 0 && $kSprache > 0) {
            if (!isset($xRowAssoc_arr[$kSprache][$nEinheit])) {
                $xRowAssoc_arr[$kSprache]            = [];
                $xRowAssoc_arr[$kSprache][$nEinheit] = ['nDezimalstellen' => 2, 'cDezimalZeichen' => ',', 'cTausenderZeichen' => '.'];
            }

            return Shop::DB()->query(
                "INSERT INTO `ttrennzeichen` 
                    (`kTrennzeichen`, `kSprache`, `nEinheit`, `nDezimalstellen`, `cDezimalZeichen`, `cTausenderZeichen`)
                    VALUES (
                      NULL, {$kSprache}, {$nEinheit}, {$xRowAssoc_arr[$kSprache][$nEinheit]['nDezimalstellen']}, 
                      '{$xRowAssoc_arr[$kSprache][$nEinheit]['cDezimalZeichen']}',
                    '{$xRowAssoc_arr[$kSprache][$nEinheit]['cTausenderZeichen']}')", 3
            );
        }

        return false;
    }

    /**
     * Loads database member into class member
     *
     * @access public
     * @var int $kSprache
     * @return array
     */
    public static function getAll($kSprache)
    {
        $kSprache = (int)$kSprache;
        $cacheID  = 'units_all_' . $kSprache;
        if (($oObjAssoc_arr = Shop::Cache()->get($cacheID)) === false) {
            $oObjAssoc_arr = [];
            if ($kSprache > 0) {
                $oObjTMP_arr = Shop::DB()->selectAll('ttrennzeichen', 'kSprache', $kSprache, 'kTrennzeichen', 'nEinheit');
                if (is_array($oObjTMP_arr) && count($oObjTMP_arr) > 0) {
                    foreach ($oObjTMP_arr as $oObjTMP) {
                        if (isset($oObjTMP->kTrennzeichen) && $oObjTMP->kTrennzeichen > 0) {
                            $oTrennzeichen = new self($oObjTMP->kTrennzeichen);

                            if (!isset($oObjAssoc_arr[$oTrennzeichen->getEinheit()])) {
                                $oObjAssoc_arr[$oTrennzeichen->getEinheit()] = [];
                            }

                            $oObjAssoc_arr[$oTrennzeichen->getEinheit()] = $oTrennzeichen;
                        }
                    }
                }
            }
            Shop::Cache()->set($cacheID, $oObjAssoc_arr, [CACHING_GROUP_CORE]);
        }

        return $oObjAssoc_arr;
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
        unset($oObj->kTrennzeichen);

        $kPrim = Shop::DB()->insert('ttrennzeichen', $oObj);

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
        $upd                    = new stdClass();
        $upd->kSprache          = (int)$this->kSprache;
        $upd->nEinheit          = $this->nEinheit;
        $upd->nDezimalstellen   = $this->nDezimalstellen;
        $upd->cDezimalZeichen   = $this->cDezimalZeichen;
        $upd->cTausenderZeichen = $this->cTausenderZeichen;
        
        return Shop::DB()->update('ttrennzeichen', 'kTrennzeichen', (int)$this->kTrennzeichen, $upd);
    }

    /**
     * Delete the class in the database
     *
     * @return int
     * @access public
     */
    public function delete()
    {
        return Shop::DB()->delete('ttrennzeichen', 'kTrennzeichen', (int)$this->kTrennzeichen);
    }

    /**
     * Sets the kTrennzeichen
     *
     * @access public
     * @param int $kTrennzeichen
     * @return $this
     */
    public function setTrennzeichen($kTrennzeichen)
    {
        $this->kTrennzeichen = (int)$kTrennzeichen;

        return $this;
    }

    /**
     * Sets the kSprache
     *
     * @access public
     * @param int $kSprache
     * @return $this
     */
    public function setSprache($kSprache)
    {
        $this->kSprache = (int)$kSprache;

        return $this;
    }

    /**
     * Sets the nEinheit
     *
     * @access public
     * @param int $nEinheit
     * @return $this
     */
    public function setEinheit($nEinheit)
    {
        $this->nEinheit = (int)$nEinheit;

        return $this;
    }

    /**
     * Sets the nDezimalstellen
     *
     * @access public
     * @param int $nDezimalstellen
     * @return $this
     */
    public function setDezimalstellen($nDezimalstellen)
    {
        $this->nDezimalstellen = (int)$nDezimalstellen;

        return $this;
    }

    /**
     * Sets the cDezimalZeichen
     *
     * @access public
     * @param string $cDezimalZeichen
     * @return $this
     */
    public function setDezimalZeichen($cDezimalZeichen)
    {
        $this->cDezimalZeichen = Shop::DB()->escape($cDezimalZeichen);

        return $this;
    }

    /**
     * Sets the cTausenderZeichen
     *
     * @access public
     * @param string $cTausenderZeichen
     * @return $this
     */
    public function setTausenderZeichen($cTausenderZeichen)
    {
        $this->cTausenderZeichen = Shop::DB()->escape($cTausenderZeichen);

        return $this;
    }

    /**
     * Gets the kTrennzeichen
     *
     * @access public
     * @return int
     */
    public function getTrennzeichen()
    {
        return $this->kTrennzeichen;
    }

    /**
     * Gets the kSprache
     *
     * @access public
     * @return int
     */
    public function getSprache()
    {
        return $this->kSprache;
    }

    /**
     * Gets the nEinheit
     *
     * @access public
     * @return int
     */
    public function getEinheit()
    {
        return $this->nEinheit;
    }

    /**
     * Gets the nDezimalstellen
     *
     * @access public
     * @return int
     */
    public function getDezimalstellen()
    {
        return $this->nDezimalstellen;
    }

    /**
     * Gets the cDezimalZeichen
     *
     * @access public
     * @return string
     */
    public function getDezimalZeichen()
    {
        return $this->cDezimalZeichen;
    }

    /**
     * Gets the cTausenderZeichen
     *
     * @access public
     * @return string
     */
    public function getTausenderZeichen()
    {
        return $this->cTausenderZeichen;
    }

    /**
     * @return mixed
     */
    public static function migrateUpdate()
    {
        $oEinstellungen = Shop::getSettings([CONF_ARTIKELDETAILS, CONF_ARTIKELUEBERSICHT]);
        $oSprache_arr   = gibAlleSprachen();

        if (is_array($oSprache_arr) && count($oSprache_arr) > 0) {
            Shop::DB()->query("TRUNCATE ttrennzeichen", 3);
            $nEinheit_arr = [JTLSEPARATER_WEIGHT, JTLSEPARATER_AMOUNT];
            foreach ($oSprache_arr as $oSprache) {
                foreach ($nEinheit_arr as $nEinheit) {
                    $oTrennzeichen = new self();
                    $oTrennzeichen->setSprache($oSprache->kSprache);
                    $oTrennzeichen->setEinheit($nEinheit);

                    if ($nEinheit == JTLSEPARATER_WEIGHT) {
                        if (isset($oEinstellungen['artikeldetails']['artikeldetails_gewicht_stellenanzahl']) &&
                            strlen($oEinstellungen['artikeldetails']['artikeldetails_gewicht_stellenanzahl']) > 0) {
                            $oTrennzeichen->setDezimalstellen($oEinstellungen['artikeldetails']['artikeldetails_gewicht_stellenanzahl']);
                        } else {
                            $oTrennzeichen->setDezimalstellen(2);
                        }
                    } else {
                        $oTrennzeichen->setDezimalstellen(2);
                    }

                    if (isset($oEinstellungen['artikeldetails']['artikeldetails_zeichen_nachkommatrenner']) &&
                        strlen($oEinstellungen['artikeldetails']['artikeldetails_zeichen_nachkommatrenner']) > 0) {
                        $oTrennzeichen->setDezimalZeichen($oEinstellungen['artikeldetails']['artikeldetails_zeichen_nachkommatrenner']);
                    } else {
                        $oTrennzeichen->setDezimalZeichen(',');
                    }

                    if (isset($oEinstellungen['artikeldetails']['artikeldetails_zeichen_tausendertrenner']) &&
                        strlen($oEinstellungen['artikeldetails']['artikeldetails_zeichen_tausendertrenner']) > 0) {
                        $oTrennzeichen->setTausenderZeichen($oEinstellungen['artikeldetails']['artikeldetails_zeichen_tausendertrenner']);
                    } else {
                        $oTrennzeichen->setTausenderZeichen('.');
                    }

                    $oTrennzeichen->save();
                }
            }

            return Shop::DB()->query(
                "DELETE teinstellungen, teinstellungenconf
                    FROM teinstellungenconf
                    LEFT JOIN teinstellungen 
                        ON teinstellungen.cName = teinstellungenconf.cWertName
                    WHERE teinstellungenconf.kEinstellungenConf IN (1458, 1459, 495, 497, 499, 501)", 3);
        }

        return false;
    }
}
