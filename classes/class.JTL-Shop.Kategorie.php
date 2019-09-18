<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Kategorie
 */
class Kategorie
{
    /**
     * @var int
     */
    public $kKategorie;

    /**
     * @var int
     */
    public $kOberKategorie;

    /**
     * @var int
     */
    public $nSort;

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
    public $cBeschreibung;

    /**
     * @var string
     */
    public $cURL;

    /**
     * @var string
     */
    public $cURLFull;

    /**
     * @var string
     */
    public $cKategoriePfad;

    /**
     * @var array
     */
    public $cKategoriePfad_arr;

    /**
     * @var string
     */
    public $cBildURL;

    /**
     * @var string
     */
    public $cBild;

    /**
     * @var int
     */
    public $nBildVorhanden;

    /**
     * @var array
     * @deprecated since version 4.05 - usage of KategorieAttribute is deprecated, use categoryFunctionAttributes instead
     */
    public $KategorieAttribute;

    /**
     * @var array - value/key pair
     */
    public $categoryFunctionAttributes;

    /**
     * @var array of objects
     */
    public $categoryAttributes;

    /**
     * @var int
     */
    public $bUnterKategorien = 0;

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
    public $cTitleTag;

    /**
     * @var int
     */
    public $kSprache;

    /**
     * @var string
     */
    public $cKurzbezeichnung = '';

    /**
     * @param int  $kKategorie Falls angegeben, wird der Kategorie mit angegebenem kKategorie aus der DB geholt
     * @param int  $kSprache
     * @param int  $kKundengruppe
     * @param bool $noCache
     */
    public function __construct($kKategorie = 0, $kSprache = 0, $kKundengruppe = 0, $noCache = false)
    {
        $this->kSprache = (int)$kSprache;
        if ((int)$kKategorie > 0) {
            $this->loadFromDB((int)$kKategorie, (int)$kSprache, (int)$kKundengruppe, false, $noCache);
        }
    }

    /**
     * Setzt Kategorie mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @param int $kKategorie Primary Key
     * @param int $kSprache
     * @param int $kKundengruppe
     * @param bool $recall - used for internal hacking only
     * @param bool $noCache
     * @return $this
     */
    public function loadFromDB($kKategorie, $kSprache = 0, $kKundengruppe = 0, $recall = false, $noCache = false)
    {
        $oSpracheTmp            = null;
        $oKategorieAttribut_arr = null;
        if (!$kKundengruppe) {
            $kKundengruppe = Kundengruppe::getDefaultGroupID();
            if (!isset($_SESSION['Kundengruppe'])) { //auswahlassistent admin fix
                $_SESSION['Kundengruppe'] = new stdClass();
            }
            $_SESSION['Kundengruppe']->kKundengruppe = $kKundengruppe;
        }
        if (!$kSprache) {
            $kSprache = Shop::getLanguage();
            if (!$kSprache) {
                $oSpracheTmp = gibStandardsprache(true);
                $kSprache    = $oSpracheTmp->kSprache;
            }
        }
        $kSprache       = (int)$kSprache;
        $kKundengruppe  = (int)$kKundengruppe;
        $kKategorie     = (int)$kKategorie;
        $this->kSprache = $kSprache;
        //exculpate session
        $cacheID = CACHING_GROUP_CATEGORY . '_' . $kKategorie . '_' . $kSprache . '_cg_' . $kKundengruppe . '_ssl_' . pruefeSSL();
        if (!$noCache && ($category = Shop::Cache()->get($cacheID)) !== false) {
            foreach (get_object_vars($category) as $k => $v) {
                $this->$k = $v;
            }
            executeHook(HOOK_KATEGORIE_CLASS_LOADFROMDB, [
                    'oKategorie' => &$this,
                    'cacheTags'  => [],
                    'cached'     => true
                ]
            );

            return $this;
        }
        // Nicht Standardsprache?
        $oSQLKategorie          = new stdClass();
        $oSQLKategorie->cSELECT = '';
        $oSQLKategorie->cJOIN   = '';
        $oSQLKategorie->cWHERE  = '';
        if (!$recall && $kSprache > 0 && !standardspracheAktiv(false, $kSprache)) {
            $oSQLKategorie->cSELECT = 'tkategoriesprache.cName AS cName_spr, 
                tkategoriesprache.cBeschreibung AS cBeschreibung_spr, 
                tkategoriesprache.cMetaDescription AS cMetaDescription_spr,
                tkategoriesprache.cMetaKeywords AS cMetaKeywords_spr, 
                tkategoriesprache.cTitleTag AS cTitleTag_spr, ';
            $oSQLKategorie->cJOIN  = ' JOIN tkategoriesprache ON tkategoriesprache.kKategorie = tkategorie.kKategorie';
            $oSQLKategorie->cWHERE = ' AND tkategoriesprache.kSprache = ' . $kSprache;
        }
        $oKategorie = Shop::DB()->query(
            "SELECT tkategorie.kKategorie, " . $oSQLKategorie->cSELECT . " tkategorie.kOberKategorie, 
                tkategorie.nSort, tkategorie.dLetzteAktualisierung,
                tkategorie.cName, tkategorie.cBeschreibung, tseo.cSeo, tkategoriepict.cPfad, tkategoriepict.cType
                FROM tkategorie
                " . $oSQLKategorie->cJOIN . "
                LEFT JOIN tkategoriesichtbarkeit ON tkategoriesichtbarkeit.kKategorie = tkategorie.kKategorie
                    AND tkategoriesichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                LEFT JOIN tseo ON tseo.cKey = 'kKategorie'
                    AND tseo.kKey = " . $kKategorie . "
                    AND tseo.kSprache = " . $kSprache . "
                LEFT JOIN tkategoriepict ON tkategoriepict.kKategorie = tkategorie.kKategorie
                WHERE tkategorie.kKategorie = " . $kKategorie . "
                    " . $oSQLKategorie->cWHERE . "
                    AND tkategoriesichtbarkeit.kKategorie IS NULL", 1
        );
        if ($oKategorie === null || $oKategorie === false) {
            if (!$recall && !standardspracheAktiv(false, $kSprache)) {
                if (defined('EXPERIMENTAL_MULTILANG_SHOP') && EXPERIMENTAL_MULTILANG_SHOP === true) {
                    if ($oSpracheTmp === null) {
                        $oSpracheTmp = gibStandardsprache();
                    }
                    $kDefaultLang = (int)$oSpracheTmp->kSprache;
                    if ($kDefaultLang !== $kSprache) {
                        return $this->loadFromDB($kKategorie, $kDefaultLang, $kKundengruppe, true);
                    }
                } elseif (KategorieHelper::categoryExists($kKategorie)) {
                    return $this->loadFromDB($kKategorie, $kSprache, $kKundengruppe, true);
                }
            }

            return $this;
        }

        //EXPERIMENTAL_MULTILANG_SHOP
        if ((!isset($oKategorie->cSeo) || $oKategorie->cSeo === null || $oKategorie->cSeo === '') &&
            defined('EXPERIMENTAL_MULTILANG_SHOP') && EXPERIMENTAL_MULTILANG_SHOP === true
        ) {
            $kDefaultLang = $oSpracheTmp !== null ? $oSpracheTmp->kSprache : gibStandardsprache()->kSprache;
            $kDefaultLang = (int)$kDefaultLang;
            if ($kSprache !== $kDefaultLang) {
                $oSeo = Shop::DB()->select(
                    'tseo',
                    'cKey', 'kKategorie',
                    'kSprache', (int)$kDefaultLang,
                    'kKey', (int)$oKategorie->kKategorie
                );
                if (isset($oSeo->cSeo)) {
                    $oKategorie->cSeo = $oSeo->cSeo;
                }
            }
        }
        //EXPERIMENTAL_MULTILANG_SHOP END

        if (isset($oKategorie->kKategorie) && $oKategorie->kKategorie > 0) {
            $this->mapData($oKategorie);
        }
        $shopURL = Shop::getURL() . '/';
        // URL bauen
        $this->cURL     = baueURL($this, URLART_KATEGORIE);
        $this->cURLFull = baueURL($this, URLART_KATEGORIE, 0, false, true);
        // Baue Kategoriepfad
        $this->cKategoriePfad_arr = gibKategoriepfad($this, $kKundengruppe, $kSprache, false);
        $this->cKategoriePfad     = implode(' > ', $this->cKategoriePfad_arr);
        // Bild holen
        $this->cBildURL       = BILD_KEIN_KATEGORIEBILD_VORHANDEN;
        $this->cBild          = $shopURL . BILD_KEIN_KATEGORIEBILD_VORHANDEN;
        $this->nBildVorhanden = 0;
        if (isset($oKategorie->cPfad) && strlen($oKategorie->cPfad) > 0) {
            $this->cBildURL       = PFAD_KATEGORIEBILDER . $oKategorie->cPfad;
            $this->cBild          = $shopURL . PFAD_KATEGORIEBILDER . $oKategorie->cPfad;
            $this->nBildVorhanden = 1;
        }
        // Attribute holen
        $this->categoryFunctionAttributes = [];
        $this->categoryAttributes         = [];
        if ($this->kKategorie > 0) {
            $oKategorieAttribut_arr = Shop::DB()->query(
                "SELECT COALESCE(tkategorieattributsprache.cName, tkategorieattribut.cName) cName,
                        COALESCE(tkategorieattributsprache.cWert, tkategorieattribut.cWert) cWert,
                        tkategorieattribut.bIstFunktionsAttribut, tkategorieattribut.nSort
                    FROM tkategorieattribut
                    LEFT JOIN tkategorieattributsprache 
                        ON tkategorieattributsprache.kAttribut = tkategorieattribut.kKategorieAttribut
                        AND tkategorieattributsprache.kSprache = " . $kSprache . "
                    WHERE kKategorie = " . (int)$this->kKategorie . "
                    ORDER BY tkategorieattribut.bIstFunktionsAttribut DESC, tkategorieattribut.nSort", 2
            );
        }
        if ($oKategorieAttribut_arr !== null && is_array($oKategorieAttribut_arr) && count($oKategorieAttribut_arr) > 0) {
            foreach ($oKategorieAttribut_arr as $oKategorieAttribut) {
                // Aus Kompatibilitätsgründen findet hier KEINE Trennung zwischen Funktions- und lokalisierten Attributen statt
                if ($oKategorieAttribut->cName === 'meta_title') {
                    $this->cTitleTag = $oKategorieAttribut->cWert;
                } elseif ($oKategorieAttribut->cName === 'meta_description') {
                    $this->cMetaDescription = $oKategorieAttribut->cWert;
                } elseif ($oKategorieAttribut->cName === 'meta_keywords') {
                    $this->cMetaKeywords = $oKategorieAttribut->cWert;
                }
                if ($oKategorieAttribut->bIstFunktionsAttribut) {
                    $this->categoryFunctionAttributes[strtolower($oKategorieAttribut->cName)] = $oKategorieAttribut->cWert;
                } else {
                    $this->categoryAttributes[strtolower($oKategorieAttribut->cName)] = $oKategorieAttribut;
                }
            }
        }
        /** @deprecated since version 4.05 - usage of KategorieAttribute is deprecated, use categoryFunctionAttributes instead */
        $this->KategorieAttribute = &$this->categoryFunctionAttributes;
        // lokalisieren
        if ($kSprache > 0 && !standardspracheAktiv()) {
            if (isset($oKategorie->cName_spr) && strlen($oKategorie->cName_spr) > 0) {
                $this->cName = $oKategorie->cName_spr;
                unset($oKategorie->cName_spr);
            }
            if (isset($oKategorie->cBeschreibung_spr) && strlen($oKategorie->cBeschreibung_spr) > 0) {
                $this->cBeschreibung = $oKategorie->cBeschreibung_spr;
                unset($oKategorie->cBeschreibung_spr);
            }
            if (isset($oKategorie->cMetaDescription_spr) && strlen($oKategorie->cMetaDescription_spr) > 0) {
                $this->cMetaDescription = $oKategorie->cMetaDescription_spr;
                unset($oKategorie->cMetaDescription_spr);
            }
            if (isset($oKategorie->cMetaKeywords_spr) && strlen($oKategorie->cMetaKeywords_spr) > 0) {
                $this->cMetaKeywords = $oKategorie->cMetaKeywords_spr;
                unset($oKategorie->cMetaKeywords_spr);
            }
            if (isset($oKategorie->cTitleTag_spr) && strlen($oKategorie->cTitleTag_spr) > 0) {
                $this->cTitleTag = $oKategorie->cTitleTag_spr;
                unset($oKategorie->cTitleTag_spr);
            }
        }
        //hat die Kat Unterkategorien?
        if ($this->kKategorie > 0) {
            $oUnterkategorien = Shop::DB()->select('tkategorie', 'kOberKategorie', (int)$this->kKategorie);
            if (isset($oUnterkategorien->kKategorie)) {
                $this->bUnterKategorien = 1;
            }
        }
        //interne Verlinkung $#k:X:Y#$
        $this->cBeschreibung         = parseNewsText($this->cBeschreibung);
        // Kurzbezeichnung
        $this->cKurzbezeichnung      = (!empty($this->categoryAttributes[ART_ATTRIBUT_SHORTNAME]) && !empty($this->categoryAttributes[ART_ATTRIBUT_SHORTNAME]->cWert))
            ? $this->categoryAttributes[ART_ATTRIBUT_SHORTNAME]->cWert
            : $this->cName;
        $cacheTags                   = [CACHING_GROUP_CATEGORY . '_' . $kKategorie, CACHING_GROUP_CATEGORY];
        executeHook(HOOK_KATEGORIE_CLASS_LOADFROMDB, [
            'oKategorie' => &$this,
            'cacheTags'  => &$cacheTags,
            'cached'     => false
        ]);
        if (!$noCache) {
            Shop::Cache()->set($cacheID, $this, $cacheTags);
        }

        return $this;
    }

    /**
     * add category into db
     *
     * @return int
     */
    public function insertInDB()
    {
        $obj                        = new stdClass();
        $obj->kKategorie            = $this->kKategorie;
        $obj->cSeo                  = $this->cSeo;
        $obj->cName                 = $this->cName;
        $obj->cBeschreibung         = $this->cBeschreibung;
        $obj->kOberKategorie        = $this->kOberKategorie;
        $obj->nSort                 = $this->nSort;
        $obj->dLetzteAktualisierung = 'now()';

        return Shop::DB()->insert('tkategorie', $obj);
    }

    /**
     * update category in db
     *
     * @return int
     */
    public function updateInDB()
    {
        $obj                        = new stdClass();
        $obj->kKategorie            = $this->kKategorie;
        $obj->cSeo                  = $this->cSeo;
        $obj->cName                 = $this->cName;
        $obj->cBeschreibung         = $this->cBeschreibung;
        $obj->kOberKategorie        = $this->kOberKategorie;
        $obj->nSort                 = $this->nSort;
        $obj->dLetzteAktualisierung = 'now()';

        return Shop::DB()->update('tkategorie', 'kKategorie', $obj->kKategorie, $obj);
    }

    /**
     * set data from given object to category
     *
     * @param object $obj
     * @return $this
     */
    public function mapData($obj)
    {
        if (is_array(get_object_vars($obj))) {
            $members = array_keys(get_object_vars($obj));
            foreach ($members as $member) {
                if ($member === 'cBeschreibung') {
                    $this->$member = parseNewsText($obj->$member);
                } else {
                    $this->$member = $obj->$member;
                }
            }
        }

        return $this;
    }

    /**
     * check if child categories exist for current category
     *
     * @return bool - true, wenn Unterkategorien existieren
     */
    public function existierenUnterkategorien()
    {
        return ($this->bUnterKategorien > 0);
    }

    /**
     * get category image
     *
     * @param bool $full
     * @return string|null
     */
    public function getKategorieBild($full = false)
    {
        if ($this->kKategorie > 0) {
            if (!empty($this->cBildURL)) {
                $res = $this->cBildURL;
            } else {
                $cacheID = 'gkb_' . $this->kKategorie;
                if (($res = Shop::Cache()->get($cacheID)) === false) {
                    $resObj = Shop::DB()->select('tkategoriepict', 'kKategorie', (int)$this->kKategorie);
                    $res    = (isset($resObj->cPfad) && $resObj->cPfad)
                        ? PFAD_KATEGORIEBILDER . $resObj->cPfad
                        : BILD_KEIN_KATEGORIEBILD_VORHANDEN;
                    Shop::Cache()->set($cacheID, $res, [CACHING_GROUP_CATEGORY . '_' . $this->kKategorie, CACHING_GROUP_CATEGORY]);
                }
            }

            return ($full === false)
                ? $res
                : (Shop::getURL() . '/' . $res);
        }

        return null;
    }

    /**
     * check if is child category
     *
     * @return bool|int
     */
    public function istUnterkategorie()
    {
        if ($this->kKategorie > 0) {
            if ($this->kOberKategorie !== null && $this->kOberKategorie > 0) {
                return (int)$this->kOberKategorie;
            }
            $oObj = Shop::DB()->query(
                "SELECT kOberKategorie
                    FROM tkategorie
                    WHERE kOberKategorie > 0
                        AND kKategorie = " . (int)$this->kKategorie, 1
            );

            return isset($oObj->kOberKategorie) ? (int)$oObj->kOberKategorie : false;
        }

        return false;
    }

    /**
     * set data from sync POST request
     *
     * @return bool - true, wenn alle notwendigen Daten vorhanden, sonst false
     */
    public function setzePostDaten()
    {
        $this->kKategorie     = (int)$_POST['KeyKategorie'];
        $this->kOberKategorie = (int)$_POST['KeyOberKategorie'];
        $this->cName          = StringHandler::htmlentities(StringHandler::filterXSS($_POST['KeyName']));
        $this->cBeschreibung  = StringHandler::htmlentities(StringHandler::filterXSS($_POST['KeyBeschreibung']));
        $this->nSort          = (int)$_POST['Sort'];

        return ($this->kKategorie > 0 && $this->cName);
    }

    /**
     * check if category is visible
     *
     * @param int $categoryId
     * @param int $customerGroupId
     * @return bool
     */
    public static function isVisible($categoryId, $customerGroupId)
    {
        $obj = Shop::DB()->select(
            'tkategoriesichtbarkeit',
            'kKategorie', (int)$categoryId,
            'kKundengruppe', (int)$customerGroupId
        );

        return empty($obj->kKategorie);
    }
}
