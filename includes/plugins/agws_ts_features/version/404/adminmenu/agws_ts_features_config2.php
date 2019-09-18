<?php
/**
 * Created by ag-websolutions.de
 *
 * File: agws_ts_features_config2.php
 * Project: agws_ts_features
 */

global $oPlugin;
$smarty = Shop::Smarty();

$ts_id_sprache_arr = "";
$ts_id = isset($_POST['ts_id']) ? Shop::DB()->realEscape($_POST['ts_id']) : "";
$ts_features_error_add = isset($_SESSION['ts_features_error_add']) ? (int)$_SESSION['ts_features_error_add'] : 0;

if ($ts_features_error_add != 1) {
    $sql = "SELECT tsprache.* FROM tsprache";
    $ts_id_sprache_arr = Shop::DB()->executeQuery($sql, 2);

    $sql = "SELECT tsprache.cNameDeutsch,xplugin_agws_ts_features_config.*
                                                    FROM xplugin_agws_ts_features_config
                                                    LEFT JOIN tsprache ON tsprache.kSprache = xplugin_agws_ts_features_config.ts_sprache
                                                    WHERE xplugin_agws_ts_features_config.ts_id='" . $ts_id . "'";
    $ts_id_all_arr = Shop::DB()->executeQuery($sql, 2);
}

$smarty->assign('ts_id_all_arr', $ts_id_all_arr);

$smarty->assign('ts_id_shopsprachen', $ts_id_sprache_arr);
$smarty->assign('ts_id', $ts_id);
$smarty->assign("URL_ADMINMENU", Shop::getURL() . "/" . PFAD_PLUGIN . $oPlugin->cVerzeichnis . "/" . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . "/" . PFAD_PLUGIN_ADMINMENU);

$smarty->display($oPlugin->cAdminmenuPfad . "template/agws_ts_features_config2.tpl");