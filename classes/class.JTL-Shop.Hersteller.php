<?php

/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Hersteller
 */
class Hersteller
{
    /**
     * @var int
     */
    public $kHersteller;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cSeo;

    /**
     * @var string
     */
    public $cMetaTitle;

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
    public $cBildpfad;

    /**
     * @var int
     */
    public $nSortNr;

    /**
     * @var string
     */
    public $nGlobal;

    /**
     * @var string
     */
    public $cURL;

    /**
     * @var string
     */
    public $cBildpfadKlein;

    /**
     * @var string
     */
    public $cBildpfadNormal;

    /**
     * Konstruktor
     *
     * @param int  $kHersteller - Falls angegeben, wird das Merkmal mit angegebenem kMerkmal aus der DB geholt
     * @param int  $kSprache
     * @param bool $noCache - set to true to avoid caching
     */
    public function __construct($kHersteller = 0, $kSprache = 0, $noCache = false)
    {
        if ((int)$kHersteller > 0) {
            $this->loadFromDB($kHersteller, $kSprache, $noCache);
        }
    }

    /**
     * @param stdClass $obj
     * @param bool     $extras
     * @return $this
     */
    public function loadFromObject(stdClass $obj, $extras = true)
    {
        $members = array_keys(get_object_vars($obj));
        if (is_array($members) && count($members) > 0) {
            foreach ($members as $member) {
                $this->{$member} = $obj->{$member};
            }
        }
        if ($extras) {
            $this->getExtras($obj);
        }

        return $this;
    }

    /**
     * Setzt Merkmal mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @param int  $kHersteller
     * @param int  $kSprache
     * @param bool $noCache
     * @return $this
     */
    public function loadFromDB($kHersteller, $kSprache = 0, $noCache = false)
    {
        //noCache param to avoid problem with de-serialization of class properties with jtl search
        $kSprache = ((int)$kSprache > 0) ? (int)$kSprache : Shop::getLanguage();
        if ($kSprache === 0) {
            $oSprache = gibStandardsprache();
            $kSprache = (int)$oSprache->kSprache;
        }
        $kHersteller = (int)$kHersteller;
        $kSprache    = (int)$kSprache;
        $cacheID     = 'manuf_' . $kHersteller . '_' . $kSprache . Shop::Cache()->getBaseID();
        $cacheTags   = [CACHING_GROUP_MANUFACTURER];
        $cached      = true;
        if ($noCache === true || ($oHersteller = Shop::Cache()->get($cacheID)) === false) {
            $oHersteller = Shop::DB()->query(
                "SELECT thersteller.kHersteller, thersteller.cName, thersteller.cHomepage, thersteller.nSortNr, 
                    thersteller.cBildpfad, therstellersprache.cMetaTitle, therstellersprache.cMetaKeywords, 
                    therstellersprache.cMetaDescription, therstellersprache.cBeschreibung, tseo.cSeo
                    FROM thersteller
                    LEFT JOIN therstellersprache ON therstellersprache.kHersteller = thersteller.kHersteller
                        AND therstellersprache.kSprache = " . $kSprache . "
                    LEFT JOIN tseo ON tseo.kKey = thersteller.kHersteller
                        AND tseo.cKey = 'kHersteller'
                        AND tseo.kSprache = " . $kSprache . "
                    WHERE thersteller.kHersteller = " . $kHersteller, 1
            );
            $cached = false;
            executeHook(HOOK_HERSTELLER_CLASS_LOADFROMDB, [
                    'oHersteller' => &$oHersteller,
                    'cached'      => false,
                    'cacheTags'   => &$cacheTags
                ]
            );
            Shop::Cache()->set($cacheID, $oHersteller, $cacheTags);
        }
        if ($cached === true) {
            executeHook(HOOK_HERSTELLER_CLASS_LOADFROMDB, [
                    'oHersteller' => &$oHersteller,
                    'cached'      => true,
                    'cacheTags'   => &$cacheTags
                ]
            );
        }
        if ($oHersteller !== false) {
            $this->loadFromObject($oHersteller);
        }

        return $this;
    }

    /**
     * @param stdClass $obj
     * @return $this
     */
    public function getExtras(stdClass $obj)
    {
        if (isset($obj->kHersteller) && $obj->kHersteller > 0) {
            // URL bauen
            $this->cURL = (isset($obj->cSeo) && strlen($obj->cSeo) > 0)
                ? Shop::getURL() . '/' . $obj->cSeo
                : Shop::getURL() . '/index.php?h=' . $obj->kHersteller;
            $this->cBeschreibung = parseNewsText($this->cBeschreibung);
        }
        if (strlen($this->cBildpfad) > 0) {
            $this->cBildpfadKlein  = PFAD_HERSTELLERBILDER_KLEIN . $this->cBildpfad;
            $this->cBildpfadNormal = PFAD_HERSTELLERBILDER_NORMAL . $this->cBildpfad;
        } else {
            $this->cBildpfadKlein  = BILD_KEIN_HERSTELLERBILD_VORHANDEN;
            $this->cBildpfadNormal = BILD_KEIN_HERSTELLERBILD_VORHANDEN;
        }

        return $this;
    }

    /**
     * @param bool $productLookup
     * @return array
     */
    public static function getAll($productLookup = true)
    {
        $sqlWhere = '';
        $kSprache = isset($_SESSION['kSprache']) ? (int)$_SESSION['kSprache'] : Shop::getLanguage();
        if ($productLookup) {
            $sqlWhere = "WHERE EXISTS (
                            SELECT 1
                            FROM tartikel
                            WHERE tartikel.kHersteller = thersteller.kHersteller
                                " . gibLagerfilter() . "
                                AND NOT EXISTS (
                                SELECT 1 FROM tartikelsichtbarkeit
                                WHERE tartikelsichtbarkeit.kArtikel = tartikel.kArtikel
                                    AND tartikelsichtbarkeit.kKundengruppe = {$_SESSION['Kundengruppe']->kKundengruppe}
							)
                        )";
        }
        $objs = Shop::DB()->query(
            "SELECT thersteller.kHersteller, thersteller.cName, thersteller.cHomepage, thersteller.nSortNr, 
                thersteller.cBildpfad, therstellersprache.cMetaTitle, therstellersprache.cMetaKeywords, 
                therstellersprache.cMetaDescription, therstellersprache.cBeschreibung, tseo.cSeo
                FROM thersteller
                LEFT JOIN therstellersprache ON therstellersprache.kHersteller = thersteller.kHersteller
                    AND therstellersprache.kSprache = {$kSprache}
                LEFT JOIN tseo ON tseo.kKey = thersteller.kHersteller
                    AND tseo.cKey = 'kHersteller'
                    AND tseo.kSprache = {$kSprache}
                {$sqlWhere}
                ORDER BY thersteller.nSortNr, thersteller.cName", 2
        );
        $results = [];
        if (is_array($objs)) {
            foreach ($objs as $obj) {
                $hersteller = new self(0, 0, true);
                $hersteller->loadFromObject($obj);
                $results[] = $hersteller;
            }
        }

        return $results;
    }
}
