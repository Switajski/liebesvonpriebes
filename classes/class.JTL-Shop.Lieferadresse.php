<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Lieferadresse
 */
class Lieferadresse extends Adresse
{
    /**
     * @var int
     */
    public $kLieferadresse;

    /**
     * @var int
     */
    public $kKunde;

    /**
     * @var string
     */
    public $cAnredeLocalized;

    /**
     * @var string
     */
    public $angezeigtesLand;

    /**
     * Konstruktor
     *
     * @param int $kLieferadresse - Falls angegeben, wird der Lieferadresse mit angegebenem kLieferadresse aus der DB geholt
     */
    public function __construct($kLieferadresse = 0)
    {
        if ($kLieferadresse > 0) {
            $this->loadFromDB($kLieferadresse);
        }
    }

    /**
     * Setzt Lieferadresse mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @access public
     * @param int $kLieferadresse
     * @return Lieferadresse|int
     */
    public function loadFromDB($kLieferadresse)
    {
        $kLieferadresse = (int)$kLieferadresse;
        $obj            = Shop::DB()->select('tlieferadresse', 'kLieferadresse', $kLieferadresse);

        if (!isset($obj->kLieferadresse)) {
            return 0;
        }

        $this->fromObject($obj);

        // Anrede mappen
        $this->cAnredeLocalized = mappeKundenanrede($this->cAnrede, 0, $this->kKunde);
        $this->angezeigtesLand  = ISO2land($this->cLand);
        if ($this->kLieferadresse > 0) {
            $this->decrypt();
        }

        executeHook(HOOK_LIEFERADRESSE_CLASS_LOADFROMDB);

        return $this;
    }

    /**
     * Fügt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @access public
     * @return int - Key von eingefügter Lieferadresse
     */
    public function insertInDB()
    {
        $this->encrypt();
        $obj = $this->toObject();

        $obj->cLand = $this->pruefeLandISO($obj->cLand);

        unset($obj->kLieferadresse, $obj->angezeigtesLand, $obj->cAnredeLocalized);

        $this->kLieferadresse = Shop::DB()->insert('tlieferadresse', $obj);
        $this->decrypt();
        // Anrede mappen
        $this->cAnredeLocalized = $this->mappeAnrede($this->cAnrede);

        return $this->kLieferadresse;
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @access public
     * @return int
     */
    public function updateInDB()
    {
        $this->encrypt();
        $obj = $this->toObject();

        $obj->cLand = $this->pruefeLandISO($obj->cLand);

        unset($obj->angezeigtesLand, $obj->cAnredeLocalized);

        $cReturn = Shop::DB()->update('tlieferadresse', 'kLieferadresse', $obj->kLieferadresse, $obj);
        $this->decrypt();

        // Anrede mappen
        $this->cAnredeLocalized = $this->mappeAnrede($this->cAnrede);

        return $cReturn;
    }

    /**
     * get shipping address
     *
     * @return array
     */
    public function gibLieferadresseAssoc()
    {
        if ($this->kLieferadresse > 0) {
            return $this->toArray();
        }

        return [];
    }
}
