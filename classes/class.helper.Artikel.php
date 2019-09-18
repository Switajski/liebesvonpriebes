<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class ArtikelHelper
 */
class ArtikelHelper
{
    /**
     * @param int $kArtikel
     * @return bool
     */
    public static function isVariChild($kArtikel)
    {
        $kArtikel = (int)$kArtikel;
        if ($kArtikel > 0) {
            $oArtikel = Shop::DB()->select(
                'tartikel',
                'kArtikel',
                $kArtikel,
                null,
                null,
                null,
                null,
                false,
                'kEigenschaftKombi'
            );

            return isset($oArtikel->kEigenschaftKombi) && (int)$oArtikel->kEigenschaftKombi > 0;
        }

        return false;
    }

    /**
     * @param int $kArtikel
     * @return int
     */
    public static function getParent($kArtikel)
    {
        $kArtikel = (int)$kArtikel;
        if ($kArtikel > 0) {
            $oArtikel = Shop::DB()->select(
                'tartikel',
                'kArtikel',
                $kArtikel,
                null,
                null,
                null,
                null,
                false,
                'kVaterArtikel'
            );

            return (isset($oArtikel->kVaterArtikel) && (int)$oArtikel->kVaterArtikel > 0)
                ? (int)$oArtikel->kVaterArtikel
                : 0;
        }

        return 0;
    }

    /**
     * @param int $kArtikel
     * @return bool
     */
    public static function isVariCombiChild($kArtikel)
    {
        return self::getParent($kArtikel) > 0;
    }

    /**
     * Holt fuer einen kVaterArtikel + gesetzte Eigenschaften, den kArtikel vom Variationskombikind
     *
     * @param int $kArtikel
     * @return int
     */
    public static function getArticleForParent($kArtikel)
    {
        $kArtikel            = (int)$kArtikel;
        $kKundengruppe       = (int)$_SESSION['Kundengruppe']->kKundengruppe;
        $properties          = self::getChildPropertiesForParent($kArtikel, $kKundengruppe);
        $kVariationKombi_arr = [];
        $nGueltig            = 1;
        if (count($properties) > 0) {
            foreach ($properties as $i => $kAlleEigenschaftWerteProEigenschaft) {
                if (!self::hasSelectedVariationValue($i)) {
                    $nGueltig = 0;
                    break;
                } else {
                    $kVariationKombi_arr[$i] = self::getSelectedVariationValue($i);
                }
            }
        }
        if ($nGueltig) {
            $cSQL1       = '';
            $cSQL2       = '';
            $oArtikelTMP = new stdClass();
            $j           = 0;
            if (is_array($kVariationKombi_arr) && count($kVariationKombi_arr) > 0) {
                foreach ($kVariationKombi_arr as $i => $kVariationKombi) {
                    if ($j > 0) {
                        $cSQL1 .= ',' . $i;
                        $cSQL2 .= ',' . (int)$kVariationKombi;
                    } else {
                        $cSQL1 .= $i;
                        $cSQL2 .= (int)$kVariationKombi;
                    }
                    $j++;
                }
                $oArtikelTMP = Shop::DB()->query(
                    "SELECT tartikel.kArtikel
                        FROM teigenschaftkombiwert
                        JOIN tartikel
                            ON tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                        LEFT JOIN tartikelsichtbarkeit
                            ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                        WHERE teigenschaftkombiwert.kEigenschaft IN (" . $cSQL1 . ")
                            AND teigenschaftkombiwert.kEigenschaftWert IN (" . $cSQL2 . ")
                            AND tartikelsichtbarkeit.kArtikel IS NULL
                            AND tartikel.kVaterArtikel = " . $kArtikel . "
                        GROUP BY tartikel.kArtikel
                        HAVING count(*) = " . count($kVariationKombi_arr), 1
                );
            }
            if (isset($oArtikelTMP->kArtikel) && $oArtikelTMP->kArtikel > 0) {
                return $oArtikelTMP->kArtikel;
            }
            if (!isset($_SESSION['variBoxAnzahl_arr'])) {
                //redirekt zum artikel, um variation/en zu waehlen / MBM beachten
                header('Location: ' . Shop::getURL() .
                    '/index.php?a=' . $kArtikel .
                    '&n=' . $_POST['anzahl'] .
                    '&r=' . R_VARWAEHLEN, true, 302);
                exit();
            }
        }

        return 0;
    }

    /**
     * Holt fuer einen kVaterArtikel alle Eigenschaften und Eigenschaftswert Assoc als Array
     * z.b. $properties[kEigenschaft] = EigenschaftWert
     *
     * @former: gibAlleKindEigenschaftenZuVater()
     * @param int $kArtikel
     * @param int $kKundengruppe
     * @return array
     */
    public static function getChildPropertiesForParent($kArtikel, $kKundengruppe)
    {
        $varCombinations = self::getPossibleVariationCombinations($kArtikel, $kKundengruppe);
        $properties      = [];
        if (is_array($varCombinations) && count($varCombinations) > 0) {
            foreach ($varCombinations as $oAlleVariationKombi) {
                if (!isset($properties[$oAlleVariationKombi->kEigenschaft]) ||
                    !is_array($properties[$oAlleVariationKombi->kEigenschaft])
                ) {
                    $properties[$oAlleVariationKombi->kEigenschaft] = [];
                }
                if (!isset($oAlleVariationKombi->kEigenschaftWert, $properties[$oAlleVariationKombi->kEigenschaft]) ||
                    !in_array($oAlleVariationKombi->kEigenschaftWert, $properties[$oAlleVariationKombi->kEigenschaft])
                ) {
                    $properties[$oAlleVariationKombi->kEigenschaft][] = $oAlleVariationKombi->kEigenschaftWert;
                }
            }
        }

        return $properties;
    }

    /**
     * @param int  $kVaterArtikel
     * @param int  $kKundengruppe
     * @param bool $bGroupBy
     * @return mixed
     */
    public static function getPossibleVariationCombinations($kVaterArtikel, $kKundengruppe = 0, $bGroupBy = false)
    {
        if (!$kKundengruppe) {
            $kKundengruppe = Kundengruppe::getDefaultGroupID();
        }
        $cGroupBy = $bGroupBy ? "GROUP BY teigenschaftkombiwert.kEigenschaftWert" : '';

        return Shop::DB()->query(
            "SELECT teigenschaftkombiwert.*
                FROM teigenschaftkombiwert
                JOIN tartikel
                    ON tartikel.kVaterArtikel = " . (int)$kVaterArtikel . "
                    AND tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                LEFT JOIN tartikelsichtbarkeit
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = " . (int)$kKundengruppe . "
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                {$cGroupBy}
                ORDER BY teigenschaftkombiwert.kEigenschaftWert", 2
        );
    }

    /**
     * @former gibGewaehlteEigenschaftenZuVariKombiArtikel()
     * @param int $kArtikel
     * @param int $nArtikelVariAufbau
     * @return array
     */
    public static function getSelectedPropertiesForVarCombiArticle($kArtikel, $nArtikelVariAufbau = 0)
    {
        $oProperties    = [];
        $propertyValues = [];
        $nVorhanden     = 1;
        $kArtikel       = (int)$kArtikel;
        if ($kArtikel > 0) {
            // Hole EigenschaftWerte zur gewaehlten VariationKombi
            $oVariationKombiKind_arr = Shop::DB()->query(
                "SELECT teigenschaftkombiwert.kEigenschaftWert, teigenschaftkombiwert.kEigenschaft, tartikel.kVaterArtikel
                    FROM teigenschaftkombiwert
                    JOIN tartikel
                        ON tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                        AND tartikel.kArtikel = " . (int)$kArtikel . "
                    LEFT JOIN tartikelsichtbarkeit
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    ORDER BY tartikel.kArtikel", 2
            );
            $kVaterArtikel = 0;
            if (is_array($oVariationKombiKind_arr) && count($oVariationKombiKind_arr) > 0) {
                $kVaterArtikel = (int)$oVariationKombiKind_arr[0]->kVaterArtikel;

                foreach ($oVariationKombiKind_arr as $oVariationKombiKind) {
                    if (!isset($propertyValues[$oVariationKombiKind->kEigenschaft]) ||
                        !is_array($propertyValues[$oVariationKombiKind->kEigenschaft])
                    ) {
                        $propertyValues[$oVariationKombiKind->kEigenschaft] = $oVariationKombiKind->kEigenschaftWert;
                    }
                }
            }
            $cSQL1 = '';
            $cSQL2 = '';
            $j     = 0;
            if (count($propertyValues) > 0) {
                foreach ($propertyValues as $i => $kEigenschaftWertProEigenschaft) {
                    if ($j > 0) {
                        $cSQL1 .= ',' . $i;
                        $cSQL2 .= ',' . $propertyValues[$i];
                    } else {
                        $cSQL1 .= $i;
                        $cSQL2 .= $propertyValues[$i];
                    }
                    $j++;
                }
            }
            $kSprache                     = isset($_SESSION['kSprache'])
                ? (int)$_SESSION['kSprache']
                : Shop::getLanguage();
            $oSQLEigenschaft              = new stdClass();
            $oSQLEigenschaft->cSELECT     = '';
            $oSQLEigenschaft->cJOIN       = '';
            $oSQLEigenschaftWert          = new stdClass();
            $oSQLEigenschaftWert->cSELECT = '';
            $oSQLEigenschaftWert->cJOIN   = '';
            if ($kSprache > 0 && !standardspracheAktiv()) {
                $oSQLEigenschaft->cSELECT = "teigenschaftsprache.cName AS cName_teigenschaftsprache, ";
                $oSQLEigenschaft->cJOIN   = "LEFT JOIN teigenschaftsprache 
                                            ON teigenschaftsprache.kEigenschaft = teigenschaft.kEigenschaft
                                            AND teigenschaftsprache.kSprache = " . $kSprache;

                $oSQLEigenschaftWert->cSELECT = "teigenschaftwertsprache.cName AS cName_teigenschaftwertsprache, ";
                $oSQLEigenschaftWert->cJOIN   = "LEFT JOIN teigenschaftwertsprache 
                                                ON teigenschaftwertsprache.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                                                AND teigenschaftwertsprache.kSprache = " . $kSprache;
            }

            $oEigenschaft_arr = Shop::DB()->query(
                "SELECT teigenschaftwert.kEigenschaftWert, teigenschaftwert.cName, " . $oSQLEigenschaftWert->cSELECT . "
                    teigenschaftwertsichtbarkeit.kKundengruppe, teigenschaftwert.kEigenschaft, teigenschaft.cTyp, " .
                    $oSQLEigenschaft->cSELECT . " teigenschaft.cName AS cNameEigenschaft, teigenschaft.kArtikel
                    FROM teigenschaftwert
                    LEFT JOIN teigenschaftwertsichtbarkeit
                        ON teigenschaftwertsichtbarkeit.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                        AND teigenschaftwertsichtbarkeit.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                    JOIN teigenschaft ON teigenschaft.kEigenschaft = teigenschaftwert.kEigenschaft
                    LEFT JOIN teigenschaftsichtbarkeit ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                        AND teigenschaftsichtbarkeit.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                    " . $oSQLEigenschaft->cJOIN . "
                    " . $oSQLEigenschaftWert->cJOIN . "
                    WHERE teigenschaftwertsichtbarkeit.kEigenschaftWert IS NULL
                        AND teigenschaftsichtbarkeit.kEigenschaft IS NULL
                        AND teigenschaftwert.kEigenschaft IN (" . $cSQL1 . ")
                        AND teigenschaftwert.kEigenschaftWert IN (" . $cSQL2 . ")", 2
            );

            $oEigenschaftTMP_arr = Shop::DB()->query(
                "SELECT teigenschaft.kEigenschaft,teigenschaft.cName,teigenschaft.cTyp
                    FROM teigenschaft
                    LEFT JOIN teigenschaftsichtbarkeit
                        ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                        AND teigenschaftsichtbarkeit.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                    WHERE (teigenschaft.kArtikel = " . $kVaterArtikel . "
                        OR teigenschaft.kArtikel = " . $kArtikel . ")
                        AND teigenschaftsichtbarkeit.kEigenschaft IS NULL
                        AND (teigenschaft.cTyp = 'FREIFELD'
                        OR teigenschaft.cTyp = 'PFLICHT-FREIFELD')", 2
            );

            if (is_array($oEigenschaft_arr) && count($oEigenschaft_arr) > 0) {
                if (is_array($oEigenschaftTMP_arr)) {
                    $oEigenschaft_arr = array_merge($oEigenschaft_arr, $oEigenschaftTMP_arr);
                }

                foreach ($oEigenschaft_arr as $oEigenschaft) {
                    if ($oEigenschaft->cTyp !== 'FREIFELD' && $oEigenschaft->cTyp !== 'PFLICHT-FREIFELD') {
                        // Ist kEigenschaft zu eigenschaftwert vorhanden
                        if (self::hasSelectedVariationValue($oEigenschaft->kEigenschaft)) {
                            $oEigenschaftWertVorhanden = Shop::DB()->query(
                                "SELECT teigenschaftwert.kEigenschaftWert
                                    FROM teigenschaftwert
                                    LEFT JOIN teigenschaftwertsichtbarkeit
                                        ON teigenschaftwertsichtbarkeit.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                                        AND teigenschaftwertsichtbarkeit.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                                    WHERE teigenschaftwert.kEigenschaftWert = " . (int)$oEigenschaft->kEigenschaftWert . "
                                        AND teigenschaftwertsichtbarkeit.kEigenschaftWert IS NULL
                                        AND teigenschaftwert.kEigenschaft = " . (int)$oEigenschaft->kEigenschaft, 1
                            );

                            if ($oEigenschaftWertVorhanden->kEigenschaftWert) {
                                unset($oEigenschaftwerte);
                                $oEigenschaftwerte                   = new stdClass();
                                $oEigenschaftwerte->kEigenschaftWert = $oEigenschaft->kEigenschaftWert;
                                $oEigenschaftwerte->kEigenschaft     = $oEigenschaft->kEigenschaft;
                                $oEigenschaftwerte->cTyp             = $oEigenschaft->cTyp;

                                if ($kSprache > 0 && !standardspracheAktiv()) {
                                    $oEigenschaftwerte->cEigenschaftName     = $oEigenschaft->cName_teigenschaftsprache;
                                    $oEigenschaftwerte->cEigenschaftWertName = $oEigenschaft->cName_teigenschaftwertsprache;
                                } else {
                                    $oEigenschaftwerte->cEigenschaftName     = $oEigenschaft->cNameEigenschaft;
                                    $oEigenschaftwerte->cEigenschaftWertName = $oEigenschaft->cName;
                                }
                                $oProperties[] = $oEigenschaftwerte;
                            } else {
                                $nVorhanden = 0;
                                break;
                            }
                        } else {
                            if (!isset($_SESSION['variBoxAnzahl_arr'])) {
                                //redirekt zum artikel, um variation/en zu waehlen / MBM beachten
                                header('Location: ' . Shop::getURL() .
                                    '/index.php?a=' . $kArtikel .
                                    '&n=' . (int)$_POST['anzahl'] .
                                    '&r=' . R_VARWAEHLEN, true, 302);
                                exit();
                            }
                        }
                    } else {
                        unset($oEigenschaftwerte);
                        if ($oEigenschaft->cTyp === 'PFLICHT-FREIFELD'
                                && self::hasSelectedVariationValue($oEigenschaft->kEigenschaft)
                                && strlen(self::getSelectedVariationValue($oEigenschaft->kEigenschaft)) === 0) {
                            header('Location: ' . Shop::getURL() .
                                '/index.php?a=' . $kArtikel .
                                '&n=' . (int)$_POST['anzahl'] .
                                '&r=' . R_VARWAEHLEN, true, 302);
                            exit();
                        } else {
                            $oEigenschaftwerte                = new stdClass();
                            $oEigenschaftwerte->cFreifeldWert = StringHandler::filterXSS(self::getSelectedVariationValue($oEigenschaft->kEigenschaft));
                            $oEigenschaftwerte->kEigenschaft  = $oEigenschaft->kEigenschaft;
                            $oEigenschaftwerte->cTyp          = $oEigenschaft->cTyp;
                            $oProperties[]                    = $oEigenschaftwerte;
                        }
                    }
                }
            }

            if (!$nVorhanden && !isset($_SESSION['variBoxAnzahl_arr'])) {
                //redirekt zum artikel, weil variation nicht vorhanden
                header('Location: ' . Shop::getURL() .
                    '/index.php?a=' . $kArtikel .
                    '&n=' . (int)$_POST['anzahl'] .
                    '&r=' . R_VARWAEHLEN, true, 301);
                exit();
            }
        }
        // Wie beim Artikel die Variationen aufbauen
        if ($nArtikelVariAufbau > 0) {
            $variations = [];
            if (is_array($oProperties) && count($oProperties) > 0) {
                foreach ($oProperties as $i => $oEigenschaftwerte) {
                    $oEigenschaftWert                   = new stdClass();
                    $oEigenschaftWert->kEigenschaftWert = $oEigenschaftwerte->kEigenschaftWert;
                    $oEigenschaftWert->kEigenschaft     = $oEigenschaftwerte->kEigenschaft;
                    $oEigenschaftWert->cName            = $oEigenschaftwerte->cEigenschaftWertName;

                    $variations[$i]               = new stdClass();
                    $variations[$i]->kEigenschaft = $oEigenschaftwerte->kEigenschaft;
                    $variations[$i]->kArtikel     = $kArtikel;
                    $variations[$i]->cWaehlbar    = 'Y';
                    $variations[$i]->cTyp         = $oEigenschaftwerte->cTyp;
                    $variations[$i]->cName        = $oEigenschaftwerte->cEigenschaftName;
                    $variations[$i]->Werte        = [];
                    $variations[$i]->Werte[]      = $oEigenschaftWert;
                }

                return $variations;
            }
        }

        return $oProperties;
    }

    /**
     * @former gibGewaehlteEigenschaftenZuArtikel()
     * @param int  $kArtikel
     * @param bool $bRedirect
     * @return array
     */
    public static function getSelectedPropertiesForArticle($kArtikel, $bRedirect = true)
    {
        $kKundengruppe = (int)$_SESSION['Kundengruppe']->kKundengruppe;
        $kArtikel      = (int)$kArtikel;
        // Pruefe welche kEigenschaft gesetzt ist
        $oEigenschaft_arr = Shop::DB()->query(
            "SELECT teigenschaft.kEigenschaft,teigenschaft.cName,teigenschaft.cTyp
                FROM teigenschaft
                LEFT JOIN teigenschaftsichtbarkeit 
                    ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                    AND teigenschaftsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                WHERE teigenschaft.kArtikel = " . $kArtikel . "
                    AND teigenschaftsichtbarkeit.kEigenschaft IS NULL", 2
        );
        // $oProperties anlegen
        $oProperties = [];
        $nVorhanden  = 1;
        if (is_array($oEigenschaft_arr) && count($oEigenschaft_arr) > 0) {
            foreach ($oEigenschaft_arr as $oEigenschaft) {
                if ($oEigenschaft->cTyp !== 'FREIFELD' && $oEigenschaft->cTyp !== 'PFLICHT-FREIFELD') {
                    // Ist kEigenschaft zu eigenschaftwert vorhanden
                    if (self::hasSelectedVariationValue($oEigenschaft->kEigenschaft)) {
                        $oEigenschaftWertVorhanden = Shop::DB()->query(
                            "SELECT teigenschaftwert.kEigenschaftWert, teigenschaftwert.cName, 
                                teigenschaftwertsichtbarkeit.kKundengruppe
                                FROM teigenschaftwert
                                LEFT JOIN teigenschaftwertsichtbarkeit
                                    ON teigenschaftwertsichtbarkeit.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                                    AND teigenschaftwertsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                                WHERE teigenschaftwert.kEigenschaftWert = " . (int)self::getSelectedVariationValue($oEigenschaft->kEigenschaft) . "
                                    AND teigenschaftwertsichtbarkeit.kEigenschaftWert IS NULL
                                    AND teigenschaftwert.kEigenschaft = " . (int)$oEigenschaft->kEigenschaft, 1
                        );

                        if ($oEigenschaftWertVorhanden->kEigenschaftWert) {
                            $oEigenschaftwerte                       = new stdClass();
                            $oEigenschaftwerte->kEigenschaftWert     = (int)self::getSelectedVariationValue($oEigenschaft->kEigenschaft);
                            $oEigenschaftwerte->kEigenschaft         = $oEigenschaft->kEigenschaft;
                            $oEigenschaftwerte->cEigenschaftName     = $oEigenschaft->cName;
                            $oEigenschaftwerte->cEigenschaftWertName = $oEigenschaftWertVorhanden->cName;
                            $oEigenschaftwerte->cTyp                 = $oEigenschaft->cTyp;
                            $oProperties[]                           = $oEigenschaftwerte;
                        } else {
                            $nVorhanden = 0;
                            break;
                        }
                    } else {
                        if (!isset($_SESSION['variBoxAnzahl_arr']) && $bRedirect) {
                            //redirekt zum artikel, um variation/en zu waehlen  MBM beachten
                            header('Location: ' . Shop::getURL() .
                                '/index.php?a=' . $kArtikel .
                                '&n=' . (int)$_POST['anzahl'] .
                                '&r=' . R_VARWAEHLEN, true, 302);
                            exit();
                        }
                    }
                } else {
                    if ($oEigenschaft->cTyp === 'PFLICHT-FREIFELD' && $bRedirect
                            && self::hasSelectedVariationValue($oEigenschaft->kEigenschaft)
                            && strlen(self::getSelectedVariationValue($oEigenschaft->kEigenschaft)) === 0) {
                        header('Location: ' . Shop::getURL() .
                            '/index.php?a=' . $kArtikel .
                            '&n=' . (int)$_POST['anzahl'] .
                            '&r=' . R_VARWAEHLEN, true, 302);
                        exit();
                    } else {
                        $oEigenschaftwerte                = new stdClass();
                        $oEigenschaftwerte->cFreifeldWert = Shop::DB()->escape(StringHandler::filterXSS(self::getSelectedVariationValue($oEigenschaft->kEigenschaft)));
                        $oEigenschaftwerte->kEigenschaft  = $oEigenschaft->kEigenschaft;
                        $oEigenschaftwerte->cTyp          = $oEigenschaft->cTyp;
                        $oProperties[]                    = $oEigenschaftwerte;
                    }
                }
            }
        }

        if (!$nVorhanden && $bRedirect && !isset($_SESSION['variBoxAnzahl_arr'])) {
            //redirect zum artikel, weil variation nicht vorhanden
            header('Location: ' . Shop::getURL() .
                '/index.php?a=' . $kArtikel .
                '&n=' . (int)$_POST['anzahl'] .
                '&r=' . R_VARWAEHLEN, true, 302);
            exit();
        }

        return $oProperties;
    }

    /**
     * Holt zu einem $kVaterArtikel alle kArtikel zu den Variationskinder
     *
     * @former holeKinderzuVater()
     * @param int $kVaterArtikel
     * @return array
     */
    public static function getChildren($kVaterArtikel)
    {
        $oVariationsKind_arr = [];
        if ($kVaterArtikel > 0) {
            $oVariationsKind_arr = Shop::DB()->selectAll(
                'tartikel',
                'kVaterArtikel',
                (int)$kVaterArtikel,
                'kArtikel, kEigenschaftKombi'
            );
        }

        return $oVariationsKind_arr;
    }

    /**
     * @former pruefeIstVaterArtikel()
     * @param int $kArtikel
     * @return bool
     */
    public static function isParent($kArtikel)
    {
        $oArtikelTMP = Shop::DB()->select(
            'tartikel',
            'kArtikel',
            (int)$kArtikel,
            null,
            null,
            null,
            null,
            false,
            'nIstVater'
        );

        return isset($oArtikelTMP->nIstVater) && $oArtikelTMP->nIstVater > 0;
    }

    /**
     * @param int  $kArtikel
     * @param bool $bInfo
     * @return bool|stdClass
     */
    public static function isStuecklisteKomponente($kArtikel, $bInfo = false)
    {
        $kArtikel = (int)$kArtikel;
        if ($kArtikel > 0) {
            $oObj = Shop::DB()->select('tstueckliste', 'kArtikel', $kArtikel);
            if (isset($oObj->kStueckliste) && $oObj->kStueckliste > 0) {
                return $bInfo ? $oObj : true;
            }
        }

        return false;
    }

    /**
     * Fallback für alte Formular-Struktur
     *
     * alt: eigenschaftwert_{kEigenschaft}
     * neu: eigenschaftwert[{kEigenschaft}]
     *
     * @param int $groupId
     * @return string
     */
    protected static function getSelectedVariationValue($groupId)
    {
        if (isset($_POST['eigenschaftwert_' . $groupId])) {
            return $_POST['eigenschaftwert_' . $groupId];
        }
        if (isset($_POST['eigenschaftwert'][$groupId])) {
            return $_POST['eigenschaftwert'][$groupId];
        }

        return false;
    }

    /**
     * @param int $groupId
     * @return bool
     */
    protected static function hasSelectedVariationValue($groupId)
    {
        return self::getSelectedVariationValue($groupId) !== false;
    }

    /**
     * @param Artikel $artikel
     * @param object[] $variationPicturesArr
     * @return void
     */
    public static function addVariationPictures(Artikel $artikel, $variationPicturesArr)
    {
        if (is_array($variationPicturesArr) && count($variationPicturesArr) > 0) {
            $artikel->Bilder = array_filter($artikel->Bilder, function ($item) {
                return !(isset($item->isVariation) && $item->isVariation);
            });
            if (count($variationPicturesArr) === 1) {
                array_unshift($artikel->Bilder, $variationPicturesArr[0]);
            } else {
                $artikel->Bilder = array_merge($artikel->Bilder, $variationPicturesArr);
            }

            $nNr = 1;
            foreach (array_keys($artikel->Bilder) as $key) {
                $artikel->Bilder[$key]->nNr = $nNr++;
            }

            $artikel->cVorschaubild = $artikel->Bilder[0]->cPfadKlein;
        }
    }

    /**
     * @param Artikel $artikel
     * @param float $fPreis
     * @param int $nAnzahl
     * @return stdClass
     */
    public static function getBasePriceUnit(Artikel $artikel, $fPreis, $nAnzahl)
    {
        $unitMappings = [
            'mg'  => 'kg',
            'g'   => 'kg',
            'mL'  => 'L',
            'cm3' => 'L',
            'cL'  => 'L',
            'dL'  => 'L',
        ];

        $result = (object)[
            'fGrundpreisMenge'   => $artikel->fGrundpreisMenge,
            'fMassMenge'         => $artikel->fMassMenge * $nAnzahl,
            'fBasePreis'         => $fPreis / $artikel->fVPEWert,
            'fVPEWert'           => (float)$artikel->fVPEWert,
            'cVPEEinheit'        => $artikel->cVPEEinheit,
        ];

        $gpUnit   = UnitsOfMeasure::getUnit($artikel->kGrundpreisEinheit);
        $massUnit = UnitsOfMeasure::getUnit($artikel->kMassEinheit);

        if (isset($gpUnit, $massUnit, $unitMappings[$gpUnit->cCode], $unitMappings[$massUnit->cCode])) {
            $fFactor    = UnitsOfMeasure::getConversionFaktor($unitMappings[$massUnit->cCode], $massUnit->cCode);
            $threshold  = 250 * $fFactor / 1000;
            $nAmount    = 1;
            $mappedCode = $unitMappings[$massUnit->cCode];

            if ($threshold > 0 && $result->fMassMenge > $threshold) {
                $result->fGrundpreisMenge = $nAmount;
                $result->fMassMenge       /= $fFactor;
                $result->fVPEWert         = $result->fMassMenge / $nAnzahl / $result->fGrundpreisMenge;
                $result->fBasePreis       = $fPreis / $result->fVPEWert;
                $result->cVPEEinheit      = $result->fGrundpreisMenge . ' ' . UnitsOfMeasure::getPrintAbbreviation($mappedCode);
            }
        }

        return $result;
    }
}
