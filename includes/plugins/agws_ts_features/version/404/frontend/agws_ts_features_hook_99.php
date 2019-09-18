<?php
/**
 * Created by ag-websolutions.de
 *
 * File: agws_ts_features_hook_99.php
 * Project: agws_trustedshops
 */

include_once($oPlugin->cAdminmenuPfad . 'inc/agws_ts_features_predefine.php');

$smarty = Shop::Smarty();

$queryResult = Shop::DB()->selectSingleRow("xplugin_agws_ts_features_config", "ts_sprache", $_SESSION['kSprache']);

if (isset($queryResult) && $queryResult->ts_ReviewStickerAktiv == "1" && ($queryResult->ts_ReviewStickerPosition == "1" || $queryResult->ts_ReviewStickerPosition == "2")) {
    $ts_ReviewStickerCode = $queryResult->ts_ReviewStickerCode;
    $ts_ReviewStickerCode = str_replace("##ts_ReviewSticker_reviews##", $queryResult->ts_ReviewStickerBewertungsanzahl, $ts_ReviewStickerCode);
    $ts_ReviewStickerCode = str_replace("##ts_ReviewSticker_betterThan##", $queryResult->ts_ReviewStickerMindestnote, $ts_ReviewStickerCode);
    $ts_ReviewStickerCode = str_replace("##ts_ReviewSticker_fontFamily##", $queryResult->ts_ReviewStickerSchriftart, $ts_ReviewStickerCode);
    $ts_ReviewStickerCode = str_replace("##ts_ReviewSticker_backgroundColor##", $queryResult->ts_ReviewStickerHintergrundfarbe, $ts_ReviewStickerCode);

    if ($queryResult->ts_ReviewStickerModus == "kommentar") {
        $ts_ReviewStickerCode = str_replace("##ts_ReviewSticker_variant##", "testimonial", $ts_ReviewStickerCode);
    } else {
        $ts_ReviewStickerCode = str_replace("##ts_ReviewSticker_variant##", "skyscraper_vertical", $ts_ReviewStickerCode);
    }

    $smarty->assign('ts_features_review_boxtitel', $oPlugin->oPluginSprachvariableAssoc_arr['agws_ts_features_review_boxtitel']);
    $smarty->assign('ReviewStickerCode', $ts_ReviewStickerCode);
}

if (isset($queryResult) && isset($queryResult->ts_RatingWidgetAktiv) && $queryResult->ts_RatingWidgetAktiv==1) {
    $smarty->assign('ts_features_rating_boxtitel', $oPlugin->oPluginSprachvariableAssoc_arr['agws_ts_features_rating_boxtitel']);
    $smarty->assign('ts_ratingwidget_img', str_replace("TS_ID", $queryResult->ts_id, TS_RATING_LINK_IMG_URL));

    switch ($_SESSION['cISOSprache']) {
        case "ger":
            $smarty->assign('ts_ratingwidget_url', str_replace("TS_ID", $queryResult->ts_id, TS_RATING_LINK_URL_DE));
            $smarty->assign('ts_ratingwidget_alt_title', TS_RATING_LINK_TEXT_DE);
            break;
        case "eng":
            $smarty->assign('ts_ratingwidget_url', str_replace("TS_ID", $queryResult->ts_id, TS_RATING_LINK_URL_EN));
            $smarty->assign('ts_ratingwidget_alt_title', TS_RATING_LINK_TEXT_EN);
            break;
        case "spa":
            $smarty->assign('ts_ratingwidget_url', str_replace("TS_ID", $queryResult->ts_id, TS_RATING_LINK_URL_ES));
            $smarty->assign('ts_ratingwidget_alt_title', TS_RATING_LINK_TEXT_ES);
            break;
        case "fre":
            $smarty->assign('ts_ratingwidget_url', str_replace("TS_ID", $queryResult->ts_id, TS_RATING_LINK_URL_FR));
            $smarty->assign('ts_ratingwidget_alt_title', TS_RATING_LINK_TEXT_FR);
            break;
        case "pol":
            $smarty->assign('ts_ratingwidget_url', str_replace("TS_ID", $queryResult->ts_id, TS_RATING_LINK_URL_PL));
            $smarty->assign('ts_ratingwidget_alt_title', TS_RATING_LINK_TEXT_PL);
            break;
        case "ita":
            $smarty->assign('ts_ratingwidget_url', str_replace("TS_ID", $queryResult->ts_id, TS_RATING_LINK_URL_IT));
            $smarty->assign('ts_ratingwidget_alt_title', TS_RATING_LINK_TEXT_IT);
            break;
        case "dut":
            $smarty->assign('ts_ratingwidget_url', str_replace("TS_ID", $queryResult->ts_id, TS_RATING_LINK_URL_NL));
            $smarty->assign('ts_ratingwidget_alt_title', TS_RATING_LINK_TEXT_NL);
            break;
        default:
            $smarty->assign('ts_ratingwidget_url', str_replace("TS_ID", $queryResult->ts_id, TS_RATING_LINK_URL_EN));
            $smarty->assign('ts_ratingwidget_alt_title', TS_RATING_LINK_TEXT_EN);
            break;
    }
}