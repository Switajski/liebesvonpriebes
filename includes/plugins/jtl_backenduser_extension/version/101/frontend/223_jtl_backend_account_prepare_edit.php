<?php
/**
 * HOOK_BACKEND_ACCOUNT_PREPARE_EDIT
 *
 * Dieses Plugin erweitert Backend Nutzeraccounts um weitere Felder
 * Ausgabe der Felder im Backend
 *
 * @package   jtl_backenduser_extension
 * @copyright JTL-Software-GmbH
 *
 * @global array $args_arr
 * @global Plugin $oPlugin
 */
require_once $oPlugin->cAdminmenuPfad . 'include/backend_account_helper.php';

$args_arr['content'] = BackendAccountHelper::getInstance($oPlugin)->getContent(
    $args_arr['oAccount'],
    $args_arr['smarty'],
    $args_arr['attribs']
);
