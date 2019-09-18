<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class ZahlungsInfo
 */
class ZahlungsInfo
{
    /**
     * @var int
     */
    public $kZahlungsInfo;

    /**
     * @var int
     */
    public $kBestellung;

    /**
     * @var int
     */
    public $kKunde;

    /**
     * @var string
     */
    public $cBankName;

    /**
     * @var string
     */
    public $cBLZ;

    /**
     * @var string
     */
    public $cBIC;

    /**
     * @var string
     */
    public $cIBAN;

    /**
     * @var string
     */
    public $cKontoNr;

    /**
     * @var string
     */
    public $cKartenNr;

    /**
     * @var string
     */
    public $cGueltigkeit;

    /**
     * @var string
     */
    public $cCVV;

    /**
     * @var string
     */
    public $cKartenTyp;

    /**
     * @var string
     */
    public $cInhaber;

    /**
     * @var string
     */
    public $cVerwendungszweck;

    /**
     * @var string
     */
    public $cAbgeholt;

    /**
     * @param int $kZahlungsInfo
     * @param int $kBestellung
     */
    public function __construct($kZahlungsInfo = 0, $kBestellung = 0)
    {
        if ((int)$kZahlungsInfo > 0 || (int)$kBestellung > 0) {
            $this->loadFromDB($kZahlungsInfo, $kBestellung);
        }
    }

    /**
     * Setzt ZahlungsInfo mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @access public
     * @param int $kZahlungsInfo Primary Key
     * @param int $kBestellung
     * @return $this
     */
    public function loadFromDB($kZahlungsInfo, $kBestellung)
    {
        $obj           = null;
        $kZahlungsInfo = (int)$kZahlungsInfo;
        $kBestellung   = (int)$kBestellung;
        if ($kZahlungsInfo > 0) {
            $obj = Shop::DB()->select('tzahlungsinfo', 'kZahlungsInfo', $kZahlungsInfo);
        } elseif ($kBestellung > 0) {
            $obj = Shop::DB()->select('tzahlungsinfo', 'kBestellung', $kBestellung);
        }

        if (is_object($obj)) {
            $members = array_keys(get_object_vars($obj));
            foreach ($members as $member) {
                $this->$member = $obj->$member;
            }

            if ($this->kZahlungsInfo > 0) {
                $this->entschluesselZahlungsinfo();
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function verschluesselZahlungsinfo()
    {
        $this->cBankName         = verschluesselXTEA(trim($this->cBankName));
        $this->cKartenNr         = verschluesselXTEA(trim($this->cKartenNr));
        $this->cCVV              = verschluesselXTEA(trim($this->cCVV));
        $this->cKontoNr          = verschluesselXTEA(trim($this->cKontoNr));
        $this->cBLZ              = verschluesselXTEA(trim($this->cBLZ));
        $this->cIBAN             = verschluesselXTEA(trim($this->cIBAN));
        $this->cBIC              = verschluesselXTEA(trim($this->cBIC));
        $this->cInhaber          = verschluesselXTEA(trim($this->cInhaber));
        $this->cVerwendungszweck = verschluesselXTEA(trim($this->cVerwendungszweck));

        return $this;
    }

    /**
     * @return $this
     */
    public function entschluesselZahlungsinfo()
    {
        $this->cBankName         = trim(entschluesselXTEA($this->cBankName));
        $this->cKartenNr         = trim(entschluesselXTEA($this->cKartenNr));
        $this->cCVV              = trim(entschluesselXTEA($this->cCVV));
        $this->cKontoNr          = trim(entschluesselXTEA($this->cKontoNr));
        $this->cBLZ              = trim(entschluesselXTEA($this->cBLZ));
        $this->cIBAN             = trim(entschluesselXTEA($this->cIBAN));
        $this->cBIC              = trim(entschluesselXTEA($this->cBIC));
        $this->cInhaber          = trim(entschluesselXTEA($this->cInhaber));
        $this->cVerwendungszweck = trim(entschluesselXTEA($this->cVerwendungszweck));

        return $this;
    }

    /**
     * Fügt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @access public
     * @return int - Key von eingefügter ZahlungsInfo
     */
    public function insertInDB()
    {
        $this->cAbgeholt = 'N';
        $this->verschluesselZahlungsinfo();
        $obj = kopiereMembers($this);
        unset($obj->kZahlungsInfo);
        $this->kZahlungsInfo = Shop::DB()->insert('tzahlungsinfo', $obj);
        $this->entschluesselZahlungsinfo();

        return $this->kZahlungsInfo;
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @access public
     * @return int
     */
    public function updateInDB()
    {
        $this->verschluesselZahlungsinfo();
        $obj     = kopiereMembers($this);
        $cReturn = Shop::DB()->update('tzahlungsinfo', 'kZahlungsInfo', $obj->kZahlungsInfo, $obj);
        $this->entschluesselZahlungsinfo();

        return $cReturn;
    }
}
