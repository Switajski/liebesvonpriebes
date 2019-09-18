<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param string $cSuche
 * @param bool   $bSpeichern
 * @return mixed
 */
function bearbeiteEinstellungsSuche($cSuche, $bSpeichern = false)
{
    $cSuche                 = StringHandler::filterXSS($cSuche);
    $oSQL                   = new stdClass();
    $oSQL->cSearch          = '';
    $oSQL->cWHERE           = '';
    $oSQL->nSuchModus       = 0;
    $oSQL->cSuche           = $cSuche;
    $oSQL->oEinstellung_arr = [];
    if (strlen($cSuche) > 0) {
        //Einstellungen die zu den Exportformaten gehören nicht holen
        $oSQL->cWHERE = "AND kEinstellungenSektion != 101 ";
        // Einstellungen Kommagetrennt?
        $kEinstellungenConf_arr = explode(',', $cSuche);
        $bKommagetrennt         = false;
        if (is_array($kEinstellungenConf_arr) && count($kEinstellungenConf_arr) > 1) {
            $bKommagetrennt = true;
            foreach ($kEinstellungenConf_arr as $i => $kEinstellungenConf) {
                if ((int)$kEinstellungenConf === 0) {
                    $bKommagetrennt = false;
                }
            }
        }
        if ($bKommagetrennt) {
            $oSQL->nSuchModus = 1;
            $oSQL->cSearch    = "Suche nach ID: ";
            $oSQL->cWHERE .= " AND kEinstellungenConf IN (";
            foreach ($kEinstellungenConf_arr as $i => $kEinstellungenConf) {
                if ($kEinstellungenConf > 0) {
                    if ($i > 0) {
                        $oSQL->cSearch .= ", " . (int)$kEinstellungenConf;
                        $oSQL->cWHERE .= ", " . (int)$kEinstellungenConf;
                    } else {
                        $oSQL->cSearch .= (int)$kEinstellungenConf;
                        $oSQL->cWHERE .= (int)$kEinstellungenConf;
                    }
                }
            }
            $oSQL->cWHERE .= ")";
        } else { // Range von Einstellungen?
            $kEinstellungenConf_arr = explode('-', $cSuche);
            $bRange                 = false;
            if (is_array($kEinstellungenConf_arr) && count($kEinstellungenConf_arr) === 2) {
                $kEinstellungenConf_arr[0] = (int)$kEinstellungenConf_arr[0];
                $kEinstellungenConf_arr[1] = (int)$kEinstellungenConf_arr[1];
                if ($kEinstellungenConf_arr[0] > 0 && $kEinstellungenConf_arr[1] > 0) {
                    $bRange = true;
                }
            }
            if ($bRange) {
                // Suche war eine Range
                $oSQL->nSuchModus = 2;
                $oSQL->cSearch    = 'Suche nach ID Range: ' . 
                    (int)$kEinstellungenConf_arr[0] . ' - ' . 
                    (int)$kEinstellungenConf_arr[1];
                $oSQL->cWHERE .= " AND ((kEinstellungenConf BETWEEN " . 
                    (int)$kEinstellungenConf_arr[0] . " AND " . 
                    (int)$kEinstellungenConf_arr[1] . ") AND cConf = 'Y')";
            } else { // Suche in cName oder kEinstellungenConf suchen
                if ((int)$cSuche > 0) {
                    $oSQL->nSuchModus = 3;
                    $oSQL->cSearch    = "Suche nach ID: " . $cSuche;
                    $oSQL->cWHERE .= " AND kEinstellungenConf = '" . (int)$cSuche . "'";
                } else {
                    $cSuche    = strtolower($cSuche);
                    $cSucheEnt = StringHandler::htmlentities($cSuche); // HTML Entities

                    $oSQL->nSuchModus = 4;
                    $oSQL->cSearch    = 'Suche nach Name: ' . $cSuche;

                    if ($cSuche === $cSucheEnt) {
                        $oSQL->cWHERE .= " AND (cName LIKE '%" . 
                            Shop::DB()->escape($cSuche) .
                            "%' AND cConf = 'Y')";
                    } else {
                        $oSQL->cWHERE .= " AND (((cName LIKE '%" . 
                            Shop::DB()->escape($cSuche) . 
                            "%' OR cName LIKE '%" . 
                            Shop::DB()->escape($cSucheEnt) . "%')) AND cConf = 'Y')";
                    }
                }
            }
        }
    }

    return holeEinstellungen($oSQL, $bSpeichern);
}

/**
 * @param object $oSQL
 * @param bool   $bSpeichern
 * @return mixed
 */
function holeEinstellungen($oSQL, $bSpeichern)
{
    if (strlen($oSQL->cWHERE) <= 0) {
        return $oSQL;
    }
    $oSQL->oEinstellung_arr = Shop::DB()->query(
        "SELECT *
            FROM teinstellungenconf
            WHERE (cModulId IS NULL OR cModulId = '') " . $oSQL->cWHERE . "
            ORDER BY kEinstellungenSektion, nSort", 2
    );

    if (count($oSQL->oEinstellung_arr) > 0) {
        foreach ($oSQL->oEinstellung_arr as $j => $oEinstellung) {
            if ($oSQL->nSuchModus == 3 && $oEinstellung->cConf === 'Y') {
                $oSQL->oEinstellung_arr = [];
                $configHead             = holeEinstellungHeadline(
                    $oEinstellung->nSort,
                    $oEinstellung->kEinstellungenSektion
                );
                if (isset($configHead->kEinstellungenConf) && 
                    $configHead->kEinstellungenConf > 0) {
                    $oSQL->oEinstellung_arr[] = $configHead;
                    $oSQL                     = holeEinstellungAbteil(
                        $oSQL, 
                        $configHead->nSort, 
                        $configHead->kEinstellungenSektion
                    );
                }
            } elseif ($oEinstellung->cConf === 'N') {
                $oSQL = holeEinstellungAbteil(
                    $oSQL, 
                    $oEinstellung->nSort, 
                    $oEinstellung->kEinstellungenSektio
                );
            }
        }
    }
    // Aufräumen
    if (count($oSQL->oEinstellung_arr) > 0) {
        $kEinstellungenConf_arr = [];
        foreach ($oSQL->oEinstellung_arr as $i => $oEinstellung) {
            $oEinstellung->kEinstellungenConf = (int)$oEinstellung->kEinstellungenConf;
            if (isset($oEinstellung->kEinstellungenConf) &&
                $oEinstellung->kEinstellungenConf > 0 &&
                !in_array($oEinstellung->kEinstellungenConf, $kEinstellungenConf_arr, true)) {
                $kEinstellungenConf_arr[$i] = $oEinstellung->kEinstellungenConf;
            } else {
                unset($oSQL->oEinstellung_arr[$i]);
            }

            if ($bSpeichern && $oEinstellung->cConf === 'N') {
                unset($oSQL->oEinstellung_arr[$i]);
            }
        }
        $oSQL->oEinstellung_arr = sortiereEinstellungen($oSQL->oEinstellung_arr);
    }

    return $oSQL;
}

/**
 * @param object $oSQL
 * @param int    $nSort
 * @param int    $kEinstellungenSektion
 * @return mixed
 */
function holeEinstellungAbteil($oSQL, $nSort, $kEinstellungenSektion)
{
    if ((int)$nSort > 0 && (int)$kEinstellungenSektion > 0) {
        $oEinstellungTMP_arr = Shop::DB()->query(
            "SELECT *
                FROM teinstellungenconf
                WHERE nSort > " . (int)$nSort . "
                    AND kEinstellungenSektion = " . (int)$kEinstellungenSektion . "
                ORDER BY nSort", 2
        );

        if (is_array($oEinstellungTMP_arr) && count($oEinstellungTMP_arr) > 0) {
            foreach ($oEinstellungTMP_arr as $oEinstellungTMP) {
                if ($oEinstellungTMP->cConf !== 'N') {
                    $oSQL->oEinstellung_arr[] = $oEinstellungTMP;
                } else {
                    break;
                }
            }
        }
    }

    return $oSQL;
}

/**
 * @param int $nSort
 * @param int $sectionID
 * @return stdClass
 */
function holeEinstellungHeadline($nSort, $sectionID)
{
    $configHead  = new stdClass();
    $sectionID   = (int)$sectionID;
    $nSort       = (int)$nSort;
    if ($nSort > 0 && $sectionID > 0) {
        $oEinstellungTMP_arr = Shop::DB()->query(
            "SELECT *
                FROM teinstellungenconf
                WHERE nSort < " . $nSort . "
                    AND kEinstellungenSektion = " . $sectionID . "
                ORDER BY nSort DESC", 2
        );

        if (is_array($oEinstellungTMP_arr) && count($oEinstellungTMP_arr) > 0) {
            foreach ($oEinstellungTMP_arr as $oEinstellungTMP) {
                if ($oEinstellungTMP->cConf === 'N') {
                    $configHead                = $oEinstellungTMP;
                    $configHead->cSektionsPfad = gibEinstellungsSektionsPfad($sectionID);
                    break;
                }
            }
        }
    }

    return $configHead;
}

/**
 * @param int $kEinstellungenSektion
 * @return string
 */
function gibEinstellungsSektionsPfad($kEinstellungenSektion)
{
    $kEinstellungenSektion = (int)$kEinstellungenSektion;
    if ($kEinstellungenSektion >= 100) {
        // Einstellungssektion ist in den Defines
        switch ($kEinstellungenSektion) {
            case CONF_ZAHLUNGSARTEN:
                return 'Storefront-&gt;Zahlungsarten-&gt;&Uuml;bersicht';
            case CONF_EXPORTFORMATE:
                return 'System-&gt;Export-&gt;Exportformate';
            case CONF_KONTAKTFORMULAR:
                return 'Storefront-&gt;Formulare-&gt;Kontaktformular';
            case CONF_SHOPINFO:
                return 'System-&gt;Export-&gt;Exportformate';
            case CONF_RSS:
                return 'System-&gt;Export-&gt;RSS Feed';
            case CONF_PREISVERLAUF:
                return 'Storefront-&gt;Artikel-&gt;Preisverlauf';
            case CONF_VERGLEICHSLISTE:
                return 'Storefront-&gt;Artikel-&gt;Vergleichsliste';
            case CONF_BEWERTUNG:
                return 'Storefront-&gt;Artikel-&gt;Bewertungen';
            case CONF_NEWSLETTER:
                return 'System-&gt;E-Mails-&gt;Newsletter';
            case CONF_KUNDENFELD:
                return 'Storefront-&gt;Formulare-&gt;Eigene Kundenfelder';
            case CONF_NAVIGATIONSFILTER:
                return 'Storefront-&gt;Suche-&gt;Filter';
            case CONF_EMAILBLACKLIST:
                return 'System-&gt;E-Mails-&gt;Blacklist';
            case CONF_METAANGABEN:
                return 'System-&gt;E-Mails-&gt;Globale Einstellungen-&gt;Globale Meta-Angaben';
            case CONF_NEWS:
                return 'Inhalte-&gt;News';
            case CONF_SITEMAP:
                return 'System-&gt;Export-&gt;Sitemap';
            case CONF_UMFRAGE:
                return 'Inhalte-&gt;Umfragen';
            case CONF_KUNDENWERBENKUNDEN:
                return 'System-&gt;Benutzer- &amp; Kundenverwaltung-&gt;Kunden werben Kunden';
            case CONF_TRUSTEDSHOPS:
                return 'Storefront-&gt;Kaufabwicklung-&gt;Trusted Shops';
            case CONF_PREISANZEIGE:
                return 'Storefront-&gt;Artikel-&gt;Preisanzeige';
            case CONF_SUCHSPECIAL:
                return 'Storefront-&gt;Artikel-&gt;Besondere Produkte';
            default:
                return '';
        }
    } else {
        // Einstellungssektion in der Datenbank nachschauen
        $section = Shop::DB()->select(
            'teinstellungensektion',
            'kEinstellungenSektion',
            $kEinstellungenSektion
        );
        if (isset($section->kEinstellungenSektion) && $section->kEinstellungenSektion > 0) {
            return 'Einstellungen-&gt;' . $section->cName;
        }
    }

    return '';
}

/**
 * @param array $oEinstellung_arr
 * @return array
 */
function sortiereEinstellungen($oEinstellung_arr)
{
    if (is_array($oEinstellung_arr) && count($oEinstellung_arr) > 0) {
        $oEinstellungTMP_arr     = [];
        $oEinstellungSektion_arr = [];
        foreach ($oEinstellung_arr as $i => $oEinstellung) {
            if (isset($oEinstellung->kEinstellungenSektion) && $oEinstellung->cConf !== 'N') {
                if (!isset($oEinstellungSektion_arr[$oEinstellung->kEinstellungenSektion])) {
                    $headline = holeEinstellungHeadline($oEinstellung->nSort, $oEinstellung->kEinstellungenSektion);
                    if (isset($headline->kEinstellungenSektion)) {
                        $oEinstellungSektion_arr[$oEinstellung->kEinstellungenSektion] = true;
                        $oEinstellungTMP_arr[]                                         = $headline;
                    }
                }
                $oEinstellungTMP_arr[] = $oEinstellung;
            }
        }
        foreach ($oEinstellungTMP_arr as $key => $value) {
            $kEinstellungenSektion[$key] = $value->kEinstellungenSektion;
            $nSort[$key]                 = $value->nSort;
        }
        array_multisort($kEinstellungenSektion, SORT_ASC, $nSort, SORT_ASC, $oEinstellungTMP_arr);

        return $oEinstellungTMP_arr;
    }

    return [];
}
