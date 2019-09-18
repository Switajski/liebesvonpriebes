<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Firma
 */
class Firma
{
    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cUnternehmer;

    /**
     * @var string
     */
    public $cStrasse;

    /**
     * @var string
     */
    public $cHausnummer;

    /**
     * @var string
     */
    public $cPLZ;

    /**
     * @var string
     */
    public $cOrt;

    /**
     * @var string
     */
    public $cLand;

    /**
     * @var string
     */
    public $cTel;

    /**
     * @var string
     */
    public $cFax;

    /**
     * @var string
     */
    public $cEMail;

    /**
     * @var string
     */
    public $cWWW;

    /**
     * @var string
     */
    public $cKontoinhaber;

    /**
     * @var string
     */
    public $cBLZ;

    /**
     * @var string
     */
    public $cKontoNr;

    /**
     * @var string
     */
    public $cBank;

    /**
     * @var string
     */
    public $cUSTID;

    /**
     * @var string
     */
    public $cSteuerNr;

    /**
     * @var string
     */
    public $cIBAN;

    /**
     * @var string
     */
    public $cBIC;

    /**
     * @param bool $load
     */
    public function __construct($load = true)
    {
        if ($load) {
            $this->loadFromDB();
        }
    }

    /**
     * Setzt Firma mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @return $this
     */
    public function loadFromDB()
    {
        $obj = Shop::DB()->query("SELECT * FROM tfirma LIMIT 1", 1);
        foreach (get_object_vars($obj) as $k => $v) {
            $this->$k = $v;
        }
        executeHook(HOOK_FIRMA_CLASS_LOADFROMDB);

        return $this;
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @access public
     * @return int
     */
    public function updateInDB()
    {
        $obj                = new stdClass();
        $obj->cName         = $this->cName;
        $obj->cUnternehmer  = $this->cUnternehmer;
        $obj->cStrasse      = $this->cStrasse;
        $obj->cHausnummer   = $this->cHausnummer;
        $obj->cPLZ          = $this->cPLZ;
        $obj->cOrt          = $this->cOrt;
        $obj->cLand         = $this->cLand;
        $obj->cTel          = $this->cTel;
        $obj->cFax          = $this->cFax;
        $obj->cEMail        = $this->cEMail;
        $obj->cWWW          = $this->cWWW;
        $obj->cKontoinhaber = $this->cKontoinhaber;
        $obj->cBLZ          = $this->cBLZ;
        $obj->cKontoNr      = $this->cKontoNr;
        $obj->cBank         = $this->cBank;
        $obj->cUSTID        = $this->cUSTID;
        $obj->cSteuerNr     = $this->cSteuerNr;
        $obj->cIBAN         = $this->cIBAN;
        $obj->cBIC          = $this->cBIC;

        return Shop::DB()->update('tfirma', 1, 1, $obj);
    }

    /**
     * setzt Daten aus Sync POST request.
     *
     * @return bool
     */
    public function setzePostDaten()
    {
        $this->cName        = StringHandler::htmlentities(StringHandler::filterXSS($_POST['Name']));
        $this->cUnternehmer = StringHandler::htmlentities(StringHandler::filterXSS($_POST['Unternehmer']));
        $this->cStrasse     = StringHandler::htmlentities(StringHandler::filterXSS($_POST['Strasse']));
        $this->cHausnummer  = StringHandler::htmlentities(StringHandler::filterXSS($_POST['Hausnummer']));
        $this->cPLZ         = StringHandler::htmlentities(StringHandler::filterXSS($_POST['PLZ']));
        $this->cOrt         = StringHandler::htmlentities(StringHandler::filterXSS($_POST['Ort']));
        $this->cLand        = StringHandler::htmlentities(StringHandler::filterXSS($_POST['Land']));
        $this->cTel         = StringHandler::htmlentities(StringHandler::filterXSS($_POST['Tel']));
        $this->cFax         = StringHandler::htmlentities(StringHandler::filterXSS($_POST['Fax']));
        $this->cEMail       = StringHandler::htmlentities(StringHandler::filterXSS($_POST['EMail']));
        $this->cWWW         = StringHandler::htmlentities(StringHandler::filterXSS($_POST['WWW']));
        $this->cBLZ         = StringHandler::htmlentities(StringHandler::filterXSS($_POST['BLZ']));
        $this->cKontoNr     = StringHandler::htmlentities(StringHandler::filterXSS($_POST['KontoNr']));
        $this->cBank        = StringHandler::htmlentities(StringHandler::filterXSS($_POST['Bank']));
        $this->cUSTID       = StringHandler::htmlentities(StringHandler::filterXSS($_POST['USTID']));
        $this->cSteuerNr    = StringHandler::htmlentities(StringHandler::filterXSS($_POST['SteuerNr']));

        return true;
    }
}
