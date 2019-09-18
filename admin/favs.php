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

$kAdminlogin = (int)$_SESSION['AdminAccount']->kAdminlogin;

switch ($action) {
    case 'login':
        if ($response->isAjax()) {
            $result = $response->buildError('Unauthorized', 401);
            $response->makeResponse($result);
        } else {
            $oAccount->redirectOnFailure();
        }

        return;

    case 'add':
        $success = false;
        $title   = isset($_GET['title']) ? utf8_decode($_GET['title']) : null;
        $url     = isset($_GET['url']) ? utf8_decode($_GET['url']) : null;

        if (!empty($title) && !empty($url)) {
            $success = AdminFavorite::add($kAdminlogin, $title, $url);
        }

        if ($success) {
            $result = $response->buildResponse([
                'title' => $title,
                'url'   => $url
            ]);
        } else {
            $result = $response->buildError('Unauthorized', 401);
        }

        $response->makeResponse($result, $action);
        break;

    case 'list':
        $result = $response->buildResponse([
            'tpl' => $smarty->assign('favorites', $oAccount->favorites())
                            ->fetch('tpl_inc/favs_drop.tpl')
        ]);
        $response->makeResponse($result, $action);
        break;

    default:
        if (isset($_POST['title'], $_POST['url'])) {
            $titles = $_POST['title'];
            $urls   = $_POST['url'];

            if (is_array($titles) && is_array($urls) && count($titles) === count($urls)) {
                AdminFavorite::remove($kAdminlogin);
                foreach ($titles as $i => $title) {
                    AdminFavorite::add($kAdminlogin, $title, $urls[$i], $i);
                }
            }
        }

        $smarty->assign('favorites', $oAccount->favorites())
               ->display('favs.tpl');
        break;
}
