<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class KategorieArtikel
 */
class KategorieArtikel
{
    /**
     * @var int
     */
    public $kKategorieArtikel;

    /**
     * @var int
     */
    public $kArtikel;

    /**
     * @var int
     */
    public $kKategorie;

    /**
     * Konstruktor
     *
     * @param int $kKategorieArtikel - Falls angegeben, wird der KategorieArtikel mit angegebenem kKategorieArtikel aus der DB geholt
     */
    public function __construct($kKategorieArtikel = 0)
    {
        if ((int)$kKategorieArtikel > 0) {
            $this->loadFromDB($kKategorieArtikel);
        }
    }

    /**
     * Setzt KategorieArtikel mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @access public
     * @param int $kKategorieArtikel
     * @return $this
     */
    public function loadFromDB($kKategorieArtikel)
    {
        $obj = Shop::DB()->select('tkategorieartikel', 'kKategorieArtikel', (int)$kKategorieArtikel);
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
        return Shop::DB()->insert('tkategorieartikel', kopiereMembers($this));
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

        return Shop::DB()->update('tkategorieartikel', 'kKategorieArtikel', $obj->kKategorieArtikel, $obj);
    }

    /**
     * setzt Daten aus Sync POST request.
     *
     * @return Bool true, wenn alle notwendigen Daten vorhanden, sonst false
     */
    public function setzePostDaten()
    {
        $this->kKategorieArtikel = (int)$_POST['KeyKategorieArtikel'];
        $this->kArtikel          = (int)$_POST['KeyArtikel'];
        $this->kKategorie        = (int)$_POST['KeyKategorie'];

        return ($this->kKategorie > 0 && $this->kArtikel > 0 && $this->kKategorieArtikel > 0);
    }
}
