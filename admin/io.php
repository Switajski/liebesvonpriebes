<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
/** @global AdminAccount $oAccount */

require_once __DIR__ . '/includes/admininclude.php';

if (!$oAccount->getIsAuthenticated()) {
    header(makeHTTPHeader(401));
    exit;
}
if (!validateToken()) {
    $io = IO::getInstance();
    $io->respondAndExit(new IOError('CSRF validation failed.', 500));
}

$jsonApi             = JSONAPI::getInstance();
$io                  = AdminIO::getInstance()->setAccount($oAccount);
$dashboardInc        = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dashboard_inc.php';
$accountInc          = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'benutzerverwaltung_inc.php';
$bannerInc           = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'banner_inc.php';
$sucheInc            = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'suche_inc.php';
$bilderverwaltungInc = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'bilderverwaltung_inc.php';
$sucheinstellungInc  = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'sucheinstellungen_inc.php';
$plzimportInc        = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'plz_ort_import_inc.php';
$redirectInc         = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'redirect_inc.php';
$dbupdaterInc        = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dbupdater_inc.php';

$io
    ->register('getPages', [$jsonApi, 'getPages'])
    ->register('getCategories', [$jsonApi, 'getCategories'])
    ->register('getProducts', [$jsonApi, 'getProducts'])
    ->register('getManufacturers', [$jsonApi, 'getManufacturers'])
    ->register('getCustomers', [$jsonApi, 'getCustomers'])
    ->register('getSeos', [$jsonApi, 'getSeos'])
    ->register('getTags', [$jsonApi, 'getTags'])
    ->register('getAttributes', [$jsonApi, 'getAttributes'])
    // Allround-IO-calls
    ->register('getCurrencyConversion', 'getCurrencyConversionIO')
    ->register('setCurrencyConversionTooltip', 'setCurrencyConversionTooltipIO')
    ->register('getNotifyDropIO')
    // Two-FA-related functions
    ->register('getNewTwoFA', ['TwoFA', 'getNewTwoFA'])
    ->register('genTwoFAEmergencyCodes', ['TwoFA', 'genTwoFAEmergencyCodes'])
    // Dashboard-related functions
    ->register('setWidgetPosition', 'setWidgetPosition', $dashboardInc, 'DASHBOARD_VIEW')
    ->register('closeWidget', 'closeWidget', $dashboardInc, 'DASHBOARD_VIEW')
    ->register('addWidget', 'addWidget', $dashboardInc, 'DASHBOARD_VIEW')
    ->register('expandWidget', 'expandWidget', $dashboardInc, 'DASHBOARD_VIEW')
    ->register('getAvailableWidgets', 'getAvailableWidgetsIO', $dashboardInc, 'DASHBOARD_VIEW')
    ->register('getRemoteData', 'getRemoteDataIO', $dashboardInc, 'DASHBOARD_VIEW')
    ->register('getShopInfo', 'getShopInfoIO', $dashboardInc, 'DASHBOARD_VIEW')
    ->register('truncateJtllog', ['Jtllog', 'truncateLog'], null, 'DASHBOARD_VIEW')
    ->register('addFav')
    ->register('reloadFavs')
    // Bilderverwaltung
    ->register('loadStats', 'loadStats', $bilderverwaltungInc, 'DISPLAY_IMAGES_VIEW')
    ->register('cleanupStorage', 'cleanupStorage', $bilderverwaltungInc, 'DISPLAY_IMAGES_VIEW')
    ->register('clearImageCache', 'clearImageCache', $bilderverwaltungInc, 'DISPLAY_IMAGES_VIEW')
    ->register('generateImageCache', 'generateImageCache', $bilderverwaltungInc, 'DISPLAY_IMAGES_VIEW')
    // PLZ-Import
    ->register('plzimportActionLoadAvailableDownloads', null, $plzimportInc, 'PLZ_ORT_IMPORT_VIEW')
    ->register('plzimportActionDoImport', null, $plzimportInc, 'PLZ_ORT_IMPORT_VIEW')
    ->register('plzimportActionResetImport', null, $plzimportInc, 'PLZ_ORT_IMPORT_VIEW')
    ->register('plzimportActionCallStatus', null, $plzimportInc, 'PLZ_ORT_IMPORT_VIEW')
    ->register('plzimportActionUpdateIndex', null, $plzimportInc, 'PLZ_ORT_IMPORT_VIEW')
    ->register('plzimportActionRestoreBackup', null, $plzimportInc, 'PLZ_ORT_IMPORT_VIEW')
    ->register('plzimportActionCheckStatus', null, $plzimportInc, 'PLZ_ORT_IMPORT_VIEW')
    ->register('plzimportActionDelTempImport', null, $plzimportInc, 'PLZ_ORT_IMPORT_VIEW')
    // DB-Updater
    ->register('dbUpdateIO', null, $dbupdaterInc, 'SHOP_UPDATE_VIEW')
    ->register('dbupdaterBackup', null, $dbupdaterInc, 'SHOP_UPDATE_VIEW')
    ->register('dbupdaterDownload', null, $dbupdaterInc, 'SHOP_UPDATE_VIEW')
    ->register('dbupdaterStatusTpl', null, $dbupdaterInc, 'SHOP_UPDATE_VIEW')
    ->register('dbupdaterMigration', null, $dbupdaterInc, 'SHOP_UPDATE_VIEW')
    // Redirects
    ->register('redirectCheckAvailability', ['Redirect', 'checkAvailability'])
    ->register('updateRedirectState', null, $redirectInc, 'REDIRECT_VIEW')
    // Other
    ->register('getRandomPassword', 'getRandomPasswordIO', $accountInc, 'ACCOUNT_VIEW')
    ->register('saveBannerAreas', 'saveBannerAreasIO', $bannerInc, 'DISPLAY_BANNER_VIEW')
    ->register('createSearchIndex', 'createSearchIndex', $sucheinstellungInc, 'SETTINGS_ARTICLEOVERVIEW_VIEW')
    ->register('clearSearchCache', 'clearSearchCache', $sucheinstellungInc, 'SETTINGS_ARTICLEOVERVIEW_VIEW')
    ->register('adminSearch', 'adminSearch', $sucheInc, 'SETTINGS_SEARCH_VIEW')
;

$data = $io->handleRequest($_REQUEST['io']);
$io->respondAndExit($data);
