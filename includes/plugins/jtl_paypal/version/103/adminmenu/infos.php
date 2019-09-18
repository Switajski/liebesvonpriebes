<?php

$results = null;
$type    = isset($_POST['validate']) ? $_POST['validate'] : null;

if ($type) {
    $module = "kPlugin_{$oPlugin->kPlugin}_paypal";

    switch ($type) {
        case 'basic':
            $module = "{$module}{$type}";
            require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . 'class/PayPalBasic.class.php';
            $payPal  = new PayPalBasic($oPlugin->oPluginZahlungsmethodeAssoc_arr[$module]->cModulId);
            $results = $payPal->test();
            break;
        case 'express':
            $module = "{$module}{$type}";
            require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . 'class/PayPalExpress.class.php';
            $payPal  = new PayPalExpress($oPlugin->oPluginZahlungsmethodeAssoc_arr[$module]->cModulId);
            $results = $payPal->test();
            break;
        case 'plus':
            $module = "{$module}{$type}";
            require_once str_replace('frontend', 'paymentmethod', $oPlugin->cFrontendPfad) . 'class/PayPalPlus.class.php';
            $payPal  = new PayPalPlus($oPlugin->oPluginZahlungsmethodeAssoc_arr[$module]->cModulId);
            $results = ['status' => 'success', 'msg' => ''];
            try {
                $payPal->isConfigured();
            } catch (Exception $ex) {
                $results = ['status' => 'Error', 'msg' => $ex->getMessage()];
            }
            break;
    }
    $results['type'] = $type;
}

$smarty->assign('results', $results)
    ->assign('post_url', URL_SHOP . '/' . PFAD_ADMIN . 'plugin.php?kPlugin=' . $oPlugin->kPlugin . '')
    ->display($oPlugin->cAdminmenuPfad . 'templates/infos.tpl');
