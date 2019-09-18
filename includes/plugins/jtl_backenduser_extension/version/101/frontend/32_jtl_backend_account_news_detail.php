<?php
/**
 * HOOK_NEWS_PAGE_DETAILANSICHT
 *
 * Dieses Plugin erweitert Backend Nutzeraccounts um weitere Felder
 * Ausgabe der Felder im News Frontend
 *
 * @package   jtl_backenduser_extension
 * @copyright JTL-Software-GmbH
 *
 * @global array $args_arr
 * @global Plugin $oPlugin
 */
require_once $oPlugin->cAdminmenuPfad . 'include/backend_account_helper.php';

$smarty  = $GLOBALS['smarty'];
$newsArr = $smarty->getVariable('oNewsArchiv');

if (isset($newsArr->value)) {
    BackendAccountHelper::getInstance($oPlugin)->getFrontend([$newsArr->value], 'NEWS', 'kNews');
}
