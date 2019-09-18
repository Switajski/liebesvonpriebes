<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_INCLUDES . 'suche_inc.php';

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return stdClass
 */
function buildSearchResults($FilterSQL, $NaviFilter)
{
    // Artikelanzahl pro Seite
    $nArtikelProSeite = 20;
    $conf             = Shop::getSettings([CONF_ARTIKELUEBERSICHT]);
    if ((int)$conf['artikeluebersicht']['artikeluebersicht_artikelproseite'] > 0) {
        $nArtikelProSeite = (int)$conf['artikeluebersicht']['artikeluebersicht_artikelproseite'];
    }
    if (isset($_SESSION['ArtikelProSeite']) && $_SESSION['ArtikelProSeite'] > 0) {
        $nArtikelProSeite = (int)$_SESSION['ArtikelProSeite'];
    }
    if ($_SESSION['oErweiterteDarstellung']->nAnzahlArtikel > 0) {
        $nArtikelProSeite = (int)$_SESSION['oErweiterteDarstellung']->nAnzahlArtikel;
    }
    // $nArtikelProSeite auf max. ARTICLES_PER_PAGE_HARD_LIMIT beschränken
    $nArtikelProSeite = min($nArtikelProSeite, ARTICLES_PER_PAGE_HARD_LIMIT);
    $nLimitN          = ($NaviFilter->nSeite - 1) * $nArtikelProSeite;

    $oSuchergebnisse                    = new stdClass();
    $oSuchergebnisse->Artikel           = new ArtikelListe();
    $oSuchergebnisse->MerkmalFilter     = [];
    $oSuchergebnisse->Herstellerauswahl = [];
    $oSuchergebnisse->Tags              = [];
    $oSuchergebnisse->Bewertung         = [];
    $oSuchergebnisse->Preisspanne       = [];
    $oSuchergebnisse->Suchspecial       = [];
    $oSuchergebnisse->SuchFilter        = [];

    baueArtikelAnzahl($FilterSQL, $oSuchergebnisse, $nArtikelProSeite, $nLimitN);
    $oSuchergebnisse->Artikel->elemente = gibArtikelKeys($FilterSQL, $nArtikelProSeite, $NaviFilter, false, $oSuchergebnisse);

    return $oSuchergebnisse;
}

/**
 * @param object $FilterSQL
 * @param object $oSuchergebnisse
 * @param int    $nArtikelProSeite
 * @param int    $nLimitN
 */
function baueArtikelAnzahl($FilterSQL, &$oSuchergebnisse, $nArtikelProSeite = 20, $nLimitN = 20)
{
    $kKundengruppe = isset($_SESSION['Kundengruppe']->kKundengruppe) ? (int)$_SESSION['Kundengruppe']->kKundengruppe : null;
    if (!$kKundengruppe) {
        $oKundengruppe = Shop::DB()->query("SELECT kKundengruppe FROM tkundengruppe WHERE cStandard = 'Y'", 1);
        $kKundengruppe = (int)$oKundengruppe->kKundengruppe;
        if (!isset($_SESSION['Kundengruppe'])) {
            $_SESSION['Kundengruppe'] = new stdClass();
        }
        $_SESSION['Kundengruppe']->kKundengruppe = $oKundengruppe->kKundengruppe;
    }
    //Anzahl holen
    $oAnzahl = Shop::DB()->query(
        "SELECT count(*) AS nGesamtAnzahl
            FROM(
                SELECT tartikel.kArtikel
                FROM tartikel
                " . (isset($FilterSQL->oSuchspecialFilterSQL->cJoin) ? $FilterSQL->oSuchspecialFilterSQL->cJoin : '') . "
            " . (isset($FilterSQL->oKategorieFilterSQL->cJoin) ? $FilterSQL->oKategorieFilterSQL->cJoin : '') . "
            " . (isset($FilterSQL->oSuchFilterSQL->cJoin) ? $FilterSQL->oSuchFilterSQL->cJoin : '') . "
            " . (isset($FilterSQL->oMerkmalFilterSQL->cJoin) ? $FilterSQL->oMerkmalFilterSQL->cJoin : '') . "
            " . (isset($FilterSQL->oTagFilterSQL->cJoin) ? $FilterSQL->oTagFilterSQL->cJoin : '') . "
            " . (isset($FilterSQL->oBewertungSterneFilterSQL->cJoin) ? $FilterSQL->oBewertungSterneFilterSQL->cJoin : '') . "
            " . (isset($FilterSQL->oPreisspannenFilterSQL->cJoin) ? $FilterSQL->oPreisspannenFilterSQL->cJoin : '') . "
            LEFT JOIN tartikelsichtbarkeit ON tartikel.kArtikel=tartikelsichtbarkeit.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
            WHERE tartikelsichtbarkeit.kArtikel IS NULL
                AND tartikel.kVaterArtikel = 0
                " . gibLagerfilter() . "
                " . (isset($FilterSQL->oSuchspecialFilterSQL->cWhere) ? $FilterSQL->oSuchspecialFilterSQL->cWhere : '') . "
                " . (isset($FilterSQL->oSuchFilterSQL->cWhere) ? $FilterSQL->oSuchFilterSQL->cWhere : '') . "
                " . (isset($FilterSQL->oHerstellerFilterSQL->cWhere) ? $FilterSQL->oHerstellerFilterSQL->cWhere : '') . "
                " . (isset($FilterSQL->oKategorieFilterSQL->cWhere) ? $FilterSQL->oKategorieFilterSQL->cWhere : '') . "
                " . (isset($FilterSQL->oMerkmalFilterSQL->cWhere) ? $FilterSQL->oMerkmalFilterSQL->cWhere : '') . "
                " . (isset($FilterSQL->oTagFilterSQL->cWhere) ? $FilterSQL->oTagFilterSQL->cWhere : '') . "
                " . (isset($FilterSQL->oBewertungSterneFilterSQL->cWhere) ? $FilterSQL->oBewertungSterneFilterSQL->cWhere : '') . "
                " . (isset($FilterSQL->oPreisspannenFilterSQL->cWhere) ? $FilterSQL->oPreisspannenFilterSQL->cWhere : '') . "
            GROUP BY tartikel.kArtikel
            " . (isset($FilterSQL->oMerkmalFilterSQL->cHaving) ? $FilterSQL->oMerkmalFilterSQL->cHaving : '') . "
                ) AS tAnzahl", 1
    );
    executeHook(
        HOOK_FILTER_INC_BAUEARTIKELANZAHL, [
            'oAnzahl'          => &$oAnzahl,
            'FilterSQL'        => &$FilterSQL,
            'oSuchergebnisse'  => &$oSuchergebnisse,
            'nArtikelProSeite' => &$nArtikelProSeite,
            'nLimitN'          => &$nLimitN
        ]
    );
    $conf = Shop::getSettings([CONF_ARTIKELUEBERSICHT]);
    if (isset($GLOBALS['NaviFilter'])) {
        buildSearchResultPage(
            $oSuchergebnisse,
            $oAnzahl->nGesamtAnzahl,
            $nLimitN,
            $GLOBALS['NaviFilter']->nSeite,
            $nArtikelProSeite,
            $conf['artikeluebersicht']['artikeluebersicht_max_seitenzahl']
        );
    } else { //workaround for sitemap export
        buildSearchResultPage(
            $oSuchergebnisse,
            $oAnzahl->nGesamtAnzahl,
            $nLimitN,
            1,
            $nArtikelProSeite,
            $conf['artikeluebersicht']['artikeluebersicht_max_seitenzahl']
        );
    }
}

/**
 * @param object $oSearchResult
 * @param int    $nProductCount
 * @param int    $nLimitN
 * @param int    $nPage
 * @param int    $nProductsPerPage
 * @param int    $nSettingMaxPageCount
 */
function buildSearchResultPage(&$oSearchResult, $nProductCount, $nLimitN, $nPage, $nProductsPerPage = 25, $nSettingMaxPageCount = 25)
{
    $oSearchResult->GesamtanzahlArtikel = $nProductCount;
    $oSearchResult->ArtikelVon          = $nLimitN + 1;
    $oSearchResult->ArtikelBis          = min($nLimitN + $nProductsPerPage, $oSearchResult->GesamtanzahlArtikel);

    if (!isset($oSearchResult->Seitenzahlen)) {
        $oSearchResult->Seitenzahlen = new stdClass();
    }
    $oSearchResult->Seitenzahlen->AktuelleSeite = $nPage;
    $oSearchResult->Seitenzahlen->MaxSeiten     = ceil($oSearchResult->GesamtanzahlArtikel / $nProductsPerPage);
    $oSearchResult->Seitenzahlen->minSeite      = min((int)$oSearchResult->Seitenzahlen->AktuelleSeite - $nSettingMaxPageCount / 2, 0);
    $oSearchResult->Seitenzahlen->maxSeite      = max($oSearchResult->Seitenzahlen->MaxSeiten, $oSearchResult->Seitenzahlen->minSeite + $nSettingMaxPageCount - 1);
    if ($oSearchResult->Seitenzahlen->maxSeite > $oSearchResult->Seitenzahlen->MaxSeiten) {
        $oSearchResult->Seitenzahlen->maxSeite = $oSearchResult->Seitenzahlen->MaxSeiten;
    }
}

/**
 * @param object   $FilterSQL
 * @param int      $nArtikelProSeite
 * @param object   $NaviFilter
 * @param bool     $bExtern
 * @param stdClass $oSuchergebnisse
 * @return array
 */
function gibArtikelKeys($FilterSQL, $nArtikelProSeite, $NaviFilter, $bExtern, $oSuchergebnisse)
{
    $oArtikel_arr = [];
    $conf         = Shop::getSettings([CONF_ARTIKELUEBERSICHT, CONF_BOXEN, CONF_NAVIGATIONSFILTER, CONF_ARTIKELDETAILS]);
    //Sortierung
    $cSortSQL      = gibArtikelsortierung($NaviFilter);
    $kKundengruppe = (int)$_SESSION['Kundengruppe']->kKundengruppe;
    if (!$kKundengruppe) {
        $oKundengruppe                           = Shop::DB()->query("SELECT kKundengruppe FROM tkundengruppe WHERE cStandard = 'Y'", 1);
        $kKundengruppe                           = (int)$oKundengruppe->kKundengruppe;
        $_SESSION['Kundengruppe']->kKundengruppe = $oKundengruppe->kKundengruppe;
    }
    // Work around Preissortierung
    $oSortierungsSQL         = new stdClass();
    $oSortierungsSQL->cJoin  = '';
    $oSortierungsSQL->cOrder = $cSortSQL;
    $tsonderpreiseTable      = 'tsonderpreise';
    if (isset($FilterSQL->oSuchspecialFilterSQL->tsp)) {
        $tsonderpreiseTable = $FilterSQL->oSuchspecialFilterSQL->tsp;
    }
    if ((isset($_SESSION['Usersortierung']) && ($_SESSION['Usersortierung'] == SEARCH_SORT_PRICE_ASC ||
                $_SESSION['Usersortierung'] == SEARCH_SORT_PRICE_DESC)) &&
        ((!isset($NaviFilter->PreisspannenFilter->fVon) && !isset($NaviFilter->PreisspannenFilter->fBis))
            || (!$NaviFilter->PreisspannenFilter->fVon && !$NaviFilter->PreisspannenFilter->fBis))
    ) {
        // @TODO: Join hinzufügen
        if (!isset($NaviFilter->Suchspecial->kKey) ||
            ($NaviFilter->Suchspecial->kKey != SEARCHSPECIALS_SPECIALOFFERS && $NaviFilter->SuchspecialFilter->kKey != SEARCHSPECIALS_SPECIALOFFERS)) {
            $oSortierungsSQL->cJoin = " LEFT JOIN tartikelsonderpreis ON tartikelsonderpreis.kArtikel = tartikel.kArtikel
                                           AND tartikelsonderpreis.cAktiv='Y'
                                           AND tartikelsonderpreis.dStart <= now()
                                           AND (tartikelsonderpreis.dEnde >= CURDATE() OR tartikelsonderpreis.dEnde = '0000-00-00')
                                           AND tartikelsonderpreis.nAnzahl < tartikel.fLagerbestand
                                           LEFT JOIN tsonderpreise AS tspgak ON tartikelsonderpreis.kArtikelSonderpreis = tspgak.kArtikelSonderpreis
                                           AND tspgak.kKundengruppe = " . $kKundengruppe;
            $tsonderpreiseTable = 'tspgak';
        }

        $oSortierungsSQL->cJoin .= " LEFT JOIN tartikelkategorierabatt ON tartikelkategorierabatt.kArtikel = tartikel.kArtikel
                                      AND tartikelkategorierabatt.kKundengruppe = " . $kKundengruppe;

        $oSortierungsSQL->cOrder = "IF (" . $tsonderpreiseTable . ".fNettoPreis < tpreise.fVKNetto, " . $tsonderpreiseTable . ".fNettoPreis, 
        IF((tartikelkategorierabatt.fRabatt > 0 && tartikelkategorierabatt.fRabatt IS NOT NULL),
        tpreise.fVKNetto-((tartikelkategorierabatt.fRabatt/100)*tpreise.fVKNetto), tpreise.fVKNetto))";
        if ($_SESSION['Usersortierung'] == SEARCH_SORT_PRICE_DESC) {
            $oSortierungsSQL->cOrder = "IF (" . $tsonderpreiseTable . ".fNettoPreis < tpreise.fVKNetto, " . $tsonderpreiseTable . ".fNettoPreis, 
            IF((tartikelkategorierabatt.fRabatt > 0 && tartikelkategorierabatt.fRabatt IS NOT NULL),
            tpreise.fVKNetto-((tartikelkategorierabatt.fRabatt/100)*tpreise.fVKNetto), tpreise.fVKNetto)) DESC";
        }
    }
    if (isset($_SESSION['Usersortierung'])) {
        //avoid joining the same table twice if we already have a bestseller search special
        if ($_SESSION['Usersortierung'] == SEARCH_SORT_BESTSELLER &&
            (!isset($NaviFilter->Suchspecial->kKey) || $NaviFilter->Suchspecial->kKey != SEARCHSPECIALS_BESTSELLER) &&
            (!isset($NaviFilter->SuchspecialFilter->kKey) || $NaviFilter->SuchspecialFilter->kKey != SEARCHSPECIALS_BESTSELLER)
        ) {
            $oSortierungsSQL->cJoin = " LEFT JOIN tbestseller ON tbestseller.kArtikel = tartikel.kArtikel";
        }
        if ($_SESSION['Usersortierung'] == SEARCH_SORT_RATING) {
            $oSortierungsSQL->cJoin .= " LEFT JOIN tbewertung ON tbewertung.kArtikel = tartikel.kArtikel";
        }
    }
    //Ab diesen Artikel rausholen
    $nLimitN = ($GLOBALS['NaviFilter']->nSeite - 1) * $nArtikelProSeite;
    // 50 nach links und 50 nach rechts für Artikeldetails blättern rausholen
    $nLimitNBlaetter = $nLimitN;
    if ($nLimitNBlaetter >= 50) {
        $nLimitNBlaetter -= 50;
    } elseif ($nLimitNBlaetter < 50) {
        $nLimitNBlaetter = 0;
    }
    // Immer 100 Artikel rausholen, damit wir in den Artikeldetails auch vernünftig blättern können
    $nArtikelProSeiteBlaetter = max(100, $nArtikelProSeite + 50);
    // Kategorie / Preise Fix
    $cSQL      = '';
    $cLimitSQL = " LIMIT " . $nLimitNBlaetter . ", " . $nArtikelProSeiteBlaetter;
    if ($bExtern) {
        $cLimitSQL = " LIMIT " . $nArtikelProSeite;
    }

    if (strlen($FilterSQL->oPreisspannenFilterSQL->cJoin) === 0) {
        $cSQL .= " JOIN tpreise ON tartikel.kArtikel = tpreise.kArtikel AND tpreise.kKundengruppe = " . $kKundengruppe;
    }

    executeHook(
        HOOK_FILTER_INC_GIBARTIKELKEYS_SQL, [
            'cSQL'           => &$cSQL,
            'FilterSQL'      => &$FilterSQL,
            'NaviFilter'     => &$NaviFilter,
            'SortierungsSQL' => &$oSortierungsSQL,
            'cLimitSQL'      => &$cLimitSQL
        ]
    );
    $oArtikelKey_arr = Shop::DB()->query(
        "SELECT tartikel.kArtikel
            FROM tartikel
            " . $cSQL . "
            " . $FilterSQL->oSuchspecialFilterSQL->cJoin . "
            " . str_replace('jSuche', 'tsuchcachetreffer', $FilterSQL->oSuchFilterSQL->cJoin) . "
            " . $FilterSQL->oKategorieFilterSQL->cJoin . "
            " . $FilterSQL->oMerkmalFilterSQL->cJoin . "
            " . $FilterSQL->oTagFilterSQL->cJoin . "
            " . $FilterSQL->oBewertungSterneFilterSQL->cJoin . "
            " . $FilterSQL->oPreisspannenFilterSQL->cJoin . "
            " . $FilterSQL->oArtikelAttributFilterSQL->cJoin . "
            " . $oSortierungsSQL->cJoin . "
            LEFT JOIN tartikelsichtbarkeit ON tartikel.kArtikel=tartikelsichtbarkeit.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
            WHERE tartikelsichtbarkeit.kArtikel IS NULL
                AND tartikel.kVaterArtikel = 0
                " . gibLagerfilter() . "
                " . $FilterSQL->oSuchspecialFilterSQL->cWhere . "
                " . $FilterSQL->oSuchFilterSQL->cWhere . "
                " . $FilterSQL->oHerstellerFilterSQL->cWhere . "
                " . $FilterSQL->oKategorieFilterSQL->cWhere . "
                " . $FilterSQL->oMerkmalFilterSQL->cWhere . "
                " . $FilterSQL->oTagFilterSQL->cWhere . "
                " . $FilterSQL->oBewertungSterneFilterSQL->cWhere . "
                " . $FilterSQL->oPreisspannenFilterSQL->cWhere . "
            GROUP BY tartikel.kArtikel
            " . $FilterSQL->oMerkmalFilterSQL->cHaving . "
            ORDER BY " . $oSortierungsSQL->cOrder . ", tartikel.kArtikel
            " . $cLimitSQL, 2
    );
    array_map(function($article) { $article->kArtikel = (int)$article->kArtikel; return $article;}, $oArtikelKey_arr);
    executeHook(HOOK_FILTER_INC_GIBARTIKELKEYS, [
            'oArtikelKey_arr' => &$oArtikelKey_arr,
            'FilterSQL'       => &$FilterSQL,
            'NaviFilter'      => &$NaviFilter,
            'SortierungsSQL'  => &$oSortierungsSQL
        ]
    );
    // Artikelkeys in der Session halten, da andere Seite wie z.b. Artikel.php auf die voherige Artikelübersicht Daten aufbaut.
    $_SESSION['oArtikelUebersichtKey_arr']   = $oArtikelKey_arr;
    $_SESSION['nArtikelUebersichtVLKey_arr'] = []; // Nur Artikel die auch wirklich auf der Seite angezeigt werden

    if (is_array($oArtikelKey_arr)) {
        //wurde kein Artikel herausgeholt, aber eine Seite > 1 angegeben?
        if ((count($oArtikelKey_arr) === 0 ||
                (isset($oSuchergebnisse->Seitenzahlen->AktuelleSeite) &&
                    $oSuchergebnisse->Seitenzahlen->AktuelleSeite > $oSuchergebnisse->Seitenzahlen->MaxSeiten)) &&
            $nLimitN > 0 && !$bExtern
        ) {
            //diese Seite hat keine Artikel -> 301 ReDir auf 1. Seite
            http_response_code(301);
            header('Location: ' . gibNaviURL($NaviFilter, true, null));
            exit;
        }

        $oArtikelOptionen                        = new stdClass();
        $oArtikelOptionen->nMerkmale             = 1;
        $oArtikelOptionen->nKategorie            = 1;
        $oArtikelOptionen->nAttribute            = 1;
        $oArtikelOptionen->nArtikelAttribute     = 1;
        $oArtikelOptionen->nVariationKombiKinder = 1;
        $oArtikelOptionen->nWarenlager           = 1;
        $oArtikelOptionen->nKonfig               = 1;
        if (PRODUCT_LIST_SHOW_RATINGS === true) {
            $oArtikelOptionen->nRatings = 1;
        }
        if (isset($conf['artikeldetails']['artikel_variationspreisanzeige']) && $conf['artikeldetails']['artikel_variationspreisanzeige'] != 0) {
            $oArtikelOptionen->nVariationDetailPreis = 1;
        }

        foreach ($oArtikelKey_arr as $i => $oArtikelKey) {
            $nLaufLimitN = $i + $nLimitNBlaetter;
            if ($bExtern || ($nLaufLimitN >= $nLimitN && $nLaufLimitN < $nLimitN + $nArtikelProSeite)) {
                $oArtikel = new Artikel();
                //$oArtikelOptionen->nVariationDetailPreis = 1;
                $oArtikel->fuelleArtikel($oArtikelKey->kArtikel, $oArtikelOptionen);
                // Aktuelle Artikelmenge in die Session (Keine Vaterartikel)
                if ($oArtikel->nIstVater === 0) {
                    $_SESSION['nArtikelUebersichtVLKey_arr'][] = $oArtikel->kArtikel;
                }
                if ($oArtikel->bHasKonfig) {
                    foreach ($oArtikel->oKonfig_arr as $gruppe) {
                        /** @var Konfigitem $piece */
                        foreach ($gruppe->oItem_arr as $piece) {
                            $konfigItemArticle = $piece->getArtikel();
                            if (!empty($konfigItemArticle) && $piece->getSelektiert()) {
                                if (isset($konfigItemArticle->nMaxDeliveryDays)) {
                                    $oArtikel->nMaxDeliveryDays = max($oArtikel->nMaxDeliveryDays, $konfigItemArticle->nMaxDeliveryDays);
                                }
                                if (isset($konfigItemArticle->nMinDeliveryDays)) {
                                    $oArtikel->nMinDeliveryDays = max($oArtikel->nMinDeliveryDays, $konfigItemArticle->nMinDeliveryDays);
                                }
                                $oArtikel->cEstimatedDelivery = getDeliverytimeEstimationText($oArtikel->nMinDeliveryDays, $oArtikel->nMaxDeliveryDays);
                            }
                        }
                    }
                }
                $oArtikel_arr[] = $oArtikel;
            } else {
                if ($nLaufLimitN > $nLimitN + $nArtikelProSeite) {
                    break;
                }
                continue;
            }
        }
    }
    //Weiterleitung, falls nur 1 Artikel rausgeholt
    $bUnterkategorien = false;
    if (isset($NaviFilter->Kategorie->kKategorie) && $NaviFilter->Kategorie->kKategorie > 0) {
        $oKategorieTMP    = new Kategorie($NaviFilter->Kategorie->kKategorie);
        $bUnterkategorien = $oKategorieTMP->existierenUnterkategorien();
    }
    if ($conf['navigationsfilter']['allgemein_weiterleitung'] === 'Y' &&
        count($oArtikel_arr) === 1 &&
        (!isset($NaviFilter->nSeite) || $NaviFilter->nSeite == 1) &&
        !$bExtern &&
        (gibAnzahlFilter($NaviFilter) > 0 ||
            (isset($NaviFilter->Kategorie->kKategorie) && $NaviFilter->Kategorie->kKategorie > 0 && !$bUnterkategorien) ||
            isset($NaviFilter->EchteSuche->cSuche))
    ) {
        http_response_code(301);
        // Weiterleitung zur Artikeldetailansicht da nur ein Artikel gefunden wurde und die Einstellung gesetzt ist.
        $url = (isset($oArtikel_arr[0]->cURL) && strlen($oArtikel_arr[0]->cURL) > 0)
            ? (Shop::getURL() . '/' . $oArtikel_arr[0]->cURL)
            : (Shop::getURL() . '/index.php?a=' . $oArtikel_arr[0]->kArtikel);
        header('Location: ' . $url);
        exit;
    }

    return $oArtikel_arr;
}

/**
 * @param object $oExtendedJTLSearchResponse
 * @return array
 */
function gibArtikelKeysExtendedJTLSearch($oExtendedJTLSearchResponse)
{
    $oArtikel_arr = [];
    if (isset($oExtendedJTLSearchResponse->oSearch->oItem_arr) &&
        is_array($oExtendedJTLSearchResponse->oSearch->oItem_arr) && count($oExtendedJTLSearchResponse->oSearch->oItem_arr) > 0) {
        // Artikelkeys in der Session halten, da andere Seite wie z.b. Artikel.php auf die voherige Artikelübersicht Daten aufbaut.
        $_SESSION['oArtikelUebersichtKey_arr']   = isset($oArtikelKey_arr) ? $oArtikelKey_arr : [];
        $_SESSION['nArtikelUebersichtVLKey_arr'] = []; // Nur Artikel die auch wirklich auf der Seite angezeigt werden
        foreach ($oExtendedJTLSearchResponse->oSearch->oItem_arr as $oItem) {
            $oArtikel                                = new Artikel();
            $oArtikelOptionen                        = new stdClass();
            $oArtikelOptionen->nMerkmale             = 1;
            $oArtikelOptionen->nAttribute            = 1;
            $oArtikelOptionen->nArtikelAttribute     = 1;
            $oArtikelOptionen->nVariationKombiKinder = 1;
            //$oArtikelOptionen->nVariationDetailPreis = 1;
            $oArtikel->fuelleArtikel($oItem->nId, $oArtikelOptionen);
            if ($oArtikel->kArtikel !== null) {
                // Aktuelle Artikelmenge in die Session (Keine Vaterartikel)
                if ($oArtikel->nIstVater === 0) {
                    $_SESSION['nArtikelUebersichtVLKey_arr'][] = $oArtikel->kArtikel;
                }
                $oArtikel_arr[] = $oArtikel;
            }
        }
    }

    return $oArtikel_arr;
}

/**
 * @param object $NaviFilter
 * @return int
 */
function gibAnzahlFilter($NaviFilter)
{
    $nCount = 0;
    // Kategoriefilter
    if (isset($NaviFilter->KategorieFilter->kKategorie) && $NaviFilter->KategorieFilter->kKategorie > 0) {
        $nCount++;
    }
    // HerstellerFilter
    if (isset($NaviFilter->HerstellerFilter->kHersteller) && $NaviFilter->HerstellerFilter->kHersteller > 0) {
        $nCount++;
    }
    // MerkmalFilter
    if (isset($NaviFilter->MerkmalFilter) && $NaviFilter->MerkmalFilter && count($NaviFilter->MerkmalFilter) > 0) {
        $nCount++;
    }
    // TagFilter
    if (isset($NaviFilter->TagFilter) && $NaviFilter->TagFilter && count($NaviFilter->TagFilter) > 0) {
        $nCount++;
    }
    // SuchFilter
    if (isset($NaviFilter->SuchFilter) && $NaviFilter->SuchFilter && count($NaviFilter->SuchFilter) > 0) {
        $nCount++;
    }
    // BewertungFilter
    if (isset($NaviFilter->BewertungFilter->nSterne) && $NaviFilter->BewertungFilter->nSterne > 0) {
        $nCount++;
    }
    // PreisspannenFilter
    if (isset($NaviFilter->PreisspannenFilter->fVon)) {
        $nCount++;
    }
    // SuchspecialFilter
    if (isset($NaviFilter->SuchspecialFilter->kKey) && $NaviFilter->SuchspecialFilter->kKey > 0) {
        $nCount++;
    }

    return $nCount;
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 */
function gibHerstellerFilterOptionen($FilterSQL, $NaviFilter)
{
    $cacheID = 'filter_hfo_' . md5(
        json_encode($_SESSION['Kundengruppe']) .
        serialize($NaviFilter) .
        json_encode($FilterSQL) .
        json_encode(Shop::$kSprache)
    );
    if (($oHerstellerFilterDB_arr = Shop::Cache()->get($cacheID)) !== false) {
        return $oHerstellerFilterDB_arr;
    }
    $oHerstellerFilterDB_arr = [];
    $conf                    = Shop::getSettings([CONF_NAVIGATIONSFILTER]);
    if ($conf['navigationsfilter']['allgemein_herstellerfilter_benutzen'] !== 'N') {
        $oHerstellerFilterDB_arr = Shop::DB()->query(
            "SELECT tseo.cSeo, ssMerkmal.kHersteller, ssMerkmal.cName, ssMerkmal.nSortNr, COUNT(*) AS nAnzahl
                FROM
                (
                    SELECT thersteller.kHersteller, thersteller.cName, thersteller.nSortNr
                    FROM tartikel
                    JOIN thersteller ON tartikel.kHersteller = thersteller.kHersteller
                    " . $FilterSQL->oSuchspecialFilterSQL->cJoin . "
                " . $FilterSQL->oSuchFilterSQL->cJoin . "
                " . $FilterSQL->oMerkmalFilterSQL->cJoin . "
                " . $FilterSQL->oKategorieFilterSQL->cJoin . "
                " . $FilterSQL->oTagFilterSQL->cJoin . "
                " . $FilterSQL->oBewertungSterneFilterSQL->cJoin . "
                " . $FilterSQL->oPreisspannenFilterSQL->cJoin . "
                LEFT JOIN tartikelsichtbarkeit ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tartikel.kVaterArtikel = 0
                    " . gibLagerfilter() . "
                    " . $FilterSQL->oSuchspecialFilterSQL->cWhere . "
                    " . $FilterSQL->oSuchFilterSQL->cWhere . "
                    " . $FilterSQL->oHerstellerFilterSQL->cWhere . "
                    " . $FilterSQL->oKategorieFilterSQL->cWhere . "
                    " . $FilterSQL->oMerkmalFilterSQL->cWhere . "
                    " . $FilterSQL->oTagFilterSQL->cWhere . "
                    " . $FilterSQL->oBewertungSterneFilterSQL->cWhere . "
                    " . $FilterSQL->oPreisspannenFilterSQL->cWhere . "
                GROUP BY tartikel.kArtikel
                " . $FilterSQL->oMerkmalFilterSQL->cHaving . "
            ) AS ssMerkmal
            LEFT JOIN tseo ON tseo.kKey = ssMerkmal.kHersteller
                AND tseo.cKey = 'kHersteller'
                AND tseo.kSprache = " . Shop::$kSprache . "
            GROUP BY ssMerkmal.kHersteller
            ORDER BY ssMerkmal.cName", 2
        );
        //baue URL
        $oZusatzFilter = new stdClass();
        $count         = count($oHerstellerFilterDB_arr);
        for ($i = 0; $i < $count; $i++) {
            if (!isset($oZusatzFilter->HerstellerFilter)) {
                $oZusatzFilter->HerstellerFilter = new stdClass();
            }
            $oZusatzFilter->HerstellerFilter->kHersteller = $oHerstellerFilterDB_arr[$i]->kHersteller;
            $oZusatzFilter->HerstellerFilter->cSeo        = $oHerstellerFilterDB_arr[$i]->cSeo;
            $oHerstellerFilterDB_arr[$i]->cURL            = gibNaviURL($NaviFilter, true, $oZusatzFilter);
        }
        unset($oZusatzFilter);
    }
    $tagArray = [CACHING_GROUP_CATEGORY, CACHING_GROUP_FILTER];
    if (isset($NaviFilter->Kategorie->kKategorie)) {
        $tagArray[] = CACHING_GROUP_CATEGORY . '_' . $NaviFilter->Kategorie->kKategorie;
    }
    if (is_array($oHerstellerFilterDB_arr)) {
        foreach ($oHerstellerFilterDB_arr as $_manuf) {
            if (isset($_manuf->kHersteller)) {
                $tagArray[] = CACHING_GROUP_MANUFACTURER . '_' . $_manuf->kHersteller;
            }
        }
    }
    Shop::Cache()->set($cacheID, $oHerstellerFilterDB_arr, $tagArray);

    return $oHerstellerFilterDB_arr;
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 */
function gibKategorieFilterOptionen($FilterSQL, $NaviFilter)
{
    //build simple string from non-empty values of $FilterSQL
    $filterString = '';
    if (is_object($FilterSQL)) {
        foreach (get_object_vars($FilterSQL) as $outerKey => $outerValue) {
            if (is_object($outerValue)) {
                foreach (get_object_vars($outerValue) as $key => $val) {
                    if (!empty($val)) {
                        $filterString .= $outerKey . $key . $val;
                    }
                }
            }
        }
    }
    if (!isset($_SESSION['Kundengruppe']->kKundengruppe)) {
        $oKundengruppe                           = Shop::DB()->select('tkundengruppe', 'cStandard', 'Y');
        $kKundengruppe                           = $oKundengruppe->kKundengruppe;
        $_SESSION['Kundengruppe']->kKundengruppe = $oKundengruppe->kKundengruppe;
    } else {
        $kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;
    }
    $cacheID = 'filter_kfo_' . md5(
        $kKundengruppe .
        serialize($NaviFilter) .
        $filterString .
        Shop::$kSprache
    );
    if (($oKategorieFilterDB_arr = Shop::Cache()->get($cacheID)) !== false) {
        return $oKategorieFilterDB_arr;
    }
    $oKategorieFilterDB_arr = [];
    $conf                   = Shop::getSettings([CONF_NAVIGATIONSFILTER]);
    if ($conf['navigationsfilter']['allgemein_kategoriefilter_benutzen'] !== 'N') {
        $limit    = (CATEGORY_FILTER_ITEM_LIMIT > -1)
            ? ' LIMIT ' . CATEGORY_FILTER_ITEM_LIMIT
            : '';
        $kSprache = (int)Shop::$kSprache;
        if (!$kSprache) {
            $oSprache = gibStandardsprache(true);
            $kSprache = (int)$oSprache->kSprache;
        }
        // Kategoriefilter anzeige
        $cSQLFilterAnzeige = "JOIN tkategorieartikel ON tartikel.kArtikel = tkategorieartikel.kArtikel
                              JOIN tkategorie ON tkategorie.kKategorie = tkategorieartikel.kKategorie";

        if ($conf['navigationsfilter']['kategoriefilter_anzeigen_als'] === 'HF' && (!isset($NaviFilter->Kategorie->kKategorie) || !$NaviFilter->Kategorie->kKategorie)) {
            $kKatFilter        = (isset($NaviFilter->KategorieFilter->kKategorie) && $NaviFilter->KategorieFilter->kKategorie > 0)
                ? ''
                : "AND tkategorieartikelgesamt.kOberKategorie = 0";
            $cSQLFilterAnzeige = "JOIN (
                SELECT tkategorieartikel.kArtikel, oberkategorie.kOberKategorie, oberkategorie.kKategorie
                FROM tkategorieartikel
                INNER JOIN tkategorie ON tkategorie.kKategorie = tkategorieartikel.kKategorie
                INNER JOIN tkategorie oberkategorie ON tkategorie.lft BETWEEN oberkategorie.lft AND oberkategorie.rght
                ) tkategorieartikelgesamt ON tartikel.kArtikel = tkategorieartikelgesamt.kArtikel
                " . $kKatFilter . "
                JOIN tkategorie ON tkategorie.kKategorie = tkategorieartikelgesamt.kKategorie";
        }
        // nicht Standardsprache? Dann hole Namen nicht aus tkategorie sondern aus tkategoriesprache
        $cSQLKategorieSprache          = new stdClass();
        $cSQLKategorieSprache->cSELECT = "tkategorie.cName";
        $cSQLKategorieSprache->cJOIN   = '';
        if (!standardspracheAktiv()) {
            $cSQLKategorieSprache->cSELECT = "IF(tkategoriesprache.cName = '', tkategorie.cName, tkategoriesprache.cName) AS cName";
            $cSQLKategorieSprache->cJOIN   = "JOIN tkategoriesprache ON tkategoriesprache.kKategorie = tkategorie.kKategorie
                                                AND tkategoriesprache.kSprache = " . (int)Shop::$kSprache;
        }

        $oKategorieFilterDB_arr = Shop::DB()->query(
            "SELECT tseo.cSeo, ssMerkmal.kKategorie, ssMerkmal.cName, ssMerkmal.nSort, COUNT(*) AS nAnzahl
                FROM
                (
                    SELECT tkategorie.kKategorie, " . $cSQLKategorieSprache->cSELECT . ", tkategorie.nSort
                    FROM tartikel
                    " . $cSQLFilterAnzeige . "
                    " . $cSQLKategorieSprache->cJOIN . "
                    " . $FilterSQL->oHerstellerFilterSQL->cJoin . "
                    " . $FilterSQL->oSuchspecialFilterSQL->cJoin . "
                    " . $FilterSQL->oSuchFilterSQL->cJoin . "
                    " . $FilterSQL->oMerkmalFilterSQL->cJoin . "
                    " . $FilterSQL->oTagFilterSQL->cJoin . "
                    " . $FilterSQL->oBewertungSterneFilterSQL->cJoin . "
                    " . $FilterSQL->oPreisspannenFilterSQL->cJoin . "
                    LEFT JOIN tartikelsichtbarkeit ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                    LEFT JOIN tkategoriesichtbarkeit ON tkategoriesichtbarkeit.kKategorie = tkategorie.kKategorie
                        AND tkategoriesichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND tartikel.kVaterArtikel = 0
                        AND tkategoriesichtbarkeit.kKategorie IS NULL
                        " . gibLagerfilter() . "
                        " . $FilterSQL->oSuchspecialFilterSQL->cWhere . "
                        " . $FilterSQL->oSuchFilterSQL->cWhere . "
                        " . $FilterSQL->oHerstellerFilterSQL->cWhere . "
                        " . $FilterSQL->oKategorieFilterSQL->cWhere . "
                        " . $FilterSQL->oMerkmalFilterSQL->cWhere . "
                        " . $FilterSQL->oTagFilterSQL->cWhere . "
                        " . $FilterSQL->oBewertungSterneFilterSQL->cWhere . "
                        " . $FilterSQL->oPreisspannenFilterSQL->cWhere . "
                    GROUP BY tkategorie.kKategorie, tartikel.kArtikel
                    " . $FilterSQL->oMerkmalFilterSQL->cHaving . "
                ) AS ssMerkmal
                LEFT JOIN tseo ON tseo.kKey = ssMerkmal.kKategorie
                    AND tseo.cKey = 'kKategorie'
                    AND tseo.kSprache = " . Shop::getLanguage(). "
                GROUP BY ssMerkmal.kKategorie
                ORDER BY ssMerkmal.nSort, ssMerkmal.cName" . $limit, 2
        );
        //baue URL
        $count = is_array($oKategorieFilterDB_arr) ? count($oKategorieFilterDB_arr) : 0;
        for ($i = 0; $i < $count; ++$i) {
            // Anzeigen als KategoriePfad
            if ($conf['navigationsfilter']['kategoriefilter_anzeigen_als'] === 'KP') {
                $oKategorie                        = new Kategorie($oKategorieFilterDB_arr[$i]->kKategorie);
                $oKategorieFilterDB_arr[$i]->cName = gibKategoriepfad($oKategorie, $kKundengruppe, $kSprache);
            }
            if (!isset($oZusatzFilter)) {
                $oZusatzFilter = new stdClass();
            }
            if (!isset($oZusatzFilter->KategorieFilter)) {
                $oZusatzFilter->KategorieFilter = new stdClass();
            }
            $oZusatzFilter->KategorieFilter->kKategorie = $oKategorieFilterDB_arr[$i]->kKategorie;
            $oZusatzFilter->KategorieFilter->cSeo       = $oKategorieFilterDB_arr[$i]->cSeo;
            $oKategorieFilterDB_arr[$i]->cURL           = gibNaviURL($NaviFilter, true, $oZusatzFilter);
        }
        //neue Sortierung
        if ($conf['navigationsfilter']['kategoriefilter_anzeigen_als'] === 'KP') {
            usort($oKategorieFilterDB_arr, 'sortierKategoriepfade');
        }
    }
    $tagArray = [CACHING_GROUP_CATEGORY];
    if (isset($NaviFilter->Kategorie->kKategorie)) {
        $tagArray[] = CACHING_GROUP_CATEGORY . '_' . (int)$NaviFilter->Kategorie->kKategorie;
    } else {
        foreach ($oKategorieFilterDB_arr as $filter) {
            if (isset($filter->kKategorie)) {
                $tagArray[] = CACHING_GROUP_CATEGORY . '_' . (int)$filter->kKategorie;
            }
        }
    }
    Shop::Cache()->set($cacheID, $oKategorieFilterDB_arr, $tagArray);

    return $oKategorieFilterDB_arr;
}

/**
 * @param object $a
 * @param object $b
 * @return int
 */
function sortierKategoriepfade($a, $b)
{
    return strcmp($a->cName, $b->cName);
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 */
function gibSuchFilterOptionen($FilterSQL, $NaviFilter)
{
    if (Shop::$kSprache > 0) {
        $kSprache = (int)Shop::$kSprache;
    } else {
        $oSprache = gibStandardsprache(true);
        $kSprache = (int)$oSprache->kSprache;
    }
    $cacheID = 'sfo_' . md5(json_encode($FilterSQL)) . '_' . (isset($_SESSION['Kundengruppe']->kKundengruppe)
            ? $_SESSION['Kundengruppe']->kKundengruppe
            : '0') . '_' . $kSprache;
    if (($oSuchFilterDB_arr = Shop::Cache()->get($cacheID)) !== false) {
        return $oSuchFilterDB_arr;
    }
    $oSuchFilterDB_arr = [];
    $conf              = Shop::getSettings([CONF_NAVIGATIONSFILTER]);
    if ($conf['navigationsfilter']['suchtrefferfilter_nutzen'] !== 'N') {
        $nLimit = (isset($conf['navigationsfilter']['suchtrefferfilter_anzahl'])
            && (int)$conf['navigationsfilter']['suchtrefferfilter_anzahl'] > 0)
            ? " LIMIT " . (int)$conf['navigationsfilter']['suchtrefferfilter_anzahl']
            : '';

        $oSuchFilterDB_arr = Shop::DB()->query(
            "SELECT ssMerkmal.kSuchanfrage, ssMerkmal.cSuche, count(*) AS nAnzahl
                FROM
                (
                    SELECT tsuchanfrage.kSuchanfrage, tsuchanfrage.cSuche
                    FROM tartikel
                    JOIN tsuchcachetreffer 
                        ON tartikel.kArtikel = tsuchcachetreffer.kArtikel
                    " . $FilterSQL->oSuchFilterSQL->cJoin . "
                JOIN tsuchcache 
                    ON tsuchcache.kSuchCache = tsuchcachetreffer.kSuchCache
                JOIN tsuchanfrage 
                    ON tsuchanfrage.cSuche = tsuchcache.cSuche
                    AND tsuchanfrage.kSprache = " . Shop::$kSprache . "
                " . $FilterSQL->oHerstellerFilterSQL->cJoin . "
                " . $FilterSQL->oSuchspecialFilterSQL->cJoin . "
                " . $FilterSQL->oKategorieFilterSQL->cJoin . "
                " . $FilterSQL->oMerkmalFilterSQL->cJoin . "
                " . $FilterSQL->oTagFilterSQL->cJoin . "
                " . $FilterSQL->oBewertungSterneFilterSQL->cJoin . "
                " . $FilterSQL->oPreisspannenFilterSQL->cJoin . "
                LEFT JOIN tartikelsichtbarkeit 
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tartikel.kVaterArtikel = 0
                    " . gibLagerfilter() . "
                    " . $FilterSQL->oSuchspecialFilterSQL->cWhere . "
                    " . $FilterSQL->oHerstellerFilterSQL->cWhere . "
                    " . $FilterSQL->oKategorieFilterSQL->cWhere . "
                    " . $FilterSQL->oMerkmalFilterSQL->cWhere . "
                    " . $FilterSQL->oTagFilterSQL->cWhere . "
                    " . $FilterSQL->oSuchFilterSQL->cWhere . "
                    " . $FilterSQL->oBewertungSterneFilterSQL->cWhere . "
                    " . $FilterSQL->oPreisspannenFilterSQL->cWhere . "
                    AND tsuchanfrage.nAktiv = 1
                GROUP BY tsuchanfrage.kSuchanfrage, tartikel.kArtikel
                " . $FilterSQL->oMerkmalFilterSQL->cHaving . "
            ) AS ssMerkmal
            GROUP BY ssMerkmal.kSuchanfrage
            ORDER BY ssMerkmal.cSuche" . $nLimit, 2
        );

        $kSuchanfrage_arr = [];
        if ($NaviFilter->Suche->kSuchanfrage > 0) {
            $kSuchanfrage_arr[] = (int)$NaviFilter->Suche->kSuchanfrage;
        }
        if (is_array($NaviFilter->SuchFilter) && count($NaviFilter->SuchFilter) > 0) {
            foreach ($NaviFilter->SuchFilter as $oSuchFilter) {
                if (isset($oSuchFilter->kSuchanfrage)) {
                    $kSuchanfrage_arr[] = (int)$oSuchFilter->kSuchanfrage;
                }
            }
        }
        // Werfe bereits gesetzte Filter aus dem Ergebnis Array
        $nCount = count($oSuchFilterDB_arr);
        for ($j = 0; $j < $nCount; $j++) {
            $count = count($kSuchanfrage_arr);
            for ($i = 0; $i < $count; $i++) {
                if ($oSuchFilterDB_arr[$j]->kSuchanfrage == $kSuchanfrage_arr[$i]) {
                    unset($oSuchFilterDB_arr[$j]);
                    break;
                }
            }
        }
        if (is_array($oSuchFilterDB_arr)) {
            $oSuchFilterDB_arr = array_merge($oSuchFilterDB_arr);
        }
        //baue URL
        $count = count($oSuchFilterDB_arr);
        for ($i = 0; $i < $count; $i++) {
            if (!isset($oZusatzFilter)) {
                $oZusatzFilter = new stdClass();
            }
            if (!isset($oZusatzFilter->SuchFilter)) {
                $oZusatzFilter->SuchFilter = new stdClass();
            }
            $oZusatzFilter->SuchFilter->kSuchanfrage = (int)$oSuchFilterDB_arr[$i]->kSuchanfrage;
            $oSuchFilterDB_arr[$i]->cURL             = gibNaviURL($NaviFilter, true, $oZusatzFilter);
        }
        // Priorität berechnen
        $nPrioStep = 0;
        $nCount    = count($oSuchFilterDB_arr);
        if ($nCount > 0) {
            $nPrioStep = ($oSuchFilterDB_arr[0]->nAnzahl - $oSuchFilterDB_arr[$nCount - 1]->nAnzahl) / 9;
        }
        foreach ($oSuchFilterDB_arr as $i => $oSuchFilterDB) {
            $oSuchFilterDB_arr[$i]->Klasse = rand(1, 10);
            if (isset($oSuchFilterDB->kSuchCache) && $oSuchFilterDB->kSuchCache > 0 && $nPrioStep >= 0) {
                $oSuchFilterDB_arr[$i]->Klasse = round(($oSuchFilterDB->nAnzahl - $oSuchFilterDB_arr[$nCount - 1]->nAnzahl) / $nPrioStep) + 1;
            }
        }
    }
    $tagArray = [CACHING_GROUP_CATEGORY];
    if (isset($NaviFilter->Kategorie->kKategorie)) {
        $tagArray[] = CACHING_GROUP_CATEGORY . '_' . (int)$NaviFilter->Kategorie->kKategorie;
    }
    Shop::Cache()->set($cacheID, $oSuchFilterDB_arr, $tagArray);

    return $oSuchFilterDB_arr;
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 */
function gibBewertungSterneFilterOptionen($FilterSQL, $NaviFilter)
{
    if (isset(Shop::$kSprache)) {
        $kSprache = (int)Shop::$kSprache;
    } else {
        $oSprache = gibStandardsprache(true);
        $kSprache = (int)$oSprache->kSprache;
    }
    $cacheID = 'filter_ps_' . md5(
        serialize($NaviFilter) .
        json_encode($FilterSQL)
    ) . '_' . $kSprache . '_' . (int)$_SESSION['Kundengruppe']->kKundengruppe;
    if (($oBewertungFilter_arr = Shop::Cache()->get($cacheID)) !== false) {
        return $oBewertungFilter_arr;
    }
    $oBewertungFilter_arr = [];
    $conf                 = Shop::getSettings([CONF_NAVIGATIONSFILTER]);
    if ($conf['navigationsfilter']['bewertungsfilter_benutzen'] !== 'N') {
        $oBewertungFilterDB_arr = Shop::DB()->query(
            "SELECT ssMerkmal.nSterne, COUNT(*) AS nAnzahl
                FROM
                (
                    SELECT round(tartikelext.fDurchschnittsBewertung, 0) AS nSterne
                    FROM tartikel
                    " . $FilterSQL->oHerstellerFilterSQL->cJoin . "
                " . $FilterSQL->oSuchspecialFilterSQL->cJoin . "
                " . $FilterSQL->oSuchFilterSQL->cJoin . "
                " . $FilterSQL->oMerkmalFilterSQL->cJoin . "
                " . $FilterSQL->oKategorieFilterSQL->cJoin . "
                " . $FilterSQL->oTagFilterSQL->cJoin . "
                JOIN tartikelext ON tartikel.kArtikel = tartikelext.kArtikel
                " . $FilterSQL->oPreisspannenFilterSQL->cJoin . "
                LEFT JOIN tartikelsichtbarkeit ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tartikel.kVaterArtikel = 0
                    " . gibLagerfilter() . "
                    " . $FilterSQL->oSuchspecialFilterSQL->cWhere . "
                    " . $FilterSQL->oSuchFilterSQL->cWhere . "
                    " . $FilterSQL->oHerstellerFilterSQL->cWhere . "
                    " . $FilterSQL->oKategorieFilterSQL->cWhere . "
                    " . $FilterSQL->oMerkmalFilterSQL->cWhere . "
                    " . $FilterSQL->oTagFilterSQL->cWhere . "
                    " . $FilterSQL->oBewertungSterneFilterSQL->cWhere . "
                    " . $FilterSQL->oPreisspannenFilterSQL->cWhere . "
                GROUP BY tartikel.kArtikel
                " . $FilterSQL->oMerkmalFilterSQL->cHaving . "
            ) AS ssMerkmal
            GROUP BY ssMerkmal.nSterne
            ORDER BY ssMerkmal.nSterne DESC", 2
        );
        // Wenn Option vorhanden, dann nur Spannen anzeigen, in denen Artikel vorhanden sind
        /*
        if ($conf['navigationsfilter']['bewertungsfilter_spannen_ausblenden'] === 'Y') {
            if (is_array($oBewertungFilterDB_arr) && count($oBewertungFilterDB_arr) > 0) {
                $nSummeSterne = 0;
                foreach ($oBewertungFilterDB_arr as $oBewertungFilterDB) {
                    $nSummeSterne += $oBewertungFilterDB->nAnzahl;
                    unset($oBewertung);
                    $oBewertung          = new stdClass();
                    $oBewertung->nStern  = $oBewertungFilterDB->nSterne;
                    $oBewertung->nAnzahl = $nSummeSterne;
                    //baue URL
                    if (!isset($oZusatzFilter)) {
                        $oZusatzFilter                  = new stdClass();
                        $oZusatzFilter->BewertungFilter = new stdClass();
                    }
                    $oZusatzFilter->BewertungFilter->nSterne = $oBewertung->nStern;
                    $oBewertung->cURL                        = gibNaviURL($NaviFilter, true, $oZusatzFilter);
                    $oBewertungFilter_arr[]                  = $oBewertung;
                }
            }
        } else {
            for ($i = 5; $i >= 1; $i--) {
                unset($oBewertung);
                $oBewertung          = new stdClass();
                $oBewertung->nStern  = $i;
                $oBewertung->nAnzahl = 0;

                if (is_array($oBewertungFilterDB_arr) && count($oBewertungFilterDB_arr) > 0) {
                    foreach ($oBewertungFilterDB_arr as $oBewertungFilterDB) {
                        if ($oBewertungFilterDB->nSterne == $i) {
                            $oBewertung->nAnzahl = $oBewertungFilterDB->nAnzahl;
                            break;
                        }
                    }
                }
                //baue URL
                if (!isset($oZusatzFilter)) {
                    $oZusatzFilter = new stdClass();
                }
                if (!isset($oZusatzFilter->BewertungFilter)) {
                    $oZusatzFilter->BewertungFilter = new stdClass();
                }
                $oZusatzFilter->BewertungFilter->nSterne = $oBewertung->nStern;
                $oBewertung->cURL                        = gibNaviURL($NaviFilter, true, $oZusatzFilter);
                $oBewertungFilter_arr[]                  = $oBewertung;
            }
        }
        */
        if (is_array($oBewertungFilterDB_arr) && count($oBewertungFilterDB_arr) > 0) {
            $nSummeSterne = 0;
            foreach ($oBewertungFilterDB_arr as $oBewertungFilterDB) {
                $nSummeSterne += $oBewertungFilterDB->nAnzahl;
                $oBewertung          = new stdClass();
                $oBewertung->nStern  = $oBewertungFilterDB->nSterne;
                $oBewertung->nAnzahl = $nSummeSterne;
                //baue URL
                if (!isset($oZusatzFilter)) {
                    $oZusatzFilter                  = new stdClass();
                    $oZusatzFilter->BewertungFilter = new stdClass();
                }
                $oZusatzFilter->BewertungFilter->nSterne = $oBewertung->nStern;
                $oBewertung->cURL                        = gibNaviURL($NaviFilter, true, $oZusatzFilter);
                $oBewertungFilter_arr[]                  = $oBewertung;
            }
        }
    }
    //@todo: bewertungen have to flush the cache
    $tagArray = [CACHING_GROUP_CATEGORY, CACHING_GROUP_FILTER];
    if (isset($NaviFilter->Kategorie->kKategorie)) {
        $tagArray[] = CACHING_GROUP_CATEGORY . '_' . (int)$NaviFilter->Kategorie->kKategorie;
    }
    Shop::Cache()->set($cacheID, $oBewertungFilter_arr, $tagArray);

    return $oBewertungFilter_arr;
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @param object $oSuchergebnisse
 * @return array|mixed
 */
function gibPreisspannenFilterOptionen($FilterSQL, $NaviFilter, $oSuchergebnisse)
{
    $conf = Shop::getSettings([CONF_NAVIGATIONSFILTER]);
    if ($conf['navigationsfilter']['preisspannenfilter_benutzen'] === 'N' || !$_SESSION['Kundengruppe']->darfPreiseSehen) {
        return [];
    }
    if (isset(Shop::$kSprache)) {
        $kSprache = (int)Shop::$kSprache;
    } else {
        $oSprache = gibStandardsprache(true);
        $kSprache = (int)$oSprache->kSprache;
    }
    $waehrung = null;
    if (isset($_SESSION['Waehrung'])) {
        $waehrung = $_SESSION['Waehrung'];
    }
    if (!isset($waehrung->kWaehrung)) {
        $waehrung = Shop::DB()->select('twaehrung', 'cStandard', 'Y');
    }
    $cacheID = 'filter_ps_' . md5(
        json_encode($_SESSION['Kundengruppe']->kKundengruppe) .
        (isset($NaviFilter->PreisspannenFilter->fVon) ? json_encode($NaviFilter->PreisspannenFilter->fVon) : '') .
        (isset($NaviFilter->PreisspannenFilter->fBis) ? json_encode($NaviFilter->PreisspannenFilter->fBis) : '') .
        json_encode($FilterSQL) .
        $oSuchergebnisse->GesamtanzahlArtikel .
        json_encode($_SESSION['Steuersatz'])
    ) . '_' . (int)$waehrung->kWaehrung . '_' . $kSprache;
    if (($oPreisspanne_arr = Shop::Cache()->get($cacheID)) !== false) {
        return $oPreisspanne_arr;
    }
    $oPreisspanne_arr = [];

    // Prüfe ob es nur einen Artikel in der Artikelübersicht gibt, falls ja und es ist noch kein Preisspannenfilter gesetzt
    // dürfen keine Preisspannenfilter angezeigt werden
    if ($oSuchergebnisse->GesamtanzahlArtikel == 1 && !isset($NaviFilter->PreisspannenFilter->fVon) && !isset($NaviFilter->PreisspannenFilter->fBis)) {
        return $oPreisspanne_arr;
    }

    $cPreisspannenJOIN = "LEFT JOIN tartikelkategorierabatt ON tartikelkategorierabatt.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                                AND tartikelkategorierabatt.kArtikel = tartikel.kArtikel
                            LEFT JOIN tartikelsonderpreis ON tartikelsonderpreis.kArtikel = tartikel.kArtikel
                                AND tartikelsonderpreis.cAktiv='Y'
                                AND tartikelsonderpreis.dStart <= now()
                                AND (tartikelsonderpreis.dEnde >= CURDATE() OR tartikelsonderpreis.dEnde = '0000-00-00')
                            LEFT JOIN tsonderpreise ON tartikelsonderpreis.kArtikelSonderpreis = tsonderpreise.kArtikelSonderpreis
                                AND tsonderpreise.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe;

    // Automatisch
    if ($conf['navigationsfilter']['preisspannenfilter_anzeige_berechnung'] === 'A') {
        // Finde den höchsten und kleinsten Steuersatz
        if (is_array($_SESSION['Steuersatz']) && (int)$_SESSION['Kundengruppe']->nNettoPreise === 0) {
            $fSteuersatz_arr = [];
            foreach ($_SESSION['Steuersatz'] as $fSteuersatz) {
                $fSteuersatz_arr[] = $fSteuersatz;
            }
            $fSteuersatzMax = count($fSteuersatz_arr) ? max($fSteuersatz_arr) : 0;
            $fSteuersatzMin = count($fSteuersatz_arr) ? min($fSteuersatz_arr) : 0;
        } elseif ((int)$_SESSION['Kundengruppe']->nNettoPreise > 0) {
            $fSteuersatzMax = 0.0;
            $fSteuersatzMin = 0.0;
        }
        $fKundenrabatt = 0.0;
        if (isset($_SESSION['Kunde']->fRabatt) && $_SESSION['Kunde']->fRabatt > 0) {
            $fKundenrabatt = $_SESSION['Kunde']->fRabatt;
        }
        $oPreisspannenFilterMaxMin = Shop::DB()->query(
            "SELECT max(ssMerkmal.fMax) AS fMax, min(ssMerkmal.fMin) AS fMin
                FROM (
                    SELECT ROUND(
                        LEAST(
                            (tpreise.fVKNetto * " . $_SESSION['Waehrung']->fFaktor . ") *
                            ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), " . $_SESSION['Kundengruppe']->fRabatt . ", " . $fKundenrabatt . ", 0)) / 100),
                            IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * " . $_SESSION['Waehrung']->fFaktor . "))) * ((100 + " . $fSteuersatzMax . ") / 100), 2) AS fMax,
                 ROUND(LEAST((tpreise.fVKNetto * " . $_SESSION['Waehrung']->fFaktor . ") *
                 ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), " . $_SESSION['Kundengruppe']->fRabatt . ", " . $fKundenrabatt . ", 0)) / 100),
                 IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * " . $_SESSION['Waehrung']->fFaktor . "))) * ((100 + " . $fSteuersatzMin . ") / 100), 2) AS fMin
                FROM tartikel
                JOIN tpreise ON tpreise.kArtikel = tartikel.kArtikel
                    AND tpreise.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                " . $FilterSQL->oHerstellerFilterSQL->cJoin . "

                " . $FilterSQL->oSuchspecialFilterSQL->cJoin . "
                " . $FilterSQL->oSuchFilterSQL->cJoin . "
                " . $FilterSQL->oKategorieFilterSQL->cJoin . "
                " . $FilterSQL->oMerkmalFilterSQL->cJoin . "
                " . $FilterSQL->oTagFilterSQL->cJoin . "
                " . $FilterSQL->oBewertungSterneFilterSQL->cJoin . "

                " . $cPreisspannenJOIN . "

                LEFT JOIN tartikelsichtbarkeit ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tartikel.kVaterArtikel = 0
                    " . gibLagerfilter() . "
                    " . $FilterSQL->oSuchspecialFilterSQL->cWhere . "
                    " . $FilterSQL->oSuchFilterSQL->cWhere . "
                    " . $FilterSQL->oHerstellerFilterSQL->cWhere . "
                    " . $FilterSQL->oKategorieFilterSQL->cWhere . "
                    " . $FilterSQL->oMerkmalFilterSQL->cWhere . "
                    " . $FilterSQL->oTagFilterSQL->cWhere . "
                    " . $FilterSQL->oBewertungSterneFilterSQL->cWhere . "
                    " . $FilterSQL->oPreisspannenFilterSQL->cWhere . "
                GROUP BY tartikel.kArtikel
                " . $FilterSQL->oMerkmalFilterSQL->cHaving . "
            ) AS ssMerkmal
            ", 1
        );
        if (isset($oPreisspannenFilterMaxMin->fMax) && isset($oPreisspannenFilterMaxMin->fMin) && $oPreisspannenFilterMaxMin->fMax == $oPreisspannenFilterMaxMin->fMin) {
            $res = [];
            Shop::Cache()->set($cacheID, $res, [CACHING_GROUP_CATEGORY, CACHING_GROUP_FILTER]);

            return $res;
        }
        if (isset($oPreisspannenFilterMaxMin->fMax) && $oPreisspannenFilterMaxMin->fMax > 0) {
            // Berechnet Max, Min, Step, Anzahl, Diff und liefert diese Werte in einem Objekt
            $oPreis = berechneMaxMinStep($oPreisspannenFilterMaxMin->fMax * $_SESSION['Waehrung']->fFaktor, $oPreisspannenFilterMaxMin->fMin * $_SESSION['Waehrung']->fFaktor);
            if (!$oPreis->nAnzahlSpannen || !$oPreis->fMaxPreis) {
                $res = [];
                Shop::Cache()->set($cacheID, $res, [CACHING_GROUP_CATEGORY, CACHING_GROUP_FILTER]);

                return $res;
            }
            // Begrenzung der Preisspannen bei zu großen Preisdifferenzen
            if ($oPreis->nAnzahlSpannen > 20) {
                $oPreis->nAnzahlSpannen = 20;
            }
            $cSelectSQL = '';
            for ($i = 0; $i < $oPreis->nAnzahlSpannen; $i++) {
                if ($i > 0) {
                    $cSelectSQL .= ', ';
                }
                $cSelectSQL .= " SUM(ssMerkmal.anz" . $i . ") AS anz" . $i;
            }
            $oPreisspannenFilterDB = Shop::DB()->query(
                "SELECT " . $cSelectSQL . "
                    FROM
                    (
                        SELECT " . berechnePreisspannenSQL($oPreis) . "
                        FROM tartikel
                        JOIN tpreise ON tpreise.kArtikel = tartikel.kArtikel
                            AND tpreise.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                        " . $FilterSQL->oHerstellerFilterSQL->cJoin . "
                        " . $FilterSQL->oSuchspecialFilterSQL->cJoin . "
                        " . $FilterSQL->oSuchFilterSQL->cJoin . "
                        " . $FilterSQL->oKategorieFilterSQL->cJoin . "
                        " . $FilterSQL->oMerkmalFilterSQL->cJoin . "
                        " . $FilterSQL->oTagFilterSQL->cJoin . "
                        " . $FilterSQL->oBewertungSterneFilterSQL->cJoin . "
                        LEFT JOIN tartikelsichtbarkeit ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "

                        " . $cPreisspannenJOIN . "

                        WHERE tartikelsichtbarkeit.kArtikel IS NULL
                            AND tartikel.kVaterArtikel = 0
                            " . gibLagerfilter() . "
                            " . $FilterSQL->oSuchspecialFilterSQL->cWhere . "
                            " . $FilterSQL->oSuchFilterSQL->cWhere . "
                            " . $FilterSQL->oHerstellerFilterSQL->cWhere . "
                            " . $FilterSQL->oKategorieFilterSQL->cWhere . "
                            " . $FilterSQL->oMerkmalFilterSQL->cWhere . "
                            " . $FilterSQL->oTagFilterSQL->cWhere . "
                            " . $FilterSQL->oBewertungSterneFilterSQL->cWhere . "
                            " . $FilterSQL->oPreisspannenFilterSQL->cWhere . "
                        GROUP BY tartikel.kArtikel
                        " . $FilterSQL->oMerkmalFilterSQL->cHaving . "
                    ) AS ssMerkmal
                    ", 1
            );

            $nPreisspannenAnzahl_arr   = is_bool($oPreisspannenFilterDB) ? null : get_object_vars($oPreisspannenFilterDB);
            $oPreisspannenFilterDB_arr = [];
            for ($i = 0; $i < $oPreis->nAnzahlSpannen; $i++) {
                if ($i === 0) {
                    $oPreisspannenFilterDB_arr[] = ($nPreisspannenAnzahl_arr['anz' . $i] - 0);
                } else {
                    $oPreisspannenFilterDB_arr[] = ($nPreisspannenAnzahl_arr['anz' . $i] - $nPreisspannenAnzahl_arr['anz' . ($i - 1)]);
                }
            }
            $nPreisMax      = $oPreis->fMaxPreis;
            $nPreisMin      = $oPreis->fMinPreis;
            $nStep          = $oPreis->fStep;
            $nAnzahlSpannen = $oPreis->nAnzahlSpannen;
            for ($i = 0; $i < $nAnzahlSpannen; $i++) {
                $oPreisspannenFilter       = new stdClass();
                $oPreisspannenFilter->nVon = ($nPreisMin + $i * $nStep);
                $oPreisspannenFilter->nBis = ($nPreisMin + ($i + 1) * $nStep);
                if ($oPreisspannenFilter->nBis > $nPreisMax) {
                    if ($oPreisspannenFilter->nVon >= $nPreisMax) {
                        $oPreisspannenFilter->nVon = ($nPreisMin + ($i - 1) * $nStep);
                    }

                    if ($oPreisspannenFilter->nBis > $nPreisMax) {
                        $oPreisspannenFilter->nBis = $nPreisMax;
                    }
                }
                // Localize Preise
                $oPreisspannenFilter->cVonLocalized  = gibPreisLocalizedOhneFaktor($oPreisspannenFilter->nVon, $waehrung);
                $oPreisspannenFilter->cBisLocalized  = gibPreisLocalizedOhneFaktor($oPreisspannenFilter->nBis, $waehrung);
                $oPreisspannenFilter->nAnzahlArtikel = $oPreisspannenFilterDB_arr[$i];
                //baue URL
                if (!isset($oZusatzFilter)) {
                    $oZusatzFilter = new stdClass();
                }
                if (!isset($oZusatzFilter->PreisspannenFilter)) {
                    $oZusatzFilter->PreisspannenFilter = new stdClass();
                }
                $oZusatzFilter->PreisspannenFilter->fVon = $oPreisspannenFilter->nVon;
                $oZusatzFilter->PreisspannenFilter->fBis = $oPreisspannenFilter->nBis;
                $oPreisspannenFilter->cURL               = gibNaviURL($NaviFilter, true, $oZusatzFilter);
                $oPreisspanne_arr[]                      = $oPreisspannenFilter;
            }
        }
    } else {
        $oPreisspannenfilter_arr = Shop::DB()->query("SELECT * FROM tpreisspannenfilter", 2);
        if (is_array($oPreisspannenfilter_arr) && count($oPreisspannenfilter_arr) > 0) {
            // Berechnet Max, Min, Step, Anzahl, Diff
            $oPreis = berechneMaxMinStep(
                $oPreisspannenfilter_arr[count($oPreisspannenfilter_arr) - 1]->nBis * $_SESSION['Waehrung']->fFaktor,
                $oPreisspannenfilter_arr[0]->nVon * $_SESSION['Waehrung']->fFaktor
            );
            if (!$oPreis->nAnzahlSpannen || !$oPreis->fMaxPreis) {
                $res = [];
                Shop::Cache()->set($cacheID, $res, [CACHING_GROUP_CATEGORY, CACHING_GROUP_FILTER]);

                return $res;
            }
            $cSelectSQL = '';
            $count      = count($oPreisspannenfilter_arr);
            for ($i = 0; $i < $count; $i++) {
                if ($i > 0) {
                    $cSelectSQL .= ', ';
                }
                $cSelectSQL .= "SUM(ssMerkmal.anz" . $i . ") AS anz" . $i;
            }

            $oPreisspannenFilterDB = Shop::DB()->query(
                "SELECT " . $cSelectSQL . "
                    FROM
                    (
                        SELECT " . berechnePreisspannenSQL($oPreis, $oPreisspannenfilter_arr) . "
                        FROM tartikel
                        JOIN tpreise ON tpreise.kArtikel = tartikel.kArtikel
                            AND tpreise.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                        " . $FilterSQL->oHerstellerFilterSQL->cJoin . "
                        " . $FilterSQL->oSuchspecialFilterSQL->cJoin . "
                        " . $FilterSQL->oSuchFilterSQL->cJoin . "
                        " . $FilterSQL->oKategorieFilterSQL->cJoin . "
                        " . $FilterSQL->oMerkmalFilterSQL->cJoin . "
                        " . $FilterSQL->oTagFilterSQL->cJoin . "
                        " . $FilterSQL->oBewertungSterneFilterSQL->cJoin . "
                        LEFT JOIN tartikelsichtbarkeit ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "

                        " . $cPreisspannenJOIN . "

                        WHERE tartikelsichtbarkeit.kArtikel IS NULL
                            AND tartikel.kVaterArtikel = 0
                            " . gibLagerfilter() . "
                            " . $FilterSQL->oSuchspecialFilterSQL->cWhere . "
                            " . $FilterSQL->oSuchFilterSQL->cWhere . "
                            " . $FilterSQL->oHerstellerFilterSQL->cWhere . "
                            " . $FilterSQL->oKategorieFilterSQL->cWhere . "
                            " . $FilterSQL->oMerkmalFilterSQL->cWhere . "
                            " . $FilterSQL->oTagFilterSQL->cWhere . "
                            " . $FilterSQL->oBewertungSterneFilterSQL->cWhere . "
                            " . $FilterSQL->oPreisspannenFilterSQL->cWhere . "
                        GROUP BY tartikel.kArtikel
                        " . $FilterSQL->oMerkmalFilterSQL->cHaving . "
                    ) AS ssMerkmal
                    ", 1
            );
            $nPreisspannenAnzahl_arr   = get_object_vars($oPreisspannenFilterDB);
            $oPreisspannenFilterDB_arr = [];
            if (is_array($nPreisspannenAnzahl_arr)) {
                $count = count($nPreisspannenAnzahl_arr);
                for ($i = 0; $i < $count; $i++) {
                    if ($i === 0) {
                        $oPreisspannenFilterDB_arr[] = ($nPreisspannenAnzahl_arr['anz' . $i] - 0);
                    } else {
                        $oPreisspannenFilterDB_arr[] = ($nPreisspannenAnzahl_arr['anz' . $i] - $nPreisspannenAnzahl_arr['anz' . ($i - 1)]);
                    }
                }
            }
            foreach ($oPreisspannenfilter_arr as $i => $oPreisspannenfilter) {
                $oPreisspannenfilterTMP                 = new stdClass();
                $oPreisspannenfilterTMP->nVon           = $oPreisspannenfilter->nVon;
                $oPreisspannenfilterTMP->nBis           = $oPreisspannenfilter->nBis;
                $oPreisspannenfilterTMP->nAnzahlArtikel = $oPreisspannenFilterDB_arr[$i];
                // Localize Preise
                $oPreisspannenfilterTMP->cVonLocalized = gibPreisLocalizedOhneFaktor($oPreisspannenfilterTMP->nVon, $waehrung);
                $oPreisspannenfilterTMP->cBisLocalized = gibPreisLocalizedOhneFaktor($oPreisspannenfilterTMP->nBis, $waehrung);
                //baue URL
                $oZusatzFilter                           = new stdClass();
                $oZusatzFilter->PreisspannenFilter       = new stdClass();
                $oZusatzFilter->PreisspannenFilter->fVon = $oPreisspannenfilterTMP->nVon;
                $oZusatzFilter->PreisspannenFilter->fBis = $oPreisspannenfilterTMP->nBis;
                $oPreisspannenfilterTMP->cURL            = gibNaviURL($NaviFilter, true, $oZusatzFilter);
                $oPreisspanne_arr[]                      = $oPreisspannenfilterTMP;
            }
        }
    }
    // Preisspannen ohne Artikel ausblenden (falls im Backend eingestellt)
    if ($conf['navigationsfilter']['preisspannenfilter_spannen_ausblenden'] === 'Y' && count($oPreisspanne_arr) > 0) {
        $oPreisspanneTMP_arr = [];
        foreach ($oPreisspanne_arr as $oPreisspanne) {
            if ($oPreisspanne->nAnzahlArtikel > 0) {
                $oPreisspanneTMP_arr[] = $oPreisspanne;
            }
        }
        $oPreisspanne_arr = $oPreisspanneTMP_arr;
    }
    $tagArray = [CACHING_GROUP_CATEGORY, CACHING_GROUP_FILTER];
    if (isset($NaviFilter->Kategorie->kKategorie)) {
        $tagArray[] = CACHING_GROUP_CATEGORY . '_' . (int)$NaviFilter->Kategorie->kKategorie;
    }
    Shop::Cache()->set($cacheID, $oPreisspanne_arr, $tagArray);

    return $oPreisspanne_arr;
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 */
function gibTagFilterOptionen($FilterSQL, $NaviFilter)
{
    if (isset(Shop::$kSprache)) {
        $kSprache = (int)Shop::$kSprache;
    } else {
        $oSprache = gibStandardsprache(true);
        $kSprache = (int)$oSprache->kSprache;
    }
    $cacheID = 'gtfo_' . md5(json_encode($FilterSQL) . serialize($NaviFilter)) . '_' . (int)$_SESSION['Kundengruppe']->kKundengruppe . '_' . $kSprache;
    if (($oTagFilter_arr = Shop::Cache()->get($cacheID)) !== false) {
        return $oTagFilter_arr;
    }
    $oTagFilter_arr = [];
    $conf           = Shop::getSettings([CONF_NAVIGATIONSFILTER]);
    if ($conf['navigationsfilter']['allgemein_tagfilter_benutzen'] !== 'N') {
        $oTagFilterDB_arr = Shop::DB()->query(
            "SELECT tseo.cSeo, ssMerkmal.kTag, ssMerkmal.cName, COUNT(*) AS nAnzahl, SUM(ssMerkmal.nAnzahlTagging) AS nAnzahlTagging
                FROM
                (
                    SELECT ttag.kTag, ttag.cName, ttagartikel.nAnzahlTagging
                    FROM tartikel
                    JOIN ttagartikel ON ttagartikel.kArtikel = tartikel.kArtikel
                    JOIN ttag ON ttagartikel.kTag = ttag.kTag
                    " . $FilterSQL->oHerstellerFilterSQL->cJoin . "
                " . $FilterSQL->oSuchspecialFilterSQL->cJoin . "
                " . $FilterSQL->oSuchFilterSQL->cJoin . "
                " . $FilterSQL->oKategorieFilterSQL->cJoin . "
                " . $FilterSQL->oMerkmalFilterSQL->cJoin . "
                " . $FilterSQL->oBewertungSterneFilterSQL->cJoin . "
                " . $FilterSQL->oPreisspannenFilterSQL->cJoin . "
                LEFT JOIN tartikelsichtbarkeit ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tartikel.kVaterArtikel = 0
                    AND ttag.nAktiv = 1
                    AND ttag.kSprache = " . (int)Shop::$kSprache . "
                    " . gibLagerfilter() . "
                    " . $FilterSQL->oSuchspecialFilterSQL->cWhere . "
                    " . $FilterSQL->oSuchFilterSQL->cWhere . "
                    " . $FilterSQL->oHerstellerFilterSQL->cWhere . "
                    " . $FilterSQL->oKategorieFilterSQL->cWhere . "
                    " . $FilterSQL->oMerkmalFilterSQL->cWhere . "
                    " . $FilterSQL->oTagFilterSQL->cWhere . "
                    " . $FilterSQL->oBewertungSterneFilterSQL->cWhere . "
                    " . $FilterSQL->oPreisspannenFilterSQL->cWhere . "
                GROUP BY ttag.kTag, tartikel.kArtikel
                " . $FilterSQL->oMerkmalFilterSQL->cHaving . "
            ) AS ssMerkmal
            LEFT JOIN tseo ON tseo.kKey = ssMerkmal.kTag
                AND tseo.cKey = 'kTag'
                AND tseo.kSprache = " . Shop::$kSprache . "
            GROUP BY ssMerkmal.kTag
            ORDER BY nAnzahl DESC LIMIT 0 , " . (int)$conf['navigationsfilter']['tagfilter_max_anzeige'], 2
        );

        if (is_array($oTagFilterDB_arr)) {
            foreach ($oTagFilterDB_arr as $oTagFilterDB) {
                $oTagFilter = new stdClass();
                if (!isset($oZusatzFilter)) {
                    $oZusatzFilter = new stdClass();
                }
                if (!isset($oZusatzFilter->TagFilter)) {
                    $oZusatzFilter->TagFilter = new stdClass();
                }
                //baue URL
                $oZusatzFilter->TagFilter->kTag = $oTagFilterDB->kTag;
                $oTagFilter->cURL               = gibNaviURL($NaviFilter, true, $oZusatzFilter);
                $oTagFilter->kTag               = $oTagFilterDB->kTag;
                $oTagFilter->cName              = $oTagFilterDB->cName;
                $oTagFilter->nAnzahl            = $oTagFilterDB->nAnzahl;
                $oTagFilter->nAnzahlTagging     = $oTagFilterDB->nAnzahlTagging;

                $oTagFilter_arr[] = $oTagFilter;
            }
        }
        // Priorität berechnen
        $nPrioStep = 0;
        $nCount    = count($oTagFilter_arr);
        if ($nCount > 0) {
            $nPrioStep = ($oTagFilter_arr[0]->nAnzahlTagging - $oTagFilter_arr[$nCount - 1]->nAnzahlTagging) / 9;
        }
        foreach ($oTagFilter_arr as $i => $oTagwolke) {
            if ($oTagwolke->kTag > 0) {
                if ($nPrioStep < 1) {
                    $oTagFilter_arr[$i]->Klasse = rand(1, 10);
                } else {
                    $oTagFilter_arr[$i]->Klasse = round(($oTagwolke->nAnzahlTagging - $oTagFilter_arr[$nCount - 1]->nAnzahlTagging) / $nPrioStep) + 1;
                }
            }
        }
    }
    $tagArray = [CACHING_GROUP_CATEGORY, CACHING_GROUP_FILTER];
    //@todo: tags should flush the cache
    if (isset($NaviFilter->Kategorie->kKategorie)) {
        $tagArray[] = CACHING_GROUP_CATEGORY . '_' . (int)$NaviFilter->Kategorie->kKategorie;
    }
    Shop::Cache()->set($cacheID, $oTagFilter_arr, $tagArray);

    return $oTagFilter_arr;
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return string
 */
function gibSuchFilterJSONOptionen($FilterSQL, $NaviFilter)
{
    $oSuchfilter_arr = gibSuchFilterOptionen($FilterSQL, $NaviFilter); // cURL
    foreach ($oSuchfilter_arr as $key => $oSuchfilter) {
        $oSuchfilter_arr[$key]->cURL = StringHandler::htmlentitydecode($oSuchfilter->cURL);
    }

    return Boxen::gibJSONString($oSuchfilter_arr);
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return string
 */
function gibTagFilterJSONOptionen($FilterSQL, $NaviFilter)
{
    $oTags_arr = gibTagFilterOptionen($FilterSQL, $NaviFilter);
    foreach ($oTags_arr as $key => $oTags) {
        $oTags_arr[$key]->cURL = StringHandler::htmlentitydecode($oTags->cURL);
    }

    return Boxen::gibJSONString($oTags_arr);
}

/**
 * @param object         $FilterSQL
 * @param object         $NaviFilter
 * @param Kategorie|null $oAktuelleKategorie
 * @param bool           $bForce true if `merkmalfilter_verwenden`, `merkmalfilter_maxmerkmale` and
 *      `merkmalfilter_maxmerkmalwerte` should be ignored
 * @return array|mixed
 */
function gibMerkmalFilterOptionen($FilterSQL, $NaviFilter, $oAktuelleKategorie = null, $bForce = false)
{
    $cacheID = 'filter_mm_' . md5(
            json_encode($FilterSQL) .
            (isset($NaviFilter->Kategorie->kKategorie) ? json_encode($NaviFilter->Kategorie->kKategorie) : 0)
        ) . '_' . (isset($_SESSION['Kundengruppe']->kKundengruppe) ?
            (int)$_SESSION['Kundengruppe']->kKundengruppe : '0') . '_' . (int)Shop::$kSprache . (($bForce === true) ? '_f'
            : '');
    if (($oMerkmalFilter_arr = Shop::Cache()->get($cacheID)) !== false) {
        return $oMerkmalFilter_arr;
    }

    $oMerkmalFilter_arr          = [];
    $cKatAttribMerkmalFilter_arr = [];
    $conf                        = Shop::getSettings([CONF_NAVIGATIONSFILTER]);
    if ($bForce ||(isset($conf['navigationsfilter']['merkmalfilter_verwenden']) &&
            $conf['navigationsfilter']['merkmalfilter_verwenden'] !== 'N')
    ) {
        // Ist Kategorie Mainword, dann prüfe die Kategorie-Funktionsattribute auf merkmalfilter
        if (isset($NaviFilter->Kategorie->kKategorie, $oAktuelleKategorie->categoryFunctionAttributes) &&
            $NaviFilter->Kategorie->kKategorie > 0 &&
            is_array($oAktuelleKategorie->categoryFunctionAttributes) &&
            count($oAktuelleKategorie->categoryFunctionAttributes) > 0 &&
            !empty($oAktuelleKategorie->categoryFunctionAttributes[KAT_ATTRIBUT_MERKMALFILTER])
        ) {
            $cKatAttribMerkmalFilter_arr = explode(';', $oAktuelleKategorie->categoryFunctionAttributes[KAT_ATTRIBUT_MERKMALFILTER]);
        }
        if (!isset($FilterSQL->oMerkmalFilterSQL->cJoinMMW)) {
            $FilterSQL->oMerkmalFilterSQL->cJoinMMW  = null;
            $FilterSQL->oMerkmalFilterSQL->cWhereMMW = null;
        }
        //Sprache beachten
        $kSprache         = (int)Shop::$kSprache;
        $kStandardSprache = (int)gibStandardsprache()->kSprache;
        if ($kSprache !== $kStandardSprache) {
            $cSelectMerkmal     = "COALESCE(tmerkmalsprache.cName, tmerkmal.cName) AS cName, ";
            $cJoinMerkmal       = "LEFT JOIN tmerkmalsprache
                                        ON tmerkmalsprache.kMerkmal = tmerkmal.kMerkmal
                                        AND tmerkmalsprache.kSprache = " . $kSprache;
            $cSelectMerkmalwert = "COALESCE(fremdSprache.cSeo, standardSprache.cSeo) AS cSeo,
                                    COALESCE(fremdSprache.cWert, standardSprache.cWert) AS cWert,";
            $cJoinMerkmalwert   = "INNER JOIN tmerkmalwertsprache AS standardSprache
                                        ON standardSprache.kMerkmalWert = tartikelmerkmal.kMerkmalWert
                                        AND standardSprache.kSprache = " . $kStandardSprache . "
                                    LEFT JOIN tmerkmalwertsprache AS fremdSprache 
                                        ON fremdSprache.kMerkmalWert = tartikelmerkmal.kMerkmalWert
                                        AND fremdSprache.kSprache = " . $kSprache . "";
        } else {
            $cSelectMerkmalwert = "tmerkmalwertsprache.cWert, tmerkmalwertsprache.cSeo,";
            $cJoinMerkmalwert   = "INNER JOIN tmerkmalwertsprache
                                        ON tmerkmalwertsprache.kMerkmalWert = tartikelmerkmal.kMerkmalWert
                                        AND tmerkmalwertsprache.kSprache = " . $kSprache;
            $cSelectMerkmal     = 'tmerkmal.cName, ';
            $cJoinMerkmal       = '';
        }
        $oMerkmalFilterDB_arr = Shop::DB()->query(
            "SELECT ssMerkmal.cSeo, ssMerkmal.kMerkmal, ssMerkmal.kMerkmalWert, ssMerkmal.cMMWBildPfad, ssMerkmal.cWert, 
                ssMerkmal.cName, ssMerkmal.cTyp, ssMerkmal.cMMBildPfad, COUNT(*) AS nAnzahl
                FROM
                (
                    SELECT tartikelmerkmal.kMerkmal, tartikelmerkmal.kMerkmalWert, tmerkmalwert.cBildPfad AS cMMWBildPfad,
                    " . $cSelectMerkmalwert . " tmerkmal.nSort AS nSortMerkmal, tmerkmalwert.nSort, 
                        " . $cSelectMerkmal . " tmerkmal.cTyp, tmerkmal.cBildPfad AS cMMBildPfad
                        FROM tartikel
                        JOIN tartikelmerkmal 
                            ON tartikel.kArtikel = tartikelmerkmal.kArtikel
                        JOIN tmerkmalwert 
                            ON tmerkmalwert.kMerkmalWert = tartikelmerkmal.kMerkmalWert
                        " . $cJoinMerkmalwert . "
                        JOIN tmerkmal 
                            ON tmerkmal.kMerkmal = tartikelmerkmal.kMerkmal
                        " . $cJoinMerkmal . "
                        " . ((isset($FilterSQL->oHerstellerFilterSQL->cJoin))
                                ? $FilterSQL->oHerstellerFilterSQL->cJoin
                                : ''
                            ) . "
                        " . ((isset($FilterSQL->oSuchspecialFilterSQL->cJoin))
                                ? $FilterSQL->oSuchspecialFilterSQL->cJoin : ''
                            ) . "
                        " . ((isset($FilterSQL->oSuchFilterSQL->cJoin))
                                ? $FilterSQL->oSuchFilterSQL->cJoin
                                : ''
                            ) . "
                        " . ((isset($FilterSQL->oKategorieFilterSQL->cJoin))
                                ? $FilterSQL->oKategorieFilterSQL->cJoin
                                : ''
                            ) . "
                        " . ((isset($FilterSQL->oMerkmalFilterSQL->cJoinMMW))
                                ? $FilterSQL->oMerkmalFilterSQL->cJoinMMW
                                : ''
                            ) . "
                        " . ((isset($FilterSQL->oTagFilterSQL->cJoin))
                                ? $FilterSQL->oTagFilterSQL->cJoin
                                : ''
                            ) . "
                        " . ((isset($FilterSQL->oBewertungSterneFilterSQL->cJoin))
                                ? $FilterSQL->oBewertungSterneFilterSQL->cJoin
                                : ''
                            ) . "
                        " . ((isset($FilterSQL->oPreisspannenFilterSQL->cJoin))
                                ? $FilterSQL->oPreisspannenFilterSQL->cJoin
                                : ''
                            ) . "
                        LEFT JOIN tartikelsichtbarkeit 
                            ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL
                            AND tartikel.kVaterArtikel = 0
                            " . gibLagerfilter() . "
                            " . ((isset($FilterSQL->oSuchspecialFilterSQL->cWhere))
                                    ? $FilterSQL->oSuchspecialFilterSQL->cWhere
                                    : ''
                                ) . "
                            " . ((isset($FilterSQL->oSuchFilterSQL->cWhere))
                                    ? $FilterSQL->oSuchFilterSQL->cWhere
                                    : ''
                                ) . "
                            " . ((isset($FilterSQL->oHerstellerFilterSQL->cWhere))
                                    ? $FilterSQL->oHerstellerFilterSQL->cWhere
                                    : ''
                                ) . "
                            " . ((isset($FilterSQL->oKategorieFilterSQL->cWhere))
                                    ? $FilterSQL->oKategorieFilterSQL->cWhere
                                    : ''
                                ) . "
                            " . ((isset($FilterSQL->oMerkmalFilterSQL->cWhereMMW))
                                    ? $FilterSQL->oMerkmalFilterSQL->cWhereMMW
                                    : ''
                                ) . "
                            " . ((isset($FilterSQL->oTagFilterSQL->cWhere))
                                    ? $FilterSQL->oTagFilterSQL->cWhere
                                    : ''
                                ) . "
                            " . ((isset($FilterSQL->oBewertungSterneFilterSQL->cWhere))
                                    ? $FilterSQL->oBewertungSterneFilterSQL->cWhere
                                    : ''
                                ) . "
                            " . ((isset($FilterSQL->oPreisspannenFilterSQL->cWhere))
                                    ? $FilterSQL->oPreisspannenFilterSQL->cWhere
                                    : ''
                                ) . "
                        GROUP BY tartikelmerkmal.kMerkmalWert, tartikel.kArtikel
                ) AS ssMerkmal
                GROUP BY ssMerkmal.kMerkmalWert
                ORDER BY ssMerkmal.nSortMerkmal, ssMerkmal.nSort, ssMerkmal.cWert", 2
        );

        if (is_array($oMerkmalFilterDB_arr) && count($oMerkmalFilterDB_arr) > 0) {
            foreach ($oMerkmalFilterDB_arr as $i => $oMerkmalFilterDB) {
                $nPos          = gibMerkmalPosition($oMerkmalFilter_arr, $oMerkmalFilterDB->kMerkmal);
                $oMerkmalWerte = new stdClass();
                $oMerkmalWerte->nAktiv = 0;
                if ((isset($NaviFilter->MerkmalWert->kMerkmalWert) &&
                        $NaviFilter->MerkmalWert->kMerkmalWert == $oMerkmalFilterDB->kMerkmalWert) ||
                    (isset($NaviFilter->MerkmalFilter) &&
                        checkMerkmalWertVorhanden($NaviFilter->MerkmalFilter, $oMerkmalFilterDB->kMerkmalWert))
                ) {
                    $oMerkmalWerte->nAktiv = 1;
                }
                $oMerkmalWerte->kMerkmalWert = $oMerkmalFilterDB->kMerkmalWert;
                $oMerkmalWerte->cWert        = $oMerkmalFilterDB->cWert;
                $oMerkmalWerte->nAnzahl      = $oMerkmalFilterDB->nAnzahl;

                if (strlen($oMerkmalFilterDB->cMMWBildPfad) > 0) {
                    $oMerkmalWerte->cBildpfadKlein  = PFAD_MERKMALWERTBILDER_KLEIN . $oMerkmalFilterDB->cMMWBildPfad;
                    $oMerkmalWerte->cBildpfadNormal = PFAD_MERKMALWERTBILDER_NORMAL . $oMerkmalFilterDB->cMMWBildPfad;
                } else {
                    $oMerkmalWerte->cBildpfadKlein = BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
                    $oMerkmalWerte->cBildpfadGross = BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
                }
                if (!isset($oZusatzFilter)) {
                    $oZusatzFilter = new stdClass();
                }
                //baue URL
                if (!isset($oZusatzFilter->MerkmalFilter)) {
                    $oZusatzFilter->MerkmalFilter = new stdClass();
                }
                $oZusatzFilter->MerkmalFilter->kMerkmalWert = $oMerkmalFilterDB->kMerkmalWert;
                $oZusatzFilter->MerkmalFilter->cSeo         = $oMerkmalFilterDB->cSeo;
                $oMerkmalWerte->cURL                        = gibNaviURL($NaviFilter, true, $oZusatzFilter);

                //hack for #4815
                if ($oMerkmalWerte->nAktiv === 1 && isset($oZusatzFilter->MerkmalFilter->cSeo)) {
                    //remove '__attrY' from '<url>attrX__attrY'
                    $newURL = str_replace('__' . $oZusatzFilter->MerkmalFilter->cSeo, '', $oMerkmalWerte->cURL);
                    //remove 'attrY__' from '<url>attrY__attrX'
                    $newURL              = str_replace($oZusatzFilter->MerkmalFilter->cSeo . '__', '', $newURL);
                    $oMerkmalWerte->cURL = $newURL;
                }
                $oMerkmal           = new stdClass();
                $oMerkmal->cName    = $oMerkmalFilterDB->cName;
                $oMerkmal->cTyp     = $oMerkmalFilterDB->cTyp;
                $oMerkmal->kMerkmal = $oMerkmalFilterDB->kMerkmal;
                if (strlen($oMerkmalFilterDB->cMMBildPfad) > 0) {
                    $oMerkmal->cBildpfadKlein  = PFAD_MERKMALBILDER_KLEIN . $oMerkmalFilterDB->cMMBildPfad;
                    $oMerkmal->cBildpfadNormal = PFAD_MERKMALBILDER_NORMAL . $oMerkmalFilterDB->cMMBildPfad;
                } else {
                    $oMerkmal->cBildpfadKlein = BILD_KEIN_MERKMALBILD_VORHANDEN;
                    $oMerkmal->cBildpfadGross = BILD_KEIN_MERKMALBILD_VORHANDEN;
                }
                $oMerkmal->oMerkmalWerte_arr = [];
                if ($nPos >= 0) {
                    $oMerkmalFilter_arr[$nPos]->oMerkmalWerte_arr[] = $oMerkmalWerte;
                } else {
                    //#533 Anzahl max Merkmale erreicht?
                    if (!$bForce && isset($conf['navigationsfilter']['merkmalfilter_maxmerkmale']) &&
                        $conf['navigationsfilter']['merkmalfilter_maxmerkmale'] > 0 &&
                        count($oMerkmalFilter_arr) >= $conf['navigationsfilter']['merkmalfilter_maxmerkmale']
                    ) {
                        continue;
                    }
                    $oMerkmal->oMerkmalWerte_arr[] = $oMerkmalWerte;
                    $oMerkmalFilter_arr[]          = $oMerkmal;
                }
            }
        }
        //Filter durchgehen und die Merkmalwerte raustun, die zuviel sind und deren Anzahl am geringsten ist.
        if (!$bForce && isset($conf['navigationsfilter']['merkmalfilter_maxmerkmalwerte']) &&
            $conf['navigationsfilter']['merkmalfilter_maxmerkmalwerte'] > 0
        ) {
            foreach ($oMerkmalFilter_arr as $oMerkmalFilter) {
                //#534 Anzahl max Merkmalwerte erreicht?
                while (count($oMerkmalFilter->oMerkmalWerte_arr) > $conf['navigationsfilter']['merkmalfilter_maxmerkmalwerte']) {
                    $nMinAnzahl = 999999;
                    $nIndex     = -1;
                    foreach ($oMerkmalFilter->oMerkmalWerte_arr as $l => $oMerkmalWert) {
                        if ($oMerkmalWert->nAnzahl < $nMinAnzahl) {
                            $nMinAnzahl = $oMerkmalWert->nAnzahl;
                            $nIndex     = $l;
                        }
                    }
                    if ($nIndex >= 0) {
                        unset($oMerkmalFilter->oMerkmalWerte_arr[$nIndex]);
                        $oMerkmalFilter->oMerkmalWerte_arr = array_merge($oMerkmalFilter->oMerkmalWerte_arr);
                    }
                }
            }
        }
        // Falls merkmalfilter Kategorieattribut gesetzt ist, alle Merkmale die nicht enthalten sein dürfen rauswerfen
        if (count($cKatAttribMerkmalFilter_arr) > 0) {
            $nKatFilter = count($oMerkmalFilter_arr);
            for ($i = 0; $i < $nKatFilter; $i++) {
                if (!in_array($oMerkmalFilter_arr[$i]->cName, $cKatAttribMerkmalFilter_arr)) {
                    unset($oMerkmalFilter_arr[$i]);
                }
            }
            $oMerkmalFilter_arr = array_merge($oMerkmalFilter_arr);
        }
        //Merkmalwerte numerisch sortieren, wenn alle Merkmalwerte eines Merkmals numerisch sind
        foreach ($oMerkmalFilter_arr as $o => $oMerkmalFilter) {
            $bAlleNumerisch = true;
            $count          = count($oMerkmalFilter->oMerkmalWerte_arr);
            for ($i = 0; $i < $count; $i++) {
                if (!is_numeric($oMerkmalFilter->oMerkmalWerte_arr[$i]->cWert)) {
                    $bAlleNumerisch = false;
                    break;
                }
            }
            if ($bAlleNumerisch) {
                usort($oMerkmalFilter_arr[$o]->oMerkmalWerte_arr, 'sortierMerkmalWerteNumerisch');
            }
        }
    }
    $tagArray = [CACHING_GROUP_CATEGORY, 'jtl_mmf', CACHING_GROUP_FILTER];
    //the cache depends on article attributes - so it has to be invalidated on every product update...
    if (isset($NaviFilter->Kategorie->kKategorie)) {
        $tagArray[] = CACHING_GROUP_CATEGORY . '_' . (int)$NaviFilter->Kategorie->kKategorie;
    }
    if (isset($oAktuelleKategorie->kKategorie)) {
        $tagArray[] = CACHING_GROUP_CATEGORY . '_' . (int)$oAktuelleKategorie->kKategorie;
    }
    Shop::Cache()->set($cacheID, $oMerkmalFilter_arr, $tagArray);

    return $oMerkmalFilter_arr;
}

/**
 * @param object $a
 * @param object $b
 * @return int
 */
function sortierMerkmalWerteNumerisch($a, $b)
{
    if ($a == $b) {
        return 0;
    }

    return ($a->cWert < $b->cWert) ? -1 : 1;
}

/**
 * @param object $FilterSQL
 * @param object $NaviFilter
 * @return array|mixed
 */
function gibSuchspecialFilterOptionen($FilterSQL, $NaviFilter)
{
    $cacheID = 'gssfo_' . md5(json_encode($FilterSQL)) . '_' . (int)$_SESSION['Kundengruppe']->kKundengruppe;
    if (($oSuchspecialFilterDB_arr = Shop::Cache()->get($cacheID)) !== false) {
        return $oSuchspecialFilterDB_arr;
    }
    $oSuchspecialFilterDB_arr = [];
    $conf                     = Shop::getSettings([CONF_NAVIGATIONSFILTER, CONF_BOXEN, CONF_GLOBAL]);
    if ($conf['navigationsfilter']['allgemein_suchspecialfilter_benutzen'] === 'Y') {
        for ($i = 1; $i < 7; $i++) {
            $oFilter = new stdClass();
            switch ($i) {
                case SEARCHSPECIALS_BESTSELLER:
                    $nAnzahl = 100;
                    if ($conf['global']['global_bestseller_minanzahl'] > 0) {
                        $nAnzahl = (int)$conf['global']['global_bestseller_minanzahl'];
                    }

                    $oFilter->cJoin  = 'JOIN tbestseller ON tbestseller.kArtikel = tartikel.kArtikel';
                    $oFilter->cWhere = ' AND round(tbestseller.fAnzahl) >= ' . $nAnzahl;
                    break;
                case SEARCHSPECIALS_SPECIALOFFERS:
                    if (!isset($NaviFilter->PreisspannenFilter->fVon) && !isset($NaviFilter->PreisspannenFilter->fBis)) {
                        $oFilter->cJoin = "JOIN tartikelsonderpreis ON tartikelsonderpreis.kArtikel = tartikel.kArtikel
                                            JOIN tsonderpreise ON tsonderpreise.kArtikelSonderpreis = tartikelsonderpreis.kArtikelSonderpreis";
                        $tsonderpreise = 'tsonderpreise';
                    } else {
                        $tsonderpreise = 'tsonderpreise';//'tspgspqf';
                    }
                    $oFilter->cWhere = " AND tartikelsonderpreis.cAktiv='Y' AND tartikelsonderpreis.dStart <= now()
                                            AND (tartikelsonderpreis.dEnde >= CURDATE() OR tartikelsonderpreis.dEnde = '0000-00-00')
                                            AND " . $tsonderpreise . ".kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe;
                    break;
                case SEARCHSPECIALS_NEWPRODUCTS:
                    $alter_tage = 30;
                    if ($conf['boxen']['box_neuimsortiment_alter_tage'] > 0) {
                        $alter_tage = (int)$conf['boxen']['box_neuimsortiment_alter_tage'];
                    }
                    $oFilter->cJoin  = '';
                    $oFilter->cWhere = " AND tartikel.cNeu='Y' AND DATE_SUB(now(),INTERVAL $alter_tage DAY) < tartikel.dErstellt";
                    break;
                case SEARCHSPECIALS_TOPOFFERS:
                    $oFilter->cJoin  = '';
                    $oFilter->cWhere = ' AND tartikel.cTopArtikel = "Y"';
                    break;
                case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                    $oFilter->cJoin  = '';
                    $oFilter->cWhere = ' AND now() < tartikel.dErscheinungsdatum';
                    break;
                case SEARCHSPECIALS_TOPREVIEWS:
                    if (!isset($NaviFilter->BewertungFilter->nSterne)) {
                        $oFilter->cJoin = "JOIN tartikelext ON tartikelext.kArtikel = tartikel.kArtikel";
                    }
                    $oFilter->cWhere = " AND round(tartikelext.fDurchschnittsBewertung) >= " . (int)$conf['boxen']['boxen_topbewertet_minsterne'];
                    break;
            }
            if (!isset($oFilter->cJoin)) {
                $oFilter->cJoin = '';
            }
            $oSuchspecialFilterDB = Shop::DB()->query(
                "SELECT COUNT(*) AS nAnzahl
                    FROM
                    (
                        SELECT tartikel.kArtikel
                        FROM tartikel
                        " . $oFilter->cJoin . "
                    " . $FilterSQL->oHerstellerFilterSQL->cJoin . "
                    " . $FilterSQL->oSuchspecialFilterSQL->cJoin . "
                    " . $FilterSQL->oSuchFilterSQL->cJoin . "
                    " . $FilterSQL->oKategorieFilterSQL->cJoin . "
                    " . $FilterSQL->oMerkmalFilterSQL->cJoin . "
                    " . $FilterSQL->oTagFilterSQL->cJoin . "
                    " . $FilterSQL->oBewertungSterneFilterSQL->cJoin . "
                    " . $FilterSQL->oPreisspannenFilterSQL->cJoin . "
                    LEFT JOIN tartikelsichtbarkeit ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND tartikel.kVaterArtikel = 0
                        " . gibLagerfilter() . "
                        " . $oFilter->cWhere . "
                        " . $FilterSQL->oSuchspecialFilterSQL->cWhere . "
                        " . $FilterSQL->oSuchFilterSQL->cWhere . "
                        " . $FilterSQL->oHerstellerFilterSQL->cWhere . "
                        " . $FilterSQL->oKategorieFilterSQL->cWhere . "
                        " . $FilterSQL->oMerkmalFilterSQL->cWhere . "
                        " . $FilterSQL->oTagFilterSQL->cWhere . "
                        " . $FilterSQL->oBewertungSterneFilterSQL->cWhere . "
                        " . $FilterSQL->oPreisspannenFilterSQL->cWhere . "
                    GROUP BY tartikel.kArtikel
                    " . $FilterSQL->oMerkmalFilterSQL->cHaving . "
                ) AS ssMerkmal
                ", 1
            );
            $oSuchspecial          = new stdClass();
            $oSuchspecial->nAnzahl = $oSuchspecialFilterDB->nAnzahl;
            $oSuchspecial->kKey    = $i;

            $oZusatzFilter                          = new stdClass();
            $oZusatzFilter->SuchspecialFilter       = new stdClass();
            $oZusatzFilter->SuchspecialFilter->kKey = $i;
            $oSuchspecial->cURL                     = gibNaviURL($NaviFilter, false, $oZusatzFilter);
            if ($oSuchspecial->nAnzahl > 0) {
                $oSuchspecialFilterDB_arr[$i]       = $oSuchspecial;
            }
        }
    }

    $tagArray = [CACHING_GROUP_CATEGORY, CACHING_GROUP_FILTER];
    if (isset($NaviFilter->Kategorie->kKategorie)) {
        $tagArray[] = CACHING_GROUP_CATEGORY . '_' . (int)$NaviFilter->Kategorie->kKategorie;
    }
    Shop::Cache()->set($cacheID, $oSuchspecialFilterDB_arr, $tagArray);

    return $oSuchspecialFilterDB_arr;
}

/**
 * @param object $NaviFilter
 * @param int    $kSpracheExt
 * @return int
 */
function bearbeiteSuchCache($NaviFilter, $kSpracheExt = 0)
{
    // Mapping beachten
    $cSuche                    = mappingBeachten($NaviFilter->Suche->cSuche, $kSpracheExt);
    $NaviFilter->Suche->cSuche = $cSuche;
    $kSprache                  = ($kSpracheExt !== 0 && $kSpracheExt !== null) ? (int)$kSpracheExt : (int)Shop::$kSprache;
    // Suchcache wurde zwar gefunden, ist jedoch nicht mehr gültig
    Shop::DB()->query(
        "DELETE tsuchcache, tsuchcachetreffer
            FROM tsuchcache
            LEFT JOIN tsuchcachetreffer ON tsuchcachetreffer.kSuchCache = tsuchcache.kSuchCache
            WHERE tsuchcache.kSprache = " . $kSprache . "
                AND tsuchcache.dGueltigBis IS NOT NULL
                AND DATE_ADD(tsuchcache.dGueltigBis, INTERVAL 5 MINUTE) < now()", 3
    );

    // Suchergebnis ist abhängig von Kundengruppe und Lagerfiltereinstellungen
    $conf     = Shop::getSettings([CONF_ARTIKELUEBERSICHT, CONF_GLOBAL]);
    $keySuche = $cSuche . ';' . $conf['global']['artikel_artikelanzeigefilter'] . ';' . $_SESSION['Kundengruppe']->kKundengruppe;

    // Suchcache checken, ob bereits vorhanden
    $oSuchCache = Shop::DB()->executeQueryPrepared(
        "SELECT kSuchCache
            FROM tsuchcache
            WHERE kSprache =  :lang
                AND cSuche = :search
                AND (dGueltigBis > now() OR dGueltigBis IS NULL)",
        ['lang' => $kSprache, 'search' => Shop::DB()->escape($keySuche)],
        1
    );

    if (isset($oSuchCache->kSuchCache) && $oSuchCache->kSuchCache > 0) {
        return $oSuchCache->kSuchCache; // Gib gültigen Suchcache zurück
    } else {
        // wenn kein Suchcache vorhanden
        $nMindestzeichen = 3;

        if ((int)$conf['artikeluebersicht']['suche_min_zeichen'] > 0) {
            $nMindestzeichen = (int)$conf['artikeluebersicht']['suche_min_zeichen'];
        }
        if (strlen($cSuche) < $nMindestzeichen) {
            require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
            $NaviFilter->Suche->Fehler = lang_suche_mindestanzahl($cSuche, $nMindestzeichen);

            return 0;
        }
        // Suchausdruck aufbereiten
        $cSuch_arr    = suchausdruckVorbereiten($cSuche);
        $cSuchTMP_arr = $cSuch_arr;
        if (count($cSuch_arr) > 0) {
            // Array mit nach Prio sort. Suchspalten holen
            $cSuchspalten_arr       = gibSuchSpalten();
            $cSuchspaltenKlasse_arr = gibSuchspaltenKlassen($cSuchspalten_arr);
            $oSuchCache             = new stdClass();
            $oSuchCache->kSprache   = $kSprache;
            $oSuchCache->cSuche     = $keySuche;
            $oSuchCache->dErstellt  = 'now()';
            $kSuchCache             = Shop::DB()->insert('tsuchcache', $oSuchCache);

            if (isset($conf['artikeluebersicht']['suche_fulltext']) &&
                $conf['artikeluebersicht']['suche_fulltext'] !== 'N' &&
                isFulltextIndexActive()
            ) {
                $oSuchCache->kSuchCache = $kSuchCache;

                return bearbeiteSuchCacheFulltext(
                    $oSuchCache,
                    $cSuchspalten_arr,
                    $cSuch_arr,
                    $conf['artikeluebersicht']['suche_max_treffer'],
                    $conf['artikeluebersicht']['suche_fulltext']
                );
            }

            if ($kSuchCache > 0) {
                if (Shop::$kSprache > 0 && !standardspracheAktiv()) {
                    $cSQL = "SELECT '" . $kSuchCache . "', IF(tartikel.kVaterArtikel > 0, tartikel.kVaterArtikel, tartikelsprache.kArtikel) AS kArtikelTMP, ";
                } else {
                    $cSQL = "SELECT '" . $kSuchCache . "', IF(tartikel.kVaterArtikel > 0, tartikel.kVaterArtikel, tartikel.kArtikel) AS kArtikelTMP, ";
                }
                // Shop2 Suche - mehr als 3 Suchwörter *
                if (count($cSuch_arr) > 3) {
                    $cSQL .= " 1 ";
                    if (Shop::$kSprache > 0 && !standardspracheAktiv()) {
                        $cSQL .= "  FROM tartikelsprache
                                        INNER JOIN tartikel 
                                            ON tartikelsprache.kArtikel = tartikel.kArtikel
                                        LEFT JOIN tartikelsichtbarkeit 
                                            ON tartikelsichtbarkeit.kArtikel = IF(tartikel.kVaterArtikel > 0, tartikel.kVaterArtikel, tartikelsprache.kArtikel)
                                            AND tartikelsichtbarkeit.kKundengruppe = " . ((int)$_SESSION['Kundengruppe']->kKundengruppe);
                    } else {
                        $cSQL .= " FROM tartikel
                                        LEFT JOIN tartikelsichtbarkeit 
                                            ON tartikelsichtbarkeit.kArtikel = IF(tartikel.kVaterArtikel > 0, tartikel.kVaterArtikel, tartikel.kArtikel)
                                            AND tartikelsichtbarkeit.kKundengruppe = " . ((int)$_SESSION['Kundengruppe']->kKundengruppe);
                    }
                    $cSQL .= " WHERE tartikelsichtbarkeit.kArtikel IS NULL " . gibLagerfilter() . " AND ";
                    if (Shop::$kSprache > 0 && !standardspracheAktiv()) {
                        $cSQL .= " tartikelsprache.kSprache = " . Shop::$kSprache . " AND (";
                    } else {
                        $cSQL .= "(";
                    }

                    foreach ($cSuchspalten_arr as $i => $cSuchspalten) {
                        if ($i > 0) {
                            $cSQL .= " OR";
                        }
                        $cSQL .= "(";
                        foreach ($cSuchTMP_arr as $j => $cSuch) {
                            if ($j > 0) {
                                $cSQL .= " AND";
                            }
                            $cSQL .= " " . $cSuchspalten . " LIKE '%" . $cSuch . "%'";
                        }
                        $cSQL .= ")";
                    }

                    $cSQL .= ")";
                } else {
                    $nKlammern = 0;
                    $nPrio     = 1;
                    foreach ($cSuchspalten_arr as $i => $cSuchspalten) {
                        if (count($cSuch_arr) > 0) {
                            // Fülle bei 1, 2 oder 3 Suchwörtern aufsplitten
                            switch (count($cSuchTMP_arr)) {
                                case 1: // Fall 1, nur ein Suchwort
                                    // "A"
                                    $nNichtErlaubteKlasse_arr = [2];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " = '" . $cSuchTMP_arr[0] . "', " . $nPrio++ . ", ";
                                    }
                                    // "A_%"
                                    $nNichtErlaubteKlasse_arr = [2, 3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '" . $cSuchTMP_arr[0] . " %', " . $nPrio++ . ", ";
                                    }
                                    // "%_A_%"
                                    $nNichtErlaubteKlasse_arr = [3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . " %', " . $nPrio++ . ", ";
                                    }
                                    // "%_A"
                                    $nNichtErlaubteKlasse_arr = [2, 3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . "', " . $nPrio++ . ", ";
                                    }
                                    // "%_A%"
                                    $nNichtErlaubteKlasse_arr = [3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . "%', " . $nPrio++ . ", ";
                                    }
                                    // "%A_%"
                                    $nNichtErlaubteKlasse_arr = [3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . " %', " . $nPrio++ . ", ";
                                    }
                                    // "A%"
                                    $nNichtErlaubteKlasse_arr = [2, 3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '" . $cSuchTMP_arr[0] . "%', " . $nPrio++ . ", ";
                                    }
                                    // "%A"
                                    $nNichtErlaubteKlasse_arr = [2, 3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . "', " . $nPrio++ . ", ";
                                    }
                                    // "%A%"
                                    $nNichtErlaubteKlasse_arr = [3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . "%', " . $nPrio++ . ", ";
                                    }
                                    break;
                                case 2: // Fall 2, zwei Suchwörter
                                    // "A_B"
                                    $nNichtErlaubteKlasse_arr = [2];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '" . $cSuchTMP_arr[0] . " " . $cSuchTMP_arr[1] . "', " . $nPrio++ . ", ";
                                    }
                                    // "B_A"
                                    $nNichtErlaubteKlasse_arr = [2, 3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '" . $cSuchTMP_arr[1] . " " . $cSuchTMP_arr[0] . "', " . $nPrio++ . ", ";
                                    }
                                    // "A_B_%"
                                    $nNichtErlaubteKlasse_arr = [2, 3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '" . $cSuchTMP_arr[0] . " " . $cSuchTMP_arr[1] . " %', " . $nPrio++ . ", ";
                                    }
                                    // "B_A_%"
                                    $nNichtErlaubteKlasse_arr = [2, 3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '" . $cSuchTMP_arr[1] . " " . $cSuchTMP_arr[0] . " %', " . $nPrio++ . ", ";
                                    }
                                    // "%_A_B"
                                    $nNichtErlaubteKlasse_arr = [2, 3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . " " . $cSuchTMP_arr[1] . "', " . $nPrio++ . ", ";
                                    }
                                    // "%_B_A"
                                    $nNichtErlaubteKlasse_arr = [2, 3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[1] . " " . $cSuchTMP_arr[0] . "', " . $nPrio++ . ", ";
                                    }
                                    // "%_A_B_%"
                                    $nNichtErlaubteKlasse_arr = [3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . " " . $cSuchTMP_arr[1] . " %', " . $nPrio++ . ", ";
                                    }
                                    // "%_B_A_%"
                                    $nNichtErlaubteKlasse_arr = [3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[1] . " " . $cSuchTMP_arr[0] . " %', " . $nPrio++ . ", ";
                                    }
                                    // "%A_B_%"
                                    $nNichtErlaubteKlasse_arr = [3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . " " . $cSuchTMP_arr[1] . " %', " . $nPrio++ . ", ";
                                    }
                                    // "%B_A_%"
                                    $nNichtErlaubteKlasse_arr = [3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[1] . " " . $cSuchTMP_arr[0] . " %', " . $nPrio++ . ", ";
                                    }
                                    // "%_A_B%"
                                    $nNichtErlaubteKlasse_arr = [3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . " " . $cSuchTMP_arr[1] . "%', " . $nPrio++ . ", ";
                                    }
                                    // "%_B_A%"
                                    $nNichtErlaubteKlasse_arr = [3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[1] . " " . $cSuchTMP_arr[0] . "%', " . $nPrio++ . ", ";
                                    }
                                    // "%A_B%"
                                    $nNichtErlaubteKlasse_arr = [3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . " " . $cSuchTMP_arr[1] . "%', " . $nPrio++ . ", ";
                                    }
                                    // "%B_A%"
                                    $nNichtErlaubteKlasse_arr = [3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[1] . " " . $cSuchTMP_arr[0] . "%', " . $nPrio++ . ", ";
                                    }
                                    // "%_A%_B_%"
                                    $nNichtErlaubteKlasse_arr = [3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . "% " . $cSuchTMP_arr[1] . " %', " . $nPrio++ . ", ";
                                    }
                                    // "%_B%_A_%"
                                    $nNichtErlaubteKlasse_arr = [3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[1] . "% " . $cSuchTMP_arr[0] . " %', " . $nPrio++ . ", ";
                                    }
                                    // "%_A_%B_%"
                                    $nNichtErlaubteKlasse_arr = [3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . " %" . $cSuchTMP_arr[1] . " %', " . $nPrio++ . ", ";
                                    }
                                    // "%_B_%A_%"
                                    $nNichtErlaubteKlasse_arr = [3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[1] . " %" . $cSuchTMP_arr[0] . " %', " . $nPrio++ . ", ";
                                    }
                                    // "%_A%_%B_%"
                                    $nNichtErlaubteKlasse_arr = [2, 3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . "% %" . $cSuchTMP_arr[1] . " %', " . $nPrio++ . ", ";
                                    }
                                    // "%_B%_%A_%"
                                    $nNichtErlaubteKlasse_arr = [2, 3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[1] . "% %" . $cSuchTMP_arr[0] . " %', " . $nPrio++ . ", ";
                                    }
                                    break;
                                case 3: // Fall 3, drei Suchwörter
                                    // "%A_%_B_%_C%"
                                    $nNichtErlaubteKlasse_arr = [3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . " % " . $cSuchTMP_arr[1] . " % " . $cSuchTMP_arr[2] . "%', " . $nPrio++ . ", ";
                                    }
                                    // "%_A_% AND %_B_% AND %_C_%"
                                    $nNichtErlaubteKlasse_arr = [3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF((" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . " %') AND (" . $cSuchspalten .
                                            " LIKE '% " . $cSuchTMP_arr[1] . " %') AND (" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[2] . " %'), " . $nPrio++ . ", ";
                                    }
                                    // "%_A_% AND %_B_% AND %C%"
                                    $nNichtErlaubteKlasse_arr = [3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF((" . $cSuchspalten . " LIKE '" . $cSuchTMP_arr[0] . "') AND (" . $cSuchspalten .
                                            " LIKE '" . $cSuchTMP_arr[1] . "') AND (" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[2] . "%'), " . $nPrio++ . ", ";
                                    }
                                    // "%_A_% AND %B% AND %_C_%"
                                    $nNichtErlaubteKlasse_arr = [3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF((" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . " %') AND (" . $cSuchspalten .
                                            " LIKE '%" . $cSuchTMP_arr[1] . "%') AND (" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[2] . " %'), " . $nPrio++ . ", ";
                                    }
                                    // "%_A_% AND %B% AND %C%"
                                    $nNichtErlaubteKlasse_arr = [3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF((" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . " %') AND (" . $cSuchspalten .
                                            " LIKE '%" . $cSuchTMP_arr[1] . "%') AND (" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[2] . "%'), " . $nPrio++ . ", ";
                                    }
                                    // "%A% AND %_B_% AND %_C_%"
                                    $nNichtErlaubteKlasse_arr = [3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF((" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . "%') AND (" . $cSuchspalten .
                                            " LIKE '% " . $cSuchTMP_arr[1] . " %') AND (" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[2] . " %'), " . $nPrio++ . ", ";
                                    }
                                    // "%A% AND %_B_% AND %C%"
                                    $nNichtErlaubteKlasse_arr = [3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF((" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . "%') AND (" . $cSuchspalten .
                                            " LIKE '% " . $cSuchTMP_arr[1] . " %') AND (" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[2] . "%'), " . $nPrio++ . ", ";
                                    }
                                    // "%A% AND %B% AND %_C_%"
                                    $nNichtErlaubteKlasse_arr = [3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF((" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . "%') AND (" . $cSuchspalten .
                                            " LIKE '%" . $cSuchTMP_arr[1] . "%') AND (" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[2] . " %'), " . $nPrio++ . ", ";
                                    }
                                    // "%A%B%C%"
                                    $nNichtErlaubteKlasse_arr = [3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF(" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . "%" . $cSuchTMP_arr[1] . "%" . $cSuchTMP_arr[2] . "%', " . $nPrio++ . ", ";
                                    }
                                    // "%A% AND %B% AND %C%"
                                    $nNichtErlaubteKlasse_arr = [3];
                                    if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                                        $nKlammern++;
                                        $cSQL .= "IF((" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . "%') AND (" . $cSuchspalten .
                                            " LIKE '%" . $cSuchTMP_arr[1] . "%') AND (" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[2] . "%'), " . $nPrio++ . ", ";
                                    }
                                    break;
                            }
                        }

                        if ($i == (count($cSuchspalten_arr) - 1)) {
                            $cSQL .= "254)";
                        }
                    }

                    for ($i = 0; $i < ($nKlammern - 1); $i++) {
                        $cSQL .= ")";
                    }

                    if (Shop::$kSprache > 0 && !standardspracheAktiv()) {
                        $cSQL .= " FROM tartikelsprache
                                        INNER JOIN tartikel 
                                            ON tartikelsprache.kArtikel = tartikel.kArtikel
                                        LEFT JOIN tartikelsichtbarkeit 
                                            ON tartikelsichtbarkeit.kArtikel = IF(tartikel.kVaterArtikel > 0, tartikel.kVaterArtikel, tartikelsprache.kArtikel)
                                            AND tartikelsichtbarkeit.kKundengruppe = " . ((int)$_SESSION['Kundengruppe']->kKundengruppe);
                    } else {
                        $cSQL .= " FROM tartikel
                                        LEFT JOIN tartikelsichtbarkeit 
                                            ON tartikelsichtbarkeit.kArtikel = IF(tartikel.kVaterArtikel > 0, tartikel.kVaterArtikel, tartikel.kArtikel)
                                            AND tartikelsichtbarkeit.kKundengruppe = " . ((int)$_SESSION['Kundengruppe']->kKundengruppe);
                    }

                    $cSQL .= " WHERE tartikelsichtbarkeit.kArtikel IS NULL " . gibLagerfilter() . " AND ";
                    if (Shop::$kSprache > 0 && !standardspracheAktiv()) {
                        $cSQL .= " tartikelsprache.kSprache = " . Shop::$kSprache . " AND (";
                    } else {
                        $cSQL .= "(";
                    }

                    foreach ($cSuchspalten_arr as $i => $cSuchspalten) {
                        if ($i > 0) {
                            $cSQL .= " OR";
                        }
                        $cSQL .= "(";

                        foreach ($cSuchTMP_arr as $j => $cSuch) {
                            if ($j > 0) {
                                $cSQL .= " AND";
                            }
                            $cSQL .= " " . $cSuchspalten . " LIKE '%" . $cSuch . "%'";
                        }
                        $cSQL .= ")";
                    }

                    $cSQL .= ")";
                }
                Shop::DB()->query("INSERT INTO tsuchcachetreffer $cSQL GROUP BY kArtikelTMP LIMIT " .
                    (int)$conf['artikeluebersicht']['suche_max_treffer'], 3);
            }

            return $kSuchCache;
        }
    }

    return 0;
}

/**
 * @param stdClass $oSuchCache
 * @param array $cSuchspalten_arr
 * @param array $cSuch_arr
 * @param int $nLimit
 * @param string $cFullText
 *
 * @return int
 */
function bearbeiteSuchCacheFulltext($oSuchCache, $cSuchspalten_arr, $cSuch_arr, $nLimit = 0, $cFullText = 'Y')
{
    $nLimit = (int)$nLimit;

    if ($oSuchCache->kSuchCache > 0) {
        $cArtikelSpalten_arr = array_map(function ($item) {
            $item_arr = explode('.', $item, 2);

            return 'tartikel.' . $item_arr[1];
        }, $cSuchspalten_arr);

        $cSprachSpalten_arr = array_filter($cSuchspalten_arr, function ($item) {
            return preg_match('/tartikelsprache\.(.*)/', $item) ? true : false;
        });

        $score = "MATCH (" . implode(', ', $cArtikelSpalten_arr) . ") AGAINST ('" . implode(' ', $cSuch_arr) . "' IN NATURAL LANGUAGE MODE)";
        if ($cFullText === 'B') {
            $match = "MATCH (" . implode(', ', $cArtikelSpalten_arr) . ") AGAINST ('" . implode('* ', $cSuch_arr) . "*' IN BOOLEAN MODE)";
        } else {
            $match = $score;
        }

        $cSQL = "SELECT {$oSuchCache->kSuchCache} AS kSuchCache,
                    IF(tartikel.kVaterArtikel > 0, tartikel.kVaterArtikel, tartikel.kArtikel) AS kArtikelTMP,
                    $score AS score
                    FROM tartikel
                    WHERE $match " . gibLagerfilter() . " ";

        if (Shop::$kSprache > 0 && !standardspracheAktiv()) {
            $score = "MATCH (" . implode(', ', $cSprachSpalten_arr) . ") AGAINST ('" . implode(' ', $cSuch_arr) . "' IN NATURAL LANGUAGE MODE)";
            if ($cFullText === 'B') {
                $score = "MATCH (" . implode(', ', $cSprachSpalten_arr) . ") AGAINST ('" . implode('* ', $cSuch_arr) . "*' IN BOOLEAN MODE)";
            } else {
                $match = $score;
            }

            $cSQL .= "UNION DISTINCT
                SELECT {$oSuchCache->kSuchCache} AS kSuchCache,
                    IF(tartikel.kVaterArtikel > 0, tartikel.kVaterArtikel, tartikel.kArtikel) AS kArtikelTMP,
                    $score AS score
                    FROM tartikel
                    INNER JOIN tartikelsprache ON tartikelsprache.kArtikel = tartikel.kArtikel
                    WHERE $match " . gibLagerfilter() . " ";
        }

        $cISQL = "INSERT INTO tsuchcachetreffer
                    SELECT kSuchCache, kArtikelTMP, ROUND(MAX(15 - score) * 10)
                    FROM ($cSQL) AS i
                    LEFT JOIN tartikelsichtbarkeit ON tartikelsichtbarkeit.kArtikel = i.kArtikelTMP
                        AND tartikelsichtbarkeit.kKundengruppe = " . ((int)$_SESSION['Kundengruppe']->kKundengruppe) . "
                    WHERE tartikelsichtbarkeit.kKundengruppe IS NULL
                    GROUP BY kSuchCache, kArtikelTMP" . ($nLimit > 0 ? " LIMIT $nLimit" : '');

        Shop::DB()->query($cISQL, 3);
    }

    return $oSuchCache->kSuchCache;
}

/**
 * @return bool
 */
function isFulltextIndexActive()
{
    static $active = null;

    if (!isset($active)) {
        $active = Shop::DB()->query("SHOW INDEX FROM tartikel WHERE KEY_NAME = 'idx_tartikel_fulltext'", 1)
            && Shop::DB()->query("SHOW INDEX FROM tartikelsprache WHERE KEY_NAME = 'idx_tartikelsprache_fulltext'", 1) ? true : false;
    }

    return $active;
}

/**
 * @param object $NaviFilter
 * @return stdClass
 */
function gibSuchFilterSQL($NaviFilter)
{
    $oFilter         = new stdClass();
    $oFilter->cJoin  = '';
    $oFilter->cWhere = '';

    if (isset($NaviFilter->Suche->kSuchCache) && $NaviFilter->Suche->kSuchCache > 0 || (isset($NaviFilter->SuchFilter) &&
            is_array($NaviFilter->SuchFilter) && count($NaviFilter->SuchFilter) > 0)) {
        if (isset($NaviFilter->Suche->kSuchCache) && $NaviFilter->Suche->kSuchCache > 0) {
            $oSuchFilter = new stdClass();
            if (isset($NaviFilter->Suche->kSuchanfrage)) {
                $oSuchFilter->kSuchanfrage = $NaviFilter->Suche->kSuchanfrage;
            }
            $oSuchFilter->kSuchCache  = $NaviFilter->Suche->kSuchCache;
            $oSuchFilter->cSuche      = $NaviFilter->Suche->cSuche;
            $NaviFilter->SuchFilter[] = $oSuchFilter;
        }

        $kSucheCache_arr = [];
        foreach ($NaviFilter->SuchFilter as $oSuchFilter) {
            $kSucheCache_arr[] = (int)$oSuchFilter->kSuchCache;
        }
        $oFilter->cJoin = " JOIN (
                            SELECT tsuchcachetreffer.kArtikel, tsuchcachetreffer.kSuchCache, MIN(tsuchcachetreffer.nSort) AS nSort
                            FROM tsuchcachetreffer
                            WHERE tsuchcachetreffer.kSuchCache IN (" . implode(',', $kSucheCache_arr) . ") GROUP BY tsuchcachetreffer.kArtikel
                            HAVING count(*) = " . count($NaviFilter->SuchFilter) . "
                            ) AS jSuche ON jSuche.kArtikel = tartikel.kArtikel";

        if (isset($GLOBALS['kSuchanfrage']) || isset(Shop::$kSuchanfrage)) {
            array_pop($NaviFilter->SuchFilter);
        }
    }

    return $oFilter;
}

/**
 * @param object $NaviFilter
 * @return stdClass
 */
function gibHerstellerFilterSQL($NaviFilter)
{
    $oFilter         = new stdClass();
    $oFilter->cJoin  = '';
    $oFilter->cWhere = '';
    // Hersteller Mainword?
    if (isset($NaviFilter->Hersteller->kHersteller) && $NaviFilter->Hersteller->kHersteller > 0) {
        $oFilter->cWhere = ' AND tartikel.kHersteller = ' . (int)$NaviFilter->Hersteller->kHersteller;
    }
    // Hersteller Filter?
    if (isset($NaviFilter->HerstellerFilter->kHersteller) && $NaviFilter->HerstellerFilter->kHersteller > 0) {
        $oFilter->cWhere = ' AND tartikel.kHersteller = ' . (int)$NaviFilter->HerstellerFilter->kHersteller;
    }

    return $oFilter;
}

/**
 * @param object $NaviFilter
 * @return stdClass
 */
function gibKategorieFilterSQL($NaviFilter)
{
    $conf            = Shop::getSettings([CONF_NAVIGATIONSFILTER]);
    $oFilter         = new stdClass();
    $oFilter->cJoin  = '';
    $oFilter->cWhere = '';
    // Kategorie Mainword?
    if (isset($NaviFilter->Kategorie->kKategorie) && $NaviFilter->Kategorie->kKategorie > 0) {
        $oFilter->cJoin  = 'JOIN tkategorieartikel ON tartikel.kArtikel = tkategorieartikel.kArtikel';
        $oFilter->cWhere = ' AND tkategorieartikel.kKategorie = ' . (int)$NaviFilter->Kategorie->kKategorie;
    }
    // Kategorie Filter?
    if (isset($NaviFilter->KategorieFilter->kKategorie) && $NaviFilter->KategorieFilter->kKategorie > 0) {
        $oFilter->cJoin  = 'JOIN tkategorieartikel ON tartikel.kArtikel = tkategorieartikel.kArtikel';
        $oFilter->cWhere = ' AND tkategorieartikel.kKategorie = ' . (int)$NaviFilter->KategorieFilter->kKategorie;
        if ($conf['navigationsfilter']['kategoriefilter_anzeigen_als'] === 'HF') {
            $oFilter->cJoin  = 'JOIN (
                SELECT tkategorieartikel.kArtikel, oberkategorie.kOberKategorie, oberkategorie.kKategorie
                FROM tkategorieartikel
                INNER JOIN tkategorie ON tkategorie.kKategorie = tkategorieartikel.kKategorie
                INNER JOIN tkategorie oberkategorie ON tkategorie.lft BETWEEN oberkategorie.lft AND oberkategorie.rght
                ) tkategorieartikelgesamt ON tartikel.kArtikel = tkategorieartikelgesamt.kArtikel';
            $oFilter->cWhere = ' AND (tkategorieartikelgesamt.kOberKategorie = ' . (int)$NaviFilter->KategorieFilter->kKategorie . ' 
                                    OR tkategorieartikelgesamt.kKategorie = ' . (int)$NaviFilter->KategorieFilter->kKategorie . ') ';
        }
    }

    return $oFilter;
}

/**
 * @param object $NaviFilter
 * @return stdClass
 */
function gibBewertungSterneFilterSQL($NaviFilter)
{
    $oFilter         = new stdClass();
    $oFilter->cJoin  = '';
    $oFilter->cWhere = '';
    // BewertungSterne Filter?
    if (isset($NaviFilter->BewertungFilter->nSterne) && $NaviFilter->BewertungFilter->nSterne > 0) {
        $oFilter->cJoin  = 'JOIN tartikelext ON tartikel.kArtikel = tartikelext.kArtikel';
        $oFilter->cWhere = ' AND round(tartikelext.fDurchschnittsBewertung, 0) >= ' . (int)$NaviFilter->BewertungFilter->nSterne;
    }

    return $oFilter;
}

/**
 * @param object $NaviFilter
 * @return stdClass
 */
function gibPreisspannenFilterSQL($NaviFilter)
{
    $oFilter         = new stdClass();
    $oFilter->cJoin  = '';
    $oFilter->cWhere = '';
    // Preisspannen Filter?
    if (isset($NaviFilter->PreisspannenFilter->fVon, $NaviFilter->PreisspannenFilter->fBis) &&
        $NaviFilter->PreisspannenFilter->fVon >= 0 &&
        $NaviFilter->PreisspannenFilter->fBis > 0
    ) {
        $oFilter->cJoin = "JOIN tpreise 
                                ON tartikel.kArtikel = tpreise.kArtikel 
                                AND tpreise.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                            LEFT JOIN tartikelkategorierabatt 
                                ON tartikelkategorierabatt.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                                AND tartikelkategorierabatt.kArtikel = tartikel.kArtikel
                            LEFT JOIN tartikelsonderpreis 
                                ON tartikelsonderpreis.kArtikel = tartikel.kArtikel
                                AND tartikelsonderpreis.cAktiv = 'Y'
                                AND tartikelsonderpreis.dStart <= now()
                                AND (tartikelsonderpreis.dEnde >= CURDATE() OR tartikelsonderpreis.dEnde = '0000-00-00')
                            LEFT JOIN tsonderpreise 
                                ON tartikelsonderpreis.kArtikelSonderpreis = tsonderpreise.kArtikelSonderpreis
                                AND tsonderpreise.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe;
        $oFilter->cWhere .= " AND";

        $fKundenrabatt = 0.0;
        if (isset($_SESSION['Kunde']->fRabatt) && $_SESSION['Kunde']->fRabatt > 0) {
            $fKundenrabatt = $_SESSION['Kunde']->fRabatt;
        }

        $nSteuersatzKeys_arr = array_keys($_SESSION['Steuersatz']);
        // bis
        if (isset($_SESSION['Kundengruppe']->nNettoPreise) && (int)$_SESSION['Kundengruppe']->nNettoPreise > 0) {
            $oFilter->cWhere .= " ROUND(LEAST((tpreise.fVKNetto * " .
                $_SESSION['Waehrung']->fFaktor . ") * ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), " .
                $_SESSION['Kundengruppe']->fRabatt . ", " . $fKundenrabatt .
                ", 0)) / 100), IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * " .
                $_SESSION['Waehrung']->fFaktor . "))), 2)";
        } else {
            foreach ($nSteuersatzKeys_arr as $nSteuersatzKeys) {
                $fSteuersatz = (float)$_SESSION['Steuersatz'][$nSteuersatzKeys];
                $oFilter->cWhere .= " IF(tartikel.kSteuerklasse = " . $nSteuersatzKeys . ",
                            ROUND(LEAST(tpreise.fVKNetto * ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), " .
                    $_SESSION['Kundengruppe']->fRabatt . ", " .
                    $fKundenrabatt . ", 0)) / 100), IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * " .
                    $_SESSION['Waehrung']->fFaktor . "))) * ((100 + " . $fSteuersatz . ") / 100
                        ), 2),";
            }
        }

        if ((int)$_SESSION['Kundengruppe']->nNettoPreise === 0) {
            $oFilter->cWhere .= "0";

            $count = count($nSteuersatzKeys_arr);
            for ($x = 0; $x < $count; $x++) {
                $oFilter->cWhere .= ")";
            }
        }
        $oFilter->cWhere .= " < " . $NaviFilter->PreisspannenFilter->fBis . " AND ";
        // von
        if ((int)$_SESSION['Kundengruppe']->nNettoPreise > 0) {
            $oFilter->cWhere .= " ROUND(LEAST(tpreise.fVKNetto * ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), " .
                $_SESSION['Kundengruppe']->fRabatt . ", " . $fKundenrabatt .
                ", 0)) / 100), IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * " .
                $_SESSION['Waehrung']->fFaktor . "))), 2)";
        } else {
            foreach ($nSteuersatzKeys_arr as $nSteuersatzKeys) {
                $fSteuersatz = (float)$_SESSION['Steuersatz'][$nSteuersatzKeys];
                $oFilter->cWhere .= " IF(tartikel.kSteuerklasse = " . $nSteuersatzKeys . ",
                            ROUND(LEAST(tpreise.fVKNetto * ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), " .
                    $_SESSION['Kundengruppe']->fRabatt . ", " . $fKundenrabatt .
                    ", 0)) / 100), IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * " .
                    $_SESSION['Waehrung']->fFaktor . "))) * ((100 + " . $fSteuersatz . ") / 100
                        ), 2),";
            }
        }
        if ((int)$_SESSION['Kundengruppe']->nNettoPreise === 0) {
            $oFilter->cWhere .= "0";
            $count = count($nSteuersatzKeys_arr);
            for ($x = 0; $x < $count; $x++) {
                $oFilter->cWhere .= ")";
            }
        }
        $oFilter->cWhere .= " >= " . $NaviFilter->PreisspannenFilter->fVon;
    }

    return $oFilter;
}

/**
 * @param object $NaviFilter
 * @return stdClass
 */
function gibTagFilterSQL($NaviFilter)
{
    $oFilter         = new stdClass();
    $oFilter->cJoin  = '';
    $oFilter->cWhere = '';
    // Tag Mainword?
    if (isset($NaviFilter->Tag->kTag) && $NaviFilter->Tag->kTag > 0) {
        $oFilter->cJoin = "    JOIN ttagartikel ON tartikel.kArtikel = ttagartikel.kArtikel
                            JOIN ttag ON ttagartikel.kTag = ttag.kTag";
        $oFilter->cWhere = "    AND ttag.nAktiv = 1
                                AND ttagartikel.kTag = " . (int)$NaviFilter->Tag->kTag;
    }
    // Tag Filter?
    if (isset($NaviFilter->TagFilter) && is_array($NaviFilter->TagFilter) && count($NaviFilter->TagFilter) > 0) {
        $kTag_arr = [];
        foreach ($NaviFilter->TagFilter as $oTag) {
            $kTag_arr[] = (int)$oTag->kTag;
        }
        $oFilter->cJoin = "    JOIN ttagartikel ON tartikel.kArtikel = ttagartikel.kArtikel
                            JOIN ttag ON ttagartikel.kTag = ttag.kTag";
        $oFilter->cWhere .= "    AND ttag.nAktiv = 1
                                AND ttagartikel.kTag IN (" . implode(',', $kTag_arr) . ")";
    }

    return $oFilter;
}

/**
 * @param object $NaviFilter
 * @return stdClass
 */
function gibMerkmalFilterSQL($NaviFilter)
{
    $oFilter          = new stdClass();
    $oFilter->cJoin   = '';
    $oFilter->cWhere  = '';
    $oFilter->cHaving = '';
    $kMerkmalWert_arr = [];
    if (isset($NaviFilter->MerkmalFilter) && is_array($NaviFilter->MerkmalFilter)) {
        foreach ($NaviFilter->MerkmalFilter as $oMerkmalWert) {
            $oMerkmalWert->kMerkmalWert = (int)$oMerkmalWert->kMerkmalWert;
            if (!in_array($oMerkmalWert->kMerkmalWert, $kMerkmalWert_arr, true)) {
                $kMerkmalWert_arr[] = $oMerkmalWert->kMerkmalWert;
            }
        }
        if (isset($NaviFilter->MerkmalWert->kMerkmalWert) &&
            $NaviFilter->MerkmalWert->kMerkmalWert > 0 &&
            !in_array((int)$NaviFilter->MerkmalWert->kMerkmalWert, $kMerkmalWert_arr, true)
        ) {
            $kMerkmalWert_arr[] = (int)$NaviFilter->MerkmalWert->kMerkmalWert;
        }
    }
    // Merkmal Filter?
    if (is_array($kMerkmalWert_arr) && count($kMerkmalWert_arr) > 0) {
        $oFilter->cJoin = "JOIN (
                                SELECT kArtikel
                                FROM tartikelmerkmal
                                WHERE kMerkmalWert IN (" . implode(',', $kMerkmalWert_arr) . ")
                                GROUP BY tartikelmerkmal.kArtikel
                                HAVING count(*) = " . count($kMerkmalWert_arr) . "
                                ) AS tmerkmaljoin ON tmerkmaljoin.kArtikel = tartikel.kArtikel ";

        $oFilter->cJoinMMW = " JOIN (
                                    SELECT kArtikel
                                    FROM tartikelmerkmal
                                    WHERE kMerkmalWert IN (" . implode(',', $kMerkmalWert_arr) . " )
                                    GROUP BY kArtikel
                                    HAVING count(*) = " . count($kMerkmalWert_arr) . "
                                    ) AS ssj1 ON tartikel.kArtikel = ssj1.kArtikel";

        $oFilter->cHavingCount = count($kMerkmalWert_arr);
        $oFilter->cHavingMMW   = "HAVING count(*) >= " . count($kMerkmalWert_arr);
    }

    return $oFilter;
}

/**
 * @param object $NaviFilter
 * @return stdClass
 */
function gibSuchspecialFilterSQL($NaviFilter)
{
    $oFilter         = new stdClass();
    $oFilter->cJoin  = '';
    $oFilter->cWhere = '';
    // Suchspecial Mainword?
    if (isset($NaviFilter->Suchspecial->kKey) && $NaviFilter->Suchspecial->kKey > 0 || isset($NaviFilter->SuchspecialFilter->kKey) && $NaviFilter->SuchspecialFilter->kKey > 0) {
        $kKey = isset($NaviFilter->Suchspecial->kKey) ? $NaviFilter->Suchspecial->kKey : null;
        if (isset($NaviFilter->SuchspecialFilter->kKey) && $NaviFilter->SuchspecialFilter->kKey > 0) {
            $kKey = $NaviFilter->SuchspecialFilter->kKey;
        }
        $conf = Shop::getSettings([CONF_BOXEN, CONF_GLOBAL]);
        switch ($kKey) {
            case SEARCHSPECIALS_BESTSELLER:
                $nAnzahl = (isset($conf['global']['global_bestseller_minanzahl'])
                    && (int)$conf['global']['global_bestseller_minanzahl'] > 0)
                    ? (int)$conf['global']['global_bestseller_minanzahl']
                    : 100;
                $oFilter->cJoin  = "JOIN tbestseller ON tbestseller.kArtikel = tartikel.kArtikel";
                $oFilter->cWhere = " AND round(tbestseller.fAnzahl) >= " . $nAnzahl;
                break;

            case SEARCHSPECIALS_SPECIALOFFERS:
                $tasp = 'tartikelsonderpreis';
                $tsp  = 'tsonderpreise';
                if ((!isset($NaviFilter->PreisspannenFilter->fVon, $NaviFilter->PreisspannenFilter->fBis)) ||
                    (!$NaviFilter->PreisspannenFilter->fVon && !$NaviFilter->PreisspannenFilter->fBis)) {
                    $oFilter->cJoin = "JOIN tartikelsonderpreis AS tasp ON tasp.kArtikel = tartikel.kArtikel
                                        JOIN tsonderpreise AS tsp ON tsp.kArtikelSonderpreis = tasp.kArtikelSonderpreis";
                    $tasp = 'tasp';
                    $tsp  = 'tsp';
                }
                $oFilter->cWhere = " AND " . $tasp . " .kArtikel = tartikel.kArtikel
                                    AND " . $tasp . ".cAktiv = 'Y' AND " . $tasp . ".dStart <= now()
                                    AND (" . $tasp . ".dEnde >= curdate() OR " . $tasp . ".dEnde = '0000-00-00')
                                    AND " . $tsp . " .kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe;
                $oFilter->tasp = $tasp;
                $oFilter->tsp  = $tsp;

                break;

            case SEARCHSPECIALS_NEWPRODUCTS:
                $alter_tage      = ($conf['boxen']['box_neuimsortiment_alter_tage'] > 0) ? (int)$conf['boxen']['box_neuimsortiment_alter_tage'] : 30;
                $oFilter->cJoin  = '';
                $oFilter->cWhere = " AND tartikel.cNeu = 'Y' AND DATE_SUB(now(),INTERVAL $alter_tage DAY) < tartikel.dErstellt
                                    AND tartikel.cNeu = 'Y'";
                break;

            case SEARCHSPECIALS_TOPOFFERS:
                $oFilter->cJoin  = '';
                $oFilter->cWhere = " AND tartikel.cTopArtikel = 'Y'";
                break;

            case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                $oFilter->cJoin  = '';
                $oFilter->cWhere = " AND now() < tartikel.dErscheinungsdatum";
                break;

            case SEARCHSPECIALS_TOPREVIEWS:
                if (!isset($NaviFilter->BewertungFilter->nSterne) || !$NaviFilter->BewertungFilter->nSterne) {
                    $nMindestSterne  = ((int)$conf['boxen']['boxen_topbewertet_minsterne'] > 0)
                        ? (int)$conf['boxen']['boxen_topbewertet_minsterne']
                        : 4;
                    $oFilter->cJoin  = "JOIN tartikelext AS taex ON taex.kArtikel = tartikel.kArtikel";
                    $oFilter->cWhere = " AND round(taex.fDurchschnittsBewertung) >= " . $nMindestSterne;
                }
                break;

            default:
                break;
        }
    }

    return $oFilter;
}

/**
 * @param object $NaviFilter
 * @return stdClass
 */
function gibArtikelAttributFilterSQL($NaviFilter)
{
    $oFilter         = new stdClass();
    $oFilter->cJoin  = '';
    $oFilter->cWhere = '';
    // Tag Mainword?
    if (isset($NaviFilter->ArtikelAttributFilter->cArtAttrib) &&
        strlen($NaviFilter->ArtikelAttributFilter->cArtAttrib) > 0
    ) {
        $oFilter->cJoin = " JOIN tartikelattribut 
                                ON tartikelattribut.kArtikel = tartikelattribut.kArtikel
                                AND tartikelattribut.cName = '" .
            StringHandler::filterXSS($NaviFilter->ArtikelAttributFilter->cArtAttrib) . "'";
    }

    return $oFilter;
}

/**
 * @param array $oMerkmalauswahl_arr
 * @param int   $kMerkmal
 * @return int
 */
function gibMerkmalPosition($oMerkmalauswahl_arr, $kMerkmal)
{
    if (is_array($oMerkmalauswahl_arr)) {
        foreach ($oMerkmalauswahl_arr as $i => $oMerkmalauswahl) {
            if ($oMerkmalauswahl->kMerkmal == $kMerkmal) {
                return $i;
            }
        }
    }

    return -1;
}

/**
 * @param array $oMerkmalauswahl_arr
 * @param int   $kMerkmalWert
 * @return bool
 */
function checkMerkmalWertVorhanden($oMerkmalauswahl_arr, $kMerkmalWert)
{
    if (is_array($oMerkmalauswahl_arr)) {
        foreach ($oMerkmalauswahl_arr as $i => $oMerkmalauswahl) {
            if ($oMerkmalauswahl->kMerkmalWert == $kMerkmalWert) {
                return true;
            }
        }
    }

    return false;
}

/**
 * @param object $NaviFilter
 * @return string
 */
function gibArtikelsortierung($NaviFilter)
{
    $conf              = Shop::getSettings([CONF_ARTIKELUEBERSICHT]);
    $Artikelsortierung = $conf['artikeluebersicht']['artikeluebersicht_artikelsortierung'];

    if (isset($_SESSION['Usersortierung'])) {
        $Artikelsortierung          = mappeUsersortierung($_SESSION['Usersortierung']);
        $_SESSION['Usersortierung'] = $Artikelsortierung;
    }
    if (isset($NaviFilter->nSortierung) && $NaviFilter->nSortierung > 0 && (int)$_SESSION['Usersortierung'] === 100) {
        $Artikelsortierung = $NaviFilter->nSortierung;
    }
    $sort = 'tartikel.nSort, tartikel.cName';
    switch ((int)$Artikelsortierung) {
        case SEARCH_SORT_STANDARD:
            $sort = 'tartikel.nSort, tartikel.cName';
            if (isset($NaviFilter->Kategorie) && $NaviFilter->Kategorie->kKategorie > 0) {
                $sort = 'tartikel.nSort, tartikel.cName';
            } elseif (isset($NaviFilter->Suche->kSuchCache, $_SESSION['Usersortierung']) &&
                $NaviFilter->Suche->kSuchCache > 0
                && (int)$_SESSION['Usersortierung'] === 100
            ) {
                $sort = 'tsuchcachetreffer.nSort';
            }
            break;
        case SEARCH_SORT_NAME_ASC:
            $sort = 'tartikel.cName';
            break;
        case SEARCH_SORT_NAME_DESC:
            $sort = 'tartikel.cName DESC';
            break;
        case SEARCH_SORT_PRICE_ASC:
            $sort = 'tpreise.fVKNetto, tartikel.cName';
            break;
        case SEARCH_SORT_PRICE_DESC:
            $sort = 'tpreise.fVKNetto DESC, tartikel.cName';
            break;
        case SEARCH_SORT_EAN:
            $sort = 'tartikel.cBarcode, tartikel.cName';
            break;
        case SEARCH_SORT_NEWEST_FIRST:
            $sort = 'tartikel.dErstellt DESC, tartikel.cName';
            break;
        case SEARCH_SORT_PRODUCTNO:
            $sort = 'tartikel.cArtNr, tartikel.cName';
            break;
        case SEARCH_SORT_AVAILABILITY:
            $sort = 'tartikel.fLagerbestand DESC, tartikel.cLagerKleinerNull DESC, tartikel.cName';
            break;
        case SEARCH_SORT_WEIGHT:
            $sort = 'tartikel.fGewicht, tartikel.cName';
            break;
        case SEARCH_SORT_DATEOFISSUE:
            $sort = 'tartikel.dErscheinungsdatum DESC, tartikel.cName';
            break;
        case SEARCH_SORT_BESTSELLER:
            $sort = 'tbestseller.fAnzahl DESC, tartikel.cName';
            break;
        case SEARCH_SORT_RATING:
            $sort = 'tbewertung.nSterne DESC, tartikel.cName';
            break;
        default:
            break;
    }

    return $sort;
}

/**
 * Die Usersortierung kann entweder ein Integer sein oder via Kategorieattribut ein String
 *
 * @param string $nUsersortierung
 * @return int
 */
function mappeUsersortierung($nUsersortierung)
{
    // Ist die Usersortierung ein Integer => Return direkt den Integer
    preg_match('/[0-9]+/', $nUsersortierung, $cTreffer_arr);
    if (isset($cTreffer_arr[0]) && strlen($nUsersortierung) === strlen($cTreffer_arr[0])) {
        return $nUsersortierung;
    }
    // Usersortierung ist ein String aus einem Kategorieattribut
    switch (strtolower($nUsersortierung)) {
        case SEARCH_SORT_CRITERION_NAME:
            return SEARCH_SORT_NAME_ASC;
            break;

        case SEARCH_SORT_CRITERION_NAME_ASC:
            return SEARCH_SORT_NAME_ASC;
            break;

        case SEARCH_SORT_CRITERION_NAME_DESC:
            return SEARCH_SORT_NAME_DESC;
            break;

        case SEARCH_SORT_CRITERION_PRODUCTNO:
            return SEARCH_SORT_PRODUCTNO;
            break;

        case SEARCH_SORT_CRITERION_AVAILABILITY:
            return SEARCH_SORT_AVAILABILITY;
            break;

        case SEARCH_SORT_CRITERION_WEIGHT:
            return SEARCH_SORT_WEIGHT;
            break;

        case SEARCH_SORT_CRITERION_PRICE:
            return SEARCH_SORT_PRICE_ASC;
            break;

        case SEARCH_SORT_CRITERION_PRICE_ASC:
            return SEARCH_SORT_PRICE_ASC;
            break;

        case SEARCH_SORT_CRITERION_PRICE_DESC:
            return SEARCH_SORT_PRICE_DESC;
            break;

        case SEARCH_SORT_CRITERION_EAN:
            return SEARCH_SORT_EAN;
            break;

        case SEARCH_SORT_CRITERION_NEWEST_FIRST:
            return SEARCH_SORT_NEWEST_FIRST;
            break;

        case SEARCH_SORT_CRITERION_DATEOFISSUE:
            return SEARCH_SORT_DATEOFISSUE;
            break;

        case SEARCH_SORT_CRITERION_BESTSELLER:
            return SEARCH_SORT_BESTSELLER;
            break;

        case SEARCH_SORT_CRITERION_RATING:
            return SEARCH_SORT_RATING;

        default:
            return SEARCH_SORT_STANDARD;
            break;
    }
}

/**
 * @param object $NaviFilter
 * @param bool   $bSeo
 * @param object $oZusatzFilter
 * @param int    $kSprache
 * @param bool   $bCanonical
 * @return string
 */
function gibNaviURL($NaviFilter, $bSeo, $oZusatzFilter, $kSprache = 0, $bCanonical = false)
{
    if (!$kSprache) {
        $kSprache = Shop::$kSprache;
    }
    if (!$kSprache) {
        $oSprache = Shop::DB()->query(
            "SELECT kSprache
                FROM tsprache
                WHERE cShopStandard = 'Y'", 1
        );
        $kSprache = $oSprache->kSprache;
    }
    $kSprache = (int)$kSprache;
    $cSEOURL  = Shop::getURL() . '/';
    // Gibt es zu der Suche bereits eine Suchanfrage und wird nicht ExtendedJTLSearch verwendet?
    if (!(isset($NaviFilter->Suche->bExtendedJTLSearch) &&
            $NaviFilter->Suche->bExtendedJTLSearch) &&
        isset($NaviFilter->Suche->cSuche) &&
        strlen($NaviFilter->Suche->cSuche) > 0
    ) {
        $oSuchanfrage = Shop::DB()->select(
            'tsuchanfrage',
            'cSuche', Shop::DB()->escape($NaviFilter->Suche->cSuche),
            'kSprache', $kSprache,
            'nAktiv', 1,
            false,
            'kSuchanfrage'
        );
        if (isset($oSuchanfrage->kSuchanfrage) && $oSuchanfrage->kSuchanfrage > 0) {
            // Hole alle aktiven Sprachen
            $oSprache_arr = $NaviFilter->oSprache_arr;
            $bSprache     = (is_array($oSprache_arr) && count($oSprache_arr) > 0);
            $oSeo_arr     = Shop::DB()->selectAll(
                'tseo',
                ['cKey', 'kKey'],
                ['kSuchanfrage', (int)$oSuchanfrage->kSuchanfrage],
                'cSeo, kSprache',
                'kSprache'
            );
            if ($bSprache) {
                foreach ($oSprache_arr as $oSprache) {
                    $NaviFilter->Suchanfrage->cSeo[$oSprache->kSprache] = '';
                    if (is_array($oSeo_arr) && count($oSeo_arr) > 0) {
                        foreach ($oSeo_arr as $oSeo) {
                            if ($oSprache->kSprache == $oSeo->kSprache) {
                                $NaviFilter->Suchanfrage->cSeo[$oSprache->kSprache] = $oSeo->cSeo;
                            }
                        }
                    }
                }
            }

            $NaviFilter->Suchanfrage->kSuchanfrage = $oSuchanfrage->kSuchanfrage;
        }
    }
    // Falls Sort, Artikelanz, Preis, Bewertung oder Tag Filter gesetzt wurde
    if ((isset($NaviFilter->PreisspannenFilter->fVon) && isset($NaviFilter->PreisspannenFilter->fBis) &&
            $NaviFilter->PreisspannenFilter->fVon >= 0 && $NaviFilter->PreisspannenFilter->fBis > 0) ||
        (isset($NaviFilter->BewertungFilter->nSterne) && $NaviFilter->BewertungFilter->nSterne > 0) ||
        (isset($NaviFilter->SuchFilter->kSuchanfrage) && $NaviFilter->SuchFilter->kSuchanfrage > 0) ||
        (isset($NaviFilter->SuchFilter) && count($NaviFilter->SuchFilter) > 0) &&
        (!isset($NaviFilter->EchteSuche->cSuche) || strlen($NaviFilter->EchteSuche->cSuche) === 0) ||
        (isset($NaviFilter->TagFilter) && count($NaviFilter->TagFilter) > 0) || (isset($NaviFilter->SuchspecialFilter->kKey) && $NaviFilter->SuchspecialFilter->kKey > 0) ||
        (isset($oZusatzFilter->PreisspannenFilter->fVon) && isset($oZusatzFilter->PreisspannenFilter->fBis) &&
            $oZusatzFilter->PreisspannenFilter->fVon >= 0 && $oZusatzFilter->PreisspannenFilter->fBis > 0) ||
        (isset($oZusatzFilter->SuchspecialFilter->kKey) && $oZusatzFilter->SuchspecialFilter->kKey > 0) ||
        (isset($oZusatzFilter->BewertungFilter->nSterne) && $oZusatzFilter->BewertungFilter->nSterne > 0) ||
        (isset($oZusatzFilter->TagFilter->kTag) && $oZusatzFilter->TagFilter->kTag > 0) ||
        (!isset($NaviFilter->Suchanfrage->kSuchanfrage) && (isset($NaviFilter->Suche->cSuche) && strlen($NaviFilter->Suche->cSuche) > 0) ||
            (isset($oZusatzFilter->SuchspecialFilter->kKey) && $oZusatzFilter->SuchspecialFilter->kKey > 0) ||
            (isset($oZusatzFilter->SuchFilter->kSuchanfrage) && $oZusatzFilter->SuchFilter->kSuchanfrage > 0))
    ) {
        $bSeo = false;
    }
    $cURL = $cSEOURL . 'navi.php?';
    // Mainwords
    if (isset($NaviFilter->Kategorie->kKategorie) && $NaviFilter->Kategorie->kKategorie > 0) {
        if (!isset($NaviFilter->Kategorie->cSeo[$kSprache]) || strlen($NaviFilter->Kategorie->cSeo[$kSprache]) === 0) {
            $bSeo = false;
        } else {
            $cSEOURL .= $NaviFilter->Kategorie->cSeo[$kSprache];
        }
        $cURL .= 'k=' . (int)$NaviFilter->Kategorie->kKategorie;
    } elseif (isset($NaviFilter->Hersteller->kHersteller) && $NaviFilter->Hersteller->kHersteller > 0) {
        $cSEOURL .= $NaviFilter->Hersteller->cSeo[$kSprache];
        if (strlen($NaviFilter->Hersteller->cSeo[$kSprache]) === 0) {
            $bSeo = false;
        }
        $cURL .= 'h=' . (int)$NaviFilter->Hersteller->kHersteller;
    } elseif (isset($NaviFilter->Suchanfrage->kSuchanfrage) && $NaviFilter->Suchanfrage->kSuchanfrage > 0) {
        $cSEOURL .= $NaviFilter->Suchanfrage->cSeo[$kSprache];
        if (strlen($NaviFilter->Suchanfrage->cSeo[$kSprache]) === 0) {
            $bSeo = false;
        }
        $cURL .= 'l=' . (int)$NaviFilter->Suchanfrage->kSuchanfrage;
    } elseif (isset($NaviFilter->MerkmalWert->kMerkmalWert) && $NaviFilter->MerkmalWert->kMerkmalWert > 0) {
        $cSEOURL .= $NaviFilter->MerkmalWert->cSeo[$kSprache];
        if (strlen($NaviFilter->MerkmalWert->cSeo[$kSprache]) === 0) {
            $bSeo = false;
        }
        $cURL .= 'm=' . (int)$NaviFilter->MerkmalWert->kMerkmalWert;
    } elseif (isset($NaviFilter->Tag->kTag) && $NaviFilter->Tag->kTag > 0) {
        $cSEOURL .= $NaviFilter->Tag->cSeo[$kSprache];
        if (strlen($NaviFilter->Tag->cSeo[$kSprache]) === 0) {
            $bSeo = false;
        }
        $cURL .= 't=' . (int)$NaviFilter->Tag->kTag;
    } elseif (isset($NaviFilter->Suchspecial->kKey) && $NaviFilter->Suchspecial->kKey > 0) {
        $cSEOURL .= $NaviFilter->Suchspecial->cSeo[$kSprache];
        if (strlen($NaviFilter->Suchspecial->cSeo[$kSprache]) === 0) {
            $bSeo = false;
        }
        $cURL .= 'q=' . (int)$NaviFilter->Suchspecial->kKey;
    } elseif (isset($NaviFilter->News->kNews) && $NaviFilter->News->kNews > 0) {
        $cSEOURL .= $NaviFilter->News->cSeo[$kSprache];
        if (strlen($NaviFilter->News->cSeo[$kSprache]) === 0) {
            $bSeo = false;
        }
        $cURL .= 'n=' . (int)$NaviFilter->News->kNews;
    } elseif (isset($NaviFilter->NewsMonat->kNewsMonatsUebersicht) && $NaviFilter->NewsMonat->kNewsMonatsUebersicht > 0) {
        $cSEOURL .= $NaviFilter->NewsMonat->cSeo[$kSprache];
        if (strlen($NaviFilter->NewsMonat->cSeo[$kSprache]) === 0) {
            $bSeo = false;
        }
        $cURL .= 'nm=' . (int)$NaviFilter->NewsMonat->kNewsMonatsUebersicht;
    } elseif (isset($NaviFilter->NewsKategorie->kNewsKategorie) && $NaviFilter->NewsKategorie->kNewsKategorie > 0) {
        $cSEOURL .= $NaviFilter->NewsKategorie->cSeo[$kSprache];
        if (strlen($NaviFilter->NewsKategorie->cSeo[$kSprache]) === 0) {
            $bSeo = false;
        }
        $cURL .= 'nk=' . (int)$NaviFilter->NewsKategorie->kNewsKategorie;
    }
    if ((isset($NaviFilter->EchteSuche->cSuche) && strlen($NaviFilter->EchteSuche->cSuche) > 0) &&
        (!isset($NaviFilter->Suchanfrage->kSuchanfrage) || (int)$NaviFilter->Suchanfrage->kSuchanfrage === 0)
    ) {
        $bSeo = false;
        $cURL .= 'suche=' . urlencode($NaviFilter->EchteSuche->cSuche);
    }
    // Filter
    // Kategorie
    if (!$bCanonical) {
        if (isset($NaviFilter->KategorieFilter->kKategorie) && $NaviFilter->KategorieFilter->kKategorie > 0 &&
            (!isset($NaviFilter->Kategorie->kKategorie) || $NaviFilter->Kategorie->kKategorie != $NaviFilter->KategorieFilter->kKategorie)
        ) {
            if (!isset($oZusatzFilter->FilterLoesen->Kategorie) || !$oZusatzFilter->FilterLoesen->Kategorie) {
                if (strlen($NaviFilter->KategorieFilter->cSeo[$kSprache]) === 0) {
                    $bSeo = false;
                }
                $conf = Shop::getSettings([CONF_NAVIGATIONSFILTER]);
                if ($conf['navigationsfilter']['kategoriefilter_anzeigen_als'] === 'HF' && !empty($oZusatzFilter->KategorieFilter->kKategorie)) {
                    if (!empty($oZusatzFilter->KategorieFilter->cSeo)) {
                        $cSEOURL .= SEP_KAT . $oZusatzFilter->KategorieFilter->cSeo;
                    } else {
                        $cSEOURL .= SEP_KAT . $NaviFilter->KategorieFilter->cSeo[$kSprache];
                    }
                    $cURL .= '&amp;kf=' . (int)$oZusatzFilter->KategorieFilter->kKategorie;
                } else {
                    $cSEOURL .= SEP_KAT . $NaviFilter->KategorieFilter->cSeo[$kSprache];
                    $cURL .= '&amp;kf=' . (int)$NaviFilter->KategorieFilter->kKategorie;
                }
            }
        } elseif ((isset($oZusatzFilter->KategorieFilter->kKategorie) && $oZusatzFilter->KategorieFilter->kKategorie > 0) &&
            (!isset($NaviFilter->Kategorie->kKategorie) || $NaviFilter->Kategorie->kKategorie != $oZusatzFilter->KategorieFilter->kKategorie)
        ) {
            $cSEOURL .= SEP_KAT . $oZusatzFilter->KategorieFilter->cSeo;
            $cURL .= '&amp;kf=' . (int)$oZusatzFilter->KategorieFilter->kKategorie;
        }
        // Hersteller
        if ((isset($NaviFilter->HerstellerFilter->kHersteller) && $NaviFilter->HerstellerFilter->kHersteller > 0) &&
            (!isset($NaviFilter->Hersteller->kHersteller) || $NaviFilter->Hersteller->kHersteller != $NaviFilter->HerstellerFilter->kHersteller)
        ) {
            if (!isset($oZusatzFilter->FilterLoesen->Hersteller) || !$oZusatzFilter->FilterLoesen->Hersteller) {
                $cSEOURL .= SEP_HST . $NaviFilter->HerstellerFilter->cSeo[$kSprache];
                if (strlen($NaviFilter->HerstellerFilter->cSeo[$kSprache]) === 0) {
                    $bSeo = false;
                }
                $cURL .= '&amp;hf=' . (int)$NaviFilter->HerstellerFilter->kHersteller;
            }
        } elseif ((isset($oZusatzFilter->HerstellerFilter->kHersteller) && $oZusatzFilter->HerstellerFilter->kHersteller > 0) &&
            (!isset($NaviFilter->Hersteller->kHersteller) || $NaviFilter->Hersteller->kHersteller != $oZusatzFilter->HerstellerFilter->kHersteller)
        ) {
            $cSEOURL .= SEP_HST . $oZusatzFilter->HerstellerFilter->cSeo;
            $cURL .= '&amp;hf=' . (int)$oZusatzFilter->HerstellerFilter->kHersteller;
        }
        // Suche
        $nLetzterSuchFilter   = 1;
        $bZusatzSuchEnthalten = false;
        $oSuchanfrage_arr     = [];
        if (isset($NaviFilter->SuchFilter) && is_array($NaviFilter->SuchFilter) && count($NaviFilter->SuchFilter) > 0) {
            foreach ($NaviFilter->SuchFilter as $i => $oSuchFilter) {
                if (isset($oSuchFilter->kSuchanfrage, $oZusatzFilter->FilterLoesen->SuchFilter) &&
                    $oSuchFilter->kSuchanfrage > 0 &&
                    $oZusatzFilter->FilterLoesen->SuchFilter != $oSuchFilter->kSuchanfrage
                ) {
                    $bSeo = false;
                    if ($oSuchFilter->kSuchanfrage != $NaviFilter->Suche->kSuchanfrage) {
                        $oSuchanfrage_arr[$i]->kSuchanfrage = $oSuchFilter->kSuchanfrage;
                    }
                    $nLetzterSuchFilter++;
                    if ($oSuchFilter->kSuchanfrage == $oZusatzFilter->SuchFilter->kSuchanfrage) {
                        $bZusatzSuchEnthalten = true;
                    }
                }
            }
        }
        // Zusatz SuchFilter
        if (isset($oZusatzFilter->SuchFilter->kSuchanfrage) &&
            $oZusatzFilter->SuchFilter->kSuchanfrage > 0 &&
            !$bZusatzSuchEnthalten
        ) {
            $nPos = count($oSuchanfrage_arr);
            if (!isset($oSuchanfrage_arr[$nPos])) {
                $oSuchanfrage_arr[$nPos] = new stdClass();
            }
            $oSuchanfrage_arr[$nPos]->kSuchanfrage = $oZusatzFilter->SuchFilter->kSuchanfrage;
        }
        // Baue SuchFilter-URL
        $oSuchanfrage_arr = sortiereFilter($oSuchanfrage_arr, 'kSuchanfrage');
        if (is_array($oSuchanfrage_arr) && count($oSuchanfrage_arr) > 0) {
            foreach ($oSuchanfrage_arr as $i => $oSuchanfrage) {
                $cURL .= '&amp;sf' . ($i + 1) . '=' . (int)$oSuchanfrage->kSuchanfrage;
            }
        }
        // Merkmale
        $nLetzterMerkmalFilter   = 1;
        $bZusatzMerkmalEnthalten = false;
        $oMerkmalWert_arr        = [];
        if (isset($NaviFilter->MerkmalFilter) && is_array($NaviFilter->MerkmalFilter) && count($NaviFilter->MerkmalFilter) > 0) {
            foreach ($NaviFilter->MerkmalFilter as $i => $oMerkmalFilter) {
                if (($oMerkmalFilter->kMerkmalWert > 0 && !isset($oZusatzFilter->FilterLoesen->Merkmale)) ||
                    ($oZusatzFilter->FilterLoesen->Merkmale != $oMerkmalFilter->kMerkmal &&
                        !isset($oZusatzFilter->FilterLoesen->MerkmalWert) && isset($oMerkmalFilter->kMerkmalWert)) ||
                    (!isset($oZusatzFilter->FilterLoesen->MerkmalWert) && isset($oMerkmalFilter->kMerkmalWert) ||
                        ($oZusatzFilter->FilterLoesen->MerkmalWert != $oMerkmalFilter->kMerkmalWert))
                ) {
                    if (strlen($oMerkmalFilter->cSeo[$kSprache]) === 0) {
                        $bSeo = false;
                    }
                    $oMerkmalWert_arr[$i]               = new stdClass();
                    $oMerkmalWert_arr[$i]->kMerkmalWert = $oMerkmalFilter->kMerkmalWert;
                    $oMerkmalWert_arr[$i]->cSeo         = $oMerkmalFilter->cSeo[$kSprache];
                    $nLetzterMerkmalFilter++;
                    if (isset($oMerkmalFilter->kMerkmalWert, $oZusatzFilter->MerkmalFilter->kMerkmalWert) &&
                        $oMerkmalFilter->kMerkmalWert == $oZusatzFilter->MerkmalFilter->kMerkmalWert
                    ) {
                        $bZusatzMerkmalEnthalten = true;
                    }
                }
            }
        }
        // Zusatz MerkmalFilter
        if (isset($oZusatzFilter->MerkmalFilter->kMerkmalWert) && $oZusatzFilter->MerkmalFilter->kMerkmalWert > 0 && !$bZusatzMerkmalEnthalten) {
            $nPos = count($oMerkmalWert_arr);
            if (!isset($oMerkmalWert_arr[$nPos])) {
                $oMerkmalWert_arr[$nPos] = new stdClass();
            }
            $oMerkmalWert_arr[$nPos]->kMerkmalWert = $oZusatzFilter->MerkmalFilter->kMerkmalWert;
            $oMerkmalWert_arr[$nPos]->cSeo         = $oZusatzFilter->MerkmalFilter->cSeo;
        }
        // Baue MerkmalFilter URL
        $oMerkmalWert_arr = sortiereFilter($oMerkmalWert_arr, 'kMerkmalWert');
        if (is_array($oMerkmalWert_arr) && count($oMerkmalWert_arr) > 0) {
            foreach ($oMerkmalWert_arr as $i => $oMerkmalWert) {
                $cSEOURL .= SEP_MERKMAL . $oMerkmalWert->cSeo;
                $cURL .= '&amp;mf' . ($i + 1) . '=' . (int)$oMerkmalWert->kMerkmalWert;
            }
        }
        // Preisspannen
        if (isset($NaviFilter->PreisspannenFilter->fVon, $NaviFilter->PreisspannenFilter->fBis) &&
            $NaviFilter->PreisspannenFilter->fVon >= 0 &&
            $NaviFilter->PreisspannenFilter->fBis > 0 &&
            !isset($oZusatzFilter->FilterLoesen->Preisspannen)
        ) {
            $cURL .= '&amp;pf=' . $NaviFilter->PreisspannenFilter->fVon . '_' . $NaviFilter->PreisspannenFilter->fBis;
        } elseif (isset($oZusatzFilter->PreisspannenFilter->fVon, $oZusatzFilter->PreisspannenFilter->fBis) &&
            $oZusatzFilter->PreisspannenFilter->fVon >= 0 &&
            $oZusatzFilter->PreisspannenFilter->fBis > 0
        ) {
            $cURL .= '&amp;pf=' . $oZusatzFilter->PreisspannenFilter->fVon . '_' . $oZusatzFilter->PreisspannenFilter->fBis;
        }
        // Bewertung
        if (isset($NaviFilter->BewertungFilter->nSterne) && $NaviFilter->BewertungFilter->nSterne > 0 &&
            !isset($oZusatzFilter->FilterLoesen->Bewertungen) && !isset($oZusatzFilter->BewertungFilter->nSterne)
        ) {
            $cURL .= '&amp;bf=' . $NaviFilter->BewertungFilter->nSterne;
        } elseif (isset($oZusatzFilter->BewertungFilter->nSterne) && $oZusatzFilter->BewertungFilter->nSterne > 0) {
            $cURL .= '&amp;bf=' . (int)$oZusatzFilter->BewertungFilter->nSterne;
        }
        // Tag
        $nLetzterTagFilter   = 1;
        $bZusatzTagEnthalten = false;
        $oTag_arr            = [];
        if (!isset($oZusatzFilter->FilterLoesen->Tags) && isset($NaviFilter->TagFilter) && is_array($NaviFilter->TagFilter)) {
            foreach ($NaviFilter->TagFilter as $i => $oTagFilter) {
                if ($oTagFilter->kTag > 0) {
                    if (!isset($oTag_arr[$i])) {
                        $oTag_arr[$i] = new stdClass();
                    }
                    $oTag_arr[$i]->kTag = $oTagFilter->kTag;
                    $nLetzterTagFilter++;
                    if (isset($oZusatzFilter->TagFilter->kTag) && $oTagFilter->kTag == $oZusatzFilter->TagFilter->kTag) {
                        $bZusatzTagEnthalten = true;
                    }
                }
            }
        }
        // Zusatz Tagfilter
        if (isset($oZusatzFilter->TagFilter->kTag) && $oZusatzFilter->TagFilter->kTag > 0 && !$bZusatzTagEnthalten) {
            //$cURL .= "&amp;tf" . $nLetzterTagFilter . "=" . $oZusatzFilter->TagFilter->kTag;
            $nPos = count($oTag_arr);
            if (!isset($oTag_arr[$nPos])) {
                $oTag_arr[$nPos] = new stdClass();
            }
            $oTag_arr[$nPos]->kTag = $oZusatzFilter->TagFilter->kTag;
        }
        // Baue TagFilter URL
        $oTag_arr = sortiereFilter($oTag_arr, 'kTag');
        if (is_array($oTag_arr) && count($oTag_arr) > 0) {
            foreach ($oTag_arr as $i => $oTag) {
                $cURL .= '&amp;tf' . ($i + 1) . '=' . (int)$oTag->kTag;
            }
        }
        // Suchspecialfilter
        if ((isset($NaviFilter->SuchspecialFilter->kKey) && $NaviFilter->SuchspecialFilter->kKey > 0) &&
            (!isset($NaviFilter->Suchspecial->kKey) || $NaviFilter->Suchspecial->kKey != $NaviFilter->SuchspecialFilter->kKey)
        ) {
            if (!isset($oZusatzFilter->FilterLoesen->Suchspecials) || !$oZusatzFilter->FilterLoesen->Suchspecials) {
                $cSEOURL .= $NaviFilter->SuchspecialFilter->cSeo[$kSprache];
                if (strlen($NaviFilter->SuchspecialFilter->cSeo[$kSprache]) === 0) {
                    $bSeo = false;
                }
                $cURL .= '&amp;qf=' . (int)$NaviFilter->SuchspecialFilter->kKey;
            }
        } elseif ((isset($oZusatzFilter->SuchspecialFilter->kKey) && $oZusatzFilter->SuchspecialFilter->kKey > 0) &&
            (!isset($NaviFilter->Suchspecial->kKey) || $NaviFilter->Suchspecial->kKey != $oZusatzFilter->SuchspecialFilter->kKey)
        ) {
            $cURL .= '&amp;qf=' . (int)$oZusatzFilter->SuchspecialFilter->kKey;
        }
        // Sortierung
        if (isset($oZusatzFilter->nSortierung) && $oZusatzFilter->nSortierung > 0) {
            $cURL .= '&amp;Sortierung=' . (int)$oZusatzFilter->nSortierung;
        }
    }

    $cISOSprache = '';
    if (isset($_SESSION['Sprachen']) && count($_SESSION['Sprachen']) > 0) {
        foreach ($_SESSION['Sprachen'] as $i => $oSprache) {
            if ($oSprache->kSprache == $kSprache) {
                $cISOSprache = $oSprache->cISO;
            }
        }
    }
    if (strlen($cSEOURL) > 254) {
        $bSeo = false;
    }

    if ($bSeo) {
        return $cSEOURL;
    }
    if ($kSprache != Shop::$kSprache) {
        return $cURL . '&amp;lang=' . $cISOSprache;
    }

    return $cURL;
}

/**
 * @param object       $oPreis
 * @param object|array $oPreisspannenfilter_arr
 * @return string
 */
function berechnePreisspannenSQL($oPreis, $oPreisspannenfilter_arr = null)
{
    $cSQL          = '';
    $fKundenrabatt = 0.0;
    $conf          = Shop::getSettings([CONF_NAVIGATIONSFILTER]);
    if (isset($_SESSION['Kunde']->fRabatt) && $_SESSION['Kunde']->fRabatt > 0) {
        $fKundenrabatt = $_SESSION['Kunde']->fRabatt;
    }
    // Wenn Option vorhanden, dann nur Spannen anzeigen, in denen Artikel vorhanden sind
    if ($conf['navigationsfilter']['preisspannenfilter_anzeige_berechnung'] === 'A') {
        $nPreisMax = $oPreis->fMaxPreis;
        $nPreisMin = $oPreis->fMinPreis;
        $nStep     = $oPreis->fStep;

        for ($i = 0; $i < $oPreis->nAnzahlSpannen; ++$i) {
            $cSQL .= "COUNT(
                    IF(";

            $nBis = ($nPreisMin + ($i + 1) * $nStep);
            if (isset($oPreisspannenfilter_arr->nBis) && $oPreisspannenfilter_arr->nBis > $nPreisMax) {
                $nBis = $nPreisMax;
            }
            // Finde den höchsten und kleinsten Steuersatz
            if (is_array($_SESSION['Steuersatz']) && (int)$_SESSION['Kundengruppe']->nNettoPreise === 0) {
                $nSteuersatzKeys_arr = array_keys($_SESSION['Steuersatz']);
                foreach ($nSteuersatzKeys_arr as $nSteuersatzKeys) {
                    $fSteuersatz = (float)$_SESSION['Steuersatz'][$nSteuersatzKeys];
                    $cSQL .= "IF(tartikel.kSteuerklasse = " . $nSteuersatzKeys . ",
                                ROUND(LEAST((tpreise.fVKNetto * " . $_SESSION['Waehrung']->fFaktor .
                        ") * ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), " .
                        $_SESSION['Kundengruppe']->fRabatt . ", " . $fKundenrabatt .
                        ", 0)) / 100), IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * " .
                        $_SESSION['Waehrung']->fFaktor . "))) * ((100 + " . $fSteuersatz . ") / 100)
                            , 2),";
                }

                $cSQL .= "0";
                $count = count($nSteuersatzKeys_arr);
                for ($x = 0; $x < $count; ++$x) {
                    $cSQL .= ")";
                }
            } elseif ((int)$_SESSION['Kundengruppe']->nNettoPreise > 0) {
                $cSQL .= "ROUND(LEAST((tpreise.fVKNetto * " . $_SESSION['Waehrung']->fFaktor .
                    ") * ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), " .
                    $_SESSION['Kundengruppe']->fRabatt . ", " . $fKundenrabatt .
                    ", 0)) / 100), IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * " .
                    $_SESSION['Waehrung']->fFaktor . "))), 2)";
            }
            $cSQL .= " < " . $nBis . ", 1, NULL)
                    ) AS anz" . $i . ", ";
        }

        $cSQL = substr($cSQL, 0, strlen($cSQL) - 2);
    } elseif (is_array($oPreisspannenfilter_arr)) {
        foreach ($oPreisspannenfilter_arr as $i => $oPreisspannenfilter) {
            $cSQL .= "COUNT(
                    IF(";

            $nBis = $oPreisspannenfilter->nBis;
            // Finde den höchsten und kleinsten Steuersatz
            if (is_array($_SESSION['Steuersatz']) && (int)$_SESSION['Kundengruppe']->nNettoPreise === 0) {
                $nSteuersatzKeys_arr = array_keys($_SESSION['Steuersatz']);
                foreach ($nSteuersatzKeys_arr as $nSteuersatzKeys) {
                    $fSteuersatz = (float)$_SESSION['Steuersatz'][$nSteuersatzKeys];
                    $cSQL .= "IF(tartikel.kSteuerklasse = " . $nSteuersatzKeys . ",
                            ROUND(LEAST((tpreise.fVKNetto * " . $_SESSION['Waehrung']->fFaktor .
                        ") * ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), " .
                        $_SESSION['Kundengruppe']->fRabatt . ", " .
                        $fKundenrabatt . ", 0)) / 100), IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * " .
                        $_SESSION['Waehrung']->fFaktor . "))) * ((100 + " . $fSteuersatz . ") / 100)
                        , 2),";
                }
                $cSQL .= "0";
                $count = count($nSteuersatzKeys_arr);
                for ($x = 0; $x < $count; ++$x) {
                    $cSQL .= ")";
                }
            } elseif ((int)$_SESSION['Kundengruppe']->nNettoPreise > 0) {
                $cSQL .= "ROUND(LEAST((tpreise.fVKNetto * " . $_SESSION['Waehrung']->fFaktor .
                    ") * ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), " .
                    $_SESSION['Kundengruppe']->fRabatt . ", " . $fKundenrabatt .
                    ", 0)) / 100), IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * " .
                    $_SESSION['Waehrung']->fFaktor . "))), 2)";
            }

            $cSQL .= " < " . $nBis . ", 1, NULL)
                    ) AS anz" . $i . ", ";
        }

        $cSQL = substr($cSQL, 0, strlen($cSQL) - 2);
    }

    return $cSQL;
}

/**
 * @param float $fMax
 * @param float $fMin
 * @return stdClass
 */
function berechneMaxMinStep($fMax, $fMin)
{
    $fStepWert_arr = [
        0.001, 0.005, 0.01, 0.05, 0.10, 0.25, 0.5, 1.0, 2.5, 5.0, 7.5,
        10.0, 12.5, 15.0, 20.0, 25.0, 50.0, 100.0, 250.0, 300.0, 350.0,
        400.0, 500.0, 750.0, 1000.0, 1500.0, 2500.0, 5000.0, 10000.0,
        25000.0, 30000.0, 40000.0, 50000.0, 60000.0, 75000.0, 100000.0,
        150000.0, 250000.0, 350000.0, 400000.0, 500000.0, 550000.0,
        600000.0, 750000.0, 1000000.0, 1500000.0, 5000000.0, 7500000.0,
        10000000.0, 12500000.0, 15000000.0, 25000000.0, 50000000.0,
        100000000.0
    ];
    $nStep      = 10;
    $fDiffPreis = (float)($fMax - $fMin) * 1000;
    $nMaxSteps  = 5;
    $conf       = Shop::getSettings([CONF_NAVIGATIONSFILTER]);
    if ($conf['navigationsfilter']['preisspannenfilter_anzeige_berechnung'] === 'M') {
        $nMaxSteps = 10;
    }
    foreach ($fStepWert_arr as $i => $fStepWert) {
        if (($fDiffPreis / (float)($fStepWert * 1000)) < $nMaxSteps) {
            $nStep = $i;
            break;
        }
    }
    $fStepWert = $fStepWert_arr[$nStep] * 1000;
    $fMax *= 1000;
    $fMin *= 1000;
    $fMaxPreis      = round(((($fMax * 100) - (($fMax * 100) % ($fStepWert * 100))) + ($fStepWert * 100)) / 100, 0);
    $fMinPreis      = round((($fMin * 100) - (($fMin * 100) % ($fStepWert * 100))) / 100, 0);
    $fDiffPreis     = $fMaxPreis - $fMinPreis;
    $nAnzahlSpannen = round($fDiffPreis / $fStepWert, 0);

    $oObject                 = new stdClass();
    $oObject->fMaxPreis      = $fMaxPreis / 1000;
    $oObject->fMinPreis      = $fMinPreis / 1000;
    $oObject->fStep          = $fStepWert_arr[$nStep];
    $oObject->fDiffPreis     = $fDiffPreis / 1000;
    $oObject->nAnzahlSpannen = $nAnzahlSpannen;

    return $oObject;
}

/**
 * @return null|string
 */
function gibBrotNaviName()
{
    global $NaviFilter;
    if (isset($NaviFilter->Kategorie->kKategorie, $NaviFilter->Kategorie->cName) && $NaviFilter->Kategorie->kKategorie > 0) {
        return isset($NaviFilter->Kategorie->cName) ? $NaviFilter->Kategorie->cName : null;
    }
    if (isset($NaviFilter->Hersteller->kHersteller) && $NaviFilter->Hersteller->kHersteller > 0) {
        return isset($NaviFilter->Hersteller->cName) ? $NaviFilter->Hersteller->cName : null;
    }
    if (isset($NaviFilter->MerkmalWert->kMerkmalWert) && $NaviFilter->MerkmalWert->kMerkmalWert > 0) {
        return isset($NaviFilter->MerkmalWert->cName) ? $NaviFilter->MerkmalWert->cName : null;
    }
    if (isset($NaviFilter->Tag->kTag) && $NaviFilter->Tag->kTag > 0) {
        return isset($NaviFilter->Tag->cName) ? $NaviFilter->Tag->cName : null;
    }
    if (isset($NaviFilter->Suchspecial->kKey) && $NaviFilter->Suchspecial->kKey > 0) {
        return isset($NaviFilter->Suchspecial->cName) ? $NaviFilter->Suchspecial->cName : null;
    }
    if (isset($NaviFilter->Suche->cSuche) && strlen($NaviFilter->Suche->cSuche) > 0) {
        return isset($NaviFilter->Suche->cSuche) ? strip_tags(trim($NaviFilter->Suche->cSuche)) : null;
    }

    return '';
}

/**
 * @return string
 */
function gibHeaderAnzeige()
{
    global $NaviFilter;
    if (isset($NaviFilter->Kategorie->kKategorie) && $NaviFilter->Kategorie->kKategorie > 0) {
        return $NaviFilter->cBrotNaviName;
    }
    if (isset($NaviFilter->Hersteller->kHersteller) && $NaviFilter->Hersteller->kHersteller > 0) {
        return Shop::Lang()->get('productsFrom', 'global') . ' ' . $NaviFilter->cBrotNaviName;
    }
    if (isset($NaviFilter->MerkmalWert->kMerkmalWert) && $NaviFilter->MerkmalWert->kMerkmalWert > 0) {
        return Shop::Lang()->get('productsWith', 'global') . ' ' . $NaviFilter->cBrotNaviName;
    }
    if (isset($NaviFilter->Tag->kTag) && $NaviFilter->Tag->kTag > 0) {
        return Shop::Lang()->get('showAllProductsTaggedWith', 'global') . ' ' . $NaviFilter->cBrotNaviName;
    }
    if (isset($NaviFilter->Suchspecial->kKey) && $NaviFilter->Suchspecial->kKey > 0) {
        return $NaviFilter->cBrotNaviName;
    }
    if (isset($NaviFilter->Suche->cSuche) && strlen($NaviFilter->Suche->cSuche) > 0) {
        return Shop::Lang()->get('for', 'global') . ' ' . $NaviFilter->cBrotNaviName;
    }

    return '';
}

/**
 * @param bool   $bSeo
 * @param object $oSuchergebnisse
 */
function erstelleFilterLoesenURLs($bSeo, $oSuchergebnisse)
{
    global $NaviFilter;

    if (isset($NaviFilter->SuchspecialFilter->kKey) && $NaviFilter->SuchspecialFilter->kKey > 0) {
        $bSeo = false;
    }
    // URLs bauen, die Filter lösen
    $oZusatzFilter                          = new stdClass();
    $oZusatzFilter->FilterLoesen            = new stdClass();
    $oZusatzFilter->FilterLoesen->Kategorie = true;
    if (!isset($NaviFilter->URL)) {
        $NaviFilter->URL = new stdClass();
    }
    $NaviFilter->URL->cAlleKategorien = gibNaviURL($NaviFilter, $bSeo, $oZusatzFilter);

    $oZusatzFilter                           = new stdClass();
    $oZusatzFilter->FilterLoesen             = new stdClass();
    $oZusatzFilter->FilterLoesen->Hersteller = true;
    $NaviFilter->URL->cAlleHersteller        = gibNaviURL($NaviFilter, $bSeo, $oZusatzFilter);

    $oZusatzFilter = new stdClass();

    $NaviFilter->URL->cAlleMerkmale     = [];
    $NaviFilter->URL->cAlleMerkmalWerte = [];
    $oZusatzFilter->FilterLoesen        = new stdClass();
    foreach ($NaviFilter->MerkmalFilter as $oMerkmal) {
        if (isset($oMerkmal->kMerkmal) && $oMerkmal->kMerkmal > 0) {
            $oZusatzFilter->FilterLoesen->Merkmale               = $oMerkmal->kMerkmal;
            $NaviFilter->URL->cAlleMerkmale[$oMerkmal->kMerkmal] = gibNaviURL($NaviFilter, $bSeo, $oZusatzFilter);
        }
        $oZusatzFilter->FilterLoesen->MerkmalWert                    = $oMerkmal->kMerkmalWert;
        $NaviFilter->URL->cAlleMerkmalWerte[$oMerkmal->kMerkmalWert] = gibNaviURL($NaviFilter, $bSeo, $oZusatzFilter);
    }
    // kinda hacky: try to build url that removes a merkmalwert url from merkmalfilter url
    if (isset($NaviFilter->MerkmalWert->kMerkmalWert, $NaviFilter->URL->cAlleKategorien) &&
        !isset($NaviFilter->URL->cAlleMerkmalWerte[$NaviFilter->MerkmalWert->kMerkmalWert])
    ) {
        // the url should be <shop>/<merkmalwert-url>__<merkmalfilter>[__<merkmalfilter>]
        $_mmwSeo = str_replace($NaviFilter->MerkmalWert->cSeo[Shop::$kSprache] . '__', '', $NaviFilter->URL->cAlleKategorien);
        if ($_mmwSeo !== $NaviFilter->URL->cAlleKategorien) {
            $NaviFilter->URL->cAlleMerkmalWerte[$NaviFilter->MerkmalWert->kMerkmalWert] = $_mmwSeo;
        }
    }

    $oZusatzFilter                             = new stdClass();
    $oZusatzFilter->FilterLoesen               = new stdClass();
    $oZusatzFilter->FilterLoesen->Preisspannen = true;
    $NaviFilter->URL->cAllePreisspannen        = gibNaviURL($NaviFilter, $bSeo, $oZusatzFilter);

    $oZusatzFilter                            = new stdClass();
    $oZusatzFilter->FilterLoesen              = new stdClass();
    $oZusatzFilter->FilterLoesen->Bewertungen = true;
    $NaviFilter->URL->cAlleBewertungen        = gibNaviURL($NaviFilter, $bSeo, $oZusatzFilter);

    $oZusatzFilter                     = new stdClass();
    $oZusatzFilter->FilterLoesen       = new stdClass();
    $oZusatzFilter->FilterLoesen->Tags = true;
    $NaviFilter->URL->cAlleTags        = gibNaviURL($NaviFilter, $bSeo, $oZusatzFilter);

    $oZusatzFilter                             = new stdClass();
    $oZusatzFilter->FilterLoesen               = new stdClass();
    $oZusatzFilter->FilterLoesen->Suchspecials = true;
    $NaviFilter->URL->cAlleSuchspecials        = gibNaviURL($NaviFilter, $bSeo, $oZusatzFilter);

    $oZusatzFilter                                  = new stdClass();
    $oZusatzFilter->FilterLoesen                    = new stdClass();
    $oZusatzFilter->FilterLoesen->Erscheinungsdatum = true;
    $NaviFilter->URL->cAlleErscheinungsdatums       = gibNaviURL($NaviFilter, false, $oZusatzFilter);

    $oZusatzFilter                    = new stdClass();
    $oZusatzFilter->FilterLoesen      = new stdClass();
    $NaviFilter->URL->cAlleSuchFilter = [];
    foreach ($NaviFilter->SuchFilter as $oSuchFilter) {
        if (isset($oSuchFilter->kSuchanfrage) && $oSuchFilter->kSuchanfrage > 0) {
            $oZusatzFilter->FilterLoesen->SuchFilter                      = $oSuchFilter->kSuchanfrage;
            $NaviFilter->URL->cAlleSuchFilter[$oSuchFilter->kSuchanfrage] = gibNaviURL($NaviFilter, $bSeo, $oZusatzFilter);
        }
    }
    $NaviFilter->URL->cNoFilter = null;
    // Filter reset
    $cSeite = '';
    if (isset($oSuchergebnisse->Seitenzahlen->AktuelleSeite) && $oSuchergebnisse->Seitenzahlen->AktuelleSeite > 1) {
        $cSeite = SEP_SEITE . $oSuchergebnisse->Seitenzahlen->AktuelleSeite;
    }

    $NaviFilter->URL->cNoFilter = gibNaviURL($NaviFilter, true, null, 0, true) . $cSeite;
}

/**
 * @deprecated since 4.06
 * @param string $cTitle
 * @return string
 */
function truncateMetaTitle($cTitle)
{
    $conf = Shop::getSettings([CONF_METAANGABEN]);
    $maxLength = !empty($conf['metaangaben']['global_meta_maxlaenge_title']) ? (int)$conf['metaangaben']['global_meta_maxlaenge_title'] : 0;

    return prepareMeta($cTitle, null, $maxLength);
}

/**
 * @param object $NaviFilter
 * @param object $oSuchergebnisse
 * @param array $GlobaleMetaAngaben_arr
 * @return string
 */
function gibNaviMetaTitle($NaviFilter, $oSuchergebnisse, $GlobaleMetaAngaben_arr)
{
    global $oMeta;
    $conf      = Shop::getSettings([CONF_METAANGABEN]);
    $cSuffix   = '';
    $maxLength = !empty($conf['metaangaben']['global_meta_maxlaenge_title']) ? (int)$conf['metaangaben']['global_meta_maxlaenge_title'] : 0;

    executeHook(HOOK_FILTER_INC_GIBNAVIMETATITLE);
    // Seitenzahl anhaengen ab Seite 2 (Doppelte Titles vermeiden, #5992)
    if ($oSuchergebnisse->Seitenzahlen->AktuelleSeite > 1) {
        $cSuffix = ', ' . Shop::Lang()->get('page', 'global') . " {$oSuchergebnisse->Seitenzahlen->AktuelleSeite}";
    }
    // Pruefen ob bereits eingestellte Metas gesetzt sind
    if (strlen($oMeta->cMetaTitle) > 0) {
        $oMeta->cMetaTitle = strip_tags($oMeta->cMetaTitle);
        // Globalen Meta Title anhaengen
        if ($conf['metaangaben']['global_meta_title_anhaengen'] === 'Y' &&
            strlen($GlobaleMetaAngaben_arr[Shop::getLanguage()]->Title) > 0
        ) {
            return prepareMeta($oMeta->cMetaTitle . ' ' . $GlobaleMetaAngaben_arr[Shop::$kSprache]->Title, $cSuffix);
        }

        return prepareMeta($oMeta->cMetaTitle, $cSuffix, $maxLength);
    }
    // Set Default Titles
    $cMetaTitle = gibMetaStart($NaviFilter, $oSuchergebnisse);
    $cMetaTitle = str_replace('"', "'", $cMetaTitle);
    $cMetaTitle = StringHandler::htmlentitydecode($cMetaTitle, ENT_NOQUOTES);
    // Kategorieattribute koennen Standard-Titles ueberschreiben
    if (isset($NaviFilter->Kategorie->kKategorie) && $NaviFilter->Kategorie->kKategorie > 0) {
        $oKategorie = new Kategorie($NaviFilter->Kategorie->kKategorie);
        if (isset($oKategorie->cTitleTag) && strlen($oKategorie->cTitleTag) > 0) {
            // meta title via new method
            $cMetaTitle = strip_tags($oKategorie->cTitleTag);
            $cMetaTitle = str_replace('"', "'", $cMetaTitle);
            $cMetaTitle = StringHandler::htmlentitydecode($cMetaTitle, ENT_NOQUOTES);
        } elseif (!empty($oKategorie->categoryAttributes['meta_title']->cWert)) {
            // Hat die aktuelle Kategorie als Kategorieattribut einen Meta Title gesetzt?
            $cMetaTitle = strip_tags($oKategorie->categoryAttributes['meta_title']->cWert);
            $cMetaTitle = str_replace('"', "'", $cMetaTitle);
            $cMetaTitle = StringHandler::htmlentitydecode($cMetaTitle, ENT_NOQUOTES);
        } elseif (!empty($oKategorie->KategorieAttribute['meta_title'])) {
            /** @deprecated since 4.05 - this is for compatibilty only! */
            $cMetaTitle = strip_tags($oKategorie->KategorieAttribute['meta_title']);
            $cMetaTitle = str_replace('"', "'", $cMetaTitle);
            $cMetaTitle = StringHandler::htmlentitydecode($cMetaTitle, ENT_NOQUOTES);
        }
    }

    // Globalen Meta Title ueberall anhaengen
    if ($conf['metaangaben']['global_meta_title_anhaengen'] === 'Y' &&
        !empty($GlobaleMetaAngaben_arr[Shop::getLanguage()]->Title)
    ) {
        $cMetaTitle .= ' - ' . $GlobaleMetaAngaben_arr[Shop::getLanguage()]->Title;
    }

    return prepareMeta($cMetaTitle, $cSuffix, $maxLength);
}

/**
 * @param array  $oArtikel_arr
 * @param object $NaviFilter
 * @param object $oSuchergebnisse
 * @param array  $GlobaleMetaAngaben_arr
 * @return string
 */
function gibNaviMetaDescription($oArtikel_arr, $NaviFilter, $oSuchergebnisse, $GlobaleMetaAngaben_arr)
{
    global $oMeta;
    $conf      = Shop::getSettings([CONF_METAANGABEN]);
    $maxLength = $conf['metaangaben']['global_meta_maxlaenge_description'] > 0 ? (int)$conf['metaangaben']['global_meta_maxlaenge_description'] : 0;

    executeHook(HOOK_FILTER_INC_GIBNAVIMETADESCRIPTION);
    $cSuffix = '';
    if ($oSuchergebnisse->Seitenzahlen->AktuelleSeite > 1 &&
        $oSuchergebnisse->ArtikelVon > 0 &&
        $oSuchergebnisse->ArtikelBis > 0
    ) {
        $cSuffix = ', ' . Shop::Lang()->get('products', 'global') .
            " {$oSuchergebnisse->ArtikelVon} - {$oSuchergebnisse->ArtikelBis}";
    }
    // Prüfen ob bereits eingestellte Metas gesetzt sind
    if (strlen($oMeta->cMetaDescription) > 0) {
        $oMeta->cMetaDescription = strip_tags($oMeta->cMetaDescription);

        return prepareMeta($oMeta->cMetaDescription, $cSuffix, $maxLength);
    }
    // Kategorieattribut?
    $cKatDescription = '';
    if (isset($NaviFilter->Kategorie->kKategorie) && $NaviFilter->Kategorie->kKategorie > 0) {
        $oKategorie = new Kategorie($NaviFilter->Kategorie->kKategorie);
        if (isset($oKategorie->cMetaDescription) && strlen($oKategorie->cMetaDescription) > 0) {
            // meta description via new method
            $cKatDescription = strip_tags($oKategorie->cMetaDescription);

            return prepareMeta($cKatDescription, $cSuffix, $maxLength);
        } elseif (!empty($oKategorie->categoryAttributes['meta_description']->cWert)) {
            // Hat die aktuelle Kategorie als Kategorieattribut eine Meta Description gesetzt?
            $cKatDescription = strip_tags($oKategorie->categoryAttributes['meta_description']->cWert);

            return prepareMeta($cKatDescription, $cSuffix, $maxLength);
        } elseif (!empty($oKategorie->KategorieAttribute['meta_description'])) {
            /** @deprecated since 4.05 - this is for compatibilty only! */
            $cKatDescription = strip_tags($oKategorie->KategorieAttribute['meta_description']);

            return prepareMeta($cKatDescription, $cSuffix, $maxLength);
        } else {
            // Hat die aktuelle Kategorie eine Beschreibung?
            if (isset($oKategorie->cBeschreibung) && strlen($oKategorie->cBeschreibung) > 0) {
                $cKatDescription = strip_tags(str_replace(['<br>', '<br />'], [' ', ' '], $oKategorie->cBeschreibung));
            } elseif ($oKategorie->bUnterKategorien) { // Hat die aktuelle Kategorie Unterkategorien?
                $oKategorieListe = new KategorieListe();
                $oKategorieListe->getAllCategoriesOnLevel($oKategorie->kKategorie);

                if (isset($oKategorieListe->elemente) && is_array($oKategorieListe->elemente) && count($oKategorieListe->elemente) > 0) {
                    foreach ($oKategorieListe->elemente as $i => $oUnterkat) {
                        if (isset($oUnterkat->cName) && strlen($oUnterkat->cName) > 0) {
                            if ($i > 0) {
                                $cKatDescription .= ', ' . strip_tags($oUnterkat->cName);
                            } else {
                                $cKatDescription .= strip_tags($oUnterkat->cName);
                            }
                        }
                    }
                }
            }

            if (strlen($cKatDescription) > 1) {
                $cKatDescription = str_replace('"', '', $cKatDescription);
                $cKatDescription = StringHandler::htmlentitydecode($cKatDescription, ENT_NOQUOTES);
                if (isset($GlobaleMetaAngaben_arr[Shop::getLanguage()]->Meta_Description_Praefix) &&
                    strlen($GlobaleMetaAngaben_arr[Shop::getLanguage()]->Meta_Description_Praefix) > 0
                ) {
                    $cMetaDescription = trim(strip_tags(
                        $GlobaleMetaAngaben_arr[Shop::getLanguage()]->Meta_Description_Praefix
                        ) . ' ' . $cKatDescription);
                } else {
                    $cMetaDescription = trim($cKatDescription);
                }
                $cMetaDescription = $cMetaDescription;

                return prepareMeta($cMetaDescription, $cSuffix, $maxLength);
            }
        }
    }
    // Keine eingestellten Metas vorhanden => generiere Standard Metas
    $cMetaDescription = '';
    if (is_array($oArtikel_arr) && count($oArtikel_arr) > 0) {
        shuffle($oArtikel_arr);
        $nCount = 12;
        if (count($oArtikel_arr) < $nCount) {
            $nCount = count($oArtikel_arr);
        }
        $cArtikelName = '';
        for ($i = 0; $i < $nCount; $i++) {
            if ($i > 0) {
                $cArtikelName .= ' - ' . $oArtikel_arr[$i]->cName;
            } else {
                $cArtikelName .= $oArtikel_arr[$i]->cName;
            }
        }
        $cArtikelName = str_replace('"', '', $cArtikelName);
        $cArtikelName = StringHandler::htmlentitydecode($cArtikelName, ENT_NOQUOTES);

        if (isset($GlobaleMetaAngaben_arr[Shop::getLanguage()]->Meta_Description_Praefix) &&
            strlen($GlobaleMetaAngaben_arr[Shop::getLanguage()]->Meta_Description_Praefix
            ) > 0) {
            $cMetaDescription = gibMetaStart($NaviFilter, $oSuchergebnisse) . ': ' .
                $GlobaleMetaAngaben_arr[Shop::getLanguage()]->Meta_Description_Praefix . ' ' . $cArtikelName;
        } else {
            $cMetaDescription = gibMetaStart($NaviFilter, $oSuchergebnisse) . ': ' . $cArtikelName;
        }
        $cMetaDescription = $cMetaDescription;
    }

    return prepareMeta(strip_tags($cMetaDescription), $cSuffix, $maxLength);
}

/**
 * @param array  $oArtikel_arr
 * @param object $NaviFilter
 * @param array  $oExcludesKeywords_arr
 * @return mixed|string
 */
function gibNaviMetaKeywords($oArtikel_arr, $NaviFilter, $oExcludesKeywords_arr)
{
    global $oMeta;

    executeHook(HOOK_FILTER_INC_GIBNAVIMETAKEYWORDS);
    // Prüfen ob bereits eingestellte Metas gesetzt sind
    if (strlen($oMeta->cMetaKeywords) > 0) {
        $oMeta->cMetaKeywords = strip_tags($oMeta->cMetaKeywords);

        return $oMeta->cMetaKeywords;
    }
    // Kategorieattribut?
    $cKatKeywords = '';
    $oKategorie   = new stdClass();
    if (isset($NaviFilter->Kategorie->kKategorie) && $NaviFilter->Kategorie->kKategorie > 0) {
        $oKategorie = new Kategorie($NaviFilter->Kategorie->kKategorie);
        if (isset($oKategorie->cMetaKeywords) && strlen($oKategorie->cMetaKeywords) > 0) {
            // meta keywords via new method
            $cKatKeywords = strip_tags($oKategorie->cMetaKeywords);

            return $cKatKeywords;
        } elseif (!empty($oKategorie->categoryAttributes['meta_keywords']->cWert)) {
            // Hat die aktuelle Kategorie als Kategorieattribut einen Meta Keywords gesetzt?
            $cKatKeywords = strip_tags($oKategorie->categoryAttributes['meta_keywords']->cWert);

            return $cKatKeywords;
        } elseif (!empty($oKategorie->KategorieAttribute['meta_keywords'])) {
            /** @deprecated since 4.05 - this is for compatibilty only! */
            $cKatKeywords = strip_tags($oKategorie->KategorieAttribute['meta_keywords']);

            return $cKatKeywords;
        }
    }
    // Keine eingestellten Metas vorhanden => baue Standard Metas
    $cMetaKeywords = '';
    if (is_array($oArtikel_arr) && count($oArtikel_arr) > 0) {
        shuffle($oArtikel_arr); // Shuffle alle Artikel
        $nCount = 6;
        if (count($oArtikel_arr) < $nCount) {
            $nCount = count($oArtikel_arr);
        }
        $cArtikelName = '';
        for ($i = 0; $i < $nCount; $i++) {
            $cExcArtikelName = gibExcludesKeywordsReplace($oArtikel_arr[$i]->cName, $oExcludesKeywords_arr);
            // Filter nicht erlaubte Keywords
            if (strpos($cExcArtikelName, ' ') !== false) {
                // Wenn der Dateiname aus mehreren Wörtern besteht
                $cSubNameTMP_arr = explode(' ', $cExcArtikelName);
                $cSubName        = '';
                if (is_array($cSubNameTMP_arr) && count($cSubNameTMP_arr) > 0) {
                    foreach ($cSubNameTMP_arr as $j => $cSubNameTMP) {
                        if (strlen($cSubNameTMP) > 2) {
                            $cSubNameTMP = str_replace(',', '', $cSubNameTMP);
                            if ($j > 0) {
                                $cSubName .= ', ' . $cSubNameTMP;
                            } else {
                                $cSubName .= $cSubNameTMP;
                            }
                        }
                    }
                }
                $cArtikelName .= $cSubName;
            } elseif ($i > 0) {
                $cArtikelName .= ', ' . $oArtikel_arr[$i]->cName;
            } else {
                $cArtikelName .= $oArtikel_arr[$i]->cName;
            }
        }
        $cMetaKeywords = $cArtikelName;
        // Prüfe doppelte Einträge und lösche diese
        $cMetaKeywordsUnique_arr = [];
        $cMeta_arr               = explode(', ', $cMetaKeywords);
        if (is_array($cMeta_arr) && count($cMeta_arr) > 1) {
            foreach ($cMeta_arr as $cMeta) {
                if (!in_array($cMeta, $cMetaKeywordsUnique_arr, true)) {
                    $cMetaKeywordsUnique_arr[] = $cMeta;
                }
            }
            $cMetaKeywords = implode(', ', $cMetaKeywordsUnique_arr);
        }
    } elseif (isset($NaviFilter->Kategorie->kKategorie) && $NaviFilter->Kategorie->kKategorie > 0) {
        // Hat die aktuelle Kategorie Unterkategorien?
        if ($oKategorie->bUnterKategorien) {
            $oKategorieListe = new KategorieListe();
            $oKategorieListe->getAllCategoriesOnLevel($oKategorie->kKategorie);
            if (isset($oKategorieListe->elemente) &&
                is_array($oKategorieListe->elemente) &&
                count($oKategorieListe->elemente) > 0
            ) {
                foreach ($oKategorieListe->elemente as $i => $oUnterkat) {
                    if (isset($oUnterkat->cName) && strlen($oUnterkat->cName) > 0) {
                        if ($i > 0) {
                            $cKatKeywords .= ', ' . $oUnterkat->cName;
                        } else {
                            $cKatKeywords .= $oUnterkat->cName;
                        }
                    }
                }
            }
        } elseif (isset($oKategorie->cBeschreibung) && strlen($oKategorie->cBeschreibung) > 0) {
            // Hat die aktuelle Kategorie eine Beschreibung?
            $cKatKeywords = $oKategorie->cBeschreibung;
        }
        $cKatKeywords  = str_replace('"', '', $cKatKeywords);
        $cMetaKeywords = $cKatKeywords;

        return strip_tags($cMetaKeywords);
    }
    $cMetaKeywords = str_replace('"', '', $cMetaKeywords);
    $cMetaKeywords = StringHandler::htmlentitydecode($cMetaKeywords, ENT_NOQUOTES);

    return strip_tags($cMetaKeywords);
}

/**
 * Baut für die NaviMetas die gesetzten Mainwords + Filter und stellt diese vor jedem Meta vorne an.
 *
 * @param object $NaviFilter
 * @param object $oSuchergebnisse
 * @return string
 */
function gibMetaStart($NaviFilter, $oSuchergebnisse)
{
    $cMetaTitle = '';

    // MerkmalWert
    if (isset($NaviFilter->MerkmalWert->kMerkmalWert, $NaviFilter->MerkmalWert->cName) &&
        $NaviFilter->MerkmalWert->kMerkmalWert > 0
    ) {
        $cMetaTitle .= $NaviFilter->MerkmalWert->cName;
    } elseif (isset($NaviFilter->Kategorie->kKategorie, $NaviFilter->Kategorie->cName) &&
        $NaviFilter->Kategorie->kKategorie > 0
    ) {
        // Kategorie
        $cMetaTitle .= $NaviFilter->Kategorie->cName;
    } elseif (isset($NaviFilter->Hersteller->kHersteller) && $NaviFilter->Hersteller->kHersteller > 0) {
        // Hersteller
        $cMetaTitle .= $NaviFilter->Hersteller->cName;
    } elseif (isset($NaviFilter->Tag->kTag, $NaviFilter->Tag->cName) && $NaviFilter->Tag->kTag > 0) {
        // Tag
        $cMetaTitle .= $NaviFilter->Tag->cName;
    } elseif (isset($NaviFilter->Suche->cSuche) && strlen($NaviFilter->Suche->cSuche) > 0) {
        // Suchebegriff
        $cMetaTitle .= $NaviFilter->Suche->cSuche;
    } elseif (isset($NaviFilter->Suchspecial->kKey, $NaviFilter->Suchspecial->cName) &&
        $NaviFilter->Suchspecial->kKey > 0
    ) {
        // Suchspecial
        $cMetaTitle .= $NaviFilter->Suchspecial->cName;
    }
    // Kategoriefilter
    if (isset($NaviFilter->KategorieFilter->kKategorie) && $NaviFilter->KategorieFilter->kKategorie > 0) {
        $cMetaTitle .= ' ' . $NaviFilter->KategorieFilter->cName;
    }
    // Herstellerfilter
    if (isset($NaviFilter->HerstellerFilter->kHersteller) &&
        $NaviFilter->HerstellerFilter->kHersteller > 0 &&
        strlen($oSuchergebnisse->Herstellerauswahl[0]->cName) > 0
    ) {
        $cMetaTitle .= ' ' . $NaviFilter->HerstellerFilter->cName;
    }
    // Tagfilter
    if (isset($NaviFilter->TagFilter, $NaviFilter->TagFilter[0]->cName) &&
        is_array($NaviFilter->TagFilter) &&
        count($NaviFilter->TagFilter) > 0
    ) {
        $cMetaTitle .= ' ' . $NaviFilter->TagFilter[0]->cName;
    }
    // Suchbegrifffilter
    if (is_array($NaviFilter->SuchFilter) && count($NaviFilter->SuchFilter) > 0) {
        foreach ($NaviFilter->SuchFilter as $i => $oSuchFilter) {
            if (isset($oSuchFilter->cName)) {
                $cMetaTitle .= ' ' . $oSuchFilter->cName;
            }
        }
    }
    // Suchspecialfilter
    if (isset($NaviFilter->SuchspecialFilter->kKey) && $NaviFilter->SuchspecialFilter->kKey > 0) {
        switch ($NaviFilter->SuchspecialFilter->kKey) {
            case SEARCHSPECIALS_BESTSELLER:
                $cMetaTitle .= ' ' . Shop::Lang()->get('bestsellers', 'global');
                break;

            case SEARCHSPECIALS_SPECIALOFFERS:
                $cMetaTitle .= ' ' . Shop::Lang()->get('specialOffers', 'global');
                break;

            case SEARCHSPECIALS_NEWPRODUCTS:
                $cMetaTitle .= ' ' . Shop::Lang()->get('newProducts', 'global');
                break;

            case SEARCHSPECIALS_TOPOFFERS:
                $cMetaTitle .= ' ' . Shop::Lang()->get('topOffers', 'global');
                break;

            case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                $cMetaTitle .= ' ' . Shop::Lang()->get('upcomingProducts', 'global');
                break;

            case SEARCHSPECIALS_TOPREVIEWS:
                $cMetaTitle .= ' ' . Shop::Lang()->get('topReviews', 'global');
                break;

            default:
                break;
        }
    }
    // MerkmalWertfilter
    if (is_array($NaviFilter->MerkmalFilter) && count($NaviFilter->MerkmalFilter) > 0) {
        foreach ($NaviFilter->MerkmalFilter as $oMerkmalFilter) {
            if (isset($oMerkmalFilter->cName)) {
                $cMetaTitle .= ' ' . $oMerkmalFilter->cName;
            }
        }
    }

    return ltrim($cMetaTitle);
}

/**
 * @param string $cSuche
 * @param int    $kSprache
 * @return int
 */
function gibSuchanfrageKey($cSuche, $kSprache)
{
    if (strlen($cSuche) > 0 && $kSprache > 0) {
        $oSuchanfrage = Shop::DB()->select('tsuchanfrage', 'cSuche', Shop::DB()->escape($cSuche), 'kSprache', (int)$kSprache);

        if (isset($oSuchanfrage->kSuchanfrage) && $oSuchanfrage->kSuchanfrage > 0) {
            return (int)$oSuchanfrage->kSuchanfrage;
        }
    }

    return 0;
}

/**
 * @param array  $Einstellungen
 * @param object $NaviFilter
 * @param int    $nDarstellung
 */
function gibErweiterteDarstellung($Einstellungen, $NaviFilter, $nDarstellung = 0)
{
    global $smarty;

    if (!isset($_SESSION['oErweiterteDarstellung'])) {
        $nStdDarstellung                                    = 0;
        $_SESSION['oErweiterteDarstellung']                 = new stdClass();
        $_SESSION['oErweiterteDarstellung']->cURL_arr       = [];
        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = ERWDARSTELLUNG_ANSICHT_ANZAHL_STD;

        if (isset($NaviFilter->Kategorie->kKategorie) && $NaviFilter->Kategorie->kKategorie > 0) {
            $oKategorie = new Kategorie($NaviFilter->Kategorie->kKategorie);
            if (!empty($oKategorie->categoryFunctionAttributes[KAT_ATTRIBUT_DARSTELLUNG])) {
                $nStdDarstellung = (int)$oKategorie->categoryFunctionAttributes[KAT_ATTRIBUT_DARSTELLUNG];
            }
        }
        if ($nDarstellung === 0 &&
            isset($Einstellungen['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht']) &&
            (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht'] > 0
        ) {
            $nStdDarstellung = (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht'];
        }
        if ($nStdDarstellung > 0) {
            switch ($nStdDarstellung) {
                case ERWDARSTELLUNG_ANSICHT_LISTE:
                    $_SESSION['oErweiterteDarstellung']->nDarstellung = ERWDARSTELLUNG_ANSICHT_LISTE;
                    if (isset($_SESSION['ArtikelProSeite'])) {
                        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                    } elseif ((int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'] > 0) {
                        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'];
                    }
                    break;
                case ERWDARSTELLUNG_ANSICHT_GALERIE:
                    $_SESSION['oErweiterteDarstellung']->nDarstellung = ERWDARSTELLUNG_ANSICHT_GALERIE;
                    if (isset($_SESSION['ArtikelProSeite'])) {
                        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                    } elseif ((int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung2'] > 0) {
                        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung2'];
                    }
                    break;
                case ERWDARSTELLUNG_ANSICHT_MOSAIK:
                    $_SESSION['oErweiterteDarstellung']->nDarstellung = ERWDARSTELLUNG_ANSICHT_MOSAIK;
                    if (isset($_SESSION['ArtikelProSeite'])) {
                        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                    } elseif ((int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung3'] > 0) {
                        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung3'];
                    }
                    break;
                default: // when given invalid option from wawi attribute
                    $nDarstellung = ERWDARSTELLUNG_ANSICHT_LISTE;
                    if (isset($Einstellungen['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht']) &&
                        (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht'] > 0
                    ) { // fallback to configured default
                        $nDarstellung = (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht'];
                    }
                    $_SESSION['oErweiterteDarstellung']->nDarstellung = $nDarstellung;
                    if (isset($_SESSION['ArtikelProSeite'])) {
                        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                    } elseif ((int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'] > 0) {
                        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'];
                    }
                    break;
            }
        } else {
            $_SESSION['oErweiterteDarstellung']->nDarstellung = ERWDARSTELLUNG_ANSICHT_LISTE; // Std ist Listendarstellung
            if (isset($_SESSION['ArtikelProSeite'])) {
                $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
            } elseif ((int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'] > 0) {
                $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'];
            }
        }
    }
    if ($nDarstellung > 0) {
        $_SESSION['oErweiterteDarstellung']->nDarstellung = $nDarstellung;
        switch ($_SESSION['oErweiterteDarstellung']->nDarstellung) {
            case ERWDARSTELLUNG_ANSICHT_LISTE:
                $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = ERWDARSTELLUNG_ANSICHT_ANZAHL_STD;
                if ((int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'] > 0) {
                    $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'];
                }
                break;
            case ERWDARSTELLUNG_ANSICHT_GALERIE:
                $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = ERWDARSTELLUNG_ANSICHT_ANZAHL_STD;
                if ((int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung2'] > 0) {
                    $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung2'];
                }
                break;
            case ERWDARSTELLUNG_ANSICHT_MOSAIK:
                $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = ERWDARSTELLUNG_ANSICHT_ANZAHL_STD;
                if ((int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung3'] > 0) {
                    $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = (int)$Einstellungen['artikeluebersicht']['artikeluebersicht_anzahl_darstellung3'];
                }
                break;
        }

        if (isset($_SESSION['ArtikelProSeite'])) {
            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
        }
    }
    if (isset($_SESSION['oErweiterteDarstellung'])) {
        $naviURL                                                                      = gibNaviURL($NaviFilter, false, null);
        $_SESSION['oErweiterteDarstellung']->cURL_arr[ERWDARSTELLUNG_ANSICHT_LISTE]   = $naviURL . '&amp;ed=' . ERWDARSTELLUNG_ANSICHT_LISTE;
        $_SESSION['oErweiterteDarstellung']->cURL_arr[ERWDARSTELLUNG_ANSICHT_GALERIE] = $naviURL . '&amp;ed=' . ERWDARSTELLUNG_ANSICHT_GALERIE;
        $_SESSION['oErweiterteDarstellung']->cURL_arr[ERWDARSTELLUNG_ANSICHT_MOSAIK]  = $naviURL . '&amp;ed=' . ERWDARSTELLUNG_ANSICHT_MOSAIK;
        $smarty->assign('oErweiterteDarstellung', $_SESSION['oErweiterteDarstellung']);
    }

    $smarty->assign('ERWDARSTELLUNG_ANSICHT_LISTE', ERWDARSTELLUNG_ANSICHT_LISTE)
           ->assign('ERWDARSTELLUNG_ANSICHT_GALERIE', ERWDARSTELLUNG_ANSICHT_GALERIE)
           ->assign('ERWDARSTELLUNG_ANSICHT_MOSAIK', ERWDARSTELLUNG_ANSICHT_MOSAIK);
}

/**
 * @param object $NaviFilter
 */
function setzeUsersortierung($NaviFilter)
{
    global $Einstellungen, $oSuchergebnisse, $AktuelleKategorie;
    if (!isset($Einstellungen['artikeluebersicht'], $Einstellungen['suchspecials'])) {
        $Einstellungen = Shop::getSettings([CONF_GLOBAL, CONF_ARTIKELUEBERSICHT, CONF_NAVIGATIONSFILTER, CONF_SUCHSPECIAL]);
    }
    // Der User möchte die Standardsortierung wiederherstellen
    if (verifyGPCDataInteger('Sortierung') > 0 && verifyGPCDataInteger('Sortierung') === 100) {
        unset($_SESSION['Usersortierung'], $_SESSION['nUsersortierungWahl'], $_SESSION['UsersortierungVorSuche']);
    }
    // Wenn noch keine Sortierung gewählt wurde => setze Standard-Sortierung aus Option
    if (!isset($_SESSION['Usersortierung']) && isset($Einstellungen['artikeluebersicht']['artikeluebersicht_artikelsortierung'])) {
        unset($_SESSION['nUsersortierungWahl']);
        $_SESSION['Usersortierung'] = $Einstellungen['artikeluebersicht']['artikeluebersicht_artikelsortierung'];
    }
    if (!isset($_SESSION['nUsersortierungWahl']) && isset($Einstellungen['artikeluebersicht']['artikeluebersicht_artikelsortierung'])) {
        $_SESSION['Usersortierung'] = $Einstellungen['artikeluebersicht']['artikeluebersicht_artikelsortierung'];
    }
    // Eine Suche wurde ausgeführt und die Suche wird auf die Suchtreffersuche eingestellt
    if (isset($NaviFilter->Suche->kSuchCache) && $NaviFilter->Suche->kSuchCache > 0 && !isset($_SESSION['nUsersortierungWahl'])) {
        // nur bei initialsuche Sortierung zurücksetzen
        $_SESSION['UsersortierungVorSuche'] = $_SESSION['Usersortierung'];
        $_SESSION['Usersortierung']         = SEARCH_SORT_STANDARD;
    }
    // Kategorie Funktionsattribut
    if (!empty($AktuelleKategorie->categoryFunctionAttributes[KAT_ATTRIBUT_ARTIKELSORTIERUNG])) {
        $_SESSION['Usersortierung'] = $AktuelleKategorie->categoryFunctionAttributes[KAT_ATTRIBUT_ARTIKELSORTIERUNG];
    }
    // Wurde zuvor etwas gesucht? Dann die Einstellung des Users vor der Suche wiederherstellen
    if (isset($_SESSION['UsersortierungVorSuche']) && (int)$_SESSION['UsersortierungVorSuche'] > 0) {
        $_SESSION['Usersortierung'] = $_SESSION['UsersortierungVorSuche'];
    }
    // Suchspecial sortierung
    if (isset($NaviFilter->Suchspecial->kKey) && $NaviFilter->Suchspecial->kKey > 0) {
        // Gibt die Suchspecialeinstellungen als Assoc Array zurück, wobei die Keys des Arrays der kKey vom Suchspecial sind.
        $oSuchspecialEinstellung_arr = gibSuchspecialEinstellungMapping($Einstellungen['suchspecials']);
        // -1 = Keine spezielle Sortierung
        if (count($oSuchspecialEinstellung_arr) > 0 &&
            isset($oSuchspecialEinstellung_arr[$NaviFilter->Suchspecial->kKey]) &&
            $oSuchspecialEinstellung_arr[$NaviFilter->Suchspecial->kKey] != -1
        ) {
            $_SESSION['Usersortierung'] = $oSuchspecialEinstellung_arr[$NaviFilter->Suchspecial->kKey];
        }
    }
    // Der User hat expliziet eine Sortierung eingestellt
    if (verifyGPCDataInteger('Sortierung') > 0 && verifyGPCDataInteger('Sortierung') !== 100) {
        $_SESSION['Usersortierung']         = verifyGPCDataInteger('Sortierung');
        $_SESSION['UsersortierungVorSuche'] = $_SESSION['Usersortierung'];
        $_SESSION['nUsersortierungWahl']    = 1;
        $oSuchergebnisse->Sortierung        = $_SESSION['Usersortierung'];
        setFsession(0, $_SESSION['Usersortierung'], 0);
    }
}

/**
 * @param object $NaviFilter
 * @param bool   $bSeo
 * @param object $oSeitenzahlen
 * @param int    $nMaxAnzeige
 * @param string $cFilterShopURL
 * @return array
 */
function baueSeitenNaviURL($NaviFilter, $bSeo, $oSeitenzahlen, $nMaxAnzeige = 7, $cFilterShopURL = '')
{
    if (strlen($cFilterShopURL) > 0) {
        $bSeo = false;
    }
    $cURL       = '';
    $oSeite_arr = [];
    $nAnfang    = 0; // Wenn die aktuelle Seite - $nMaxAnzeige größer 0 ist, wird nAnfang gesetzt
    $nEnde      = 0; // Wenn die aktuelle Seite + $nMaxAnzeige <= $nSeiten ist, wird nEnde gesetzt
    $nVon       = 0; // Die aktuellen Seiten in der Navigation, die angezeigt werden sollen.
    $nBis       = 0; // Begrenzt durch $nMaxAnzeige.
    $naviURL    = gibNaviURL($NaviFilter, $bSeo, null);
    if (isset($oSeitenzahlen->MaxSeiten, $oSeitenzahlen->AktuelleSeite) &&
        $oSeitenzahlen->MaxSeiten > 0 &&
        $oSeitenzahlen->AktuelleSeite > 0
    ) {
        $oSeitenzahlen->AktuelleSeite = (int)$oSeitenzahlen->AktuelleSeite;
        $nMax                         = floor($nMaxAnzeige / 2);
        if ($oSeitenzahlen->MaxSeiten > $nMaxAnzeige) {
            if ($oSeitenzahlen->AktuelleSeite - $nMax >= 1) {
                $nDiff = 0;
                $nVon  = $oSeitenzahlen->AktuelleSeite - $nMax;
            } else {
                $nVon  = 1;
                $nDiff = $nMax - $oSeitenzahlen->AktuelleSeite + 1;
            }
            if ($oSeitenzahlen->AktuelleSeite + $nMax + $nDiff <= $oSeitenzahlen->MaxSeiten) {
                $nBis = $oSeitenzahlen->AktuelleSeite + $nMax + $nDiff;
            } else {
                $nDiff = $oSeitenzahlen->AktuelleSeite + $nMax - $oSeitenzahlen->MaxSeiten;
                if ($nDiff == 0) {
                    $nVon -= ($nMaxAnzeige - ($nMax + 1));
                } elseif ($nDiff > 0) {
                    $nVon = $oSeitenzahlen->AktuelleSeite - $nMax - $nDiff;
                }
                $nBis = (int)$oSeitenzahlen->MaxSeiten;
            }
            // Laufe alle Seiten durch und baue URLs + Seitenzahl
            for ($i = $nVon; $i <= $nBis; $i++) {
                $oSeite         = new stdClass();
                $oSeite->nSeite = $i;
                if ((int)$i === (int)$oSeitenzahlen->AktuelleSeite) {
                    $oSeite->cURL = '';
                } else {
                    if ($oSeite->nSeite === 1) {
                        $oSeite->cURL = $naviURL . $cFilterShopURL;
                    } else {
                        if ($bSeo) {
                            $cURL = $naviURL;
                            if (strpos(basename($cURL), 'navi.php') !== false) {
                                $oSeite->cURL = $cURL . '&amp;seite=' . $oSeite->nSeite . $cFilterShopURL;
                            } else {
                                $oSeite->cURL = $cURL . SEP_SEITE . $oSeite->nSeite;
                            }
                        } else {
                            $oSeite->cURL = $naviURL . '&amp;seite=' . $oSeite->nSeite . $cFilterShopURL;
                        }
                    }
                }

                $oSeite_arr[] = $oSeite;
            }
        } else {
            // Laufe alle Seiten durch und baue URLs + Seitenzahl
            for ($i = 0; $i < $oSeitenzahlen->MaxSeiten; $i++) {
                $oSeite         = new stdClass();
                $oSeite->nSeite = $i + 1;

                if ($i + 1 === $oSeitenzahlen->AktuelleSeite) {
                    $oSeite->cURL = '';
                } else {
                    if ($oSeite->nSeite === 1) {
                        $oSeite->cURL = $naviURL . $cFilterShopURL;
                    } else {
                        if ($bSeo) {
                            $cURL = $naviURL;
                            if (strpos(basename($cURL), 'navi.php') !== false) {
                                $oSeite->cURL = $cURL . '&amp;seite=' . $oSeite->nSeite . $cFilterShopURL;
                            } else {
                                $oSeite->cURL = $cURL . SEP_SEITE . $oSeite->nSeite;
                            }
                        } else {
                            $oSeite->cURL = $naviURL . '&amp;seite=' . $oSeite->nSeite . $cFilterShopURL;
                        }
                    }
                }
                $oSeite_arr[] = $oSeite;
            }
        }
        // Baue Zurück-URL
        $oSeite_arr['zurueck']       = new stdClass();
        $oSeite_arr['zurueck']->nBTN = 1;
        if ($oSeitenzahlen->AktuelleSeite > 1) {
            $oSeite_arr['zurueck']->nSeite = (int)$oSeitenzahlen->AktuelleSeite - 1;
            if ($oSeite_arr['zurueck']->nSeite === 1) {
                $oSeite_arr['zurueck']->cURL = $naviURL . $cFilterShopURL;
            } else {
                if ($bSeo) {
                    $cURL = $naviURL;
                    if (strpos(basename($cURL), 'navi.php') !== false) {
                        $oSeite_arr['zurueck']->cURL = $cURL . '&amp;seite=' .
                            $oSeite_arr['zurueck']->nSeite . $cFilterShopURL;
                    } else {
                        $oSeite_arr['zurueck']->cURL = $cURL . SEP_SEITE .
                            $oSeite_arr['zurueck']->nSeite;
                    }
                } else {
                    $oSeite_arr['zurueck']->cURL = $naviURL . '&amp;seite=' .
                        $oSeite_arr['zurueck']->nSeite . $cFilterShopURL;
                }
            }
        }
        // Baue Vor-URL
        $oSeite_arr['vor']       = new stdClass();
        $oSeite_arr['vor']->nBTN = 1;
        if ($oSeitenzahlen->AktuelleSeite < $oSeitenzahlen->maxSeite) {
            $oSeite_arr['vor']->nSeite = $oSeitenzahlen->AktuelleSeite + 1;
            if ($bSeo) {
                $cURL = $naviURL;
                if (strpos(basename($cURL), 'navi.php') !== false) {
                    $oSeite_arr['vor']->cURL = $cURL . '&amp;seite=' . $oSeite_arr['vor']->nSeite . $cFilterShopURL;
                } else {
                    $oSeite_arr['vor']->cURL = $cURL . SEP_SEITE . $oSeite_arr['vor']->nSeite;
                }
            } else {
                $oSeite_arr['vor']->cURL = $naviURL . '&amp;seite=' . $oSeite_arr['vor']->nSeite . $cFilterShopURL;
            }
        }
    }

    return $oSeite_arr;
}

/**
 * @param object $NaviFilter
 * @return mixed|stdClass
 */
function bauFilterSQL($NaviFilter)
{
    $cacheID = 'fsql_' . md5(serialize($NaviFilter));
    if (($FilterSQL = Shop::Cache()->get($cacheID)) === false) {
        $FilterSQL = new stdClass();
        //Filter SQLs Objekte
        $FilterSQL->oHerstellerFilterSQL      = gibHerstellerFilterSQL($NaviFilter);
        $FilterSQL->oKategorieFilterSQL       = gibKategorieFilterSQL($NaviFilter);
        $FilterSQL->oMerkmalFilterSQL         = gibMerkmalFilterSQL($NaviFilter);
        $FilterSQL->oTagFilterSQL             = gibTagFilterSQL($NaviFilter);
        $FilterSQL->oBewertungSterneFilterSQL = gibBewertungSterneFilterSQL($NaviFilter);
        $FilterSQL->oPreisspannenFilterSQL    = gibPreisspannenFilterSQL($NaviFilter);
        $FilterSQL->oSuchFilterSQL            = gibSuchFilterSQL($NaviFilter);
        $FilterSQL->oSuchspecialFilterSQL     = gibSuchspecialFilterSQL($NaviFilter);
        $FilterSQL->oArtikelAttributFilterSQL = gibArtikelAttributFilterSQL($NaviFilter);

        executeHook(HOOK_FILTER_INC_BAUFILTERSQL, [
            'NaviFilter' => &$NaviFilter,
            'FilterSQL'  => &$FilterSQL
            ]
        );

        Shop::Cache()->set($cacheID, $FilterSQL, [CACHING_GROUP_CATEGORY, CACHING_GROUP_FILTER]);
    }

    return $FilterSQL;
}

/**
 * @param null|array $Einstellungen
 * @param bool $bExtendedJTLSearch
 * @return array
 */
function gibSortierliste($Einstellungen = null, $bExtendedJTLSearch = false)
{
    $Sortierliste = [];
    $search       = [];
    if ($bExtendedJTLSearch) {
        $names     = ['suche_sortierprio_name', 'suche_sortierprio_name_ab', 'suche_sortierprio_preis', 'suche_sortierprio_preis_ab'];
        $values    = [SEARCH_SORT_NAME_ASC, SEARCH_SORT_NAME_DESC, SEARCH_SORT_PRICE_ASC, SEARCH_SORT_PRICE_DESC];
        $languages = ['sortNameAsc', 'sortNameDesc', 'sortPriceAsc', 'sortPriceDesc'];
        foreach ($names as $i => $name) {
            $obj                  = new stdClass();
            $obj->name            = $name;
            $obj->value           = $values[$i];
            $obj->angezeigterName = Shop::Lang()->get($languages[$i], 'global');

            $Sortierliste[] = $obj;
        }

        return $Sortierliste;
    }
    if ($Einstellungen === null) {
        $Einstellungen = Shop::getSettings([CONF_ARTIKELUEBERSICHT]);
    }
    while (($obj = gibNextSortPrio($search, $Einstellungen)) !== null) {
        $search[] = $obj->name;
        unset($obj->name);
        $Sortierliste[] = $obj;
    }

    return $Sortierliste;
}

/**
 * @param array $search
 * @param null|array $Einstellungen
 * @return null|stdClass
 */
function gibNextSortPrio($search, $Einstellungen = null)
{
    if ($Einstellungen === null) {
        $Einstellungen = Shop::getSettings([CONF_ARTIKELUEBERSICHT]);
    }
    $max = 0;
    $obj = null;
    if ($max < $Einstellungen['artikeluebersicht']['suche_sortierprio_name'] &&
        !in_array('suche_sortierprio_name', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_name';
        $obj->value           = SEARCH_SORT_NAME_ASC;
        $obj->angezeigterName = Shop::Lang()->get('sortNameAsc', 'global');
        $max                  = $Einstellungen['artikeluebersicht']['suche_sortierprio_name'];
    }
    if ($max < $Einstellungen['artikeluebersicht']['suche_sortierprio_name_ab'] &&
        !in_array('suche_sortierprio_name_ab', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_name_ab';
        $obj->value           = SEARCH_SORT_NAME_DESC;
        $obj->angezeigterName = Shop::Lang()->get('sortNameDesc', 'global');
        $max                  = $Einstellungen['artikeluebersicht']['suche_sortierprio_name_ab'];
    }
    if ($max < $Einstellungen['artikeluebersicht']['suche_sortierprio_preis'] &&
        !in_array('suche_sortierprio_preis', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_preis';
        $obj->value           = SEARCH_SORT_PRICE_ASC;
        $obj->angezeigterName = Shop::Lang()->get('sortPriceAsc', 'global');
        $max                  = $Einstellungen['artikeluebersicht']['suche_sortierprio_preis'];
    }
    if ($max < $Einstellungen['artikeluebersicht']['suche_sortierprio_preis_ab'] &&
        !in_array('suche_sortierprio_preis_ab', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_preis_ab';
        $obj->value           = SEARCH_SORT_PRICE_DESC;
        $obj->angezeigterName = Shop::Lang()->get('sortPriceDesc', 'global');
        $max                  = $Einstellungen['artikeluebersicht']['suche_sortierprio_preis_ab'];
    }
    if ($max < $Einstellungen['artikeluebersicht']['suche_sortierprio_ean'] &&
        !in_array('suche_sortierprio_ean', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_ean';
        $obj->value           = SEARCH_SORT_EAN;
        $obj->angezeigterName = Shop::Lang()->get('sortEan', 'global');
        $max                  = $Einstellungen['artikeluebersicht']['suche_sortierprio_ean'];
    }
    if ($max < $Einstellungen['artikeluebersicht']['suche_sortierprio_erstelldatum'] &&
        !in_array('suche_sortierprio_erstelldatum', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_erstelldatum';
        $obj->value           = SEARCH_SORT_NEWEST_FIRST;
        $obj->angezeigterName = Shop::Lang()->get('sortNewestFirst', 'global');
        $max                  = $Einstellungen['artikeluebersicht']['suche_sortierprio_erstelldatum'];
    }
    if ($max < $Einstellungen['artikeluebersicht']['suche_sortierprio_artikelnummer'] &&
        !in_array('suche_sortierprio_artikelnummer', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_artikelnummer';
        $obj->value           = SEARCH_SORT_PRODUCTNO;
        $obj->angezeigterName = Shop::Lang()->get('sortProductno', 'global');
        $max                  = $Einstellungen['artikeluebersicht']['suche_sortierprio_artikelnummer'];
    }
    if ($max < $Einstellungen['artikeluebersicht']['suche_sortierprio_lagerbestand'] &&
        !in_array('suche_sortierprio_lagerbestand', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_lagerbestand';
        $obj->value           = SEARCH_SORT_AVAILABILITY;
        $obj->angezeigterName = Shop::Lang()->get('sortAvailability', 'global');
        $max                  = $Einstellungen['artikeluebersicht']['suche_sortierprio_lagerbestand'];
    }
    if ($max < $Einstellungen['artikeluebersicht']['suche_sortierprio_gewicht'] &&
        !in_array('suche_sortierprio_gewicht', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_gewicht';
        $obj->value           = SEARCH_SORT_WEIGHT;
        $obj->angezeigterName = Shop::Lang()->get('sortWeight', 'global');
        $max                  = $Einstellungen['artikeluebersicht']['suche_sortierprio_gewicht'];
    }
    if ($max < $Einstellungen['artikeluebersicht']['suche_sortierprio_erscheinungsdatum'] &&
        !in_array('suche_sortierprio_erscheinungsdatum', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_erscheinungsdatum';
        $obj->value           = SEARCH_SORT_DATEOFISSUE;
        $obj->angezeigterName = Shop::Lang()->get('sortDateofissue', 'global');
        $max                  = $Einstellungen['artikeluebersicht']['suche_sortierprio_erscheinungsdatum'];
    }
    if ($max < $Einstellungen['artikeluebersicht']['suche_sortierprio_bestseller'] &&
        !in_array('suche_sortierprio_bestseller', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_bestseller';
        $obj->value           = SEARCH_SORT_BESTSELLER;
        $obj->angezeigterName = Shop::Lang()->get('bestseller', 'global');
        $max                  = $Einstellungen['artikeluebersicht']['suche_sortierprio_bestseller'];
    }
    if ($max < $Einstellungen['artikeluebersicht']['suche_sortierprio_bewertung'] &&
        !in_array('suche_sortierprio_bewertung', $search, true)
    ) {
        $obj                  = new stdClass();
        $obj->name            = 'suche_sortierprio_bewertung';
        $obj->value           = SEARCH_SORT_RATING;
        $obj->angezeigterName = Shop::Lang()->get('rating', 'global');
    }

    return $obj;
}
