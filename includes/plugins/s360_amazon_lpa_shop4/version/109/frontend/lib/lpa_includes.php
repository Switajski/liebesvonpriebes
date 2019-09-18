<?php

/*
 * Replaces the need for the globalinclude.php file.
 */
require_once dirname(__FILE__) . '/../../../../../../config.JTL-Shop.ini.php';
require_once PFAD_ROOT . 'includes/defines.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'error_handler.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'autoload.php';

$shop = Shop::getInstance();

require_once PFAD_ROOT . PFAD_INCLUDES . 'tools.Global.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.StringHandler.php';
require_once PFAD_ROOT . PFAD_BLOWFISH . 'xtea.class.php';
require_once PFAD_ROOT . PFAD_CLASSES_CORE . 'class.core.NiceDB.php';
require_once PFAD_ROOT . PFAD_CLASSES_CORE . 'class.core.Nice.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Profiler.php';

// datenbankverbindung aufbauen, falls noch nicht offen
try {
    if(!isset($GLOBALS['DB']) || !$GLOBALS['DB']->isConnected()) {
        $GLOBALS['DB'] = new NiceDB(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    }
} catch (Exception $exc) {
    die($exc->getMessage());
}

$GLOBALS['bSeo'] = true; //seo module is always available, keep global for compatibility reasons
require_once PFAD_ROOT . PFAD_INCLUDES . 'plugin_inc.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Shopsetting.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.JTLCache.php';
$cache = JTLCache::getInstance();
$cache->setJtlCacheConfig();

$conf = Shop::getSettings(array(CONF_GLOBAL));

require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'parameterhandler.php';
//standard includes
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Template.php';
require_once PFAD_ROOT . PFAD_CLASSES_CORE . 'class.core.Session.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.helper.Artikel.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.helper.Url.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.helper.Link.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.helper.Versandart.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.MainModel.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Hersteller.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Warenkorb.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.ExtensionPoint.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Boxen.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Sprache.php';
//globale Werkzeuge
require_once PFAD_ROOT . PFAD_XAJAX . 'xajax_core/xajax.inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES_EXT . 'auswahlassistent_ext_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'artikelsuchspecial_inc.php';
// Liste aller Hooks, die momentan im Shop gebraucht werden könnten
// An jedem Hook hängt ein Array mit Plugin die diesen Hook benutzen
$oPluginHookListe_arr = Plugin::getHookList();
$nSystemlogFlag = getSytemlogFlag();
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Jtllog.php';


$GLOBALS['oSprache'] = Sprache::getInstance();
