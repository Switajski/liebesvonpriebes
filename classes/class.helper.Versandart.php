<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class VersandartHelper
 */
class VersandartHelper
{
    /**
     * @var VersandartHelper
     */
    private static $_instance;

    /**
     * @var string
     */
    public $cacheID;

    /**
     * @var array
     */
    public $shippingMethods;

    /**
     * @var array
     */
    public $countries = [];

    /**
     *
     */
    public function __construct()
    {
        $this->cacheID         = 'smeth_' . Shop::Cache()->getBaseID();
        $this->shippingMethods = $this->getShippingMethods();
        self::$_instance       = $this;
    }

    /**
     * @return VersandartHelper
     */
    public static function getInstance()
    {
        return (self::$_instance === null) ? new self() : self::$_instance;
    }

    /**
     * @return array
     */
    public function getShippingMethods()
    {
        return ($this->shippingMethods === null) 
            ? Shop::DB()->query("SELECT * FROM tversandart", 2) 
            : $this->shippingMethods;
    }

    /**
     * @param float|int $freeFromX
     * @return array
     */
    public function filter($freeFromX)
    {
        $res       = [];
        $freeFromX = (float)$freeFromX;
        foreach ($this->shippingMethods as $_method) {
            if ($_method->fVersandkostenfreiAbX !== '0.00' &&
                (float)$_method->fVersandkostenfreiAbX > 0 &&
                (float)$_method->fVersandkostenfreiAbX <= $freeFromX

            ) {
                $res[] = $_method;
            }
        }

        return $res;
    }

    /**
     * @param float|int $wert
     * @param int       $kKundengruppe
     * @param int       $versandklasse
     * @return string
     */
    public function getFreeShippingCountries($wert, $kKundengruppe, $versandklasse = 0)
    {
        if (!isset($this->countries[$kKundengruppe][$versandklasse])) {
            if (!isset($this->countries[$kKundengruppe])) {
                $this->countries[$kKundengruppe] = [];
            }
            $this->countries[$kKundengruppe][$versandklasse] = Shop::DB()->query(
                "SELECT *
                    FROM tversandart
                    WHERE fVersandkostenfreiAbX > 0
                        AND (cVersandklassen = '-1'
                        OR cVersandklassen RLIKE '^([0-9 -]* )?" . $versandklasse . " ')
                        AND (cKundengruppen = '-1' OR FIND_IN_SET('" . (int)$kKundengruppe
                            . "', REPLACE(cKundengruppen, ';', ',')) > 0)", 2
            );
        }
        $shippingFreeCountries = [];
        foreach ($this->countries[$kKundengruppe][$versandklasse] as $_method) {
            if (isset($_method->fVersandkostenfreiAbX) &&
                (float)$_method->fVersandkostenfreiAbX > 0 &&
                (float)$_method->fVersandkostenfreiAbX < $wert
            ) {
                foreach (explode(' ', $_method->cLaender) as $_country) {
                    if (strlen($_country) > 0) {
                        $shippingFreeCountries[] = $_country;
                    }
                }
            }
        }
        $shippingFreeCountries = array_unique($shippingFreeCountries);
        $res                   = '';
        foreach ($shippingFreeCountries as $i => $_country) {
            $res .= (($i > 0) ? ', ' : '') . $_country;
        }

        return $res;
    }

    /**
     * @param string $cLand
     * @return bool
     */
    public static function normalerArtikelversand($cLand)
    {
        $bNoetig = false;
        /** @var array('Warenkorb') $_SESSION['Warenkorb'] */
        if (isset($_SESSION['Warenkorb']->PositionenArr) && is_array($_SESSION['Warenkorb']->PositionenArr)) {
            foreach ($_SESSION['Warenkorb']->PositionenArr as $Pos) {
                if ($Pos->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL) {
                    if (!self::gibArtikelabhaengigeVersandkosten($cLand, $Pos->Artikel, $Pos->nAnzahl)) {
                        $bNoetig = true;
                        break;
                    }
                }
            }
        }

        return $bNoetig;
    }

    /**
     * @param string $cLand
     * @return bool
     */
    public static function hasSpecificShippingcosts($cLand)
    {
        $result = self::gibArtikelabhaengigeVersandkostenImWK($cLand, $_SESSION['Warenkorb']->PositionenArr);

        return !empty($result);
    }

    /**
     * @former gibMoeglicheVersandarten()
     * @param string $lieferland
     * @param string $plz
     * @param string $versandklassen
     * @param int    $kKundengruppe
     * @return array
     */
    public static function getPossibleShippingMethods($lieferland, $plz, $versandklassen, $kKundengruppe)
    {
        /** @var array('Warenkorb' => Warenkorb) $_SESSION */
        $kSteuerklasse            = $_SESSION['Warenkorb']->gibVersandkostenSteuerklasse();
        $moeglicheVersandarten    = [];
        $minVersand               = 10000;
        $cISO                     = $lieferland;
        $cNurAbhaengigeVersandart = 'N';
        $hasSpecificShippingcosts = self::hasSpecificShippingcosts($lieferland);
        $vatNote                  = null;
        if (self::normalerArtikelversand($lieferland) === false) {
            $cNurAbhaengigeVersandart = 'Y';
        }
        $versandarten = Shop::DB()->queryPrepared(
            "SELECT * FROM tversandart
                WHERE cNurAbhaengigeVersandart = :depOnly
                    AND cLaender LIKE :iso
                    AND (cVersandklassen = '-1'
                    OR cVersandklassen RLIKE :sClasses)
                    AND (cKundengruppen = '-1'
                    OR FIND_IN_SET(:cGroupID, REPLACE(cKundengruppen, ';', ',')) > 0)
                ORDER BY nSort",
            [
                'iso'      => '%' . $cISO . '%',
                'cGroupID' => $kKundengruppe,
                'sClasses' => '^([0-9 -]* )?' . $versandklassen . ' ',
                'depOnly'  => $cNurAbhaengigeVersandart
            ],
            2
        );
        $cnt             = count($versandarten);
        $netPricesActive = (int)$_SESSION['Kundengruppe']->nNettoPreise === 1;

        for ($i = 0; $i < $cnt; $i++) {
            $bSteuerPos                  = $versandarten[$i]->eSteuer !== 'netto';
            $versandarten[$i]->Zuschlag  = gibVersandZuschlag($versandarten[$i], $cISO, $plz);
            $versandarten[$i]->fEndpreis = berechneVersandpreis($versandarten[$i], $cISO, null);
            if ($versandarten[$i]->fEndpreis == -1) {
                unset($versandarten[$i]);
                continue;
            }
            if ($netPricesActive === true) {
                if ($bSteuerPos) {
                    $shippingCosts = $versandarten[$i]->fEndpreis / (100 + gibUst($kSteuerklasse)) * 100.0;
                } else {
                    $shippingCosts = round($versandarten[$i]->fEndpreis, 2);
                }
                $vatNote       = ' ' . Shop::Lang()->get('plus', 'productDetails') . ' ' . Shop::Lang()->get('vat', 'productDetails');
            } else {
                if ($bSteuerPos) {
                    $shippingCosts = $versandarten[$i]->fEndpreis;
                } else {
                    $shippingCosts = round($versandarten[$i]->fEndpreis * (100 + gibUst($kSteuerklasse)) / 100, 2);
                }
            }
            //posname lokalisiert ablegen
            $versandarten[$i]->angezeigterName           = [];
            $versandarten[$i]->angezeigterHinweistext    = [];
            $versandarten[$i]->cLieferdauer              = [];
            $versandarten[$i]->specificShippingcosts_arr = null;
            foreach ($_SESSION['Sprachen'] as $Sprache) {
                $name_spr = Shop::DB()->select(
                    'tversandartsprache',
                    'kVersandart',
                    (int)$versandarten[$i]->kVersandart,
                    'cISOSprache',
                    $Sprache->cISO
                );
                if (isset($name_spr->cName)) {
                    $versandarten[$i]->angezeigterName[$Sprache->cISO]        = $name_spr->cName;
                    $versandarten[$i]->angezeigterHinweistext[$Sprache->cISO] = $name_spr->cHinweistextShop;
                    $versandarten[$i]->cLieferdauer[$Sprache->cISO]           = $name_spr->cLieferdauer;
                }
            }
            if ($versandarten[$i]->fEndpreis < $minVersand) {
                $minVersand = $versandarten[$i]->fEndpreis;
            }
            // lokalisieren
            // Versandart Versandkostenfrei
            if ($versandarten[$i]->fEndpreis == 0) {
                // Abfrage ob ein Artikel Artikelabhängige Versandkosten besitzt
                $versandarten[$i]->cPreisLocalized = Shop::Lang()->get('freeshipping', 'global');
                if ($hasSpecificShippingcosts === true) {
                    $versandarten[$i]->specificShippingcosts_arr = self::gibArtikelabhaengigeVersandkostenImWK(
                        $lieferland,
                        $_SESSION['Warenkorb']->PositionenArr
                    );
                }
            // Versandartkosten
            } else {
                // Abfrage ob ein Artikel Artikelabhängige Versandkosten besitzt
                $versandarten[$i]->cPreisLocalized = gibPreisStringLocalized($shippingCosts) . ($vatNote !== null
                        ? $vatNote
                        : '');
                if ($hasSpecificShippingcosts === true) {
                    $versandarten[$i]->specificShippingcosts_arr = self::gibArtikelabhaengigeVersandkostenImWK(
                        $lieferland,
                        $_SESSION['Warenkorb']->PositionenArr
                    );
                }
            }
            //Abfrage ob die Zahlungsart/en zur Versandart gesetzt ist/sind
            $zahlungsarten = Shop::DB()->query(
                "SELECT tversandartzahlungsart.*, tzahlungsart.*
                     FROM tversandartzahlungsart, tzahlungsart
                     WHERE tversandartzahlungsart.kVersandart = " . (int)$versandarten[$i]->kVersandart . "
                         AND tversandartzahlungsart.kZahlungsart = tzahlungsart.kZahlungsart
                         AND (tzahlungsart.cKundengruppen IS NULL OR tzahlungsart.cKundengruppen=''
                         OR FIND_IN_SET('{$kKundengruppe}', REPLACE(tzahlungsart.cKundengruppen, ';', ',')) > 0)
                         AND tzahlungsart.nActive = 1
                         AND tzahlungsart.nNutzbar = 1
                     ORDER BY tzahlungsart.nSort", 2
            );
            $bVersandGueltig = false;
            foreach ($zahlungsarten as $zahlungsart) {
                if (ZahlungsartHelper::shippingMethodWithValidPaymentMethod($zahlungsart)) {
                    $bVersandGueltig = true;
                    break;
                }
            }
            if (!$bVersandGueltig) {
                unset($versandarten[$i]);
            }
        }
        $versandarten = array_merge($versandarten);
        //auf anzeige filtern
        foreach ($versandarten as $versandart) {
            switch ($versandart->cAnzeigen) {
                case 'guenstigste' :
                    if ($versandart->fEndpreis <= $minVersand) {
                        $moeglicheVersandarten[] = $versandart;
                    }
                    break;

                case 'immer' :
                    $moeglicheVersandarten[] = $versandart;
                    break;

                default:
                    break;
            }
        }
        //evtl. Versandkupon anwenden
        if (isset($_SESSION['VersandKupon']) && $_SESSION['VersandKupon']) {
            $smCount = count($moeglicheVersandarten);
            for ($i = 0; $i < $smCount; ++$i) {
                $moeglicheVersandarten[$i]->fEndpreis = 0;
                //lokalisieren
                $moeglicheVersandarten[$i]->cPreisLocalized = gibPreisStringLocalized($versandarten[$i]->fEndpreis);
            }
        }

        return $moeglicheVersandarten;
    }

    /**
     * @former ermittleVersandkosten()
     * @param string $cLand
     * @param string $cPLZ
     * @param string $cError
     * @return bool
     */
    public static function getShippingCosts($cLand, $cPLZ, &$cError = '')
    {
        if ($cLand !== null && $cPLZ !== null && strlen($cLand) > 0 && strlen($cPLZ) > 0) {
            $kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;
            if (isset($_SESSION['Kunde']->kKundengruppe) && $_SESSION['Kunde']->kKundengruppe > 0) {
                $kKundengruppe = $_SESSION['Kunde']->kKundengruppe;
            }

            $oVersandart_arr = self::getPossibleShippingMethods(
                StringHandler::filterXSS($cLand),
                StringHandler::filterXSS($cPLZ),
                self::getShippingClasses($_SESSION['Warenkorb']),
                $kKundengruppe
            );
            if (count($oVersandart_arr) > 0) {
                Shop::Smarty()->assign('ArtikelabhaengigeVersandarten', self::gibArtikelabhaengigeVersandkostenImWK(
                    $cLand,
                    $_SESSION['Warenkorb']->PositionenArr
                ))
                    ->assign('Versandarten', $oVersandart_arr)
                    ->assign('Versandland', ISO2land(StringHandler::filterXSS($cLand)))
                    ->assign('VersandPLZ', StringHandler::filterXSS($cPLZ));
            } else {
                $cError = Shop::Lang()->get('noDispatchAvailable', 'global');
            }
            executeHook(HOOK_WARENKORB_PAGE_ERMITTLEVERSANDKOSTEN);

            return true;
        }
        if ((strlen($cLand) === 0 && isset($_POST['versandrechnerBTN'])) ||
            (strlen($cPLZ) === 0 && isset($_POST['versandrechnerBTN']))
        ) {
            return false;
        }

        return true;
    }

    /**
     * @former ermittleVersandkostenExt()
     * @param array $oArtikel_arr
     * @return string
     */
    public static function getShippingCostsExt($oArtikel_arr)
    {
        if (!isset($_SESSION['shipping_count'])) {
            $_SESSION['shipping_count'] = 0;
        }
        if (!is_array($oArtikel_arr) || count($oArtikel_arr) === 0) {
            return null;
        }
        $cLandISO = isset($_SESSION['cLieferlandISO']) ? $_SESSION['cLieferlandISO'] : false;
        if (!$cLandISO) {
            //Falls kein Land in tfirma da
            $cLandISO = 'DE';
        }

        $kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;
        // Baue ZusatzArtikel
        $oZusatzArtikel                  = new stdClass();
        $oZusatzArtikel->fAnzahl         = 0;
        $oZusatzArtikel->fWarenwertNetto = 0;
        $oZusatzArtikel->fGewicht        = 0;

        $cVersandklassen                                   = self::getShippingClasses($_SESSION['Warenkorb']);
        $conf                                              = Shop::getSettings([CONF_KAUFABWICKLUNG]);
        $fSummeHinzukommendeArtikelabhaengigeVersandkosten = 0;
        $fWarensummeProSteuerklasse_arr                    = [];
        $kSteuerklasse                                     = 0;
        // Vorkonditionieren -- Gleiche kartikel aufsummieren
        // aber nur, wenn artikelabhaengiger Versand bei dem jeweiligen kArtikel
        $nArtikelAssoc_arr = [];
        foreach ($oArtikel_arr as $oArtikel) {
            $kArtikel                     = $oArtikel['kArtikel'];
            $nArtikelAssoc_arr[$kArtikel] = (!isset($nArtikelAssoc_arr[$kArtikel])) ? 0 : 1;
        }

        $bMerge         = false;
        $defaultOptions = Artikel::getDefaultOptions();
        foreach ($nArtikelAssoc_arr as $kArtikel => $nArtikelAssoc) {
            if ($nArtikelAssoc == 1) {
                $oArtikelTMP = new Artikel();
                $oArtikelTMP->fuelleArtikel($kArtikel, $defaultOptions);
                // Normaler Variationsartikel
                if (
                    $oArtikelTMP->nIstVater == 0 &&
                    $oArtikelTMP->kVaterArtikel == 0 &&
                    count($oArtikelTMP->Variationen) > 0
                ) {
                    // Nur wenn artikelabhaengiger Versand gestaffelt als Funktionsattribut gesetzt ist
                    if (self::pruefeArtikelabhaengigeVersandkosten($oArtikelTMP) == 2) {
                        $fAnzahl      = 0;
                        $nArrayAnzahl = count($oArtikel_arr);
                        for ($i = 0; $i < $nArrayAnzahl; $i++) {
                            if ($oArtikel_arr[$i]['kArtikel'] == $kArtikel) {
                                $fAnzahl += $oArtikel_arr[$i]['fAnzahl'];
                                unset($oArtikel_arr[$i]);
                            }
                        }

                        $oArtikelMerged             = [];
                        $oArtikelMerged['kArtikel'] = $kArtikel;
                        $oArtikelMerged['fAnzahl']  = $fAnzahl;
                        $oArtikel_arr[]             = $oArtikelMerged;
                        $bMerge                     = true;
                    }
                }
            }
        }

        if ($bMerge) {
            $oArtikel_arr = array_merge($oArtikel_arr);
        }

        $defaultOptions = Artikel::getDefaultOptions();
        foreach ($oArtikel_arr as $i => $oArtikel) {
            $oArtikelTMP = new Artikel();
            $oArtikelTMP->fuelleArtikel($oArtikel['kArtikel'], $defaultOptions);
            $kSteuerklasse = $oArtikelTMP->kSteuerklasse;

            if ($oArtikelTMP->kArtikel !== null && $oArtikelTMP->kArtikel > 0) {
                // Artikelabhaengige Versandkosten?
                if ($oArtikelTMP->nIstVater == 0) {
                    //Summen pro Steuerklasse summieren
                    if ($oArtikelTMP->kSteuerklasse === null) {
                        $fWarensummeProSteuerklasse_arr[$oArtikelTMP->kSteuerklasse] = 0;
                    }

                    $fWarensummeProSteuerklasse_arr[$oArtikelTMP->kSteuerklasse] +=
                        $oArtikelTMP->Preise->fVKNetto * $oArtikel['fAnzahl'];

                    $oVersandPos = self::gibHinzukommendeArtikelAbhaengigeVersandkosten(
                        $oArtikelTMP,
                        $cLandISO,
                        $oArtikel['fAnzahl']
                    );
                    if ($oVersandPos !== false) {
                        $fSummeHinzukommendeArtikelabhaengigeVersandkosten += $oVersandPos->fKosten;
                        continue;
                    }
                }
                // Normaler Artikel oder Kind Artikel
                if ($oArtikelTMP->kVaterArtikel > 0 || count($oArtikelTMP->Variationen) === 0) {
                    $oZusatzArtikel->fAnzahl += $oArtikel['fAnzahl'];
                    $oZusatzArtikel->fWarenwertNetto += $oArtikel['fAnzahl'] * $oArtikelTMP->Preise->fVKNetto;
                    $oZusatzArtikel->fGewicht += $oArtikel['fAnzahl'] * $oArtikelTMP->fGewicht;

                    if (strlen($cVersandklassen) > 0 && strpos($cVersandklassen, $oArtikelTMP->kVersandklasse) === false) {
                        $cVersandklassen = '-' . $oArtikelTMP->kVersandklasse;
                    } elseif (strlen($cVersandklassen) === 0) {
                        $cVersandklassen = $oArtikelTMP->kVersandklasse;
                    }
                } elseif ($oArtikelTMP->nIstVater == 0 &&
                    $oArtikelTMP->kVaterArtikel == 0 &&
                    count($oArtikelTMP->Variationen) > 0
                ) { // Normale Variation
                    if ($oArtikel['cInputData']{0} == '_') {
                        // 1D
                        $cVariation0                             = substr($oArtikel['cInputData'], 1);
                        list($kEigenschaft0, $kEigenschaftWert0) = explode(':', $cVariation0);

                        $oVariation = findeVariation($oArtikelTMP->Variationen, $kEigenschaft0, $kEigenschaftWert0);

                        $oZusatzArtikel->fAnzahl += $oArtikel['fAnzahl'];
                        $oZusatzArtikel->fWarenwertNetto += $oArtikel['fAnzahl'] *
                            ($oArtikelTMP->Preise->fVKNetto + $oVariation->fAufpreisNetto);
                        $oZusatzArtikel->fGewicht += $oArtikel['fAnzahl'] *
                            ($oArtikelTMP->fGewicht + $oVariation->fGewichtDiff);
                    } else {
                        // 2D
                        list($cVariation0, $cVariation1)         = explode('_', $oArtikel['cInputData']);
                        list($kEigenschaft0, $kEigenschaftWert0) = explode(':', $cVariation0);
                        list($kEigenschaft1, $kEigenschaftWert1) = explode(':', $cVariation1);

                        $oVariation0 = findeVariation($oArtikelTMP->Variationen, $kEigenschaft0, $kEigenschaftWert0);
                        $oVariation1 = findeVariation($oArtikelTMP->Variationen, $kEigenschaft1, $kEigenschaftWert1);

                        $oZusatzArtikel->fAnzahl += $oArtikel['fAnzahl'];
                        $oZusatzArtikel->fWarenwertNetto += $oArtikel['fAnzahl'] *
                            ($oArtikelTMP->Preise->fVKNetto + $oVariation0->fAufpreisNetto + $oVariation1->fAufpreisNetto);
                        $oZusatzArtikel->fGewicht += $oArtikel['fAnzahl'] *
                            ($oArtikelTMP->fGewicht + $oVariation0->fGewichtDiff + $oVariation1->fGewichtDiff);
                    }
                    if (strlen($cVersandklassen) > 0 && strpos($cVersandklassen, $oArtikelTMP->kVersandklasse) === false) {
                        $cVersandklassen = '-' . $oArtikelTMP->kVersandklasse;
                    } elseif (strlen($cVersandklassen) === 0) {
                        $cVersandklassen = $oArtikelTMP->kVersandklasse;
                    }
                } elseif ($oArtikelTMP->nIstVater > 0) { // Variationskombination (Vater)
                    $oArtikelKind = new Artikel();
                    if ($oArtikel['cInputData']{0} === '_') {
                        // 1D
                        $cVariation0                             = substr($oArtikel['cInputData'], 1);
                        list($kEigenschaft0, $kEigenschaftWert0) = explode(':', $cVariation0);
                        $kKindArtikel                            = findeKindArtikelZuEigenschaft(
                            $oArtikelTMP->kArtikel,
                            $kEigenschaft0,
                            $kEigenschaftWert0
                        );
                        $oArtikelKind->fuelleArtikel($kKindArtikel, $defaultOptions);
                        //Summen pro Steuerklasse summieren
                        if (!array_key_exists($oArtikelKind->kSteuerklasse, $fWarensummeProSteuerklasse_arr)) {
                            $fWarensummeProSteuerklasse_arr[$oArtikelKind->kSteuerklasse] = 0;
                        }

                        $fWarensummeProSteuerklasse_arr[$oArtikelKind->kSteuerklasse] +=
                            $oArtikelKind->Preise->fVKNetto * $oArtikel['fAnzahl'];

                        $fSumme = self::gibHinzukommendeArtikelAbhaengigeVersandkosten(
                            $oArtikelKind,
                            $cLandISO,
                            $oArtikel['fAnzahl']
                        );
                        if ($fSumme !== false) {
                            $fSummeHinzukommendeArtikelabhaengigeVersandkosten += $fSumme;
                            continue;
                        }

                        $oZusatzArtikel->fAnzahl += $oArtikel['fAnzahl'];
                        $oZusatzArtikel->fWarenwertNetto += $oArtikel['fAnzahl'] * $oArtikelKind->Preise->fVKNetto;
                        $oZusatzArtikel->fGewicht += $oArtikel['fAnzahl'] * $oArtikelKind->fGewicht;
                    } else {
                        // 2D
                        list($cVariation0, $cVariation1)         = explode('_', $oArtikel['cInputData']);
                        list($kEigenschaft0, $kEigenschaftWert0) = explode(':', $cVariation0);
                        list($kEigenschaft1, $kEigenschaftWert1) = explode(':', $cVariation1);

                        $kKindArtikel = findeKindArtikelZuEigenschaft(
                            $oArtikelTMP->kArtikel,
                            $kEigenschaft0,
                            $kEigenschaftWert0,
                            $kEigenschaft1,
                            $kEigenschaftWert1
                        );
                        $oArtikelKind->fuelleArtikel($kKindArtikel, $defaultOptions);
                        //Summen pro Steuerklasse summieren
                        if (!array_key_exists($oArtikelKind->kSteuerklasse, $fWarensummeProSteuerklasse_arr)) {
                            $fWarensummeProSteuerklasse_arr[$oArtikelKind->kSteuerklasse] = 0;
                        }

                        $fWarensummeProSteuerklasse_arr[$oArtikelKind->kSteuerklasse] += $oArtikelKind->Preise->fVKNetto * $oArtikel['fAnzahl'];

                        $fSumme = self::gibHinzukommendeArtikelAbhaengigeVersandkosten($oArtikelKind, $cLandISO, $oArtikel['fAnzahl']);
                        if ($fSumme !== false) {
                            $fSummeHinzukommendeArtikelabhaengigeVersandkosten += $fSumme;
                            continue;
                        }

                        $oZusatzArtikel->fAnzahl += $oArtikel['fAnzahl'];
                        $oZusatzArtikel->fWarenwertNetto += $oArtikel['fAnzahl'] * $oArtikelKind->Preise->fVKNetto;
                        $oZusatzArtikel->fGewicht += $oArtikel['fAnzahl'] * $oArtikelKind->fGewicht;
                    }
                    if (strlen($cVersandklassen) > 0 && strpos($cVersandklassen, $oArtikelKind->kVersandklasse) === false) {
                        $cVersandklassen = '-' . $oArtikelKind->kVersandklasse;
                    } elseif (strlen($cVersandklassen) === 0) {
                        $cVersandklassen = $oArtikelKind->kVersandklasse;
                    }
                }
            }
        }

        if (isset($_SESSION['Warenkorb']->PositionenArr) &&
            is_array($_SESSION['Warenkorb']->PositionenArr) &&
            count($_SESSION['Warenkorb']->PositionenArr) > 0
        ) {
            // Wenn etwas im Warenkorb ist, dann Vesandart vom Warenkorb rausfinden
            $oVersandartNurWK                   = gibGuenstigsteVersandart(
                $cLandISO,
                $cVersandklassen,
                $kKundengruppe,
                null
            );
            $oArtikelAbhaenigeVersandkosten_arr = self::gibArtikelabhaengigeVersandkostenImWK(
                $cLandISO,
                $_SESSION['Warenkorb']->PositionenArr
            );

            $fSumme = 0;
            if (count($oArtikelAbhaenigeVersandkosten_arr) > 0) {
                foreach ($oArtikelAbhaenigeVersandkosten_arr as $oArtikelAbhaenigeVersandkosten) {
                    $fSumme += $oArtikelAbhaenigeVersandkosten->fKosten;
                }
            }

            $oVersandartNurWK->fEndpreis += $fSumme;
            $oVersandart = gibGuenstigsteVersandart($cLandISO, $cVersandklassen, $kKundengruppe, $oZusatzArtikel);
            $oVersandart->fEndpreis += ($fSumme + $fSummeHinzukommendeArtikelabhaengigeVersandkosten);
        } else {
            $oVersandartNurWK            = new stdClass();
            $oVersandart                 = new stdClass();
            $oVersandartNurWK->fEndpreis = 0;
            $oVersandart->fEndpreis      = $fSummeHinzukommendeArtikelabhaengigeVersandkosten;
        }

        if (abs($oVersandart->fEndpreis - $oVersandartNurWK->fEndpreis) > 0.01) {
            //Versand mit neuen Artikeln > als Versand ohne
            //Steuerklasse bestimmen
            if (is_array($_SESSION['Warenkorb']->PositionenArr)) {
                foreach ($_SESSION['Warenkorb']->PositionenArr as $oPosition) {
                    if ($oPosition->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL) {
                        //Summen pro Steuerklasse summieren
                        if (!array_key_exists($oPosition->Artikel->kSteuerklasse, $fWarensummeProSteuerklasse_arr)) {
                            $fWarensummeProSteuerklasse_arr[$oPosition->Artikel->kSteuerklasse] = 0;
                        }
                        $fWarensummeProSteuerklasse_arr[$oPosition->Artikel->kSteuerklasse] +=
                            $oPosition->Artikel->Preise->fVKNetto * $oPosition->nAnzahl;
                    }
                }
            }

            if ($conf['kaufabwicklung']['bestellvorgang_versand_steuersatz'] === 'US') {
                $nMaxSumme = 0;
                foreach ($fWarensummeProSteuerklasse_arr as $j => $fWarensummeProSteuerklasse) {
                    if ($fWarensummeProSteuerklasse > $nMaxSumme) {
                        $nMaxSumme     = $fWarensummeProSteuerklasse;
                        $kSteuerklasse = $j;
                    }
                }
            } else {
                $nMaxSteuersatz = 0;
                foreach ($fWarensummeProSteuerklasse_arr as $j => $fWarensummeProSteuerklasse) {
                    if (gibUst($j) > $nMaxSteuersatz) {
                        $nMaxSteuersatz = gibUst($j);
                        $kSteuerklasse  = $j;
                    }
                }
            }

            return sprintf(
                Shop::Lang()->get('productExtraShippingNotice', 'global'),
                gibPreisStringLocalized(berechneBrutto($oVersandart->fEndpreis, gibUst($kSteuerklasse)) . ' an.')
            );
        }
         
        //Versand mit neuen Artikeln gleich oder guenstiger als ohne
        return Shop::Lang()->get('productNoExtraShippingNotice', 'global');
    }

    /**
     * Prueft, ob es artikelabhaengige Versandkosten gibt und falls ja,
     * wird die hinzukommende Versandsumme fuer den Artikel
     * der hinzugefuegt werden soll errechnet und zurueckgegeben.
     *
     * @param Artikel $oArtikel
     * @param string  $cLandISO
     * @param float   $fArtikelAnzahl
     * @return bool|stdClass
     */
    public static function gibHinzukommendeArtikelAbhaengigeVersandkosten($oArtikel, $cLandISO, $fArtikelAnzahl)
    {
        // Prueft, ob es Artikel abhaengige Versandkosten bei dem hinzukommenden Artikel gibt
        $nArtikelAbhaengigeVersandkosten = self::pruefeArtikelabhaengigeVersandkosten($oArtikel);

        if ($nArtikelAbhaengigeVersandkosten == 1) {
            // Artikelabhaengige Versandkosten

            return self::gibArtikelabhaengigeVersandkosten($cLandISO, $oArtikel, $fArtikelAnzahl, false);
        } elseif ($nArtikelAbhaengigeVersandkosten == 2) {
            // Artikelabhaengige Versandkosten Gestaffelt

            // Gib alle Artikel im Warenkorb, die Artikel abhaengige Versandkosten beinhalten
            $oWarenkorbArtikelAbhaengigerVersand_arr = self::gibArtikelabhaengigeVersandkostenImWK(
                $cLandISO,
                $_SESSION['Warenkorb']->PositionenArr,
                false
            );

            if (count($oWarenkorbArtikelAbhaengigerVersand_arr) > 0) {
                $nAnzahl = $fArtikelAnzahl;
                $fKosten = 0;
                foreach ($oWarenkorbArtikelAbhaengigerVersand_arr as $oWarenkorbArtikelAbhaengigerVersand) {
                    // Wenn es bereits den hinzukommenden Artikel im Warenkorb gibt
                    // zaehle die Anzahl vom Warenkorb hinzu und gib die Kosten fuer den Artikel im Warenkorb
                    if ($oWarenkorbArtikelAbhaengigerVersand->kArtikel == $oArtikel->kArtikel) {
                        // Zaehle die Anzahl des gleichen Artikels im Warenkorb auf die Anzahl die hinzukommen soll hinzu
                        $nAnzahl += $oWarenkorbArtikelAbhaengigerVersand->nAnzahl;
                        // Die Kosten vom Artikel im Warenkorb merken
                        $fKosten = $oWarenkorbArtikelAbhaengigerVersand->fKosten;
                        break;
                    }
                }

                // Gib die Differenzsumme fuer den hinzukommen Artikel zurueck
                return self::gibArtikelabhaengigeVersandkosten($cLandISO, $oArtikel, $nAnzahl, false) - $fKosten;
            }
        }

        return false;
    }

    /**
     * @param Artikel $oArtikel
     * @return int
     */
    public static function pruefeArtikelabhaengigeVersandkosten($oArtikel)
    {
        $bHookReturn = false;
        executeHook(HOOK_TOOLS_GLOBAL_PRUEFEARTIKELABHAENGIGEVERSANDKOSTEN, [
                'oArtikel'    => &$oArtikel,
                'bHookReturn' => &$bHookReturn
            ]
        );

        if ($bHookReturn) {
            return -1;
        }

        if ($oArtikel->FunktionsAttribute[FKT_ATTRIBUT_VERSANDKOSTEN]) {
            // Artikelabhaengige Versandkosten
            return 1;
        }

        if ($oArtikel->FunktionsAttribute[FKT_ATTRIBUT_VERSANDKOSTEN_GESTAFFELT]) {
            // Artikelabhaengige Versandkosten gestaffelt
            return 2;
        }

        return -1;  // Keine artikelabhaengigen Versandkosten
    }

    /**
     * @param string  $cLand
     * @param Artikel $Artikel
     * @param int     $nAnzahl
     * @param bool    $bCheckLieferadresse
     * @return bool|stdClass
     */
    public static function gibArtikelabhaengigeVersandkosten($cLand, $Artikel, $nAnzahl, $bCheckLieferadresse = true)
    {
        $steuerSatz  = null;
        $bHookReturn = false;
        executeHook(HOOK_TOOLS_GLOBAL_GIBARTIKELABHAENGIGEVERSANDKOSTEN, [
                'oArtikel'    => &$Artikel,
                'cLand'       => &$cLand,
                'nAnzahl'     => &$nAnzahl,
                'bHookReturn' => &$bHookReturn
            ]
        );

        if ($bHookReturn) {
            return false;
        }
        $netPricesActive = (int)$_SESSION['Kundengruppe']->nNettoPreise === 1;
        // Steuersatz nur benötigt, wenn Nettokunde
        /** @var array('Warenkorb') $_SESSION['Warenkorb'] */
        if ($netPricesActive === true) {
            $steuerDaten = Shop::DB()->select(
                'tsteuersatz',
                'kSteuerklasse',
                (int)$_SESSION['Warenkorb']->gibVersandkostenSteuerklasse()
            );
            $steuerSatz  = $steuerDaten->fSteuersatz;
        }
        //gestaffelte
        if (isset($Artikel->FunktionsAttribute[FKT_ATTRIBUT_VERSANDKOSTEN_GESTAFFELT]) &&
            $Artikel->FunktionsAttribute[FKT_ATTRIBUT_VERSANDKOSTEN_GESTAFFELT]
        ) {
            $arrVersand = explode(';', $Artikel->FunktionsAttribute[FKT_ATTRIBUT_VERSANDKOSTEN_GESTAFFELT]);
            foreach ($arrVersand as $cVersand) {
                if ($cVersand) {
                    //DE 1-45,00:2-60,00:3-80;AT 1-90,00:2-120,00:3-150,00
                    list($cLandAttr, $KostenTeil) = explode(' ', $cVersand);
                    if ($cLandAttr && ($cLand === $cLandAttr || $bCheckLieferadresse === false)) {
                        $arrKosten = explode(':', $KostenTeil);
                        foreach ($arrKosten as $staffel) {
                            list($bisAnzahl, $fPreis) = explode('-', $staffel);
                            $fPreis                   = (float)str_replace(',', '.', $fPreis);
                            if ($fPreis >= 0 && $bisAnzahl > 0 && $nAnzahl <= $bisAnzahl) {
                                $oVersandPos = new stdClass();
                                //posname lokalisiert ablegen
                                $oVersandPos->cName = [];
                                foreach ($_SESSION['Sprachen'] as $Sprache) {
                                    $oVersandPos->cName[$Sprache->cISO] = Shop::Lang()->get('shippingFor', 'checkout') .
                                        ' ' . $Artikel->cName . ' (' . $cLandAttr . ')';
                                }
                                $oVersandPos->fKosten         = $fPreis;
                                if ($netPricesActive === true) {
                                    $oVersandPos->cPreisLocalized = gibPreisStringLocalized(
                                        berechneNetto((float)$oVersandPos->fKosten, $steuerSatz)
                                        ) . ' ' . Shop::Lang()->get('plus', 'productDetails') . ' ' .
                                        Shop::Lang()->get('vat', 'productDetails');
                                } else {
                                    $oVersandPos->cPreisLocalized = gibPreisStringLocalized($oVersandPos->fKosten);
                                }

                                return $oVersandPos;
                            }
                        }
                    }
                }
            }
        }
        //flache
        if (isset($Artikel->FunktionsAttribute[FKT_ATTRIBUT_VERSANDKOSTEN]) && $Artikel->FunktionsAttribute[FKT_ATTRIBUT_VERSANDKOSTEN]) {
            $arrVersand = explode(';', $Artikel->FunktionsAttribute[FKT_ATTRIBUT_VERSANDKOSTEN]);
            foreach ($arrVersand as $cVersand) {
                if ($cVersand) {
                    list($cLandAttr, $fKosten) = explode(' ', $cVersand);
                    if ($cLandAttr && ($cLand == $cLandAttr || $bCheckLieferadresse == false)) {
                        $oVersandPos = new stdClass();
                        //posname lokalisiert ablegen
                        $oVersandPos->cName = [];
                        foreach ($_SESSION['Sprachen'] as $Sprache) {
                            $oVersandPos->cName[$Sprache->cISO] = Shop::Lang()->get('shippingFor', 'checkout') . ' ' .
                                $Artikel->cName . ' (' . $cLandAttr . ')';
                        }
                        $oVersandPos->fKosten         = (float)str_replace(',', '.', $fKosten) * $nAnzahl;
                        if ($netPricesActive === true) {
                            $oVersandPos->cPreisLocalized = gibPreisStringLocalized(berechneNetto(
                                    (float)$oVersandPos->fKosten,
                                    $steuerSatz
                                )) . ' ' .  Shop::Lang()->get('plus', 'productDetails') . ' ' .
                                Shop::Lang()->get('vat', 'productDetails');
                        } else {
                            $oVersandPos->cPreisLocalized = gibPreisStringLocalized($oVersandPos->fKosten);
                        }

                        return $oVersandPos;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param string $cLand
     * @param array  $PositionenArr
     * @param bool   $bCheckLieferadresse
     * @return array
     */
    public static function gibArtikelabhaengigeVersandkostenImWK($cLand, $PositionenArr, $bCheckLieferadresse = true)
    {
        $arrVersandpositionen = [];
        if (is_array($PositionenArr)) {
            foreach ($PositionenArr as $Pos) {
                if ($Pos->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL  && is_object($Pos->Artikel)) {
                    unset($oVersandPos);
                    $oVersandPos = self::gibArtikelabhaengigeVersandkosten(
                        $cLand,
                        $Pos->Artikel,
                        $Pos->nAnzahl,
                        $bCheckLieferadresse
                    );
                    if (isset($oVersandPos->cName) && $oVersandPos->cName) {
                        $oVersandPos->kArtikel  = $Pos->Artikel->kArtikel;
                        $arrVersandpositionen[] = $oVersandPos;
                    }
                }
            }
        }

        return $arrVersandpositionen;
    }

    /**
     * @param Warenkorb $Warenkorb
     * @return string
     */
    public static function getShippingClasses($Warenkorb)
    {
        $VKarr = [];
        if (isset($Warenkorb->PositionenArr) && is_array($Warenkorb->PositionenArr)) {
            foreach ($Warenkorb->PositionenArr as $pos) {
                if ($pos->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL && !in_array($pos->kVersandklasse, $VKarr)) {
                    if ((int)$pos->kVersandklasse > 0) {
                        $VKarr[] = $pos->kVersandklasse;
                    }
                }
            }
            sort($VKarr);
        }

        return implode('-', $VKarr);
    }
}
