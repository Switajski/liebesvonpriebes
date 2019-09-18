<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class MerkmalWert
 */
class MerkmalWert
{
    /**
     * @var int
     */
    public $kSprache;

    /**
     * @var int
     */
    public $kMerkmalWert;

    /**
     * @var int
     */
    public $kMerkmal;

    /**
     * @var int
     */
    public $nSort;

    /**
     * @var string
     */
    public $cWert;

    /**
     * @var string
     */
    public $cMetaKeywords;

    /**
     * @var string
     */
    public $cMetaDescription;

    /**
     * @var string
     */
    public $cBeschreibung;

    /**
     * @var string
     */
    public $cSeo;

    /**
     * @var string
     */
    public $cURL;

    /**
     * @var string
     */
    public $cBildpfad;

    /**
     * @var string
     */
    public $cBildpfadKlein;

    /**
     * @var string
     */
    public $nBildKleinVorhanden;

    /**
     * @var string
     */
    public $cBildpfadNormal;

    /**
     * @var string
     */
    public $nBildNormalVorhanden;

    /**
     * Konstruktor
     *
     * @param int $kMerkmalWert - Falls angegeben, wird der MerkmalWert mit angegebenem kMerkmalWert aus der DB geholt
     */
    public function __construct($kMerkmalWert = 0)
    {
        if ($kMerkmalWert > 0) {
            $this->loadFromDB($kMerkmalWert);
        }
    }

    /**
     * Setzt MerkmalWert mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @access public
     * @param int $kMerkmalWert
     * @return $this
     */
    public function loadFromDB($kMerkmalWert)
    {
        $kSprache = null;
        if (isset($_SESSION['kSprache'])) {
            $kSprache = (int)$_SESSION['kSprache'];
        }
        if (!$kSprache) {
            $oSprache = gibStandardsprache();
            if (isset($oSprache->kSprache) && $oSprache->kSprache > 0) {
                $kSprache = (int)$oSprache->kSprache;
            }
        }
        $kMerkmalWert = (int)$kMerkmalWert;
        $kSprache     = (int)$kSprache;
        $id           = 'mmw_' . $kMerkmalWert . '_' . $kSprache;
        if (Shop::has($id)) {
            foreach (get_object_vars(Shop::get($id)) as $k => $v) {
                $this->$k = $v;
            }

            return $this;
        }
        $kStandardSprache = (int)gibStandardsprache()->kSprache;
        if ($kSprache !== $kStandardSprache) {
            $cSelect = "COALESCE(fremdSprache.kSprache, standardSprache.kSprache) AS kSprache, 
                        COALESCE(fremdSprache.cWert, standardSprache.cWert) AS cWert,
                        COALESCE(fremdSprache.cMetaTitle, standardSprache.cMetaTitle) AS cMetaTitle, 
                        COALESCE(fremdSprache.cMetaKeywords, standardSprache.cMetaKeywords) AS cMetaKeywords,
                        COALESCE(fremdSprache.cMetaDescription, standardSprache.cMetaDescription) AS cMetaDescription, 
                        COALESCE(fremdSprache.cBeschreibung, standardSprache.cBeschreibung) AS cBeschreibung,
                        COALESCE(fremdSprache.cSeo, standardSprache.cSeo) AS cSeo";
            $cJoin   = "INNER JOIN tmerkmalwertsprache AS standardSprache 
                            ON standardSprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                            AND standardSprache.kSprache = " . $kStandardSprache . "
                        LEFT JOIN tmerkmalwertsprache AS fremdSprache 
                            ON fremdSprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                            AND fremdSprache.kSprache = " . $kSprache . "";
        } else {
            $cSelect = "tmerkmalwertsprache.kSprache, tmerkmalwertsprache.cWert, tmerkmalwertsprache.cMetaTitle,
                        tmerkmalwertsprache.cMetaKeywords, tmerkmalwertsprache.cMetaDescription,
                        tmerkmalwertsprache.cBeschreibung, tmerkmalwertsprache.cSeo";
            $cJoin   = "INNER JOIN tmerkmalwertsprache ON tmerkmalwertsprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                            AND tmerkmalwertsprache.kSprache = " . $kSprache;
        }
        $oMerkmalWert = Shop::DB()->query(
            "SELECT tmerkmalwert.*, {$cSelect}
                FROM tmerkmalwert
                {$cJoin}
                WHERE tmerkmalwert.kMerkmalWert = {$kMerkmalWert}", 1
        );
        if (isset($oMerkmalWert->kMerkmalWert) && $oMerkmalWert->kMerkmalWert > 0) {
            $cMember_arr = array_keys(get_object_vars($oMerkmalWert));
            foreach ($cMember_arr as $cMember) {
                $this->$cMember = $oMerkmalWert->$cMember;
            }
            $this->cURL = baueURL($this, URLART_MERKMAL);
            executeHook(HOOK_MERKMALWERT_CLASS_LOADFROMDB, ['oMerkmalWert' => &$this]);
        }

        $this->cBildpfadKlein       = BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
        $this->nBildKleinVorhanden  = 0;
        $this->cBildpfadNormal      = BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
        $this->nBildNormalVorhanden = 0;
        $this->nSort                = (int)$this->nSort;
        $this->kSprache             = (int)$this->kSprache;
        $this->kMerkmal             = (int)$this->kMerkmal;
        $this->kMerkmalWert         = (int)$this->kMerkmalWert;
        if ($this->cBildpfad !== null && strlen($this->cBildpfad) > 0) {
            if (file_exists(PFAD_MERKMALWERTBILDER_KLEIN . $this->cBildpfad)) {
                $this->cBildpfadKlein      = PFAD_MERKMALWERTBILDER_KLEIN . $this->cBildpfad;
                $this->nBildKleinVorhanden = 1;
            }
            if (file_exists(PFAD_MERKMALWERTBILDER_NORMAL . $this->cBildpfad)) {
                $this->cBildpfadNormal      = PFAD_MERKMALWERTBILDER_NORMAL . $this->cBildpfad;
                $this->nBildNormalVorhanden = 1;
            }
        }
        Shop::set($id, $this);

        return $this;
    }

    /**
     * @param int $kMerkmal
     * @return array
     */
    public function holeAlleMerkmalWerte($kMerkmal)
    {
        $oMerkmalWert_arr = [];
        if ($kMerkmal > 0) {
            $kSprache = Shop::getLanguage();
            if (!$kSprache) {
                $oSprache = gibStandardsprache();
                if (isset($oSprache->kSprache) && $oSprache->kSprache > 0) {
                    $kSprache = (int)$oSprache->kSprache;
                }
            }
            $kStandardSprache = (int)gibStandardsprache()->kSprache;
            if ($kSprache !== $kStandardSprache) {
                $cSelect = "COALESCE(fremdSprache.kSprache, standardSprache.kSprache) AS kSprache, 
                            COALESCE(fremdSprache.cWert, standardSprache.cWert) AS cWert,
                            COALESCE(fremdSprache.cMetaTitle, standardSprache.cMetaTitle) AS cMetaTitle, 
                            COALESCE(fremdSprache.cMetaKeywords, standardSprache.cMetaKeywords) AS cMetaKeywords,
                            COALESCE(fremdSprache.cMetaDescription, standardSprache.cMetaDescription) AS cMetaDescription, 
                            COALESCE(fremdSprache.cBeschreibung, standardSprache.cBeschreibung) AS cBeschreibung,
                            COALESCE(fremdSprache.cSeo, standardSprache.cSeo) AS cSeo";
                $cJoin   = "INNER JOIN tmerkmalwertsprache AS standardSprache 
                                ON standardSprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                                AND standardSprache.kSprache = " . $kStandardSprache . "
                        LEFT JOIN tmerkmalwertsprache AS fremdSprache 
                            ON fremdSprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                            AND fremdSprache.kSprache = " . $kSprache . "";
            } else {
                $cSelect = "tmerkmalwertsprache.kSprache, tmerkmalwertsprache.cWert, tmerkmalwertsprache.cMetaTitle,
                        tmerkmalwertsprache.cMetaKeywords, tmerkmalwertsprache.cMetaDescription,
                        tmerkmalwertsprache.cBeschreibung, tmerkmalwertsprache.cSeo";
                $cJoin   = "INNER JOIN tmerkmalwertsprache ON tmerkmalwertsprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                                AND tmerkmalwertsprache.kSprache = " . $kSprache;
            }
            $oMerkmalWert_arr = Shop::DB()->query(
                "SELECT tmerkmalwert.*, {$cSelect}
                    FROM tmerkmalwert
                    {$cJoin}
                    WHERE tmerkmalwert.kMerkmal = " . (int)$kMerkmal . "
                    ORDER BY tmerkmalwert.nSort", 2
            );

            if (is_array($oMerkmalWert_arr) && count($oMerkmalWert_arr) > 0) {
                foreach ($oMerkmalWert_arr as $i => $oMerkmalWert) {
                    $oMerkmalWert_arr[$i]->cURL = baueURL($oMerkmalWert, URLART_MERKMAL);

                    if (isset($oMerkmalWert->cBildpfad) && strlen($oMerkmalWert->cBildpfad) > 0) {
                        $oMerkmalWert_arr[$i]->cBildpfadKlein  = PFAD_MERKMALWERTBILDER_KLEIN . $oMerkmalWert->cBildpfad;
                        $oMerkmalWert_arr[$i]->cBildpfadNormal = PFAD_MERKMALWERTBILDER_NORMAL . $oMerkmalWert->cBildpfad;
                    } else {
                        $oMerkmalWert_arr[$i]->cBildpfadKlein = BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
                        $oMerkmalWert_arr[$i]->cBildpfadGross = BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
                    }
                }
            }
        }

        return $oMerkmalWert_arr;
    }
}
