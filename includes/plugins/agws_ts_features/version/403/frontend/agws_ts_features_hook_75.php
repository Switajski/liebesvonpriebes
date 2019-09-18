<?php
/**
 * Created by ag-websolutions.de
 *
 * File: agws_ts_features_hook_75.php
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

$_SESSION['agws_kWarenkorb_TS'] = $args_arr['oBestellung']->kWarenkorb;
$_SESSION['agws_kKunde_TS'] = $args_arr['oBestellung']->kKunde;