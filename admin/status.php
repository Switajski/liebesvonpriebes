<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_CLASSES . 'class.JTL-Shopadmin.AjaxResponse.php';
/** @global JTLSmarty $smarty */
$response = new AjaxResponse();
$action   = isset($_GET['action']) ? $_GET['action'] : null;

if ($oAccount->logged() !== true) {
    $action = 'login';
}

switch ($action) {
    case 'login':
        if ($response->isAjax()) {
            $result = $response->buildError('Unauthorized', 401);
            $response->makeResponse($result);
        } else {
            $oAccount->redirectOnFailure();
        }

        return;

    case 'notify':
        $result = $response->buildResponse([
            'tpl' => $smarty->assign('notifications', $notify)
                            ->fetch('tpl_inc/notify_drop.tpl')
        ]);
        $response->makeResponse($result, $action);
        break;

    default:
        $smarty->assign('status', Status::getInstance())
               ->assign('sub', Shop()->RS()->getSubscription())
               ->assign('phpLT55', version_compare(PHP_VERSION, '5.5') < 0)
               ->display('status.tpl');
        break;
}
