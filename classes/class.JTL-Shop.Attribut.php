<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Attribut
 */
class Attribut
{
    /**
     * @var int
     */
    public $kAttribut;

    /**
     * @var int
     */
    public $kArtikel;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cStringWert;

    /**
     * @var string
     */
    public $cTextWert;

    /**
     * Konstruktor
     *
     * @param int $kAttribut - Falls angegeben, wird Attribut mit angegebenem kAttribut aus der DB geholt
     */
    public function __construct($kAttribut = 0)
    {
        $kAttribut = (int)$kAttribut;
        if ($kAttribut > 0) {
            $this->loadFromDB($kAttribut);
        }
    }

    /**
     * Setzt Attribut mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @access public
     * @param int $kAttribut Primary Key
     * @return $this
     */
    public function loadFromDB($kAttribut)
    {
        $obj = Shop::DB()->select('tattribut', 'kAttribut', (int)$kAttribut);
        foreach (get_object_vars($obj) as $k => $v) {
            $this->$k = $v;
        }
        executeHook(HOOK_ATTRIBUT_CLASS_LOADFROMDB);

        return $this;
    }

    /**
     * Fügt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @access public
     * @return mixed
     */
    public function insertInDB()
    {
        $obj = kopiereMembers($this);
        unset($obj->kAttribut);

        return Shop::DB()->insert('tattribut', $obj);
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @return int
     */
    public function updateInDB()
    {
        $obj = kopiereMembers($this);

        return Shop::DB()->update('tattribut', 'kAttribut', $obj->kAttribut, $obj);
    }

    /**
     * setzt Daten aus Sync POST request.
     *
     * @return bool - true, wenn alle notwendigen Daten vorhanden, sonst false
     */
    public function setzePostDaten()
    {
        $this->kAttribut   = (int)$_POST['KeyAttribut'];
        $this->kArtikel    = (int)$_POST['KeyArtikel'];
        $this->cName       = StringHandler::htmlentities(StringHandler::filterXSS($_POST['Name']));
        $this->cStringWert = StringHandler::htmlentities(StringHandler::filterXSS($_POST['StringWert']));
        $this->cTextWert   = StringHandler::htmlentities(StringHandler::filterXSS($_POST['TextWert']));

        return ($this->kAttribut > 0 && $this->kArtikel > 0 && $this->cName);
    }
}
