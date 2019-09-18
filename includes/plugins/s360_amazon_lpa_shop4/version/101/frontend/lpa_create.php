<?php

/*
 * This script handles account creation on login with amazon.
 * It basically replicates the functionality of registrieren.php, but adds amazon related functionality.
 */
require_once(dirname(__FILE__) . '/lib/lpa_includes.php');
$oPlugin = Plugin::getPluginById('s360_amazon_lpa_shop4');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPAController.php');
require_once($oPlugin->cFrontendPfad . 'lib/lpa_defines.php');
require_once($oPlugin->cFrontendPfad . 'lib/lpa_utils.php');
require_once(PFAD_ROOT . PFAD_INCLUDES . "bestellvorgang_inc.php");
require_once(PFAD_ROOT . PFAD_INCLUDES . "mailTools.php");
require_once(PFAD_ROOT . PFAD_INCLUDES . "newsletter_inc.php");
require_once(PFAD_ROOT . PFAD_INCLUDES . "registrieren_inc.php");

pruefeHttps();

// Determine if this is a mobile template
$isMobileTemplate = false;
$template = new Template();
if ($template->mobilTemplateAktiv()) {
    $isMobileTemplate = true;
}
Shop::Smarty()->assign('lpa_template_mobile', $isMobileTemplate);


Shop::Smarty()->assign('lpa_create_form_target', $oPlugin->cFrontendPfadURLSSL . 'lpa_create.php');
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

Shop::Smarty()->assign('lpa_shop3_compatibility', $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_SHOP3_COMPATIBILITY]);

if (!isset($_SESSION)) {
    session_start();
}

$controller = new LPAController();
$config = $controller->getConfig();
/*
 * Before we start creating the account, we verify that the sent verification code is valid
 */
$amazonId = StringHandler::filterXSS($_REQUEST['amazon_id']);
$verificationCode = StringHandler::filterXSS($_REQUEST['verification_code']);
$sql = 'SELECT * FROM ' . S360_LPA_TABLE_ACCOUNTMAPPING . ' WHERE cAmazonId LIKE "' . $amazonId . '" AND cVerifizierungsCode LIKE "' . $verificationCode . '" LIMIT 1';
$result = Shop::DB()->query($sql, 1);
if (empty($result)) {
    JTLLog::writeLog("LPA: LPA-Login-Fehler: Falsche AmazonID/VerifizierungsCode-Kombination empfangen: $amazonID, $verificationCode", JTLLOG_LEVEL_NOTICE);
    header('HTTP/1.1 404 Not Found');
    echo 'Page not found';
    exit;
}

// Use custom template if it exists.
if (file_exists($oPlugin->cFrontendPfad . 'template/lpa_create_custom.tpl')) {
    Shop::Smarty()->assign('cPluginTemplate', $oPlugin->cFrontendPfad . 'template/lpa_create_custom.tpl');
}

$cPost_arr = $_POST;

$createUrl = str_replace("http://", "https://", URL_SHOP) . '/lpacreate';
if (Shop::getLanguage(true) === "eng") {
    $createUrl .= '-en';
}
Shop::Smarty()->assign('lpa_create_url_localized', $createUrl);

Shop::Smarty()->assign('lpa_login_verification_code', $verificationCode);
Shop::Smarty()->assign('lpa_login_amazon_id', $amazonId);
Shop::Smarty()->assign('lpa_seller_id', $config['merchant_id']);
Shop::Smarty()->assign('nAnzeigeOrt', CHECKBOX_ORT_REGISTRIERUNG);

$success = false;
$kontoAktiv = false;

$hinweis = "";
$knd;
$Kunde;
$cKundenattribut_arr;

/*
 * This code is from registrieren_inc, unfortunately we have to modify it subtly for this use case.
 */
global $GlobaleEinstellungen, $Einstellungen;

Shop::Smarty()->assign('cPost_arr', $cPost_arr);

/*
 * Check input
 */
$fehlendeAngaben = checkKundenFormular(1, 0);

/*
 * load customer data from input
 */
$knd = getKundendaten($cPost_arr, 1, 0);
$cKundenattribut_arr = getKundenattribute($cPost_arr);

require_once(PFAD_ROOT . PFAD_INCLUDES . "tools.Global.php");

$kKundengruppe = gibAktuelleKundengruppe();

// CheckBox Plausi
require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.CheckBox.php");
$oCheckBox = new CheckBox();
$fehlendeAngaben = array_merge($fehlendeAngaben, $oCheckBox->validateCheckBox(CHECKBOX_ORT_REGISTRIERUNG, $kKundengruppe, $cPost_arr, true));

if(isset($fehlendeAngaben['captcha'])) {
    unset($fehlendeAngaben['captcha']);
}

$nReturnValue = angabenKorrekt($fehlendeAngaben);

if ($nReturnValue) {
    // CheckBox Spezialfunktion ausführen
    $oCheckBox->triggerSpecialFunction(CHECKBOX_ORT_REGISTRIERUNG, $kKundengruppe, true, $cPost_arr, array("oKunde" => $knd));
    $oCheckBox->checkLogging(CHECKBOX_ORT_REGISTRIERUNG, $kKundengruppe, $cPost_arr, true);


    // Guthaben des Neukunden aufstocken insofern er geworben wurde
    $oNeukunde = Shop::DB()->query("SELECT kKundenWerbenKunden FROM tkundenwerbenkunden WHERE cEmail='" . $knd->cMail . "' AND nRegistriert = 0", 1);

    $kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;

    if (isset($oNeukunde) && !empty($oNeukunde) && $oNeukunde->kKundenWerbenKunden > 0 && isset($Einstellungen['kundenwerbenkunden']['kwk_kundengruppen']) && intval($Einstellungen['kundenwerbenkunden']['kwk_kundengruppen']) > 0) {
        $kKundengruppe = (int) $Einstellungen['kundenwerbenkunden']['kwk_kundengruppen'];
    }

    $knd->kKundengruppe = $kKundengruppe;
    $knd->kSprache = $_SESSION['kSprache'];
    $knd->cAbgeholt = "N";
    $knd->cAktiv = "Y";
    $knd->cSperre = "N";
    //konto sofort aktiv?
    if ($GlobaleEinstellungen['global']['global_kundenkonto_aktiv'] === "A") {
        $knd->cAktiv = "N";
        $kontoAktiv = false;
    } else {
        $kontoAktiv = true;
    }
    // we generate a random password of length 16
    $cPasswortKlartext = substr(str_shuffle(md5(time())), 0, 16);
    $customer = new Kunde();
    $knd->cPasswort = $customer->generatePasswordHash($cPasswortKlartext);

    $knd->dErstellt = "now()";
    $knd->nRegistriert = 1;
    $knd->angezeigtesLand = ISO2land($knd->cLand);

    // Work Around Mail zerhaut cLand
    $cLand = $knd->cLand;

    //mail
    $knd->cPasswortKlartext = $cPasswortKlartext;
    $obj = new stdClass();
    $obj->tkunde = $knd;
    sendeMail(MAILTEMPLATE_NEUKUNDENREGISTRIERUNG, $obj);

    $knd->cLand = $cLand;
    unset($knd->cPasswortKlartext);
    unset($knd->Anrede);

    $knd->kKunde = $knd->insertInDB();

    // Kampagne
    if (isset($_SESSION['Kampagnenbesucher'])) {
        setzeKampagnenVorgang(KAMPAGNE_DEF_ANMELDUNG, $knd->kKunde, 1.0); // Anmeldung
    }

    // Insert Kundenattribute
    if (is_array($cKundenattribut_arr) && count($cKundenattribut_arr) > 0) {
        $nKundenattributKey_arr = array_keys($cKundenattribut_arr);

        foreach ($nKundenattributKey_arr as $kKundenfeld) {
            unset($oKundenattribut);

            $oKundenattribut->kKunde = $knd->kKunde;
            $oKundenattribut->kKundenfeld = $cKundenattribut_arr[$kKundenfeld]->kKundenfeld;
            $oKundenattribut->cName = $cKundenattribut_arr[$kKundenfeld]->cWawi;
            $oKundenattribut->cWert = $cKundenattribut_arr[$kKundenfeld]->cWert;

            Shop::DB()->insert("tkundenattribut", $oKundenattribut);
        }
    }

    if ($Einstellungen['global']['global_kundenkonto_aktiv'] != "A") {
        $_SESSION['Kunde'] = new Kunde($knd->kKunde);
        $_SESSION['Kunde']->cKundenattribut_arr = $cKundenattribut_arr;
    }

    // Guthaben des Neukunden aufstocken insofern er geworben wurde
    if (isset($oNeukunde) && !empty($oNeukunde) && $oNeukunde->kKundenWerbenKunden > 0) {
        Shop::DB()->query("UPDATE tkunde SET fGuthaben=fGuthaben+" . doubleval($Einstellungen['kundenwerbenkunden']['kwk_neukundenguthaben']) . " WHERE kKunde=" . $knd->kKunde, 3);
        Shop::DB()->query("UPDATE tkundenwerbenkunden SET nRegistriert=1 WHERE cEmail='" . $knd->cMail . "'", 3);
    }

    $success = true;
    verifyLPAAccountMapping($amazonId, $knd->kKunde);
} else {
    Shop::Smarty()->assign('fehlendeAngaben', $fehlendeAngaben);
    $Kunde = $knd;
    Shop::Smarty()->assign('Kunde', $Kunde);
    $success = false;
}
$lpaCreateEinstellungen = Shop::getSettings(array(
                CONF_GLOBAL,
                CONF_RSS,
                CONF_KUNDEN,
                CONF_KUNDENFELD,
                CONF_SONSTIGES,
                CONF_NEWS,
                CONF_SITEMAP,
                CONF_ARTIKELUEBERSICHT,
                CONF_AUSWAHLASSISTENT,
                CONF_CACHING
    ));
Shop::Smarty()->assign('oKundenfeld_arr', gibSelbstdefKundenfelder());
Shop::Smarty()->assign('lpaEinstellungen', $lpaCreateEinstellungen);
Shop::Smarty()->assign('lpa_create_result', $success);
Shop::Smarty()->assign('lpa_account_active', $kontoAktiv);

if ($success && $kontoAktiv) {
    // succesfully created a new account and the account is active instantly
    // we can redirect  to the page where we started, if we want to, else let smarty handle the display
    redirectToLPACookieLocation();
}
