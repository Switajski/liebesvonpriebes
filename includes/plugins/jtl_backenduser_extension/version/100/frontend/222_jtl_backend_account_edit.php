<?php
/**
 * HOOK_BACKEND_ACCOUNT_EDIT
 *
 * Dieses Plugin erweitert Backend Nutzeraccounts um weitere Felder
 * Validierung der Felder im Backend
 *
 * @package   jtl_backenduser_extension
 * @copyright JTL-Software-GmbH
 *
 * @global array $args_arr
 * @global Plugin $oPlugin
 */
require_once $oPlugin->cAdminmenuPfad . 'include/backend_account_helper.php';

switch ($args_arr['type']) {
    case 'VALIDATE':
        $args_arr['result'] = BackendAccountHelper::getInstance($oPlugin)->validateAccount(
            $args_arr['oAccount'],
            $args_arr['attribs'],
            $args_arr['messages']
        );
        break;
    default:
        $args_arr['result'] = true;
}
