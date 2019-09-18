<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

// Anzahl Antworten die komplett angezeigt werden, der Rest wird unter "Sonstige" zusammengefasst
define('UMFRAGE_MAXANZAHLANZEIGEN', 20);

/**
 * @deprecated since 4.06
 *
 * @param string $string
 * @return string
 */
function convertDate($string)
{
    list($dDatum, $dZeit) = explode(' ', $string);
    $exploded             = explode(':', $dZeit);
    if (count($exploded) === 2) {
        list($nStunde, $nMinute) = $exploded;
    } else {
        list($nStunde, $nMinute, $nSekunde) = $exploded;
    }
    list($nTag, $nMonat, $nJahr) = explode('.', $dDatum);

    return $nJahr . '-' . $nMonat . '-' . $nTag . ' ' . $nStunde . ':' . $nMinute . ':00';
}

/**
 * @deprecated since 4.06
 *
 * @param string $cDateTimeStr
 * @return stdClass
 */
function gibJahrMonatVonDateTime($cDateTimeStr)
{
    list($dDatum, $dUhrzeit)     = explode(' ', $cDateTimeStr);
    list($dJahr, $dMonat, $dTag) = explode('-', $dDatum);

    unset($oDatum);
    $oDatum        = new stdClass();
    $oDatum->Jahr  = $dJahr;
    $oDatum->Monat = $dMonat;
    $oDatum->Tag   = $dTag;

    return $oDatum;
}

/**
 * @param int    $kUmfrageFrage
 * @param string $cTyp
 * @param array  $cNameOption_arr
 * @param array  $cNameAntwort_arr
 * @param array  $nSortAntwort_arr
 * @param array  $nSortOption_arr
 * @param array  $kUmfrageFrageAntwort_arr
 * @param array  $kUmfrageMatrixOption_arr
 * @return stdClass
 */
function updateAntwortUndOption($kUmfrageFrage, $cTyp, $cNameOption_arr, $cNameAntwort_arr, $nSortAntwort_arr, $nSortOption_arr, $kUmfrageFrageAntwort_arr, $kUmfrageMatrixOption_arr)
{
    $oAnzahlAUndOVorhanden                   = new stdClass();
    $oAnzahlAUndOVorhanden->nAnzahlAntworten = count($kUmfrageFrageAntwort_arr);
    $oAnzahlAUndOVorhanden->nAnzahlOptionen  = count($kUmfrageMatrixOption_arr);

    if ($cTyp !== 'text_klein' & $cTyp !== 'text_gross') {
        // Vorhandene Antworten updaten
        if (is_array($kUmfrageFrageAntwort_arr) && count($kUmfrageFrageAntwort_arr) > 0) {
            foreach ($kUmfrageFrageAntwort_arr as $i => $kUmfrageFrageAntwort) {
                $_upd        = new stdClass();
                $_upd->cName = $cNameAntwort_arr[$i];
                $_upd->nSort = (int)$nSortAntwort_arr[$i];
                Shop::DB()->update('tumfragefrageantwort', 'kUmfrageFrageAntwort', (int)$kUmfrageFrageAntwort, $_upd);
            }
        }
        // Matrix
        if ($cTyp === 'matrix_single' || $cTyp === 'matrix_multi') {
            if (is_array($kUmfrageMatrixOption_arr) && count($kUmfrageMatrixOption_arr) > 0) {
                foreach ($kUmfrageMatrixOption_arr as $j => $kUmfrageMatrixOption) {
                    $_upd        = new stdClass();
                    $_upd->cName = $cNameOption_arr[$j];
                    $_upd->nSort = (int)$nSortOption_arr[$j];
                    Shop::DB()->update('tumfragematrixoption', 'kUmfrageMatrixOption', (int)$kUmfrageMatrixOption, $_upd);
                }
            }
        }
    }

    return $oAnzahlAUndOVorhanden;
}

/**
 * @param int          $kUmfrageFrage
 * @param string       $cTyp
 * @param string|array $cNameOption
 * @param string|array $cNameAntwort
 * @param array        $nSortAntwort_arr
 * @param array        $nSortOption_arr
 * @param object       $oAnzahlAUndOVorhanden
 */
function speicherAntwortZuFrage($kUmfrageFrage, $cTyp, $cNameOption, $cNameAntwort, $nSortAntwort_arr, $nSortOption_arr, $oAnzahlAUndOVorhanden)
{
    $kUmfrageFrage = (int)$kUmfrageFrage;
    switch ($cTyp) {
        case 'multiple_single':
            if (is_array($cNameAntwort) && count($cNameAntwort) > 0) {
                $count = count($cNameAntwort);
                for ($i = $oAnzahlAUndOVorhanden->nAnzahlAntworten; $i < $count; $i++) {
                    unset($oUmfrageFrageAntwort);
                    $oUmfrageFrageAntwort                = new stdClass();
                    $oUmfrageFrageAntwort->kUmfrageFrage = $kUmfrageFrage;
                    $oUmfrageFrageAntwort->cName         = $cNameAntwort[$i];
                    $oUmfrageFrageAntwort->nSort         = $nSortAntwort_arr[$i];

                    Shop::DB()->insert('tumfragefrageantwort', $oUmfrageFrageAntwort);
                }
            }
            break;
        case 'multiple_multi':
            if (is_array($cNameAntwort) && count($cNameAntwort) > 0) {
                $count = count($cNameAntwort);
                for ($i = $oAnzahlAUndOVorhanden->nAnzahlAntworten; $i < $count; $i++) {
                    unset($oUmfrageFrageAntwort);
                    $oUmfrageFrageAntwort                = new stdClass();
                    $oUmfrageFrageAntwort->kUmfrageFrage = $kUmfrageFrage;
                    $oUmfrageFrageAntwort->cName         = $cNameAntwort[$i];
                    $oUmfrageFrageAntwort->nSort         = $nSortAntwort_arr[$i];

                    Shop::DB()->insert('tumfragefrageantwort', $oUmfrageFrageAntwort);
                }
            }
            break;
        case 'select_single':
            if (is_array($cNameAntwort) && count($cNameAntwort) > 0) {
                $count = count($cNameAntwort);
                for ($i = $oAnzahlAUndOVorhanden->nAnzahlAntworten; $i < $count; $i++) {
                    unset($oUmfrageFrageAntwort);
                    $oUmfrageFrageAntwort                = new stdClass();
                    $oUmfrageFrageAntwort->kUmfrageFrage = $kUmfrageFrage;
                    $oUmfrageFrageAntwort->cName         = $cNameAntwort[$i];
                    $oUmfrageFrageAntwort->nSort         = $nSortAntwort_arr[$i];

                    Shop::DB()->insert('tumfragefrageantwort', $oUmfrageFrageAntwort);
                }
            }
            break;
        case 'select_multi':
            if (is_array($cNameAntwort) && count($cNameAntwort) > 0) {
                $count = count($cNameAntwort);
                for ($i = $oAnzahlAUndOVorhanden->nAnzahlAntworten; $i < $count; $i++) {
                    unset($oUmfrageFrageAntwort);
                    $oUmfrageFrageAntwort                = new stdClass();
                    $oUmfrageFrageAntwort->kUmfrageFrage = $kUmfrageFrage;
                    $oUmfrageFrageAntwort->cName         = $cNameAntwort[$i];
                    $oUmfrageFrageAntwort->nSort         = $nSortAntwort_arr[$i];

                    Shop::DB()->insert('tumfragefrageantwort', $oUmfrageFrageAntwort);
                }
            }
            break;
        case 'matrix_single':
            if (is_array($cNameAntwort) && is_array($cNameOption) && count($cNameAntwort) > 0 && count($cNameOption) > 0) {
                $count = count($cNameAntwort);
                for ($i = $oAnzahlAUndOVorhanden->nAnzahlAntworten; $i < $count; $i++) {
                    unset($oUmfrageFrageAntwort);
                    $oUmfrageFrageAntwort                = new stdClass();
                    $oUmfrageFrageAntwort->kUmfrageFrage = $kUmfrageFrage;
                    $oUmfrageFrageAntwort->cName         = $cNameAntwort[$i];
                    $oUmfrageFrageAntwort->nSort         = $nSortAntwort_arr[$i];

                    Shop::DB()->insert('tumfragefrageantwort', $oUmfrageFrageAntwort);
                }
                $count = count($cNameOption);
                for ($i = $oAnzahlAUndOVorhanden->nAnzahlOptionen; $i < $count; $i++) {
                    unset($matrixOpt);
                    $matrixOpt                = new stdClass();
                    $matrixOpt->kUmfrageFrage = $kUmfrageFrage;
                    $matrixOpt->cName         = $cNameOption[$i];
                    $matrixOpt->nSort         = $nSortOption_arr[$i];

                    Shop::DB()->insert('tumfragematrixoption', $matrixOpt);
                }
            }
            break;
        case 'matrix_multi':
            if (is_array($cNameAntwort) && is_array($cNameOption) && count($cNameAntwort) > 0 && count($cNameOption) > 0) {
                $count = count($cNameAntwort);
                for ($i = $oAnzahlAUndOVorhanden->nAnzahlAntworten; $i < $count; $i++) {
                    unset($oUmfrageFrageAntwort);
                    $oUmfrageFrageAntwort                = new stdClass();
                    $oUmfrageFrageAntwort->kUmfrageFrage = $kUmfrageFrage;
                    $oUmfrageFrageAntwort->cName         = $cNameAntwort[$i];
                    $oUmfrageFrageAntwort->nSort         = $nSortAntwort_arr[$i];

                    Shop::DB()->insert('tumfragefrageantwort', $oUmfrageFrageAntwort);
                }
                $count = count($cNameOption);
                for ($i = $oAnzahlAUndOVorhanden->nAnzahlOptionen; $i < $count; $i++) {
                    unset($matrixOpt);
                    $matrixOpt                = new stdClass();
                    $matrixOpt->kUmfrageFrage = $kUmfrageFrage;
                    $matrixOpt->cName         = $cNameOption[$i];
                    $matrixOpt->nSort         = $nSortOption_arr[$i];

                    Shop::DB()->insert('tumfragematrixoption', $matrixOpt);
                }
            }
            break;
    }
}

/**
 * @param int $kUmfrageFrage
 */
function loescheFrage($kUmfrageFrage)
{
    $kUmfrageFrage = (int)$kUmfrageFrage;
    if ($kUmfrageFrage > 0) {
        Shop::DB()->query(
            "DELETE tumfragefrage, tumfragedurchfuehrungantwort 
                FROM tumfragefrage
                LEFT JOIN tumfragedurchfuehrungantwort 
                    ON tumfragedurchfuehrungantwort.kUmfrageFrage = tumfragefrage.kUmfrageFrage
                WHERE tumfragefrage.kUmfrageFrage = " . $kUmfrageFrage, 3
        );
        Shop::DB()->delete('tumfragefrageantwort', 'kUmfrageFrage', $kUmfrageFrage);
        Shop::DB()->delete('tumfragematrixoption', 'kUmfrageFrage', $kUmfrageFrage);
    }
}

/**
 * @param string $cTyp
 * @param int    $kUmfrageFrage
 * @return bool
 */
function pruefeTyp($cTyp, $kUmfrageFrage)
{
    $oUmfrageFrage = Shop::DB()->select('tumfragefrage', 'kUmfrageFrage', (int)$kUmfrageFrage);
    // Wenn sich der Typ geändert hat, dann return false
    return $cTyp === $oUmfrageFrage->cTyp;
}

/**
 * @param int $kUmfrage
 * @return mixed
 */
function holeUmfrageStatistik($kUmfrage)
{
    $kUmfrage = (int)$kUmfrage;
    // Umfragen Objekt
    $oUmfrageStats = Shop::DB()->query(
        "SELECT *, DATE_FORMAT(dGueltigVon, '%d.%m.%Y %H:%i') AS dGueltigVon_de, 
            DATE_FORMAT(dGueltigBis, '%d.%m.%Y %H:%i') AS dGueltigBis_de
            FROM tumfrage
            WHERE kUmfrage = " . $kUmfrage, 1
    );
    // Wenn es eine Umfrage gibt
    if ($oUmfrageStats->kUmfrage > 0) {
        // Hole alle Fragen der Umfrage
        $oUmfrageStats->oUmfrageFrage_arr = [];
        $oUmfrageFrage_arr                = Shop::DB()->query(
            "SELECT *
                FROM tumfragefrage
                WHERE kUmfrage = " . (int)$oUmfrageStats->kUmfrage . "
                ORDER BY nSort", 2
        );
        // Mappe Fragentyp
        if (is_array($oUmfrageFrage_arr) && count($oUmfrageFrage_arr) > 0) {
            foreach ($oUmfrageFrage_arr as $i => $oUmfrageFrage) {
                $oUmfrageFrage_arr[$i]->cTypMapped = mappeFragenTyp($oUmfrageFrage->cTyp);
            }
        }
        $oUmfrageStats->oUmfrageFrage_arr = $oUmfrageFrage_arr;
        // Anzahl Durchführungen
        $oUmfrageDurchfuehrung_arr = Shop::DB()->query(
            "SELECT kUmfrageDurchfuehrung
                FROM tumfragedurchfuehrung
                WHERE kUmfrage = " . (int)$oUmfrageStats->kUmfrage, 2
        );
        $oUmfrageStats->nAnzahlDurchfuehrung = count($oUmfrageDurchfuehrung_arr);
        // Laufe alle Fragen der Umfrage durch und berechne die Statistik
        if (is_array($oUmfrageFrage_arr) && count($oUmfrageFrage_arr) > 0) {
            foreach ($oUmfrageFrage_arr as $i => $oUmfrageFrage) {
                if ($oUmfrageFrage->cTyp !== 'text_statisch_seitenwechsel' && $oUmfrageFrage->cTyp !== 'text_statisch') {
                    $oUmfrageStats->oUmfrageFrage_arr[$i]->oUmfrageFrageAntwort_arr = [];

                    // Matrix
                    if ($oUmfrageFrage->cTyp === 'matrix_single' || $oUmfrageFrage->cTyp === 'matrix_multi') {
                        $oUmfrageFrageAntwort_arr = [];
                        $matrixOpt_arr            = [];
                        $oErgebnisMatrix_arr      = [];

                        $oUmfrageFrageAntwortTMP_arr = Shop::DB()->query(
                            "SELECT cName, kUmfrageFrageAntwort
                                FROM tumfragefrageantwort
                                WHERE kUmfrageFrage = " . (int)$oUmfrageFrage->kUmfrageFrage . "
                                ORDER BY nSort", 2
                        );
                        //Hilfarray basteln für die Anzeige mit Antworten der Matrix
                        if (is_array($oUmfrageFrageAntwortTMP_arr)) {
                            foreach ($oUmfrageFrageAntwortTMP_arr as $oUmfrageFrageAntwortTMP) {
                                unset($oUmfrageFrageAntwort);
                                $oUmfrageFrageAntwort                       = new stdClass();
                                $oUmfrageFrageAntwort->cName                = $oUmfrageFrageAntwortTMP->cName;
                                $oUmfrageFrageAntwort->kUmfrageFrageAntwort = $oUmfrageFrageAntwortTMP->kUmfrageFrageAntwort;
                                $oUmfrageFrageAntwort_arr[]                 = $oUmfrageFrageAntwort;
                            }
                        }
                        $matrixOptTMP_arr = Shop::DB()->query(
                            "SELECT tumfragematrixoption.kUmfrageMatrixOption, tumfragematrixoption.cName, 
                                count(tumfragedurchfuehrungantwort.kUmfrageMatrixOption) AS nAnzahlOption
                                FROM tumfragematrixoption
                                LEFT JOIN tumfragedurchfuehrungantwort 
                                    ON tumfragedurchfuehrungantwort.kUmfrageMatrixOption = tumfragematrixoption.kUmfrageMatrixOption
                                WHERE tumfragematrixoption.kUmfrageFrage = " . (int)$oUmfrageFrage->kUmfrageFrage . "
                                GROUP BY tumfragematrixoption.kUmfrageMatrixOption
                                ORDER BY tumfragematrixoption.nSort", 2
                        );
                        //Hilfarray basteln für die Anzeige mit Optionen der Matrix
                        if (is_array($matrixOptTMP_arr)) {
                            foreach ($matrixOptTMP_arr as $matrixOptTMP) {
                                unset($matrixOpt);
                                $matrixOpt                       = new stdClass();
                                $matrixOpt->nAnzahlOption        = $matrixOptTMP->nAnzahlOption;
                                $matrixOpt->cName                = $matrixOptTMP->cName;
                                $matrixOpt->kUmfrageMatrixOption = $matrixOptTMP->kUmfrageMatrixOption;
                                $matrixOpt_arr[]                 = $matrixOpt;
                            }
                        }
                        //Leereinträge in die Matrix einfügen
                        if (is_array($matrixOpt_arr) && is_array($oUmfrageFrageAntwort_arr) > 0) {
                            foreach ($oUmfrageFrageAntwort_arr as $oUmfrageFrageAntwort) {
                                foreach ($matrixOpt_arr as $matrixOpt) {
                                    $oErgebnisEintrag                                                                                   = new stdClass();
                                    $oErgebnisEintrag->nAnzahl                                                                          = 0;
                                    $oErgebnisEintrag->nGesamtAnzahl                                                                    = $matrixOpt->nAnzahlOption;
                                    $oErgebnisEintrag->fProzent                                                                         = 0;
                                    $oErgebnisEintrag->nBold                                                                            = 0;
                                    $oErgebnisMatrix_arr[$oUmfrageFrageAntwort->kUmfrageFrageAntwort][$matrixOpt->kUmfrageMatrixOption] = $oErgebnisEintrag;
                                }
                            }
                        }
                        //der gesamten umfrage hinzufügen
                        $oUmfrageStats->oUmfrageFrage_arr[$i]->oUmfrageFrageAntwort_arr = $oUmfrageFrageAntwort_arr;
                        $oUmfrageStats->oUmfrageFrage_arr[$i]->oUmfrageMatrixOption_arr = $matrixOpt_arr;
                        //hole pro Option die Anzahl raus
                        if (is_array($matrixOpt_arr) && count($matrixOpt_arr) > 0) {
                            foreach ($matrixOpt_arr as $matrixOpt) {
                                $matrixOptAnzahlSpalte_arr = Shop::DB()->query(
                                    "SELECT count(*) AS nAnzahlOptionProAntwort, kUmfrageFrageAntwort
                                        FROM  tumfragedurchfuehrungantwort
                                        WHERE kUmfrageMatrixOption = " . (int)$matrixOpt->kUmfrageMatrixOption . "
                                            AND kUmfrageFrage = " . (int)$oUmfrageFrage->kUmfrageFrage . "
                                        GROUP BY kUmfrageFrageAntwort ", 2
                                );
                                //setze jeder Antwort den entsprechenden Matrixeintrag
                                if (is_array($matrixOptAnzahlSpalte_arr)) {
                                    foreach ($matrixOptAnzahlSpalte_arr as $matrixOptAnzahlSpalte) {
                                        $oErgebnisMatrix_arr[$matrixOptAnzahlSpalte->kUmfrageFrageAntwort][$matrixOpt->kUmfrageMatrixOption]->nAnzahl =
                                            $matrixOptAnzahlSpalte->nAnzahlOptionProAntwort;
                                        $oErgebnisMatrix_arr[$matrixOptAnzahlSpalte->kUmfrageFrageAntwort][$matrixOpt->kUmfrageMatrixOption]->fProzent =
                                            round(
                                                (
                                                    $matrixOptAnzahlSpalte->nAnzahlOptionProAntwort /
                                                    $oErgebnisMatrix_arr[$matrixOptAnzahlSpalte->kUmfrageFrageAntwort][$matrixOpt->kUmfrageMatrixOption]->nGesamtAnzahl
                                                ) * 100,
                                                1
                                            );
                                    }
                                }
                            }
                        }
                        //ermittele die maximalen Werte und setze nBold=1
                        if (is_array($matrixOpt_arr) && count($matrixOpt_arr) > 0) {
                            foreach ($matrixOpt_arr as $matrixOpt) {
                                $nMaxAntworten = 0;
                                if (is_array($oUmfrageFrageAntwort_arr)) {
                                    //max ermitteln
                                    foreach ($oUmfrageFrageAntwort_arr as $oUmfrageFrageAntwort) {
                                        if ($oErgebnisMatrix_arr[$oUmfrageFrageAntwort->kUmfrageFrageAntwort][$matrixOpt->kUmfrageMatrixOption]->nAnzahl > $nMaxAntworten) {
                                            $nMaxAntworten = $oErgebnisMatrix_arr[$oUmfrageFrageAntwort->kUmfrageFrageAntwort][$matrixOpt->kUmfrageMatrixOption]->nAnzahl;
                                        }
                                    }
                                    //bold setzen
                                    foreach ($oUmfrageFrageAntwort_arr as $oUmfrageFrageAntwort) {
                                        if ($oErgebnisMatrix_arr[$oUmfrageFrageAntwort->kUmfrageFrageAntwort][$matrixOpt->kUmfrageMatrixOption]->nAnzahl == $nMaxAntworten) {
                                            $oErgebnisMatrix_arr[$oUmfrageFrageAntwort->kUmfrageFrageAntwort][$matrixOpt->kUmfrageMatrixOption]->nBold = 1;
                                        }
                                    }
                                }
                            }
                        }
                        //Ergebnismatrix für die Frage setzen
                        $oUmfrageStats->oUmfrageFrage_arr[$i]->oErgebnisMatrix_arr = $oErgebnisMatrix_arr;
                    } else {
                        if ($oUmfrageFrage->cTyp === 'text_klein' || $oUmfrageFrage->cTyp === 'text_gross') {
                            $oUmfrageFrageAntwort_arr = Shop::DB()->query(
                                "SELECT cText AS cName, count(cText) AS nAnzahlAntwort
                                    FROM tumfragedurchfuehrungantwort
                                    WHERE kUmfrageFrage = " . (int)$oUmfrageFrage->kUmfrageFrage . "
                                        AND TRIM(cText) !=''
                                    GROUP BY cText
                                    ORDER BY nAnzahlAntwort DESC
                                    LIMIT " . UMFRAGE_MAXANZAHLANZEIGEN, 2
                            );
                            // Anzahl Antworten
                            if (is_array($oUmfrageFrageAntwort_arr) && count($oUmfrageFrageAntwort_arr) > 0) {
                                foreach ($oUmfrageFrageAntwort_arr as $j => $oUmfrageFrageAntwort) {
                                    $oUmfrageStats->oUmfrageFrage_arr[$i]->nAnzahlAntworten += $oUmfrageFrageAntwort->nAnzahlAntwort;
                                }
                            }
                            // Anzahl Sonstiger Antworten
                            $oUmfrageFrageAntwortTMP = Shop::DB()->query(
                                "SELECT SUM(b.nAnzahlAntwort) AS nAnzahlAntwort
                                     FROM
                                     (
                                        SELECT count(cText) AS nAnzahlAntwort
                                            FROM tumfragedurchfuehrungantwort
                                            WHERE kUmfrageFrage = " . (int)$oUmfrageFrage->kUmfrageFrage . "
                                            GROUP BY cText
                                            ORDER BY nAnzahlAntwort DESC
                                            LIMIT " . UMFRAGE_MAXANZAHLANZEIGEN . ", " . count($oUmfrageFrageAntwort_arr) . "
                                     ) AS b", 1
                            );
                            if (isset($oUmfrageFrageAntwortTMP->nAnzahlAntwort) && (int)$oUmfrageFrageAntwortTMP->nAnzahlAntwort > 0) {
                                $oUmfrageStats->oUmfrageFrage_arr[$i]->nAnzahlAntworten += (int)$oUmfrageFrageAntwortTMP->nAnzahlAntwort;
                                $oTMP        = new stdClass();
                                $oTMP->cName = '<a href="umfrage.php?umfrage=1&uf=' . $oUmfrageFrage->kUmfrageFrage . '&aa=' . $oUmfrageStats->oUmfrageFrage_arr[$i]->nAnzahlAntworten .
                                    '&ma=' . count($oUmfrageFrageAntwort_arr) . '&a=zeige_sonstige">Sonstige</a>';
                                $oTMP->nAnzahlAntwort = $oUmfrageFrageAntwortTMP->nAnzahlAntwort;
                                $oTMP->fProzent       = round(($oUmfrageFrageAntwortTMP->nAnzahlAntwort / $oUmfrageStats->oUmfrageFrage_arr[$i]->nAnzahlAntworten) * 100, 1);
                            }
                            $oUmfrageStats->oUmfrageFrage_arr[$i]->oUmfrageFrageAntwort_arr = [];
                            //$oUmfrageStats->oUmfrageFrage_arr[$i]->nAnzahlAntworten = count($oUmfrageFrageAntwort_arr);
                            if (is_array($oUmfrageFrageAntwort_arr) && count($oUmfrageFrageAntwort_arr) > 0) {
                                $oUmfrageStats->oUmfrageFrage_arr[$i]->oUmfrageFrageAntwort_arr = $oUmfrageFrageAntwort_arr;

                                foreach ($oUmfrageFrageAntwort_arr as $j => $oUmfrageFrageAntwort) {
                                    $oUmfrageStats->oUmfrageFrage_arr[$i]->oUmfrageFrageAntwort_arr[$j]->fProzent =
                                        round(($oUmfrageFrageAntwort->nAnzahlAntwort / $oUmfrageStats->oUmfrageFrage_arr[$i]->nAnzahlAntworten) * 100, 1);
                                }
                            }
                            // Sontiges Element (falls vorhanden) dem Antworten Array hinzufügen
                            if (isset($oUmfrageFrageAntwortTMP->nAnzahlAntwort) && (int)$oUmfrageFrageAntwortTMP->nAnzahlAntwort > 0) {
                                $oUmfrageStats->oUmfrageFrage_arr[$i]->oUmfrageFrageAntwort_arr[] = $oTMP;
                            }
                        } else {
                            $oUmfrageFrageAntwort_arr = Shop::DB()->query(
                                "SELECT tumfragefrageantwort.kUmfrageFrageAntwort, tumfragefrageantwort.cName, 
                                    count(tumfragedurchfuehrungantwort.kUmfrageFrageAntwort) AS nAnzahlAntwort
                                    FROM tumfragefrageantwort
                                    LEFT JOIN tumfragedurchfuehrungantwort 
                                        ON tumfragedurchfuehrungantwort.kUmfrageFrageAntwort = tumfragefrageantwort.kUmfrageFrageAntwort
                                    WHERE tumfragefrageantwort.kUmfrageFrage = " . (int)$oUmfrageFrage->kUmfrageFrage . "
                                    GROUP BY tumfragefrageantwort.kUmfrageFrageAntwort
                                    ORDER BY nAnzahlAntwort DESC, tumfragefrageantwort.kUmfrageFrageAntwort", 2
                            );
                            $oAnzahl = Shop::DB()->query(
                                "SELECT count(*) AS nAnzahl
                                    FROM tumfragedurchfuehrungantwort
                                    WHERE kUmfrageFrage = " . (int)$oUmfrageFrage->kUmfrageFrage . "
                                        AND kUmfrageFrageAntwort != 0", 1
                            );
                            $oUmfrageFrageAntwortFreifeld_arr = [];
                            if ($oUmfrageStats->oUmfrageFrage_arr[$i]->nFreifeld == 1) {
                                $oUmfrageFrageAntwortFreifeld_arr = Shop::DB()->query(
                                    "SELECT cText AS cName, count(cText) AS nAnzahlAntwort
                                        FROM tumfragedurchfuehrungantwort
                                        WHERE kUmfrageFrage = " . (int)$oUmfrageFrage->kUmfrageFrage . "
                                            AND kUmfrageFrageAntwort = 0
                                            AND kUmfrageMatrixOption = 0
                                            AND TRIM(cText) !=''
                                        GROUP BY cText
                                        ORDER BY nAnzahlAntwort DESC", 2
                                );
                            }
                            $oUmfrageStats->oUmfrageFrage_arr[$i]->oUmfrageFrageAntwort_arr = [];
                            $oUmfrageStats->oUmfrageFrage_arr[$i]->oUmfrageFrageAntwort_arr = array_merge($oUmfrageFrageAntwort_arr, $oUmfrageFrageAntwortFreifeld_arr);
                            $oUmfrageStats->oUmfrageFrage_arr[$i]->nAnzahlAntworten         = $oAnzahl->nAnzahl + count($oUmfrageFrageAntwortFreifeld_arr);

                            if (is_array($oUmfrageStats->oUmfrageFrage_arr[$i]->oUmfrageFrageAntwort_arr) && count($oUmfrageStats->oUmfrageFrage_arr[$i]->oUmfrageFrageAntwort_arr) > 0) {
                                foreach ($oUmfrageStats->oUmfrageFrage_arr[$i]->oUmfrageFrageAntwort_arr as $j => $oUmfrageFrageAntwort) {
                                    $oUmfrageStats->oUmfrageFrage_arr[$i]->oUmfrageFrageAntwort_arr[$j]->fProzent = 0.0;
                                    if ($oUmfrageStats->oUmfrageFrage_arr[$i]->nAnzahlAntworten > 0) {
                                        $oUmfrageStats->oUmfrageFrage_arr[$i]->oUmfrageFrageAntwort_arr[$j]->fProzent =
                                            round(($oUmfrageFrageAntwort->nAnzahlAntwort / $oUmfrageStats->oUmfrageFrage_arr[$i]->nAnzahlAntworten) * 100, 1);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $oUmfrageStats->cKundengruppe_arr = [];
        $kKundengruppe_arr                = gibKeyArrayFuerKeyString($oUmfrageStats->cKundengruppe, ';');
        foreach ($kKundengruppe_arr as $kKundengruppe) {
            if ($kKundengruppe == -1) {
                $oUmfrageStats->cKundengruppe_arr[] = 'Alle';
            } else {
                $oKundengruppe = Shop::DB()->select('tkundengruppe', 'kKundengruppe', (int)$kKundengruppe);
                if (!empty($oKundengruppe->cName)) {
                    $oUmfrageStats->cKundengruppe_arr[] = $oKundengruppe->cName;
                }
            }
        }
    }

    return $oUmfrageStats;
}

/**
 * @param int $kUmfrageFrage
 * @param int $nAnzahlAnwort
 * @param int $nMaxAntworten
 * @return stdClass
 */
function holeSonstigeTextAntworten($kUmfrageFrage, $nAnzahlAnwort, $nMaxAntworten)
{
    $oUmfrageFrage                           = new stdClass();
    $oUmfrageFrage->oUmfrageFrageAntwort_arr = [];
    if (!$kUmfrageFrage || !$nAnzahlAnwort || !$nMaxAntworten) {
        return $oUmfrageFrage;
    }
    $oUmfrageFrage = Shop::DB()->query(
        "SELECT kUmfrage, cName, cTyp
            FROM tumfragefrage
            WHERE kUmfrageFrage = " . (int)$kUmfrageFrage, 1
    );
    $oUmfrageFrageAntwort_arr = Shop::DB()->query(
        "SELECT cText AS cName, count(cText) AS nAnzahlAntwort
            FROM tumfragedurchfuehrungantwort
            WHERE kUmfrageFrage = " . (int)$kUmfrageFrage . "
            GROUP BY cText
            ORDER BY nAnzahlAntwort DESC
            LIMIT " . UMFRAGE_MAXANZAHLANZEIGEN . ", " . (int)$nMaxAntworten, 2
    );
    $oUmfrageFrage->nMaxAntworten = $nAnzahlAnwort;
    if (is_array($oUmfrageFrageAntwort_arr) && count($oUmfrageFrageAntwort_arr) > 0) {
        $oUmfrageFrage->oUmfrageFrageAntwort_arr = $oUmfrageFrageAntwort_arr;
        foreach ($oUmfrageFrage->oUmfrageFrageAntwort_arr as $i => $oUmfrageFrageAntwort) {
            $oUmfrageFrage->oUmfrageFrageAntwort_arr[$i]->nProzent = round(($oUmfrageFrageAntwort->nAnzahlAntwort / $nAnzahlAnwort) * 100, 1);
        }
    }

    return $oUmfrageFrage;
}

/**
 * @param string $cTyp
 * @return string
 */
function mappeFragenTyp($cTyp)
{
    switch ($cTyp) {
        case 'multiple_single':
            return 'Multiple Choice (Eine Antwort)';
            break;

        case 'multiple_multi':
            return 'Multiple Choice (Viele Antworten)';
            break;

        case 'select_single':
            return 'Selectbox (Eine Antwort)';
            break;

        case 'select_multi':
            return 'SelectBox (Viele Antworten)';
            break;

        case 'text_klein':
            return 'Textfeld (klein)';
            break;

        case 'text_gross':
            return 'Textfeld (groß)';
            break;

        case 'matrix_single':
            return 'Matrix (Eine Antwort pro Zeile)';
            break;

        case 'matrix_multi':
            return 'Matrix (Viele Antworten pro Zeile)';
            break;

        case 'text_statisch':
            return 'Statischer Trenntext';
            break;

        case 'text_statisch_seitenwechsel':
            return 'Statischer Trenntext + Seitenwechsel';
            break;

        default:
            return '';
    }
}
