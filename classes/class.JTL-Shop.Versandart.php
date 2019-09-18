<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Versandart
 */
class Versandart
{
    /**
     * @var int
     */
    public $kVersandart;

    /**
     * @var int
     */
    public $kVersandberechnung;

    /**
     * @var string
     */
    public $cVersandklassen;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cLaender;

    /**
     * @var string
     */
    public $cAnzeigen;

    /**
     * @var string
     */
    public $cKundengruppen;

    /**
     * @var string
     */
    public $cBild;

    /**
     * @var string
     */
    public $cNurAbhaengigeVersandart;

    /**
     * @var int
     */
    public $nSort;

    /**
     * @var float
     */
    public $fPreis;

    /**
     * @var float
     */
    public $fVersandkostenfreiAbX;

    /**
     * @var float
     */
    public $fDeckelung;

    /**
     * @var array
     */
    public $oVersandartSprache_arr;

    /**
     * @var array
     */
    public $oVersandartStaffel_arr;

    /**
     * @var int
     */
    public $kRechnungsadresse;

    /**
     * @var string
     */
    public $cSendConfirmationMail;

    /**
     * @var string
     */
    public $cIgnoreShippingProposal;

    /**
     * @var int
     */
    public $nMinLiefertage;

    /**
     * @var int
     */
    public $nMaxLiefertage;

    /**
     * Konstruktor
     *
     * @param int $kVersandart
     */
    public function __construct($kVersandart = 0)
    {
        if ((int)$kVersandart > 0) {
            $this->loadFromDB($kVersandart);
        }
    }

    /**
     * Setzt Versandart mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @access public
     * @param int $kVersandart
     * @return int
     */
    public function loadFromDB($kVersandart)
    {
        $obj = Shop::DB()->select('tversandart', 'kVersandart', (int)$kVersandart);
        if (!isset($obj->kVersandart) || !$obj->kVersandart) {
            return 0;
        }
        $members = array_keys(get_object_vars($obj));
        foreach ($members as $member) {
            $this->$member = $obj->$member;
        }
        // VersandartSprache
        $oVersandartSprache_arr = Shop::DB()->selectAll('tversandartsprache', 'kVersandart', (int)$this->kVersandart);
        if (is_array($oVersandartSprache_arr) && count($oVersandartSprache_arr) > 0) {
            foreach ($oVersandartSprache_arr as $oVersandartSprache) {
                $this->oVersandartSprache_arr[$oVersandartSprache->cISOSprache] = $oVersandartSprache;
            }
        }
        // Versandstaffel
        $this->oVersandartStaffel_arr = Shop::DB()->selectAll(
            'tversandartstaffel',
            'kVersandart',
            (int)$this->kVersandart
        );

        return 1;
    }

    /**
     * Fügt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @access public
     * @return int - Key von eingefügter Versandart
     */
    public function insertInDB()
    {
        $obj = kopiereMembers($this);
        unset(
            $obj->oVersandartSprache_arr,
            $obj->oVersandartStaffel_arr,
            $obj->kRechnungsadresse,
            $obj->nMinLiefertage,
            $obj->nMaxLiefertage
        );
        $this->kRechnungsadresse = Shop::DB()->insert('tversandart', $obj);

        return $this->kVersandart;
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @access public
     * @return int
     */
    public function updateInDB()
    {
        $obj = kopiereMembers($this);
        unset(
            $obj->oVersandartSprache_arr,
            $obj->oVersandartStaffel_arr,
            $obj->kRechnungsadresse,
            $obj->nMinLiefertage,
            $obj->nMaxLiefertage
        );

        return Shop::DB()->update('tversandart', 'kVersandart', $obj->kVersandart, $obj);
    }

    /**
     * @param int $kVersandart
     * @return bool
     */
    public static function deleteInDB($kVersandart)
    {
        $kVersandart = (int)$kVersandart;
        if ($kVersandart > 0) {
            Shop::DB()->delete('tversandart', 'kVersandart', $kVersandart);
            Shop::DB()->delete('tversandartsprache', 'kVersandart', $kVersandart);
            Shop::DB()->delete('tversandartzahlungsart', 'kVersandart', $kVersandart);
            Shop::DB()->delete('tversandartstaffel', 'kVersandart', $kVersandart);
            Shop::DB()->query(
                "DELETE tversandzuschlag, tversandzuschlagplz, tversandzuschlagsprache
                    FROM tversandzuschlag
                    LEFT JOIN tversandzuschlagplz 
                        ON tversandzuschlagplz.kVersandzuschlag = tversandzuschlag.kVersandzuschlag
                    LEFT JOIN tversandzuschlagsprache 
                        ON tversandzuschlagsprache.kVersandzuschlag = tversandzuschlag.kVersandzuschlag
                    WHERE tversandzuschlag.kVersandart = {$kVersandart}", 4
            );

            return true;
        }

        return false;
    }

    /**
     * @param int $kVersandart
     * @return bool
     */
    public static function cloneShipping($kVersandart)
    {
        $kVersandart  = (int)$kVersandart;
        $cSection_arr = [
            'tversandartsprache'     => 'kVersandart',
            'tversandartstaffel'     => 'kVersandartStaffel',
            'tversandartzahlungsart' => 'kVersandartZahlungsart',
            'tversandzuschlag'       => 'kVersandzuschlag'
        ];

        $oVersandart = Shop::DB()->select('tversandart', 'kVersandart', $kVersandart);

        if (isset($oVersandart->kVersandart) && $oVersandart->kVersandart > 0) {
            unset($oVersandart->kVersandart);
            $kVersandartNew = Shop::DB()->insert('tversandart', $oVersandart);

            if ($kVersandartNew > 0) {
                foreach ($cSection_arr as $cSection => $cKey) {
                    $oSection_arr = self::getShippingSection($cSection, 'kVersandart', $kVersandart);
                    self::cloneShippingSection($oSection_arr, $cSection, 'kVersandart', $kVersandartNew, $cKey);
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @param string $table
     * @param string $key
     * @param int    $value
     * @return array
     */
    private static function getShippingSection($table, $key, $value)
    {
        $value = (int)$value;

        if ($value > 0 && strlen($table) > 0 && strlen($key) > 0) {
            $Objs = Shop::DB()->selectAll($table, $key, $value);

            if (is_array($Objs)) {
                return $Objs;
            }
        }

        return [];
    }

    /**
     * @param array       $objectArr
     * @param string      $table
     * @param string      $key
     * @param mixed       $value
     * @param null|string $unsetKey
     */
    private static function cloneShippingSection(array $objectArr = null, $table, $key, $value, $unsetKey = null)
    {
        $value = (int)$value;

        if ($value > 0 && is_array($objectArr) && count($objectArr) > 0 && strlen($key) > 0) {
            foreach ($objectArr as $Obj) {
                $kKeyPrim = $Obj->$unsetKey;
                if ($unsetKey !== null) {
                    unset($Obj->$unsetKey);
                }
                $Obj->$key = $value;
                if ($table === 'tversandartzahlungsart' && empty($Obj->fAufpreis)) {
                    $Obj->fAufpreis = 0;
                }
                $kKey = Shop::DB()->insert($table, $Obj);

                if ((int)$kKey > 0 && $table === 'tversandzuschlag') {
                    self::cloneShippingSectionSpecial($kKeyPrim, $kKey);
                }
            }
        }
    }

    /**
     * @param int $oldKey
     * @param int $newKey
     */
    private static function cloneShippingSectionSpecial($oldKey, $newKey)
    {
        $oldKey = (int)$oldKey;
        $newKey = (int)$newKey;

        if ($oldKey > 0 && $newKey > 0) {
            $cSectionSub_arr = [
                'tversandzuschlagplz'     => 'kVersandzuschlagPlz',
                'tversandzuschlagsprache' => 'kVersandzuschlag'
            ];

            foreach ($cSectionSub_arr as $cSectionSub => $cSubKey) {
                $oSubSection_arr = self::getShippingSection($cSectionSub, 'kVersandzuschlag', $oldKey);

                self::cloneShippingSection($oSubSection_arr, $cSectionSub, 'kVersandzuschlag', $newKey, $cSubKey);
            }
        }
    }
}
