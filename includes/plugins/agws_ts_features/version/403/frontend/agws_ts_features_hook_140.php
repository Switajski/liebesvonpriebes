<?php
/**
 * Created by ag-websolutions.de
 *
 * File: agws_ts_features_hook_140.php
 * Project: agws_trustedshops
 */

include_once($oPlugin->cAdminmenuPfad . 'inc/agws_ts_features_predefine.php');
require_once $oPlugin->cAdminmenuPfad . 'inc/class.agws_plugin_ts_feature.helper.php';

$helper = agwsPluginHelperTS::getInstance($oPlugin);

if ($helper->isShop4()) {
    $smarty = Shop::Smarty();
} else {
    global $smarty;
}

($helper->isShop4()) ?
    $queryResult = Shop::DB()->selectSingleRow("xplugin_agws_ts_features_config", "iTS_Sprache", (int) $_SESSION['kSprache']) :
    $queryResult = $GLOBALS["DB"]->selectSingleRow("xplugin_agws_ts_features_config", "iTS_Sprache", (int) $_SESSION['kSprache']);

if (isset($queryResult)) {
    //pQuery-insert "Trustbadge" auf allen Seiten
    if ($queryResult->cTS_BadgeCode != "0")
        pq('body')->append($queryResult->cTS_BadgeCode);
    /**/

    //pQuery-remove "trustedShopsCheckout (alt)" auf allen Seiten - nur Shop 3
    if (pq('#trustedShopsCheckout')->length() > 0)
        pq('#trustedShopsCheckout')->remove();
    /**/

    //pQuery-insert "ReviewSticker" auf allen Seiten im Footer wenn aktiviert
    if ($queryResult->bTS_ReviewStickerShow == "1" && $queryResult->iTS_ReviewStickerPosition == "3" && $helper->gibSeiten__Typ() != PAGE_ARTIKEL) {
        $smarty->assign('bIstShop4', $helper->isShop4());

        $htmlReviewStickerWrapper = $smarty->fetch($oPlugin->cFrontendPfad . "tpl_inc/inc_ts_features_review_footer.tpl");
        if (file_exists(PFAD_PLUGIN . $oPlugin->cVerzeichnis . "/" . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . "/" . PFAD_PLUGIN_FRONTEND . "tpl_inc/inc_ts_features_review_footer_custom.tpl"))
            $htmlReviewStickerWrapper = $smarty->fetch($oPlugin->cFrontendPfad . "tpl_inc/inc_ts_features_review_footer_custom.tpl");

        if ($helper->isShop4()) {
            $ts_review_pq_selector = TS_REVIEW_PQ_SELECTOR_V4_FOOTER;
            $ts_review_pq_method = TS_REVIEW_PQ_METHOD_V4_FOOTER;
        } else {
            $ts_review_pq_selector = TS_REVIEW_PQ_SELECTOR_FOOTER;
            $ts_review_pq_method = TS_REVIEW_PQ_METHOD_FOOTER;
        }

        pq($ts_review_pq_selector)->$ts_review_pq_method($htmlReviewStickerWrapper);
        pq('#tsReviewStickerWrapper')->append($queryResult->cTS_ReviewStickerCode);
    }
    /**/

    //pQuery-insert "RatingWidget" auf allen Seiten im Footer wenn aktiviert
    if ($queryResult->bTS_RatingWidgetShow == "1" && $queryResult->iTS_RatingWidgetPosition == "3") {
        $smarty->assign('bIstShop4', $helper->isShop4());

        $htmlRatingWidget = $smarty->fetch($oPlugin->cFrontendPfad . "tpl_inc/inc_ts_features_rating_footer.tpl");
        if (file_exists(PFAD_PLUGIN . $oPlugin->cVerzeichnis . "/" . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . "/" . PFAD_PLUGIN_FRONTEND . "tpl_inc/inc_ts_features_rating_footer_custom.tpl"))
            $htmlRatingWidget = $smarty->fetch($oPlugin->cFrontendPfad . "tpl_inc/inc_ts_features_rating_footer_custom.tpl");
        
        if ($helper->isShop4()) {
            $ts_rating_pq_selector = TS_RATING_PQ_SELECTOR_FOOTER_V4;
            $ts_rating_pq_method = TS_RATING_PQ_METHOD_FOOTER_V4;
        } else {
            $ts_rating_pq_selector = TS_RATING_PQ_SELECTOR_FOOTER;
            $ts_rating_pq_method = TS_RATING_PQ_METHOD_FOOTER;
        }

        pq($ts_rating_pq_selector)->$ts_rating_pq_method($htmlRatingWidget);
    }
    /**/

    //pQuery-inserts "Produktbewertung" auf Artikeldetailseite in Registertab
    $oArtikel_tmp = $smarty->get_template_vars('Artikel');
    if ($helper->gibSeiten__Typ() == PAGE_ARTIKEL && $queryResult->bTS_ProductStickerShow == 1) {
        $smarty->assign('bIstShop4', $helper->isShop4());
        $smarty->assign('agws_ts_features_reviews_sku', "['" . $oArtikel_tmp->cArtNr . "']");
        $smarty->assign("agws_ts_features_showtab", "on");

        if ($oArtikel_tmp->nIstVater == 1 && $queryResult->iTS_ProductStickerArt == 2) {
            $smarty->assign("agws_ts_features_showtab", "off");
        } elseif ($oArtikel_tmp->nIstVater == 1 && $queryResult->iTS_ProductStickerArt == 1 && $oArtikel_tmp->kVariKindArtikel == null) {
            $smarty->assign("agws_ts_features_showtab", "on");
            $sql = "SELECT cArtNr FROM `tartikel` WHERE kVaterArtikel = " . (int) $oArtikel_tmp->kArtikel;
            ($helper->isShop4()) ?
                $agws_parent_reviews = Shop::DB()->query($sql, 2) :
                $agws_parent_reviews = $GLOBALS["DB"]->executeQuery($sql, 2);

            if (count($agws_parent_reviews) > 0) {
                $agws_ts_features_reviews_sku = "['";
                foreach ($agws_parent_reviews as $agws_parent_reviewsku) {
                    $agws_ts_features_reviews_sku .= $agws_parent_reviewsku->cArtNr . "','";
                }

                $agws_ts_features_reviews_sku = substr($agws_ts_features_reviews_sku, 0, -2);
                $agws_ts_features_reviews_sku .= "]";

                $smarty->assign('agws_ts_features_reviews_sku', $agws_ts_features_reviews_sku);
            } else {
                $smarty->assign("agws_ts_features_showtab", "off");
            }
        } elseif ($oArtikel_tmp->nIstVater == 1 && $queryResult->iTS_ProductStickerArt == 1 && $oArtikel_tmp->kVariKindArtikel > 0) {
            $smarty->assign('agws_ts_features_reviews_sku', "['" . $oArtikel_tmp->cArtNr . "']");
        }
        //tab-titel quelltext erzeugen
        $smarty->assign("agws_ts_features_tabtitel", $oPlugin->oPluginSprachvariableAssoc_arr['agws_ts_features_tabtitel']);

        //tab-inhalt quelltext erzeugen
        $smarty->assign("agws_ts_features_TSID", $queryResult->cTS_ID);
        $smarty->assign("agws_ts_features_tabintrotext", $oPlugin->oPluginSprachvariableAssoc_arr['agws_ts_features_tabintrotext']);

        //neuen Tab einfügen als letztes element
        if ($helper->isShop4()) {
            $ts_review_artikel_pq_selector_semtabs = TS_REVIEW_ARTIKEL_PQ_SELECTOR_SEMTABS_V4;
            $ts_review_artikel_pq_method_semtabs = TS_REVIEW_ARTIKEL_PQ_METHOD_SEMTABS_V4;
        } else {
            $ts_review_artikel_pq_selector_semtabs = TS_REVIEW_ARTIKEL_PQ_SELECTOR_SEMTABS;
            $ts_review_artikel_pq_method_semtabs = TS_REVIEW_ARTIKEL_PQ_METHOD_SEMTABS;
        }

        $newTab = $smarty->fetch($oPlugin->cFrontendPfad . "tpl_inc/inc_ts_features_review_article_tab.tpl");
        if (file_exists(PFAD_PLUGIN . $oPlugin->cVerzeichnis . "/" . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . "/" . PFAD_PLUGIN_FRONTEND . "tpl_inc/inc_ts_features_review_article_tab_custom.tpl"))
            $newTab = $smarty->fetch($oPlugin->cFrontendPfad . "tpl_inc/inc_ts_features_review_article_tab_custom.tpl");

        pq($ts_review_artikel_pq_selector_semtabs)->$ts_review_artikel_pq_method_semtabs($newTab);
    }

    //pQuery-inserts "trustedShopsCheckout" auf Bestellabschlussseite bzw. Statusseite
    if (($helper->gibSeiten__Typ() == PAGE_BESTELLABSCHLUSS || $helper->gibSeiten__Typ() == PAGE_BESTELLSTATUS) && isset($_SESSION['agws_kWarenkorb_TS']) && (int) $_SESSION['agws_kWarenkorb_TS'] > 0) {
        $ts_checkout_pq_selector = TS_CHECKOUT_PQ_SELECTOR;
        $ts_checkout_pq_method = TS_CHECKOUT_PQ_METHOD;

        ($helper->isShop4()) ?
            $agws_kWarenkorb_TS = Shop::DB()->selectSingleRow('tbestellung', 'kWarenkorb', (int) $_SESSION['agws_kWarenkorb_TS']) :
            $agws_kWarenkorb_TS = $GLOBALS["DB"]->selectSingleRow('tbestellung', 'kWarenkorb', (int) $_SESSION['agws_kWarenkorb_TS']);

        ($helper->isShop4()) ?
            $agws_kKunde_TS = Shop::DB()->selectSingleRow('tkunde', 'kKunde', (int) $_SESSION['agws_kKunde_TS']) :
            $agws_kKunde_TS = $GLOBALS["DB"]->executeQuery($sql, 1);


        $agws_bestellung_TS = new Bestellung($agws_kWarenkorb_TS->kBestellung);
        $agws_bestellung_TS->fuelleBestellung(0);
        $agws_oWarenkorb_Positionen_TS = $agws_bestellung_TS->Positionen;

        $smarty->assign('Warenkorb_Positionen_TS', $agws_oWarenkorb_Positionen_TS);
        $smarty->assign('Kunde_TS', $agws_kKunde_TS);

        $htmlResultCardCode = $smarty->fetch($oPlugin->cFrontendPfad . "tpl_inc/inc_ts_features_confirmation_page.tpl");
        if (file_exists(PFAD_PLUGIN . $oPlugin->cVerzeichnis . "/" . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . "/" . PFAD_PLUGIN_FRONTEND . "tpl_inc/inc_ts_features_confirmation_page_custom.tpl"))
            $htmlResultCardCode = $smarty->fetch($oPlugin->cFrontendPfad . "tpl_inc/inc_ts_features_confirmation_page_custom.tpl");

        pq($ts_checkout_pq_selector)->$ts_checkout_pq_method($htmlResultCardCode);

        unset($_SESSION['agws_kWarenkorb_TS']);
        unset($_SESSION['agws_kKunde_TS']);
    }
    /**/

    //pQuery-inserts "RichSnippets" auf Startseite, Kategorieseite, Artikeldetailseite
    if ($helper->gibSeiten__Typ() == PAGE_ARTIKEL && $queryResult->bTS_RichSnippetsProduct == '1' || $helper->gibSeiten__Typ() == PAGE_ARTIKELLISTE && $queryResult->bTS_RichSnippetsCategory == '1' || $helper->gibSeiten__Typ() == PAGE_STARTSEITE && $queryResult->bTS_RichSnippetsMain == '1') {
        $tsId = $queryResult->cTS_ID;
        $cacheFileName = PFAD_LOGFILES . $tsId . '.json';
        $cacheTimeOut = 43200; // half a day
        $apiUrl = str_replace("TS_ID", $queryResult->cTS_ID, TS_RICHSNIPPET_API_URL);
        $reviewsFound = false;
        if (!function_exists('agws_ts_cachecheck')) {
            function agws_ts_cachecheck($filename_cache, $timeout = 10800)
            {
                if (file_exists($filename_cache) && time() - filemtime($filename_cache) < $timeout)
                    return true;

                return false;
            }
        }
        // check if cached version exists
        if (!agws_ts_cachecheck($cacheFileName, $cacheTimeOut)) {
            // load fresh from API
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            $output = curl_exec($ch);
            curl_close($ch);
            // Write the contents back to the file
            // Make sure you can write to file's destination
            $x = file_put_contents($cacheFileName, $output);
        }
        if ($jsonObject = json_decode(file_get_contents($cacheFileName), true)) {
            $result = isset($jsonObject['response']['data']) ? $jsonObject['response']['data']['shop']['qualityIndicators']['reviewIndicator']['overallMark'] : 0;
            $count = isset($jsonObject['response']['data']) ? $jsonObject['response']['data']['shop']['qualityIndicators']['reviewIndicator']['activeReviewCount'] : 0;
            $shopName = isset($jsonObject['response']['data']) ? $jsonObject['response']['data']['shop']['name'] : "";
            $max = "5.00";
            if ($count > 0) {
                $reviewsFound = true;
            }
        }

        if ($reviewsFound) {
            $smarty->assign('ts_features_richsnippet_result', $result);
            $smarty->assign('ts_features_richsnippet_count', $count);
            $smarty->assign('ts_features_richsnippet_shopName', utf8_decode($shopName));
            $smarty->assign('ts_features_richsnippet_max', $max);
            $smarty->assign('ts_features_richsnippet_tsid', $queryResult->cTS_ID);
            $smarty->assign('bIstShop4', $helper->isShop4());

            $htmlRichSnippetsWidget = $smarty->fetch($oPlugin->cFrontendPfad . "tpl_inc/inc_ts_features_richsnippets.tpl");
            if (file_exists(PFAD_PLUGIN . $oPlugin->cVerzeichnis . "/" . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . "/" . PFAD_PLUGIN_FRONTEND . "tpl_inc/inc_ts_features_richsnippets_custom.tpl"))
                $htmlRichSnippetsWidget = $smarty->fetch($oPlugin->cFrontendPfad . "tpl_inc/inc_ts_features_richsnippets_custom.tpl");

            if ($helper->isShop4()) {
                $ts_richsnippet_pq_selector = TS_RICHSNIPPET_PQ_SELECTOR_V4;
                $ts_richsnippet_pq_method = TS_RICHSNIPPET_PQ_METHOD_V4;
            } else {
                $ts_richsnippet_pq_selector = TS_RICHSNIPPET_PQ_SELECTOR;
                $ts_richsnippet_pq_method = TS_RICHSNIPPET_PQ_METHOD;
            }

            pq($ts_richsnippet_pq_selector)->$ts_richsnippet_pq_method($htmlRichSnippetsWidget);
        }
    }
    /**/
} else {
    pq('#sidebox_ts_rating')->remove();
    pq('#sidebox_ts_review')->remove();
}