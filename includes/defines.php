<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

// Charset
ifndef('JTL_CHARSET', 'iso-8859-1');
ini_set('default_charset', JTL_CHARSET);
ifndef('SHOP_TIMEZONE', 'Europe/Berlin');
date_default_timezone_set(SHOP_TIMEZONE);
// Log-Levels
ifndef('SYNC_LOG_LEVEL', E_ERROR | E_PARSE);
ifndef('ADMIN_LOG_LEVEL', E_ERROR | E_PARSE);
ifndef('SHOP_LOG_LEVEL', E_ERROR | E_PARSE);
ifndef('SMARTY_LOG_LEVEL', E_ERROR | E_PARSE);
error_reporting(SHOP_LOG_LEVEL);
// if this is set to false, Hersteller, Linkgruppen and oKategorie_arr will not be added to $_SESSION
// this requires changes in templates!
ifndef('TEMPLATE_COMPATIBILITY', false);
// Image compatibility level 0 => disabled, 1 => referenced in history table, 2 => automatic detection
ifndef('IMAGE_COMPATIBILITY_LEVEL', 1);
ifndef('KEEP_SYNC_FILES', false);
ifndef('PROFILE_PLUGINS', false);
ifndef('PROFILE_SHOP', false);
ifndef('PROFILE_QUERIES', false);
ifndef('PROFILE_QUERIES_ECHO', false);
ifndef('IO_LOG_CONSOLE', false);
ifndef('DEFAULT_CURL_OPT_VERIFYPEER', true);
ifndef('DEFAULT_CURL_OPT_VERIFYHOST', 2);
/**
 * Lieferschwellen-Option: Gleichbleibende Bruttopreise (SHOP-2633)
 */
ifndef('CONSISTENT_GROSS_PRICES', false);
ini_set('session.use_trans_sid', 0);
// Logging (in logs/) 0 => aus, 1 => nur errors, 2 => errors, notifications, 3 => errors, notifications, debug
ifndef('ES_LOGGING', 1);
ifndef('ES_DB_LOGGING', 0);
// PHP Error Handler
ifndef('PHP_ERROR_HANDLER', false);
ifndef('DEBUG_FRAME', false);
ifndef('SMARTY_DEBUG_CONSOLE', false);
ifndef('SMARTY_SHOW_LANGKEY', false);
ifndef('SMARTY_FORCE_COMPILE', false);
ifndef('JTL_INCLUDE_ONLY_DB', 0);
ifndef('SOCKET_TIMEOUT', 30);
ifndef('ARTICLES_PER_PAGE_HARD_LIMIT', 100);
// Pfade
ifndef('PFAD_CLASSES', 'classes/');
ifndef('PFAD_CONFIG', 'config/');
ifndef('PFAD_INCLUDES', 'includes/');
ifndef('PFAD_TEMPLATES', 'templates/');
ifndef('PFAD_COMPILEDIR', 'templates_c/');
ifndef('PFAD_EMAILPDFS', 'emailpdfs/');
ifndef('PFAD_NEWSLETTERBILDER', 'newsletter/');
ifndef('PFAD_LINKBILDER', 'links/');
ifndef('PFAD_INCLUDES_LIBS', PFAD_INCLUDES . 'libs/');
ifndef('PFAD_MINIFY', PFAD_INCLUDES_LIBS . 'minify');
ifndef('PFAD_CKEDITOR', PFAD_INCLUDES_LIBS . 'ckeditor/');
ifndef('PFAD_CODEMIRROR', PFAD_INCLUDES_LIBS . 'codemirror-5.18.2/');
ifndef('PFAD_INCLUDES_TOOLS', PFAD_INCLUDES . 'tools/');
ifndef('PFAD_INCLUDES_EXT', PFAD_INCLUDES . 'ext/');
ifndef('PFAD_INCLUDES_MODULES', PFAD_INCLUDES . 'modules/');
ifndef('PFAD_SMARTY', PFAD_INCLUDES . 'vendor/smarty/smarty/libs/');
ifndef('SMARTY_DIR', PFAD_ROOT . PFAD_SMARTY);
ifndef('PFAD_XAJAX', PFAD_INCLUDES_LIBS . 'xajax_0.5_standard/');
ifndef('PFAD_FLASHCHART', PFAD_INCLUDES_LIBS . 'flashchart/');
ifndef('PFAD_FLASHCLOUD', PFAD_INCLUDES_LIBS . 'flashcloud/');
ifndef('PFAD_PHPQUERY', PFAD_INCLUDES_LIBS . 'phpQuery/');
ifndef('PFAD_PCLZIP', PFAD_INCLUDES_LIBS . 'pclzip-2-8-2/');
ifndef('PFAD_PHPMAILER', PFAD_INCLUDES . 'vendor/phpmailer/phpmailer/src/');
ifndef('PFAD_GRAPHCLASS', PFAD_INCLUDES_LIBS . 'graph-2005-08-28/');
ifndef('PFAD_AJAXCHECKOUT', PFAD_INCLUDES_LIBS . 'ajaxcheckout/');
ifndef('PFAD_AJAXSUGGEST', PFAD_INCLUDES_LIBS . 'ajaxsuggest/');
ifndef('PFAD_ART_ABNAHMEINTERVALL', PFAD_INCLUDES_LIBS . 'artikel_abnahmeintervall/');
ifndef('PFAD_BLOWFISH', PFAD_INCLUDES_LIBS . 'xtea/');
ifndef('PFAD_FLASHPLAYER', PFAD_INCLUDES_LIBS . 'flashplayer/');
ifndef('PFAD_IMAGESLIDER', PFAD_INCLUDES_LIBS . 'slideitmoo_image_slider/');
ifndef('PFAD_CLASSES_CORE', PFAD_CLASSES . 'core/');
ifndef('PFAD_OBJECT_CACHING', 'caching/');
ifndef('PFAD_GFX', 'gfx/');
ifndef('PFAD_GFX_AMPEL', PFAD_GFX . 'ampel/');
ifndef('PFAD_GFX_BEWERTUNG_STERNE', PFAD_GFX . 'bewertung_sterne/');
ifndef('PFAD_DBES', 'dbeS/');
ifndef('PFAD_DBES_TMP', PFAD_DBES . 'tmp/');
ifndef('PFAD_BILDER', 'bilder/');
ifndef('PFAD_BILDER_SLIDER', PFAD_BILDER . 'slider/');
ifndef('PFAD_CRON', 'cron/');
ifndef('PFAD_FONTS', PFAD_INCLUDES . 'fonts/');
ifndef('PFAD_BILDER_INTERN', PFAD_BILDER . 'intern/');
ifndef('PFAD_BILDER_BANNER', PFAD_BILDER . 'banner/');
ifndef('PFAD_NEWSBILDER', PFAD_BILDER . 'news/');
ifndef('PFAD_NEWSKATEGORIEBILDER', PFAD_BILDER . 'newskategorie/');
ifndef('PFAD_SHOPLOGO', PFAD_BILDER_INTERN . 'shoplogo/');
ifndef('PFAD_ADMIN', 'admin/');
ifndef('PFAD_EMAILVORLAGEN', PFAD_ADMIN . 'mailtemplates/');
ifndef('PFAD_MEDIAFILES', 'mediafiles/');
ifndef('PFAD_GFX_TRUSTEDSHOPS', PFAD_BILDER_INTERN . 'trustedshops/');
ifndef('PFAD_PRODUKTBILDER', PFAD_BILDER . 'produkte/');
ifndef('PFAD_PRODUKTBILDER_MINI', PFAD_PRODUKTBILDER . 'mini/');
ifndef('PFAD_PRODUKTBILDER_KLEIN', PFAD_PRODUKTBILDER . 'klein/');
ifndef('PFAD_PRODUKTBILDER_NORMAL', PFAD_PRODUKTBILDER . 'normal/');
ifndef('PFAD_PRODUKTBILDER_GROSS', PFAD_PRODUKTBILDER . 'gross/');
ifndef('PFAD_KATEGORIEBILDER', PFAD_BILDER . 'kategorien/');
ifndef('PFAD_VARIATIONSBILDER', PFAD_BILDER . 'variationen/');
ifndef('PFAD_VARIATIONSBILDER_MINI', PFAD_VARIATIONSBILDER . 'mini/');
ifndef('PFAD_VARIATIONSBILDER_NORMAL', PFAD_VARIATIONSBILDER . 'normal/');
ifndef('PFAD_VARIATIONSBILDER_GROSS', PFAD_VARIATIONSBILDER . 'gross/');
ifndef('PFAD_HERSTELLERBILDER', PFAD_BILDER . 'hersteller/');
ifndef('PFAD_HERSTELLERBILDER_NORMAL', PFAD_HERSTELLERBILDER . 'normal/');
ifndef('PFAD_HERSTELLERBILDER_KLEIN', PFAD_HERSTELLERBILDER . 'klein/');
ifndef('PFAD_MERKMALBILDER', PFAD_BILDER . 'merkmale/');
ifndef('PFAD_MERKMALBILDER_NORMAL', PFAD_MERKMALBILDER . 'normal/');
ifndef('PFAD_MERKMALBILDER_KLEIN', PFAD_MERKMALBILDER . 'klein/');
ifndef('PFAD_MERKMALWERTBILDER', PFAD_BILDER . 'merkmalwerte/');
ifndef('PFAD_MERKMALWERTBILDER_NORMAL', PFAD_MERKMALWERTBILDER . 'normal/');
ifndef('PFAD_MERKMALWERTBILDER_KLEIN', PFAD_MERKMALWERTBILDER . 'klein/');
ifndef('PFAD_BRANDINGBILDER', PFAD_BILDER . 'brandingbilder/');
ifndef('PFAD_SUCHSPECIALOVERLAY', PFAD_BILDER . 'suchspecialoverlay/');
ifndef('PFAD_SUCHSPECIALOVERLAY_KLEIN', PFAD_SUCHSPECIALOVERLAY . 'klein/');
ifndef('PFAD_SUCHSPECIALOVERLAY_NORMAL', PFAD_SUCHSPECIALOVERLAY . 'normal/');
ifndef('PFAD_SUCHSPECIALOVERLAY_GROSS', PFAD_SUCHSPECIALOVERLAY . 'gross/');
ifndef('PFAD_KONFIGURATOR_KLEIN', PFAD_BILDER . 'konfigurator/klein/');
ifndef('PFAD_LOGFILES', PFAD_ROOT . 'jtllogs/');
ifndef('PFAD_EXPORT', 'export/');
ifndef('PFAD_EXPORT_BACKUP', PFAD_EXPORT . 'backup/');
ifndef('PFAD_EXPORT_YATEGO', PFAD_EXPORT . 'yatego/');
ifndef('PFAD_UPDATE', 'update/');
ifndef('PFAD_WIDGETS', 'widgets/');
ifndef('PFAD_INSTALL', 'install/');
ifndef('PFAD_SHOPMD5', 'shopmd5files/');
ifndef('PFAD_NUSOAP', 'nusoap/');
ifndef('PFAD_UPLOADS', PFAD_ROOT . 'uploads/');
ifndef('PFAD_DOWNLOADS_REL', 'downloads/');
ifndef('PFAD_DOWNLOADS_PREVIEW_REL', PFAD_DOWNLOADS_REL . 'vorschau/');
ifndef('PFAD_DOWNLOADS', PFAD_ROOT . PFAD_DOWNLOADS_REL);
ifndef('PFAD_DOWNLOADS_PREVIEW', PFAD_ROOT . PFAD_DOWNLOADS_PREVIEW_REL);
ifndef('PFAD_UPLOADIFY', PFAD_INCLUDES_LIBS . 'uploadify/');
ifndef('PFAD_UPLOAD_CALLBACK', PFAD_INCLUDES_EXT . 'uploads_cb.php');
ifndef('PFAD_IMAGEMAP', PFAD_BILDER . 'banner/');
ifndef('PFAD_EMAILTEMPLATES', 'templates_mail/');
ifndef('PFAD_MEDIA_IMAGE', 'media/image/');
ifndef('PFAD_MEDIA_IMAGE_STORAGE', PFAD_MEDIA_IMAGE . 'storage/');
// Plugins
ifndef('PFAD_PLUGIN', PFAD_INCLUDES . 'plugins/');
// dbeS
ifndef('PFAD_SYNC_TMP', 'tmp/'); //rel zu dbeS
ifndef('PFAD_SYNC_LOGS', PFAD_ROOT . PFAD_DBES . 'logs/');
// Dateien
ifndef('FILE_RSS_FEED', 'rss.xml');
ifndef('FILE_SHOP_FEED', 'shopinfo.xml');
ifndef('FILE_PHPFEHLER', PFAD_LOGFILES . 'phperror.log');
// StandardBilder
ifndef('BILD_KEIN_KATEGORIEBILD_VORHANDEN', PFAD_GFX . 'keinBild.gif');
ifndef('BILD_KEIN_ARTIKELBILD_VORHANDEN', PFAD_GFX . 'keinBild.gif');
ifndef('BILD_KEIN_HERSTELLERBILD_VORHANDEN', PFAD_GFX . 'keinBild.gif');
ifndef('BILD_KEIN_MERKMALBILD_VORHANDEN', PFAD_GFX . 'keinBild.gif');
ifndef('BILD_KEIN_MERKMALWERTBILD_VORHANDEN', PFAD_GFX . 'keinBild_kl.gif');
ifndef('BILD_UPLOAD_ZUGRIFF_VERWEIGERT', PFAD_GFX . 'keinBild.gif');
//MediaImage Regex
ifndef('MEDIAIMAGE_REGEX', '/^media\/image\/(?P<type>product|category|variation|manufacturer)\/(?P<id>\d+)\/(?P<size>xs|sm|md|lg)\/(?P<name>[a-zA-Z0-9\-_]+)(?:(?:~(?P<number>\d+))?)\.(?P<ext>jpg|jpeg|png|gif)$/');
// Suchcache Lebensdauer in Minuten nach letzter Artikeländerung durch JTL-Wawi
ifndef('SUCHCACHE_LEBENSDAUER', 60);
// Steuersatz Standardland OVERRIDE - setzt ein anderes Steuerland, als im Shop angegeben (upper case, ISO 3166-2)
// ifndef('STEUERSATZ_STANDARD_LAND', 'DE')
ifndef('JTLLOG_MAX_LOGSIZE', 200000);
// temp dir for pclzip extension
ifndef('PCLZIP_TEMPORARY_DIR', PFAD_ROOT . PFAD_COMPILEDIR);

ifndef('IMAGE_PRELOAD_LIMIT', 10);
//when the shop has up to n categories, all category data will be loaded by KategorieHelper::combinedGetAll()
//with more then n categories, some db fields will only be selected if the corresponding options are active
ifndef('CATEGORY_FULL_LOAD_LIMIT', 10000);
ifndef('CATEGORY_FULL_LOAD_MAX_LEVEL', 3);
//maximum number of entries in category filter, -1 for no limit
ifndef('CATEGORY_FILTER_ITEM_LIMIT', -1);
ifndef('PRODUCT_LIST_SHOW_RATINGS', false);
ifndef('IMAGE_CLEANUP_LIMIT', 50);
ifndef('OBJECT_CACHE_DIR', PFAD_ROOT . PFAD_COMPILEDIR . 'filecache/');

ifndef('FIX_REGEX_ENCODING', true);

/**
 * @param string     $constant
 * @param string|int $value
 */
function ifndef($constant, $value)
{
    defined($constant) || define($constant, $value);
}

/*$shop_writeable_paths = array(
    // Directories
    //PFAD_BILDER_SLIDER,
    PFAD_GFX_TRUSTEDSHOPS, // ifndef('PFAD_GFX_TRUSTEDSHOPS', PFAD_BILDER_INTERN . 'trustedshops/');
    PFAD_NEWSBILDER, // ifndef('PFAD_NEWSBILDER', PFAD_BILDER . 'news/');
    PFAD_SHOPLOGO, // ifndef('PFAD_SHOPLOGO', PFAD_BILDER_INTERN . 'shoplogo/');
    PFAD_MEDIAFILES . 'Bilder',
    PFAD_MEDIAFILES . 'Musik',
    PFAD_MEDIAFILES . 'Sonstiges',
    PFAD_MEDIAFILES . 'Videos',
    PFAD_IMAGEMAP,
    PFAD_PRODUKTBILDER_MINI,
    PFAD_PRODUKTBILDER_KLEIN,
    PFAD_PRODUKTBILDER_NORMAL,
    PFAD_PRODUKTBILDER_GROSS,
    PFAD_KATEGORIEBILDER,
    PFAD_VARIATIONSBILDER_MINI,
    PFAD_VARIATIONSBILDER_NORMAL,
    PFAD_VARIATIONSBILDER_GROSS,
    PFAD_HERSTELLERBILDER_NORMAL,
    PFAD_HERSTELLERBILDER_KLEIN,
    PFAD_MERKMALBILDER_NORMAL,
    PFAD_MERKMALBILDER_KLEIN,
    PFAD_MERKMALWERTBILDER_NORMAL,
    PFAD_MERKMALWERTBILDER_KLEIN,
    PFAD_BRANDINGBILDER,
    PFAD_SUCHSPECIALOVERLAY_KLEIN,
    PFAD_SUCHSPECIALOVERLAY_NORMAL,
    PFAD_SUCHSPECIALOVERLAY_GROSS,
    PFAD_KONFIGURATOR_KLEIN,
    PFAD_BILDER . PFAD_LINKBILDER,
    PFAD_BILDER . PFAD_NEWSLETTERBILDER,
    PFAD_LOGFILES,
    PFAD_EXPORT,
    PFAD_EXPORT_BACKUP,
    PFAD_EXPORT_YATEGO,
    PFAD_COMPILEDIR,
    PFAD_DBES_TMP,
    PFAD_UPLOADS,
    PFAD_MEDIA_IMAGE,
    PFAD_MEDIA_IMAGE_STORAGE,
    PFAD_SYNC_LOGS,
    PFAD_ADMIN . PFAD_COMPILEDIR,
    PFAD_ADMIN . PFAD_INCLUDES . PFAD_EMAILPDFS,
    // Files
    FILE_RSS_FEED,
    FILE_SHOP_FEED
);
*/

/**
 * @deprecated
 * @return array
function shop_writeable_paths()
{
    trigger_error('The function "shop_writeable_paths()" is removed in a future version!', E_USER_DEPRECATED);

    global $shop_writeable_paths;

    return array_map(function ($v) {
        if (strpos($v, PFAD_ROOT) === 0) {
            $v = substr($v, strlen(PFAD_ROOT));
        }

        return trim($v, '/\\');
    }, $paths);
}
 */

// Static defines (do not edit)
require_once __DIR__ . '/defines_inc.php';
require_once __DIR__ . '/hooks_inc.php';
