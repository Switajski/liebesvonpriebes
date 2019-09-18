<?php

/**
 * Handles Plugin-Configuration misc settings.
 */

global $oPlugin;
require_once(PFAD_ROOT . PFAD_INCLUDES . "tools.Global.php");
require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.Jtllog.php");
require_once($oPlugin->cFrontendPfad . 'lib/lpa_defines.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPACurrencyHelper.php');
$customConfigKeys = array(
    S360_LPA_CONFKEY_EXCLUDED_DELIVERY_METHODS,
    S360_LPA_CONFKEY_EXCLUDED_CURRENCIES
);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['update_lpa_misc_settings']) && (int)$_POST['update_lpa_misc_settings'] === 1) {
    if (validateToken()) {
        // handle submit, else ignore any changes here
        foreach ($customConfigKeys as $configKey) {
            if ($configKey === S360_LPA_CONFKEY_EXCLUDED_DELIVERY_METHODS) {
                $configKeyContent = (isset($_POST[$configKey])) ? implode(",", $_POST[$configKey]) : "";
                s360_insertOrUpdateMiscPluginEinstellung($configKey, $configKeyContent);
            } elseif($configKey === S360_LPA_CONFKEY_EXCLUDED_CURRENCIES) {
                $configKeyContent = (isset($_POST[$configKey])) ? implode(",", $_POST[$configKey]) : "";
                s360_insertOrUpdateMiscPluginEinstellung($configKey, $configKeyContent);
            } else {
                s360_insertOrUpdateMiscPluginEinstellung($configKey, $_POST[$configKey]);
            }
        }
    }
}

/* Load current settings into smarty */
$s360_lpa_config_misc = array();
$s360_excluded_delivery_methods = array();
$s360_excluded_currencies = array();
foreach ($customConfigKeys as $configKey) {
    $result = Shop::DB()->select(S360_LPA_TABLE_CONFIG, 'cName', $configKey);
    if (!empty($result)) {
        if ($configKey === S360_LPA_CONFKEY_EXCLUDED_DELIVERY_METHODS) {
            $s360_excluded_delivery_methods = explode(",", $result->cWert);
        } elseif($configKey === S360_LPA_CONFKEY_EXCLUDED_CURRENCIES) {
            $s360_excluded_currencies = explode(",", $result->cWert);
        } else {
            $s360_lpa_config_misc[$configKey] = $result->cWert;
        }
    }
}

/*
 * Load all available delivery methods.
 */
$s360_available_delivery_methods = array();
$result = Shop::DB()->query('SELECT * FROM tversandart', 2);
if (!empty($result) && is_array($result)) {
    foreach ($result as $res) {
        $method = array();
        $method['key'] = $res->kVersandart;
        $method['name'] = $res->cName;
        if (!empty($s360_excluded_delivery_methods) && in_array($method['key'], $s360_excluded_delivery_methods)) {
            $method['isExcluded'] = true;
        } else {
            $method['isExcluded'] = false;
        }
        $s360_available_delivery_methods[] = $method;
    }
}
$s360_lpa_config_misc['lpa_available_delivery_methods'] = $s360_available_delivery_methods;

/**
 * Load all available currencies that might be possible for amazon
 */
$s360_available_currencies = array();
$result = LPACurrencyHelper::getAvailableSupportedCurrencies(true); // load currencies for config, i.e. ignore currently disabled currencies
if(!empty($result) && is_array($result)) {
    foreach ($result as $res) {
        $currency = array();
        $currency['key'] = $res->cISO;
        $currency['name'] = $res->cName;
        if (!empty($s360_excluded_currencies) && in_array($currency['key'], $s360_excluded_currencies)) {
            $currency['isExcluded'] = true;
        } else {
            $currency['isExcluded'] = false;
        }
        $s360_available_currencies[] = $currency;
    }
}
$s360_lpa_config_misc['lpa_available_currencies'] = $s360_available_currencies;

Shop::Smarty()->assign('pluginAdminUrl', 'plugin.php?kPlugin=' . $oPlugin->kPlugin . '&')
              ->assign('s360_lpa_config_misc', $s360_lpa_config_misc);

// Workaround for failed save attempts on first opening
Shop::Smarty()->assign('s360_jtl_token', getTokenInput());

Shop::Smarty()->display($oPlugin->cAdminmenuPfad . "template/config_misc.tpl");

function s360_insertOrUpdateMiscPluginEinstellung($configName, $configWert) {
    $query = 'SELECT * FROM ' . S360_LPA_TABLE_CONFIG . ' WHERE cName LIKE :configName';
    $result = Shop::DB()->executeQueryPrepared($query, ['configName' => $configName], 2);
    if (count($result) == 0) {
        $insertQuery = 'INSERT INTO ' . S360_LPA_TABLE_CONFIG . ' (cName, cWert) VALUES (:configName,:configWert)';
        Shop::DB()->executeQueryPrepared($insertQuery, ['configName' => $configName, 'configWert' => $configWert], 3);
    } else {
        $updateQuery = 'UPDATE ' . S360_LPA_TABLE_CONFIG . ' SET cWert = :configWert WHERE cName LIKE :configName';
        Shop::DB()->executeQueryPrepared($updateQuery, ['configWert' => $configWert, 'configName' => $configName], 3);
    }
}
