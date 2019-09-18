<?php

//require_once(PFAD_ROOT.PFAD_CLASSES.'class.JTL-Shop.Artikel.php');
require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'interface.IItemData.php');
require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.Product.php');
require_once(PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Preise.php');
/**
 * copyright (c) 2006-2011 JTL-Software-GmbH, all rights reserved
 *
 * this file may not be redistributed in whole or significant part
 * and is subject to the JTL-Software-GmbH license.
 *
 * license: http://jtl-software.de/jtlshop3license.html
 */

/**
 * Description of ProductData
 *
 * @author Andre Vermeulen
 */
class ProductData implements IItemData
{
    private $oDB;
    private $oDebugger;
    private $oLanguage_arr;
    private $oUsergroup_arr;
    private $oProduct;
    private $bDebug = true;
    private $fBestsellerMax = null;
    private $oNichtSichtbar_arr = array();

    public function __construct(JTLSearchDB $oDB, IDebugger $oDebugger, $kArtikel = 0)
    {
        try {
            $this->oDB = $oDB;
            $this->oDebugger = $oDebugger;

            $this->loadLanguages();
            $this->loadUsergroups();

            if ($kArtikel > 0) {
                $this->loadFromDB($kArtikel);
            }
        } catch (Exception $oEx) {
            //@todo: Errorhandling
            return $oEx;
        }
    }

    public function getCount()
    {
        $oRes = $this->oDB->getAsObject('SELECT COUNT(*) AS nAnzahl FROM tartikel', 1);
        if ($oRes !== false && $oRes->nAnzahl > 0) {
            return intval($oRes->nAnzahl);
        }
        return 0;
    }

    public static function getItemKeys(JTLSearchDB $oDB, $nLimitN, $nLimitM)
    {
        $nLimitN = intval($nLimitN);
        $nLimitM = intval($nLimitM);
        
        $oRes = $oDB->getAsObject("SELECT kArtikel AS kItem FROM tartikel ORDER BY kArtikel LIMIT {$nLimitN}, {$nLimitM}", 2);
        if ($oRes !== false && count($oRes) > 0) {
            return $oRes;
        } else {
            return array();
        }
    }

    private function getDefaultLanguage()
    {
        if (!count($this->oLanguage_arr) > 0) {
            $this->loadLanguages();
        }

        foreach ($this->oLanguage_arr as $oSprache) {
            if ($oSprache->cShopStandard == "Y") {
                return $oSprache;
            }
        }
    }

    private function loadLanguages()
    {
        $this->oLanguage_arr = $this->oDB->getAsObject('SELECT tsprache.* FROM tsprache JOIN tjtlsearchexportlanguage ON tsprache.cISO = tjtlsearchexportlanguage.cISO ORDER BY cShopStandard DESC', 2);
        if (count($this->oLanguage_arr) == 0) {
            throw new Exception('Es ist ein Fehler beim Laden der Sprachen geschehen: Keine Sprachen vorhanden.', 1);
        }
    }

    private function loadUsergroups()
    {
        $this->oUsergroup_arr = $this->oDB->getAsObject('SELECT * FROM tkundengruppe ORDER BY cStandard DESC', 2);

        if (count($this->oUsergroup_arr) == 0) {
            throw new Exception('Es ist ein Fehler beim Laden der Kundengruppen geschehen: Keine Kundengruppen vorhanden.', 1);
        }
    }

    private function loadUsergroupVisibility()
    {
        $this->oNichtSichtbar_arr = $this->oDB->getAsObject("SELECT kKundengruppe FROM tartikelsichtbarkeit WHERE kArtikel = {$this->oProduct->kArtikel}", 2);
    }

    public function loadFromDB($kItem)
    {
        $kItem = intval($kItem);
        try {
            $oRes = $this->oDB->getAsObject("
                SELECT
                    tartikel.kArtikel, tartikel.kHersteller, tartikel.kSteuerklasse, tartikel.kEigenschaftKombi,
                    tartikel.kVaterArtikel, tartikel.kStueckliste, tartikel.kWarengruppe, tartikel.cArtNr,
                    tartikel.cName, tartikel.cBeschreibung, tartikel.cKurzBeschreibung, tartikel.cAnmerkung,
                    tartikel.fLagerbestand, tartikel.fMwSt, tartikel.cBarcode, tartikel.cLagerBeachten,
                    tartikel.cLagerKleinerNull, tartikel.cLagerVariation, tartikel.cTeilbar, tartikel.fPackeinheit,
                    tartikel.cSuchbegriffe, tartikel.cSerie, tartikel.cISBN, tartikel.cASIN, tartikel.cHAN,
                    tartikel.cUPC, tartikel.nIstVater, tartikel.fVPEWert, tartikel.cVPE,
                    (SELECT cPfad FROM tartikelpict WHERE kArtikel = {$kItem} AND nNr = 1 LIMIT 0, 1) AS cPfad,
                    (SELECT cSeo FROM tseo WHERE  kKey = {$kItem} AND kSprache = (SELECT kSprache FROM tsprache WHERE cShopStandard = 'Y' LIMIT 0, 1) AND cKey = 'kArtikel' LIMIT 0, 1) AS cSeo,
                    teinheit.cName AS cEinheit
                FROM
                    tartikel
                    LEFT JOIN teinheit ON teinheit.kEinheit = tartikel.kEinheit AND teinheit.kSprache = (SELECT kSprache FROM tsprache WHERE cShopStandard = 'Y' LIMIT 0, 1)
                WHERE
                    kArtikel = {$kItem}", 1);
            if ($oRes == false) {
                throw new Exception('Es ist ein Fehler beim Laden des Artikel geschehen: Kein Datensatz zu kArtikel: ' . $kItem . ' vorhanden.', 1);
            }

            $this->oProduct = $oRes;
            
            unset($oRes);
            if (isset($this->oProduct->kArtikel) && $this->oProduct->kArtikel > 0) {
                $this->loadUsergroupVisibility();
                $this->loadProductCategoryFromDB();
                $this->loadProductPriceFromDB();
                $this->loadProductLanguagesFromDB();
                $this->loadProductAttributeFromDB();
                $this->loadProductVariationFromDB();
                $this->loadMetaKeywordsFromDB();
                $this->loadSalesRank();
            } else {
                $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; Schwerer Fehler beim Exportieren! $this->oProduct = ' . var_export($this->oProduct, true), JTLLOG_LEVEL_ERROR);
            }
        } catch (Exception $oEx) {
            $oReturnObj = new stdClass();
            $oReturnObj->nReturnCode = 0;
            $oReturnObj->cMessage = $oEx->getMessage();
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; ' . json_encode($oReturnObj), JTLLOG_LEVEL_ERROR);
            /*echo json_encode($oReturnObj);
            die();*/
        }
    }

    private function buildProductURL($kSprache = null)
    {
        $cLang = '';
        if ($kSprache !== null) {
            foreach ($this->oLanguage_arr as $oLanguage) {
                if ($oLanguage->kSprache == intval($kSprache)) {
                    $cLang = '&amp;lang=' . $oLanguage->kSprache;
                    break;
                }
            }
        }

        if ($GLOBALS['bSeo']) {
            if ($kSprache == null) {
                if (isset($this->oProduct->cSeo)) {
                    return URL_SHOP . '/' . $this->oProduct->cSeo;
                }
            } else {
                $kArtikel = $this->oProduct->kArtikel;
                foreach ($this->oProduct->oProductLanguage_arr as $oArtikelSprache) {
                    if ($oArtikelSprache->kSprache == intval($kSprache) && isset($oArtikelSprache->cSeo)) {
                        return URL_SHOP . '/' . $oArtikelSprache->cSeo;
                    }
                }
            }
        }
        $cURL = URL_SHOP . '/index.php?a=' . $this->kArtikel . $cLang;


        if ($obj->cSeo && $GLOBALS['bSeo']) {
            return $obj->cSeo;
        }
        return $cDatei . "?a=" . $obj->kArtikel;
    }

    private function loadProductLanguagesFromDB()
    {
        $oArtikelSprache_arr = $this->oDB->getAsObject("
            SELECT
                kSprache, cName, cBeschreibung, cKurzBeschreibung, (SELECT cSeo FROM tseo WHERE  kKey = {$this->oProduct->kArtikel} AND kSprache = tartikelsprache.kSprache AND cKey = 'kArtikel' LIMIT 0, 1) as cSeo
            FROM
                tartikelsprache
            WHERE
                kArtikel = {$this->oProduct->kArtikel}", 2);
        $this->oProduct->oProductLanguage_arr = array();
        if (!is_array($oArtikelSprache_arr)) {
            if ($this->bDebug == true) {
                $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; Konnte keine Sprachen zu kArtikel: ' . $this->oProduct->kArtikel . ' aus der DB holen.');
            }
        } else {
            foreach ($oArtikelSprache_arr as $oArtikelSprache) {
                $this->oProduct->oProductLanguage_arr[$oArtikelSprache->kSprache] = $oArtikelSprache;
            }
        }
    }

    private function loadProductAttributeFromDB()
    {
        if ($this->bDebug == true) {
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; Hole Attribute und Merkmale zu kArtikel: ' . $this->oProduct->kArtikel . ' aus der DB.');
        }
        $oArtikelAttribut_arr = $this->oDB->getAsObject("
            SELECT
                cName, cStringWert, cTextWert
            FROM
                tattribut
            WHERE
                kArtikel = {$this->oProduct->kArtikel}
                AND (cStringWert <> '' OR cTextWert <> '')
                AND cName NOT LIKE 'tab%_inhalt' AND cName NOT LIKE 'tab%_name'", 2);

        $this->oProduct->oAttribute_arr = array();
        if (is_array($oArtikelAttribut_arr)) {
            foreach ($oArtikelAttribut_arr as $oArtikelAttribut) {
                $oAttribut = new stdClass();
                $oAttribut->cName = $oArtikelAttribut->cName;
                $oAttribut->cLanguageIso = 'ger';
                if (isset($oArtikelAttribut->cStringWert) && !empty($oArtikelAttribut->cStringWert)) {
                    $oAttribut->cWert = $oArtikelAttribut->cStringWert;
                } else {
                    $oAttribut->cWert = $oArtikelAttribut->cTextWert;
                }

                $this->oProduct->oAttribute_arr[] = $oAttribut;
                unset($oAttribut);
            }
        } elseif ($this->bDebug == true) {
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; Konnte keine Attribute zu kArtikel: ' . $this->oProduct->kArtikel . ' aus der DB holen.');
        }

        $oMerkmal_arr = $this->oDB->getAsObject("SELECT tmerkmal.cName AS cName, tmerkmalwertsprache.cWert AS cWert, tmerkmalwertsprache.kSprache AS kSprache
                                                    FROM tartikelmerkmal
                                                    JOIN tmerkmal ON tmerkmal.kMerkmal = tartikelmerkmal.kMerkmal
                                                    JOIN tmerkmalwertsprache ON tmerkmalwertsprache.kMerkmalWert = tartikelmerkmal.kMerkmalWert
                                                    WHERE tartikelmerkmal.kArtikel = {$this->oProduct->kArtikel}", 2);

        if (is_array($oMerkmal_arr)) {
            foreach ($oMerkmal_arr as $oMerkmal) {
                $oAttribut = new stdClass();
                $oAttribut->cName = $oMerkmal->cName;
                foreach ($this->oLanguage_arr as $oLanguage) {
                    if ($oLanguage->kSprache == intval($oMerkmal->kSprache)) {
                        $oAttribut->cLanguageIso = $oLanguage->cISO;
                        break;
                    }
                }
                $oAttribut->cWert = $oMerkmal->cWert;

                $this->oProduct->oAttribute_arr[] = $oAttribut;
                unset($oAttribut);
            }
        } elseif ($this->bDebug == true) {
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; Konnte keine Merkmale zu kArtikel: ' . $this->oProduct->kArtikel . ' aus der DB holen.');
        }
    }

    private function loadProductVariationFromDB()
    {
        $oVariation_arr = $this->oDB->getAsObject("SELECT teigenschaft.kEigenschaft AS kEigenschaft, teigenschaftwert.kEigenschaftWert AS kEigenschaftWert FROM teigenschaft JOIN teigenschaftwert ON teigenschaftwert.kEigenschaft = teigenschaft.kEigenschaft WHERE teigenschaft.kArtikel = {$this->oProduct->kArtikel}", 2);

        if (is_array($oVariation_arr)) {
            $cSQLWhere = '';
            foreach ($oVariation_arr as $oVariation) {
                $cSQLWhere .= (strlen($cSQLWhere) > 0) ? ' OR ' : '';
                $cSQLWhere .= '(teigenschaftsprache.kEigenschaft = ' . $oVariation->kEigenschaft . ' AND
                    teigenschaftwertsprache.kEigenschaftWert = ' . $oVariation->kEigenschaftWert . ')';
            }
            if (strlen($cSQLWhere) > 0) {
                $oVariationLanguage_arr = $this->oDB->getAsObject("
                    SELECT
                        teigenschaftsprache.cName AS cName,
                        (SELECT cISO FROM tsprache WHERE kSprache = teigenschaftsprache.kSprache) as cLanguageIso,
                        teigenschaftwertsprache.cName AS cWert
                    FROM
                        teigenschaftsprache,
                        teigenschaftwertsprache
                    WHERE
                        ({$cSQLWhere}) AND
                        teigenschaftsprache.kSprache = teigenschaftwertsprache.kSprache
                    ORDER BY teigenschaftwertsprache.kEigenschaftWert, teigenschaftwertsprache.kSprache", 2);
                if (is_array($oVariationLanguage_arr)) {
                    $this->oProduct->oVariation_arr = $oVariationLanguage_arr;
                }
            }
        }
        if (!is_array($this->oProduct->oVariation_arr)) {
            $this->oProduct->oVariation_arr = array();
        }
    }

    private function loadMetaKeywordsFromDB()
    {
        $oMetaKeywords = $this->oDB->getAsObject("SELECT cWert FROM tartikelattribut WHERE kArtikel = {$this->oProduct->kArtikel} AND cName = 'meta_title'", 1);

        $this->oProduct->cMetaKeywords = '';
        if (is_object($oMetaKeywords)) {
            $this->oProduct->cMetaKeywords = $oMetaKeywords->cWert;
        }
    }

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

    private function loadProductPriceFromDB()
    {
        $oWaehrung_arr = $this->oDB->getAsObject("SELECT * FROM twaehrung", 2);

        foreach ($this->oUsergroup_arr as $oUsergroup) {
            foreach ($oWaehrung_arr as $oWaehrung) {
                $_SESSION['Waehrung'] = $oWaehrung;

                $oPreis = new Preise($oUsergroup->kKundengruppe, $this->oProduct->kArtikel);
                $oPrice = new stdClass();

                if ($oUsergroup->nNettoPreise == 1) {
                    $oPrice->fPrice = $oPreis->fVK[1];
                } else {
                    $oPrice->fPrice = $oPreis->fVK[0];
                }
                $oPrice->cCurrencyIso = strtoupper($oWaehrung->cISO);
                $oPrice->kUserGroup = $oUsergroup->kKundengruppe;
                if ($this->checkVisibility($oUsergroup->kKundengruppe)) {
                    $this->oProduct->oPrice_arr[] = $oPrice;
                }

                unset($oPrice);
                unset($_SESSION['Waehrung']);
            }
        }
    }

    private function loadProductCategoryFromDB()
    {
        $oKategorien_arr = $this->oDB->getAsObject("SELECT kKategorie FROM tkategorieartikel WHERE kArtikel = {$this->oProduct->kArtikel} OR kArtikel = {$this->oProduct->kVaterArtikel} GROUP BY kKategorie", 2);
        if (count($oKategorien_arr) > 0) {
            foreach ($oKategorien_arr as $oKategorie) {
                $this->oProduct->kKategorie_arr[] = $oKategorie->kKategorie;
            }
        } else {
            $this->oProduct->kKategorie_arr = array();
            $this->oDebugger->doDebug(__FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . '; Konnte keine Kategorie zu kArtikel: ' . $this->oProduct->kArtikel . ' aus der DB holen.');
        }
    }

    private function loadSalesRank()
    {
        if ($this->fBestsellerMax === null) {
            $oRes = $this->oDB->getAsObject('SELECT MAX(fAnzahl) AS fBestsellerMax FROM tbestseller', 1);
            if ($oRes->fBestsellerMax == null) {
                $this->fBestsellerMax = 1;
            } else {
                $this->fBestsellerMax = $oRes->fBestsellerMax;
            }
            unset($oRes);
        }

        $oRes = $this->oDB->getAsObject("SELECT fAnzahl FROM tbestseller WHERE kArtikel = {$this->oProduct->kArtikel}", 1);

        $this->oProduct->nSalesRank = intval(100 / $this->fBestsellerMax * floatval($oRes->fAnzahl));
    }

    private function getProductAvailability()
    {
        if ($this->oProduct->cLagerBeachten == 'N' || $this->oProduct->cLagerKleinerNull == 'Y') {
            return -1;
        } else {
            return $this->oProduct->fLagerbestand;
        }
    }

    public function getFilledObject()
    {
        $oDefaultLanguage = $this->getDefaultLanguage();
        if (isset($this->oProduct->oPrice_arr) && is_array($this->oProduct->oPrice_arr) && isset($this->oProduct->kArtikel) && $this->oProduct->kArtikel > 0) {
            $oProduct = new Product();
            $oProduct->setId($this->oProduct->kArtikel);
            $oProduct->setArticleNumber($this->oProduct->cArtNr);
            $oProduct->setAvailability($this->getProductAvailability());
            $oProduct->setMasterId($this->oProduct->kVaterArtikel);
            if (strlen($this->oProduct->cPfad) > 0) {
                $oProduct->setPictureURL(URL_SHOP . '/' . gibArtikelBildPfad(PFAD_PRODUKTBILDER_MINI . $this->oProduct->cPfad));
            }
            $oProduct->setManufacturer($this->oProduct->kHersteller);
            $oProduct->setEAN($this->oProduct->cBarcode);
            $oProduct->setISBN($this->oProduct->cISBN);
            $oProduct->setMPN($this->oProduct->cHAN);
            $oProduct->setUPC($this->oProduct->cUPC);
            $oProduct->setSalesRank($this->oProduct->nSalesRank);
            foreach ($this->oProduct->kKategorie_arr as $kKategorie) {
                $oProduct->setCategory($kKategorie);
            }

            $cKeywords = trim($this->oProduct->cMetaKeywords . ' ' . $this->oProduct->cSuchbegriffe);
            if (strlen($cKeywords) > 0) {
                $oProduct->setKeywords($cKeywords, $oDefaultLanguage->cISO);
            }

            $oProduct->setName($this->oProduct->cName, $oDefaultLanguage->cISO);
            $oProduct->setDescription($this->oProduct->cBeschreibung, $oDefaultLanguage->cISO);
            $oProduct->setShortDescription($this->oProduct->cKurzBeschreibung, $oDefaultLanguage->cISO);
            $oProduct->setURL($this->buildProductURL(), $oDefaultLanguage->cISO);
            foreach ($this->oLanguage_arr as $oLanguage) {
                if ($oLanguage->cShopStandard == 'N') {
                    $oProduct->setName((strlen(trim($this->oProduct->oProductLanguage_arr[$oLanguage->kSprache]->cName)) > 0) ? $this->oProduct->oProductLanguage_arr[$oLanguage->kSprache]->cName : $this->oProduct->cName, $oLanguage->cISO);
                    $oProduct->setDescription((strlen(trim($this->oProduct->oProductLanguage_arr[$oLanguage->kSprache]->cBeschreibung)) > 0) ? $this->oProduct->oProductLanguage_arr[$oLanguage->kSprache]->cBeschreibung : $this->oProduct->cBeschreibung, $oLanguage->cISO);
                    $oProduct->setShortDescription((strlen(trim($this->oProduct->oProductLanguage_arr[$oLanguage->kSprache]->cKurzBeschreibung)) > 0) ? $this->oProduct->oProductLanguage_arr[$oLanguage->kSprache]->cKurzBeschreibung : $this->oProduct->cKurzBeschreibung, $oLanguage->cISO);
                    if (strlen($cKeywords) > 0) {
                        $oProduct->setKeywords($this->oProduct->cMetaKeywords . ' ' . $this->oProduct->cSuchbegriffe, $oLanguage->cISO);
                    }
                    $oProduct->setURL($this->buildProductURL($oLanguage->kSprache), $oLanguage->cISO);
                }
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
                if ($this->oProduct->cVPE == 'Y' && isset($this->oProduct->fVPEWert) && $this->oProduct->fVPEWert > 0 && isset($this->oProduct->cEinheit) && strlen($this->oProduct->cEinheit) > 0) {
                    $oProduct->setPrice($oPrice->cCurrencyIso, $oPrice->kUserGroup, $oPrice->fPrice, ($oPrice->fPrice/floatval($this->oProduct->fVPEWert)).'/'.$this->oProduct->cEinheit);
                } else {
                    $oProduct->setPrice($oPrice->cCurrencyIso, $oPrice->kUserGroup, $oPrice->fPrice);
                }
            }
            if ($this->isVisible()) {
                return $oProduct;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    protected function isVisible()
    {
        $bVisible = true;
        
        if ($GLOBALS['GlobaleEinstellungen']['global']['artikel_artikelanzeigefilter']==EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGER) {
            if ($this->oProduct->cLagerBeachten == 'Y' && $this->oProduct->fLagerbestand <= 0) {
                $bVisible = false;
            }
        } elseif ($GLOBALS['GlobaleEinstellungen']['global']['artikel_artikelanzeigefilter']==EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL) {
            if ($this->oProduct->cLagerBeachten == 'Y' && $this->oProduct->fLagerbestand <= 0 && $this->oProduct->cLagerKleinerNull == 'N') {
                $bVisible = false;
            }
        }
        return $bVisible;
    }
}
