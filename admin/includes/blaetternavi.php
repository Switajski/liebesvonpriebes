<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * This pagination implementation is deprecated. Use the Pagination admin class instead!
 */

/**
 * @param int $nAktuelleSeite
 * @param int $nAnzahl
 * @param int $nAnzahlProSeite
 * @return stdClass
 * @deprecated since 4.05
 */
function baueBlaetterNavi($nAktuelleSeite, $nAnzahl, $nAnzahlProSeite)
{
    $nAnzahl               = (int)$nAnzahl;
    $nAnzahlProSeite       = (int)$nAnzahlProSeite;
    $oBlaetterNavi         = new stdClass();
    $oBlaetterNavi->nAktiv = 0;

    if ($nAnzahl > $nAnzahlProSeite) {
        $nBlaetterAnzahl_arr = [];

        $nSeiten     = ceil($nAnzahl / $nAnzahlProSeite);
        $nMaxAnzeige = 5; // Zeige in der Navigation nur maximal X Seiten an
        $nAnfang     = 0; // Wenn die aktuelle Seite - $nMaxAnzeige größer 0 ist, wird nAnfang gesetzt
        $nEnde       = 0; // Wenn die aktuelle Seite + $nMaxAnzeige <= $nSeitenist, wird nEnde gesetzt
        $nVon        = 0; // Diese Variablen ermitteln die aktuellen Seiten in der Navigation, die angezeigt werden sollen.
        $nBis        = 0; // Begrenzt durch $nMaxAnzeige.
        $nVoherige   = $nAktuelleSeite - 1; // Zum zurück blättern in der Navigation
        if ($nVoherige <= 0) {
            $nVoherige = 1;
        }
        $nNaechste = $nAktuelleSeite + 1; // Zum vorwärts blättern in der Navigation
        if ($nNaechste >= $nSeiten) {
            $nNaechste = $nSeiten;
        }

        if ($nSeiten > $nMaxAnzeige) {
            // Ist die aktuelle Seite nach dem abzug der Begrenzung größer oder gleich 1?
            if (($nAktuelleSeite - $nMaxAnzeige) >= 1) {
                $nAnfang = 1;
                $nVon    = ($nAktuelleSeite - $nMaxAnzeige) + 1;
            } else {
                $nAnfang = 0;
                $nVon    = 1;
            }
            // Ist die aktuelle Seite nach dem addieren der Begrenzung kleiner als die maximale Anzahl der Seiten
            if (($nAktuelleSeite + $nMaxAnzeige) < $nSeiten) {
                $nEnde = $nSeiten;
                $nBis  = ($nAktuelleSeite + $nMaxAnzeige) - 1;
            } else {
                $nEnde = 0;
                $nBis  = $nSeiten;
            }
            // Baue die Seiten für die Navigation
            for ($i = $nVon; $i <= $nBis; $i++) {
                $nBlaetterAnzahl_arr[] = $i;
            }
        } else {
            // Baue die Seiten für die Navigation
            for ($i = 1; $i <= $nSeiten; $i++) {
                $nBlaetterAnzahl_arr[] = $i;
            }
        }

        // Blaetter Objekt um später in Smarty damit zu arbeiten
        $oBlaetterNavi->nSeiten             = $nSeiten;
        $oBlaetterNavi->nVoherige           = $nVoherige;
        $oBlaetterNavi->nNaechste           = $nNaechste;
        $oBlaetterNavi->nAnfang             = $nAnfang;
        $oBlaetterNavi->nEnde               = $nEnde;
        $oBlaetterNavi->nBlaetterAnzahl_arr = $nBlaetterAnzahl_arr;
        $oBlaetterNavi->nAktiv              = 1;
        $oBlaetterNavi->nAnzahl             = $nAnzahl;
    }

    $oBlaetterNavi->nAktuelleSeite = $nAktuelleSeite;
    $oBlaetterNavi->nVon           = (($oBlaetterNavi->nAktuelleSeite - 1) * $nAnzahlProSeite) + 1;
    $oBlaetterNavi->nBis           = $oBlaetterNavi->nAktuelleSeite * $nAnzahlProSeite;
    if ($oBlaetterNavi->nBis > $nAnzahl) {
        $oBlaetterNavi->nBis = $nAnzahl;
    }

    //if($oBlaetterNavi->nBis > $nAnzahl)
    //$oBlaetterNavi->nBis -= 1;

    return $oBlaetterNavi;
}

/**
 * @param int $nAnzahl
 * @param int $nAnzahlProSeite
 * @return bool|stdClass
 * @deprecated since 4.05
 */
function baueBlaetterNaviGetterSetter($nAnzahl, $nAnzahlProSeite)
{
    $nAnzahl           = (int)$nAnzahl;
    $nAnzahlProSeite   = (int)$nAnzahlProSeite;
    $oBlaetterNaviConf = new stdClass();

    if ($nAnzahl > 0 && $nAnzahlProSeite > 0) {
        // Baue Getter
        for ($i = 1; $i <= $nAnzahl; $i++) {
            $cOffset        = 'nOffset' . $i;
            $cSQL           = 'cSQL' . $i;
            $nAktuelleSeite = 'nAktuelleSeite' . $i;
            $cLimit         = 'cLimit' . $i;

            $oBlaetterNaviConf->$cOffset        = 0;
            $oBlaetterNaviConf->$cSQL           = ' LIMIT ' . $nAnzahlProSeite;
            $oBlaetterNaviConf->$nAktuelleSeite = 1;
            $oBlaetterNaviConf->$cLimit         = 0;
            // GET || POST
            if (verifyGPCDataInteger('s' . $i) > 0) {
                $nSeite                             = verifyGPCDataInteger('s' . $i);
                $oBlaetterNaviConf->$cOffset        = (($nSeite - 1) * $nAnzahlProSeite);
                $oBlaetterNaviConf->$cSQL           = ' LIMIT ' . (($nSeite - 1) * $nAnzahlProSeite) . ", " . $nAnzahlProSeite;
                $oBlaetterNaviConf->$nAktuelleSeite = $nSeite;
                $oBlaetterNaviConf->$cLimit         = (($nSeite - 1) * $nAnzahlProSeite);
            }
        }

        return $oBlaetterNaviConf;
    }

    return false;
}
