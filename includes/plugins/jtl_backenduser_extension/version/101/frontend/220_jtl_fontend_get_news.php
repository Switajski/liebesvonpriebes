<?php
/**
 * HOOK_GET_NEWS
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
BackendAccountHelper::getInstance($oPlugin)->getFrontend($args_arr['oNews_arr'], 'NEWS', 'kNews');
