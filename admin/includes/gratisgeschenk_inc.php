<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param string $cSQL
 * @return array
 */
function holeAktiveGeschenke($cSQL)
{
    $oAktiveGeschenk_arr = [];
    if (strlen($cSQL) > 0) {
        $oAktiveGeschenkTMP_arr = Shop::DB()->query(
            "SELECT kArtikel
                FROM tartikelattribut
                WHERE cName = '" . ART_ATTRIBUT_GRATISGESCHENKAB . "'
                ORDER BY CAST(cWert AS SIGNED) DESC" . $cSQL, 2
        );
    }
    if (isset($oAktiveGeschenkTMP_arr) && is_array($oAktiveGeschenkTMP_arr) && count($oAktiveGeschenkTMP_arr) > 0) {
        $articleOptions = Artikel::getDefaultOptions();
        $articleOptions->nKeinLagerbestandBeachten = 1;
        foreach ($oAktiveGeschenkTMP_arr as $oAktiveGeschenkTMP) {
            $oArtikel = new Artikel();
            $oArtikel->fuelleArtikel($oAktiveGeschenkTMP->kArtikel, $articleOptions, 0, 0, true);
            if ($oArtikel->kArtikel > 0) {
                $oAktiveGeschenk_arr[] = $oArtikel;
            }
        }
    }

    return $oAktiveGeschenk_arr;
}

/**
 * @param string $cSQL
 * @return array
 */
function holeHaeufigeGeschenke($cSQL)
{
    $oHaeufigGeschenk_arr = [];

    if (strlen($cSQL) > 0) {
        $oHaeufigGeschenkTMP_arr = Shop::DB()->query(
            "SELECT kArtikel, count(*) AS nAnzahl
                FROM twarenkorbpos
                WHERE nPosTyp = " . C_WARENKORBPOS_TYP_GRATISGESCHENK . "
                GROUP BY kArtikel
                ORDER BY nAnzahl DESC, cName" . $cSQL, 2
        );
    }

    if (isset($oHaeufigGeschenkTMP_arr) && is_array($oHaeufigGeschenkTMP_arr) && count($oHaeufigGeschenkTMP_arr) > 0) {
        $articleOptions = Artikel::getDefaultOptions();
        $articleOptions->nKeinLagerbestandBeachten = 1;
        foreach ($oHaeufigGeschenkTMP_arr as $oHaeufigGeschenkTMP) {
            $oArtikel = new Artikel();
            $oArtikel->fuelleArtikel($oHaeufigGeschenkTMP->kArtikel, $articleOptions, 0, 0, true);
            if ($oArtikel->kArtikel > 0) {
                $oArtikel->nGGAnzahl = $oHaeufigGeschenkTMP->nAnzahl;
                $oHaeufigGeschenk_arr[] = $oArtikel;
            }
        }
    }

    return $oHaeufigGeschenk_arr;
}

/**
 * @param string $cSQL
 * @return array
 */
function holeLetzten100Geschenke($cSQL)
{
    $oLetzten100Geschenk_arr = [];

    if (strlen($cSQL) > 0) {
        $oLetzten100GeschenkTMP_arr = Shop::DB()->query(
            "SELECT sub1.kArtikel, count(*) AS nAnzahl
                FROM
                    (
                        SELECT kArtikel
                        FROM twarenkorbpos
                        WHERE nPosTyp = " . C_WARENKORBPOS_TYP_GRATISGESCHENK . "
                        ORDER BY kWarenkorbPos DESC
                        LIMIT 100
                    ) AS sub1
                GROUP BY sub1.kArtikel
                ORDER BY nAnzahl DESC" . $cSQL, 2
        );
    }
    if (isset($oLetzten100GeschenkTMP_arr) &&
        is_array($oLetzten100GeschenkTMP_arr) &&
        count($oLetzten100GeschenkTMP_arr) > 0) {
        $articleOptions = Artikel::getDefaultOptions();
        $articleOptions->nKeinLagerbestandBeachten = 1;
        foreach ($oLetzten100GeschenkTMP_arr as $oLetzten100GeschenkTMP) {
            $oArtikel = new Artikel();
            $oArtikel->fuelleArtikel($oLetzten100GeschenkTMP->kArtikel, $articleOptions, 0, 0, true);
            if ($oArtikel->kArtikel > 0) {
                $oArtikel->nGGAnzahl = $oLetzten100GeschenkTMP->nAnzahl;
                $oLetzten100Geschenk_arr[] = $oArtikel;
            }
        }
    }

    return $oLetzten100Geschenk_arr;
}

/**
 * @return int
 */
function gibAnzahlAktiverGeschenke()
{
    $nAnzahlGeschenke = Shop::DB()->query(
        "SELECT count(*) AS nAnzahl
            FROM tartikelattribut
            WHERE cName = '" . ART_ATTRIBUT_GRATISGESCHENKAB . "'", 1
    );

    if (isset($nAnzahlGeschenke->nAnzahl) && $nAnzahlGeschenke->nAnzahl > 0) {
        return $nAnzahlGeschenke->nAnzahl;
    }

    return 0;
}

/**
 * @return int
 */
function gibAnzahlHaeufigGekaufteGeschenke()
{
    $nAnzahlGeschenke = Shop::DB()->query(
        "SELECT count(DISTINCT(kArtikel)) AS nAnzahl
            FROM twarenkorbpos
            WHERE nPosTyp = " . C_WARENKORBPOS_TYP_GRATISGESCHENK, 1
    );

    if (isset($nAnzahlGeschenke->nAnzahl) && $nAnzahlGeschenke->nAnzahl > 0) {
        return (int)$nAnzahlGeschenke->nAnzahl;
    }

    return 0;
}

/**
 * @return int
 */
function gibAnzahlLetzten100Geschenke()
{
    $nAnzahlGeschenke = Shop::DB()->query(
        "SELECT count(*) AS nAnzahl
            FROM
                (
                    SELECT kArtikel
                    FROM twarenkorbpos
                    WHERE nPosTyp = " . C_WARENKORBPOS_TYP_GRATISGESCHENK . "
                    ORDER BY kWarenkorbPos DESC
                    LIMIT 100
                ) AS sub1
            GROUP BY sub1.kArtikel", 1
    );

    if (isset($nAnzahlGeschenke->nAnzahl) && $nAnzahlGeschenke->nAnzahl > 0) {
        return $nAnzahlGeschenke->nAnzahl;
    }

    return 0;
}
