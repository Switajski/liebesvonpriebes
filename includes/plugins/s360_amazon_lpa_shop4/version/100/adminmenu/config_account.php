<?php

/**
 * Handles Plugin-Configuration settings tab.
 */
global $oPlugin;
require_once(PFAD_ROOT . PFAD_INCLUDES . "tools.Global.php");
require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.Jtllog.php");
require_once($oPlugin->cFrontendPfad . 'lib/lpa_defines.php');

$customConfigKeys = array(
    S360_LPA_CONFKEY_MERCHANT_ID,
    S360_LPA_CONFKEY_ACCESS_KEY,
    S360_LPA_CONFKEY_SECRET_KEY,
    S360_LPA_CONFKEY_ENVIRONMENT,
    S360_LPA_CONFKEY_REGION,
    S360_LPA_CONFKEY_CLIENT_ID,
    S360_LPA_CONFKEY_CLIENT_SECRET
);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['update_lpa_account_settings']) && $_POST['update_lpa_account_settings'] === "1") {
    // handle submit, else ignore any changes here
    foreach ($customConfigKeys as $configKey) {
        s360_insertOrUpdatePluginEinstellung($configKey, $_POST[$configKey]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['lpa_simplepath_return']) && $_POST['lpa_simplepath_return'] === "1") {
    // handle submit of simple path return data, which is a json string
    $simplepathReturnData = json_decode($_POST['lpa_simplepath_json'], true);
    if(!empty($simplepathReturnData)) {
        foreach ($simplepathReturnData as $key => $value) {
            switch($key) {
                case "merchant_id": 
                    s360_insertOrUpdatePluginEinstellung(S360_LPA_CONFKEY_MERCHANT_ID, $value);
                    break;
                case "access_key":
                    s360_insertOrUpdatePluginEinstellung(S360_LPA_CONFKEY_ACCESS_KEY, $value);
                    break;
                case "secret_key":
                    s360_insertOrUpdatePluginEinstellung(S360_LPA_CONFKEY_SECRET_KEY, $value);
                    break;
                case "client_Id":
                    s360_insertOrUpdatePluginEinstellung(S360_LPA_CONFKEY_CLIENT_ID, $value);
                    break;
                case "client_secret":
                    s360_insertOrUpdatePluginEinstellung(S360_LPA_CONFKEY_CLIENT_SECRET, $value);
                    break;
                case "uniqueId":  // ignored, we generated this ourselves
                case "marketplaceId": // ignored, not relevant for LPA
                default:
                    break;
            }
        }
        // also set mode to Sandbox initially
        s360_insertOrUpdatePluginEinstellung(S360_LPA_CONFKEY_ENVIRONMENT, 'sandbox');
    }
}

/* Load current settings into smarty */
$s360_lpa_config = array();
foreach ($customConfigKeys as $configKey) {
    $result = Shop::DB()->query('SELECT * FROM ' . S360_LPA_TABLE_CONFIG . ' WHERE cName LIKE "' . $configKey . '" LIMIT 1', 1);
    if (!empty($result)) {
        $s360_lpa_config[$configKey] = $result->cWert;
    }
}

$s360_lpa_config['lpa_ipn_url'] = str_replace('http:', 'https:', $oPlugin->cFrontendPfadURLSSL) . 'ipn.php';
$returnUrls = array();
$returnUrls[] = str_replace('http:', 'https:', Shop::getURL(true)) . '/';
$returnUrls[] = str_replace('http:', 'https:', gibShopURL(true)) . '/lpalogin';
$returnUrls[] = str_replace('http:', 'https:', gibShopURL(true)) . '/lpalogin-en';
$returnUrls[] = str_replace('http:', 'https:', gibShopURL(true)) . '/lpacheckout';
$returnUrls[] = str_replace('http:', 'https:', gibShopURL(true)) . '/lpacheckout-en';
$s360_lpa_config['lpa_allowed_js_origin'] = $returnUrls[0];
$s360_lpa_config['lpa_allowed_return_urls'] = $returnUrls;
Shop::Smarty()->assign('pluginAdminUrl', 'plugin.php?kPlugin=' . $oPlugin->kPlugin . '&');
Shop::Smarty()->assign('s360_lpa_config', $s360_lpa_config);

/*
 * Simple Path additional data
 * 
 * @TODO: Check if the Platform ID is correct here or if this should be a separate ID provided by AP
 */
Shop::Smarty()->assign('s360_sp_id', S360_LPA_PLATFORM_ID);

$spUniqueId = "LPA-SP-" . preg_replace("/[^A-Za-z0-9]/", "", Shop::getURL());
Shop::Smarty()->assign('s360_sp_unique_id', $spUniqueId);

$spLocale = "DE"; // can be US, UK or DE
Shop::Smarty()->assign('s360_sp_locale', $spLocale);

$spStoreDescription = '';
$oGlobaleMetaAngabenAssoc_arr = holeGlobaleMetaAngaben();
if (!empty($oGlobaleMetaAngabenAssoc_arr[Shop::$kSprache]->Title)) {
    $spStoreDescription = $oGlobaleMetaAngabenAssoc_arr[Shop::$kSprache]->Title;
}
Shop::Smarty()->assign('s360_sp_store_description', $spStoreDescription);

$spPrivacyNoticeUrl = "";
$privacyLinks = Shop::DB()->query("SELECT tls.cISOSprache as lang, tls.cSeo as seo FROM tlink tl, tlinksprache tls WHERE tls.kLink = tl.kLink AND tl.nLinkart = " . LINKTYP_DATENSCHUTZ, 2);
if(!empty($privacyLinks)) {
    foreach($privacyLinks as $privacyLink) {
        if($privacyLink->lang === "ger") {
            $spPrivacyNoticeUrl = Shop::getURL() . '/' . $privacyLink->seo;
            break;
        }
    }
    if(empty($spPrivacyNoticeUrl) && count($privacyLinks) > 0) {
        $spPrivacyNoticeUrl = Shop::getURL() . '/' . $privacyLinks[0]->seo;
    }
}
Shop::Smarty()->assign('s360_sp_privacy_notice_url', $spPrivacyNoticeUrl);

print(Shop::Smarty()->fetch($oPlugin->cAdminmenuPfad . "template/config_account.tpl"));

function s360_insertOrUpdatePluginEinstellung($configName, $configWert) {
    $result = Shop::DB()->query('SELECT * FROM ' . S360_LPA_TABLE_CONFIG . ' WHERE cName LIKE "' . $configName . '"', 2);
    if (count($result) == 0) {
        Shop::DB()->query('INSERT INTO ' . S360_LPA_TABLE_CONFIG . ' (cName, cWert) VALUES ("' . $configName . '","' . $configWert . '")', 3);
    } else {
        Shop::DB()->query('UPDATE ' . S360_LPA_TABLE_CONFIG . ' SET cWert = "' . $configWert . '" WHERE cName LIKE "' . $configName . '"', 3);
    }
}
