<?php
/**
 * Created by ag-websolutions.de
 *
 * File: agws_ts_features_config2.php
 * Project: agws_ts_features
 */

global $oPlugin;

require_once $oPlugin->cAdminmenuPfad . 'inc/class.agws_plugin_ts_feature.helper.php';

$helper = agwsPluginHelperTS::getInstance($oPlugin);

if ($helper->isShop4()) {
    $smarty = Shop::Smarty();
} else {
    global $oPlugin, $smarty;
}

if($_SESSION['ts_features_error_add']!="1")
{
    $sql="SELECT tsprache.* FROM tsprache";
    ($helper->isShop4()) ?
        $ts_id_sprache_arr = Shop::DB()->executeQuery($sql,2):
        $ts_id_sprache_arr = $GLOBALS["DB"]->executeQuery($sql,2);

    $sql="SELECT tsprache.cNameDeutsch,xplugin_agws_ts_features_config.*
                                                    FROM xplugin_agws_ts_features_config
                                                    LEFT JOIN tsprache ON tsprache.kSprache = xplugin_agws_ts_features_config.iTS_Sprache
                                                    WHERE xplugin_agws_ts_features_config.cTS_ID='".$helper->filter__XSS($_POST['ts_id'])."'";
    ($helper->isShop4()) ?
        $ts_id_all_arr = Shop::DB()->executeQuery($sql,2):
        $ts_id_all_arr = $GLOBALS["DB"]->executeQuery($sql,2);
}

$smarty->assign('ts_id_all_arr',$ts_id_all_arr);

$smarty->assign('ts_id_shopsprachen',$ts_id_sprache_arr);
$smarty->assign('ts_id',$helper->filter__XSS($_POST['ts_id']));
$smarty->assign("URL_ADMINMENU", URL_SHOP . "/" . PFAD_PLUGIN . $oPlugin->cVerzeichnis . "/" . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . "/" . PFAD_PLUGIN_ADMINMENU);

print($smarty->fetch($oPlugin->cAdminmenuPfad . "template/agws_ts_features_config2.tpl"));