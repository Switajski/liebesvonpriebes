<?php

/*
 * This script handles the amazon specific checkout.
 */
// benötigt, um alle JTL-Funktionen zur Verfügung zu haben
require_once(dirname(__FILE__) . '/lib/lpa_includes.php');
require_once(PFAD_ROOT . PFAD_INCLUDES . "tools.Global.php");
require_once(PFAD_ROOT . PFAD_INCLUDES . "smartyInclude.php");
require_once(PFAD_ROOT . PFAD_INCLUDES . "bestellabschluss_inc.php");
$oPlugin = Plugin::getPluginById('s360_amazon_lpa_shop4');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPAController.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPADatabase.php');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPAAdapter.php');
require_once($oPlugin->cFrontendPfad . 'lib/lpa_defines.php');
require_once($oPlugin->cFrontendPfad . 'lib/lpa_utils.php');
require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.CheckBox.php");


$session = Session::getInstance();

pruefeHttps();

$bestellung = new Bestellung($_SESSION['kBestellung']);
$bestellung->fuelleBestellung(0);
$bestellung->machGoogleAnalyticsReady();
Shop::Smarty()->assign('Bestellung', $bestellung);
$Einstellungen = Shop::getSettings(array(CONF_GLOBAL, CONF_RSS, CONF_KUNDEN, CONF_SONSTIGES, CONF_NEWS, CONF_SITEMAP, CONF_ARTIKELUEBERSICHT, CONF_AUSWAHLASSISTENT));
Shop::Smarty()->assign('Einstellungen', $Einstellungen)
              ->assign('lpa_shop3_compatibility', $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_SHOP3_COMPATIBILITY]);

// Use custom template if it exists.
if (file_exists($oPlugin->cFrontendPfad . 'template/lpa_checkout_complete_custom.tpl')) {
    Shop::Smarty()->assign('cPluginTemplate', $oPlugin->cFrontendPfad . 'template/lpa_checkout_complete_custom.tpl');
}

/*
 * Clean session - this was "raeumeSessionAuf" in Shop 3
 */
$session->cleanUp();

if (empty($_SESSION['Kunde']) || intval($_SESSION['Kunde']->kKunde) == 0) {
    // unset kunde only if the customer was not actually logged in
    unset($_SESSION['Kunde']);
}
unset($_SESSION['Rechnungsadresse']);
unset($_SESSION['oVersandfreiKupon']);
unset($_SESSION['kommentar']);
