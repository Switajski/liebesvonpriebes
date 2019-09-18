<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @return array
 */
function holeAlleKampagnenDefinitionen()
{
    $oKampagneDef_arr = Shop::DB()->query(
        "SELECT *
            FROM tkampagnedef
            ORDER BY kKampagneDef", 2
    );

    if (is_array($oKampagneDef_arr) && count($oKampagneDef_arr) > 0) {
        $oKampagneDef_arr = baueAssocArray($oKampagneDef_arr, 'kKampagneDef');
    }

    return $oKampagneDef_arr;
}

/**
 * @param int $kKampagne
 * @return mixed
 */
function holeKampagne($kKampagne)
{
    $kKampagne = (int)$kKampagne;
    if ($kKampagne > 0) {
        return Shop::DB()->query(
            "SELECT *, DATE_FORMAT(dErstellt, '%d.%m.%Y %H:%i:%s') AS dErstellt_DE
                FROM tkampagne
                WHERE kKampagne = " . $kKampagne, 1
        );
    }

    return new stdClass();
}

/**
 * @param int $kKampagneDef
 * @return mixed
 */
function holeKampagneDef($kKampagneDef)
{
    $kKampagneDef = (int)$kKampagneDef;
    if ($kKampagneDef > 0) {
        return Shop::DB()->select('tkampagnedef', 'kKampagneDef', $kKampagneDef);
    }

    return new stdClass();
}

/**
 * @param array $oKampagne_arr
 * @param array $oKampagneDef_arr
 * @return array
 */
function holeKampagneGesamtStats($oKampagne_arr, $oKampagneDef_arr)
{
    $oKampagneStat_arr = [];
    $cSQL              = '';
    $cDatum_arr        = gibDatumTeile($_SESSION['Kampagne']->cStamp);
    switch ((int)$_SESSION['Kampagne']->nAnsicht) {
        case 1:    // Monat
            $cSQL = "WHERE '" . $cDatum_arr['cJahr'] . "-" . 
                $cDatum_arr['cMonat'] . "' = DATE_FORMAT(dErstellt, '%Y-%m')";
            break;
        case 2:    // Woche
            $cDatum_arr = ermittleDatumWoche($cDatum_arr['cJahr'] . "-" . 
                $cDatum_arr['cMonat'] . "-" . $cDatum_arr['cTag']);
            $cSQL       = "WHERE dErstellt BETWEEN FROM_UNIXTIME(" . 
                $cDatum_arr[0] . ", '%Y-%m-%d %H:%i:%s') AND FROM_UNIXTIME(" . 
                $cDatum_arr[1] . ", '%Y-%m-%d %H:%i:%s')";
            break;
        case 3:    // Tag
            $cSQL = "WHERE '" . $cDatum_arr['cJahr'] . '-' . 
                $cDatum_arr['cMonat'] . '-' . 
                $cDatum_arr['cTag'] . "' = DATE_FORMAT(dErstellt, '%Y-%m-%d')";
            break;
    }

    if (is_array($oKampagne_arr) && count($oKampagne_arr) > 0 && 
        is_array($oKampagneDef_arr) && count($oKampagneDef_arr)) {
        foreach ($oKampagne_arr as $oKampagne) {
            foreach ($oKampagneDef_arr as $oKampagneDef) {
                $oKampagneStat_arr[$oKampagne->kKampagne][$oKampagneDef->kKampagneDef] = 0;
                $oKampagneStat_arr['Gesamt'][$oKampagneDef->kKampagneDef]              = 0;
            }
        }
    }

    $oStats_arr = Shop::DB()->query(
        "SELECT kKampagne, kKampagneDef, SUM(fWert) AS fAnzahl
            FROM tkampagnevorgang
            " . $cSQL . "
            GROUP BY kKampagne, kKampagneDef", 2
    );

    if (is_array($oStats_arr) && count($oStats_arr) > 0) {
        foreach ($oStats_arr as $oStats) {
            $oKampagneStat_arr[$oStats->kKampagne][$oStats->kKampagneDef] = $oStats->fAnzahl;
        }
    }
    // Sortierung
    if (isset($_SESSION['Kampagne']->nSort) && $_SESSION['Kampagne']->nSort > 0) {
        $oSort_arr = [];
        if ((int)$_SESSION['Kampagne']->nSort > 0 && count($oKampagneStat_arr) > 0) {
            foreach ($oKampagneStat_arr as $i => $oKampagneStatDef_arr) {
                $oSort_arr[$i] = $oKampagneStatDef_arr[$_SESSION['Kampagne']->nSort];
            }
        }
        if ($_SESSION['Kampagne']->cSort === 'ASC') {
            uasort($oSort_arr, 'kampagneSortASC');
        } else {
            uasort($oSort_arr, 'kampagneSortDESC');
        }
        $oKampagneStatTMP_arr = [];
        foreach ($oSort_arr as $i => $oSort_arrTmp) {
            $oKampagneStatTMP_arr[$i] = $oKampagneStat_arr[$i];
        }
        $oKampagneStat_arr = $oKampagneStatTMP_arr;
    }
    // Gesamtstats
    if (is_array($oStats_arr) && count($oStats_arr) > 0) {
        foreach ($oStats_arr as $oStats) {
            $oKampagneStat_arr['Gesamt'][$oStats->kKampagneDef] += $oStats->fAnzahl;
        }
    }

    return $oKampagneStat_arr;
}

/**
 * @param int $a
 * @param int $b
 * @return int
 */
function kampagneSortDESC($a, $b)
{
    if ($a == $b) {
        return 0;
    }

    return ($a > $b) ? -1 : 1;
}

/**
 * @param int $a
 * @param int $b
 * @return int
 */
function kampagneSortASC($a, $b)
{
    if ($a == $b) {
        return 0;
    }

    return ($a < $b) ? -1 : 1;
}

/**
 * @param int   $kKampagne
 * @param array $oKampagneDef_arr
 * @return array
 */
function holeKampagneDetailStats($kKampagne, $oKampagneDef_arr)
{
    // Zeitraum
    $cSQLWHERE           = '';
    $nAnzahlTageProMonat = date('t', mktime(
        0,
        0,
        0,
        $_SESSION['Kampagne']->cFromDate_arr['nMonat'],
        1,
        $_SESSION['Kampagne']->cFromDate_arr['nJahr'])
    );
    // Int String Work Around
    $cMonat = $_SESSION['Kampagne']->cFromDate_arr['nMonat'];
    if ($cMonat < 10) {
        $cMonat = '0' . $cMonat;
    }
    $cTag = $_SESSION['Kampagne']->cFromDate_arr['nTag'];
    if ($cTag < 10) {
        $cTag = '0' . $cTag;
    }

    switch ((int)$_SESSION['Kampagne']->nDetailAnsicht) {
        case 1:    // Jahr
            $cSQLWHERE = " WHERE dErstellt BETWEEN '" . $_SESSION['Kampagne']->cFromDate_arr['nJahr'] . "-" .
                $_SESSION['Kampagne']->cFromDate_arr['nMonat'] . "-01' AND '" .
                $_SESSION['Kampagne']->cToDate_arr['nJahr'] . "-" .
                $_SESSION['Kampagne']->cToDate_arr['nMonat'] . "-" . $nAnzahlTageProMonat . "'";
            if ($_SESSION['Kampagne']->cFromDate_arr['nJahr'] == $_SESSION['Kampagne']->cToDate_arr['nJahr']) {
                $cSQLWHERE = " WHERE DATE_FORMAT(dErstellt, '%Y') = '" .
                    $_SESSION['Kampagne']->cFromDate_arr['nJahr'] . "'";
            }
            break;
        case 2:    // Monat
            $cSQLWHERE = " WHERE dErstellt BETWEEN '" . $_SESSION['Kampagne']->cFromDate_arr['nJahr'] . "-" .
                $_SESSION['Kampagne']->cFromDate_arr['nMonat'] .
                "-01' AND '" . $_SESSION['Kampagne']->cToDate_arr['nJahr'] . "-" .
                $_SESSION['Kampagne']->cToDate_arr['nMonat'] . "-" . $nAnzahlTageProMonat . "'";
            if ($_SESSION['Kampagne']->cFromDate_arr['nJahr'] == $_SESSION['Kampagne']->cToDate_arr['nJahr'] &&
                $_SESSION['Kampagne']->cFromDate_arr['nMonat'] == $_SESSION['Kampagne']->cToDate_arr['nMonat']
            ) {
                $cSQLWHERE = " WHERE DATE_FORMAT(dErstellt, '%Y-%m') = '" .
                    $_SESSION['Kampagne']->cFromDate_arr['nJahr'] . "-" . $cMonat . "'";
            }
            break;
        case 3:    // Woche
            $cDatumWocheAnfang_arr = ermittleDatumWoche($_SESSION['Kampagne']->cFromDate);
            $cDatumWocheEnde_arr   = ermittleDatumWoche($_SESSION['Kampagne']->cToDate);
            $cSQLWHERE             = " WHERE dErstellt BETWEEN '" .
                date('Y-m-d H:i:s', $cDatumWocheAnfang_arr[0]) . "' AND '" .
                date('Y-m-d H:i:s', $cDatumWocheEnde_arr[1]) . "'";
            break;
        case 4:    // Tag
            $cSQLWHERE = " WHERE dErstellt BETWEEN '" . $_SESSION['Kampagne']->cFromDate .
                "' AND '" . $_SESSION['Kampagne']->cToDate . "'";
            if ($_SESSION['Kampagne']->cFromDate == $_SESSION['Kampagne']->cToDate) {
                $cSQLWHERE = " WHERE DATE_FORMAT(dErstellt, '%Y-%m-%d') = '" .
                    $_SESSION['Kampagne']->cFromDate_arr['nJahr'] . "-" . $cMonat . "-" . $cTag . "'";
            }
            break;
    }

    switch ((int)$_SESSION['Kampagne']->nDetailAnsicht) {
        case 1:    // Jahr
            $cSQLSELECT  = "DATE_FORMAT(dErstellt, '%Y') AS cDatum";
            $cSQLGROUPBY = "GROUP BY YEAR(dErstellt)";
            break;
        case 2:    // Monat
            $cSQLSELECT  = "DATE_FORMAT(dErstellt, '%Y-%m') AS cDatum";
            $cSQLGROUPBY = "GROUP BY MONTH(dErstellt), YEAR(dErstellt)";
            break;
        case 3:    // Woche
            $cSQLSELECT  = "WEEK(dErstellt, 1) AS cDatum";
            $cSQLGROUPBY = "GROUP BY WEEK(dErstellt, 1), YEAR(dErstellt)";
            break;
        case 4:    // Tag
            $cSQLSELECT  = "DATE_FORMAT(dErstellt, '%Y-%m-%d') AS cDatum";
            $cSQLGROUPBY = "GROUP BY DAY(dErstellt), YEAR(dErstellt), MONTH(dErstellt)";
            break;
        default:
            return [];
    }
    // Zeitraum
    $cZeitraum_arr = gibDetailDatumZeitraum();

    $oStats_arr = Shop::DB()->query(
        "SELECT kKampagne, kKampagneDef, SUM(fWert) AS fAnzahl, " . $cSQLSELECT . "
            FROM tkampagnevorgang
            " . $cSQLWHERE . "
                AND kKampagne = " . $kKampagne . "
            " . $cSQLGROUPBY . ", kKampagneDef", 2
    );
    // Vorbelegen
    $oStatsAssoc_arr = [];
    if (is_array($cZeitraum_arr['cDatum']) && count($cZeitraum_arr['cDatum']) > 0 &&
        is_array($oKampagneDef_arr) && count($oKampagneDef_arr) > 0) {
        foreach ($cZeitraum_arr['cDatum'] as $i => $cZeitraum) {
            if (!isset($oStatsAssoc_arr[$cZeitraum]['cDatum'])) {
                $oStatsAssoc_arr[$cZeitraum]['cDatum'] = $cZeitraum_arr['cDatumFull'][$i];
            }

            foreach ($oKampagneDef_arr as $oKampagneDef) {
                $oStatsAssoc_arr[$cZeitraum][$oKampagneDef->kKampagneDef] = 0;
            }
        }
    }
    // Finde den maximalen Wert heraus, um die Höhe des Graphen zu ermitteln
    $nGraphMaxAssoc_arr = []; // Assoc Array key = kKampagneDef
    if (is_array($oStats_arr) && count($oStats_arr) > 0 &&
        is_array($oKampagneDef_arr) && count($oKampagneDef_arr) > 0) {
        foreach ($oStats_arr as $oStats) {
            foreach ($oKampagneDef_arr as $oKampagneDef) {
                if (isset($oStatsAssoc_arr[$oStats->cDatum][$oKampagneDef->kKampagneDef])) {
                    $oStatsAssoc_arr[$oStats->cDatum][$oStats->kKampagneDef] = $oStats->fAnzahl;

                    if (!isset($nGraphMaxAssoc_arr[$oStats->kKampagneDef])) {
                        $nGraphMaxAssoc_arr[$oStats->kKampagneDef] = $oStats->fAnzahl;
                    } elseif ($nGraphMaxAssoc_arr[$oStats->kKampagneDef] < $oStats->fAnzahl) {
                        $nGraphMaxAssoc_arr[$oStats->kKampagneDef] = $oStats->fAnzahl;
                    }
                }
            }
        }
    }
    if (!isset($_SESSION['Kampagne']->oKampagneDetailGraph)) {
        $_SESSION['Kampagne']->oKampagneDetailGraph = new stdClass();
    }
    $_SESSION['Kampagne']->oKampagneDetailGraph->oKampagneDetailGraph_arr = $oStatsAssoc_arr;
    $_SESSION['Kampagne']->oKampagneDetailGraph->nGraphMaxAssoc_arr       = $nGraphMaxAssoc_arr;

    // Maximal 31 Einträge pro Graph
    if (count($_SESSION['Kampagne']->oKampagneDetailGraph->oKampagneDetailGraph_arr) > 31) {
        $nVonKey = count($_SESSION['Kampagne']->oKampagneDetailGraph->oKampagneDetailGraph_arr) - 31;

        $oTMP_arr = [];
        foreach ($_SESSION['Kampagne']->oKampagneDetailGraph->oKampagneDetailGraph_arr as $i => $oKampagneDetailGraph) {
            if ($nVonKey <= 0) {
                $oTMP_arr[$i] = $oKampagneDetailGraph;
            }
            $nVonKey--;
        }

        $_SESSION['Kampagne']->oKampagneDetailGraph->oKampagneDetailGraph_arr = $oTMP_arr;
    }
    // Gesamtstats
    if (is_array($oStatsAssoc_arr) && count($oStatsAssoc_arr) > 0) {
        foreach ($oStatsAssoc_arr as $oStatsDefAssoc_arr) {
            foreach ($oStatsDefAssoc_arr as $kKampagneDef => $oStatsDefAssoc) {
                if ($kKampagneDef !== 'cDatum') {
                    if (!isset($oStatsAssoc_arr['Gesamt'][$kKampagneDef])) {
                        $oStatsAssoc_arr['Gesamt'][$kKampagneDef] = $oStatsDefAssoc;
                    } else {
                        $oStatsAssoc_arr['Gesamt'][$kKampagneDef] += $oStatsDefAssoc;
                    }
                }
            }
        }
    }

    return $oStatsAssoc_arr;
}

/**
 * @param int    $kKampagne
 * @param object $oKampagneDef
 * @param string $cStamp
 * @param string $cStampText
 * @param array  $cMember_arr
 * @param string $cBlaetterSQL1
 * @return array
 */
function holeKampagneDefDetailStats($kKampagne, $oKampagneDef, $cStamp, &$cStampText, &$cMember_arr, $cBlaetterSQL1)
{
    $oDaten_arr = [];
    if ((int)$kKampagne > 0 && (int)$oKampagneDef->kKampagneDef > 0 && strlen($cStamp) > 0) {
        $cSQLSELECT = '';
        $cSQLWHERE  = '';
        baueDefDetailSELECTWHERE($cSQLSELECT, $cSQLWHERE, $cStamp);

        $oStats_arr = Shop::DB()->query(
            "SELECT kKampagne, kKampagneDef, kKey " . $cSQLSELECT . "
                FROM tkampagnevorgang
                " . $cSQLWHERE . "
                    AND kKampagne = " . (int)$kKampagne . "
                    AND kKampagneDef = " . (int)$oKampagneDef->kKampagneDef . $cBlaetterSQL1, 2
        );
        // Stamp Text
        switch ((int)$_SESSION['Kampagne']->nDetailAnsicht) {
            case 1:    // Jahr
                $cStampText = $oStats_arr[0]->cStampText;
                break;
            case 2:    // Monat
                $cStampTextParts_arr = isset($oStats_arr[0]->cStampText) ? explode('.', $oStats_arr[0]->cStampText) : [];
                $cMonat              = isset($cStampTextParts_arr [0]) ? $cStampTextParts_arr [0] : '';
                $cJahr               = isset($cStampTextParts_arr [1]) ? $cStampTextParts_arr [1] : '';
                $cStampText          = mappeENGMonat($cMonat) . ' ' . $cJahr;
                break;
            case 3:    // Woche
                $nDatum_arr = ermittleDatumWoche($oStats_arr[0]->cStampText);
                $cStampText = date('d.m.Y', $nDatum_arr[0]) . ' - ' . date('d.m.Y', $nDatum_arr[1]);
                break;
            case 4:    // Tag
                $cStampText = $oStats_arr[0]->cStampText;
                break;
        }

        // Kampagnendefinitionen
        switch ((int)$oKampagneDef->kKampagneDef) {
            case KAMPAGNE_DEF_HIT:    // HIT
                $oDaten_arr = Shop::DB()->query(
                    "SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey " . $cSQLSELECT . ",
                        tkampagnevorgang.cCustomData, DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                        IF(tbesucher.cIP IS NULL, tbesucherarchiv.cIP, tbesucher.cIP) AS cIP,
                        IF(tbesucher.cReferer IS NULL, tbesucherarchiv.cReferer, tbesucher.cReferer) AS cReferer,
                        IF(tbesucher.cEinstiegsseite IS NULL, tbesucherarchiv.cEinstiegsseite, tbesucher.cEinstiegsseite) AS cEinstiegsseite,
                        IF(tbesucher.cBrowser IS NULL, tbesucherarchiv.cBrowser, tbesucher.cBrowser) AS cBrowser,
                        DATE_FORMAT(if(tbesucher.dZeit IS NULL, tbesucherarchiv.dZeit, tbesucher.dZeit), '%d.%m.%Y %H:%i') AS dErstellt_DE,
                        tbesucherbot.cUserAgent
                        FROM tkampagnevorgang
                        LEFT JOIN tbesucher ON tbesucher.kBesucher = tkampagnevorgang.kKey
                        LEFT JOIN tbesucherarchiv ON tbesucherarchiv.kBesucher = tkampagnevorgang.kKey
                        LEFT JOIN tbesucherbot ON tbesucherbot.kBesucherBot = tbesucher.kBesucherBot
                        " . $cSQLWHERE . "
                            AND kKampagne = " . (int)$kKampagne . "
                            AND kKampagneDef = " . (int)$oKampagneDef->kKampagneDef . "
                        ORDER BY tkampagnevorgang.dErstellt DESC" . $cBlaetterSQL1, 2
                );

                if (is_array($oDaten_arr) && count($oDaten_arr) > 0) {
                    foreach ($oDaten_arr as $i => $oDaten) {
                        $cCustomDataParts_arr = explode(';', $oDaten->cCustomData);
                        $cEinstiegsseite      = isset($cCustomDataParts_arr [0]) ? $cCustomDataParts_arr [0] : '';
                        $cReferer             = isset($cCustomDataParts_arr [1]) ? $cCustomDataParts_arr [1] : '';

                        $oDaten_arr[$i]->cEinstiegsseite = $cEinstiegsseite;
                        $oDaten_arr[$i]->cReferer        = $cReferer;
                    }

                    $cMember_arr = [
                        'cIP'                 => 'IP-Adresse',
                        'cReferer'            => 'Referer',
                        'cEinstiegsseite'     => 'Einstiegsseite',
                        'cBrowser'            => 'Browser',
                        'cUserAgent'          => 'Suchmaschine',
                        'dErstellt_DE'        => 'Datum',
                        'dErstelltVorgang_DE' => 'Vorgangsdatum'
                    ];
                }
                break;
            case KAMPAGNE_DEF_VERKAUF:    // VERKAUF
                $oDaten_arr = Shop::DB()->query(
                    "SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey " . $cSQLSELECT . ",
                        DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                        IF(tkunde.cVorname IS NULL, 'n.v.', tkunde.cVorname) AS cVorname,
                        IF(tkunde.cNachname IS NULL, 'n.v.', tkunde.cNachname) AS cNachname,
                        IF(tkunde.cFirma IS NULL, 'n.v.', tkunde.cFirma) AS cFirma,
                        IF(tkunde.cMail IS NULL, 'n.v.', tkunde.cMail) AS cMail,
                        IF(tkunde.nRegistriert IS NULL, 'n.v.', tkunde.nRegistriert) AS nRegistriert,
                        IF(tbestellung.cZahlungsartName IS NULL, 'n.v.', tbestellung.cZahlungsartName) AS cZahlungsartName,
                        IF(tbestellung.cVersandartName IS NULL, 'n.v.', tbestellung.cVersandartName) AS cVersandartName,
                        IF(tbestellung.fGesamtsumme IS NULL, 'n.v.', tbestellung.fGesamtsumme) AS fGesamtsumme,
                        IF(tbestellung.cBestellNr IS NULL, 'n.v.', tbestellung.cBestellNr) AS cBestellNr,
                        IF(tbestellung.cStatus IS NULL, 'n.v.', tbestellung.cStatus) AS cStatus,
                        DATE_FORMAT(tbestellung.dErstellt, '%d.%m.%Y') AS dErstellt_DE
                        FROM tkampagnevorgang
                        LEFT JOIN tbestellung ON tbestellung.kBestellung = tkampagnevorgang.kKey
                        LEFT JOIN tkunde ON tkunde.kKunde = tbestellung.kKunde
                        " . $cSQLWHERE . "
                            AND kKampagne = " . (int)$kKampagne . "
                            AND kKampagneDef = " . (int)$oKampagneDef->kKampagneDef . "
                        ORDER BY tkampagnevorgang.dErstellt DESC", 2
                );

                if (is_array($oDaten_arr) && count($oDaten_arr) > 0) {
                    $dCount = count($oDaten_arr);
                    for ($i = 0; $i < $dCount; $i++) {
                        if ($oDaten_arr[$i]->cNachname !== 'n.v.') {
                            $oDaten_arr[$i]->cNachname = trim(entschluesselXTEA($oDaten_arr[$i]->cNachname));
                        }
                        if ($oDaten_arr[$i]->cFirma !== 'n.v.') {
                            $oDaten_arr[$i]->cFirma = trim(entschluesselXTEA($oDaten_arr[$i]->cFirma));
                        }
                        if ($oDaten_arr[$i]->nRegistriert !== 'n.v.') {
                            $oDaten_arr[$i]->nRegistriert = (int)$oDaten_arr[$i]->nRegistriert === 1
                                ? 'Ja'
                                : 'Nein';
                        }
                        if ($oDaten_arr[$i]->fGesamtsumme !== 'n.v.') {
                            $oDaten_arr[$i]->fGesamtsumme = gibPreisStringLocalized($oDaten_arr[$i]->fGesamtsumme);
                        }
                        if ($oDaten_arr[$i]->cStatus !== 'n.v.') {
                            $oDaten_arr[$i]->cStatus = lang_bestellstatus($oDaten_arr[$i]->cStatus);
                        }
                    }

                    $cMember_arr = [
                        'cZahlungsartName'    => 'Zahlungsart',
                        'cVersandartName'     => 'Versandart',
                        'nRegistriert'        => 'Registrierter Kunde',
                        'cVorname'            => 'Vorname',
                        'cNachname'           => 'Nachname',
                        'cStatus'             => 'Status',
                        'cBestellNr'          => 'BestellNr',
                        'fGesamtsumme'        => 'Bestellwert',
                        'dErstellt_DE'        => 'Bestelldatum',
                        'dErstelltVorgang_DE' => 'Vorgangsdatum'
                    ];
                }
                break;
            case KAMPAGNE_DEF_ANMELDUNG:    // ANMELDUNG
                $oDaten_arr = Shop::DB()->query(
                    "SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey " . $cSQLSELECT . ",
                        DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                        IF(tkunde.cVorname IS NULL, 'n.v.', tkunde.cVorname) AS cVorname,
                        IF(tkunde.cNachname IS NULL, 'n.v.', tkunde.cNachname) AS cNachname,
                        IF(tkunde.cFirma IS NULL, 'n.v.', tkunde.cFirma) AS cFirma,
                        IF(tkunde.cMail IS NULL, 'n.v.', tkunde.cMail) AS cMail,
                        IF(tkunde.nRegistriert IS NULL, 'n.v.', tkunde.nRegistriert) AS nRegistriert,
                        DATE_FORMAT(tkunde.dErstellt, '%d.%m.%Y') AS dErstellt_DE
                        FROM tkampagnevorgang
                        LEFT JOIN tkunde ON tkunde.kKunde = tkampagnevorgang.kKey
                        " . $cSQLWHERE . "
                            AND kKampagne = " . (int)$kKampagne . "
                            AND kKampagneDef = " . (int)$oKampagneDef->kKampagneDef . "
                        ORDER BY tkampagnevorgang.dErstellt DESC", 2
                );

                if (is_array($oDaten_arr) && count($oDaten_arr) > 0) {
                    $count = count($oDaten_arr);
                    for ($i = 0; $i < $count; $i++) {
                        if ($oDaten_arr[$i]->cNachname !== 'n.v.') {
                            $oDaten_arr[$i]->cNachname = trim(entschluesselXTEA($oDaten_arr[$i]->cNachname));
                        }
                        if ($oDaten_arr[$i]->cFirma !== 'n.v.') {
                            $oDaten_arr[$i]->cFirma = trim(entschluesselXTEA($oDaten_arr[$i]->cFirma));
                        }
                        if ($oDaten_arr[$i]->nRegistriert !== 'n.v.') {
                            $oDaten_arr[$i]->nRegistriert = ((int)$oDaten_arr[$i]->nRegistriert === 1)
                                ? 'Ja'
                                : 'Nein';
                        }
                    }

                    $cMember_arr = [
                        'cVorname'            => 'Vorname',
                        'cNachname'           => 'Nachname',
                        'cFirma'              => 'Firma',
                        'cMail'               => 'eMail',
                        'nRegistriert'        => 'Registriert',
                        'dErstellt_DE'        => 'Anmeldedatum',
                        'dErstelltVorgang_DE' => 'Vorgangsdatum'
                    ];
                }
                break;
            case KAMPAGNE_DEF_VERKAUFSSUMME:    // VERKAUFSSUMME
                $oDaten_arr = Shop::DB()->query(
                    "SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey " . $cSQLSELECT . ",
                        DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                        IF(tkunde.cVorname IS NULL, 'n.v.', tkunde.cVorname) AS cVorname,
                        IF(tkunde.cNachname IS NULL, 'n.v.', tkunde.cNachname) AS cNachname,
                        IF(tkunde.cFirma IS NULL, 'n.v.', tkunde.cFirma) AS cFirma,
                        IF(tkunde.cMail IS NULL, 'n.v.', tkunde.cMail) AS cMail,
                        IF(tkunde.nRegistriert IS NULL, 'n.v.', tkunde.nRegistriert) AS nRegistriert,
                        IF(tbestellung.cZahlungsartName IS NULL, 'n.v.', tbestellung.cZahlungsartName) AS cZahlungsartName,
                        IF(tbestellung.cVersandartName IS NULL, 'n.v.', tbestellung.cVersandartName) AS cVersandartName,
                        IF(tbestellung.fGesamtsumme IS NULL, 'n.v.', tbestellung.fGesamtsumme) AS fGesamtsumme,
                        IF(tbestellung.cBestellNr IS NULL, 'n.v.', tbestellung.cBestellNr) AS cBestellNr,
                        IF(tbestellung.cStatus IS NULL, 'n.v.', tbestellung.cStatus) AS cStatus,
                        DATE_FORMAT(tbestellung.dErstellt, '%d.%m.%Y') AS dErstellt_DE
                        FROM tkampagnevorgang
                        LEFT JOIN tbestellung ON tbestellung.kBestellung = tkampagnevorgang.kKey
                        LEFT JOIN tkunde ON tkunde.kKunde = tbestellung.kKunde
                        " . $cSQLWHERE . "
                            AND kKampagne = " . (int)$kKampagne . "
                            AND kKampagneDef = " . (int)$oKampagneDef->kKampagneDef . "
                        ORDER BY tkampagnevorgang.dErstellt DESC", 2
                );
                $dCount = count($oDaten_arr);
                if (is_array($oDaten_arr) && $dCount > 0) {
                    for ($i = 0; $i < $dCount; $i++) {
                        if ($oDaten_arr[$i]->cNachname !== 'n.v.') {
                            $oDaten_arr[$i]->cNachname = trim(entschluesselXTEA($oDaten_arr[$i]->cNachname));
                        }
                        if ($oDaten_arr[$i]->cFirma !== 'n.v.') {
                            $oDaten_arr[$i]->cFirma = trim(entschluesselXTEA($oDaten_arr[$i]->cFirma));
                        }
                        if ($oDaten_arr[$i]->nRegistriert !== 'n.v.') {
                            $oDaten_arr[$i]->nRegistriert = ((int)$oDaten_arr[$i]->nRegistriert === 1)
                                ? 'Ja'
                                : 'Nein';
                        }
                        if ($oDaten_arr[$i]->fGesamtsumme !== 'n.v.') {
                            $oDaten_arr[$i]->fGesamtsumme = gibPreisStringLocalized($oDaten_arr[$i]->fGesamtsumme);
                        }
                        if ($oDaten_arr[$i]->cStatus !== 'n.v.') {
                            $oDaten_arr[$i]->cStatus = lang_bestellstatus($oDaten_arr[$i]->cStatus);
                        }
                    }

                    $cMember_arr = [
                        'cZahlungsartName'    => 'Zahlungsart',
                        'cVersandartName'     => 'Versandart',
                        'nRegistriert'        => 'Registrierter Kunde',
                        'cVorname'            => 'Vorname',
                        'cNachname'           => 'Nachname',
                        'cStatus'             => 'Status',
                        'cBestellNr'          => 'BestellNr',
                        'fGesamtsumme'        => 'Bestellwert',
                        'dErstellt_DE'        => 'Bestelldatum',
                        'dErstelltVorgang_DE' => 'Vorgangsdatum'
                    ];
                }
                break;
            case KAMPAGNE_DEF_FRAGEZUMPRODUKT:    // FRAGEZUMPRODUKT
                $oDaten_arr = Shop::DB()->query(
                    "SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey " . $cSQLSELECT . ",
                        DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                        IF(tproduktanfragehistory.cVorname IS NULL, 'n.v.', tproduktanfragehistory.cVorname) AS cVorname,
                        IF(tproduktanfragehistory.cNachname IS NULL, 'n.v.', tproduktanfragehistory.cNachname) AS cNachname,
                        IF(tproduktanfragehistory.cFirma IS NULL, 'n.v.', tproduktanfragehistory.cFirma) AS cFirma,
                        IF(tproduktanfragehistory.cTel IS NULL, 'n.v.', tproduktanfragehistory.cTel) AS cTel,
                        IF(tproduktanfragehistory.cMail IS NULL, 'n.v.', tproduktanfragehistory.cMail) AS cMail,
                        IF(tproduktanfragehistory.cNachricht IS NULL, 'n.v.', tproduktanfragehistory.cNachricht) AS cNachricht,
                        IF(tartikel.cName IS NULL, 'n.v.', tartikel.cName) AS cArtikelname,
                        IF(tartikel.cArtNr IS NULL, 'n.v.', tartikel.cArtNr) AS cArtNr,
                        DATE_FORMAT(tproduktanfragehistory.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_DE
                        FROM tkampagnevorgang
                        LEFT JOIN tproduktanfragehistory ON tproduktanfragehistory.kProduktanfrageHistory = tkampagnevorgang.kKey
                        LEFT JOIN tartikel ON tartikel.kArtikel = tproduktanfragehistory.kArtikel
                        " . $cSQLWHERE . "
                            AND kKampagne = " . (int)$kKampagne . "
                            AND kKampagneDef = " . (int)$oKampagneDef->kKampagneDef . "
                        ORDER BY tkampagnevorgang.dErstellt DESC", 2
                );

                if (is_array($oDaten_arr) && count($oDaten_arr) > 0) {
                    $cMember_arr = [
                        'cArtikelname'        => 'Artikel',
                        'cArtNr'              => 'Artikelnummer',
                        'cVorname'            => 'Vorname',
                        'cNachname'           => 'Nachname',
                        'cFirma'              => 'Firma',
                        'cTel'                => 'Telefon',
                        'cMail'               => 'eMail',
                        'cNachricht'          => 'Nachricht',
                        'dErstellt_DE'        => 'Erstellt am',
                        'dErstelltVorgang_DE' => 'Vorgangsdatum'
                    ];
                }

                break;
            case KAMPAGNE_DEF_VERFUEGBARKEITSANFRAGE:    // VERFUEGBARKEITSANFRAGE
                $oDaten_arr = Shop::DB()->query(
                    "SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey " . $cSQLSELECT . ",
                        DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                        IF(tverfuegbarkeitsbenachrichtigung.cVorname IS NULL, 'n.v.', tverfuegbarkeitsbenachrichtigung.cVorname) AS cVorname,
                        IF(tverfuegbarkeitsbenachrichtigung.cNachname IS NULL, 'n.v.', tverfuegbarkeitsbenachrichtigung.cNachname) AS cNachname,
                        IF(tverfuegbarkeitsbenachrichtigung.cMail IS NULL, 'n.v.', tverfuegbarkeitsbenachrichtigung.cMail) AS cMail,
                        IF(tverfuegbarkeitsbenachrichtigung.cAbgeholt IS NULL, 'n.v.', tverfuegbarkeitsbenachrichtigung.cAbgeholt) AS cAbgeholt,
                        IF(tartikel.cName IS NULL, 'n.v.', tartikel.cName) AS cArtikelname,
                        IF(tartikel.cArtNr IS NULL, 'n.v.', tartikel.cArtNr) AS cArtNr,
                        DATE_FORMAT(tverfuegbarkeitsbenachrichtigung.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_DE
                        FROM tkampagnevorgang
                        LEFT JOIN tverfuegbarkeitsbenachrichtigung 
                                ON tverfuegbarkeitsbenachrichtigung.kVerfuegbarkeitsbenachrichtigung = tkampagnevorgang.kKey
                        LEFT JOIN tartikel 
                                ON tartikel.kArtikel = tverfuegbarkeitsbenachrichtigung.kArtikel
                        " . $cSQLWHERE . "
                            AND kKampagne = " . (int)$kKampagne . "
                            AND kKampagneDef = " . (int)$oKampagneDef->kKampagneDef . "
                        ORDER BY tkampagnevorgang.dErstellt DESC", 2
                );

                if (is_array($oDaten_arr) && count($oDaten_arr) > 0) {
                    $cMember_arr = [
                        'cArtikelname'        => 'Artikel',
                        'cArtNr'              => 'Artikelnummer',
                        'cVorname'            => 'Vorname',
                        'cNachname'           => 'Nachname',
                        'cMail'               => 'eMail',
                        'cAbgeholt'           => 'Abgeholt durch Wawi',
                        'dErstellt_DE'        => 'Erstellt am',
                        'dErstelltVorgang_DE' => 'Vorgangsdatum'
                    ];
                }

                break;
            case KAMPAGNE_DEF_LOGIN:    // LOGIN
                $oDaten_arr = Shop::DB()->query(
                    "SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey " . $cSQLSELECT . ",
                        DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                        IF(tkunde.cVorname IS NULL, 'n.v.', tkunde.cVorname) AS cVorname,
                        IF(tkunde.cNachname IS NULL, 'n.v.', tkunde.cNachname) AS cNachname,
                        IF(tkunde.cFirma IS NULL, 'n.v.', tkunde.cFirma) AS cFirma,
                        IF(tkunde.cMail IS NULL, 'n.v.', tkunde.cMail) AS cMail,
                        IF(tkunde.nRegistriert IS NULL, 'n.v.', tkunde.nRegistriert) AS nRegistriert,
                        DATE_FORMAT(tkunde.dErstellt, '%d.%m.%Y') AS dErstellt_DE
                        FROM tkampagnevorgang
                        LEFT JOIN tkunde 
                                ON tkunde.kKunde = tkampagnevorgang.kKey
                        " . $cSQLWHERE . "
                            AND kKampagne = " . (int)$kKampagne . "
                            AND kKampagneDef = " . (int)$oKampagneDef->kKampagneDef . "
                        ORDER BY tkampagnevorgang.dErstellt DESC", 2
                );
                $dCount = count($oDaten_arr);
                if (is_array($oDaten_arr) && $dCount > 0) {
                    for ($i = 0; $i < $dCount; $i++) {
                        if ($oDaten_arr[$i]->cNachname !== 'n.v.') {
                            $oDaten_arr[$i]->cNachname = trim(entschluesselXTEA($oDaten_arr[$i]->cNachname));
                        }
                        if ($oDaten_arr[$i]->cFirma !== 'n.v.') {
                            $oDaten_arr[$i]->cFirma = trim(entschluesselXTEA($oDaten_arr[$i]->cFirma));
                        }

                        if ($oDaten_arr[$i]->nRegistriert !== 'n.v.') {
                            $oDaten_arr[$i]->nRegistriert = ((int)$oDaten_arr[$i]->nRegistriert === 1)
                                ? 'Ja'
                                : 'Nein';
                        }
                    }

                    $cMember_arr = [
                        'cVorname'            => 'Vorname',
                        'cNachname'           => 'Nachname',
                        'cFirma'              => 'Firma',
                        'cMail'               => 'eMail',
                        'nRegistriert'        => 'Registriert',
                        'dErstellt_DE'        => 'Anmeldedatum',
                        'dErstelltVorgang_DE' => 'Vorgangsdatum'
                    ];
                }
                break;
            case KAMPAGNE_DEF_WUNSCHLISTE:    // WUNSCHLISTE
                $oDaten_arr = Shop::DB()->query(
                    "SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey " . $cSQLSELECT . ",
                        DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                        IF(tkunde.cVorname IS NULL, 'n.v.', tkunde.cVorname) AS cVorname,
                        IF(tkunde.cNachname IS NULL, 'n.v.', tkunde.cNachname) AS cNachname,
                        IF(tkunde.cFirma IS NULL, 'n.v.', tkunde.cFirma) AS cFirma,
                        IF(tkunde.cMail IS NULL, 'n.v.', tkunde.cMail) AS cMail,
                        IF(tkunde.nRegistriert IS NULL, 'n.v.', tkunde.nRegistriert) AS nRegistriert,
                        IF(tartikel.cName IS NULL, 'n.v.', tartikel.cName) AS cArtikelname,
                        IF(tartikel.cArtNr IS NULL, 'n.v.', tartikel.cArtNr) AS cArtNr,
                        DATE_FORMAT(twunschlistepos.dHinzugefuegt, '%d.%m.%Y') AS dErstellt_DE
                        FROM tkampagnevorgang
                        LEFT JOIN twunschlistepos ON twunschlistepos.kWunschlistePos = tkampagnevorgang.kKey
                        LEFT JOIN twunschliste ON twunschliste.kWunschliste = twunschlistepos.kWunschliste
                        LEFT JOIN tkunde ON tkunde.kKunde = twunschliste.kKunde
                        LEFT JOIN tartikel ON tartikel.kArtikel = twunschlistepos.kArtikel
                        " . $cSQLWHERE . "
                            AND kKampagne = " . (int)$kKampagne . "
                            AND kKampagneDef = " . (int)$oKampagneDef->kKampagneDef . "
                        ORDER BY tkampagnevorgang.dErstellt DESC", 2
                );
                $dCount = count($oDaten_arr);
                if (is_array($oDaten_arr) && $dCount > 0) {
                    for ($i = 0; $i < $dCount; $i++) {
                        if ($oDaten_arr[$i]->cNachname !== 'n.v.') {
                            $oDaten_arr[$i]->cNachname = trim(entschluesselXTEA($oDaten_arr[$i]->cNachname));
                        }
                        if ($oDaten_arr[$i]->cFirma !== 'n.v.') {
                            $oDaten_arr[$i]->cFirma = trim(entschluesselXTEA($oDaten_arr[$i]->cFirma));
                        }

                        if ($oDaten_arr[$i]->nRegistriert !== 'n.v.') {
                            $oDaten_arr[$i]->nRegistriert = ((int)$oDaten_arr[$i]->nRegistriert === 1)
                                ? 'Ja'
                                : 'Nein';
                        }
                    }

                    $cMember_arr = [
                        'cArtikelname'        => 'Artikel',
                        'cArtNr'              => 'Artikelnummer',
                        'cVorname'            => 'Vorname',
                        'cNachname'           => 'Nachname',
                        'cFirma'              => 'Firma',
                        'cMail'               => 'eMail',
                        'nRegistriert'        => 'Registriert',
                        'dErstellt_DE'        => 'Anmeldedatum',
                        'dErstelltVorgang_DE' => 'Vorgangsdatum'
                    ];
                }
                break;
            case KAMPAGNE_DEF_WARENKORB:    // WARENKORB
                $kKundengruppe = Kundengruppe::getDefaultGroupID();

                $oDaten_arr = Shop::DB()->query(
                    "SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey " . $cSQLSELECT . ",
                        DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                        IF(tartikel.kArtikel IS NULL, 'n.v.', tartikel.kArtikel) AS kArtikel,
                        if(tartikel.cName IS NULL, 'n.v.', tartikel.cName) AS cName,
                        IF(tartikel.fLagerbestand IS NULL, 'n.v.', tartikel.fLagerbestand) AS fLagerbestand,
                        IF(tartikel.cArtNr IS NULL, 'n.v.', tartikel.cArtNr) AS cArtNr,
                        IF(tartikel.fMwSt IS NULL, 'n.v.', tartikel.fMwSt) AS fMwSt,
                        IF(tpreise.fVKNetto IS NULL, 'n.v.', tpreise.fVKNetto) AS fVKNetto, DATE_FORMAT(tartikel.dLetzteAktualisierung, '%d.%m.%Y %H:%i') AS dLetzteAktualisierung_DE
                        FROM tkampagnevorgang
                        LEFT JOIN tartikel ON tartikel.kArtikel = tkampagnevorgang.kKey
                        LEFT JOIN tpreise ON tpreise.kArtikel = tartikel.kArtikel
                            AND kKundengruppe = " . $kKundengruppe . "
                        " . $cSQLWHERE . "
                            AND tkampagnevorgang.kKampagne = " . (int)$kKampagne . "
                            AND tkampagnevorgang.kKampagneDef = " . (int)$oKampagneDef->kKampagneDef . "
                        ORDER BY tkampagnevorgang.dErstellt DESC", 2
                );

                if (is_array($oDaten_arr) && count($oDaten_arr) > 0) {
                    $_SESSION['Kundengruppe']->darfPreiseSehen = 1;
                    $count = count($oDaten_arr);
                    for ($i = 0; $i < $count; $i++) {
                        if (isset($oDaten_arr[$i]->fVKNetto) && $oDaten_arr[$i]->fVKNetto > 0) {
                            $oDaten_arr[$i]->fVKNetto = gibPreisStringLocalized($oDaten_arr[$i]->fVKNetto);
                        }
                        if (isset($oDaten_arr[$i]->fMwSt) && $oDaten_arr[$i]->fMwSt > 0) {
                            $oDaten_arr[$i]->fMwSt = number_format($oDaten_arr[$i]->fMwSt, 2) . "%";
                        }
                    }

                    $cMember_arr = [
                        'cName'                    => 'Artikel',
                        'cArtNr'                   => 'Artikelnummer',
                        'fVKNetto'                 => 'Netto Preis',
                        'fMwSt'                    => 'MwSt',
                        'fLagerbestand'            => 'Lagerbestand',
                        'dLetzteAktualisierung_DE' => 'Letzte Aktualisierung',
                        'dErstelltVorgang_DE'      => 'Vorgangsdatum'
                    ];
                }
                break;
            case KAMPAGNE_DEF_NEWSLETTER:    // NEWSLETTER
                $oDaten_arr = Shop::DB()->query(
                    "SELECT tkampagnevorgang.kKampagne, tkampagnevorgang.kKampagneDef, tkampagnevorgang.kKey " . $cSQLSELECT . ",
                        DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltVorgang_DE,
                        IF(tnewsletter.cName IS NULL, 'n.v.', tnewsletter.cName) AS cName,
                        IF(tnewsletter.cBetreff IS NULL, 'n.v.', tnewsletter.cBetreff) AS cBetreff, DATE_FORMAT(tnewslettertrack.dErstellt, '%d.%m.%Y %H:%i') AS dErstelltTrack_DE,
                        IF(tnewsletterempfaenger.cVorname IS NULL, 'n.v.', tnewsletterempfaenger.cVorname) AS cVorname,
                        IF(tnewsletterempfaenger.cNachname IS NULL, 'n.v.', tnewsletterempfaenger.cNachname) AS cNachname,
                        IF(tnewsletterempfaenger.cEmail IS NULL, 'n.v.', tnewsletterempfaenger.cEmail) AS cEmail
                        FROM tkampagnevorgang
                        LEFT JOIN tnewslettertrack ON tnewslettertrack.kNewsletterTrack = tkampagnevorgang.kKey
                        LEFT JOIN tnewsletter ON tnewsletter.kNewsletter = tnewslettertrack.kNewsletter
                        LEFT JOIN tnewsletterempfaenger ON tnewsletterempfaenger.kNewsletterEmpfaenger = tnewslettertrack.kNewsletterEmpfaenger
                        " . $cSQLWHERE . "
                            AND tkampagnevorgang.kKampagne = " . (int)$kKampagne . "
                            AND tkampagnevorgang.kKampagneDef = " . (int)$oKampagneDef->kKampagneDef . "
                        ORDER BY tkampagnevorgang.dErstellt DESC", 2
                );

                if (is_array($oDaten_arr) && count($oDaten_arr) > 0) {
                    $cMember_arr = [
                        'cName'               => 'Newsletter',
                        'cBetreff'            => 'Betreff',
                        'cVorname'            => 'Vorname',
                        'cNachname'           => 'Nachname',
                        'cEmail'              => 'eMail',
                        'dErstelltTrack_DE'   => 'Datum der &Ouml;ffnung',
                        'dErstelltVorgang_DE' => 'Vorgangsdatum'
                    ];
                }
                break;
        }
    }

    return $oDaten_arr;
}

/**
 * @param string $cSQLSELECT
 * @param string $cSQLWHERE
 * @param string $cStamp
 */
function baueDefDetailSELECTWHERE(&$cSQLSELECT, &$cSQLWHERE, $cStamp)
{
    $cStamp = Shop::DB()->escape($cStamp);
    switch ((int)$_SESSION['Kampagne']->nDetailAnsicht) {
        case 1:    // Jahr
            $cSQLSELECT = ", DATE_FORMAT(tkampagnevorgang.dErstellt, '%Y') AS cStampText";
            $cSQLWHERE  = " WHERE DATE_FORMAT(tkampagnevorgang.dErstellt, '%Y') = '" . $cStamp . "'";
            break;
        case 2:    // Monat
            $cSQLSELECT = ", DATE_FORMAT(tkampagnevorgang.dErstellt, '%m.%Y') AS cStampText";
            $cSQLWHERE  = " WHERE DATE_FORMAT(tkampagnevorgang.dErstellt, '%Y-%m') = '" . $cStamp . "'";
            break;
        case 3:    // Woche
            $cSQLSELECT = ", DATE_FORMAT(tkampagnevorgang.dErstellt, '%Y-%m-%d') AS cStampText";
            $cSQLWHERE  = " WHERE DATE_FORMAT(tkampagnevorgang.dErstellt, '%u') = '" . $cStamp . "'";
            break;
        case 4:    // Tag
            $cSQLSELECT = ", DATE_FORMAT(tkampagnevorgang.dErstellt, '%d.%m.%Y') AS cStampText";
            $cSQLWHERE  = " WHERE DATE_FORMAT(tkampagnevorgang.dErstellt, '%Y-%m-%d') = '" . $cStamp . "'";
            break;
    }
}

/**
 * @return array
 */
function gibDetailDatumZeitraum()
{
    $cZeitraum_arr               = [];
    $cZeitraum_arr['cDatum']     = [];
    $cZeitraum_arr['cDatumFull'] = [];
    switch ((int)$_SESSION['Kampagne']->nDetailAnsicht) {
        case 1:    // Jahr
            $nFromStamp          = mktime(0, 0, 0, $_SESSION['Kampagne']->cFromDate_arr['nMonat'], 1, $_SESSION['Kampagne']->cFromDate_arr['nJahr']);
            $nAnzahlTageProMonat = date('t', mktime(0, 0, 0, $_SESSION['Kampagne']->cToDate_arr['nMonat'], 1, $_SESSION['Kampagne']->cToDate_arr['nJahr']));
            $nToStamp            = mktime(0, 0, 0, $_SESSION['Kampagne']->cToDate_arr['nMonat'], (int)$nAnzahlTageProMonat, $_SESSION['Kampagne']->cToDate_arr['nJahr']);
            $nTMPStamp           = $nFromStamp;
            while ($nTMPStamp <= $nToStamp) {
                $cZeitraum_arr['cDatum'][]     = date('Y', $nTMPStamp);
                $cZeitraum_arr['cDatumFull'][] = date('Y', $nTMPStamp);
                $nDiff                         = mktime(0, 0, 0, (int)date('m', $nTMPStamp), (int)date('d', $nTMPStamp), (int)date('Y', $nTMPStamp) + 1) - $nTMPStamp;
                $nTMPStamp += $nDiff;
            }
            break;
        case 2:    // Monat
            $nFromStamp          = mktime(0, 0, 0, $_SESSION['Kampagne']->cFromDate_arr['nMonat'], 1, $_SESSION['Kampagne']->cFromDate_arr['nJahr']);
            $nAnzahlTageProMonat = date('t', mktime(0, 0, 0, $_SESSION['Kampagne']->cToDate_arr['nMonat'], 1, $_SESSION['Kampagne']->cToDate_arr['nJahr']));
            $nToStamp            = mktime(0, 0, 0, $_SESSION['Kampagne']->cToDate_arr['nMonat'], (int)$nAnzahlTageProMonat, $_SESSION['Kampagne']->cToDate_arr['nJahr']);
            $nTMPStamp           = $nFromStamp;
            while ($nTMPStamp <= $nToStamp) {
                $cZeitraum_arr['cDatum'][]     = date('Y-m', $nTMPStamp);
                $cZeitraum_arr['cDatumFull'][] = mappeENGMonat(date('m', $nTMPStamp)) . ' ' . date('Y', $nTMPStamp);
                $nMonat                        = (int)date('m', $nTMPStamp) + 1;
                $nJahr                         = (int)date('Y', $nTMPStamp);
                if ($nMonat > 12) {
                    $nMonat = 1;
                    $nJahr++;
                }

                $nDiff = mktime(0, 0, 0, $nMonat, (int)date('d', $nTMPStamp), $nJahr) - $nTMPStamp;

                $nTMPStamp += $nDiff;
            }
            break;
        case 3:    // Woche
            $nDatumWoche_arr = ermittleDatumWoche($_SESSION['Kampagne']->cFromDate_arr['nJahr'] . '-' . $_SESSION['Kampagne']->cFromDate_arr['nMonat'] . '-' . $_SESSION['Kampagne']->cFromDate_arr['nTag']);
            $nFromStamp      = $nDatumWoche_arr[0];
            $nToStamp        = mktime(0, 0, 0, $_SESSION['Kampagne']->cToDate_arr['nMonat'], $_SESSION['Kampagne']->cToDate_arr['nTag'], $_SESSION['Kampagne']->cToDate_arr['nJahr']);
            $nTMPStamp       = $nFromStamp;
            while ($nTMPStamp <= $nToStamp) {
                $nDatumWoche_arr               = ermittleDatumWoche(date('Y-m-d', $nTMPStamp));
                $cZeitraum_arr['cDatum'][]     = date('W', $nTMPStamp);
                $cZeitraum_arr['cDatumFull'][] = date('d.m.Y', $nDatumWoche_arr[0]) . ' - ' . date('d.m.Y', $nDatumWoche_arr[1]);
                $nAnzahlTageProMonat           = date('t', $nTMPStamp);

                $nTag   = (int)date('d', $nDatumWoche_arr[1]) + 1;
                $nMonat = (int)date('m', $nDatumWoche_arr[1]);
                $nJahr  = (int)date('Y', $nDatumWoche_arr[1]);

                if ($nTag > $nAnzahlTageProMonat) {
                    $nTag = 1;
                    $nMonat++;

                    if ($nMonat > 12) {
                        $nMonat = 1;
                        $nJahr++;
                    }
                }

                $nDiff = mktime(0, 0, 0, $nMonat, $nTag, $nJahr) - $nTMPStamp;

                $nTMPStamp += $nDiff;
            }
            break;
        case 4:    // Tag
            $nFromStamp = mktime(0, 0, 0, $_SESSION['Kampagne']->cFromDate_arr['nMonat'], $_SESSION['Kampagne']->cFromDate_arr['nTag'], $_SESSION['Kampagne']->cFromDate_arr['nJahr']);
            $nToStamp   = mktime(0, 0, 0, $_SESSION['Kampagne']->cToDate_arr['nMonat'], $_SESSION['Kampagne']->cToDate_arr['nTag'], $_SESSION['Kampagne']->cToDate_arr['nJahr']);
            $nTMPStamp  = $nFromStamp;
            while ($nTMPStamp <= $nToStamp) {
                $cZeitraum_arr['cDatum'][]     = date('Y-m-d', $nTMPStamp);
                $cZeitraum_arr['cDatumFull'][] = date('d.m.Y', $nTMPStamp);
                $nAnzahlTageProMonat           = date('t', $nTMPStamp);
                $nTag                          = date('d', $nTMPStamp) + 1;
                $nMonat                        = date('m', $nTMPStamp);
                $nJahr                         = date('Y', $nTMPStamp);

                if ($nTag > $nAnzahlTageProMonat) {
                    $nTag = 1;
                    $nMonat++;

                    if ($nMonat > 12) {
                        $nMonat = 1;
                        $nJahr++;
                    }
                }

                $nDiff = mktime(0, 0, 0, $nMonat, $nTag, $nJahr) - $nTMPStamp;

                $nTMPStamp += $nDiff;
            }
            break;
    }

    return $cZeitraum_arr;
}

/**
 * @param string $cStampOld
 * @param int    $nSprung - -1 = Vergangenheit, 1 = Zukunft
 * @param int    $nAnsicht
 * @return string
 */
function gibStamp($cStampOld, $nSprung, $nAnsicht)
{
    if (strlen($cStampOld) > 0 && ($nSprung == -1 || $nSprung == 1) && $nAnsicht > 0) {
        $cFkt = ($nSprung == 1) ? 'DATE_ADD' : 'DATE_SUB';

        switch ((int)$nAnsicht) {
            case 1:    // Monat
                $oDate = Shop::DB()->query("SELECT " . $cFkt . "('" . $cStampOld . "', INTERVAL 1 MONTH) AS cStampNew, if(" . $cFkt . "('" . $cStampOld . "', INTERVAL 1 MONTH) > now(), 1, 0) AS nGroesser", 1);

                if ($oDate->nGroesser == 1) {
                    return date('Y-m-d');
                }

                $cDatum_arr = gibDatumTeile($oDate->cStampNew);

                return $cDatum_arr['cJahr'] . '-' . $cDatum_arr['cMonat'] . '-' . $cDatum_arr['cTag'];
                break;
            case 2:    // Woche
                $oDate = Shop::DB()->query("SELECT " . $cFkt . "('" . $cStampOld . "', INTERVAL 1 WEEK) AS cStampNew, if(" . $cFkt . "('" . $cStampOld . "', INTERVAL 1 WEEK) > now(), 1, 0) AS nGroesser", 1);

                if ($oDate->nGroesser == 1) {
                    return date('Y-m-d');
                }

                $cDatum_arr = gibDatumTeile($oDate->cStampNew);

                return $cDatum_arr['cJahr'] . '-' . $cDatum_arr['cMonat'] . '-' . $cDatum_arr['cTag'];
                break;
            case 3:    // Tag
                $oDate = Shop::DB()->query("SELECT " . $cFkt . "('" . $cStampOld . "', INTERVAL 1 DAY) AS cStampNew, if(" . $cFkt . "('" . $cStampOld . "', INTERVAL 1 DAY) > now(), 1, 0) AS nGroesser", 1);

                if ($oDate->nGroesser == 1) {
                    return date('Y-m-d');
                }

                $cDatum_arr = gibDatumTeile($oDate->cStampNew);

                return $cDatum_arr['cJahr'] . '-' . $cDatum_arr['cMonat'] . '-' . $cDatum_arr['cTag'];
                break;
        }
    }

    return $cStampOld;
}

/**
 * @param Kampagne $oKampagne
 * @return int
 *
 * Returncodes:
 * 1 = Alles O.K.
 * 2 = Kampagne konnte nicht gespeichert werden
 * 3 = Kampagnenname ist leer
 * 4 = Kampagnenparamter ist leer
 * 5 = Kampagnenwert ist leer
 * 6 = Kampagnennamen schon vergeben
 * 7 = Kampagnenparameter schon vergeben
 */
function speicherKampagne($oKampagne)
{
    // Standardkampagnen (Interne) Werte herstellen
    if (isset($oKampagne->kKampagne) && ($oKampagne->kKampagne < 1000 && $oKampagne->kKampagne > 0)) {
        $oKampagneTMP = Shop::DB()->query(
            "SELECT *
                FROM tkampagne
                WHERE kKampagne = " . (int)$oKampagne->kKampagne, 1
        );

        if (isset($oKampagneTMP->kKampagne)) {
            $oKampagne->cName      = $oKampagneTMP->cName;
            $oKampagne->cWert      = $oKampagneTMP->cWert;
            $oKampagne->nDynamisch = $oKampagneTMP->nDynamisch;
        }
    }

    // Plausi
    if (strlen($oKampagne->cName) === 0) {
        return 3;// Kampagnenname ist leer
    }
    if (strlen($oKampagne->cParameter) === 0) {
        return 4;// Kampagnenparamter ist leer
    }
    if (strlen($oKampagne->cWert) === 0 && $oKampagne->nDynamisch != 1) {
        return 5;//  Kampagnenwert ist leer
    }
    // Name schon vorhanden?
    $oKampagneTMP = Shop::DB()->queryPrepared(
        "SELECT kKampagne
            FROM tkampagne
            WHERE cName = :cName", ['cName' => $oKampagne->cName], 1
    );

    if (isset($oKampagneTMP->kKampagne) && $oKampagneTMP->kKampagne > 0 && (!isset($oKampagne->kKampagne) || $oKampagne->kKampagne == 0)) {
        return 6;// Kampagnennamen schon vergeben
    }
    // Parameter schon vorhanden?
    if (isset($oKampagne->nDynamisch) && $oKampagne->nDynamisch == 1) {
        $oKampagneTMP = Shop::DB()->queryPrepared(
            "SELECT kKampagne
                FROM tkampagne
                WHERE cParameter = :param", ['param' => $oKampagne->cParameter], 1
        );

        if (isset($oKampagneTMP->kKampagne) && $oKampagneTMP->kKampagne > 0 && (!isset($oKampagne->kKampagne) || $oKampagne->kKampagne == 0)) {
            return 7;// Kampagnenparameter schon vergeben
        }
    }
    // Editieren?
    if (isset($oKampagne->kKampagne) && $oKampagne->kKampagne > 0) {
        $oKampagne->updateInDB();
    } else {
        $oKampagne->insertInDB();
    }
    // Speichern
    Shop::Cache()->flush('campaigns');

    return 1;
}

/**
 * @param int $nReturnValue
 * @return string
 */
function mappeFehlerCodeSpeichern($nReturnValue)
{
    if ((int)$nReturnValue > 0) {
        switch ((int)$nReturnValue) {
            case 2:
                return 'Fehler: Kampagne konnte nicht gespeichert werden';
                break;
            case 3:
                return 'Fehler: Bitte geben Sie einen Kampagnennamen ein.';
                break;
            case 4:
                return 'Fehler: Bitte geben Sie einen Kampagnenparameter ein.';
                break;
            case 5:
                return 'Fehler: Bitte geben Sie einen Kampagnenwert ein.';
                break;
            case 6:
                return 'Fehler: Der angegebene Kampagnenname ist bereits vergeben.';
                break;
            case 7:
                return 'Fehler: Der angegebene Kampagnenparameter ist bereits vergeben.';
                break;
        }
    }

    return '';
}

/**
 * @param array $kKampagne_arr
 * @return int
 */
function loescheGewaehlteKampagnen($kKampagne_arr)
{
    if (is_array($kKampagne_arr) && count($kKampagne_arr) > 0) {
        foreach ($kKampagne_arr as $i => $kKampagne) {
            if ($kKampagne >= 1000) {
                // Nur alle externen Kampagnen sind löschbar

                $oKampagne = new Kampagne($kKampagne);
                if ($oKampagne->kKampagne !== null && $oKampagne->kKampagne > 0) {
                    $oKampagne->deleteInDB();
                }
            }
        }
        Shop::Cache()->flush('campaigns');

        return 1;
    }

    return 0;
}

/**
 * @param array $cDatumNow_arr
 */
function setzeDetailZeitraum($cDatumNow_arr)
{
    // 1 = Jahr
    // 2 = Monat
    // 3 = Woche
    // 4 = Tag
    if (!isset($_SESSION['Kampagne']->nDetailAnsicht)) {
        $_SESSION['Kampagne']->nDetailAnsicht = 2;
    }
    if (!isset($_SESSION['Kampagne']->cFromDate_arr)) {
        $_SESSION['Kampagne']->cFromDate_arr['nJahr']  = (int)$cDatumNow_arr['cJahr'];
        $_SESSION['Kampagne']->cFromDate_arr['nMonat'] = (int)$cDatumNow_arr['cMonat'];
        $_SESSION['Kampagne']->cFromDate_arr['nTag']   = (int)$cDatumNow_arr['cTag'];
    }
    if (!isset($_SESSION['Kampagne']->cToDate_arr)) {
        $_SESSION['Kampagne']->cToDate_arr['nJahr']  = (int)$cDatumNow_arr['cJahr'];
        $_SESSION['Kampagne']->cToDate_arr['nMonat'] = (int)$cDatumNow_arr['cMonat'];
        $_SESSION['Kampagne']->cToDate_arr['nTag']   = (int)$cDatumNow_arr['cTag'];
    }
    if (!isset($_SESSION['Kampagne']->cFromDate)) {
        $_SESSION['Kampagne']->cFromDate = (int)$cDatumNow_arr['cJahr'] . '-' . (int)$cDatumNow_arr['cMonat'] . '-' . (int)$cDatumNow_arr['cTag'];
    }
    if (!isset($_SESSION['Kampagne']->cToDate)) {
        $_SESSION['Kampagne']->cToDate = (int)$cDatumNow_arr['cJahr'] . '-' . (int)$cDatumNow_arr['cMonat'] . '-' . (int)$cDatumNow_arr['cTag'];
    }
    // Ansicht und Zeitraum
    if (verifyGPCDataInteger('zeitraum') === 1) {
        // Ansicht
        if (isset($_POST['nAnsicht']) && (int)$_POST['nAnsicht'] > 0) {
            $_SESSION['Kampagne']->nDetailAnsicht = $_POST['nAnsicht'];
        }
        // Zeitraum
        if (isset($_POST['cFromDay'], $_POST['cFromMonth'], $_POST['cFromYear']) &&
            (int)$_POST['cFromDay'] > 0 &&
            (int)$_POST['cFromMonth'] > 0 &&
            (int)$_POST['cFromYear'] > 0
        ) {
            $_SESSION['Kampagne']->cFromDate_arr['nJahr']  = (int)$_POST['cFromYear'];
            $_SESSION['Kampagne']->cFromDate_arr['nMonat'] = (int)$_POST['cFromMonth'];
            $_SESSION['Kampagne']->cFromDate_arr['nTag']   = (int)$_POST['cFromDay'];
            $_SESSION['Kampagne']->cFromDate               = (int)$_POST['cFromYear'] . '-' .
                (int)$_POST['cFromMonth'] . '-' .
                (int)$_POST['cFromDay'];
        }
        if (isset($_POST['cToDay'], $_POST['cToMonth'], $_POST['cToYear']) &&
            (int)$_POST['cToDay'] > 0 &&
            (int)$_POST['cToMonth'] > 0 &&
            (int)$_POST['cToYear'] > 0
        ) {
            $_SESSION['Kampagne']->cToDate_arr['nJahr']  = (int)$_POST['cToYear'];
            $_SESSION['Kampagne']->cToDate_arr['nMonat'] = (int)$_POST['cToMonth'];
            $_SESSION['Kampagne']->cToDate_arr['nTag']   = (int)$_POST['cToDay'];
            $_SESSION['Kampagne']->cToDate               = (int)$_POST['cToYear'] . '-' .
                (int)$_POST['cToMonth'] . '-' . (int)$_POST['cToDay'];
        }
    }

    checkGesamtStatZeitParam();
}

/**
 * @return bool|string
 */
function checkGesamtStatZeitParam()
{
    $cStamp = '';

    // Klick durch Gesamtübersicht
    if (strlen(verifyGPDataString('cZeitParam')) > 0) {
        $cZeitraum          = base64_decode(verifyGPDataString('cZeitParam'));
        $cZeitraumParts_arr = explode(' - ', $cZeitraum);
        $cStartDatum        = isset($cZeitraumParts_arr[0]) ? $cZeitraumParts_arr[0] : '';
        $cEndDatum          = isset($cZeitraumParts_arr[1]) ? $cZeitraumParts_arr[1] : '';

        // Ansicht war Tag
        if (strlen($cEndDatum) === 0) {
            list($cTagStart, $cMonatStart, $cJahrStart) = explode('.', $cStartDatum);

            $_SESSION['Kampagne']->cFromDate_arr['nJahr']  = (int)$cJahrStart;
            $_SESSION['Kampagne']->cFromDate_arr['nMonat'] = (int)$cMonatStart;
            $_SESSION['Kampagne']->cFromDate_arr['nTag']   = (int)$cTagStart;
            $_SESSION['Kampagne']->cFromDate               = (int)$cJahrStart . '-' . (int)$cMonatStart . '-' . (int)$cTagStart;

            $_SESSION['Kampagne']->cToDate_arr['nJahr']  = (int)$cJahrStart;
            $_SESSION['Kampagne']->cToDate_arr['nMonat'] = (int)$cMonatStart;
            $_SESSION['Kampagne']->cToDate_arr['nTag']   = (int)$cTagStart;
            $_SESSION['Kampagne']->cToDate               = (int)$cJahrStart . '-' . (int)$cMonatStart . '-' . (int)$cTagStart;
        } else {
            list($cTagStart, $cMonatStart, $cJahrStart) = explode('.', $cStartDatum);
            list($cTagEnde, $cMonatEnde, $cJahrEnde)    = explode('.', $cEndDatum);

            $_SESSION['Kampagne']->cFromDate_arr['nJahr']  = (int)$cJahrStart;
            $_SESSION['Kampagne']->cFromDate_arr['nMonat'] = (int)$cMonatStart;
            $_SESSION['Kampagne']->cFromDate_arr['nTag']   = (int)$cTagStart;
            $_SESSION['Kampagne']->cFromDate               = (int)$cJahrStart . '-' . (int)$cMonatStart . '-' . (int)$cTagStart;

            $_SESSION['Kampagne']->cToDate_arr['nJahr']  = (int)$cJahrEnde;
            $_SESSION['Kampagne']->cToDate_arr['nMonat'] = (int)$cMonatEnde;
            $_SESSION['Kampagne']->cToDate_arr['nTag']   = (int)$cTagEnde;
            $_SESSION['Kampagne']->cToDate               = (int)$cJahrEnde . '-' . (int)$cMonatEnde . '-' . (int)$cTagEnde;
        }

        // Int String Work Around
        $cMonat = $_SESSION['Kampagne']->cFromDate_arr['nMonat'];
        if ($cMonat < 10) {
            $cMonat = '0' . $cMonat;
        }

        $cTag = $_SESSION['Kampagne']->cFromDate_arr['nTag'];
        if ($cTag < 10) {
            $cTag = '0' . $cTag;
        }

        switch ((int)$_SESSION['Kampagne']->nAnsicht) {
            case 1:    // Monat
                $_SESSION['Kampagne']->nDetailAnsicht = 2;
                $cStamp                               = $_SESSION['Kampagne']->cFromDate_arr['nJahr'] . '-' . $cMonat;
                break;
            case 2: // Woche
                $_SESSION['Kampagne']->nDetailAnsicht = 3;
                $cStamp                               = date('W', mktime(0, 0, 0, $_SESSION['Kampagne']->cFromDate_arr['nMonat'],
                    $_SESSION['Kampagne']->cFromDate_arr['nTag'], $_SESSION['Kampagne']->cFromDate_arr['nJahr'])
                );
                break;
            case 3: // Tag
                $_SESSION['Kampagne']->nDetailAnsicht = 4;
                $cStamp                               = $_SESSION['Kampagne']->cFromDate_arr['nJahr'] . '-' .
                    $cMonat . '-' . $cTag;
                break;
        }
    }

    return $cStamp;
}

/**
 * @param string $cMonat
 * @return string
 */
function mappeENGMonat($cMonat)
{
    $cMonatDE = '';
    if (strlen($cMonat) > 0) {
        switch ($cMonat) {
            case '01':
                $cMonatDE .= Shop::Lang()->get('january', 'news');
                break;
            case '02':
                $cMonatDE .= Shop::Lang()->get('february', 'news');
                break;
            case '03':
                $cMonatDE .= Shop::Lang()->get('march', 'news');
                break;
            case '04':
                $cMonatDE .= Shop::Lang()->get('april', 'news');
                break;
            case '05':
                $cMonatDE .= Shop::Lang()->get('may', 'news');
                break;
            case '06':
                $cMonatDE .= Shop::Lang()->get('june', 'news');
                break;
            case '07':
                $cMonatDE .= Shop::Lang()->get('july', 'news');
                break;
            case '08':
                $cMonatDE .= Shop::Lang()->get('august', 'news');
                break;
            case '09':
                $cMonatDE .= Shop::Lang()->get('september', 'news');
                break;
            case '10':
                $cMonatDE .= Shop::Lang()->get('october', 'news');
                break;
            case '11':
                $cMonatDE .= Shop::Lang()->get('november', 'news');
                break;
            case '12':
                $cMonatDE .= Shop::Lang()->get('december', 'news');
                break;
        }
    }

    return $cMonatDE;
}

/**
 * @return array
 */
function GetTypes()
{
    $Serienames = [
        1  => 'Hit',
        2  => 'Verkauf',
        3  => 'Anmeldung',
        4  => 'Verkaufssumme',
        5  => 'Frage zum Produkt',
        6  => 'Verf&uuml;gbarkeitsanfrage',
        7  => 'Login',
        8  => 'Produkt auf Wunschliste',
        9  => 'Produkt in den Warenkorb',
        10 => 'Angeschaute Newsletter'
    ];

    return $Serienames;
}

/**
 * @param int $Type
 * @return string
 */
function GetKampTypeName($Type)
{
    $Serienames = GetTypes();
    if (isset($Serienames[$Type])) {
        return $Serienames[$Type];
    }

    return '';
}

/**
 * @param array $Stats
 * @param mixed $Type
 * @return Linechart
 */
function PrepareLineChartKamp($Stats, $Type)
{
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Linechart.php';

    $chart = new Linechart(['active' => false]);

    if (is_array($Stats) && count($Stats) > 0) {
        $chart->setActive(true);
        $data = [];
        foreach ($Stats as $Date => $Dates) {
            if (strpos($Date, 'Gesamt') === false) {
                $x = '';
                foreach ($Dates as $Key => $Stat) {
                    if (strpos($Key, 'cDatum') !== false) {
                        $x = utf8_encode($Dates[$Key]);
                    }

                    if ($Key == $Type) {
                        $obj    = new stdClass();
                        $obj->y = (float) $Stat;

                        $chart->addAxis((string) $x);
                        $data[] = $obj;
                    }
                }
            }
        }
        $chart->addSerie(GetKampTypeName($Type), $data);
        $chart->memberToJSON();
    }

    return $chart;
}
