<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Preise
 */
class Preise
{
    /**
     * @var int
     */
    public $kKundengruppe;

    /**
     * @var int
     */
    public $kArtikel;

    /**
     * @var int
     */
    public $kKunde;

    /**
     * @var string
     */
    public $cPreis1Localized;

    /**
     * @var string
     */
    public $cPreis2Localized;

    /**
     * @var string
     */
    public $cPreis3Localized;

    /**
     * @var string
     */
    public $cPreis4Localized;

    /**
     * @var string
     */
    public $cPreis5Localized;

    /**
     * @var string
     */
    public $cVKLocalized;

    /**
     * @var float
     */
    public $fVKNetto;

    /**
     * @var float
     */
    public $fVKBrutto;

    /**
     * @var float
     */
    public $fPreis1;

    /**
     * @var float
     */
    public $fPreis2;

    /**
     * @var float
     */
    public $fPreis3;

    /**
     * @var float
     */
    public $fPreis4;

    /**
     * @var float
     */
    public $fPreis5;

    /**
     * @var float
     */
    public $fUst;

    /**
     * @var float
     */
    public $alterVKNetto;

    /**
     * @var int
     */
    public $nAnzahl1;

    /**
     * @var int
     */
    public $nAnzahl2;

    /**
     * @var int
     */
    public $nAnzahl3;

    /**
     * @var int
     */
    public $nAnzahl4;

    /**
     * @var int
     */
    public $nAnzahl5;

    /**
     * @var string
     */
    public $strPreisGrafik_Detail;

    /**
     * @var string
     */
    public $strPreisGrafik_Suche;

    /**
     * @var array
     */
    public $alterVK;

    /**
     * @var array
     */
    public $fStaffelpreis1;

    /**
     * @var array
     */
    public $fStaffelpreis2;

    /**
     * @var array
     */
    public $fStaffelpreis3;

    /**
     * @var array
     */
    public $fStaffelpreis4;

    /**
     * @var array
     */
    public $fStaffelpreis5;

    /**
     * @var float
     */
    public $rabatt;

    /**
     * @var array
     */
    public $alterVKLocalized;

    /**
     * @var array
     */
    public $fVK;

    /**
     * @var array
     */
    public $nAnzahl_arr = [];

    /**
     * @var array
     */
    public $fPreis_arr = [];

    /**
     * @var array
     */
    public $fStaffelpreis_arr = [];

    /**
     * @var array
     */
    public $cPreisLocalized_arr = [];

    /**
     * @var string
     */
    public $strPreisGrafik_Topbox;

    /**
     * @var string
     */
    public $strPreisGrafik_Sonderbox;

    /**
     * @var string
     */
    public $strPreisGrafik_Neubox;

    /**
     * @var string
     */
    public $strPreisGrafik_Bestsellerbox;

    /**
     * @var string
     */
    public $strPreisGrafik_Zuletztbox;

    /**
     * @var string
     */
    public $strPreisGrafik_Baldbox;

    /**
     * @var string
     */
    public $cPreisGrafik_Boxen;

    /**
     * @var string
     */
    public $strPreisGrafik_TopboxStartseite;

    /**
     * @var string
     */
    public $strPreisGrafik_SonderboxStartseite;

    /**
     * @var string
     */
    public $strPreisGrafik_NeuboxStartseite;

    /**
     * @var string
     */
    public $strPreisGrafik_BestsellerboxStartseite;

    /**
     * @var string
     */
    public $strPreisGrafik_ZuletztboxStartseite;

    /**
     * @var string
     */
    public $strPreisGrafik_BaldboxStartseite;

    /**
     * @var string
     */
    public $cPreisGrafik_Startseite;

    /**
     * @var string
     */
    public $cPreisGrafik_Artikeldetails;

    /**
     * @var string
     */
    public $strPreisGrafik_Uebersicht;

    /**
     * @var string
     */
    public $cPreisGrafik_Artikeluebersicht;

    /**
     * @var bool|int
     */
    public $Sonderpreis_aktiv = false;

    /**
     * @var bool
     */
    public $Kundenpreis_aktiv = false;

    /**
     * Konstruktor
     *
     * @param int $kKundengruppe
     * @param int $kArtikel
     * @param int $kKunde
     * @param int $kSteuerklasse
     */
    public function __construct($kKundengruppe, $kArtikel, $kKunde = 0, $kSteuerklasse = 0)
    {
        $kKundengruppe = (int)$kKundengruppe;
        $kArtikel      = (int)$kArtikel;
        $kKunde        = (int)$kKunde;
        $filterKunde   = "AND p.kKundengruppe = {$kKundengruppe}";

        if ($kKunde > 0 && $this->hasCustomPrice($kKunde)) {
            $filterKunde = "AND (p.kKundengruppe, COALESCE(p.kKunde, 0)) = (
                            SELECT min(IFNULL(p1.kKundengruppe, {$kKundengruppe})), max(IFNULL(p1.kKunde, 0))
                            FROM tpreis AS p1
                            WHERE p1.kArtikel = {$kArtikel}
                                AND (p1.kKundengruppe = 0 OR p1.kKundengruppe = {$kKundengruppe})
                                AND (p1.kKunde = {$kKunde} OR p1.kKunde IS NULL))";
        }

        $prices = Shop::DB()->query("
            SELECT *
                FROM tpreis AS p
                JOIN tpreisdetail AS d ON d.kPreis = p.kPreis
                WHERE p.kArtikel = {$kArtikel}
                    {$filterKunde}
                ORDER BY d.nAnzahlAb", 2);

        if (count($prices) > 0) {
            if ($kSteuerklasse === 0) {
                $tax           =
                    Shop::DB()->select('tartikel', 'kArtikel', $kArtikel, null, null, null, null, false, 'kSteuerklasse');
                $kSteuerklasse = (int)$tax->kSteuerklasse;
            }
            $this->fUst          = gibUst($kSteuerklasse);
            $tmp = Shop::DB()->select(
                'tartikel',
                'kArtikel',
                $kArtikel,
                null,
                null,
                null,
                null,
                false,
                'fMwSt'
                );
            $defaultTax        = $tmp->fMwSt;
            $currentTax        = $this->fUst;
            $this->kArtikel      = $kArtikel;
            $this->kKundengruppe = $kKundengruppe;
            $this->kKunde        = $kKunde;
            $specialPriceValue   = null;
            foreach ($prices as $i => $price) {
                // Kundenpreis?
                if ((int)$price->kKunde > 0) {
                    $this->Kundenpreis_aktiv = true;
                }
                // Standardpreis
                if ($price->nAnzahlAb < 1) {
                    $this->fVKNetto = $this->getRecalculatedNetPrice($price->fVKNetto, $defaultTax, $currentTax);
                    $specialPrice   = Shop::DB()->query("
                        SELECT tsonderpreise.fNettoPreis, tartikelsonderpreis.dEnde AS dEnde_en,
                            DATE_FORMAT(tartikelsonderpreis.dEnde, '%d.%m.%Y') AS dEnde_de
                            FROM tsonderpreise
                            JOIN tartikel 
                                ON tartikel.kArtikel = " . $kArtikel . "
                            JOIN tartikelsonderpreis 
                                ON tartikelsonderpreis.kArtikelSonderpreis = tsonderpreise.kArtikelSonderpreis
                                AND tartikelsonderpreis.kArtikel = " . $kArtikel . "
                                AND tartikelsonderpreis.cAktiv = 'Y'
                                AND tartikelsonderpreis.dStart <= date(now())
                                AND (tartikelsonderpreis.dEnde >= CURDATE() OR tartikelsonderpreis.dEnde = '0000-00-00')
                                AND (tartikelsonderpreis.nAnzahl <= tartikel.fLagerbestand OR tartikelsonderpreis.nIstAnzahl = 0)
                            WHERE tsonderpreise.kKundengruppe = {$kKundengruppe}", 1);

                    if (isset($specialPrice->fNettoPreis) && (double)$specialPrice->fNettoPreis < $this->fVKNetto) {
                        $specialPriceValue       = $this->getRecalculatedNetPrice($specialPrice->fNettoPreis, $defaultTax, $currentTax);
                        $this->alterVKNetto      = $this->fVKNetto;
                        $this->fVKNetto          = $specialPriceValue;
                        $this->Sonderpreis_aktiv = 1;
                        $this->SonderpreisBis_de = $specialPrice->dEnde_de;
                        $this->SonderpreisBis_en = $specialPrice->dEnde_en;
                    }
                } else {
                    // Alte Preisstaffeln
                    if ($i <= 5) {
                        $scaleGetter = "nAnzahl{$i}";
                        $priceGetter = "fPreis{$i}";

                        $this->{$scaleGetter} = (int)$price->nAnzahlAb;
                        $this->{$priceGetter} = isset($specialPriceValue) ? $specialPriceValue : $this->getRecalculatedNetPrice($price->fVKNetto, $defaultTax, $currentTax);
                    }

                    $this->nAnzahl_arr[] = (int)$price->nAnzahlAb;
                    $this->fPreis_arr[]  =
                        ($specialPriceValue !== null && $specialPriceValue < (double)$price->fVKNetto)
                            ? $specialPriceValue
                            : $this->getRecalculatedNetPrice($price->fVKNetto, $defaultTax, $currentTax);
                }
            }
        }
        $this->berechneVKs();
    }
    
    /**
     * Return recalculated new net price based on the rounded default gross price.
     * This is necessary for having consistent gross prices in case of threshold delivery (Tax rate != default tax rate).
     *
     * @param double $netPrice the product net price
     * @param double $defaultTax the default tax factor of the product e.g. 19 for 19% vat
     * @param double $conversionTax the taxFactor of the delivery country / delivery threshold
     * @return double calculated net price based on a rounded(!!!) DEFAULT gross price.
     */
    private function getRecalculatedNetPrice($netPrice, $defaultTax, $conversionTax)
    {
        $newNetPrice = $netPrice;
        if (defined('CONSISTENT_GROSS_PRICES') && CONSISTENT_GROSS_PRICES === true && $defaultTax > 0 && $conversionTax > 0 && $defaultTax != $conversionTax) {
            $newNetPrice = round($netPrice * ($defaultTax + 100) / 100, 2) / ($conversionTax + 100) * 100;
        }
        
        return (double)$newNetPrice;
    }

    /**
     * @param int $kKunde
     * @return bool
     */
    protected function hasCustomPrice($kKunde)
    {
        $kKunde   = (int)$kKunde;
        if ($kKunde > 0) {
            $cacheID = 'custprice_' . $kKunde;
            if (($oCustomPrice = Shop::Cache()->get($cacheID)) === false) {
                $oCustomPrice = Shop::DB()->query(
                    "SELECT count(kPreis) AS nAnzahl 
                        FROM tpreis
                        WHERE kKunde = {$kKunde}",
                    1
                );

                if (is_object($oCustomPrice)) {
                    $cacheTags = [CACHING_GROUP_ARTICLE];
                    Shop::Cache()->set($cacheID, $oCustomPrice, $cacheTags);
                }
            }

            return is_object($oCustomPrice) && $oCustomPrice->nAnzahl > 0;
        }

        return false;
    }

    /**
     * Setzt Preise mit Daten aus der DB mit spezifizierten Primary Keys
     *
     * @access public
     * @param int $kKundengruppe
     * @param int $kArtikel
     * @return $this
     */
    public function loadFromDB($kKundengruppe, $kArtikel)
    {
        $kKundengruppe = (int)$kKundengruppe;
        $kArtikel      = (int)$kArtikel;
        $obj           = Shop::DB()->select('tpreise', 'kArtikel', $kArtikel, 'kKundengruppe', $kKundengruppe);
        if (!empty($obj->kArtikel)) {
            $members = array_keys(get_object_vars($obj));
            foreach ($members as $member) {
                $this->$member = $obj->$member;
            }
            $ust_obj    = Shop::DB()->query("SELECT kSteuerklasse FROM tartikel WHERE kArtikel = " . $kArtikel, 1);
            $this->fUst = gibUst($ust_obj->kSteuerklasse);
            //hat dieser Artikel fuer diese Kundengruppe einen Sonderpreis?
            $sonderpreis = Shop::DB()->query(
                "SELECT tsonderpreise.fNettoPreis
                    FROM tsonderpreise
                    JOIN tartikel 
                        ON tartikel.kArtikel = " . $kArtikel . "
                    JOIN tartikelsonderpreis 
                        ON tartikelsonderpreis.kArtikelSonderpreis = tsonderpreise.kArtikelSonderpreis
                        AND tartikelsonderpreis.kArtikel = " . $kArtikel . "
                        AND tartikelsonderpreis.cAktiv = 'Y'
                        AND tartikelsonderpreis.dStart <= date(now())
                        AND (tartikelsonderpreis.dEnde >= CURDATE() OR tartikelsonderpreis.dEnde = '0000-00-00')
                        AND (tartikelsonderpreis.nAnzahl <= tartikel.fLagerbestand OR tartikelsonderpreis.nIstAnzahl = 0)
                    WHERE tsonderpreise.kKundengruppe = " . $kKundengruppe, 1
            );
            if (isset($sonderpreis->fNettoPreis)) {
                if ($sonderpreis->fNettoPreis && $sonderpreis->fNettoPreis < $this->fVKNetto) {
                    $this->alterVKNetto      = $this->fVKNetto;
                    $this->fVKNetto          = $sonderpreis->fNettoPreis;
                    $this->Sonderpreis_aktiv = 1;
                }
                if ($sonderpreis->fNettoPreis && $sonderpreis->fNettoPreis < $this->fPreis1) {
                    $this->fPreis1 = $sonderpreis->fNettoPreis;
                }
                if ($sonderpreis->fNettoPreis && $sonderpreis->fNettoPreis < $this->fPreis2) {
                    $this->fPreis2 = $sonderpreis->fNettoPreis;
                }
                if ($sonderpreis->fNettoPreis && $sonderpreis->fNettoPreis < $this->fPreis3) {
                    $this->fPreis3 = $sonderpreis->fNettoPreis;
                }
                if ($sonderpreis->fNettoPreis && $sonderpreis->fNettoPreis < $this->fPreis4) {
                    $this->fPreis4 = $sonderpreis->fNettoPreis;
                }
                if ($sonderpreis->fNettoPreis && $sonderpreis->fNettoPreis < $this->fPreis5) {
                    $this->fPreis5 = $sonderpreis->fNettoPreis;
                }
            }
            $this->berechneVKs();
        }

        return $this;
    }

    /**
     * @param float $Rabatt
     * @param float $offset
     * @return $this
     */
    public function rabbatierePreise($Rabatt, $offset = 0.0)
    {
        if ($Rabatt != 0 && !$this->Sonderpreis_aktiv && !$this->Kundenpreis_aktiv) {
            $this->rabatt       = $Rabatt;
            $this->alterVKNetto = $this->fVKNetto;

            $this->fVKNetto = ($this->fVKNetto - $this->fVKNetto * $Rabatt / 100.0) + $offset;
            $this->fPreis1  = ($this->fPreis1 - $this->fPreis1 * $Rabatt / 100.0) + $offset;
            $this->fPreis2  = ($this->fPreis2 - $this->fPreis2 * $Rabatt / 100.0) + $offset;
            $this->fPreis3  = ($this->fPreis3 - $this->fPreis3 * $Rabatt / 100.0) + $offset;
            $this->fPreis4  = ($this->fPreis4 - $this->fPreis4 * $Rabatt / 100.0) + $offset;
            $this->fPreis5  = ($this->fPreis5 - $this->fPreis5 * $Rabatt / 100.0) + $offset;

            foreach ($this->fPreis_arr as $i => $fPreis) {
                $this->fPreis_arr[$i] = ($fPreis - $fPreis * $Rabatt / 100.0) + $offset;
            }
            $this->berechneVKs();
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function localizePreise()
    {
        $this->cPreis1Localized[0] = gibPreisStringLocalized(berechneBrutto($this->fPreis1, $this->fUst));
        $this->cPreis2Localized[0] = gibPreisStringLocalized(berechneBrutto($this->fPreis2, $this->fUst));
        $this->cPreis3Localized[0] = gibPreisStringLocalized(berechneBrutto($this->fPreis3, $this->fUst));
        $this->cPreis4Localized[0] = gibPreisStringLocalized(berechneBrutto($this->fPreis4, $this->fUst));
        $this->cPreis5Localized[0] = gibPreisStringLocalized(berechneBrutto($this->fPreis5, $this->fUst));

        $this->cPreis1Localized[1] = gibPreisStringLocalized($this->fPreis1);
        $this->cPreis2Localized[1] = gibPreisStringLocalized($this->fPreis2);
        $this->cPreis3Localized[1] = gibPreisStringLocalized($this->fPreis3);
        $this->cPreis4Localized[1] = gibPreisStringLocalized($this->fPreis4);
        $this->cPreis5Localized[1] = gibPreisStringLocalized($this->fPreis5);

        $this->cPreisLocalized_arr = [];
        foreach ($this->fPreis_arr as $fPreis) {
            $this->cPreisLocalized_arr[] = [
                gibPreisStringLocalized(berechneBrutto($fPreis, $this->fUst)),
                gibPreisStringLocalized($fPreis)
            ];
        }

        $this->cVKLocalized[0] = gibPreisStringLocalized(berechneBrutto($this->fVKNetto, $this->fUst));
        $this->cVKLocalized[1] = gibPreisStringLocalized($this->fVKNetto);

        $this->fVKBrutto = berechneBrutto($this->fVKNetto, $this->fUst);

        if ($this->alterVKNetto) {
            $this->alterVKLocalized[0] = gibPreisStringLocalized(berechneBrutto($this->alterVKNetto, $this->fUst));
            $this->alterVKLocalized[1] = gibPreisStringLocalized($this->alterVKNetto);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function berechneVKs()
    {
        $waehrung = isset($_SESSION['Waehrung']) ? $_SESSION['Waehrung'] : null;
        if (!isset($waehrung->kWaehrung)) {
            $waehrung = Shop::DB()->select('twaehrung', 'cStandard', 'Y');
        }

        $this->fVKBrutto = berechneBrutto($this->fVKNetto, $this->fUst);

        $this->fVK[0] = berechneBrutto($this->fVKNetto * $waehrung->fFaktor, $this->fUst);
        $this->fVK[1] = $this->fVKNetto * $waehrung->fFaktor;

        $this->alterVK[0] = berechneBrutto($this->alterVKNetto * $waehrung->fFaktor, $this->fUst);
        $this->alterVK[1] = $this->alterVKNetto * $waehrung->fFaktor;

        $this->fStaffelpreis1[0] = berechneBrutto($this->fPreis1 * $waehrung->fFaktor, $this->fUst);
        $this->fStaffelpreis1[1] = $this->fPreis1 * $waehrung->fFaktor;
        $this->fStaffelpreis2[0] = berechneBrutto($this->fPreis2 * $waehrung->fFaktor, $this->fUst);
        $this->fStaffelpreis2[1] = $this->fPreis2 * $waehrung->fFaktor;
        $this->fStaffelpreis3[0] = berechneBrutto($this->fPreis3 * $waehrung->fFaktor, $this->fUst);
        $this->fStaffelpreis3[1] = $this->fPreis3 * $waehrung->fFaktor;
        $this->fStaffelpreis4[0] = berechneBrutto($this->fPreis4 * $waehrung->fFaktor, $this->fUst);
        $this->fStaffelpreis4[1] = $this->fPreis4 * $waehrung->fFaktor;
        $this->fStaffelpreis5[0] = berechneBrutto($this->fPreis5 * $waehrung->fFaktor, $this->fUst);
        $this->fStaffelpreis5[1] = $this->fPreis5 * $waehrung->fFaktor;

        $this->fStaffelpreis_arr = [];
        foreach ($this->fPreis_arr as $fPreis) {
            $this->fStaffelpreis_arr[] = [
                berechneBrutto($fPreis * $waehrung->fFaktor, $this->fUst),
                $fPreis * $waehrung->fFaktor
            ];
        }

        return $this;
    }

    /**
     * Fuegt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @access public
     * @retun int
     */
    public function insertInDB()
    {
        $obj                = new stdClass();
        $obj->kKundengruppe = $this->kKundengruppe;
        $obj->kArtikel      = $this->kArtikel;
        $obj->fVKNetto      = $this->fVKNetto;
        $obj->nAnzahl1      = $this->nAnzahl1;
        $obj->nAnzahl2      = $this->nAnzahl2;
        $obj->nAnzahl3      = $this->nAnzahl3;
        $obj->nAnzahl4      = $this->nAnzahl4;
        $obj->nAnzahl5      = $this->nAnzahl5;
        $obj->fPreis1       = $this->fPreis1;
        $obj->fPreis2       = $this->fPreis2;
        $obj->fPreis3       = $this->fPreis3;
        $obj->fPreis4       = $this->fPreis4;
        $obj->fPreis5       = $this->fPreis5;

        return Shop::DB()->insert('tpreise', $obj);
    }

    /**
     * setzt Daten aus Sync POST request.
     *
     * @return bool - true, wenn alle notwendigen Daten vorhanden, sonst false
     */
    public function setzePostDaten()
    {
        /* @TODO
        $this->kPreisverlauf = Shop::DB()->escape($_POST['PStaffelKey']);
        $this->kArtikel = Shop::DB()->escape($_POST['KeyArtikel']);
        $this->fPreisPrivat = Shop::DB()->escape($_POST['ArtikelVKBrutto']);
        $this->fPreisHaendler = Shop::DB()->escape($_POST['ArtikelVKHaendlerBrutto']);
        $this->dDate = 'now()';
         */
        return $this->kArtikel > 0;
    }

    /**
     * @param int    $kKundengruppe
     * @param string $priceAlias
     * @param string $detailAlias
     * @param string $productAlias
     * @return string
     */
    public static function getPriceJoinSql($kKundengruppe, $priceAlias = 'tpreis', $detailAlias = 'tpreisdetail', $productAlias = 'tartikel')
    {
        $kKundengruppe = (int)$kKundengruppe;

        return "JOIN tpreis AS {$priceAlias} ON {$priceAlias}.kArtikel = {$productAlias}.kArtikel
                    AND {$priceAlias}.kKundengruppe = {$kKundengruppe}
                JOIN tpreisdetail AS {$detailAlias} ON {$detailAlias}.kPreis = {$priceAlias}.kPreis
                    AND {$detailAlias}.nAnzahlAb = 0";
    }

    /**
     * Set all fvk prices to zero.
     */
    public function setPricesToZero()
    {
        $this->fVKNetto  = 0;
        $this->fVKBrutto = 0;
        foreach ($this->fVK as $key => $fVK) {
            $this->fVK[$key] = 0;
        }
        foreach ($this->alterVK as $key => $alterVK) {
            $this->alterVK[$key] = 0;
        }
        $this->fPreis1 = 0;
        $this->fPreis2 = 0;
        $this->fPreis3 = 0;
        $this->fPreis4 = 0;
        $this->fPreis5 = 0;
        foreach ($this->fStaffelpreis1 as $key => $fSteffelpreis1) {
            $this->fStaffelpreis1[$key] = 0;
        }
        foreach ($this->fStaffelpreis2 as $key => $fSteffelpreis2) {
            $this->fStaffelpreis2[$key] = 0;
        }
        foreach ($this->fStaffelpreis3 as $key => $fSteffelpreis3) {
            $this->fStaffelpreis3[$key] = 0;
        }
        foreach ($this->fStaffelpreis4 as $key => $fSteffelpreis4) {
            $this->fStaffelpreis4[$key] = 0;
        }
        foreach ($this->fStaffelpreis5 as $key => $fSteffelpreis5) {
            $this->fStaffelpreis5[$key] = 0;
        }
        foreach ($this->fPreis_arr as $key => $fPreis) {
            $this->fPreis_arr[$key] = 0;
        }
        foreach ($this->fStaffelpreis_arr as $fStaffelpreisKey => $fStaffelpreis) {
            foreach ($fStaffelpreis as $fPreisKey => $fPreis) {
                $this->fStaffelpreis_arr[$fStaffelpreisKey][$fPreisKey] = 0;
            }
        }
    }
}
