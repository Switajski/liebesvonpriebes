<?php

/**
 * Handles Plugin-Configuration misc settings.
 */
global $oPlugin;
require_once(PFAD_ROOT . PFAD_INCLUDES . "tools.Global.php");
require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.Jtllog.php");
require_once($oPlugin->cFrontendPfad . 'lib/lpa_defines.php');

$customConfigKeys = array(
    S360_LPA_CONFKEY_EXCLUDED_DELIVERY_METHODS
);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['update_lpa_misc_settings']) && $_POST['update_lpa_misc_settings'] === "1") {
    // handle submit, else ignore any changes here
    foreach ($customConfigKeys as $configKey) {
        if($configKey === S360_LPA_CONFKEY_EXCLUDED_DELIVERY_METHODS) {
            s360_insertOrUpdateMiscPluginEinstellung($configKey, implode(",", $_POST[$configKey]));
        } else {
            s360_insertOrUpdateMiscPluginEinstellung($configKey, $_POST[$configKey]);
        }
    }
}

/* Load current settings into smarty */
$s360_lpa_config_misc = array();
$s360_excluded_delivery_methods = array();
foreach ($customConfigKeys as $configKey) {
    $result = Shop::DB()->query('SELECT * FROM ' . S360_LPA_TABLE_CONFIG . ' WHERE cName LIKE "' . $configKey . '" LIMIT 1', 1);
    if (!empty($result)) {
        if ($configKey === S360_LPA_CONFKEY_EXCLUDED_DELIVERY_METHODS) {
            $s360_excluded_delivery_methods = explode(",", $result->cWert);
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

Shop::Smarty()->assign('pluginAdminUrl', 'plugin.php?kPlugin=' . $oPlugin->kPlugin . '&');
Shop::Smarty()->assign('s360_lpa_config_misc', $s360_lpa_config_misc);

print(Shop::Smarty()->fetch($oPlugin->cAdminmenuPfad . "template/config_misc.tpl"));

function s360_insertOrUpdateMiscPluginEinstellung($configName, $configWert) {
    $result = Shop::DB()->query('SELECT * FROM ' . S360_LPA_TABLE_CONFIG . ' WHERE cName LIKE "' . $configName . '"', 2);
    if (count($result) == 0) {
        Shop::DB()->query('INSERT INTO ' . S360_LPA_TABLE_CONFIG . ' (cName, cWert) VALUES ("' . $configName . '","' . $configWert . '")', 3);
    } else {
        Shop::DB()->query('UPDATE ' . S360_LPA_TABLE_CONFIG . ' SET cWert = "' . $configWert . '" WHERE cName LIKE "' . $configName . '"', 3);
    }
}
