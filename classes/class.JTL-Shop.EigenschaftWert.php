<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class EigenschaftWert
 */
class EigenschaftWert
{
    /**
     * @var int
     */
    public $kEigenschaftWert;

    /**
     * @var int
     */
    public $kEigenschaft;

    /**
     * @var float
     */
    public $fAufpreisNetto;

    /**
     * @var float
     */
    public $fGewichtDiff;

    /**
     * @var float
     */
    public $fLagerbestand;

    /**
     * @var float
     */
    public $fPackeinheit;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var float
     */
    public $fAufpreis;

    /**
     * @var int
     */
    public $nSort;

    /**
     * Konstruktor
     *
     * @param int $kEigenschaftWert - Falls angegeben, wird der EigenschaftWert mit angegebenem kEigenschaftWert aus der DB geholt
     */
    public function __construct($kEigenschaftWert = 0)
    {
        $kEigenschaftWert = (int)$kEigenschaftWert;
        if ($kEigenschaftWert > 0) {
            $this->loadFromDB($kEigenschaftWert);
        }
    }

    /**
     * Setzt EigenschaftWert mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @access public
     * @param int $kEigenschaftWert
     * @return $this
     */
    public function loadFromDB($kEigenschaftWert)
    {
        $kEigenschaftWert = (int)$kEigenschaftWert;
        if ($kEigenschaftWert > 0) {
            $obj = Shop::DB()->select('teigenschaftwert', 'kEigenschaftWert', $kEigenschaftWert);
            if (isset($obj->kEigenschaftWert) && $obj->kEigenschaftWert > 0) {
                foreach (get_object_vars($obj) as $k => $v) {
                    $this->$k = $v;
                }
                $this->kEigenschaft     = (int)$this->kEigenschaft;
                $this->kEigenschaftWert = (int)$this->kEigenschaftWert;
                $this->nSort            = (int)$this->nSort;
                if ($this->fPackeinheit == 0) {
                    $this->fPackeinheit = 1;
                }
            }
            executeHook(HOOK_EIGENSCHAFTWERT_CLASS_LOADFROMDB);
        }

        return $this;
    }

    /**
     * Fuegt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @return int
     */
    public function insertInDB()
    {
        $obj = kopiereMembers($this);
        unset($obj->fAufpreis);

        return Shop::DB()->insert('teigenschaftwert', $obj);
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @return int
     */
    public function updateInDB()
    {
        $obj = kopiereMembers($this);
        unset($obj->fAufpreis);

        return Shop::DB()->update('teigenschaftwert', 'kEigenschaftWert', $obj->kEigenschaftWert, $obj);
    }

    /**
     * setzt Daten aus Sync POST request.
     *
     * @return bool - true, wenn alle notwendigen Daten vorhanden, sonst false
     */
    public function setzePostDaten()
    {
        $this->kEigenschaftWert = (int)$_POST['KeyEigenschaftWert'];
        $this->kEigenschaft     = (int)$_POST['KeyEigenschaft'];
        $this->fAufpreis        = (int)$_POST['Aufpreis'];
        $this->cName            = StringHandler::htmlentities(StringHandler::filterXSS($_POST['Name']));

        return ($this->kEigenschaftWert > 0 && $this->kEigenschaft > 0);
    }
}
