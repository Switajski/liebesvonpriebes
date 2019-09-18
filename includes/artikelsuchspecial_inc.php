<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @return string
 */
function gibVaterSQL()
{
    // Muss ein VaterArtikel sein!
    return ' AND tartikel.kVaterArtikel = 0';
}

/**
 * @param int $nLimit
 * @param int $kKundengruppe
 * @return array
 */
function gibTopAngebote($nLimit, $kKundengruppe = 0)
{
    $kKundengruppe = (int)$kKundengruppe;
    $nLimit        = (int)$nLimit;
    if (!$nLimit) {
        $nLimit = 20;
    }
    if (!$kKundengruppe) {
        $kKundengruppe = Kundengruppe::getDefaultGroupID();
    }
    $topArticles = Shop::DB()->query(
        "SELECT tartikel.kArtikel
            FROM tartikel
            LEFT JOIN tartikelsichtbarkeit 
                ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
            WHERE tartikelsichtbarkeit.kArtikel IS NULL
                AND tartikel.cTopArtikel = 'Y'
                " . gibVaterSQL() . "
                " . gibLagerfilter(), 2
    );

    return array_random_assoc($topArticles, min(count($topArticles), $nLimit));
}

/**
 * @param array $arr
 * @param int   $num
 * @return array
 */
function array_random_assoc($arr, $num = 1)
{
    $r    = [];
    $keys = array_keys($arr);
    shuffle($keys);
    for ($i = 0; $i < $num; ++$i) {
        $r[] = $arr[$keys[$i]];
    }

    return $r;
}

/**
 * @param int $nLimit
 * @param int $kKundengruppe
 * @return array
 */
function gibBestseller($nLimit, $kKundengruppe = 0)
{
    $kKundengruppe = (int)$kKundengruppe;
    $nLimit        = (int)$nLimit;
    if (!$nLimit) {
        $nLimit = 20;
    }
    if (!$kKundengruppe) {
        $kKundengruppe = Kundengruppe::getDefaultGroupID();
    }
    $oGlobalnEinstellung_arr = Shop::getSettings([CONF_GLOBAL]);
    $nSchwelleBestseller     = isset($oGlobalnEinstellung_arr['global']['global_bestseller_minanzahl'])
        ? (float)$oGlobalnEinstellung_arr['global']['global_bestseller_minanzahl']
        : 10;
    $bestsellers = Shop::DB()->query(
        "SELECT tartikel.kArtikel, tbestseller.fAnzahl
            FROM tbestseller, tartikel
            LEFT JOIN tartikelsichtbarkeit 
                ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
            WHERE tartikelsichtbarkeit.kArtikel IS NULL
                AND tbestseller.kArtikel = tartikel.kArtikel
                AND round(tbestseller.fAnzahl) >= " . $nSchwelleBestseller . "
                " . gibVaterSQL() . "
                " . gibLagerfilter() . "
            ORDER BY fAnzahl DESC
            LIMIT " . $nLimit, 2
    );

    return array_random_assoc($bestsellers, min(count($bestsellers), $nLimit));
}

/**
 * @param int $nLimit
 * @param int $kKundengruppe
 * @return array
 */
function gibSonderangebote($nLimit, $kKundengruppe = 0)
{
    $kKundengruppe = (int)$kKundengruppe;
    $nLimit        = (int)$nLimit;
    if (!$nLimit) {
        $nLimit = 20;
    }
    if (!$kKundengruppe) {
        $kKundengruppe = Kundengruppe::getDefaultGroupID();
    }
    $specialOffers = Shop::DB()->query(
        "SELECT tartikel.kArtikel, tsonderpreise.fNettoPreis
            FROM tartikel
            JOIN tartikelsonderpreis 
                ON tartikelsonderpreis.kArtikel = tartikel.kArtikel
            JOIN tsonderpreise 
                ON tsonderpreise.kArtikelSonderpreis = tartikelsonderpreis.kArtikelSonderpreis
            LEFT JOIN tartikelsichtbarkeit 
                ON tartikel.kArtikel=tartikelsichtbarkeit.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
            WHERE tartikelsichtbarkeit.kArtikel IS NULL
                AND tartikelsonderpreis.kArtikel = tartikel.kArtikel
                AND tsonderpreise.kKundengruppe = " . $kKundengruppe . "
                AND tartikelsonderpreis.cAktiv = 'Y'
                AND tartikelsonderpreis.dStart <= now()
                AND (tartikelsonderpreis.dEnde >= CURDATE() OR tartikelsonderpreis.dEnde = '0000-00-00')
                AND (tartikelsonderpreis.nAnzahl < tartikel.fLagerbestand OR tartikelsonderpreis.nIstAnzahl = 0)
                " . gibVaterSQL() . "
                " . gibLagerfilter(), 2
    );

    return array_random_assoc($specialOffers, min(count($specialOffers), $nLimit));
}

/**
 * @param int $nLimit
 * @param int $kKundengruppe
 * @return array
 */
function gibNeuImSortiment($nLimit, $kKundengruppe = 0)
{
    $kKundengruppe = (int)$kKundengruppe;
    $nLimit        = (int)$nLimit;
    if (!$nLimit) {
        $nLimit = 20;
    }
    if (!$kKundengruppe) {
        $kKundengruppe = Kundengruppe::getDefaultGroupID();
    }
    $config     = Shop::getSettings([CONF_BOXEN]);
    $nAlterTage = ($config['boxen']['box_neuimsortiment_alter_tage'] > 0)
        ? (int)$config['boxen']['box_neuimsortiment_alter_tage']
        : 30;
    $new = Shop::DB()->query(
        "SELECT tartikel.kArtikel
            FROM tartikel
            LEFT JOIN tartikelsichtbarkeit 
                ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
            WHERE tartikelsichtbarkeit.kArtikel IS NULL
                AND tartikel.cNeu = 'Y'
                AND dErscheinungsdatum <= now()
                AND DATE_SUB(now(), INTERVAL " . $nAlterTage . " DAY) < tartikel.dErstellt
                " . gibVaterSQL() . "
                " . gibLagerfilter(), 2
    );

    return array_random_assoc($new, min(count($new), $nLimit));
}
