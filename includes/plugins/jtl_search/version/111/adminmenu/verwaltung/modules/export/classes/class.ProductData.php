<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'interface.IItemData.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.Product.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Preise.php';

/**
 * Description of ProductData
 *
 * @author Andre Vermeulen
 */
class ProductData implements IItemData
{
    /**
     * @var IDebugger
     */
    private $oDebugger;

    /**
     * @var array
     */
    private $oLanguage_arr;

    /**
     * @var array
     */
    private $oUsergroup_arr;

    /**
     * @var object
     */
    private $oProduct;

    /**
     * @var bool
     */
    private $bDebug = true;

    /**
     * @var null|float
     */
    private $fBestsellerMax = null;

    /**
     * @var array
     */
    private $oNichtSichtbar_arr = [];

    /**
     * @param IDebugger $oDebugger
     * @param int       $kArtikel
     */
    public function __construct(IDebugger $oDebugger, $kArtikel = 0)
    {
        try {
            $this->oDebugger = $oDebugger;

            $this->loadLanguages();
            $this->loadUsergroups();

            if ($kArtikel > 0) {
                $this->loadFromDB($kArtikel);
            }
        } catch (Exception $oEx) {
            //@todo: Errorhandling
            Shop::dbg($oEx->getMessage(), true, 'Exception when constructing debugger:');
        }
    }

    /**
     * @return int
     */
    public function getCount()
    {
        $oRes = Shop::DB()->query("
            SELECT COUNT(*) AS nAnzahl
                FROM tartikel
                LEFT JOIN tartikelattribut 
                    ON tartikelattribut.kArtikel = tartikel.kArtikel
                    AND tartikelattribut.cName = '" . JTLSEARCH_PRODUCT_EXCLUDE_ATTR . "'
                WHERE tartikelattribut.kArtikel IS NULL " . gibLagerfilter(), 1
        );
        if ($oRes !== false && $oRes->nAnzahl > 0) {
            return intval($oRes->nAnzahl);
        }

        return 0;
    }

    /**
     * @param int  $nLimitN
     * @param  int $nLimitM
     * @return array
     */
    public static function getItemKeys($nLimitN, $nLimitM)
    {
        $nLimitN = (int)$nLimitN;
        $nLimitM = (int)$nLimitM;

        $oRes = Shop::DB()->query("
                SELECT tartikel.kArtikel AS kItem
                    FROM tartikel
                    LEFT JOIN tartikelattribut ON tartikelattribut.kArtikel = tartikel.kArtikel
                        AND tartikelattribut.cName = '" . JTLSEARCH_PRODUCT_EXCLUDE_ATTR . "'
                    WHERE tartikelattribut.kArtikel IS NULL " . gibLagerfilter()."
                    ORDER BY tartikel.kArtikel LIMIT {$nLimitN}, {$nLimitM}", 2
        );
        if ($oRes !== false && count($oRes) > 0) {
            return $oRes;
        }

        return [];
    }

    /**
     * @return mixed
     * @throws Exception
     */
    private function getDefaultLanguage()
    {
        if (!count($this->oLanguage_arr) > 0) {
            $this->loadLanguages();
        }

        foreach ($this->oLanguage_arr as $oSprache) {
            if ($oSprache->cShopStandard === 'Y') {
                return $oSprache;
            }
        }

        return null;
    }

    /**
     * @return $this
     * @throws Exception
     */
    private function loadLanguages()
    {
        $this->oLanguage_arr = Shop::DB()->query("
            SELECT tsprache.* 
                FROM tsprache 
                JOIN tjtlsearchexportlanguage 
                    ON tsprache.cISO = tjtlsearchexportlanguage.cISO 
                ORDER BY cShopStandard DESC", 2
        );
        if (count($this->oLanguage_arr) === 0) {
            throw new Exception('Fehler beim Laden der Sprachen: Keine Sprachen vorhanden.', 1);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    private function loadUsergroups()
    {
        $this->oUsergroup_arr = Shop::DB()->query('SELECT * FROM tkundengruppe ORDER BY cStandard DESC', 2);
        if (count($this->oUsergroup_arr) === 0) {
            throw new Exception('Fehler beim Laden der Kundengruppen: Keine Kundengruppen vorhanden.', 1);
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function loadUsergroupVisibility()
    {
        $this->oNichtSichtbar_arr = Shop::DB()->query("
            SELECT kKundengruppe 
                FROM tartikelsichtbarkeit 
                WHERE kArtikel = {$this->oProduct->kArtikel}", 2
        );

        return $this;
    }

    /**
     * @param $kItem
     * @return $this
     */
    public function loadFromDB($kItem)
    {
        $kItem = (int)$kItem;
        try {
            $oRes = Shop::DB()->query("
                SELECT tartikel.kArtikel, tartikel.kHersteller, tartikel.kSteuerklasse, tartikel.kEigenschaftKombi,
                    tartikel.kVaterArtikel, tartikel.kStueckliste, tartikel.kWarengruppe, tartikel.cArtNr,
                    tartikel.cName, tartikel.cBeschreibung, tartikel.cKurzBeschreibung, tartikel.cAnmerkung,
                    tartikel.fLagerbestand, tartikel.fMwSt, tartikel.cBarcode, tartikel.cLagerBeachten,
                    tartikel.cLagerKleinerNull, tartikel.cLagerVariation, tartikel.cTeilbar, tartikel.fPackeinheit,
                    tartikel.cSuchbegriffe, tartikel.cSerie, tartikel.cISBN, tartikel.cASIN, tartikel.cHAN,
                    tartikel.cUPC, tartikel.nIstVater, tartikel.fVPEWert, tartikel.cVPE,
                    (SELECT cPfad FROM tartikelpict WHERE kArtikel = {$kItem} AND nNr = 1 LIMIT 0, 1) AS cPfad,
                    (SELECT cSeo FROM tseo WHERE kKey = {$kItem} 
                        AND kSprache = (
                            SELECT kSprache 
                                FROM tsprache 
                                WHERE cShopStandard = 'Y' LIMIT 0, 1
                            ) 
                        AND cKey = 'kArtikel' LIMIT 0, 1) AS cSeo,
                    teinheit.cName AS cEinheit
                FROM tartikel
                LEFT JOIN teinheit 
                    ON teinheit.kEinheit = tartikel.kEinheit 
                    AND teinheit.kSprache = (SELECT kSprache FROM tsprache WHERE cShopStandard = 'Y' LIMIT 0, 1)
                LEFT JOIN tartikelattribut 
                    ON tartikelattribut.kArtikel = tartikel.kArtikel
                    AND tartikelattribut.cName = '" . JTLSEARCH_PRODUCT_EXCLUDE_ATTR . "'
                WHERE tartikelattribut.kArtikel IS NULL
                    AND tartikel.kArtikel = {$kItem}", 1
            );
            if ($oRes === false) {
                throw new Exception('Fehler beim Laden des Artikel: Kein Datensatz zu kArtikel: ' . $kItem . ' vorhanden.', 1);
            }

            $this->oProduct = $oRes;
            unset($oRes);
            if (isset($this->oProduct->kArtikel) && $this->oProduct->kArtikel > 0) {
                $this->loadUsergroupVisibility()
                     ->loadProductCategoryFromDB()
                     ->loadProductPriceFromDB()
                     ->loadProductLanguagesFromDB()
                     ->loadProductAttributeFromDB()
                     ->loadProductVariationFromDB()
                     ->loadMetaKeywordsFromDB()
                     ->loadSalesRank();
            } else {
                $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ .
                    '; Schwerer Fehler beim Exportieren! $this->oProduct = ' . var_export($this->oProduct, true), JTLLOG_LEVEL_ERROR);
            }
        } catch (Exception $oEx) {
            $oReturnObj              = new stdClass();
            $oReturnObj->nReturnCode = 0;
            $oReturnObj->cMessage    = $oEx->getMessage();
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; ' .
                json_encode($oReturnObj), JTLLOG_LEVEL_ERROR);
        }

        return $this;
    }

    /**
     * @param null $kSprache
     * @param bool $bForceNoneSeo
     * @return string
     */
    private function buildProductURL($kSprache = null, $bForceNoneSeo = false)
    {
        $shopURL = Shop::getURL();
        $cReturn = $shopURL;
        if ((Shop::$bSeo || $GLOBALS['bSeo']) && $bForceNoneSeo === false) {
            if ($kSprache === null) {
                $cReturn .= "/{$this->oProduct->cSeo}";
            } else {
                foreach ($this->oProduct->oProductLanguage_arr as $oProductLanguage) {
                    if (isset($oProductLanguage->kSprache) && $oProductLanguage->kSprache == $kSprache) {
                        $cReturn .= "/{$oProductLanguage->cSeo}";
                        break;
                    }
                }
            }
        } else {
            $cReturn .= "?a={$this->oProduct->kArtikel}";
            if ($kSprache !== null) {
                $cReturn .= "&lang={$kSprache}";
            }
        }

        if ($cReturn == $shopURL) {
            $cReturn = $this->buildProductURL($kSprache, true);
        }

        return $cReturn;
    }

    /**
     * @return $this
     */
    private function loadProductLanguagesFromDB()
    {
        $oArtikelSprache_arr                  = Shop::DB()->query("
            SELECT tartikelsprache.kSprache, tartikelsprache.cName, tartikelsprache.cBeschreibung, tartikelsprache.cKurzBeschreibung,
                (SELECT cSeo 
                    FROM tseo 
                    WHERE  kKey = {$this->oProduct->kArtikel} 
                    AND kSprache = tartikelsprache.kSprache 
                    AND cKey = 'kArtikel' LIMIT 0, 1) AS cSeo, tsprache.cShopStandard
                FROM tartikelsprache
                LEFT JOIN tsprache ON tartikelsprache.kSprache = tsprache.kSprache
                WHERE tartikelsprache.kArtikel = {$this->oProduct->kArtikel}", 2
        );
        $this->oProduct->oProductLanguage_arr = [];
        if (!is_array($oArtikelSprache_arr)) {
            if ($this->bDebug === true) {
                $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ .
                    '; Konnte keine Sprachen zu kArtikel: ' . $this->oProduct->kArtikel . ' aus der DB holen.');
            }
        } else {
            foreach ($oArtikelSprache_arr as $oArtikelSprache) {
                $this->oProduct->oProductLanguage_arr[$oArtikelSprache->kSprache] = $oArtikelSprache;
            }
        }
        // tab% inhalt an beschreibungen anhaengen
        $oArtikelAttr_arr = Shop::DB()->query("
            SELECT kAttribut, cStringWert, cTextWert
                FROM tattribut
                WHERE kArtikel = {$this->oProduct->kArtikel} 
                AND cName LIKE \"tab% inhalt\"", 2
        );
        $oStdLang = $this->getDefaultLanguage();
        if (is_array($oArtikelAttr_arr)) {
            foreach ($oArtikelAttr_arr as $oArtikelAttr) {
                if (strlen($oArtikelAttr->cStringWert) > 0) {
                    $cText = $oArtikelAttr->cStringWert;
                } else {
                    $cText = $oArtikelAttr->cTextWert;
                }
                //std-Sprache
                $this->oProduct->cBeschreibung .= ' ' . $cText;
                //Andere Sprachen
                $oArtikelAttrSprache_arr = Shop::DB()->query("
                    SELECT kSprache, cStringWert, cTextWert
                        FROM tattributsprache
                        WHERE kAttribut = {$oArtikelAttr->kAttribut} AND (cStringWert != \"\" OR cTextWert != \"\")", 2
                );
                if (is_array($oArtikelAttrSprache_arr)) {
                    foreach ($oArtikelAttrSprache_arr as $oArtikelAttrSprache) {
                        if (!empty($oArtikelAttrSprache->cStringWert)) {
                            $cText = $oArtikelAttrSprache->cStringWert;
                        } else {
                            $cText = $oArtikelAttrSprache->cTextWert;
                        }
                        if (!isset($this->oProduct->oProductLanguage_arr[$oArtikelAttrSprache->kSprache])) {
                            $this->oProduct->oProductLanguage_arr[$oArtikelAttrSprache->kSprache] = new stdClass();
                            $this->oProduct->oProductLanguage_arr[$oArtikelAttrSprache->kSprache]->cBeschreibung = '';
                        }
                        $this->oProduct->oProductLanguage_arr[$oArtikelAttrSprache->kSprache]->cBeschreibung .= ' ' . $cText;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function loadProductAttributeFromDB()
    {
        if ($this->bDebug === true) {
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ .
                '; Hole Attribute und Merkmale zu kArtikel: ' . $this->oProduct->kArtikel . ' aus der DB.');
        }
        $oArtikelAttribut_arr           = Shop::DB()->query("
            SELECT cName, cStringWert, cTextWert
                FROM tattribut
                WHERE kArtikel = {$this->oProduct->kArtikel}
                    AND (cStringWert <> '' OR cTextWert <> '')
                    AND cName NOT LIKE 'tab%_inhalt' AND cName NOT LIKE 'tab%_name'", 2
        );
        $this->oProduct->oAttribute_arr = [];
        if (is_array($oArtikelAttribut_arr)) {
            foreach ($oArtikelAttribut_arr as $oArtikelAttribut) {
                $oAttribut               = new stdClass();
                $oAttribut->cName        = $oArtikelAttribut->cName;
                $oAttribut->cLanguageIso = 'ger';
                if (isset($oArtikelAttribut->cStringWert) && !empty($oArtikelAttribut->cStringWert)) {
                    $oAttribut->cWert = $oArtikelAttribut->cStringWert;
                } else {
                    $oAttribut->cWert = $oArtikelAttribut->cTextWert;
                }

                $this->oProduct->oAttribute_arr[] = $oAttribut;
                unset($oAttribut);
            }
        } elseif ($this->bDebug === true) {
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ .
                '; Konnte keine Attribute zu kArtikel: ' . $this->oProduct->kArtikel . ' aus der DB holen.');
        }

        $oMerkmal_arr = Shop::DB()->query(
            "SELECT tmerkmal.cName AS cName, tmerkmalwertsprache.cWert AS cWert, tmerkmalwertsprache.kSprache AS kSprache
                FROM tartikelmerkmal
                JOIN tmerkmal 
                    ON tmerkmal.kMerkmal = tartikelmerkmal.kMerkmal
                JOIN tmerkmalwertsprache 
                    ON tmerkmalwertsprache.kMerkmalWert = tartikelmerkmal.kMerkmalWert
                WHERE tartikelmerkmal.kArtikel = {$this->oProduct->kArtikel}", 2
        );
        if (is_array($oMerkmal_arr)) {
            foreach ($oMerkmal_arr as $oMerkmal) {
                $oAttribut        = new stdClass();
                $oAttribut->cName = $oMerkmal->cName;
                foreach ($this->oLanguage_arr as $oLanguage) {
                    if ($oLanguage->kSprache == (int)$oMerkmal->kSprache) {
                        $oAttribut->cLanguageIso = $oLanguage->cISO;
                        break;
                    }
                }
                $oAttribut->cWert = $oMerkmal->cWert;

                $this->oProduct->oAttribute_arr[] = $oAttribut;
                unset($oAttribut);
            }
        } elseif ($this->bDebug === true) {
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ .
                '; Konnte keine Merkmale zu kArtikel: ' . $this->oProduct->kArtikel . ' aus der DB holen.');
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function loadProductVariationFromDB()
    {
        $oVariation_arr = Shop::DB()->query(
            "SELECT teigenschaft.kEigenschaft AS kEigenschaft, teigenschaftwert.kEigenschaftWert AS kEigenschaftWert, 
                teigenschaft.cName AS cName, teigenschaftwert.cName AS cWert, 
                (SELECT cISO FROM tsprache WHERE cShopStandard = 'Y') AS cLanguageIso
                FROM teigenschaft
                JOIN teigenschaftwert
                    ON teigenschaftwert.kEigenschaft = teigenschaft.kEigenschaft
                    WHERE teigenschaft.kArtikel = {$this->oProduct->kArtikel}", 2
        );
        if (is_array($oVariation_arr)) {
            $cSQLWhere = '';
            foreach ($oVariation_arr as $oVariation) {
                $cSQLWhere .= (strlen($cSQLWhere) > 0) ? ' OR ' : '';
                $cSQLWhere .= '(teigenschaftsprache.kEigenschaft = ' . $oVariation->kEigenschaft . ' AND
                    teigenschaftwertsprache.kEigenschaftWert = ' . $oVariation->kEigenschaftWert . ')';
            }
            if (strlen($cSQLWhere) > 0) {
                $oVariationLanguage_arr = Shop::DB()->query("
                    SELECT teigenschaftsprache.cName AS cName,
                        (SELECT cISO FROM tsprache WHERE kSprache = teigenschaftsprache.kSprache) AS cLanguageIso,
                        teigenschaftwertsprache.cName AS cWert
                        FROM teigenschaftsprache, teigenschaftwertsprache
                        WHERE ({$cSQLWhere})
                            AND teigenschaftsprache.kSprache = teigenschaftwertsprache.kSprache
                        ORDER BY teigenschaftwertsprache.kEigenschaftWert, teigenschaftwertsprache.kSprache", 2
                );
                if (is_array($oVariationLanguage_arr)) {
                    $this->oProduct->oVariation_arr = $oVariationLanguage_arr;
                }
            }
            $this->oProduct->oVariation_arr = isset($this->oProduct->oVariation_arr)
                ? array_merge($this->oProduct->oVariation_arr, $oVariation_arr)
                : $oVariation_arr;
        }
        if (!isset($this->oProduct->oVariation_arr) || !is_array($this->oProduct->oVariation_arr)) {
            $this->oProduct->oVariation_arr = [];
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function loadMetaKeywordsFromDB()
    {
        $oMetaKeywords = Shop::DB()->select(
            'tartikelattribut',
            'kArtikel', $this->oProduct->kArtikel,
            'cName', 'meta_title'
        );

        $this->oProduct->cMetaKeywords = '';
        if (isset($oMetaKeywords->cWert)) {
            $this->oProduct->cMetaKeywords = $oMetaKeywords->cWert;
        }

        return $this;
    }

    /**
     * @param int $kKundengruppe
     * @return bool
     */
    private function checkVisibility($kKundengruppe)
    {
        if (is_array($this->oNichtSichtbar_arr)) {
            foreach ($this->oNichtSichtbar_arr as $oNichtSichtbar) {
                if ($oNichtSichtbar->kKundengruppe == $kKundengruppe) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return $this
     */
    private function loadProductPriceFromDB()
    {
        $oWaehrung_arr = Shop::DB()->query("SELECT * FROM twaehrung", 2);
        $_oldCurrency  = isset($_SESSION['Waehrung']) ? $_SESSION['Waehrung'] : null;
        foreach ($this->oUsergroup_arr as $oUsergroup) {
            foreach ($oWaehrung_arr as $oWaehrung) {
                $_SESSION['Waehrung'] = $oWaehrung;

                $oPreis = new Preise($oUsergroup->kKundengruppe, $this->oProduct->kArtikel);
                $oPrice = new stdClass();
                $oPrice->fPrice       = (int)$oUsergroup->nNettoPreise === 1
                    ? $oPreis->fVK[1]
                    : $oPrice->fPrice = $oPreis->fVK[0];
                $oPrice->cCurrencyIso = strtoupper($oWaehrung->cISO);
                $oPrice->kUserGroup   = $oUsergroup->kKundengruppe;
                if ($this->checkVisibility($oUsergroup->kKundengruppe)) {
                    $this->oProduct->oPrice_arr[] = $oPrice;
                }
            }
        }
        if ($_oldCurrency !== null) {
            $_SESSION['Waehrung'] = $_oldCurrency;
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function loadProductCategoryFromDB()
    {
        $oKategorien_arr = Shop::DB()->query("
            SELECT kKategorie 
                FROM tkategorieartikel 
                WHERE kArtikel = {$this->oProduct->kArtikel} 
                    OR kArtikel = {$this->oProduct->kVaterArtikel} 
                GROUP BY kKategorie", 2
        );
        if (count($oKategorien_arr) > 0) {
            foreach ($oKategorien_arr as $oKategorie) {
                $this->oProduct->kKategorie_arr[] = $oKategorie->kKategorie;
            }
        } else {
            $this->oProduct->kKategorie_arr =[];
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ .
                '; Konnte keine Kategorie zu kArtikel: ' . $this->oProduct->kArtikel . ' aus der DB holen.');
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function loadSalesRank()
    {
        if ($this->fBestsellerMax === null) {
            $oRes                 = Shop::DB()->query('SELECT MAX(fAnzahl) AS fBestsellerMax FROM tbestseller', 1);
            $this->fBestsellerMax = ($oRes->fBestsellerMax === null) ? 1 : $oRes->fBestsellerMax;
        }
        $oRes = Shop::DB()->query("SELECT fAnzahl FROM tbestseller WHERE kArtikel = {$this->oProduct->kArtikel}", 1);

        $this->oProduct->nSalesRank = (isset($oRes->fAnzahl)) ? intval(100 / $this->fBestsellerMax * floatval($oRes->fAnzahl)) : 0;

        return $this;
    }

    /**
     * @return int
     */
    private function getProductAvailability()
    {
        return ($this->oProduct->cLagerBeachten === 'N' || $this->oProduct->cLagerKleinerNull === 'Y')
            ?  -1
            : $this->oProduct->fLagerbestand;
    }

    /**
     * @return null|Product
     */
    public function getFilledObject()
    {
        $oDefaultLanguage = $this->getDefaultLanguage();
        if (isset($this->oProduct->oPrice_arr) &&
            is_array($this->oProduct->oPrice_arr) &&
            isset($this->oProduct->kArtikel) &&
            $this->oProduct->kArtikel > 0
        ) {
            $oProduct = new Product();
            if (strlen($this->oProduct->cPfad) > 0) {
                $oProduct->setPictureURL(
                    Shop::getURL() . '/' .
                    MediaImage::getThumb(
                        Image::TYPE_PRODUCT,
                        $this->oProduct->kArtikel,
                        $this->oProduct,
                        Image::SIZE_XS,
                        0
                    )
                );
            }
            $oProduct->setId($this->oProduct->kArtikel)
                     ->setArticleNumber($this->oProduct->cArtNr)
                     ->setAvailability($this->getProductAvailability())
                     ->setMasterId($this->oProduct->kVaterArtikel)
                     ->setManufacturer($this->oProduct->kHersteller)
                     ->setEAN($this->oProduct->cBarcode)
                     ->setISBN($this->oProduct->cISBN)
                     ->setMPN($this->oProduct->cHAN)
                     ->setUPC($this->oProduct->cUPC)
                     ->setSalesRank($this->oProduct->nSalesRank);

            if($oDefaultLanguage->cStandard == $oDefaultLanguage->cShopStandard) {
                $oProduct->setName($this->oProduct->cName, $oDefaultLanguage->cISO)
                    ->setDescription($this->oProduct->cBeschreibung, $oDefaultLanguage->cISO)
                    ->setShortDescription($this->oProduct->cKurzBeschreibung, $oDefaultLanguage->cISO)
                    ->setURL($this->buildProductURL(), $oDefaultLanguage->cISO);
            }

            foreach ($this->oProduct->kKategorie_arr as $kKategorie) {
                $oProduct->setCategory($kKategorie);
            }

            $cKeywords = trim($this->oProduct->cMetaKeywords . ' ' . $this->oProduct->cSuchbegriffe);

            foreach ($this->oLanguage_arr as $oLanguage) {
                if (isset($this->oProduct->oProductLanguage_arr[$oLanguage->kSprache])) {
                    $oProduct->setName(
                        (isset($this->oProduct->oProductLanguage_arr[$oLanguage->kSprache]->cName) &&
                            strlen(trim($this->oProduct->oProductLanguage_arr[$oLanguage->kSprache]->cName)) > 0)
                            ? $this->oProduct->oProductLanguage_arr[$oLanguage->kSprache]->cName
                            : $this->oProduct->cName, $oLanguage->cISO
                    )->setDescription(
                        (isset($this->oProduct->oProductLanguage_arr[$oLanguage->kSprache]->cBeschreibung) &&
                            strlen(trim($this->oProduct->oProductLanguage_arr[$oLanguage->kSprache]->cBeschreibung)) > 0)
                            ? $this->oProduct->oProductLanguage_arr[$oLanguage->kSprache]->cBeschreibung
                            : $this->oProduct->cBeschreibung, $oLanguage->cISO
                    )->setShortDescription(
                        (isset($this->oProduct->oProductLanguage_arr[$oLanguage->kSprache]->cKurzBeschreibung) &&
                            strlen(trim($this->oProduct->oProductLanguage_arr[$oLanguage->kSprache]->cKurzBeschreibung)) > 0)
                            ? $this->oProduct->oProductLanguage_arr[$oLanguage->kSprache]->cKurzBeschreibung
                            : $this->oProduct->cKurzBeschreibung, $oLanguage->cISO
                    );
                }
                if (strlen($cKeywords) > 0) {
                    $oProduct->setKeywords($cKeywords, $oLanguage->cISO);
                }
                $oProduct->setURL($this->buildProductURL($oLanguage->kSprache), $oLanguage->cISO);
            }
            if (isset($this->oProduct->oAttribute_arr) && is_array($this->oProduct->oAttribute_arr)) {
                foreach ($this->oProduct->oAttribute_arr as $oValue) {
                    $oProduct->setAttribute($oValue->cName, $oValue->cWert, $oValue->cLanguageIso);
                }
            }
            if (isset($this->oProduct->oVariation_arr) && is_array($this->oProduct->oVariation_arr)) {
                foreach ($this->oProduct->oVariation_arr as $oValue) {
                    $oProduct->setVariation($oValue->cName, $oValue->cWert, $oValue->cLanguageIso);
                }
            }
            foreach ($this->oProduct->oPrice_arr as $oPrice) {
                if ($this->oProduct->cVPE == 'Y' &&
                    isset($this->oProduct->fVPEWert) &&
                    $this->oProduct->fVPEWert > 0 &&
                    isset($this->oProduct->cEinheit) &&
                    strlen($this->oProduct->cEinheit) > 0
                ) {
                    $oProduct->setPrice(
                        $oPrice->cCurrencyIso,
                        $oPrice->kUserGroup,
                        $oPrice->fPrice,
                        ($oPrice->fPrice / floatval($this->oProduct->fVPEWert)) . '/' . $this->oProduct->cEinheit
                    );
                } else {
                    $oProduct->setPrice($oPrice->cCurrencyIso, $oPrice->kUserGroup, $oPrice->fPrice);
                }
            }

            return ($this->isVisible()) ? $oProduct : null;
        }

        return null;
    }

    /**
     * @return bool
     */
    protected function isVisible()
    {
        $bVisible = true;
        $conf     = Shop::getSettings(array(CONF_GLOBAL));
        if ($conf['global']['artikel_artikelanzeigefilter'] == EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGER) {
            if ($this->oProduct->cLagerBeachten === 'Y' && $this->oProduct->fLagerbestand <= 0) {
                $bVisible = false;
            }
        } elseif ($conf['global']['artikel_artikelanzeigefilter'] == EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL) {
            if ($this->oProduct->cLagerBeachten === 'Y' && $this->oProduct->fLagerbestand <= 0 && $this->oProduct->cLagerKleinerNull === 'N') {
                $bVisible = false;
            }
        }

        return $bVisible;
    }
}
