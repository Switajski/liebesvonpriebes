<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Eigenschaft
 */
class Eigenschaft
{
    /**
     * @var int
     */
    public $kEigenschaft;

    /**
     * @var int
     */
    public $kArtikel;

    /**
     * @var string
     */
    public $cName;

    /**
     * string - 'Y'/'N'
     */
    public $cWaehlbar;

    /**
     * Eigenschaft Wert
     *
     * @var EigenschaftWert
     */
    public $EigenschaftsWert;

    /**
     * Konstruktor
     *
     * @param int $kEigenschaft - Falls angegeben, wird der Eigenschaft mit angegebenem kEigenschaft aus der DB geholt
     */
    public function __construct($kEigenschaft = 0)
    {
        if ((int)$kEigenschaft > 0) {
            $this->loadFromDB($kEigenschaft);
        }
    }

    /**
     * Setzt Eigenschaft mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @param int $kEigenschaft Primary Key
     * @return $this
     */
    public function loadFromDB($kEigenschaft)
    {
        $obj = Shop::DB()->select('teigenschaft', 'kEigenschaft', (int)$kEigenschaft);
        foreach (get_object_vars($obj) as $k => $v) {
            $this->$k = $v;
        }
        executeHook(HOOK_EIGENSCHAFT_CLASS_LOADFROMDB);

        return $this;
    }

    /**
     * Fuegt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @access public
     * @return int
     */
    public function insertInDB()
    {
        $obj = kopiereMembers($this);
        unset($obj->EigenschaftsWert);

        return Shop::DB()->insert('teigenschaft', $obj);
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

        return Shop::DB()->update('teigenschaft', 'kEigenschaft', $obj->kEigenschaft, $obj);
    }

    /**
     * setzt Daten aus Sync POST request.
     *
     * @return bool - true, wenn alle notwendigen Daten vorhanden, sonst false
     */
    public function setzePostDaten()
    {
        $this->kEigenschaft = (int)$_POST['KeyEigenschaft'];
        $this->kArtikel     = (int)$_POST['KeyArtikel'];
        $this->cName        = StringHandler::htmlentities(StringHandler::filterXSS($_POST['Name']));
        $this->cWaehlbar    = StringHandler::htmlentities(StringHandler::filterXSS($_POST['Waehlbar']));

        return ($this->kEigenschaft > 0 && $this->kArtikel > 0);
    }
}
