<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param Vergleichsliste $oVergleichsliste
 * @return array
 */
function baueMerkmalundVariation($oVergleichsliste)
{
    $Tmp_arr          = [];
    $oMerkmale_arr    = [];
    $oVariationen_arr = [];
    // Falls es min. einen Artikel in der Vergleichsliste gibt ...
    if (isset($oVergleichsliste->oArtikel_arr) && count($oVergleichsliste->oArtikel_arr) > 0) {
        // Alle Artikel in der Vergleichsliste durchgehen
        foreach ($oVergleichsliste->oArtikel_arr as $oArtikel) {
            // Falls ein Artikel min. ein Merkmal besitzt
            if (isset($oArtikel->oMerkmale_arr) && count($oArtikel->oMerkmale_arr) > 0) {
                // Falls das Merkmal Array nicht leer ist
                if (count($oMerkmale_arr) > 0) {
                    foreach ($oArtikel->oMerkmale_arr as $oMerkmale) {
                        if (!istMerkmalEnthalten($oMerkmale_arr, $oMerkmale->kMerkmal)) {
                            $oMerkmale_arr[] = $oMerkmale;
                        }
                    }
                } else {
                    $oMerkmale_arr = $oArtikel->oMerkmale_arr;
                }
            }
            // Falls ein Artikel min. eine Variation enthält
            if (isset($oArtikel->Variationen) && count($oArtikel->Variationen) > 0) {
                if (count($oVariationen_arr) > 0) {
                    foreach ($oArtikel->Variationen as $oVariationen) {
                        if (!istVariationEnthalten($oVariationen_arr, $oVariationen->cName)) {
                            $oVariationen_arr[] = $oVariationen;
                        }
                    }
                } else {
                    $oVariationen_arr = $oArtikel->Variationen;
                }
            }
        }
        if (count($oMerkmale_arr) > 0) {
            uasort($oMerkmale_arr, function ($a, $b) {
                return $a->nSort > $b->nSort;
            });
        }
    }

    $Tmp_arr[0] = $oMerkmale_arr;
    $Tmp_arr[1] = $oVariationen_arr;

    return $Tmp_arr;
}

/**
 * @param array $oMerkmale_arr
 * @param int   $kMerkmal
 * @return bool
 */
function istMerkmalEnthalten($oMerkmale_arr, $kMerkmal)
{
    foreach ($oMerkmale_arr as $oMerkmale) {
        if ($oMerkmale->kMerkmal == $kMerkmal) {
            return true;
        }
    }

    return false;
}

/**
 * @param array  $oVariationen_arr
 * @param string $cName
 * @return bool
 */
function istVariationEnthalten($oVariationen_arr, $cName)
{
    foreach ($oVariationen_arr as $oVariationen) {
        if ($oVariationen->cName == $cName) {
            return true;
        }
    }

    return false;
}

/**
 * @param array $cExclude
 * @param array $config
 * @return string
 */
function gibMaxPrioSpalteV($cExclude, $config)
{
    $nMax     = 0;
    $cElement = '';
    if ($config['vergleichsliste']['vergleichsliste_artikelnummer'] > $nMax && !in_array('cArtNr', $cExclude, true)) {
        $nMax     = $config['vergleichsliste']['vergleichsliste_artikelnummer'];
        $cElement = 'cArtNr';
    }
    if ($config['vergleichsliste']['vergleichsliste_hersteller'] > $nMax && !in_array('cHersteller', $cExclude, true)) {
        $nMax     = $config['vergleichsliste']['vergleichsliste_hersteller'];
        $cElement = 'cHersteller';
    }
    if ($config['vergleichsliste']['vergleichsliste_beschreibung'] > $nMax && !in_array('cBeschreibung', $cExclude, true)) {
        $nMax     = $config['vergleichsliste']['vergleichsliste_beschreibung'];
        $cElement = 'cBeschreibung';
    }
    if ($config['vergleichsliste']['vergleichsliste_kurzbeschreibung'] > $nMax && !in_array('cKurzBeschreibung', $cExclude, true)) {
        $nMax     = $config['vergleichsliste']['vergleichsliste_kurzbeschreibung'];
        $cElement = 'cKurzBeschreibung';
    }
    if ($config['vergleichsliste']['vergleichsliste_artikelgewicht'] > $nMax && !in_array('fArtikelgewicht', $cExclude, true)) {
        $nMax     = $config['vergleichsliste']['vergleichsliste_artikelgewicht'];
        $cElement = 'fArtikelgewicht';
    }
    if ($config['vergleichsliste']['vergleichsliste_versandgewicht'] > $nMax && !in_array('fGewicht', $cExclude, true)) {
        $nMax     = $config['vergleichsliste']['vergleichsliste_versandgewicht'];
        $cElement = 'fGewicht';
    }
    if ($config['vergleichsliste']['vergleichsliste_merkmale'] > $nMax && !in_array('Merkmale', $cExclude, true)) {
        $nMax     = $config['vergleichsliste']['vergleichsliste_merkmale'];
        $cElement = 'Merkmale';
    }
    if ($config['vergleichsliste']['vergleichsliste_variationen'] > $nMax && !in_array('Variationen', $cExclude, true)) {
        $cElement = 'Variationen';
    }

    return $cElement;
}

/**
 * Fügt nach jedem Preisvergleich eine Statistik in die Datenbank.
 * Es sind allerdings nur 3 Einträge pro IP und Tag möglich
 *
 * @param Vergleichsliste $oVergleichsliste
 */
function setzeVergleich($oVergleichsliste)
{
    if (isset($oVergleichsliste->oArtikel_arr) &&
        is_array($oVergleichsliste->oArtikel_arr) &&
        count($oVergleichsliste->oArtikel_arr) > 0
    ) {
        $nVergleiche = Shop::DB()->query(
            "SELECT count(kVergleichsliste) AS nVergleiche
                FROM tvergleichsliste
                WHERE cIP = '" . gibIP() . "'
                    AND dDate > DATE_SUB(now(),INTERVAL 1 DAY)", 1
        );

        if ($nVergleiche->nVergleiche < 3) {
            $oVergleichslisteTable        = new stdClass();
            $oVergleichslisteTable->cIP   = gibIP();
            $oVergleichslisteTable->dDate = date('Y-m-d H:i:s', time());

            $kVergleichsliste = Shop::DB()->insert('tvergleichsliste', $oVergleichslisteTable);
            foreach ($oVergleichsliste->oArtikel_arr as $oArtikel) {
                $oVergleichslistePosTable                   = new stdClass();
                $oVergleichslistePosTable->kVergleichsliste = $kVergleichsliste;
                $oVergleichslistePosTable->kArtikel         = $oArtikel->kArtikel;
                $oVergleichslistePosTable->cArtikelName     = $oArtikel->cName;

                Shop::DB()->insert('tvergleichslistepos', $oVergleichslistePosTable);
            }
        }
    }
}
