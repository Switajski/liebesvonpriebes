<?php

/*
 * This script handles account merging on login with amazon.
 */
$oPlugin = Plugin::getPluginById('s360_amazon_lpa_shop4');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPAController.php');
require_once($oPlugin->cFrontendPfad . 'lib/lpa_defines.php');
require_once($oPlugin->cFrontendPfad . 'lib/lpa_utils.php');
require_once(PFAD_ROOT . PFAD_INCLUDES . "globalinclude.php");
require_once(PFAD_ROOT . PFAD_INCLUDES . "bestellvorgang_inc.php");
require_once(PFAD_ROOT . PFAD_INCLUDES . "jtl_inc.php");
require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.Bestellung.php");
require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.Kampagne.php");
require_once(PFAD_ROOT . PFAD_INCLUDES . "mailTools.php");
require_once(PFAD_ROOT . PFAD_INCLUDES . "wunschliste_inc.php");
require_once(PFAD_ROOT . PFAD_INCLUDES . "kundenwerbenkeunden_inc.php");


Shop::Smarty()->assign('lpa_merge_form_target', $oPlugin->cFrontendPfadURLSSL . 'lpa_merge.php');

if (file_exists($oPlugin->cFrontendPfad . 'template/lpa_create_account_form_custom.tpl')) {
    Shop::Smarty()->assign('lpa_template_path_create_form', $oPlugin->cFrontendPfad . 'template/lpa_create_account_form_custom.tpl');
} else {
    Shop::Smarty()->assign('lpa_template_path_create_form', $oPlugin->cFrontendPfad . 'template/lpa_create_account_form.tpl');
}
if (file_exists($oPlugin->cFrontendPfad . 'template/lpa_merge_account_form_custom.tpl')) {
    Shop::Smarty()->assign('lpa_template_path_merge_form', $oPlugin->cFrontendPfad . 'template/lpa_merge_account_form_custom.tpl');
} else {
    Shop::Smarty()->assign('lpa_template_path_merge_form', $oPlugin->cFrontendPfad . 'template/lpa_merge_account_form.tpl');
}

Shop::Smarty()->assign('laender', gibBelieferbareLaender());

if (!isset($_SESSION)) {
    session_start();
}

pruefeHttps();
/*
 * Before we start merging accounts, we verify that the sent verification code is valid
 */
$amazonId = StringHandler::filterXSS($_REQUEST['amazon_id']);
$verificationCode = StringHandler::filterXSS($_REQUEST['verification_code']);
$sql = 'SELECT * FROM ' . S360_LPA_TABLE_ACCOUNTMAPPING . ' WHERE cAmazonId LIKE "' . $amazonId . '" AND cVerifizierungsCode LIKE "' . $verificationCode . '" LIMIT 1';
$result = Shop::DB()->query($sql, 1);
if (empty($result)) {
    JTLLog::writeLog("LPA: LPA-Login-Fehler: Falsches AmazonID/VerifizierungsCode-Kombination empfangen: {$amazonID}, {$verificationCode}", JTLLOG_LEVEL_NOTICE);
    header('HTTP/1.1 404 Not Found');
    echo 'Page not found';
    exit;
}

// Use custom template if it exists.
if (file_exists($oPlugin->cFrontendPfad . 'template/lpa_merge_custom.tpl')) {
    Shop::Smarty()->assign('cPluginTemplate', $oPlugin->cFrontendPfad . 'template/lpa_merge_custom.tpl');
}

$mergeUrl = str_replace("http://", "https://", URL_SHOP) . '/lpamerge';
if (Shop::getLanguage(true) === "eng") {
    $mergeUrl .= '-en';
}
Shop::Smarty()->assign('lpa_merge_url_localized', $mergeUrl);

Shop::Smarty()->assign('lpa_login_verification_code', $verificationCode);
Shop::Smarty()->assign('lpa_login_amazon_id', $amazonId);

Shop::Smarty()->assign('lpa_shop3_compatibility', $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_SHOP3_COMPATIBILITY]);

/*
 * For the account merge to be successful, the given password must be correct.
 */
$success = false;
$userId = 0;
$knd = null;
$password = $_REQUEST['password'];
if (!empty($password)) {
    // load the user key against which we need to check
    $userId = intval($result->kKunde);
    if ($userId > 0) {
        $Kunde = new Kunde($userId);
        $nReturnValue = $Kunde->holLoginKunde($Kunde->cMail, $password);
        if ($nReturnValue === 1) {
            $knd = $Kunde;
            $success = true;
        } else {
            $success = false;
        }
    }
}


/*
 * Perform a last security check that the User we checked is really the user we expect.
 */
if ($success && $userId !== intval($Kunde->kKunde)) {
    $success = false;
}

if ($success) {
    // verify account mapping in strict mode, i.e. if the mapping is not known or does not match, nothing is set to verified. 
    // the mapping MUST exist because we loaded it right before.
    $success = verifyLPAAccountMapping($amazonId, $userId, true);
}

Shop::Smarty()->assign('lpa_merge_result', $success);

if ($success) {
    // login the user and redirect if necessary
    $hinweis = loginUserForKunde($Kunde);
    if (empty($hinweis)) {
        redirectToLPACookieLocation();
    } else {
        Shop::Smarty()->assign('lpa_merge_hinweis', $hinweis);
    }
} 