<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param string $cLimitSQL
 * @param string $cSuchFilter
 * @return array
 */
function gibBestellungsUebersicht($cLimitSQL, $cSuchFilter)
{
    $oBestellung_arr = [];
    $cSuchFilterSQL  = '';
    if (strlen($cSuchFilter)) {
        $cSuchFilterSQL = " WHERE cBestellNr LIKE '%" . Shop::DB()->escape($cSuchFilter) . "%'";
    }
    $oBestellungToday_arr = Shop::DB()->query(
        "SELECT kBestellung
            FROM tbestellung
            " . $cSuchFilterSQL . "
            ORDER BY dErstellt DESC" . $cLimitSQL, 2
    );
    if (is_array($oBestellungToday_arr) && count($oBestellungToday_arr) > 0) {
        foreach ($oBestellungToday_arr as $oBestellungToday) {
            if (isset($oBestellungToday->kBestellung) && $oBestellungToday->kBestellung > 0) {
                $oBestellung = new Bestellung($oBestellungToday->kBestellung);
                $oBestellung->fuelleBestellung(1, 0, false);
                $oBestellung_arr[] = $oBestellung;
            }
        }
    }

    return $oBestellung_arr;
}

/**
 * @param string $cSuchFilter
 * @return int
 */
function gibAnzahlBestellungen($cSuchFilter)
{
    $cSuchFilterSQL = (strlen($cSuchFilter) > 0)
        ? " WHERE cBestellNr LIKE '%" . Shop::DB()->escape($cSuchFilter) . "%'"
        : '';
    $oBestellung = Shop::DB()->query(
        "SELECT count(*) AS nAnzahl
            FROM tbestellung" . $cSuchFilterSQL, 1
    );
    if (isset($oBestellung->nAnzahl) && $oBestellung->nAnzahl > 0) {
        return (int)$oBestellung->nAnzahl;
    }

    return 0;
}

/**
 * @param array $kBestellung_arr
 * @return int
 */
function setzeAbgeholtZurueck($kBestellung_arr)
{
    if (is_array($kBestellung_arr) && count($kBestellung_arr) > 0) {
        $kBestellung_arr = array_map(function ($i) { return (int)$i; }, $kBestellung_arr);
        // Kunden cAbgeholt zurücksetzen
        $oKunde_arr = Shop::DB()->query(
            "SELECT kKunde
                FROM tbestellung
                WHERE kBestellung IN(" . implode(',', $kBestellung_arr) . ")
                    AND cAbgeholt = 'Y'", 2
        );
        if (is_array($oKunde_arr) && count($oKunde_arr) > 0) {
            $kKunde_arr = [];
            foreach ($oKunde_arr as $oKunde) {
                $oKunde->kKunde = (int)$oKunde->kKunde;
                if (!in_array($oKunde->kKunde, $kKunde_arr, true)) {
                    $kKunde_arr[] = $oKunde->kKunde;
                }
            }
            Shop::DB()->query(
                "UPDATE tkunde
                    SET cAbgeholt = 'N'
                    WHERE kKunde IN(" . implode(',', $kKunde_arr) . ")", 3
            );
        }
        // Bestellungen cAbgeholt zurücksetzen
        Shop::DB()->query(
            "UPDATE tbestellung
                SET cAbgeholt = 'N'
                WHERE kBestellung IN(" . implode(',', $kBestellung_arr) . ")
                    AND cAbgeholt = 'Y'", 3
        );

        // Zahlungsinfo cAbgeholt zurücksetzen
        Shop::DB()->query(
            "UPDATE tzahlungsinfo
                SET cAbgeholt = 'N'
                WHERE kBestellung IN(" . implode(',', $kBestellung_arr) . ")
                    AND cAbgeholt = 'Y'", 3
        );

        return -1;
    }

    return 1; // Array mit Keys nicht vorhanden oder leer
}
