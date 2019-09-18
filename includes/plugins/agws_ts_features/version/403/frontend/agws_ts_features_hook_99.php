<?php
/**
 * Created by ag-websolutions.de
 *
 * File: agws_ts_features_hook_99.php
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
    $queryResult = Shop::DB()->selectSingleRow("xplugin_agws_ts_features_config", "iTS_Sprache", $_SESSION['kSprache']) :
    $queryResult = $GLOBALS["DB"]->selectSingleRow("xplugin_agws_ts_features_config", "iTS_Sprache", $_SESSION['kSprache']);

if (isset($queryResult) && $queryResult->bTS_ReviewStickerShow == "1") {
    $smarty->assign('bIstShop4', $helper->isShop4());
    $smarty->assign('ts_features_review_boxtitel', $oPlugin->oPluginSprachvariableAssoc_arr['agws_ts_features_review_boxtitel']);
    $smarty->assign('ReviewStickerCode', $queryResult->cTS_ReviewStickerCode);
}

if (isset($queryResult) && $queryResult->bTS_RatingWidgetShow == "1") {
    $smarty->assign('bIstShop4', $helper->isShop4());
    $smarty->assign('ts_features_rating_boxtitel', $oPlugin->oPluginSprachvariableAssoc_arr['agws_ts_features_rating_boxtitel']);
    $smarty->assign('ts_ratingwidget_img', str_replace("TS_ID", $queryResult->cTS_ID, TS_RATING_LINK_IMG_URL));

    switch ($_SESSION['cISOSprache']) {
        case "ger":
            $smarty->assign('ts_ratingwidget_url', str_replace("TS_ID", $queryResult->cTS_ID, TS_RATING_LINK_URL_DE));
            $smarty->assign('ts_ratingwidget_alt_title', TS_RATING_LINK_TEXT_DE);
            break;
        case "eng":
            $smarty->assign('ts_ratingwidget_url', str_replace("TS_ID", $queryResult->cTS_ID, TS_RATING_LINK_URL_EN));
            $smarty->assign('ts_ratingwidget_alt_title', TS_RATING_LINK_TEXT_EN);
            break;
        case "spa":
            $smarty->assign('ts_ratingwidget_url', str_replace("TS_ID", $queryResult->cTS_ID, TS_RATING_LINK_URL_ES));
            $smarty->assign('ts_ratingwidget_alt_title', TS_RATING_LINK_TEXT_ES);
            break;
        case "fre":
            $smarty->assign('ts_ratingwidget_url', str_replace("TS_ID", $queryResult->cTS_ID, TS_RATING_LINK_URL_FR));
            $smarty->assign('ts_ratingwidget_alt_title', TS_RATING_LINK_TEXT_FR);
            break;
        case "pol":
            $smarty->assign('ts_ratingwidget_url', str_replace("TS_ID", $queryResult->cTS_ID, TS_RATING_LINK_URL_PL));
            $smarty->assign('ts_ratingwidget_alt_title', TS_RATING_LINK_TEXT_PL);
            break;
        case "ita":
            $smarty->assign('ts_ratingwidget_url', str_replace("TS_ID", $queryResult->cTS_ID, TS_RATING_LINK_URL_IT));
            $smarty->assign('ts_ratingwidget_alt_title', TS_RATING_LINK_TEXT_IT);
            break;
        case "dut":
            $smarty->assign('ts_ratingwidget_url', str_replace("TS_ID", $queryResult->cTS_ID, TS_RATING_LINK_URL_NL));
            $smarty->assign('ts_ratingwidget_alt_title', TS_RATING_LINK_TEXT_NL);
            break;
        default:
            $smarty->assign('ts_ratingwidget_url', str_replace("TS_ID", $queryResult->cTS_ID, TS_RATING_LINK_URL_EN));
            $smarty->assign('ts_ratingwidget_alt_title', TS_RATING_LINK_TEXT_EN);
            break;
    }
}