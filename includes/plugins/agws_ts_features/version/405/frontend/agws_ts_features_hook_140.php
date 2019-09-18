<?php
/**
 * Created by ag-websolutions.de
 *
 * File: agws_ts_features_hook_140.php
 * Project: agws_trustedshops
 */

include_once($oPlugin->cAdminmenuPfad . 'inc/agws_ts_features_predefine.php');

$smarty = Shop::Smarty();

$queryResult = Shop::DB()->selectSingleRow("xplugin_agws_ts_features_config", "ts_sprache", (int)$_SESSION['kSprache']);

if (isset($queryResult)) {
    //pQuery-insert "Trustbadge" auf allen Seiten
    if ($queryResult->ts_BadgeCode != "0") {
        $ts_BadgeCode = $queryResult->ts_BadgeCode;
        $ts_BadgeCode = str_replace("##ts_BadgeYOffset##", $queryResult->ts_BadgeYOffset, $ts_BadgeCode);
        $ts_BadgeCode = str_replace("##ts_BadgeVariante##", $queryResult->ts_BadgeVariante, $ts_BadgeCode);
        pq('body')->append($ts_BadgeCode);
    }
    /**/

    //pQuery-remove "trustedShopsCheckout (alt)" auf allen Seiten - nur Shop 3
    if (pq('#trustedShopsCheckout')->length() > 0)
        pq('#trustedShopsCheckout')->remove();
    /**/

    //pQuery-insert "ReviewSticker" auf allen Seiten im Footer wenn aktiviert
    if ($queryResult->ts_ReviewStickerAktiv == "1" && $queryResult->ts_ReviewStickerModus == "bewertung" && $queryResult->ts_ReviewStickerPosition == "3") {
        $htmlReviewStickerWrapper = $smarty->fetch($oPlugin->cFrontendPfad . "tpl_inc/inc_ts_features_review_footer.tpl");
        if (file_exists(PFAD_PLUGIN . $oPlugin->cVerzeichnis . "/" . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . "/" . PFAD_PLUGIN_FRONTEND . "tpl_inc/inc_ts_features_review_footer_custom.tpl"))
            $htmlReviewStickerWrapper = $smarty->fetch($oPlugin->cFrontendPfad . "tpl_inc/inc_ts_features_review_footer_custom.tpl");

        $ts_review_pq_selector = TS_REVIEW_PQ_SELECTOR_V4_FOOTER;
        $ts_review_pq_method = TS_REVIEW_PQ_METHOD_V4_FOOTER;

        $ts_ReviewStickerCode = $queryResult->ts_ReviewStickerCode;
        $ts_ReviewStickerCode = str_replace("##ts_ReviewSticker_variant##", "skyscraper_horizontal", $ts_ReviewStickerCode);
        $ts_ReviewStickerCode = str_replace("##ts_ReviewSticker_reviews##", $queryResult->ts_ReviewStickerBewertungsanzahl, $ts_ReviewStickerCode);
        $ts_ReviewStickerCode = str_replace("##ts_ReviewSticker_betterThan##", $queryResult->ts_ReviewStickerMindestnote, $ts_ReviewStickerCode);
        $ts_ReviewStickerCode = str_replace("##ts_ReviewSticker_backgroundColor##", $queryResult->ts_ReviewStickerHintergrundfarbe, $ts_ReviewStickerCode);
        $ts_ReviewStickerCode = str_replace("##ts_ReviewSticker_fontFamily##", $queryResult->ts_ReviewStickerSchriftart, $ts_ReviewStickerCode);


        pq($ts_review_pq_selector)->$ts_review_pq_method($htmlReviewStickerWrapper);
        pq('#tsReviewStickerWrapper')->append($ts_ReviewStickerCode);
    }
    /**/

    //pQuery-insert "RatingWidget" auf allen Seiten im Footer wenn aktiviert
    if ($queryResult->ts_RatingWidgetAktiv == "1" && $queryResult->ts_RatingWidgetPosition == "3") {
        $htmlRatingWidget = $smarty->fetch($oPlugin->cFrontendPfad . "tpl_inc/inc_ts_features_rating_footer.tpl");
        if (file_exists(PFAD_PLUGIN . $oPlugin->cVerzeichnis . "/" . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . "/" . PFAD_PLUGIN_FRONTEND . "tpl_inc/inc_ts_features_rating_footer_custom.tpl"))
            $htmlRatingWidget = $smarty->fetch($oPlugin->cFrontendPfad . "tpl_inc/inc_ts_features_rating_footer_custom.tpl");
        $ts_rating_pq_selector = TS_RATING_PQ_SELECTOR_FOOTER_V4;
        $ts_rating_pq_method = TS_RATING_PQ_METHOD_FOOTER_V4;
        pq($ts_rating_pq_selector)->$ts_rating_pq_method($htmlRatingWidget);
    }
    /**/

    //Bau des ArtNr-Arrays bei Standard- und Vater-/Kindartikel für Produktbewertung Sterne bzw. Register
    if (Shop::getPageType() == PAGE_ARTIKEL && ($queryResult->ts_ProduktBewertungSterneAktiv == 1 || $queryResult->ts_ProduktBewertungRegisterAktiv == 1) ) {
        $oArtikel_tmp = $smarty->get_template_vars('Artikel');
        
        //Standardartikel
        if ($oArtikel_tmp->nIstVater == 0 && $oArtikel_tmp->kVaterArtikel == 0) {
            $oArtikel_tmp->cArtNr_TS = $oArtikel_tmp->cArtNr;
        }

        //Vater-Artikel
        if ($oArtikel_tmp->nIstVater == 1 && $oArtikel_tmp->kVaterArtikel == 0) {
            $cArtNrVater = $oArtikel_tmp->cArtNr;
            $sql = "SELECT cArtNr FROM `tartikel` WHERE kVaterArtikel = " . (int)$oArtikel_tmp->kArtikel;
            $cArtNrKinder = Shop::DB()->query($sql, 2);
            $cArtNrVarKombi = "";
            if (count($cArtNrKinder) > 0) {
                for($i=0; $i < count($cArtNrKinder); $i++) {
                    $cArtNrVarKombi .= $cArtNrKinder[$i]->cArtNr . "','";
                }
            }
            $cArtNrVarKombi .= $cArtNrVater;
            $oArtikel_tmp->cArtNr_TS = $cArtNrVarKombi;
        }

        //Kind-Artikel
        if ($oArtikel_tmp->nIstVater == 1 && $oArtikel_tmp->kVaterArtikel > 0) {
            $sql = "SELECT cArtNr FROM `tartikel` WHERE kArtikel = " . (int)$oArtikel_tmp->kVaterArtikel;
            $cArtNrVater = Shop::DB()->query($sql, 1);
            $cArtNrVater = $cArtNrVater->cArtNr;
            $sql = "SELECT cArtNr FROM `tartikel` WHERE kVaterArtikel = " . (int)$oArtikel_tmp->kVaterArtikel;
            $cArtNrKinder = Shop::DB()->query($sql, 2);
            $cArtNrVarKombi = "";
            if (count($cArtNrKinder) > 0) {
                for($i=0; $i < count($cArtNrKinder); $i++) {
                       $cArtNrVarKombi .= $cArtNrKinder[$i]->cArtNr . "','";
                }
            }
            $cArtNrVarKombi .= $cArtNrVater;
            $oArtikel_tmp->cArtNr_TS = $cArtNrVarKombi;
        }
    }

    //pQuery-inserts "Produktbewertung - STERNE" auf Artikeldetailseite
    if (Shop::getPageType() == PAGE_ARTIKEL && $queryResult->ts_ProduktBewertungSterneAktiv == 1) {
        $oArtikel_tmp = $smarty->get_template_vars('Artikel');
        $ts_ProduktBewertungSterneCode = $queryResult->ts_ProduktBewertungSterneCode;
        $ts_ProduktBewertungSterneCode = str_replace("##ts_ProduktBewertungSterne_sku##", $oArtikel_tmp->cArtNr_TS, $ts_ProduktBewertungSterneCode);
        $ts_ProduktBewertungSterneCode = str_replace("##ts_ProduktBewertungSterne_starColor##", $queryResult->ts_ProduktBewertungSterneSternfarbe, $ts_ProduktBewertungSterneCode);
        $ts_ProduktBewertungSterneCode = str_replace("##ts_ProduktBewertungSterne_starSize##", $queryResult->ts_ProduktBewertungSterneSterngroesse."px", $ts_ProduktBewertungSterneCode);
        $ts_ProduktBewertungSterneCode = str_replace("##ts_ProduktBewertungSterne_fontSize##", $queryResult->ts_ProduktBewertungSterneSchriftgroesse."px", $ts_ProduktBewertungSterneCode);

        if ($queryResult->ts_ProduktBewertungSterneLeer == 1) {
            $ts_ProduktBewertungSterneCode = str_replace("##ts_ProduktBewertungSterne_enablePlaceholder##", 'false', $ts_ProduktBewertungSterneCode);
        } else {
            $ts_ProduktBewertungSterneCode = str_replace("##ts_ProduktBewertungSterne_enablePlaceholder##", 'true', $ts_ProduktBewertungSterneCode);
        }

        $ts_ProduktBewertungSterneCSS = '<link rel="stylesheet" type="text/css" href="'.$oPlugin->cFrontendPfadURL.'/css/agws_ts_features_frontend.css">';
        pq('head')->append($ts_ProduktBewertungSterneCSS);

        $ts_ProduktBewertungSterne_pq_selector = $queryResult->ts_ProduktBewertungSterneSelector;
        $ts_ProduktBewertungSterne_pq_method = $queryResult->ts_ProduktBewertungSterneMethode;
        pq($ts_ProduktBewertungSterne_pq_selector)->$ts_ProduktBewertungSterne_pq_method($ts_ProduktBewertungSterneCode);

    }
    /**/

    //pQuery-inserts "Produktbewertung - REGISTER" auf Artikeldetailseite
    if (Shop::getPageType() == PAGE_ARTIKEL && $queryResult->ts_ProduktBewertungRegisterAktiv == 1) {
        $oArtikel_tmp = $smarty->get_template_vars('Artikel');
        $smarty->assign("agws_ts_features_tabtitel", $queryResult->ts_ProduktBewertungRegisterNameTab);
        $newTab = $smarty->fetch($oPlugin->cFrontendPfad . "tpl_inc/inc_ts_features_review_article_tab.tpl");
        if (file_exists(PFAD_PLUGIN . $oPlugin->cVerzeichnis . "/" . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . "/" . PFAD_PLUGIN_FRONTEND . "tpl_inc/inc_ts_features_review_article_tab_custom.tpl"))
            $newTab = $smarty->fetch($oPlugin->cFrontendPfad . "tpl_inc/inc_ts_features_review_article_tab_custom.tpl");

        $ts_ProduktBewertungRegister_pq_selector = $queryResult->ts_ProduktBewertungRegisterSelector;
        $ts_ProduktBewertungRegister_pq_method = $queryResult->ts_ProduktBewertungRegisterMethode;
        pq($ts_ProduktBewertungRegister_pq_selector)->$ts_ProduktBewertungRegister_pq_method($newTab);

        $ts_ProduktBewertungRegisterCode = $queryResult->ts_ProduktBewertungRegisterCode;
        $ts_ProduktBewertungRegisterCode = str_replace("##ts_ProduktBewertungRegister_sku##", $oArtikel_tmp->cArtNr_TS, $ts_ProduktBewertungRegisterCode);
        $ts_ProduktBewertungRegisterCode = str_replace("##ts_ProduktBewertungRegister_starColor##", $queryResult->ts_ProduktBewertungRegisterSternfarbe, $ts_ProduktBewertungRegisterCode);
        $ts_ProduktBewertungRegisterCode = str_replace("##ts_ProduktBewertungRegister_starSize##", $queryResult->ts_ProduktBewertungRegisterSterngroesse."px", $ts_ProduktBewertungRegisterCode);
        $ts_ProduktBewertungRegisterCode = str_replace("##ts_ProduktBewertungRegister_borderColor##", $queryResult->ts_ProduktBewertungRegisterRahmenfarbe, $ts_ProduktBewertungRegisterCode);
        $ts_ProduktBewertungRegisterCode = str_replace("##ts_ProduktBewertungRegister_introtext##", '', $ts_ProduktBewertungRegisterCode);

        if ($queryResult->ts_ProduktBewertungRegisterLeer == 1) {
            $ts_ProduktBewertungRegisterCode = str_replace("##ts_ProduktBewertungRegister_hideEmptySticker##", 'true', $ts_ProduktBewertungRegisterCode);
        } else {
            $ts_ProduktBewertungRegisterCode = str_replace("##ts_ProduktBewertungRegister_hideEmptySticker##", 'false', $ts_ProduktBewertungRegisterCode);
        }

        pq('#ts_article_reviews_wrapper')->append($ts_ProduktBewertungRegisterCode);

    }

    //pQuery-inserts "trustedShopsCheckout" auf Bestellabschlussseite bzw. Statusseite
    if ((Shop::getPageType() == PAGE_BESTELLABSCHLUSS || Shop::getPageType() == PAGE_BESTELLSTATUS) && isset($_SESSION['agws_kWarenkorb_TS']) && (int)$_SESSION['agws_kWarenkorb_TS'] > 0) {
        $ts_checkout_pq_selector = TS_CHECKOUT_PQ_SELECTOR;
        $ts_checkout_pq_method = TS_CHECKOUT_PQ_METHOD;
        $agws_kWarenkorb_TS = Shop::DB()->selectSingleRow('tbestellung', 'kWarenkorb', (int)$_SESSION['agws_kWarenkorb_TS']);
        $agws_kKunde_TS = Shop::DB()->selectSingleRow('tkunde', 'kKunde', (int)$_SESSION['agws_kKunde_TS']);
        $agws_bestellung_TS = new Bestellung($agws_kWarenkorb_TS->kBestellung);
        $agws_bestellung_TS->fuelleBestellung(0);
        $agws_oWarenkorb_Positionen_TS = $agws_bestellung_TS->Positionen;

        for($i=0; $i < count($agws_oWarenkorb_Positionen_TS); $i++) {
            if($agws_oWarenkorb_Positionen_TS[$i]->nPosTyp==1){
                if ($agws_oWarenkorb_Positionen_TS[$i]->Artikel->kVaterArtikel > 0){
                    $cArtNrVater = Shop::DB()->selectSingleRow("tartikel", "kArtikel", (int)$agws_oWarenkorb_Positionen_TS[$i]->Artikel->kVaterArtikel);
                    $agws_oWarenkorb_Positionen_TS[$i]->Artikel->cArtNrVater = (int)$cArtNrVater->cArtNr;
                } else {
                    $agws_oWarenkorb_Positionen_TS[$i]->Artikel->cArtNrVater = 0;
                }
            }
        }

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
    if ($queryResult->ts_RichSnippetsAktiv && (Shop::getPageType() == PAGE_ARTIKEL && $queryResult->ts_RichSnippetsArtikelseite == '1' || Shop::getPageType() == PAGE_ARTIKELLISTE && $queryResult->ts_RichSnippetsKategorieseite == '1' || Shop::getPageType() == PAGE_STARTSEITE && $queryResult->ts_RichSnippetsStartseite == '1')) {

        $tsId = $queryResult->ts_id;
        $cacheFileName = PFAD_LOGFILES . $tsId . '.json';
        $cacheTimeOut = 43200; // half a day
        $apiUrl = str_replace("TS_ID", $queryResult->ts_id, TS_RICHSNIPPET_API_URL);
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
            $ts_RichSnippetsCode = $queryResult->ts_RichSnippetsCode;
            $ts_RichSnippetsCode = str_replace("##ts_features_richsnippet_shopName##", utf8_decode($shopName), $ts_RichSnippetsCode);
            $ts_RichSnippetsCode = str_replace("##ts_features_richsnippet_result##", $result, $ts_RichSnippetsCode);
            $ts_RichSnippetsCode = str_replace("##ts_features_richsnippet_max##", $max, $ts_RichSnippetsCode);
            $ts_RichSnippetsCode = str_replace("##ts_features_richsnippet_count##", $count, $ts_RichSnippetsCode);

            $ts_richsnippet_pq_selector = TS_RICHSNIPPET_PQ_SELECTOR_V4;
            $ts_richsnippet_pq_method = TS_RICHSNIPPET_PQ_METHOD_V4;
            pq($ts_richsnippet_pq_selector)->$ts_richsnippet_pq_method($ts_RichSnippetsCode);
        }
    }
    /**/
} else {
    pq('#sidebox_ts_rating')->remove();
    pq('#sidebox_ts_review')->remove();
}