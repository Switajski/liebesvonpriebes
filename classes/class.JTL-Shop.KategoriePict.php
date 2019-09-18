<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class KategoriePict
 */
class KategoriePict
{
    /**
     * @var int
     */
    public $kKategoriePict;

    /**
     * @var int
     */
    public $kKategorie;

    /**
     * @var string
     */
    public $cPfad;

    /**
     * @var string
     */
    public $cType;

    /**
     * Konstruktor
     *
     * @param int $kKategoriePict - Falls angegeben, wird der KategoriePict mit angegebenem KategoriePict aus der DB geholt
     */
    public function __construct($kKategoriePict = 0)
    {
        if ((int)$kKategoriePict > 0) {
            $this->loadFromDB($kKategoriePict);
        }
    }

    /**
     * Setzt KategoriePict mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @access public
     * @param int $kKategoriePict Primary Key
     * @return $this
     */
    public function loadFromDB($kKategoriePict)
    {
        $obj = Shop::DB()->select('tkategoriepict', 'kKategoriePict', (int)$kKategoriePict);
        foreach (get_object_vars($obj) as $k => $v) {
            $this->$k = $v;
        }

        return $this;
    }

    /**
     * Fügt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @access public
     * @return int
     */
    public function insertInDB()
    {
        return Shop::DB()->insert('tkategoriepict', kopiereMembers($this));
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

        return Shop::DB()->update('tkategoriepict', 'kKategoriePict', $obj->kKategoriePict, $obj);
    }

    /**
     * setzt Daten aus Sync POST request.
     *
     * @return bool - true, wenn alle notwendigen Daten vorhanden, sonst false
     */
    public function setzePostDaten()
    {
        $this->kKategoriePict = (int)$_POST['KeyKategoriePict'];
        $this->kKategorie     = (int)$_POST['KeyKategorie'];
        $this->cPfad          = 'k' . StringHandler::htmlentities(StringHandler::filterXSS($_POST['KeyKategorie'])) . '.jpg';
        $this->cType          = 0;

        return ($this->kKategoriePict > 0 && $this->kKategorie > 0 && $this->cPfad);
    }
}
