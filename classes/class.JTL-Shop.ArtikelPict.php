<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class ArtikelPict
 */
class ArtikelPict
{
    /**
     * @var int
     */
    public $kArtikel;

    /**
     * @var int
     */
    public $nNr;

    /**
     * @var string
     */
    public $cPfad1;

    /**
     * @var string
     */
    public $cPfad2;

    /**
     * @var string
     */
    public $cPfad3;

    /**
     * Setzt ArtikelPict mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @access public
     * @param int $kArtikel
     * @return $this
     */
    public function loadFromDB($kArtikel)
    {
        $obj = Shop::DB()->select('tartikelpict', 'kArtikel', (int)$kArtikel);
        if ($obj->kArtikel > 0) {
            foreach (get_object_vars($obj) as $k => $v) {
                $this->$k = $v;
            }
        }

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

        return Shop::DB()->insert('tartikelpict', $obj);
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @return int
     */
    public function updateInDB()
    {
        $obj = kopiereMembers($this);

        return Shop::DB()->update('tartikelpict', 'kArtikel', $obj->kArtikel, $obj);
    }

    /**
     * setzt Daten aus Sync POST request.
     *
     * @return bool - true, wenn alle notwendigen Daten vorhanden, sonst false
     */
    public function setzePostDaten()
    {
        $this->kArtikel = (int)$_POST['KeyArtikel'];
        $this->nNr      = 0;
        $this->cPfad1   = str_replace('\\', '/', StringHandler::htmlentities(StringHandler::filterXSS($_POST['Path1'])));
        $this->cPfad2   = str_replace('\\', '/', StringHandler::htmlentities(StringHandler::filterXSS($_POST['Path2'])));
        $this->cPfad3   = str_replace('\\', '/', StringHandler::htmlentities(StringHandler::filterXSS($_POST['Path3'])));

        return ($this->kArtikel > 0);
    }
}
