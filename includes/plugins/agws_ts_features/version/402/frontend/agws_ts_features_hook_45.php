<?php
/**
 * Created by ag-websolutions.de
 *
 * File: agws_ts_features_hook_45.php
 * Project: agws_trustedshops
 */

include_once($oPlugin->cAdminmenuPfad . 'inc/agws_ts_features_predefine.php');
require_once $oPlugin->cAdminmenuPfad . 'inc/class.agws_plugin_ts_feature.helper.php';

$helper = agwsPluginHelperTS::getInstance($oPlugin);

if ($helper->isShop4() === false) {
    global $smarty;
    $queryResult = $GLOBALS["DB"]->selectSingleRow("xplugin_agws_ts_features_config", "iTS_Sprache", $_SESSION['kSprache']);

    if (count($queryResult) == 1 && $queryResult->cTS_ID != "") {
        $agws_ts_features_ArtNr = utf8_encode($args_arr['oArtikel']->cArtNr);
        $args_arr['objResponse']->script('ts_article_review_init("' . $agws_ts_features_ArtNr . '");');
    }
}