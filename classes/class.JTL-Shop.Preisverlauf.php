<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Preisverlauf
 */
class Preisverlauf
{
    /**
     * @var int
     */
    public $kPreisverlauf;

    /**
     * @var int
     */
    public $kArtikel;

    /**
     * @var float
     */
    public $fPreisPrivat;

    /**
     * @var float
     */
    public $fPreisHaendler;

    /**
     * @var string
     */
    public $dDate;

    /**
     * Konstruktor
     *
     * @param int $kPreisverlauf - Falls angegeben, wird der Preisverlauf mit angegebenem kPreisverlauf aus der DB geholt
     */
    public function __construct($kPreisverlauf = 0)
    {
        if ($kPreisverlauf > 0) {
            $this->loadFromDB($kPreisverlauf);
        }
    }

    /**
     * @param int $kArtikel
     * @param int $kKundengruppe
     * @param int $nMonat
     * @return mixed
     */
    public function gibPreisverlauf($kArtikel, $kKundengruppe, $nMonat)
    {
        $_currency     = null;
        $kArtikel      = (int)$kArtikel;
        $kKundengruppe = (int)$kKundengruppe;
        $nMonat        = (int)$nMonat;
        $cacheID       = 'gpv_' . $kArtikel . '_' . $kKundengruppe . '_' . $nMonat;
        if (($obj_arr = Shop::Cache()->get($cacheID)) === false) {
            $obj_arr = Shop::DB()->query(
                "SELECT tpreisverlauf.fVKNetto, tartikel.fMwst, UNIX_TIMESTAMP(tpreisverlauf.dDate) AS timestamp
                    FROM tpreisverlauf 
                    LEFT JOIN tartikel
                        ON tartikel.kArtikel = tpreisverlauf.kArtikel
                    WHERE tpreisverlauf.kArtikel = " . $kArtikel . "
                        AND tpreisverlauf.kKundengruppe = " . $kKundengruppe . "
                        AND DATE_SUB(now(), INTERVAL " . $nMonat . " MONTH) < tpreisverlauf.dDate
                    ORDER BY tpreisverlauf.dDate DESC", 2
            );
            if (isset($_SESSION['Waehrung'])) {
                $_currency = $_SESSION['Waehrung'];
            }
            if (!isset($_SESSION['Waehrung'])) {
                $_currency = Shop::DB()->select('twaehrung', 'cStandard', 'Y');
            }
            if (is_array($obj_arr)) {
                $dt = new DateTime();
                foreach ($obj_arr as &$_pv) {
                    if (isset($_pv->timestamp)) {
                        $dt->setTimestamp($_pv->timestamp);
                        $_pv->date   = $dt->format('d.m.');
                        $_pv->fPreis = ($_SESSION['Kundengruppe']->nNettoPreise == 1) ?
                            round($_pv->fVKNetto * $_currency->fFaktor, 2) :
                            berechneBrutto($_pv->fVKNetto * $_currency->fFaktor, $_pv->fMwst);
                        $_pv->currency = $_currency->cISO;
                    }
                }
            }
            Shop::Cache()->set($cacheID, $obj_arr, [CACHING_GROUP_ARTICLE, CACHING_GROUP_ARTICLE . '_' . $kArtikel]);
        }

        return $obj_arr;
    }

    /**
     * Setzt Preisverlauf mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @access public
     * @param int $kPreisverlauf
     * @return $this
     */
    public function loadFromDB($kPreisverlauf)
    {
        $obj     = Shop::DB()->select('tpreisverlauf', 'kPreisverlauf', (int)$kPreisverlauf);
        $members = array_keys(get_object_vars($obj));
        foreach ($members as $member) {
            $this->$member = $obj->$member;
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
        $obj = kopiereMembers($this);
        unset($obj->kPreisverlauf);
        $this->kPreisverlauf = Shop::DB()->insert('tpreisverlauf', $obj);

        return $this->kPreisverlauf;
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @return int
     * @access public
     */
    public function updateInDB()
    {
        $obj = kopiereMembers($this);

        return Shop::DB()->update('tpreisverlauf', 'kPreisverlauf', $obj->kPreisverlauf, $obj);
    }

    /**
     * setzt Daten aus Sync POST request.
     *
     * @return bool - true, wenn alle notwendigen Daten vorhanden, sonst false
     */
    public function setzePostDaten()
    {
        $this->kPreisverlauf  = (int)$_POST['PStaffelKey'];
        $this->kArtikel       = (int)$_POST['KeyArtikel'];
        $this->fPreisPrivat   = (float)$_POST['ArtikelVKBrutto'];
        $this->fPreisHaendler = (float)$_POST['ArtikelVKHaendlerBrutto'];
        $this->dDate          = 'now()';

        return ($this->kArtikel > 0);
    }
}
