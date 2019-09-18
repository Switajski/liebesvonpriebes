<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Preisradar
 */
class Preisradar
{
    /**
     * @param int $kKundengruppe
     * @param int $nLimit
     * @param int $nTage
     * @return array
     */
    public static function getProducts($kKundengruppe, $nLimit = 3, $nTage = 3)
    {
        $kKundengruppe = (int)$kKundengruppe;
        $nTage         = (int)$nTage;
        $nLimit        = (int)$nLimit;
        $oProduct_arr  = [];
        // Hole alle Produkte, die mindestens zwei mal den Preis in der angegebenden Zeit geändert haben
        $oObj_arr = Shop::DB()->query(
            "SELECT kArtikel
                FROM tpreisverlauf
                WHERE DATE_SUB(now(), INTERVAL {$nTage} DAY) < dDate
                    AND kKundengruppe = {$kKundengruppe}
                GROUP BY kArtikel
                HAVING count(*) >= 2
                ORDER BY dDate DESC
                LIMIT {$nLimit}", 2
        );
        if (is_array($oObj_arr) && count($oObj_arr) > 0) {
            $cArtikelSQL = " kArtikel IN (";
            foreach ($oObj_arr as $i => $oObj) {
                if ($i > 0) {
                    $cArtikelSQL .= ", {$oObj->kArtikel}";
                } else {
                    $cArtikelSQL .= $oObj->kArtikel;
                }
            }
            $cArtikelSQL .= ")";
            // Hole Daten von jenen Produkten, die mindestens zwei mal den Preis geändert haben
            $oObj_arr = Shop::DB()->query(
                "SELECT
                    x.*
                  FROM
                  ( /* Union, da Join in MySQL-CE kein Limit kann */
                      ( /* Artikel letzter Preis */
                          SELECT
                              *
                          FROM tpreisverlauf
                          WHERE
                              DATE_SUB(now(), INTERVAL 30 DAY) < dDate AND kKundengruppe = 1 AND {$cArtikelSQL}
                          ORDER BY dDate DESC
                          LIMIT 0,1
                      ) UNION ( /* Artikel vorletzter Preis */
                          SELECT
                              *
                          FROM tpreisverlauf
                          WHERE
                              DATE_SUB(now(), INTERVAL 30 DAY) < dDate AND kKundengruppe = 1 AND {$cArtikelSQL}
                          ORDER BY dDate DESC
                          LIMIT 1,1
                      )
                  ) as x
                  WHERE x.{$cArtikelSQL}
                  LIMIT " . (int)($nLimit * 2), 2
            );
            // Hilfs Array bauen, welches nur die letzten zwei Preisänderungen pro Artikel speichert
            // Um damit hinterher die Differenz zu ermitteln
            $xHelperAssoc_arr = [];
            foreach ($oObj_arr as $i => $oObj) {
                if (!isset($xHelperAssoc_arr[$oObj->kArtikel])) {
                    $xHelperAssoc_arr[$oObj->kArtikel] = [];
                }
                $xHelperAssoc_arr[$oObj->kArtikel][] = $oObj;
            }
            $oMaxDiff_arr = [];
            foreach ($xHelperAssoc_arr as $kArtikel => $xHelper_arr) {
                // Der neue Preis muss niedriger sein als der vorige,
                // nur dann ist das Produkt günstiger geworden und nur das wollen wir anzeigen
                if (isset($xHelper_arr[0]->fVKNetto, $xHelper_arr[1]->fVKNetto) &&
                    $xHelper_arr[0]->fVKNetto < $xHelper_arr[1]->fVKNetto
                ) {
                    $fProzentDiff           = round(
                        (
                            ($xHelper_arr[1]->fVKNetto - $xHelper_arr[0]->fVKNetto) /
                            $xHelper_arr[0]->fVKNetto
                        ) * 100,
                        1
                    );
                    $fDiff                  = $xHelper_arr[0]->fVKNetto - $xHelper_arr[1]->fVKNetto;
                    $oProduct               = new stdClass();
                    $oProduct->kArtikel     = $kArtikel;
                    $oProduct->fDiff        = $fDiff;
                    $oProduct->fProzentDiff = $fProzentDiff;
                    $oMaxDiff_arr[]         = $oProduct;
                }
            }
            // Array sortieren
            objectSort($oMaxDiff_arr, 'fProzentDiff');
            $oProduct_arr = array_reverse($oMaxDiff_arr);
        }

        return $oProduct_arr;
    }
}
